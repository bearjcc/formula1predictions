#!/usr/bin/env python3
"""
Generate favicon and logo assets from a single source PNG (e.g. F1 car image).

Requirements: pip install Pillow

Usage:
  python scripts/prepare-favicon.py [path/to/source.png]

  If no path is given, uses scripts/source/venice-f1-car.png (copy your
  source image there first).

Outputs into public/:
  - favicon.ico (16, 32, 48)
  - favicon.svg (embeds 32x32 PNG)
  - apple-touch-icon.png (180x180)
  - images/logo.png (64px height, for sidebar)
"""

import argparse
import base64
import sys
from pathlib import Path

try:
    from PIL import Image
except ImportError:
    print("Pillow required: pip install Pillow", file=sys.stderr)
    sys.exit(1)

# Project root: parent of scripts/
SCRIPT_DIR = Path(__file__).resolve().parent
PROJECT_ROOT = SCRIPT_DIR.parent
PUBLIC_DIR = PROJECT_ROOT / "public"
DEFAULT_SOURCE = SCRIPT_DIR / "source" / "venice-f1-car.png"

# White -> transparent threshold (R, G, B all >= this)
WHITE_THRESHOLD = 250

# Sizes
FAVICON_SIZES = [(16, 16), (32, 32), (48, 48)]
APPLE_TOUCH_SIZE = (180, 180)
LOGO_HEIGHT = 64


def load_and_prepare(path: Path) -> Image.Image:
    """Load PNG, ensure RGBA, make near-white background transparent."""
    img = Image.open(path).convert("RGBA")
    data = img.getdata()
    new_data = []
    for item in data:
        r, g, b, a = item
        if r >= WHITE_THRESHOLD and g >= WHITE_THRESHOLD and b >= WHITE_THRESHOLD:
            new_data.append((r, g, b, 0))
        else:
            new_data.append(item)
    img.putdata(new_data)
    return img


def center_crop_square(img: Image.Image) -> Image.Image:
    """Crop to a square using the shorter side, centered."""
    w, h = img.size
    side = min(w, h)
    left = (w - side) // 2
    top = (h - side) // 2
    return img.crop((left, top, left + side, top + side))


def main() -> None:
    parser = argparse.ArgumentParser(description="Generate favicon and logo from a PNG.")
    parser.add_argument(
        "source",
        nargs="?",
        type=Path,
        default=DEFAULT_SOURCE,
        help="Path to source PNG (default: scripts/source/venice-f1-car.png)",
    )
    args = parser.parse_args()
    src = args.source.resolve()
    if not src.is_file():
        print(f"Source image not found: {src}", file=sys.stderr)
        print("Copy your PNG to scripts/source/venice-f1-car.png or pass path.", file=sys.stderr)
        sys.exit(1)

    img = load_and_prepare(src)
    square = center_crop_square(img)

    PUBLIC_DIR.mkdir(parents=True, exist_ok=True)
    (PUBLIC_DIR / "images").mkdir(parents=True, exist_ok=True)

    # favicon.ico (multi-size; source must be >= largest size so Pillow downscales)
    size48 = square.resize((48, 48), Image.Resampling.LANCZOS)
    ico_path = PUBLIC_DIR / "favicon.ico"
    size48.save(ico_path, format="ICO", sizes=FAVICON_SIZES)
    print(f"Wrote {ico_path}")

    # 32x32 for SVG embed
    size32 = square.resize((32, 32), Image.Resampling.LANCZOS)
    png32_path = PUBLIC_DIR / "favicon-32x32.png"
    size32.save(png32_path, format="PNG")
    print(f"Wrote {png32_path}")

    # favicon.svg with embedded 32x32 PNG
    with open(png32_path, "rb") as f:
        b64 = base64.standard_b64encode(f.read()).decode("ascii")
    svg = f'''<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 32 32" width="32" height="32">
  <image width="32" height="32" xlink:href="data:image/png;base64,{b64}"/>
</svg>'''
    svg_path = PUBLIC_DIR / "favicon.svg"
    svg_path.write_text(svg, encoding="utf-8")
    print(f"Wrote {svg_path}")

    # apple-touch-icon.png
    apple = square.resize(APPLE_TOUCH_SIZE, Image.Resampling.LANCZOS)
    apple_path = PUBLIC_DIR / "apple-touch-icon.png"
    apple.save(apple_path, format="PNG")
    print(f"Wrote {apple_path}")

    # Sidebar logo (fixed height, proportional width)
    w, h = square.size
    logo_w = int(w * LOGO_HEIGHT / h)
    logo = square.resize((logo_w, LOGO_HEIGHT), Image.Resampling.LANCZOS)
    logo_path = PUBLIC_DIR / "images" / "logo.png"
    logo.save(logo_path, format="PNG")
    print(f"Wrote {logo_path}")


if __name__ == "__main__":
    main()

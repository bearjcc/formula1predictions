# Dark Mode Testing Guidelines

## Overview
This document provides comprehensive guidelines for ensuring proper dark mode support across the F1 Predictions application. All UI changes must pass these checks before deployment.

## Dark Mode Testing Checklist

### Before Deploying Any UI Changes, Verify:

#### Text Contrast
- [ ] All text is readable against its background in both light and dark modes
- [ ] No black text appears on dark backgrounds
- [ ] No white text appears on light backgrounds
- [ ] Text contrast ratios meet WCAG 2.1 AA standards (4.5:1 for normal text, 3:1 for large text)
- [ ] Secondary and muted text maintain sufficient contrast in both modes

#### Interactive Elements
- [ ] Buttons have sufficient contrast in both light and dark modes
- [ ] Links are clearly distinguishable from regular text
- [ ] Form inputs have proper contrast for text and borders
- [ ] Focus indicators are visible in both modes
- [ ] Hover states work properly in both modes

#### Backgrounds and Cards
- [ ] Card backgrounds properly switch between light and dark modes
- [ ] Container backgrounds maintain proper contrast with content
- [ ] No hardcoded background colors that don't adapt to dark mode
- [ ] Shadow effects work appropriately in both modes

#### Icons and Visual Elements
- [ ] Icons maintain proper contrast against their backgrounds
- [ ] SVG icons adapt to dark mode when needed
- [ ] Images and graphics don't have hardcoded colors
- [ ] Decorative elements work in both modes

#### Navigation and Layout
- [ ] Navigation elements are clearly visible in both modes
- [ ] Sidebar and menu backgrounds adapt properly
- [ ] Breadcrumbs and navigation indicators work in both modes
- [ ] Layout spacing remains consistent

## Design System Color Classes

### Text Colors (Use These Instead of Hardcoded Colors)

```css
/* Primary text - automatically adapts */
.text-auto { /* Applied by default to all text elements */ }

/* Secondary text - slightly lighter but still readable */
.text-auto-secondary {
    @apply text-zinc-700 dark:text-zinc-300;
}

/* Muted text - for less important elements */
.text-auto-muted {
    @apply text-zinc-500 dark:text-zinc-400;
}

/* Inverse text - light on dark backgrounds, dark on light backgrounds */
.text-auto-inverse {
    @apply text-white dark:text-zinc-900;
}
```

### Background Colors

```css
/* Primary backgrounds */
.bg-auto-primary {
    @apply bg-white dark:bg-zinc-900;
}

/* Secondary backgrounds */
.bg-auto-secondary {
    @apply bg-zinc-50 dark:bg-zinc-800;
}

/* Tertiary backgrounds */
.bg-auto-tertiary {
    @apply bg-zinc-100 dark:bg-zinc-700;
}

/* Card backgrounds */
.bg-card {
    @apply bg-white dark:bg-zinc-800;
}
```

## Common Patterns to Avoid

### ❌ Don't Do This:
```html
<!-- Hardcoded colors without dark mode variants -->
<p class="text-zinc-600">This text will be invisible in dark mode</p>
<div class="bg-white">This background won't adapt</div>

<!-- Inconsistent color usage -->
<p class="text-zinc-600 dark:text-zinc-400">Manual dark mode variants</p>
```

### ✅ Do This Instead:
```html
<!-- Use auto-switching classes -->
<p class="text-auto-muted">This text automatically adapts</p>
<div class="bg-card">This background automatically adapts</div>

<!-- Or use the design system classes -->
<p class="text-body">Uses design system text colors</p>
<div class="card">Uses design system card styling</div>
```

## Testing Process

### 1. Manual Testing
1. Toggle between light and dark modes
2. Check each page and component
3. Verify all text is readable
4. Test interactive elements
5. Check focus states and hover effects

### 2. Automated Testing
Run the accessibility tests:
```bash
php artisan test --filter=AccessibilityTest
```

### 3. Browser Testing
- Test in Chrome, Firefox, Safari
- Test with different zoom levels
- Test with high contrast mode enabled
- Test with reduced motion preferences

### 4. Device Testing
- Test on mobile devices
- Test on tablets
- Test with different screen sizes
- Test with different pixel densities

## Color Contrast Validation

### Minimum Contrast Ratios (WCAG 2.1 AA)
- **Normal Text**: 4.5:1
- **Large Text (18pt+ or 14pt+ bold)**: 3:1
- **UI Components**: 3:1

### Tools for Testing
- Browser DevTools color contrast checker
- WebAIM Contrast Checker
- axe DevTools browser extension
- Lighthouse accessibility audit

## Common Issues and Solutions

### Issue: Text invisible in dark mode
**Solution**: Replace hardcoded colors with auto-switching classes
```html
<!-- Before -->
<p class="text-zinc-600">Invisible in dark mode</p>

<!-- After -->
<p class="text-auto-muted">Visible in both modes</p>
```

### Issue: Card backgrounds don't adapt
**Solution**: Use the card background class
```html
<!-- Before -->
<div class="bg-white">White background in dark mode</div>

<!-- After -->
<div class="bg-card">Adapts to dark mode</div>
```

### Issue: Icons not visible in dark mode
**Solution**: Ensure icons have proper color variants
```html
<!-- Before -->
<x-mary-icon name="o-home" class="text-zinc-600" />

<!-- After -->
<x-mary-icon name="o-home" class="text-zinc-600 dark:text-zinc-400" />
```

## Maintenance

### Regular Reviews
- Review new components for dark mode compliance
- Update this document when new patterns emerge
- Conduct monthly accessibility audits
- Keep design system classes up to date

### Code Reviews
- Always check for hardcoded colors in pull requests
- Verify dark mode variants are included
- Test color contrast ratios
- Ensure consistent use of design system classes

## Resources

- [WCAG 2.1 Guidelines](https://www.w3.org/WAI/WCAG21/quickref/)
- [WebAIM Contrast Checker](https://webaim.org/resources/contrastchecker/)
- [Tailwind CSS Dark Mode](https://tailwindcss.com/docs/dark-mode)
- [Design System Documentation](./DESIGN_SYSTEM.md)

---

**Remember**: Dark mode isn't just a preference—it's an accessibility requirement. Always test both modes before deploying any UI changes.

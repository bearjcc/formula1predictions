# F1 Predictions Design System

## Overview

This design system ensures consistent, accessible, and visually appealing components across the F1 Predictions application. All components follow WCAG 2.1 AA accessibility standards.

## Brand & external alignment

- **Visual style:** Colours and tone should align with F1’s official presence: [f1.com](https://f1.com), [F1 TV](https://f1tv.com), and [f1api.dev](https://f1api.dev). Prefer dark themes with F1 red accents and racing-inspired typography where it fits the app.
- **Assets:** Use only assets we have rights to: fair use, open source, or explicit permission. Document source and license for any third-party assets.

## Color Palette

### Primary Colors (F1 Brand)
- **Red 50-950**: F1 brand colors with proper contrast ratios
- **Usage**: Primary actions, branding, highlights

### Neutral Colors
- **Zinc 50-950**: High-contrast neutral palette
- **Usage**: Text, backgrounds, borders

### Semantic Colors
- **Success**: Green for positive actions
- **Warning**: Amber for caution states
- **Error**: Red for error states
- **Info**: Blue for informational content

## Typography

### Display Text
- `.text-display-1`: Large hero text (5xl, bold)
- `.text-display-2`: Secondary hero text (4xl, bold)

### Headings
- `.text-heading-1`: Main section headings (3xl, bold)
- `.text-heading-2`: Subsection headings (2xl, semibold)
- `.text-heading-3`: Card titles (xl, semibold)

### Body Text
- `.text-body-large`: Large body text (lg)
- `.text-body`: Standard body text (base)
- `.text-body-small`: Small body text (sm)
- `.text-caption`: Caption text (xs)

### Utility Classes
- `.text-muted`: Muted text color
- `.text-high-contrast`: Maximum contrast text
- `.text-medium-contrast`: Medium contrast text
- `.text-low-contrast`: Low contrast text

## Components

### Buttons

#### Primary Button
```html
<button class="btn-primary">
    Primary Action
</button>
```

#### Secondary Button
```html
<button class="btn-secondary">
    Secondary Action
</button>
```

#### Outline Button
```html
<button class="btn-outline">
    Outline Action
</button>
```

### Cards

#### Basic Card
```html
<div class="card">
    <div class="card-header">
        <h3 class="text-heading-3">Card Title</h3>
    </div>
    <div class="card-body">
        <p class="text-body">Card content goes here.</p>
    </div>
    <div class="card-footer">
        <button class="btn-primary">Action</button>
    </div>
</div>
```

### Forms

#### Input Field
```html
<label class="form-label">Label</label>
<input type="text" class="form-input" placeholder="Enter text">
<div class="form-error">Error message</div>
```

### Navigation

#### Navigation Link
```html
<a href="#" class="nav-link">
    <x-mary-icon name="o-home" class="w-4 h-4" />
    <span>Home</span>
</a>
```

## Accessibility Guidelines

### Color Contrast
- **Normal Text**: Minimum 4.5:1 contrast ratio
- **Large Text**: Minimum 3:1 contrast ratio
- **UI Components**: Minimum 3:1 contrast ratio

### Focus Management
- All interactive elements must have visible focus indicators
- Use `.focus-visible` class for custom focus styles
- Focus order should follow logical document flow

### Semantic HTML
- Use proper heading hierarchy (h1 → h2 → h3)
- Include alt text for all images
- Use proper button types and form labels
- Ensure link text is descriptive

### Keyboard Navigation
- All interactive elements must be keyboard accessible
- Tab order should be logical and intuitive
- Provide keyboard shortcuts for common actions

### Screen Reader Support
- Use `.sr-only` class for screen reader only text
- Include proper ARIA labels and roles
- Ensure form fields have associated labels

## Dark Mode Support

### Automatic Detection
The design system automatically adapts to user's dark mode preference using CSS custom properties. Appearance is set via `data-appearance` on `<html>` and a blocking script in `partials.head` so there is no flash (see [Auth layout design](docs/AUTH_LAYOUT_DESIGN.md) for the shared pattern).

### Manual Toggle
```html
<html class="dark">
    <!-- Dark mode styles applied -->
</html>
```

## Component Usage

- **Mary UI** (`x-mary-*`) is the primary UI component library. Use Mary components (x-mary-button, x-mary-card, x-mary-icon) for consistency.
- **daisyUI** classes (btn btn-primary, select select-bordered, form-control) appear in some forms; prefer Mary components for new forms.
- **Design system** classes (.btn-primary, .form-input, .form-label) are available when Mary/daisyUI do not fit.

## Usage Guidelines

### Do's
- ✅ Use Mary UI components when available for consistency
- ✅ Use design system classes for consistent styling
- ✅ Test color combinations for accessibility
- ✅ Include proper focus indicators
- ✅ Use semantic HTML elements
- ✅ Provide alternative text for images
- ✅ Test with keyboard navigation

### Don'ts
- ❌ Don't use hardcoded colors outside the design system
- ❌ Don't skip heading levels
- ❌ Don't rely solely on color to convey information
- ❌ Don't create custom focus styles without testing
- ❌ Don't use generic link text like "click here"

## Testing

### Automated Testing
Run accessibility tests:
```bash
php artisan test tests/Feature/AccessibilityTest.php
```

### Manual Testing
1. **Color Contrast**: Use browser dev tools to check contrast ratios
2. **Keyboard Navigation**: Navigate using Tab key only
3. **Screen Reader**: Test with NVDA, JAWS, or VoiceOver
4. **Focus Indicators**: Ensure all interactive elements have visible focus

### Browser Testing
- Test in multiple browsers (Chrome, Firefox, Safari, Edge)
- Test with different zoom levels (100%, 200%, 400%)
- Test with high contrast mode enabled

## Color Validation

### JavaScript Utilities
```javascript
import { validateColorCombination } from './resources/js/accessibility.js';

// Check if a color combination meets WCAG AA standards
const result = validateColorCombination('#000000', '#ffffff');
console.log(result.passes); // true/false
```

### Design System Validation
```javascript
import { validateDesignSystemColors } from './resources/js/accessibility.js';

// Validate all design system color combinations
const results = validateDesignSystemColors();
const failingCombinations = results.filter(r => !r.passes);
```

## Maintenance

### Adding New Colors
1. Add color to CSS custom properties
2. Update JavaScript color palette
3. Test contrast ratios
4. Update documentation

### Adding New Components
1. Create component with accessibility in mind
2. Add to design system CSS
3. Include focus management
4. Test with keyboard and screen readers
5. Update documentation

### Regular Audits
- Monthly accessibility audits
- Quarterly color contrast reviews
- Annual WCAG compliance checks

## Resources

- [WCAG 2.1 Guidelines](https://www.w3.org/WAI/WCAG21/quickref/)
- [Color Contrast Checker](https://webaim.org/resources/contrastchecker/)
- [Accessibility Testing Tools](https://www.w3.org/WAI/ER/tools/)
- [ARIA Guidelines](https://www.w3.org/WAI/ARIA/apg/)

## Support

For questions about the design system or accessibility:
1. Check this documentation
2. Review accessibility test results
3. Consult WCAG guidelines
4. Contact the development team


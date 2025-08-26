/**
 * F1 Predictions Accessibility Utilities
 * WCAG 2.1 AA Compliance Tools
 */

// Color contrast calculation utility
export function calculateContrastRatio(color1, color2) {
    const luminance1 = getLuminance(color1);
    const luminance2 = getLuminance(color2);
    
    const lighter = Math.max(luminance1, luminance2);
    const darker = Math.min(luminance1, luminance2);
    
    return (lighter + 0.05) / (darker + 0.05);
}

// Calculate relative luminance
function getLuminance(color) {
    const rgb = hexToRgb(color);
    if (!rgb) return 0;
    
    const { r, g, b } = rgb;
    
    const [rs, gs, bs] = [r, g, b].map(c => {
        c = c / 255;
        return c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4);
    });
    
    return 0.2126 * rs + 0.7152 * gs + 0.0722 * bs;
}

// Convert hex color to RGB
function hexToRgb(hex) {
    const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result ? {
        r: parseInt(result[1], 16),
        g: parseInt(result[2], 16),
        b: parseInt(result[3], 16)
    } : null;
}

// Check if contrast ratio meets WCAG AA standards
export function meetsWCAGAA(contrastRatio, isLargeText = false) {
    return isLargeText ? contrastRatio >= 3 : contrastRatio >= 4.5;
}

// Validate color combinations
export function validateColorCombination(textColor, backgroundColor) {
    const contrastRatio = calculateContrastRatio(textColor, backgroundColor);
    const meetsLargeText = meetsWCAGAA(contrastRatio, true);
    const meetsNormalText = meetsWCAGAA(contrastRatio, false);
    
    return {
        contrastRatio: Math.round(contrastRatio * 100) / 100,
        meetsLargeText,
        meetsNormalText,
        passes: meetsNormalText
    };
}

// Design system color palette
export const designSystemColors = {
    // Primary colors
    primary: {
        50: '#fef2f2',
        100: '#fee2e2',
        200: '#fecaca',
        300: '#fca5a5',
        400: '#f87171',
        500: '#ef4444',
        600: '#dc2626',
        700: '#b91c1c',
        800: '#991b1b',
        900: '#7f1d1d',
        950: '#450a0a'
    },
    
    // Neutral colors
    neutral: {
        50: '#fafafa',
        100: '#f5f5f5',
        200: '#e5e5e5',
        300: '#d4d4d4',
        400: '#a3a3a3',
        500: '#737373',
        600: '#525252',
        700: '#404040',
        800: '#262626',
        900: '#171717',
        950: '#0a0a0a'
    },
    
    // Text colors
    text: {
        primary: '#171717',
        secondary: '#525252',
        tertiary: '#737373',
        inverse: '#ffffff',
        muted: '#a3a3a3'
    },
    
    // Background colors
    background: {
        primary: '#ffffff',
        secondary: '#fafafa',
        tertiary: '#f5f5f5',
        inverse: '#171717'
    }
};

// Dark mode color palette
export const darkModeColors = {
    text: {
        primary: '#ffffff',
        secondary: '#e5e5e5',
        tertiary: '#d4d4d4',
        inverse: '#171717',
        muted: '#a3a3a3'
    },
    
    background: {
        primary: '#0a0a0a',
        secondary: '#171717',
        tertiary: '#262626',
        inverse: '#ffffff'
    }
};

// Validate design system color combinations
export function validateDesignSystemColors() {
    const results = [];
    
    // Test light mode combinations
    Object.entries(designSystemColors.text).forEach(([textKey, textColor]) => {
        Object.entries(designSystemColors.background).forEach(([bgKey, bgColor]) => {
            const validation = validateColorCombination(textColor, bgColor);
            results.push({
                mode: 'light',
                textColor: textKey,
                backgroundColor: bgKey,
                ...validation
            });
        });
    });
    
    // Test dark mode combinations
    Object.entries(darkModeColors.text).forEach(([textKey, textColor]) => {
        Object.entries(darkModeColors.background).forEach(([bgKey, bgColor]) => {
            const validation = validateColorCombination(textColor, bgColor);
            results.push({
                mode: 'dark',
                textColor: textKey,
                backgroundColor: bgKey,
                ...validation
            });
        });
    });
    
    return results;
}

// Accessibility audit function
export function runAccessibilityAudit() {
    const issues = [];
    
    // Check for missing alt text on images
    const images = document.querySelectorAll('img');
    images.forEach((img, index) => {
        if (!img.alt && !img.getAttribute('aria-label')) {
            issues.push({
                type: 'missing-alt-text',
                element: img,
                severity: 'error',
                message: `Image at index ${index} is missing alt text or aria-label`
            });
        }
    });
    
    // Check for proper heading hierarchy
    const headings = document.querySelectorAll('h1, h2, h3, h4, h5, h6');
    let previousLevel = 0;
    headings.forEach((heading, index) => {
        const level = parseInt(heading.tagName.charAt(1));
        if (level - previousLevel > 1) {
            issues.push({
                type: 'heading-hierarchy',
                element: heading,
                severity: 'warning',
                message: `Heading hierarchy skipped from h${previousLevel} to h${level}`
            });
        }
        previousLevel = level;
    });
    
    // Check for proper button semantics
    const buttons = document.querySelectorAll('button');
    buttons.forEach((button, index) => {
        if (!button.type) {
            issues.push({
                type: 'missing-button-type',
                element: button,
                severity: 'error',
                message: `Button at index ${index} is missing type attribute`
            });
        }
    });
    
    // Check for proper link text
    const links = document.querySelectorAll('a');
    links.forEach((link, index) => {
        const linkText = link.textContent.trim();
        if (!linkText || linkText.length < 2) {
            issues.push({
                type: 'insufficient-link-text',
                element: link,
                severity: 'error',
                message: `Link at index ${index} has insufficient descriptive text: "${linkText}"`
            });
        }
    });
    
    // Check for focus management
    const focusableElements = document.querySelectorAll('button, a, input, select, textarea, [tabindex]');
    focusableElements.forEach((element, index) => {
        const computedStyle = window.getComputedStyle(element);
        if (computedStyle.outline === 'none' && !element.classList.contains('focus-visible')) {
            issues.push({
                type: 'missing-focus-styles',
                element: element,
                severity: 'warning',
                message: `Focusable element at index ${index} may have insufficient focus indicators`
            });
        }
    });
    
    return issues;
}

// Auto-fix common accessibility issues
export function autoFixAccessibilityIssues() {
    const fixes = [];
    
    // Add missing button types
    const buttons = document.querySelectorAll('button:not([type])');
    buttons.forEach(button => {
        button.type = 'button';
        fixes.push('Added missing type="button" to buttons');
    });
    
    // Add missing alt text to decorative images
    const images = document.querySelectorAll('img:not([alt]):not([aria-label])');
    images.forEach(img => {
        if (img.classList.contains('decorative') || img.role === 'presentation') {
            img.alt = '';
            fixes.push('Added empty alt text to decorative images');
        }
    });
    
    return fixes;
}

// Initialize accessibility monitoring
export function initAccessibilityMonitoring() {
    // Monitor for dynamic content changes
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.type === 'childList') {
                // Check new nodes for accessibility issues
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        const issues = runAccessibilityAudit();
                        if (issues.length > 0) {
                            console.warn('Accessibility issues detected:', issues);
                        }
                    }
                });
            }
        });
    });
    
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
    
    return observer;
}

// Export default accessibility utilities
export default {
    calculateContrastRatio,
    meetsWCAGAA,
    validateColorCombination,
    validateDesignSystemColors,
    runAccessibilityAudit,
    autoFixAccessibilityIssues,
    initAccessibilityMonitoring
};


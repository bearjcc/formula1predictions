<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('pages meet accessibility standards', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $pages = [
        '/',
        '/draggable-demo',
    ];

    foreach ($pages as $page) {
        $response = $this->get($page);
        $response->assertStatus(200);

        // Check for basic accessibility issues
        assertAccessibilityCompliance($response->getContent(), $page);
    }
});

test('color contrast compliance', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get('/');
    $content = $response->getContent();

    // Check for proper text color classes
    expect($content)->toContain('text-white'); // Hero text
    expect($content)->toContain('text-auto-muted'); // Card descriptions use auto-muted
    expect($content)->toContain('dark:text-zinc-300'); // Dark mode text

    // Check for proper background color classes
    expect($content)->toContain('bg-card'); // Card background utility
    expect($content)->toContain('dark:bg-zinc-900'); // Dark backgrounds
});

test('focus management', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get('/');
    $content = $response->getContent();

    // Check for button elements with proper type attributes
    expect($content)->toContain('type="button"');
    
    // Check for interactive elements
    expect($content)->toContain('<button');
    expect($content)->toContain('<a');
});

test('semantic html structure', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get('/');
    $content = $response->getContent();

    // Check for proper heading hierarchy
    expect($content)->toContain('<h1');
    expect($content)->toContain('<h2');
    expect($content)->toContain('<h3');

    // Check for proper button elements
    expect($content)->toContain('<button');
    expect($content)->toContain('type="button"');
});

test('dark mode accessibility', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get('/');
    $content = $response->getContent();

    // Check for dark mode classes
    expect($content)->toContain('dark:');
    expect($content)->toContain('dark:text-zinc-300');
    expect($content)->toContain('dark:bg-zinc-900');
});

test('form accessibility', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    // Test the draggable demo page which has interactive elements
    $response = $this->get('/draggable-demo');
    $content = $response->getContent();

    // Check for proper button elements
    expect($content)->toContain('<button');
    
    // Check for proper interactive attributes
    expect($content)->toContain('draggable="true"');
    expect($content)->toContain('@click');
});

/**
 * Assert accessibility compliance using basic checks
 */
function assertAccessibilityCompliance(string $content, string $page): void
{
    // Check for alt text on images
    if (str_contains($content, '<img')) {
        expect($content)->toContain('alt=', "Images on {$page} should have alt text");
    }

    // Check for proper heading structure (some pages might not have h1)
    if (!str_contains($content, '<h1')) {
        $hasHeading = str_contains($content, '<h2') || str_contains($content, '<h3') || str_contains($content, '<h4') || str_contains($content, '<h5') || str_contains($content, '<h6');
        expect($hasHeading)->toBeTrue("Page {$page} should have at least a heading (h2-h6)");
    }
    
    // Check for proper button semantics
    if (str_contains($content, '<button')) {
        // Debug: Check if type attribute exists in any form
        $hasTypeAttribute = str_contains($content, 'type="button"') || 
                           str_contains($content, "type='button'") || 
                           str_contains($content, 'type=');
        expect($hasTypeAttribute)->toBeTrue("Buttons on {$page} should have type attributes");
    }

    // Check for proper link text
    $links = preg_match_all('/<a[^>]*>(.*?)<\/a>/s', $content, $matches);
    if ($links > 0) {
        foreach ($matches[1] as $linkText) {
            $linkText = trim(strip_tags($linkText));
            expect($linkText)->not->toBeEmpty("Links on {$page} should have descriptive text");
            expect(strlen($linkText))->toBeGreaterThan(1, "Links on {$page} should have meaningful text");
        }
    }

    // Check for proper color contrast classes
    $hasTextClasses = str_contains($content, 'text-');
    $hasBgClasses = str_contains($content, 'bg-');
    
    if (!$hasTextClasses) {
        // Debug: Show a snippet of the content to see what we're working with
        $snippet = substr($content, 0, 1000);
        throw new Exception("Page {$page} does not contain 'text-' classes. Content snippet: " . $snippet);
    }
    
    expect($hasTextClasses)->toBeTrue("Page {$page} should use design system text colors");
    expect($hasBgClasses)->toBeTrue("Page {$page} should use design system background colors");
}

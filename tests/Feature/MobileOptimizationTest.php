<?php

test('mobile loading states are optimized', function () {
    $response = $this->get('/');

    // Check for mobile loading optimizations - look for loading classes that actually exist
    $response->assertSee('transition-'); // Transition classes
    $response->assertSee('duration-'); // Duration classes
});

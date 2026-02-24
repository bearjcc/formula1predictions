<?php

uses()->group('browser', 'slow');

it('can visit the homepage', function () {
    $page = visit('/')
        ->on()->desktop()
        ->inLightMode();

    $page->assertNoJavascriptErrors();
});

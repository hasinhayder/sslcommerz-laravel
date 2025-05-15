<?php

declare(strict_types=1);

test('has config', function () {
    expect(config('sslcommerz'))
        ->toHaveKeys(['sandbox', 'store', 'route']);
});

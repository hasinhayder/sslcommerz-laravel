<?php

declare(strict_types=1);

use HasinHayder\Sslcommerz\Exceptions\SslcommerzException;

describe('SslcommerzException', function () {
    it('throws and returns message', function () {
        $msg = 'Test error';
        $exception = new SslcommerzException($msg);
        expect($exception->getMessage())->toBe($msg);
    });
});

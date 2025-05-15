<?php

declare(strict_types=1);

use Raziul\Sslcommerz\Data\PaymentResponse;

describe('PaymentResponse', function () {
    it('initializes with null data by default', function () {
        $response = new PaymentResponse;
        expect($response->toArray())->toBeNull();
    });

    it('initializes with provided data', function () {
        $data = ['status' => 'SUCCESS'];
        $response = new PaymentResponse($data);
        expect($response->toArray())->toBe($data);
    });

    describe('status()', function () {
        it('returns status in lowercase', function () {
            $response = new PaymentResponse(['status' => 'SUCCESS']);
            expect($response->status())->toBe('success');
        });

        it('returns null when status is missing', function () {
            $response = new PaymentResponse(['other' => 'data']);
            expect($response->status())->toBeNull();
        });
    });

    describe('success()', function () {
        it('returns true when status is success', function () {
            $response = new PaymentResponse(['status' => 'success']);
            expect($response->success())->toBeTrue();
        });

        it('returns false for non-success status', function () {
            $response = new PaymentResponse(['status' => 'failed']);
            expect($response->success())->toBeFalse();
        });

        it('returns false when status is missing', function () {
            $response = new PaymentResponse;
            expect($response->success())->toBeFalse();
        });
    });

    describe('failed()', function () {
        it('returns true when failed reason exists', function () {
            $response = new PaymentResponse(['failedreason' => 'error']);
            expect($response->failed())->toBeTrue();
        });

        it('returns false when no failed reason', function () {
            $response = new PaymentResponse;
            expect($response->failed())->toBeFalse();
        });
    });

    describe('failedReason()', function () {
        it('returns failed reason', function () {
            $response = new PaymentResponse(['failedreason' => 'Invalid card']);
            expect($response->failedReason())->toBe('Invalid card');
        });

        it('returns null when failed reason is missing', function () {
            $response = new PaymentResponse;
            expect($response->failedReason())->toBeNull();
        });
    });

    describe('sessionKey()', function () {
        it('returns session key', function () {
            $response = new PaymentResponse(['sessionkey' => 'abc123']);
            expect($response->sessionKey())->toBe('abc123');
        });

        it('returns null when session key is missing', function () {
            $response = new PaymentResponse;
            expect($response->sessionKey())->toBeNull();
        });
    });

    describe('gatewayList()', function () {
        it('returns gateway list', function () {
            $gateways = ['visa', 'mastercard'];
            $response = new PaymentResponse(['gw' => $gateways]);
            expect($response->gatewayList())->toBe($gateways);
        });

        it('returns null when gateway list is missing', function () {
            $response = new PaymentResponse;
            expect($response->gatewayList())->toBeNull();
        });
    });

    describe('URL methods', function () {
        $urls = [
            'gatewayPageURL' => 'GatewayPageURL',
            'redirectGatewayURL' => 'redirectGatewayURL',
            'directPaymentURLBank' => 'directPaymentURLBank',
            'directPaymentURLCard' => 'directPaymentURLCard',
            'directPaymentURL' => 'directPaymentURL',
            'redirectGatewayURLFailed' => 'redirectGatewayURLFailed',
        ];

        foreach ($urls as $method => $key) {
            it("returns {$key} for {$method}()", function () use ($method, $key) {
                $url = "https://example.com/{$key}";
                $response = new PaymentResponse([$key => $url]);
                expect($response->$method())->toBe($url);
            });

            it("returns null when {$key} is missing", function () use ($method) {
                $response = new PaymentResponse;
                expect($response->$method())->toBeNull();
            });
        }
    });

    describe('store media methods', function () {
        it('returns store banner URL', function () {
            $response = new PaymentResponse(['storeBanner' => 'banner.jpg']);
            expect($response->storeBanner())->toBe('banner.jpg');
        });

        it('returns store logo URL', function () {
            $response = new PaymentResponse(['storeLogo' => 'logo.png']);
            expect($response->storeLogo())->toBe('logo.png');
        });
    });

    describe('description()', function () {
        it('returns description array', function () {
            $desc = ['item1' => 'Product 1'];
            $response = new PaymentResponse(['desc' => $desc]);
            expect($response->description())->toBe($desc);
        });

        it('returns null when description is missing', function () {
            $response = new PaymentResponse;
            expect($response->description())->toBeNull();
        });
    });

    it('returns null for all methods when data is null', function () {
        $response = new PaymentResponse;

        expect($response->status())->toBeNull()
            ->and($response->sessionKey())->toBeNull()
            ->and($response->gatewayList())->toBeNull()
            ->and($response->gatewayPageURL())->toBeNull()
            ->and($response->redirectGatewayURL())->toBeNull()
            ->and($response->storeBanner())->toBeNull()
            ->and($response->description())->toBeNull();
    });
});

<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Raziul\Sslcommerz\Data\PaymentResponse;
use Raziul\Sslcommerz\Data\RefundResponse;
use Raziul\Sslcommerz\Data\RefundStatus;
use Raziul\Sslcommerz\Facades\Sslcommerz;

beforeEach(function () {
    config()->set('sslcommerz.store.id', 'test_store');
    config()->set('sslcommerz.store.password', 'test_pass');
    config()->set('sslcommerz.store.currency', 'BDT');
    config()->set('sslcommerz.sandbox', true);
    config()->set('sslcommerz.route.success', 'home');
    config()->set('sslcommerz.route.failure', 'home');
    config()->set('sslcommerz.route.cancel', 'home');
    config()->set('sslcommerz.route.ipn', 'home');
    config()->set('sslcommerz.product_profile', 'general');
    Route::get('home', fn () => 'ok')->name('home');
});

describe('Sslcommerz Facade', function () {
    it('can set order and make payment', function () {
        Http::fake([
            'sandbox.sslcommerz.com/gwprocess/v4/api.php' => Http::response([
                'status' => 'SUCCESS',
                'GatewayPageURL' => 'https://sandbox.sslcommerz.com/gwprocess/v4/gateway.php?session=abc',
            ], 200),
        ]);
        $response = Sslcommerz::setOrder(1000, 'INV123', 'Test Product')->makePayment();
        expect($response)->toBeInstanceOf(PaymentResponse::class);
        expect($response->status())->toBe('success');
    });

    it('can refund and check refund status', function () {
        Http::fake([
            'sandbox.sslcommerz.com/validator/api/merchantTransIDvalidationAPI.php*' => Http::response([
                'status' => 'SUCCESS',
                'refund_ref_id' => 'RR123',
            ], 200),
        ]);
        $refund = Sslcommerz::refundPayment('BANK123', 100, 'Test refund');
        expect($refund)->toBeInstanceOf(RefundResponse::class);
        expect($refund->status())->toBe('success');

        $status = Sslcommerz::checkRefundStatus('RR123');
        expect($status)->toBeInstanceOf(RefundStatus::class);
        expect($status->status())->toBe('success');
    });
});

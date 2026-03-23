<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use HasinHayder\Sslcommerz\Data\PaymentResponse;
use HasinHayder\Sslcommerz\Data\RefundResponse;
use HasinHayder\Sslcommerz\Data\RefundStatus;
use HasinHayder\Sslcommerz\SslcommerzClient;

describe('SslcommerzClient', function () {
    beforeEach(function () {
        $this->storeId = 'test_store';
        $this->storePassword = 'test_pass';
        $this->currency = 'BDT';
        $this->sandbox = true;
        $this->client = new SslcommerzClient(
            $this->storeId,
            $this->storePassword,
            $this->currency,
            $this->sandbox
        );
    });

    it('sets order data', function () {
        $client = $this->client->setOrder(1000, 'INV123', 'Test Product', 'Category');
        expect($client)->toBeInstanceOf(SslcommerzClient::class);
    });

    it('sets customer data', function () {
        $client = $this->client->setCustomer('John Doe', 'john@example.com', '0123456789', 'Addr', 'Dhaka', 'Dhaka', '1200', 'Bangladesh');
        expect($client)->toBeInstanceOf(SslcommerzClient::class);
    });

    it('sets shipping info', function () {
        $client = $this->client->setShippingInfo(1, 'Addr', 'John Doe', 'Dhaka', 'Dhaka', '1200', 'Bangladesh');
        expect($client)->toBeInstanceOf(SslcommerzClient::class);
    });

    it('sets product profile', function () {
        $client = $this->client->setProductProfile('general');
        expect($client)->toBeInstanceOf(SslcommerzClient::class);
    });

    it('sets gateways', function () {
        $client = $this->client->setGateways(['bkash', 'dbbl']);
        expect($client)->toBeInstanceOf(SslcommerzClient::class);
    });

    it('sets callback urls', function () {
        $client = $this->client->setCallbackUrls('success', 'fail', 'cancel', 'ipn');
        expect($client)->toBeInstanceOf(SslcommerzClient::class);
    });

    it('makes payment and returns PaymentResponse', function () {
        Http::fake([
            'sandbox.sslcommerz.com/gwprocess/v4/api.php' => Http::response([
                'status' => 'SUCCESS',
                'GatewayPageURL' => 'https://sandbox.sslcommerz.com/gwprocess/v4/gateway.php?session=abc',
            ], 200),
        ]);
        $client = $this->client->setOrder(1000, 'INV123', 'Test Product');
        $response = $client->makePayment();
        expect($response)->toBeInstanceOf(PaymentResponse::class);
        expect($response->status())->toBe('success');
    });

    it('uses the live gateway when sandbox mode is disabled', function () {
        $liveClient = new SslcommerzClient('live_store', 'live_pass', 'BDT', false);

        Http::fake([
            'securepay.sslcommerz.com/gwprocess/v4/api.php' => Http::response([
                'status' => 'SUCCESS',
                'GatewayPageURL' => 'https://securepay.sslcommerz.com/gwprocess/v4/gateway.php?session=xyz',
            ], 200),
        ]);

        $response = $liveClient->setOrder(1000, 'INV999', 'Live Product')->makePayment();

        expect($response)->toBeInstanceOf(PaymentResponse::class);
        expect($response->gatewayPageURL())->toBe('https://securepay.sslcommerz.com/gwprocess/v4/gateway.php?session=xyz');
    });

    it('merges payment payload data from callbacks gateways and additional data', function () {
        Http::fake([
            'sandbox.sslcommerz.com/gwprocess/v4/api.php' => Http::response([
                'status' => 'SUCCESS',
                'GatewayPageURL' => 'https://sandbox.sslcommerz.com/gwprocess/v4/gateway.php?session=abc',
            ], 200),
        ]);

        $this->client
            ->setCallbackUrls('success-url', 'failure-url', 'cancel-url', 'ipn-url')
            ->setGateways(['bkash', 'dbbl'])
            ->setOrder(1250, 'INV1250', 'Test Product', 'Category')
            ->setCustomer('John Doe', 'john@example.com')
            ->setShippingInfo(2, 'Dhaka')
            ->makePayment([
                'custom_field' => 'custom-value',
            ]);

        Http::assertSent(function (Request $request): bool {
            $data = $request->data();

            return $data['success_url'] === 'success-url'
                && $data['fail_url'] === 'failure-url'
                && $data['cancel_url'] === 'cancel-url'
                && $data['ipn_url'] === 'ipn-url'
                && $data['multi_card_name'] === 'bkash,dbbl'
                && $data['custom_field'] === 'custom-value'
                && $data['tran_id'] === 'INV1250'
                && $data['cus_name'] === 'John Doe'
                && $data['num_of_item'] === 2;
        });
    });

    it('validates payment successfully', function () {
        Http::fake([
            'sandbox.sslcommerz.com/validator/api/validationserverAPI.php*' => Http::response([
                'status' => 'VALID',
                'tran_id' => 'INV123',
                'amount' => 1000,
                'currency_type' => 'BDT',
                'currency_amount' => 1000,
            ], 200),
        ]);
        $payload = ['val_id' => 'val123'];
        $result = $this->client->validatePayment($payload, 'INV123', 1000, 'BDT');
        expect($result)->toBeTrue();
    });

    it('returns false for payment validation with missing val_id', function () {
        $payload = [];
        $result = $this->client->validatePayment($payload, 'INV123', 1000, 'BDT');
        expect($result)->toBeFalse();
    });

    it('returns false for payment validation when response is empty', function () {
        Http::fake([
            'sandbox.sslcommerz.com/validator/api/validationserverAPI.php*' => Http::response([], 200),
        ]);
        $payload = ['val_id' => 'val123'];
        $result = $this->client->validatePayment($payload, 'INV123', 1000, 'BDT');
        expect($result)->toBeFalse();
    });

    it('returns false for payment validation when required keys are missing or status is INVALID_TRANSACTION', function () {
        Http::fake([
            'sandbox.sslcommerz.com/validator/api/validationserverAPI.php*' => Http::response([
                'status' => 'INVALID_TRANSACTION ',
                // missing tran_id and amount
            ], 200),
        ]);
        $payload = ['val_id' => 'val123'];
        $result = $this->client->validatePayment($payload, 'INV123', 1000, 'BDT');
        expect($result)->toBeFalse();
    });

    it('returns false for payment validation when transaction id does not match', function () {
        Http::fake([
            'sandbox.sslcommerz.com/validator/api/validationserverAPI.php*' => Http::response([
                'status' => 'VALID',
                'tran_id' => 'NOT_MATCHING',
                'amount' => 1000,
                'currency_type' => 'BDT',
                'currency_amount' => 1000,
            ], 200),
        ]);
        $payload = ['val_id' => 'val123'];
        $result = $this->client->validatePayment($payload, 'INV123', 1000, 'BDT');
        expect($result)->toBeFalse();
    });

    it('returns false for verifyHash when verify_sign is missing', function () {
        $data = [
            'verify_key' => 'foo',
            'foo' => 'bar',
        ];
        $result = $this->client->verifyHash($data);
        expect($result)->toBeFalse();
    });

    it('returns false for verifyHash when verify_key is missing', function () {
        $data = [
            'verify_sign' => 'somesign',
            'foo' => 'bar',
        ];
        $result = $this->client->verifyHash($data);
        expect($result)->toBeFalse();
    });

    it('verifies hash correctly', function () {
        $data = [
            'verify_sign' => 'd3b07384d113edec49eaa6238ad5ff00',
            'verify_key' => 'store_id,tran_id',
            'store_id' => 'test_store',
            'tran_id' => 'INV-001',
        ];

        // Manually calculate expected hash
        $hashString = 'store_id=test_store&store_passwd=' . md5('test_pass') . '&tran_id=INV-001';
        $expectedHash = md5($hashString);
        $data['verify_sign'] = $expectedHash;

        $valid = $this->client->verifyHash($data);
        expect($valid)->toBeTrue();
    });

    it('refunds payment and returns RefundResponse', function () {
        Http::fake([
            'sandbox.sslcommerz.com/validator/api/merchantTransIDvalidationAPI.php*' => Http::response([
                'status' => 'SUCCESS',
                'refund_ref_id' => 'RR123',
            ], 200),
        ]);
        $response = $this->client->refundPayment('BANK123', 100, 'Test refund');
        expect($response)->toBeInstanceOf(RefundResponse::class);
        expect($response->status())->toBe('success');
    });

    it('checks refund status and returns RefundStatus', function () {
        Http::fake([
            'sandbox.sslcommerz.com/validator/api/merchantTransIDvalidationAPI.php*' => Http::response([
                'status' => 'SUCCESS',
                'refund_ref_id' => 'RR123',
            ], 200),
        ]);
        $response = $this->client->checkRefundStatus('RR123');
        expect($response)->toBeInstanceOf(RefundStatus::class);
        expect($response->status())->toBe('success');
    });

    it('returns true for payment validation when currency is not BDT and matches', function () {
        Http::fake([
            'sandbox.sslcommerz.com/validator/api/validationserverAPI.php*' => Http::response([
                'status' => 'VALID',
                'tran_id' => 'INV123',
                'amount' => 1000,
                'currency_type' => 'USD',
                'currency_amount' => 50,
            ], 200),
        ]);
        $client = new SslcommerzClient('test_store', 'test_pass', 'USD', true);
        $payload = ['val_id' => 'val123'];
        $result = $client->validatePayment($payload, 'INV123', 50, 'USD');
        expect($result)->toBeTrue();
    });

    it('returns false for payment validation when non-BDT currency details do not match', function () {
        Http::fake([
            'sandbox.sslcommerz.com/validator/api/validationserverAPI.php*' => Http::response([
                'status' => 'VALID',
                'tran_id' => 'INV123',
                'amount' => 1000,
                'currency_type' => 'USD',
                'currency_amount' => 49,
            ], 200),
        ]);

        $payload = ['val_id' => 'val123'];
        $result = $this->client->validatePayment($payload, 'INV123', 50, 'USD');

        expect($result)->toBeFalse();
    });
});

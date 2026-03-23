<?php

declare(strict_types=1);

use HasinHayder\Sslcommerz\Data\RefundResponse;

describe('RefundResponse', function () {
    it('initializes with null data', function () {
        $response = new RefundResponse(null);
        expect($response->toArray())->toBeNull();
    });

    it('initializes with provided data', function () {
        $data = ['status' => 'SUCCESS'];
        $response = new RefundResponse($data);
        expect($response->toArray())->toBe($data);
    });

    describe('status()', function () {
        it('returns status in lowercase', function () {
            $response = new RefundResponse(['status' => 'PROCESSING']);
            expect($response->status())->toBe('processing');
        });

        it('returns null when status is missing', function () {
            $response = new RefundResponse(['other' => 'data']);
            expect($response->status())->toBeNull();
        });
    });

    describe('status checks', function () {
        $cases = [
            'success' => ['status' => 'success', 'method' => 'success'],
            'processing' => ['status' => 'processing', 'method' => 'processing'],
            'failed' => ['status' => 'failed', 'method' => 'failed'],
        ];

        foreach ($cases as $name => $case) {
            it("returns true for {$name} status", function () use ($case) {
                $response = new RefundResponse(['status' => $case['status']]);
                expect($response->{$case['method']}())->toBeTrue();
            });

            it("returns false for {$name} when other status", function () use ($case) {
                $response = new RefundResponse(['status' => 'other']);
                expect($response->{$case['method']}())->toBeFalse();
            });
        }

        it('returns false for all checks when status is missing', function () {
            $response = new RefundResponse(null);
            expect($response->success())->toBeFalse()
                ->and($response->processing())->toBeFalse()
                ->and($response->failed())->toBeFalse();
        });
    });

    describe('failedReason()', function () {
        it('returns error reason', function () {
            $response = new RefundResponse(['errorReason' => 'Insufficient balance']);
            expect($response->failedReason())->toBe('Insufficient balance');
        });

        it('returns null when error reason is missing', function () {
            $response = new RefundResponse(null);
            expect($response->failedReason())->toBeNull();
        });
    });

    describe('ID methods', function () {
        $methods = [
            'bankTranId' => 'bank_tran_id',
            'transId' => 'trans_id',
            'refundRefId' => 'refund_ref_id',
        ];

        foreach ($methods as $method => $key) {
            it("returns {$key} for {$method}()", function () use ($method, $key) {
                $value = "test_{$key}_123";
                $response = new RefundResponse([$key => $value]);
                expect($response->$method())->toBe($value);
            });

            it("returns null when {$key} is missing", function () use ($method) {
                $response = new RefundResponse(null);
                expect($response->$method())->toBeNull();
            });
        }
    });

    it('handles null data for all methods', function () {
        $response = new RefundResponse(null);

        expect($response->status())->toBeNull()
            ->and($response->success())->toBeFalse()
            ->and($response->processing())->toBeFalse()
            ->and($response->failed())->toBeFalse()
            ->and($response->failedReason())->toBeNull()
            ->and($response->bankTranId())->toBeNull()
            ->and($response->transId())->toBeNull()
            ->and($response->refundRefId())->toBeNull();
    });

    it('handles mixed case status', function () {
        $response = new RefundResponse(['status' => 'FaIlEd']);
        expect($response->failed())->toBeTrue();
    });
});

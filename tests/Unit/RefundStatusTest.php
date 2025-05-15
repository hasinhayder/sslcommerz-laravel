<?php

declare(strict_types=1);

use Raziul\Sslcommerz\Data\RefundStatus;

describe('RefundStatus', function () {
    it('initializes with null data', function () {
        $response = new RefundStatus(null);
        expect($response->toArray())->toBeNull();
    });

    it('initializes with provided data', function () {
        $data = ['status' => 'REFUNDED'];
        $response = new RefundStatus($data);
        expect($response->toArray())->toBe($data);
    });

    describe('status()', function () {
        it('returns status in lowercase', function () {
            $response = new RefundStatus(['status' => 'PROCESSING']);
            expect($response->status())->toBe('processing');
        });

        it('returns null when status is missing', function () {
            $response = new RefundStatus(['other' => 'data']);
            expect($response->status())->toBeNull();
        });
    });

    describe('status checks', function () {
        $cases = [
            'refunded' => ['status' => 'refunded', 'method' => 'refunded'],
            'processing' => ['status' => 'processing', 'method' => 'processing'],
            'cancelled' => ['status' => 'cancelled', 'method' => 'cancelled'],
        ];

        foreach ($cases as $name => $case) {
            it("returns true for {$name} status", function () use ($case) {
                $response = new RefundStatus(['status' => $case['status']]);
                expect($response->{$case['method']}())->toBeTrue();
            });

            it("returns false for {$name} when other status", function () use ($case) {
                $response = new RefundStatus(['status' => 'other']);
                expect($response->{$case['method']}())->toBeFalse();
            });
        }

        it('returns false for all checks when status is missing', function () {
            $response = new RefundStatus(null);
            expect($response->refunded())->toBeFalse()
                ->and($response->processing())->toBeFalse()
                ->and($response->cancelled())->toBeFalse();
        });
    });

    describe('reason()', function () {
        it('returns error reason', function () {
            $response = new RefundStatus(['errorReason' => 'Bank rejection']);
            expect($response->reason())->toBe('Bank rejection');
        });

        it('returns null when error reason is missing', function () {
            $response = new RefundStatus(null);
            expect($response->reason())->toBeNull();
        });
    });

    describe('date methods', function () {
        $dates = [
            'initiatedAt' => 'initiated_on',
            'refundedAt' => 'refunded_on',
        ];

        foreach ($dates as $method => $key) {
            it("returns {$key} for {$method}()", function () use ($method, $key) {
                $date = '2024-01-01 12:00:00';
                $response = new RefundStatus([$key => $date]);
                expect($response->$method())->toBe($date);
            });

            it("returns null when {$key} is missing", function () use ($method) {
                $response = new RefundStatus(null);
                expect($response->$method())->toBeNull();
            });
        }
    });

    describe('ID methods', function () {
        $methods = [
            'bankTranId' => 'bank_tran_id',
            'transId' => 'trans_id',
            'refundRefId' => 'refund_ref_id',
        ];

        foreach ($methods as $method => $key) {
            it("returns {$key} for {$method}()", function () use ($method, $key) {
                $value = "test_{$key}_456";
                $response = new RefundStatus([$key => $value]);
                expect($response->$method())->toBe($value);
            });

            it("returns null when {$key} is missing", function () use ($method) {
                $response = new RefundStatus(null);
                expect($response->$method())->toBeNull();
            });
        }
    });

    it('handles null data for all methods', function () {
        $response = new RefundStatus(null);

        expect($response->status())->toBeNull()
            ->and($response->refunded())->toBeFalse()
            ->and($response->processing())->toBeFalse()
            ->and($response->cancelled())->toBeFalse()
            ->and($response->reason())->toBeNull()
            ->and($response->initiatedAt())->toBeNull()
            ->and($response->refundedAt())->toBeNull()
            ->and($response->bankTranId())->toBeNull()
            ->and($response->transId())->toBeNull()
            ->and($response->refundRefId())->toBeNull();
    });

    it('handles mixed case status', function () {
        $response = new RefundStatus(['status' => 'CaNcElLeD']);
        expect($response->cancelled())->toBeTrue();
    });
});

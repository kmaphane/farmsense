<?php

use Carbon\Carbon;
use Domains\Broiler\DTOs\BatchData;
use Domains\Broiler\Enums\BatchStatus;

describe('BatchData DTO', function () {
    it('can be created with required properties', function () {
        $data = new BatchData(
            team_id: 1,
            name: 'Test Batch',
            batch_number: 'BRO-2025-001',
            start_date: Carbon::parse('2025-01-01'),
            expected_end_date: Carbon::parse('2025-02-12'),
            actual_end_date: null,
            status: BatchStatus::Planned,
            initial_quantity: 1000,
            current_quantity: 1000,
            supplier_id: null,
            target_weight_kg: 2.5,
            average_weight_kg: null,
        );

        expect($data->team_id)->toBe(1);
        expect($data->name)->toBe('Test Batch');
        expect($data->batch_number)->toBe('BRO-2025-001');
        expect($data->start_date)->toBeInstanceOf(Carbon::class);
        expect($data->status)->toBe(BatchStatus::Planned);
        expect($data->initial_quantity)->toBe(1000);
        expect($data->current_quantity)->toBe(1000);
    });

    it('can be created from array', function () {
        $data = BatchData::from([
            'team_id' => 1,
            'name' => 'Test Batch',
            'batch_number' => 'BRO-2025-001',
            'start_date' => Carbon::parse('2025-01-01'),
            'expected_end_date' => Carbon::parse('2025-02-12'),
            'actual_end_date' => null,
            'status' => 'planned',
            'initial_quantity' => 1000,
            'current_quantity' => 1000,
            'supplier_id' => null,
            'target_weight_kg' => 2.5,
            'average_weight_kg' => null,
        ]);

        expect($data->team_id)->toBe(1);
        expect($data->name)->toBe('Test Batch');
        expect($data->status)->toBe(BatchStatus::Planned);
    });

    it('can convert to array', function () {
        $data = new BatchData(
            team_id: 1,
            name: 'Test Batch',
            batch_number: 'BRO-2025-001',
            start_date: Carbon::parse('2025-01-01'),
            expected_end_date: Carbon::parse('2025-02-12'),
            actual_end_date: null,
            status: BatchStatus::Planned,
            initial_quantity: 1000,
            current_quantity: 1000,
            supplier_id: null,
            target_weight_kg: 2.5,
            average_weight_kg: null,
        );

        $array = $data->toArray();

        expect($array)->toBeArray();
        expect($array['team_id'])->toBe(1);
        expect($array['name'])->toBe('Test Batch');
        expect($array['batch_number'])->toBe('BRO-2025-001');
    });

    it('accepts nullable properties', function () {
        $data = new BatchData(
            team_id: 1,
            name: 'Test Batch',
            batch_number: 'BRO-2025-001',
            start_date: Carbon::parse('2025-01-01'),
            expected_end_date: null,
            actual_end_date: null,
            status: BatchStatus::Planned,
            initial_quantity: 1000,
            current_quantity: null,
            supplier_id: null,
            target_weight_kg: null,
            average_weight_kg: null,
        );

        expect($data->expected_end_date)->toBeNull();
        expect($data->current_quantity)->toBeNull();
        expect($data->target_weight_kg)->toBeNull();
    });

    it('handles different batch statuses', function () {
        $statuses = [
            BatchStatus::Planned,
            BatchStatus::Active,
            BatchStatus::Harvesting,
            BatchStatus::Closed,
        ];

        foreach ($statuses as $status) {
            $data = new BatchData(
                team_id: 1,
                name: 'Test Batch',
                batch_number: 'BRO-2025-001',
                start_date: Carbon::parse('2025-01-01'),
                expected_end_date: null,
                actual_end_date: null,
                status: $status,
                initial_quantity: 1000,
                current_quantity: 1000,
                supplier_id: null,
                target_weight_kg: null,
                average_weight_kg: null,
            );

            expect($data->status)->toBe($status);
        }
    });

    it('handles decimal weight values', function () {
        $data = new BatchData(
            team_id: 1,
            name: 'Test Batch',
            batch_number: 'BRO-2025-001',
            start_date: Carbon::parse('2025-01-01'),
            expected_end_date: null,
            actual_end_date: null,
            status: BatchStatus::Active,
            initial_quantity: 1000,
            current_quantity: 950,
            supplier_id: null,
            target_weight_kg: 2.45,
            average_weight_kg: 1.87,
        );

        expect($data->target_weight_kg)->toBe(2.45);
        expect($data->average_weight_kg)->toBe(1.87);
    });
});

describe('BatchData validation', function () {
    it('requires team_id to be an integer', function () {
        $data = BatchData::from([
            'team_id' => 1,
            'name' => 'Test Batch',
            'batch_number' => 'BRO-2025-001',
            'start_date' => Carbon::parse('2025-01-01'),
            'status' => 'planned',
            'initial_quantity' => 1000,
        ]);

        expect($data->team_id)->toBeInt();
    });

    it('casts dates from strings to Carbon instances', function () {
        $data = BatchData::from([
            'team_id' => 1,
            'name' => 'Test Batch',
            'batch_number' => 'BRO-2025-001',
            'start_date' => Carbon::parse('2025-01-01'),
            'expected_end_date' => Carbon::parse('2025-02-12'),
            'status' => 'planned',
            'initial_quantity' => 1000,
        ]);

        expect($data->start_date)->toBeInstanceOf(Carbon::class);
        expect($data->expected_end_date)->toBeInstanceOf(Carbon::class);
        expect($data->start_date->format('Y-m-d'))->toBe('2025-01-01');
    });

    it('casts status from string to enum', function () {
        $data = BatchData::from([
            'team_id' => 1,
            'name' => 'Test Batch',
            'batch_number' => 'BRO-2025-001',
            'start_date' => Carbon::parse('2025-01-01'),
            'status' => 'active',
            'initial_quantity' => 1000,
        ]);

        expect($data->status)->toBeInstanceOf(BatchStatus::class);
        expect($data->status)->toBe(BatchStatus::Active);
    });
});

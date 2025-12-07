<?php

use Carbon\Carbon;
use Domains\Broiler\DTOs\DailyLogData;

describe('DailyLogData DTO', function () {
    it('can be created with all properties', function () {
        $data = new DailyLogData(
            team_id: 1,
            batch_id: 1,
            log_date: Carbon::parse('2025-01-15'),
            mortality_count: 3,
            feed_consumed_kg: 150.50,
            water_consumed_liters: 280.75,
            temperature_celsius: 32.5,
            humidity_percent: 65.0,
            ammonia_ppm: 12.5,
            notes: 'Normal day',
            recorded_by: 1,
        );

        expect($data->team_id)->toBe(1);
        expect($data->batch_id)->toBe(1);
        expect($data->log_date)->toBeInstanceOf(Carbon::class);
        expect($data->mortality_count)->toBe(3);
        expect($data->feed_consumed_kg)->toBe(150.50);
        expect($data->water_consumed_liters)->toBe(280.75);
        expect($data->temperature_celsius)->toBe(32.5);
        expect($data->humidity_percent)->toBe(65.0);
        expect($data->ammonia_ppm)->toBe(12.5);
        expect($data->notes)->toBe('Normal day');
        expect($data->recorded_by)->toBe(1);
    });

    it('can be created from array', function () {
        $data = DailyLogData::from([
            'team_id' => 1,
            'batch_id' => 1,
            'log_date' => Carbon::parse('2025-01-15'),
            'mortality_count' => 3,
            'feed_consumed_kg' => 150.50,
            'water_consumed_liters' => 280.75,
            'temperature_celsius' => 32.5,
            'humidity_percent' => 65.0,
            'ammonia_ppm' => 12.5,
            'notes' => 'Normal day',
            'recorded_by' => 1,
        ]);

        expect($data->team_id)->toBe(1);
        expect($data->batch_id)->toBe(1);
        expect($data->mortality_count)->toBe(3);
    });

    it('can convert to array', function () {
        $data = new DailyLogData(
            team_id: 1,
            batch_id: 1,
            log_date: Carbon::parse('2025-01-15'),
            mortality_count: 3,
            feed_consumed_kg: 150.50,
            water_consumed_liters: null,
            temperature_celsius: null,
            humidity_percent: null,
            ammonia_ppm: null,
            notes: null,
            recorded_by: 1,
        );

        $array = $data->toArray();

        expect($array)->toBeArray();
        expect($array['team_id'])->toBe(1);
        expect($array['batch_id'])->toBe(1);
        expect($array['mortality_count'])->toBe(3);
        expect($array['feed_consumed_kg'])->toBe(150.50);
    });

    it('accepts nullable optional properties', function () {
        $data = new DailyLogData(
            team_id: 1,
            batch_id: 1,
            log_date: Carbon::parse('2025-01-15'),
            mortality_count: 0,
            feed_consumed_kg: 150.0,
            water_consumed_liters: null,
            temperature_celsius: null,
            humidity_percent: null,
            ammonia_ppm: null,
            notes: null,
            recorded_by: 1,
        );

        expect($data->water_consumed_liters)->toBeNull();
        expect($data->temperature_celsius)->toBeNull();
        expect($data->humidity_percent)->toBeNull();
        expect($data->ammonia_ppm)->toBeNull();
        expect($data->notes)->toBeNull();
    });

    it('handles zero mortality count', function () {
        $data = new DailyLogData(
            team_id: 1,
            batch_id: 1,
            log_date: Carbon::parse('2025-01-15'),
            mortality_count: 0,
            feed_consumed_kg: 150.0,
            water_consumed_liters: null,
            temperature_celsius: null,
            humidity_percent: null,
            ammonia_ppm: null,
            notes: null,
            recorded_by: 1,
        );

        expect($data->mortality_count)->toBe(0);
    });

    it('handles decimal precision for measurements', function () {
        $data = new DailyLogData(
            team_id: 1,
            batch_id: 1,
            log_date: Carbon::parse('2025-01-15'),
            mortality_count: 0,
            feed_consumed_kg: 152.75,
            water_consumed_liters: 287.33,
            temperature_celsius: 31.8,
            humidity_percent: 67.5,
            ammonia_ppm: 15.3,
            notes: null,
            recorded_by: 1,
        );

        expect($data->feed_consumed_kg)->toBe(152.75);
        expect($data->water_consumed_liters)->toBe(287.33);
        expect($data->temperature_celsius)->toBe(31.8);
        expect($data->humidity_percent)->toBe(67.5);
        expect($data->ammonia_ppm)->toBe(15.3);
    });
});

describe('DailyLogData validation', function () {
    it('casts log_date from string to Carbon', function () {
        $data = DailyLogData::from([
            'team_id' => 1,
            'batch_id' => 1,
            'log_date' => Carbon::parse('2025-01-15'),
            'mortality_count' => 3,
            'feed_consumed_kg' => 150.0,
            'recorded_by' => 1,
        ]);

        expect($data->log_date)->toBeInstanceOf(Carbon::class);
        expect($data->log_date->format('Y-m-d'))->toBe('2025-01-15');
    });

    it('requires team_id to be an integer', function () {
        $data = DailyLogData::from([
            'team_id' => 1,
            'batch_id' => 1,
            'log_date' => Carbon::parse('2025-01-15'),
            'mortality_count' => 3,
            'feed_consumed_kg' => 150.0,
            'recorded_by' => 1,
        ]);

        expect($data->team_id)->toBeInt();
    });

    it('requires batch_id to be an integer', function () {
        $data = DailyLogData::from([
            'team_id' => 1,
            'batch_id' => 5,
            'log_date' => Carbon::parse('2025-01-15'),
            'mortality_count' => 3,
            'feed_consumed_kg' => 150.0,
            'recorded_by' => 1,
        ]);

        expect($data->batch_id)->toBeInt();
        expect($data->batch_id)->toBe(5);
    });

    it('requires recorded_by to be an integer', function () {
        $data = DailyLogData::from([
            'team_id' => 1,
            'batch_id' => 1,
            'log_date' => Carbon::parse('2025-01-15'),
            'mortality_count' => 3,
            'feed_consumed_kg' => 150.0,
            'recorded_by' => 42,
        ]);

        expect($data->recorded_by)->toBeInt();
        expect($data->recorded_by)->toBe(42);
    });
});

describe('DailyLogData with edge cases', function () {
    it('handles high mortality counts', function () {
        $data = new DailyLogData(
            team_id: 1,
            batch_id: 1,
            log_date: Carbon::parse('2025-01-15'),
            mortality_count: 100,
            feed_consumed_kg: 150.0,
            water_consumed_liters: null,
            temperature_celsius: null,
            humidity_percent: null,
            ammonia_ppm: null,
            notes: 'Disease outbreak',
            recorded_by: 1,
        );

        expect($data->mortality_count)->toBe(100);
        expect($data->notes)->toBe('Disease outbreak');
    });

    it('handles very large feed consumption values', function () {
        $data = new DailyLogData(
            team_id: 1,
            batch_id: 1,
            log_date: Carbon::parse('2025-01-15'),
            mortality_count: 0,
            feed_consumed_kg: 9999.99,
            water_consumed_liters: 15000.0,
            temperature_celsius: null,
            humidity_percent: null,
            ammonia_ppm: null,
            notes: null,
            recorded_by: 1,
        );

        expect($data->feed_consumed_kg)->toBe(9999.99);
        expect($data->water_consumed_liters)->toBe(15000.0);
    });

    it('handles extreme temperature values', function () {
        $data = new DailyLogData(
            team_id: 1,
            batch_id: 1,
            log_date: Carbon::parse('2025-01-15'),
            mortality_count: 0,
            feed_consumed_kg: 150.0,
            water_consumed_liters: null,
            temperature_celsius: 45.5, // Very hot day
            humidity_percent: 95.0, // Very humid
            ammonia_ppm: null,
            notes: 'Heat wave',
            recorded_by: 1,
        );

        expect($data->temperature_celsius)->toBe(45.5);
        expect($data->humidity_percent)->toBe(95.0);
    });
});

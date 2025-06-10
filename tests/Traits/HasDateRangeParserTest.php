<?php

use Carbon\Carbon;
use ZephyrIt\Shared\Tests\Stubs\DateRangeParserStub;

test('parses valid string date range', function () {
    $stub = new DateRangeParserStub;
    [$start, $end] = $stub->testParseDateRange('2024-01-01 - 2024-01-31');

    expect($start)->toBeInstanceOf(Carbon::class)
        ->and($start->toDateString())->toBe('2024-01-01')
        ->and($end->toDateString())->toBe('2024-01-31');
});

test('throws exception for invalid date filter', function () {
    $stub = new DateRangeParserStub;

    expect(fn () => $stub->testParseDateRange('invalid'))
        ->toThrow(InvalidArgumentException::class);
});

test('throws exception for unsupported date format', function () {
    $stub = new DateRangeParserStub;

    expect(fn () => $stub->testParseDateRange('2024/01/01 - 2024/01/02'))
        ->toThrow(InvalidArgumentException::class);
});

test('parses carbon instances and returns strings', function () {
    $stub = new DateRangeParserStub;
    $start = Carbon::create(2024, 2, 1);
    $end = Carbon::create(2024, 2, 10);

    [$from, $to] = $stub->testParseDateRangeAsStrings([$start, $end]);

    expect($from)->toBe('2024-02-01')
        ->and($to)->toBe('2024-02-10');
});

test('parses custom format string output', function () {
    $stub = new DateRangeParserStub;

    [$start, $end] = $stub->testParseDateRangeAsStrings('01/03/2024 - 05/03/2024', 'd/m/Y');

    expect($start)->toBe('01/03/2024')
        ->and($end)->toBe('05/03/2024');
});

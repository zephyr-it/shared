<?php

use Carbon\Carbon;
use InvalidArgumentException;
use ZephyrIt\Shared\Traits\HasDateRangeParser;
use ZephyrIt\Shared\Tests\TestCase;

class DateRangeParserStub
{
    use HasDateRangeParser;
}

test('parses valid string date range', function () {
    $stub = new DateRangeParserStub();
    [$start, $end] = $stub->parseDateRange('2024-01-01 - 2024-01-31');

    expect($start)->toBeInstanceOf(Carbon::class)
        ->and($start->toDateString())->toBe('2024-01-01')
        ->and($end->toDateString())->toBe('2024-01-31');
});

test('throws exception for invalid date filter', function () {
    $stub = new DateRangeParserStub();

    expect(fn () => $stub->parseDateRange('invalid'))
        ->toThrow(InvalidArgumentException::class);
});

test('throws exception for unsupported date format', function () {
    $stub = new DateRangeParserStub();

    expect(fn () => $stub->parseDateRange('2024/01/01 - 2024/01/02'))
        ->toThrow(InvalidArgumentException::class);
});

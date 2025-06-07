<?php

use ZephyrIt\Shared\Helpers\ArrayHelpers;
use ZephyrIt\Shared\Helpers\StringHelpers;
use ZephyrIt\Shared\Helpers\FormatHelpers;

it('flattens nested arrays using dot notation', function () {
    $input = ['a' => ['b' => 1, 'c' => ['d' => 2]]];
    $expected = ['a.b' => 1, 'a.c.d' => 2];

    expect(ArrayHelpers::flattenArray($input))->toBe($expected);
});

it('normalizes strings safely', function () {
    expect(StringHelpers::normalizeString(null))->toBe('');
    expect(StringHelpers::normalizeString(' Foo Bar '))->toBe('foobar');
});

it('formats numbers into Indian format', function () {
    expect(FormatHelpers::numberToIndianFormat(1234567))->toBe('12,34,567');
});

it('formats negative and decimal numbers into Indian format', function () {
    expect(FormatHelpers::numberToIndianFormat(-1234567))->toBe('-12,34,567');
    expect(FormatHelpers::numberToIndianFormat(1234567.89))->toBe('12,34,567.89');
});

it('shortens numbers near unit boundaries', function () {
    expect(FormatHelpers::formatNumberShort(999))->toBe('999');
    expect(FormatHelpers::formatNumberShort(1000))->toBe('1K');
    expect(FormatHelpers::formatNumberShort(1500000))->toBe('1.5M');
});

it('sanitizes and formats strings correctly', function () {
    expect(StringHelpers::sanitizeAndFormat('  jOhN DOE  '))->toBe('John Doe');
    expect(StringHelpers::sanitizeAndFormat('  Mixed   CASE  ', true))->toBe('mixed case');
    expect(StringHelpers::sanitizeAndFormat('  Multi   Words ', false, true))->toBe('MultiWords');
});

it('removes special characters from strings', function () {
    expect(StringHelpers::sanitizeSpecialCharacters('Hello@World#'))->toBe('Hello World');
    expect(StringHelpers::sanitizeSpecialCharacters('A-B=C', '-'))->toBe('A-B C');
    expect(StringHelpers::sanitizeSpecialCharacters(null))->toBeNull();
});

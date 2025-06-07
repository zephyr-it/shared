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

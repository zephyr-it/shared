<?php

use ZephyrIt\Shared\Helpers\FormatHelpers;

it('generates add button label with or without prefix', function () {
    expect(FormatHelpers::formatAddButtonLabel())
        ->toBe(__('shared::labels.add'));
    expect(FormatHelpers::formatAddButtonLabel('User'))
        ->toBe(__('shared::labels.add')." User");
});

it('generates copy message with or without prefix', function () {
    expect(FormatHelpers::formatCopyMessage(null))
        ->toBe(__('shared::messages.copied'));
    expect(FormatHelpers::formatCopyMessage('User ID'))
        ->toBe('User ID '.__('shared::messages.copied'));
});

it('converts numbers to words using number formatter', function () {
    expect(FormatHelpers::numberToWord(5))->toBe('Five');
    expect(FormatHelpers::numberToWord(2, 'ordinal'))->toBe('2nd');
});

it('formats numbers to currency and short style', function () {
    expect(FormatHelpers::numberToWord(1500, 'currency', 'en', 'USD'))
        ->toBe('$1,500.00');
    expect(FormatHelpers::numberToWord(1500000, 'short'))
        ->toBe('1.5M');
});

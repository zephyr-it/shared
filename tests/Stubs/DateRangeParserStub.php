<?php

namespace ZephyrIt\Shared\Tests\Stubs;

use ZephyrIt\Shared\Traits\HasDateRangeParser;

class DateRangeParserStub
{
    use HasDateRangeParser;

    public function testParseDateRange(string | array $input): array
    {
        return $this->parseDateRange($input);
    }

    public function testParseDateRangeAsStrings(string | array $input, string $format = 'Y-m-d'): array
    {
        return $this->parseDateRangeAsStrings($input, $format);
    }
}

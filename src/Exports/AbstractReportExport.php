<?php

declare(strict_types=1);

namespace ZephyrIt\Shared\Exports;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use ZephyrIt\Shared\Traits\HasDateRangeParser;

abstract class AbstractReportExport implements WithMultipleSheets
{
    use HasDateRangeParser;

    protected Model $entity;

    protected Carbon $startDate;

    protected Carbon $endDate;

    protected ?string $extraAttribute;

    public function __construct(Model $entity, string | array $dateFilter, ?string $extraAttribute = null)
    {
        $this->entity = $entity;
        [$this->startDate, $this->endDate] = $this->parseDateRange($dateFilter);
        $this->extraAttribute = $extraAttribute;
    }

    /**
     * Required by WithMultipleSheets.
     */
    abstract public function sheets(): array;
}

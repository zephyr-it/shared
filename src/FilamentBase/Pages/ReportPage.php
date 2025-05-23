<?php

namespace ZephyrIt\Shared\FilamentBase\Pages;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;
use ZephyrIt\Shared\Traits\HasDateRangeParser;

abstract class ReportPage extends BaseDashboard
{
    use HasDateRangeParser;
    use HasFiltersForm;
    use HasPageShield;

    protected static string $titleKey = 'navigations.labels.report';

    protected static ?int $navigationSort = 100;

    public static function getNavigationSort(): ?int
    {
        return static::$navigationSort;
    }

    /**
     * Default group name for sidebar.
     */
    public static function getNavigationGroup(): string
    {
        return __('navigations.groups.reports');
    }

    /**
     * Provide a default title if child class doesn't override.
     */
    public function getTitle(): string
    {
        return __(static::$titleKey);
    }

    /**
     * Use same label as title by default.
     */
    public static function getNavigationLabel(): string
    {
        return __(static::$titleKey);
    }

    /**
     * Final filters form method — always shows date first, merges others.
     */
    public function filtersForm(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()
                ->schema(array_merge(
                    $this->getDefaultFilterFormSchema(),
                    $this->getCustomFilterFormSchema()
                ))
                ->columns(4),
        ]);
    }

    public function persistsFiltersInSession(): bool
    {
        return false;
    }

    /**
     * Always-present date filter.
     */
    protected function getDefaultFilterFormSchema(): array
    {
        return [
            DateRangePicker::make('dateRange')
                ->label(__('labels.date_range'))
                ->defaultToday(),
        ];
    }

    /**
     * Optional filters defined in child class (if exists).
     */
    protected function getCustomFilterFormSchema(): array
    {
        if (method_exists($this, 'getFilterFormSchema')) {
            return $this->getFilterFormSchema();
        }

        return [];
    }
}

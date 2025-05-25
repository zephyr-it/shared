<?php

declare(strict_types=1);

namespace ZephyrIt\Shared\Traits;

use Exception;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;
use ZephyrIt\Shared\FilamentBase\Components\ResetAction;

trait DashboardFiltersTrait
{
    use HasDateRangeParser;
    use HasFiltersForm;

    /**
     * Define the filters form for the dashboard.
     * Includes a customizable section that supports overriding in consuming classes.
     */
    public function filtersForm(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()
                ->heading(fn () => $this->getCustomSubheading()) // Dynamic subheading based on filter state
                ->schema(array_filter([
                    $this->getDateRangeFilter(),       // Default date range filter
                    ...$this->getAdditionalFilters(),  // Optional filters defined in subclass/implementer
                ]))
                ->collapsed()
                ->collapsible()
                ->compact()
                ->columns(3)
                ->headerActions([
                    $this->getResetAction(), // Customizable reset action
                ]),
        ]);
    }

    /**
     * Provides the default date range picker.
     * Can be replaced or extended in the child trait/implementation.
     */
    protected function getDateRangeFilter(): ?Forms\Components\Component
    {
        return DateRangePicker::make('dateRange')
            ->label(__('shared::labels.date_range'))
            ->defaultToday();
    }

    /**
     * Extendable method to inject additional filters into the form.
     * Child traits/classes should override to provide more filters.
     */
    protected function getAdditionalFilters(): array
    {
        return [];
    }

    /**
     * Reset action for the filter form.
     * Resets to today’s date range by default. Extend if needed.
     */
    protected function getResetAction(): Forms\Components\Actions\Action
    {
        $today = now()->format('d/m/Y');

        return ResetAction::make()
            ->extraParams([
                'dateRange' => "{$today} - {$today}",
            ]);
    }

    /**
     * Builds the subheading text for the filter section.
     * Parses the date range into a readable string.
     */
    protected function getCustomSubheading(): ?string
    {
        $dateRange = $this->filters['dateRange'] ?? null;

        try {
            [$startDate, $endDate] = $this->parseDateRange($dateRange); // Uses HasDateRangeParser
        } catch (Exception) {
            // Fallback to default 30-day range
            $startDate = now()->subDays(30)->startOfDay();
            $endDate = now()->endOfDay();
        }

        return __('shared::dashboard.sub_heading.date_range', [
            'start_date' => $startDate->toFormattedDateString(),
            'end_date' => $endDate->toFormattedDateString(),
        ]);
    }
}

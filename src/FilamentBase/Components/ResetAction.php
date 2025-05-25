<?php

declare(strict_types=1);

namespace ZephyrIt\Shared\FilamentBase\Components;

use Filament\Forms\Components\Actions\Action;

class ResetAction extends Action
{
    protected array $extraParams = [];

    /**
     * Define the default name for the action.
     */
    public static function getDefaultName(): ?string
    {
        return 'reset';
    }

    /**
     * Set up the reset action with icon, color, and behavior.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->icon('ri-refresh-line')
            ->hiddenLabel()
            ->color('danger')
            ->action(function ($livewire) {
                $this->resetFilters($livewire);
            });
    }

    /**
     * Set additional parameters to apply when resetting filters.
     */
    public function extraParams(array $params): static
    {
        $this->extraParams = $params;

        return $this;
    }

    /**
     * Reset filters or form data based on the page type.
     */
    protected function resetFilters($livewire): void
    {
        $livewire->filters['startDate'] = now()->startOfMonth()->format('Y-m-d');
        $livewire->filters['endDate'] = now()->endOfMonth()->format('Y-m-d');

        foreach ($this->extraParams as $key => $value) {
            $livewire->filters[$key] = $value;
        }

        $this->storeFiltersInSession($livewire);
    }

    /**
     * Store filters in the session for Dashboard persistence.
     */
    protected function storeFiltersInSession($livewire): void
    {
        session()->put($livewire->getFiltersSessionKey(), $livewire->filters);
    }
}

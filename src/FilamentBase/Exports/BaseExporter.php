<?php

declare(strict_types=1);

namespace ZephyrIt\Shared\FilamentBase\Exports;

use Filament\Actions\Exports\Exporter;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\CellVerticalAlignment;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;

abstract class BaseExporter extends Exporter
{
    /**
     * Default cell style for XLSX export body.
     */
    public function getXlsxCellStyle(): ?Style
    {
        return (new Style)
            ->setFontSize(12)
            ->setFontName('Consolas')
            ->setCellAlignment(CellAlignment::LEFT)
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER);
    }

    /**
     * Default header row style for XLSX export.
     */
    public function getXlsxHeaderCellStyle(): ?Style
    {
        return (new Style)
            ->setFontBold()
            ->setFontItalic()
            ->setFontSize(14)
            ->setFontName('Consolas')
            ->setFontColor(Color::rgb(255, 255, 255))
            ->setBackgroundColor(Color::rgb(0, 51, 153))
            ->setCellAlignment(CellAlignment::CENTER)
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER);
    }
}

<?php

declare(strict_types=1);

namespace ZephyrIt\Shared\Traits;

trait ColorPaletteTrait
{
    /**
     * Returns a color palette for chart elements, generating additional colors if needed.
     */
    public function getColors($count): array
    {
        $predefinedColors = [
            'rgba(255, 99, 132, 0.7)',    // Red
            'rgba(54, 162, 235, 0.7)',    // Blue
            'rgba(255, 206, 86, 0.7)',    // Yellow
            'rgba(75, 192, 192, 0.7)',    // Teal
            'rgba(153, 102, 255, 0.7)',   // Purple
            'rgba(255, 159, 64, 0.7)',    // Orange
            'rgba(34, 197, 94, 0.7)',     // Green
            'rgba(255, 99, 71, 0.7)',     // Tomato
            'rgba(139, 69, 19, 0.7)',     // SaddleBrown
            'rgba(255, 140, 0, 0.7)',     // DarkOrange
            'rgba(47, 79, 79, 0.7)',      // DarkSlateGray
            'rgba(64, 224, 208, 0.7)',    // Turquoise
        ];

        $backgroundColor = [];
        $borderColor = [];

        for ($i = 0; $i < $count; $i++) {
            if (isset($predefinedColors[$i])) {
                $backgroundColor[] = $predefinedColors[$i];
                $borderColor[] = str_replace('0.7', '1', $predefinedColors[$i]);
            } else {
                $randomColor = $this->generateRandomColor();
                $backgroundColor[] = $randomColor['background'];
                $borderColor[] = $randomColor['border'];
            }
        }

        return [
            'backgroundColor' => $backgroundColor,
            'borderColor' => $borderColor,
        ];
    }

    /**
     * Generates a random color for both background and border.
     */
    protected function generateRandomColor(): array
    {
        $r = rand(0, 255);
        $g = rand(0, 255);
        $b = rand(0, 255);

        return [
            'background' => "rgba({$r}, {$g}, {$b}, 0.7)",
            'border' => "rgba({$r}, {$g}, {$b}, 1)",
        ];
    }
}

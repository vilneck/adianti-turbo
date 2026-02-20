<?php
/**
 * BarChartWrapper
 *
 * amCharts bar chart wrapper for Adianti
 */
class BarChartWrapper extends ChartWrapper
{
    protected string $categoryField = 'category';
    protected array $series = [];
    protected bool $zoomEnabled = false;

    public function setCategoryField(string $field): void
    {
        $this->categoryField = $field;
    }

    public function addSeries(string $name, string $valueField): void
    {
        $this->series[] = [
            'name' => $name,
            'valueField' => $valueField,
        ];
    }

    public function enableZoom(): void
    {
        $this->zoomEnabled = true;
    }

    public function disableZoom(): void
    {
        $this->zoomEnabled = false;
    }

    protected function getChartType(): string
    {
        return 'bar';
    }

    protected function getSpecificConfig(): array
    {
        return [
            'categoryField' => $this->categoryField,
            'series' => $this->series,
            'zoomEnabled' => $this->zoomEnabled,
        ];
    }
}

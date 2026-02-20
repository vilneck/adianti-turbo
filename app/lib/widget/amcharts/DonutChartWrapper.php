<?php
/**
 * DonutChartWrapper
 *
 * amCharts donut chart wrapper for Adianti
 */
class DonutChartWrapper extends ChartWrapper
{
    protected string $categoryField = 'category';
    protected string $valueField = 'value';
    protected int $innerRadius = 60;

    public function setCategoryField(string $field): void
    {
        $this->categoryField = $field;
    }

    public function setValueField(string $field): void
    {
        $this->valueField = $field;
    }

    public function setInnerRadius(int $innerRadius): void
    {
        $this->innerRadius = $innerRadius;
    }

    protected function getChartType(): string
    {
        return 'donut';
    }

    protected function getSpecificConfig(): array
    {
        return [
            'categoryField' => $this->categoryField,
            'valueField' => $this->valueField,
            'innerRadius' => $this->innerRadius,
        ];
    }
}

<?php
/**
 * ChartWrapper
 *
 * Base wrapper for amCharts in Adianti
 */
abstract class ChartWrapper
{
    protected string $id;
    protected string $title;
    protected string $width;
    protected string $height;
    protected array $data;

    public function __construct(string $title = '')
    {
        $this->id = 'amchart_' . uniqid();
        $this->title = $title;
        $this->width = '100%';
        $this->height = '340px';
        $this->data = [];
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function setWidth(string $width): void
    {
        $this->width = $width;
    }

    public function setHeight(string $height): void
    {
        $this->height = $height;
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }

    abstract protected function getChartType(): string;

    abstract protected function getSpecificConfig(): array;

    protected function buildConfig(): array
    {
        return array_merge(
            [
                'type' => $this->getChartType(),
                'title' => $this->title,
                'data' => $this->data,
            ],
            $this->getSpecificConfig()
        );
    }

    public function show(): void
    {
        $config = json_encode($this->buildConfig(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $template = new THtmlRenderer('app/resources/amcharts_chart_wrapper.html');
        $template->enableSection(
            'main',
            [
                'chart_id' => $this->id,
                'width' => $this->width,
                'height' => $this->height,
                'config' => $config,
            ]
        );
        $template->show();
    }
}

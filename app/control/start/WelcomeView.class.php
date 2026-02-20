<?php
/**
 * WelcomeView
 *
 * @version    1.0
 * @package    samples
 * @subpackage tutor
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006-2012 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    https://adiantiframework.com.br/license
 */
class WelcomeView extends TPage
{
    private $html;
    
    /**
     * Class constructor
     * Creates the page
     */
    function __construct()
    {
        parent::__construct();

        $lineChart = new LineChartWrapper('Vendas Mensais (Linha)');
        $lineChart->setHeight('320px');
        $lineChart->setCategoryField('mes');
        $lineChart->setData([
            ['mes' => 'Jan', 'meta' => 120, 'realizado' => 100],
            ['mes' => 'Fev', 'meta' => 135, 'realizado' => 128],
            ['mes' => 'Mar', 'meta' => 145, 'realizado' => 139],
            ['mes' => 'Abr', 'meta' => 155, 'realizado' => 149],
            ['mes' => 'Mai', 'meta' => 165, 'realizado' => 171],
            ['mes' => 'Jun', 'meta' => 175, 'realizado' => 182],
        ]);
        $lineChart->addSeries('Meta', 'meta');
        $lineChart->addSeries('Realizado', 'realizado');
        $lineChart->enableZoom();

        $barChart = new BarChartWrapper('Faturamento por Categoria (Barra)');
        $barChart->setHeight('320px');
        $barChart->setCategoryField('categoria');
        $barChart->setData([
            ['categoria' => 'Serviços', 'valor' => 420],
            ['categoria' => 'Produtos', 'valor' => 310],
            ['categoria' => 'Assinaturas', 'valor' => 270],
            ['categoria' => 'Treinamentos', 'valor' => 190],
        ]);
        $barChart->addSeries('Faturamento', 'valor');
        $barChart->enableZoom();

        $donutChart = new DonutChartWrapper('Participação por Canal (Donut)');
        $donutChart->setHeight('320px');
        $donutChart->setCategoryField('canal');
        $donutChart->setValueField('valor');
        $donutChart->setData([
            ['canal' => 'Web', 'valor' => 48],
            ['canal' => 'Parceiros', 'valor' => 27],
            ['canal' => 'Loja', 'valor' => 15],
            ['canal' => 'Outros', 'valor' => 10],
        ]);

        $this->html = new THtmlRenderer('app/resources/amcharts_welcome.html');
        $replace = [
            'line_chart' => $lineChart,
            'bar_chart' => $barChart,
            'donut_chart' => $donutChart,
        ];
        $this->html->enableSection('main', $replace);
        
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->html);
        
        // add the build to the page
        parent::add($container);
    }
}

<?php

class DragDropView extends TPage
{
    private $form;

    public function __construct()
    {
        parent::__construct();

        $this->form = new BootstrapFormBuilder('form_drag_drop');
        $this->form->setFormTitle('CustomDragDropField');

        $dragDropField = new CustomDragDropField('columns_state');
        $dragDropField->setSize('420px');
        $dragDropField->addItems([
            ['id' => 1, 'name' => 'ID da Ordem de Servico', 'visible' => true],
            ['id' => 2, 'name' => 'Status', 'visible' => true],
            ['id' => 3, 'name' => 'Cliente', 'visible' => true],
            ['id' => 4, 'name' => 'Produto', 'visible' => true],
            ['id' => 5, 'name' => 'IMEI 1', 'visible' => true],
        ]);

        $info = new TLabel('Arraste para reordenar e use o toggle para controlar a visibilidade. O estado atual fica salvo em um hidden no proprio componente.');
        $info->style = 'display:block; margin-bottom:10px; color:#4b5563;';

        $this->form->addContent([$info]);
        $this->form->addFields([new TLabel('Campos')], [$dragDropField]);
        $this->form->addAction('Salvar estado', new TAction([$this, 'onSave']), 'fa:save green');

        $container = new TVBox;
        $container->style = 'width:100%';
        //$container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);

        parent::add($container);
    }

    public function onSave($param)
    {
        $data = $this->form->getData();
        $this->form->setData($data);

        $decoded = json_decode($data->columns_state ?? '[]', true);
        $content = json_last_error() === JSON_ERROR_NONE
            ? json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            : (string) ($data->columns_state ?? '');

        $content = htmlspecialchars($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        new TMessage('info', "<pre style='margin:0; text-align:left'>{$content}</pre>");
    }
}

<?php

use Adianti\Control\TPage;
use Adianti\Widget\Base\TElement;
use Adianti\Widget\Base\TScript;
use Adianti\Widget\Form\AdiantiWidgetInterface;
use Adianti\Widget\Form\TField;

class CustomDragDropField extends TField implements AdiantiWidgetInterface
{
    protected $id;
    protected $items;
    protected $width;
    protected $height;
    protected $listId;
    protected $hiddenId;
    protected $activeItemBackgroundColor;
    protected $inactiveItemBackgroundColor;

    public function __construct($name)
    {
        parent::__construct($name);

        $this->id = 'customdragdropfield_' . mt_rand(1000000000, 1999999999);
        $this->listId = $this->id . '_list';
        $this->hiddenId = $this->id . '_hidden';
        $this->items = [];
        $this->width = '100%';
        $this->height = null;
        $this->activeItemBackgroundColor = null;
        $this->inactiveItemBackgroundColor = null;

        $this->tag = new TElement('div');
        $this->tag->{'class'} = 'custom-dragdrop-field';
        $this->tag->{'widget'} = 'customdragdropfield';
    }

    public function setSize($width, $height = null)
    {
        $this->width = $width;
        $this->height = $height;
    }

    public function addItems($items)
    {
        $this->items = $this->normalizeItems($items);
    }

    public function setItems($items)
    {
        $this->addItems($items);
    }

    public function getItems()
    {
        return $this->items;
    }

    public function setActiveItemBackgroundColor($color)
    {
        $this->activeItemBackgroundColor = $color;
    }

    public function setInactiveItemBackgroundColor($color)
    {
        $this->inactiveItemBackgroundColor = $color;
    }

    public function setValue($value)
    {
        if (is_string($value))
        {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded))
            {
                $value = $decoded;
            }
            else
            {
                parent::setValue($value);
                return;
            }
        }

        if (is_array($value))
        {
            $this->items = $this->normalizeItems($value);
            parent::setValue($this->encodeItems($this->items));
            return;
        }

        parent::setValue($value);
    }

    public function getPostData()
    {
        $name = str_replace(['[', ']'], ['', ''], $this->name);

        if (isset($_POST[$name]))
        {
            return $_POST[$name];
        }

        return '';
    }

    public function show()
    {
        TPage::include_css('app/resources/customdragdropfield.css');
        TPage::include_js('https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js');
        TPage::include_js('app/resources/customdragdropfield.js');

        $items = $this->items;

        if (!empty($this->value))
        {
            $decoded = json_decode($this->value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded))
            {
                $items = $this->normalizeItems($decoded);
            }
        }

        $value = $this->encodeItems($items);

        $wrapper = new TElement('div');
        $wrapper->{'id'} = $this->id;
        $wrapper->{'class'} = 'custom-dragdrop-field';
        $wrapper->{'widget'} = 'customdragdropfield';
        $wrapper->{'data-name'} = $this->name;
        $wrapper->{'data-active-item-background-color'} = $this->activeItemBackgroundColor;
        $wrapper->{'data-inactive-item-background-color'} = $this->inactiveItemBackgroundColor;

        if (!parent::getEditable())
        {
            $wrapper->{'class'} .= ' disabled';
        }

        $style = [];

        if (!empty($this->width))
        {
            $style[] = 'width:' . $this->formatSize($this->width);
        }

        if (!empty($this->height))
        {
            $style[] = 'min-height:' . $this->formatSize($this->height);
        }

        if ($style)
        {
            $wrapper->{'style'} = implode(';', $style) . ';';
        }

        $list = new TElement('div');
        $list->{'id'} = $this->listId;
        $list->{'class'} = 'custom-dragdrop-field-list';

        $hidden = new TElement('input');
        $hidden->{'id'} = $this->hiddenId;
        $hidden->{'type'} = 'hidden';
        $hidden->{'name'} = $this->name;
        $hidden->{'value'} = $value;

        $wrapper->add($list);
        $wrapper->add($hidden);
        $wrapper->show();

        $config = [
            'id' => $this->id,
            'list_id' => $this->listId,
            'hidden_id' => $this->hiddenId,
            'items' => $value,
            'editable' => parent::getEditable(),
            'active_item_background_color' => $this->activeItemBackgroundColor,
            'inactive_item_background_color' => $this->inactiveItemBackgroundColor,
            'sortable_url' => 'https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js',
        ];

        $configJson = json_encode($config, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        TScript::create("customdragdropfield_start({$configJson});");
    }

    protected function normalizeItems($items)
    {
        $normalized = [];

        if (!is_array($items))
        {
            return $normalized;
        }

        foreach ($items as $key => $item)
        {
            if (is_array($item))
            {
                $id = $item['id'] ?? $item['value'] ?? $key;
                $name = $item['name'] ?? $item['label'] ?? $item['title'] ?? $id;
                $visible = $item['visible'] ?? true;
            }
            else
            {
                $id = $key;
                $name = $item;
                $visible = true;
            }

            $normalized[] = [
                'id' => (string) $id,
                'name' => (string) $name,
                'visible' => filter_var($visible, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (bool) $visible,
            ];
        }

        return $normalized;
    }

    protected function encodeItems($items)
    {
        return json_encode($items, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    protected function formatSize($size)
    {
        if (is_numeric($size))
        {
            return $size . 'px';
        }

        return $size;
    }
}

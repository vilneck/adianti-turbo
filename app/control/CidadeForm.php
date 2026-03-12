<?php
class CidadeForm extends TPage
{
    protected $form;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->form = new BootstrapFormBuilder('form_Cidade');
        $this->form->setFormTitle('Cidade');
        
        $id = new TEntry('id');
        $nome = new TEntry('nome');
        
        $id->setEditable(FALSE);
        $nome->addValidation('Nome', new TRequiredValidator);
        
        $this->form->addFields([new TLabel('ID')], [$id], [new TLabel('Nome')], [$nome]);
        $this->form->setData(new stdClass);
        
        $btn = $this->form->addAction('Salvar', new TAction([$this, 'onSave']), 'far:save #fff');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addAction('Limpar', new TAction([$this, 'onClear']), 'fa:eraser red');
        
        parent::add($this->form);
    }
    
    public function onSave($param)
    {
        try {
            TTransaction::open('sample');
            
            $this->form->validate();
            $data = $this->form->getData();
            
            $object = new Cidade;
            $object->fromArray((array) $data);
            $object->store();
            
            $data->id = $object->id;
            $this->form->setData($data);
            
            TTransaction::close();
            new TMessage('info', 'Registro salvo');
            TScript::create("__adianti_load_page('index.php?class=CidadeList');");
        } catch (Exception $e) {
            $this->form->setData($this->form->getData());
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    public function onEdit($param)
    {
        try {
            $key = $param['key'] ?? $param['id'] ?? NULL;
            
            if ($key) {
                TTransaction::open('sample');
                
                $object = new Cidade($key);
                $this->form->setData($object);
                
                TTransaction::close();
            } else {
                $this->form->clear(TRUE);
            }
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    public function onClear($param)
    {
        $this->form->clear(TRUE);
    }
}

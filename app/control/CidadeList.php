<?php
class CidadeList extends TPage
{
    protected $form;
    protected $datagrid;
    protected $pageNavigation;
    protected $loaded;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->form = new BootstrapFormBuilder('form_search_Cidade');
        $this->form->setFormTitle('Cidades');
        
        $nome = new TEntry('nome');
        $this->form->addFields([new TLabel('Nome')], [$nome]);
        
        $find_action = $this->form->addAction('Buscar', new TAction([$this, 'onSearch']), 'fa:search blue');
        $find_action->class = 'btn btn-sm btn-default';
        
        $new_action = $this->form->addAction('Novo', new TAction(['CidadeForm', 'onEdit']), 'fa:plus green');
        $new_action->class = 'btn btn-sm btn-primary';
        
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->width = '100%';
        
        $column_id = new TDataGridColumn('id', 'ID', 'center', '10%');
        $column_nome = new TDataGridColumn('nome', 'Nome', 'left');
        
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_nome);
        
        $action_edit = new TDataGridAction(['CidadeForm', 'onEdit']);
        $action_edit->setLabel('Editar');
        $action_edit->setImage('far:edit blue');
        $action_edit->setField('id');
        $this->datagrid->addAction($action_edit);
        
        $action_del = new TDataGridAction([$this, 'onDelete']);
        $action_del->setLabel('Excluir');
        $action_del->setImage('far:trash-alt red');
        $action_del->setField('id');
        $this->datagrid->addAction($action_del);
        
        $this->datagrid->createModel();
        
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add($this->form);
        $container->add($this->datagrid);
        $container->add($this->pageNavigation);
        
        parent::add($container);
    }
    
    public function onSearch($param)
    {
        $data = $this->form->getData();
        
        $filters = [];
        if (!empty($data->nome)) {
            $filters[] = new TFilter('nome', 'like', "%{$data->nome}%");
        }
        
        TSession::setValue(__CLASS__ . '_filters', $filters);
        TSession::setValue(__CLASS__ . '_filter_data', $data);
        
        $this->form->setData($data);
        $this->onReload(['offset' => 0, 'first_page' => 1]);
    }
    
    public function onReload($param = NULL)
    {
        try {
            TTransaction::open('sample');
            
            $repository = new TRepository('Cidade');
            $limit = 10;
            
            $criteria = new TCriteria;
            $criteria->setProperties($param);
            $criteria->setProperty('limit', $limit);
            $criteria->setProperty('order', 'id');
            $criteria->setProperty('direction', 'desc');
            
            $filters = TSession::getValue(__CLASS__ . '_filters');
            if ($filters) {
                foreach ($filters as $filter) {
                    $criteria->add($filter);
                }
            }
            
            $objects = $repository->load($criteria, FALSE);
            $this->datagrid->clear();
            
            if ($objects) {
                foreach ($objects as $object) {
                    $this->datagrid->addItem($object);
                }
            }
            
            $criteria->resetProperties();
            $count = $repository->count($criteria);
            
            $this->pageNavigation->setCount($count);
            $this->pageNavigation->setProperties($param);
            $this->pageNavigation->setLimit($limit);
            
            TTransaction::close();
            $this->loaded = TRUE;
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    public function onDelete($param)
    {
        $action = new TAction([$this, 'onDeleteConfirmed']);
        $action->setParameters($param);
        
        new TQuestion('Deseja realmente excluir?', $action);
    }
    
    public function onDeleteConfirmed($param)
    {
        try {
            TTransaction::open('sample');
            
            $key = $param['key'] ?? $param['id'] ?? NULL;
            if ($key) {
                $object = new Cidade($key);
                $object->delete();
            }
            
            TTransaction::close();
            $this->onReload($param);
            
            new TMessage('info', 'Registro excluido');
        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    public function show()
    {
        if (!$this->loaded && (!isset($_GET['method']) || $_GET['method'] !== 'onReload')) {
            $this->onReload(func_get_arg(0));
        }
        parent::show();
    }
}

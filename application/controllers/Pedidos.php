<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Pedidos extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(PedidoModel::class);
        $this->view_data = [];
        $this->load->library('session');
    }

    public function salvar() {
        $this->load->helper('html');
        $this->load->helper('url');
        $this->load->helper('form');
        $this->view_data['title'] = 'Alterar/Incluir Pedido';

        $this->load->model('ClienteModel');
        $this->view_data['clientes'] = array_column($this->ClienteModel->list_distinct(['id', 'nome']), 'nome', 'id');
        $this->view_data['cliente_id'] = [];

        //gera novo registro
        $this->view_data['pedido_fields'] = array_fill_keys(array_diff($this->PedidoModel->fields, $this->PedidoModel->primary_key()), null);

        if ($this->input->method() == 'post') {
            if ($this->PedidoModel->save($this->input->post()) !== false) {
                $this->load->helper('url');
                redirect('pedidos');
            }
        } else if ($this->uri->total_segments() > 3) {
            $pedido = $this->PedidoModel->select($this->uri->uri_to_assoc());
            $this->view_data['cliente_id'] = $pedido['cliente_id'];
            $this->view_data['pedido_fields'] = $pedido;
        }
        $this->show_salvar();
    }

    public function show_salvar() {
        $this->load->view('default/top', $this->view_data);
        $this->load->view('pedido/form', $this->view_data);
        $this->load->view('default/bottom', $this->view_data);
    }

    public function remover() {
        if ($this->uri->total_segments() > 3) {
            if ($this->PedidoModel->remove($this->uri->uri_to_assoc()) !== false) {
                $this->load->helper('url');
                redirect('pedidos');
            }
        }
    }

    public function index() {
        $this->load->helper('html');
        
        $this->load->join_model($this->PedidoModel, 'pedido.cliente_id = cliente.id', 'ClienteModel');
        
        if ($this->uri->total_segments() > 3) {
            var_dump($this->uri->uri_to_assoc());
//            pagination
        } else if ($this->input->method() == 'post') {
            $this->__filter_session_persistence();
            $list = $this->PedidoModel->list($this->session->last_filter);
        } else {
            $list = $this->PedidoModel->list();
//            print_r($this->PedidoModel->db->last_query());
//            die;
        }
        log_message('debug', print_r($list, true));
        $this->view_data['title'] = 'Lista de Pedidos';
        $this->view_data['table'] = $this->__pedido_table($list);
        $this->view_data['comands'] = implode(str_repeat('&nbsp;', 1), $this->__pedido_comands());
        $this->show_index();
    }

    private function show_index() {
        $this->load->view('default/top', $this->view_data);
        $this->load->view('pedido/table', $this->view_data);
        $this->load->view('pedido/comands', $this->view_data);
        $this->load->view('default/bottom', $this->view_data);
    }

    private function __pedido_comands() {
        return array($this->__anchor('PedidoModel', 'pedidos/salvar', [], 'Add', ['role' => 'buttom', 'class' => 'btn btn-info']));
    }

    private function __pedido_table(array $list) {
        $this->load->library('table');
        $this->table->set_template([
            'table_open' => '<table border="1" cellpadding="4" cellspacing="0">',
            'thead_open' => '<thead>',
            'thead_close' => '</thead>',
            'heading_row_start' => '<tr>',
            'heading_row_end' => '</tr>',
            'heading_cell_start' => '<th>',
            'heading_cell_end' => '</th>',
            'tbody_open' => '<tbody>',
            'tbody_close' => '</tbody>',
            'row_start' => '<tr>',
            'row_end' => '</tr>',
            'cell_start' => '<td>',
            'cell_end' => '</td>',
            'row_alt_start' => '<tr>',
            'row_alt_end' => '</tr>',
            'cell_alt_start' => '<td>',
            'cell_alt_end' => '</td>',
            'table_close' => '</table>'
        ]);

        $this->table->set_heading('Pedido', 'Cliente', 'Data', 'Total do Pedido', 'Ações');
        $this->__list_transformations($list);
        $table = $this->table->generate($list);
        return $table;
    }

    private function __list_transformations(&$list) {
        array_walk($list, function(&$v, $k) {
            $cliente = $this->__load_obj('ClienteModel', ['id' => $v['cliente_id']]);
            $v['cliente_id'] = $v['cliente_id'] . ' - ' . $cliente['nome'];
            $v['actions'] = $this->__anchor('PedidoItemModel', 'pedido_itens/salvar', ['pedido_id' => $v['id']], 'Itens', ['role' => 'buttom', 'class' => 'btn btn-warning'])
                    . str_repeat('&nbsp;', 1)
                    . $this->__anchor('PedidoModel', 'pedidos/remover', $v, 'Remove', ['role' => 'buttom', 'class' => 'btn btn-danger']);
        });
        return $list;
    }

}

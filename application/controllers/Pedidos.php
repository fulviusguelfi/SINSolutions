<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Pedidos extends CI_Controller {

    private $view_data, $save_anchor, $delete_anchor;

    public function __construct() {
        parent::__construct();
        $this->load->model(PedidoModel::class);
        $this->view_data = [];
        $this->load->library('session');
    }

    public function salvar() {
        $this->load->helper('html');
        $this->load->helper('form');
        $this->view_data['title'] = 'Alterar/Incluir Pedido';

        $this->load->model('ClienteModel');
        $this->view_data['clientes'] = array_column($this->ClienteModel->list_distinct(['id', 'nome']), 'nome', 'id');
        $this->view_data['cliente_id'] = [];

        //gera novo registro
        $this->view_data['pedido_fields'] = array_fill_keys(array_diff($this->PedidoModel->fields, $this->PedidoModel->primary_key), null);

        if ($this->input->method() == 'post') {
            if ($this->PedidoModel->save($this->input->post()) !== false) {
                $this->load->helper('url');
                redirect('pedidos');
            }
        } else if ($this->uri->total_segments() > 3) {
            $pedido_id = $this->uri->uri_to_assoc();
            $pedido = $this->PedidoModel->select($pedido_id);
            $this->view_data['cliente_id'] = $pedido['cliente_id'];
            $this->view_data['pedido_fields'] = $pedido;
        }
        $this->show_salvar();
    }

    public function show_salvar() {
        $this->load->view('pedido/table_top', $this->view_data);
        $this->load->view('pedido/form', $this->view_data);
        $this->load->view('pedido/table_bottom', $this->view_data);
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
        if ($this->uri->total_segments() > 3) {
            var_dump($this->uri->uri_to_assoc());
//            pagination
        } else if ($this->input->method() == 'post') {
            $this->__filter_session_persistence();
            $list = $this->PedidoModel->list($this->session->last_filter);
        } else {
            $list = $this->PedidoModel->list();
        }
        log_message('debug', print_r($list, true));
        $this->view_data['title'] = 'Lista de Pedidos';
        $this->view_data['table'] = $this->__pedido_table($list);
        $this->view_data['comands'] = implode(str_repeat('&nbsp;', 1), $this->__pedido_comands());
        $this->show_index();
    }

    private function show_index() {
        $this->load->view('pedido/table_top', $this->view_data);
        $this->load->view('pedido/table', $this->view_data);
        $this->load->view('pedido/comands', $this->view_data);
        $this->load->view('pedido/table_bottom', $this->view_data);
    }

    private function __pedido_comands() {
        return array($this->__save_anchor([], 'Add', ['role' => 'buttom', 'class' => 'btn btn-info']));
    }

    private function __pedido_table(array $list) {
        $this->load->library('table');
        $this->table->set_template([
            'table_open' => '<table border="0" cellpadding="4" cellspacing="0">',
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

        $this->table->set_heading('Pedido', 'Cliente', 'Data','Total do Pedido', 'Ações');
        $this->__list_transformations($list);
        $table = $this->table->generate($list);
        return $table;
    }
    
    private function __list_itens_anchor(array $key, $title = '', $attributes = []) {
        $this->load->helper('array');
        $this->load->helper('url');
        $key = (empty($key) ? '' : $this->uri->assoc_to_uri($key));
        $uri = "pedido_itens/salvar/{$key}";
        return anchor($uri, $title, $attributes);
    }


    private function __save_anchor(array $key, $title = '', $attributes = []) {
        $this->load->helper('array');
        $this->load->helper('url');
        $key = (empty($key) ? '' : $this->uri->assoc_to_uri(elements($this->PedidoModel->primary_key, $key)));
        $uri = "pedidos/salvar/{$key}";
        return anchor($uri, $title, $attributes);
    }

    private function __delete_anchor(array $key, $title = '', $attributes = []) {
        $this->load->helper('array');
        $this->load->helper('url');
        $key = $this->uri->assoc_to_uri(elements($this->PedidoModel->primary_key, $key));
        $uri = "pedidos/remover/{$key}";
        return anchor($uri, $title, $attributes);
    }

    private function __list_transformations(&$list) {
        $this->load->model('ClienteModel');
        array_walk($list, function(&$v, $k) {
            $cliente = $this->ClienteModel->select(['id' => $v['cliente_id']]);
            $v['cliente_id'] = $v['cliente_id'] . ' - ' . $cliente['nome'];
            $v['actions'] = $this->__list_itens_anchor(['pedido_id' => $v['id']], 'Itens', ['role' => 'buttom', 'class' => 'btn btn-warning'])
                    . str_repeat('&nbsp;', 1)
                    . $this->__delete_anchor($v, 'Remove', ['role' => 'buttom', 'class' => 'btn btn-danger']);
        });
        return $list;
    }

    private function __delete_cols(&$array, array $cols) {
        array_walk($array, function (&$v) use ($cols) {
            foreach ($cols as $key) {
                unset($v[$key]);
            }
        });
    }

    private function __filter_session_persistence() {
        ($this->uri->post('clear_filter') !== null) ?
                        $this->session->unset_userdata('last_filter') :
                        $this->session->set_userdata('last_filter', $this->uri->post());
    }

}

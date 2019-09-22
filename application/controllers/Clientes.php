<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Clientes extends CI_Controller {

    private $view_data, $save_anchor, $delete_anchor;

    public function __construct() {
        parent::__construct();
        $this->load->model(ClienteModel::class);
        $this->view_data = [];
    }

    public function salvar() {
        $this->load->helper('html');
        $this->load->helper('form');
        
        //gera novo registro
        $this->view_data['cliente_fields'] = array_fill_keys(array_diff(ClienteModel::$fields, ClienteModel::$primary_key), null);
        
        if ($this->input->method() == 'post') {
            if ($this->ClienteModel->save($this->input->post()) !== false) {
                $this->load->helper('url');
                redirect('clientes');
            }
        } else if ($this->uri->total_segments() > 3) {
            $this->view_data['cliente_fields'] = $this->ClienteModel->select($this->uri->uri_to_assoc());
        }
        $this->show_salvar();
    }

    public function show_salvar() {
        $this->load->view('cliente/table_top', $this->view_data);
        $this->load->view('cliente/form', $this->view_data);
        $this->load->view('cliente/table_bottom', $this->view_data);
    }

    public function remover() {
        if ($this->uri->total_segments() > 3) {
            if ($this->ClienteModel->remove($this->uri->uri_to_assoc()) !== false) {
                $this->load->helper('url');
                redirect('clientes');
            }
        }
    }

    public function index() {
        $this->load->helper('html');
        if ($this->uri->total_segments() > 3) {
            var_dump($this->uri->uri_to_assoc());
//            pagination
        } else if ($this->input->method() == 'post') {
            var_dump($this->uri->post());
//            filter
            $list = $this->ClienteModel->list($this->uri->post());
        } else {
            $list = $this->ClienteModel->list();
        }
        log_message('debug', print_r($list, true));
        $this->view_data['title'] = 'Lista de Clientes';
        $this->view_data['table'] = $this->__cliente_table($list);
        $this->view_data['comands'] = implode(str_repeat('&nbsp;', 1), $this->__cliente_comands());
        $this->show_index();
    }

    private function show_index() {
        $this->load->view('cliente/table_top', $this->view_data);
        $this->load->view('cliente/table', $this->view_data);
        $this->load->view('cliente/comands', $this->view_data);
        $this->load->view('cliente/table_bottom', $this->view_data);
    }

    private function __cliente_comands() {
        return array($this->__save_anchor([], 'Add', ['role' => 'buttom', 'class' => 'btn btn-info']));
    }

    private function __cliente_table(array $list) {
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

        $this->table->set_heading('Nome', 'Email', 'Ações');
        $this->__list_transformations($list);
        $this->__delete_cols($list, ['id']);
        $table = $this->table->generate($list);
        return $table;
    }

    private function __save_anchor(array $key, $title = '', $attributes = []) {
        $this->load->helper('array');
        $this->load->helper('url');
        $key = (empty($key) ? '' : $this->uri->assoc_to_uri(elements(ClienteModel::$primary_key, $key)));
        $uri = "clientes/salvar/{$key}";
        return anchor($uri, $title, $attributes);
    }

    private function __delete_anchor(array $key, $title = '', $attributes = []) {
        $this->load->helper('array');
        $this->load->helper('url');
        $key = $this->uri->assoc_to_uri(elements(ClienteModel::$primary_key, $key));
        $uri = "clientes/remover/{$key}";
        return anchor($uri, $title, $attributes);
    }

    private function __list_transformations(&$list) {
        array_walk($list, function(&$v, $k) {
            $v['actions'] = $this->__save_anchor($v, 'Altera', ['role' => 'buttom', 'class' => 'btn btn-warning'])
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

}

<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Produtos extends CI_Controller {

    private $view_data, $save_anchor, $delete_anchor;

    public function __construct() {
        parent::__construct();
        $this->load->model(ProdutoModel::class);
        $this->view_data = [];
        $this->load->library('session');
    }

    public function salvar() {
        $this->load->helper('html');
        $this->load->helper('form');
        $this->view_data['title'] = 'Alterar/Incluir Produtos';

        //gera novo registro
        $this->view_data['produto_fields'] = array_fill_keys(array_diff($this->ProdutoModel->fields, $this->ProdutoModel->primary_key), null);

        if ($this->input->method() == 'post') {
            if ($this->ProdutoModel->save($this->input->post()) !== false) {
                $this->load->helper('url');
                redirect('produtos');
            }
        } else if ($this->uri->total_segments() > 3) {
            $this->view_data['produto_fields'] = $this->ProdutoModel->select($this->uri->uri_to_assoc());
        }
        $this->show_salvar();
    }

    public function show_salvar() {
        $this->load->view('produto/table_top', $this->view_data);
        $this->load->view('produto/form', $this->view_data);
        $this->load->view('produto/table_bottom', $this->view_data);
    }

    public function remover() {
        if ($this->uri->total_segments() > 3) {
            if ($this->ProdutoModel->remove($this->uri->uri_to_assoc()) !== false) {
                $this->load->helper('url');
                redirect('produtos');
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
            $list = $this->ProdutoModel->list($this->session->last_filter);
        } else {
            $list = $this->ProdutoModel->list();
        }
        log_message('debug', print_r($list, true));
        $this->view_data['title'] = 'Lista de Produtos';
        $this->view_data['table'] = $this->__produto_table($list);
        $this->view_data['comands'] = implode(str_repeat('&nbsp;', 1), $this->__produto_comands());
        $this->show_index();
    }

    private function show_index() {
        $this->load->view('produto/table_top', $this->view_data);
        $this->load->view('produto/table', $this->view_data);
        $this->load->view('produto/comands', $this->view_data);
        $this->load->view('produto/table_bottom', $this->view_data);
    }

    private function __produto_comands() {
        return array($this->__save_anchor([], 'Add', ['role' => 'buttom', 'class' => 'btn btn-info']));
    }

    private function __produto_table(array $list) {
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

        $this->table->set_heading('Produto', 'Valor de Venda', 'Ações');
        $this->__list_transformations($list);
        $this->__delete_cols($list, ['id']);
        $table = $this->table->generate($list);
        return $table;
    }

    private function __save_anchor(array $key, $title = '', $attributes = []) {
        $this->load->helper('array');
        $this->load->helper('url');
        $key = (empty($key) ? '' : $this->uri->assoc_to_uri(elements($this->ProdutoModel->primary_key, $key)));
        $uri = "produtos/salvar/{$key}";
        return anchor($uri, $title, $attributes);
    }

    private function __delete_anchor(array $key, $title = '', $attributes = []) {
        $this->load->helper('array');
        $this->load->helper('url');
        $key = $this->uri->assoc_to_uri(elements($this->ProdutoModel->primary_key, $key));
        $uri = "produtos/remover/{$key}";
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

    private function __filter_session_persistence() {
        ($this->uri->post('clear_filter') !== null) ?
                        $this->session->unset_userdata('last_filter') :
                        $this->session->set_userdata('last_filter', $this->uri->post());
    }

    private function __load_obj(string $model_name, array $key_value): array {
        if (!empty($model_name) && !empty($key_value)) {
            $this->load->model($model_name);
            $key_value = array_merge(array_fill_keys($this->$model_name->primary_key, null), $key_value);
            return $this->$model_name->select($key_value);
        }
    }

}

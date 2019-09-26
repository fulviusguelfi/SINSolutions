<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Produtos extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(ProdutoModel::class);
        $this->view_data = [];
        $this->load->library('session');
    }

    public function salvar() {
        $this->load->helper('html');
        $this->load->helper('url');
        $this->load->helper('form');
        $this->view_data['title'] = 'Alterar/Incluir Produtos';

        //gera novo registro
        $this->view_data['produto_fields'] = array_fill_keys(array_diff($this->ProdutoModel->fields, $this->ProdutoModel->primary_key()), null);

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
        $this->load->view('default/top', $this->view_data);
        $this->load->view('produto/form', $this->view_data);
        $this->load->view('default/bottom', $this->view_data);
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
        $this->load->view('default/top', $this->view_data);
        $this->load->view('produto/table', $this->view_data);
        $this->load->view('produto/comands', $this->view_data);
        $this->load->view('default/bottom', $this->view_data);
    }

    private function __produto_comands() {
        return array($this->__anchor('ProdutoModel', 'produtos/salvar', [], 'Add', ['role' => 'buttom', 'class' => 'btn btn-info']));
    }

    private function __produto_table(array $list) {
        $this->load->library('table');
        $this->table->set_template($this->get_table_template());

        $this->table->set_heading('Produto', 'Valor de Venda', 'Ações');
        $this->__list_transformations($list);
        $this->__delete_cols($list, ['id']);
        $table = $this->table->generate($list);
        return $table;
    }

    private function __list_transformations(&$list) {
        array_walk($list, function(&$v, $k) {
            $v['actions'] = $this->__anchor('ProdutoModel', 'produtos/salvar', $v, 'Altera', ['role' => 'buttom', 'class' => 'btn btn-warning'])
                    . str_repeat('&nbsp;', 1)
                    . $this->__anchor('ProdutoModel', 'produtos/remover', $v, 'Remove', ['role' => 'buttom', 'class' => 'btn btn-danger']);
        });
        return $list;
    }
}

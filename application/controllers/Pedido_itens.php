<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Pedido_itens extends CI_Controller {

    private $view_data, $save_anchor, $delete_anchor;

    public function __construct() {
        parent::__construct();
        $this->load->model(PedidoItemModel::class);
        $this->view_data = [];
        $this->load->library('session');
    }

    public function salvar() {
        $this->load->helper('html');
        $this->load->helper('form');
        $this->view_data['title'] = 'Alterar/Incluir Item';

        $this->load->model('ProdutoModel');
        $this->view_data['produtos'] = array_column($this->ProdutoModel->list_distinct(['id', 'produto']), 'produto', 'id');
        $this->view_data['produto_id'] = [];

        //gera novo registro
        $this->view_data['pedido_item_fields'] = array_fill_keys(array_diff($this->PedidoItemModel->fields, $this->PedidoItemModel->primary_key), null);

        if ($this->input->method() == 'post') {
            if ($this->PedidoItemModel->save($this->input->post()) !== false) {
                $this->load->helper('url');
                redirect('pedido_itens/salvar');
            }
        } else if ($this->uri->total_segments() > 3) {
            $this->load->helper('array');
            $this->load->model('PedidoModel');
            //criterio de busca
            $pedido_item_search = $this->uri->uri_to_assoc();
            if (empty(elements($this->PedidoItemModel->primary_key, array_keys($pedido_item_search)))) {
                //busca selecionado
                $pedido_item = $this->PedidoItemModel->select($pedido_item_search);
                $this->view_data['pedido_item_fields'] = $pedido_item;
            } else {
                //busca lista por pedido_id
                $pedido_key_name = $this->PedidoModel->table_name . '_id';
                if (!empty($pedido_item_search[$pedido_key_name])) {
                    $pedido = ['id' => $pedido_item_search[$pedido_key_name]];
                    $this->load->model('PedidoModel');
                    $pedido = $this->PedidoModel->select($pedido);
                    $this->view_data['pedido_item_fields']['pedido_id'] = $pedido['id'];
                    $this->load->model('ClienteModel');
                    $cliente = $this->ClienteModel->select(['id' => $pedido['cliente_id']]);
                    $this->view_data['pedido_label'] = $pedido['id'] . ' - ' . $pedido['data'] . ' - ' . $cliente['nome'];
                }
                $pedido_itens = $this->PedidoItemModel->list_distinct($this->PedidoItemModel->fields, $pedido_item_search);
                if (!empty($pedido_itens)) {
                    $this->view_data['table'] = $this->__pedido_item_table($pedido_itens);
                }
            }
        }
        $this->show_salvar();
    }

    public function show_salvar() {
        $this->load->view('pedido_item/table_top', $this->view_data);
        $this->load->view('pedido_item/form', $this->view_data);
        $this->load->view('pedido_item/table', $this->view_data);
        $this->load->view('pedido_item/table_bottom', $this->view_data);
    }

    public function remover() {
        if ($this->uri->total_segments() > 3) {
            if ($this->PedidoItemModel->remove($this->uri->uri_to_assoc()) !== false) {
                $this->load->helper('url');
                redirect('pedido_itens/salvar');
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
            $list = $this->PedidoItemModel->list($this->session->last_filter);
        } else {
            $list = $this->PedidoItemModel->list();
        }
        log_message('debug', print_r($list, true));
        $this->view_data['table'] = $this->__pedido_item_table($list);
        $this->view_data['comands'] = implode(str_repeat('&nbsp;', 1), $this->__pedido_item_comands());
        $this->view_data['title'] = 'Lista de Pedidos';
        $this->show_index();
    }

    private function show_index() {
        $this->load->view('pedido_item/table_top', $this->view_data);
        $this->load->view('pedido_item/table', $this->view_data);
        $this->load->view('pedido_item/comands', $this->view_data);
        $this->load->view('pedido_item/table_bottom', $this->view_data);
    }

    private function __pedido_item_comands() {
        return array($this->__save_anchor([], 'Add', ['role' => 'buttom', 'class' => 'btn btn-info']));
    }

    private function __pedido_item_table(array $list) {
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

        $this->table->set_heading('Pedido / Data / Cliente', 'Produto', 'Quantidade', 'Sub-Total', 'Ações');
        $this->__list_transformations($list);
        $this->__delete_cols($list, ['id']);
        $table = $this->table->generate($list);
        return $table;
    }

    private function __save_anchor(array $key, $title = '', $attributes = []) {
        $this->load->helper('array');
        $this->load->helper('url');
        $key = (empty($key) ? '' : $this->uri->assoc_to_uri(elements($this->PedidoModel->primary_key, $key)));
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
        $this->load->model('PedidoModel');
        $this->load->model('ProdutoModel');
        array_walk($list, function(&$v, $k) {
            $pedido = $this->PedidoModel->select(['id' => $v['pedido_id']]);
            $cliente = $this->ClienteModel->select(['id' => $pedido['cliente_id']]);
            $v['pedido_id'] = $pedido['id'] . ' - ' . $pedido['data'] . ' - ' . $cliente['nome'];

            $produto = $this->ProdutoModel->select(['id' => $v['produto_id']]);
            $v['produto_id'] = $v['produto_id'] . ' - ' . $produto['produto'];

            $v['sub_total'] = ((int) $v['qtd']) * floatval($produto['valor_venda']);

            $v['actions'] = $this->__delete_anchor($v, 'Remove', ['role' => 'buttom', 'class' => 'btn btn-danger']);

            $visual_order = array_fill_keys(['pedido_id', 'produto_id', 'qtd', 'sub_total', 'actions'], null);
            $v = array_merge($visual_order, $v);


//            $v['actions'] = $this->__save_anchor($v, 'Altera', ['role' => 'buttom', 'class' => 'btn btn-warning'])
//                    . str_repeat('&nbsp;', 1)
//                    . $this->__delete_anchor($v, 'Remove', ['role' => 'buttom', 'class' => 'btn btn-danger']);
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

<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Pedido_itens extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(PedidoItemModel::class);
        $this->view_data = [];
        $this->load->library('session');
    }

    public function salvar() {
        $this->load->helper('html');
        $this->load->helper('url');
        $this->load->helper('form');
        $this->view_data['title'] = 'Alterar/Incluir Item';

        $this->load->model('ProdutoModel');
        $this->view_data['produtos'] = array_column($this->ProdutoModel->list_distinct(['id', 'produto']), 'produto', 'id');
        $this->view_data['produto_id'] = [];

        $this->session->set_flashdata('remove_redirect', $this->uri->uri_string());

        //gera novo registro
        $this->view_data['pedido_item_fields'] = array_fill_keys(array_diff($this->PedidoItemModel->fields, $this->PedidoItemModel->primary_key()), null);

        if ($this->input->method() == 'post') {
            $produto = $this->__load_obj('ProdutoModel', ['id' => $this->input->post('produto_id')]);
            $pedido = $this->__load_obj('PedidoModel', ['id' => $this->input->post('pedido_id')]);

            //altera o valor total do pedido
            $sub_total = (floatval($produto['valor_venda']) * (int) $this->input->post('qtd'));
            $pedido['total'] = floatval($pedido['total']) + $sub_total;

            $trans_result = ($this->PedidoItemModel->save($this->input->post(), OPEN_TRANS) &&
                    $this->PedidoModel->save($pedido, CLOSE_TRANS));
            if ($trans_result !== false) {

                $this->load->helper('url');
                redirect($this->uri->uri_string());
//                redirect('pedido_itens/salvar');
            }
        } else if ($this->uri->total_segments() > 3) {
            $this->load->helper('array');
            //criterio de busca
            $pedido_item_search = $this->uri->uri_to_assoc();
            if (empty(elements($this->PedidoItemModel->primary_key(), array_keys($pedido_item_search)))) {
                //busca selecionado
                $pedido_item = $this->PedidoItemModel->select($pedido_item_search);
                $this->view_data['pedido_item_fields'] = $pedido_item;
            } else {
                //busca lista por pedido_id
                $this->load->model('PedidoModel');
                $pedido_key_name = $this->PedidoModel->table_name . '_id';
                if (!empty($pedido_item_search[$pedido_key_name])) {
                    $pedido = $this->__load_obj('PedidoModel', ['id' => $pedido_item_search[$pedido_key_name]]);
                    $this->view_data['pedido_item_fields']['pedido_id'] = $pedido['id'];

                    $cliente = $this->__load_obj('ClienteModel', ['id' => $pedido['cliente_id']]);
                    $this->view_data['pedido_label'] = $pedido['id'] . ' - Valor: R$' . $pedido['total'] . ' - Cliente: ' . $cliente['nome'] . ' - Data: ' . $pedido['data'];
                }
                $pedido_itens = $this->PedidoItemModel->list_distinct($this->PedidoItemModel->fields, $pedido_item_search);
                $this->view_data['table'] = $this->__pedido_item_table($pedido_itens);
            }
        }
        $this->show_salvar();
    }

    public function show_salvar() {
        $this->load->view('default/top', $this->view_data);
        $this->load->view('pedido_item/form', $this->view_data);
        $this->load->view('pedido_item/table', $this->view_data);
        $this->load->view('default/bottom', $this->view_data);
    }

    public function remover() {
        if ($this->uri->total_segments() > 3) {

            $pedido_item = $this->__load_obj('PedidoItemModel', $this->uri->uri_to_assoc());
            $produto = $this->__load_obj('ProdutoModel', ['id' => $pedido_item['produto_id']]);
            $pedido = $this->__load_obj('PedidoModel', ['id' => $pedido_item['pedido_id']]);

            //altera o valor total do pedido
            $sub_total = (floatval($produto['valor_venda']) * (int) $pedido_item['qtd']);
            $pedido['total'] = floatval($pedido['total']) - $sub_total;

            $trans_result = ($this->PedidoModel->save($pedido, OPEN_TRANS) &&
                    $this->PedidoItemModel->remove($this->uri->uri_to_assoc(), CLOSE_TRANS));
            if ($trans_result !== false) {
                $this->load->helper('url');
                if ($this->session->flashdata('remove_redirect') !== null) {
                    $this->session->keep_flashdata('remove_redirect');
                    redirect($this->session->flashdata('remove_redirect'));
                } else {
                    redirect('pedido_itens');
                }
            }
        }
    }

    private function __pedido_item_table(array $list) {
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

        $this->table->set_heading('Pedido / Data / Cliente', 'Produto', 'Quantidade', 'Sub-Total', 'Ações');
        $this->__list_transformations($list);
        $this->__delete_cols($list, ['id']);
        $table = $this->table->generate($list);
        return $table;
    }

    private function __list_transformations(&$list) {
        array_walk($list, function(&$v, $k) {
            $v['actions'] = $this->__anchor('PedidoItemModel', 'pedido_itens/remover', $v, 'Remove', ['role' => 'buttom', 'class' => 'btn btn-danger']);

            $pedido = $this->__load_obj('PedidoModel', ['id' => $v['pedido_id']]);
            $cliente = $this->__load_obj('ClienteModel', ['id' => $pedido['cliente_id']]);
            $v['pedido_id'] = $pedido['id'] . ' - ' . $pedido['data'] . ' - ' . $cliente['nome'];

            $produto = $this->__load_obj('ProdutoModel', ['id' => $v['produto_id']]);
            $v['produto_id'] = $v['produto_id'] . ' - ' . $produto['produto'];

            $v['sub_total'] = ((int) $v['qtd']) * floatval($produto['valor_venda']);

            $visual_order = array_fill_keys(['pedido_id', 'produto_id', 'qtd', 'sub_total', 'actions'], null);
            $v = array_merge($visual_order, $v);
        });
        return $list;
    }
}

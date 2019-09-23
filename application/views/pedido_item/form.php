<?php

defined('BASEPATH') OR exit('No direct script access allowed');
$this->load->view('default/menu');
echo form_open();
echo form_fieldset('Dados dos Itens do Pedido', []);
$visual_order = array_intersect_key(array_fill_keys(['id', 'pedido_id', 'produto_id', 'qtd'], null), $pedido_item_fields);
$pedido_item_fields = array_merge($visual_order, $pedido_item_fields);
foreach ($pedido_item_fields as $key => $field) {
    $this->load->view("pedido_item/fields/{$key}", array_merge((array) $this, [$key => $field]));
}
echo form_fieldset_close();
echo form_submit('', 'Enviar', []);
echo form_reset('', 'Limpar', []);
echo form_close();
?>

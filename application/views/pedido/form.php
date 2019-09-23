<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$this->load->view('default/menu');
echo form_open();
echo form_fieldset('Dados do Pedido', []);
foreach ($pedido_fields as $key => $field) {
    $this->load->view("pedido/fields/{$key}", [$key => $field]);
}
echo form_fieldset_close();
echo form_submit('', 'Enviar', []);
echo form_reset('', 'Limpar', []);
echo form_close();
?>

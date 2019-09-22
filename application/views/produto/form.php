<?php
defined('BASEPATH') OR exit('No direct script access allowed');
echo form_open();
echo form_fieldset('Dados do Produto', []);
foreach ($produto_fields as $key => $field) {
    $this->load->view("produto/fields/{$key}", [$key => $field]);
}
echo form_fieldset_close();
echo form_submit('', 'Enviar', []);
echo form_reset('', 'Limpar', []);
echo form_close();
?>

<?php
defined('BASEPATH') OR exit('No direct script access allowed');
echo form_open();
echo form_fieldset('Dados do Cliente', []);
foreach ($cliente_fields as $key => $field) {
    $this->load->view("cliente/fields/{$key}", [$key => $field]);
}
echo form_fieldset_close();
echo form_submit('', 'Enviar', []);
echo form_reset('', 'Limpar', []);
echo form_close();
?>

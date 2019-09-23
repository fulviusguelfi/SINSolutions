<?php
defined('BASEPATH') OR exit('No direct script access allowed');

echo form_input_group_open();
echo form_label('Cliente', 'cliente_id');
echo '&nbsp;';
echo form_dropdown('cliente_id', $clientes, set_value('cliente_id', ($cliente_id ?? '')), ['id'=>'cliente_id']);
echo form_error('cliente_id', '<div class="error">', '</div>');
echo form_input_group_close();
?>
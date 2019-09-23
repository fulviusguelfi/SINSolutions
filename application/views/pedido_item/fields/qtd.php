<?php
defined('BASEPATH') OR exit('No direct script access allowed');

echo form_input_group_open();
echo form_label('Quantidade','qtd');
echo '&nbsp;';
echo form_input_number('qtd', set_value('qtd',($qtd??0)),['id' => 'qtd']);
echo form_input_group_close();
?>
<?php
defined('BASEPATH') OR exit('No direct script access allowed');
echo form_label('Valor de Venda', 'valor_venda');
echo '&nbsp;';
echo form_input_number('valor_venda', set_value('valor_venda', ($valor_venda ?? '')), ['id'=>'valor_venda'], 0, 0.01);
echo form_error('valor_venda', '<div class="error">', '</div>');
?>
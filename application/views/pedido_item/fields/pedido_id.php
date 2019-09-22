<?php

defined('BASEPATH') OR exit('No direct script access allowed');
echo form_label('Pedido', 'pedido_id');
echo '&nbsp;';
echo form_hidden('pedido_id', set_value('pedido_id', $pedido_id));
echo form_label($pedido_label);
echo form_error('pedido_id', '<div class="error">', '</div>');
?>
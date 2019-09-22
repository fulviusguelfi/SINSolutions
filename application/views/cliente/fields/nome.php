<?php
defined('BASEPATH') OR exit('No direct script access allowed');
echo form_label('Nome', 'nome');
echo '&nbsp;';
echo form_input('nome', set_value('nome', ($nome ?? '')), ['id'=>'nome']);
echo form_error('nome', '<div class="error">', '</div>');
?>
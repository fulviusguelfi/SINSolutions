<?php
defined('BASEPATH') OR exit('No direct script access allowed');

echo form_input_group_open();
echo form_label('Produto', 'produto');
echo '&nbsp;';
echo form_input('produto', set_value('produto', ($produto ?? '')), ['id'=>'produto']);
echo form_error('produto', '<div class="error">', '</div>');
echo form_input_group_close();
?>
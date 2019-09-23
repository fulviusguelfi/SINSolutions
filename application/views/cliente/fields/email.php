<?php
defined('BASEPATH') OR exit('No direct script access allowed');

echo form_input_group_open();
echo form_label('Email', 'email');
echo '&nbsp;';
echo form_input('email', set_value('email', ($email ?? '')), ['id'=>'email']);
echo form_error('email', '<div class="error">', '</div>');
echo form_input_group_close();
?>
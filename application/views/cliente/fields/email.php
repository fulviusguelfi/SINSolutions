<?php
defined('BASEPATH') OR exit('No direct script access allowed');
echo form_label('Email', 'email');
echo form_input('email', set_value('email', ($email ?? '')), ['id'=>'email']);
echo form_error('email', '<div class="error">', '</div>');
?>
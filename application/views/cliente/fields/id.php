<?php
defined('BASEPATH') OR exit('No direct script access allowed');
echo form_hidden('id', set_value('id',($id ?? '')));
echo form_error('id', '<div class="error">', '</div>');
?>
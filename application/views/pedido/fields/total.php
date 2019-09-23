<?php
defined('BASEPATH') OR exit('No direct script access allowed');

echo form_input_group_open();
echo form_label('Valor Total');
echo '&nbsp;';
echo form_label((number_format($total,2) ?? 0.00),'',[]);
echo form_input_group_close();
?>
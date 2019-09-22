<?php

defined('BASEPATH') OR exit('No direct script access allowed');
echo form_label('Data do Pedido:');
echo '&nbsp;';
$format = 'Y-m-d';
$date = date('Y-m-d');
echo form_hidden('data', set_value('data', $date));
$show_format = 'd/m/Y';
echo form_label(date_format(DateTime::createFromFormat($format, set_value('data',$date)), $show_format), '', []);
?>
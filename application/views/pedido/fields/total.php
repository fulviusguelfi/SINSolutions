<?php
defined('BASEPATH') OR exit('No direct script access allowed');
echo form_label('Valor Total');
echo '&nbsp;';
echo form_label((number_format($total,2) ?? 0.00),'',[]);
?>
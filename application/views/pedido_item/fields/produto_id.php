<?php

defined('BASEPATH') OR exit('No direct script access allowed');
echo form_label('Produto:', 'produto_id');
echo '&nbsp;';
echo form_dropdown('produto_id', $produtos, set_value('produto_id', ($produto_id??[])), []);
?>
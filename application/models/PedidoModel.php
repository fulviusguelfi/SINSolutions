<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Description of PedidoModel
 *
 * @author fulvius
 */
class PedidoModel extends MY_Model {

    public function __construct() {
        $this->table_name = 'pedido';
        parent::__construct();
    }

}

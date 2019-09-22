<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Description of PedidoModel
 *
 * @author fulvius
 */
class PedidoItemModel extends MY_Model {

    public function __construct() {
        $this->table_name = 'pedido_item';
        parent::__construct();
    }

}

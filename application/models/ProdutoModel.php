<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Description of ProdutoModel
 *
 * @author fulvius
 */
class ProdutoModel extends MY_Model {

    public function __construct() {
        $this->table_name = 'produto';
        parent::__construct();
    }

}

<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Description of ClienteModel
 *
 * @author fulvius
 */
class ClienteModel extends MY_Model {

    public function __construct() {
        $this->table_name = 'cliente';
        parent::__construct();
    }

}

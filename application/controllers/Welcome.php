<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper('html');
        $this->load->helper('url');
    }

    public function index() {
        $this->view_data['title'] = 'Bem vindo a SINSolutions';

        $this->load->view('default/top', $this->view_data);
        $this->load->view('welcome/index', $this->view_data);
        $this->load->view('default/bottom', $this->view_data);
    }

}

<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MY_Controller
 *
 * @author fulvius
 */
class MY_Controller extends CI_Controller {

    protected $view_data;

    protected function __delete_cols(&$array, array $cols) {
        array_walk($array, function (&$v) use ($cols) {
            foreach ($cols as $key) {
                unset($v[$key]);
            }
        });
    }

    protected function __filter_session_persistence() {
        ($this->uri->post('clear_filter') !== null) ?
                        $this->session->unset_userdata('last_filter') :
                        $this->session->set_userdata('last_filter', $this->uri->post());
    }

    protected function __load_obj(string $model_name, array $key_value): array {
        if (!empty($model_name) && !empty($key_value)) {
            $this->load->model($model_name);
            $key_value = array_merge(array_fill_keys($this->$model_name->primary_key(), null), $key_value);
            return $this->$model_name->select($key_value);
        }
    }

    protected function __anchor($model_name, $uri, array $key, $title = '', $attributes = []) {
        $this->load->helper('array');
        $this->load->helper('url');
        $this->load->model($model_name);
        $pk_arr = $this->$model_name->primary_key();
        $tmp_key = array_filter(elements($pk_arr, $key));
        if (!empty($tmp_key))
            $key = $tmp_key;
        $key = (empty($key) ? '' : $this->uri->assoc_to_uri($key));
        $uri .= DIRECTORY_SEPARATOR . $key;
        return anchor($uri, $title, $attributes);
    }
    
    protected function __set_table_order(array $order, array $data){
        $visual_order = array_fill_keys($order, null);
            return array_merge($visual_order, $data);
    }

}

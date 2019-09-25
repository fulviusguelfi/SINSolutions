<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MY_Loader
 *
 * @author fulvius
 */
class MY_Loader extends CI_Loader {

    public function __construct() {
        parent::__construct();
        log_message('info', 'MY_Loader Class Initialized');
    }

    public function join_model(MY_Model &$parent_model, $join, string $model_name, string $alias_name = '', $db_conn = FALSE) {
        if (empty($model_name) || empty($parent_model) || empty($join)) {
            return $this;
        }
        // load
//        if (!$this->is_loaded(get_class($parent_model))) {
//            $this->model(get_class($parent_model), $parent_name, $db_conn);
//        }
        $this->model($model_name, $alias_name, $db_conn);

        //getting components
        $model = $this->_ci_get_component(($alias_name === '') ? $model_name : $alias_name);


        //join
        /*
         * Use $join like string whith a single join clausule
         * Use $join like an array of strings of join clausules
         * Use $join as a associative array where 'key' is type of join and 'value' is the string of join clausule
         */
        if (is_array($join)) {
            foreach ($join as $type => $join) {
                if (is_int($type)) {
                    $parent_model->join($model->table_name, $join);
                    log_message('info', 'Model "' . get_class($model) . '" and parent Model ' . get_class($parent_model) . ' joined on ' . $join . ' and type inner');
                } else {
                    $parent_model->join($model->table_name, $join, $type);
                    log_message('info', 'Model "' . get_class($model) . '" and parent Model ' . get_class($parent_model) . ' joined on ' . $join . ' and type ' . $type);
                }
            }
        } elseif (is_string($join)) {
            $parent_model->join($model->table_name, $join);
            log_message('info', 'Model "' . get_class($model) . '" and parent Model ' . get_class($parent_model) . ' joined on ' . $join);
        }
        return $this;
    }

}

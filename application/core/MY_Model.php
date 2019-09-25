<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

define('OPEN_TRANS', 1);
define('CLOSE_TRANS', 2);

/**
 * Description of MY_Model
 *
 * @author fulvius
 */
class MY_Model extends CI_Model {

    protected $query, $joined, $table_fields;
    public $fields, $table_name, $field_data;

    public function __construct() {
        parent::__construct();
        $this->joined = false;
        if ($this->db->table_exists($this->table_name)) {
            $this->__load_fields($this->table_name);
        } else {
            log_message('error', 'Table ' . $this->table_name . ' does not exist.');
        }
    }

    public function list_distinct(array $fields, array $where = null) {
        $this->db->start_cache();
        $this->db->distinct();
        $this->db->select($fields);
        $this->db->where(($where ?? []));
        $this->db->stop_cache();
        return $this->__result();
    }

    public function list(array $where = null, int $limit = null, int $offset = 0, array $group_by = null, string $order_by = null): array {
        $this->db->start_cache();
        $this->db->select($this->table_fields);
        $this->db->where(($where ?? []));
        $this->db->limit(($limit ?? $this->db->count_all($this->table_name)), $offset);
        $this->db->group_by(($group_by ?? $this->primary_key()));
        $this->db->order_by(($order_by ?? implode(',', $this->primary_key())));
        $this->db->stop_cache();
        return $this->__result();
    }

    public function select(array $primary_key): array {
        $this->load->helper('array');
        $this->db->start_cache();
        $this->db->select($this->table_fields);
        $this->db->where(elements($this->primary_key(), $primary_key));
        $this->db->stop_cache();
        return $this->__row();
    }

    public function save(array $data, int $transactional = 0): bool {
        $this->load->helper('array');
        $this->db->flush_cache();
        $retorno = false;
        if ($transactional === OPEN_TRANS) {
            $this->db->trans_start();
        }
        if (empty(array_diff($this->primary_key(), array_keys($data)))) {
            $where = array_filter(elements($this->primary_key(), $data));
            $vals = array_diff($data, $where);
            $this->db->where($where);
            $retorno = $this->db->update($this->table_name, $vals);
            if ($retorno === false) {
                show_error('Erro ao alterar registro da tabela: ' . $this->table_name . ' com a primary key: ' . print_r($where, true) . ' com os dados: ' . print_r($vals, true), 500, 'Erro na Base de dados');
            }
            log_message('info', 'Registro alterado na tabela: ' . $this->table_name . ' com a primary key: ' . print_r($where, true) . ' com os dados: ' . print_r($vals, true) . ' qtd de linhas afetadas: ' . $this->db->affected_rows());
        } else {
            $retorno = $this->db->insert($this->table_name, $data);
            if ($retorno === false) {
                show_error('Erro ao adicionar registro da tabela: ' . $this->table_name . ' com os dados: ' . print_r($vals, true), 500, 'Erro na Base de dados');
            }
            log_message('info', 'Registro adicionado na tabela: ' . $this->table_name . ' novo id: ' . $this->db->insert_id());
        }
        if ($transactional === CLOSE_TRANS) {
            $this->db->trans_complete();
            if ($this->db->trans_status() === FALSE) {
                show_error('Erro na operação após ' . $this->db->total_queries() . ' querys - Erro em: ' . $this->db->last_query(), 500, 'Erro na Base de dados');
            }
            log_message('info', 'Transação Completa');
        }
        return $retorno;
    }

    public function remove(array $data, int $transactional = 0): bool {
        $this->load->helper('array');
        $data = elements($this->primary_key(), $data);
        $this->db->flush_cache();
        if ($transactional === OPEN_TRANS) {
            $this->db->trans_start();
        }
        $retorno = $this->db->delete($this->table_name, $data);
        if ($retorno === false) {
            show_error('Erro ao remover registro da tabela: ' . $this->table_name . ' com a primary key: ' . print_r($$$data, true), 500, 'Erro na Base de dados');
        }
        log_message('info', 'Registro removido com sucesso na tabela: ' . $this->table_name . ' dados: ' . print_r($data, true));
        if ($transactional === CLOSE_TRANS) {
            $this->db->trans_complete();
            if ($this->db->trans_status() === FALSE) {
                show_error('Erro na operação após ' . $this->db->total_queries() . ' querys - Erro em: ' . $this->db->last_query(), 500, 'Erro na Base de dados');
            }
            log_message('info', 'Transação Completa');
        }
        return $retorno;
    }

    public function primary_key() {
        return [$this->db->primary($this->table_name)];
    }

    public function join(string $table_name, string $join, string $type = 'inner') {
        $this->db->start_cache();
        $this->__load_fields($table_name);
        $this->db->join($table_name, $join, $type);
        $this->db->stop_cache();
        $this->joined = true;
    }

    protected function __execute_query(): void {
        $this->query = $this->db->get($this->table_name);
        $this->db->flush_cache();
        log_message('debug', $this->db->last_query());
    }

    protected function __row(): array {
        $this->__execute_query();
        return ($this->query->row_array() ?? []);
    }

    protected function __result(): array {
        $this->__execute_query();
        return $this->query->result_array();
    }

    protected function __load_fields(string $table_name = null) {
        $this->field_data[$table_name] = $this->db->field_data($table_name);
        $this->fields = array_column($this->field_data[$this->table_name], 'name');

        $this->table_fields = [];
        foreach ($this->field_data as $table => $fields_data) {
            if ($table !== $this->table_name) {
                $pk = $this->db->primary($table);
                $f = array_column($fields_data, 'name');
                unset($fields_data[array_search($pk, $f)]);
            }
            foreach ($fields_data as $value) {
                $this->table_fields[] = $this->get_table_fild_name($table, $value->name);
            }
        }
    }
    
    public function get_table_fild_name($table, $field){
        return $table.'.'.$field;
    }


    public function get_table_fields(){
        return $this->table_fields;
    }

}

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

    protected $query;
    public $fields, $table_name, $max_length_fields;
    public $remove_key = ['id'], $update_key = ['id'], $select_key = ['id'], $primary_key = ['id'];

    public function __construct() {
        parent::__construct();
        if ($this->db->table_exists($this->table_name)) {
            $this->fields = $this->db->list_fields($this->table_name);
            array_walk($this->fields, function($v, $k, $field_data) {
                $field_index = array_search($v, array_column($field_data, 'name'));
                $this->max_length_fields[$v] = $field_data[$field_index]->max_length;
                if ($field_data[$field_index]->primary_key == 1) {
                    $this->primary_key[] = $v;
                }
            }, $this->db->field_data($this->table_name));
            $this->remove_key = $this->update_key = $this->select_key = $this->primary_key;
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
        $this->db->select($this->fields);
        $this->db->where(($where ?? []));
        $this->db->limit(($limit ?? $this->db->count_all($this->table_name)), $offset);
        $this->db->group_by(($group_by ?? $this->primary_key));
        $this->db->order_by(($order_by ?? implode(',', $this->primary_key)));
        $this->db->stop_cache();
        return $this->__result();
    }

    public function select(array $primary_key): array {
        $this->load->helper('array');
        $this->db->start_cache();
        $this->db->select($this->fields);
        $this->db->where(elements($this->select_key, $primary_key));
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
        if (empty(array_diff($this->update_key, array_keys($data)))) {
            $where = array_filter(elements($this->update_key, $data));
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
                show_error('Erro na operação após '.$this->db->total_queries().' querys - Erro em: ' . $this->db->last_query() , 500, 'Erro na Base de dados');
            }
            log_message('info', 'Transação Completa');
        }
        return $retorno;
    }

    public function remove(array $data, int $transactional = 0): bool {
        $this->load->helper('array');
        $data = elements($this->remove_key, $data);
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
                show_error('Erro na operação após '.$this->db->total_queries().' querys - Erro em: ' . $this->db->last_query() , 500, 'Erro na Base de dados');
            }
            log_message('info', 'Transação Completa');
        }
        return $retorno;
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

}

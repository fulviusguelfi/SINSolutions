<?php

defined('BASEPATH') OR exit('No direct script access allowed');
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ClienteModel
 *
 * @author fulvius
 */
class ClienteModel extends CI_Model {

    private $table_name, $query;
    public static $fields;
    public static $remove_key = ['id'], $update_key = ['id'], $select_key = ['id'], $primary_key = ['id'];

    public function __construct() {
        parent::__construct();
        $this->table_name = 'cliente';
        if ($this->db->table_exists($this->table_name)) {
            self::$fields = $this->db->list_fields($this->table_name);
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
        $this->db->select(self::$fields);
        $this->db->where(($where ?? []));
        $this->db->limit(($limit ?? $this->db->count_all($this->table_name)), $offset);
        $this->db->group_by(($group_by ?? self::$primary_key));
        $this->db->order_by(($order_by ?? implode(',', self::$primary_key)));
        $this->db->stop_cache();
        return $this->__result();
    }

    public function select(array $primary_key): array {
        $this->load->helper('array');
        $this->db->start_cache();
        $this->db->select(self::$fields);
        $this->db->where(elements(self::$select_key, $primary_key));
        $this->db->stop_cache();
        return $this->__row();
    }

    public function save(array $cliente): bool {
        $this->load->helper('array');
        $this->db->flush_cache();
        $retorno = false;
        if(empty(array_diff(self::$update_key, array_keys($cliente)))){
            $where = array_filter(elements(self::$update_key, $cliente));
            $vals = array_diff($cliente, $where);
            $this->db->where($where);
            $retorno = $this->db->update($this->table_name, $vals);
            if ($retorno === false) {
                show_error('Erro ao alterar registro da tabela: ' . $this->table_name . ' com a primary key: ' . print_r($where, true) . ' com os dados: ' . print_r($vals, true), 500, 'Erro na Base de dados');
            }
            log_message('info', 'Registro alterado na tabela: ' . $this->table_name . ' com a primary key: ' . print_r($where, true) . ' com os dados: ' . print_r($vals, true) . ' qtd de linhas afetadas: ' . $this->db->affected_rows());
        } else {
            $retorno = $this->db->insert($this->table_name, $cliente);
            if ($retorno === false) {
                show_error('Erro ao adicionar registro da tabela: ' . $this->table_name . ' com os dados: ' . print_r($vals, true), 500, 'Erro na Base de dados');
            }
            log_message('info', 'Registro adicionado na tabela: ' . $this->table_name . ' novo id: ' . $this->db->insert_id());
        }
        return $retorno;
    }

    public function remove(array $cliente): bool {
        $this->load->helper('array');
        $cliente_key = elements(self::$remove_key, $cliente);
        $this->db->flush_cache();
        $retorno = $this->db->delete($this->table_name, $cliente_key);
        if ($retorno === false) {
            show_error('Erro ao remover registro da tabela: ' . $this->table_name . ' com a primary key: ' . print_r($cliente_key, true), 500, 'Erro na Base de dados');
        }
        return $retorno;
    }

    private function __execute_query(): void {
        $this->query = $this->db->get($this->table_name);
        $this->db->flush_cache();
        log_message('debug', $this->db->last_query());
    }

    private function __row(): array {
        $this->__execute_query();
        return $this->query->row_array();
    }

    private function __result(): array {
        $this->__execute_query();
        return $this->query->result_array();
    }

}

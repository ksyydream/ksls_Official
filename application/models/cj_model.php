<?php
/**
 * Created by PhpStorm.
 * User: bin.shen
 * Date: 6/2/16
 * Time: 21:22
 */

class Cj_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        ini_set('date.timezone','Asia/Shanghai');
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    public function get_number(){
        $config_cj = $this->db->select()->from('cj_config')->get()->row_array();
         if(!$config_cj){
             $config_cj=array(
                 'max'=>150,
                 'min'=>1
             );
         }
        $number = $this->_get_number($config_cj['min'],$config_cj['max']);
        $data = array(
            'number' => $number,
            'cdate' => date("Y-m-d H:i:s")
        );
        $this->db->insert('cj_list', $data);
        echo $number;
    }

    private function _get_number($min,$max){
        $number = rand($min,$max);
        if($this->_check_number($number)){
            return $number;
        }else{
            return $this->_get_number($min,$max);
        }
    }

    private function _check_number($number){
        $row = $this->db->select('count(1) as num')->from('cj_list')->where(array('number'=>$number))->get()->row();
        if($row->num==0){
            return true;
        }else{
            return false;
        }
    }

    public function del_cj(){
        $this->db->where('id >','0')->delete('cj_list');
    }
}
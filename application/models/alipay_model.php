<?php
/**
 * Created by PhpStorm.
 * User: bin.shen
 * Date: 5/31/16
 * Time: 23:00
 */

class Alipay_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    public function save_order(){

        $data = array(
            'company_id' => $this->session->userdata('login_company_id'),
            'qty' => $this->input->post('qty'),
            'style' => 1,
            'demo' => '支付宝充值',
            'user_id' => $this->session->userdata('login_user_id'),
            't_id' => -1,
            't_name'=>null,
            'flag'=>2,
            'created' => date("Y-m-d H:i:s")
        );
        $res = $this->db->insert('sum_log',$data);
        if($res){
            return $this->db->insert_id();
        }else{
            return -1;
        }
    }

    public function change_order($out_trade_no,$log=null){
        $row = $this->db->select()->from('sum_log')->where('id',$out_trade_no)->get()->row_array();

        /*if($log){
            $data = array(
                'number' => 1,
                'cdate' => date("Y-m-d H:i:s")
            );
            $this->db->insert('cj_list', $data);
        }else{
            $data = array(
                'number' => 2,
                'cdate' => date("Y-m-d H:i:s")
            );
            $this->db->insert('cj_list', $data);
        }*/
        if($row){
            if($row['flag'] == 2){
                $this->db->trans_start();//--------开始事务

                $this->db->set('sum','sum + '.$row['qty'],false);
                $this->db->where('id',$row['company_id']);
                $this->db->update('company');

                $this->db->set('flag',1);
                if($log){
                    $this->db->set('demo','支付宝在线充值');
                }
                $this->db->where('id',$out_trade_no);
                $this->db->update('sum_log');
                $this->db->trans_complete();//------结束事务
                if ($this->db->trans_status() === FALSE) {
                    return -1;
                } else {
                    return 1;
                }
            }
        }
        return -2;
    }

}
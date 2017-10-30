<?php
/**
 * Created by PhpStorm.
 * User: bin.shen
 * Date: 5/31/16
 * Time: 23:00
 */

class Account_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    public function recharge_list($page){
        // 每页显示的记录条数，默认20条
        $numPerPage = $this->input->post('numPerPage') ? $this->input->post('numPerPage') : 10;
        $pageNum = $this->input->post('pageNum') ? $this->input->post('pageNum') : $page;

        $this->db->select('count(1) as num');
        $this->db->from('company');
        $this->db->where('flag',1);
        if ($this->input->post('company')){
            $this->db->like('name',trim($this->input->post('company')));
        }
        $row = $this->db->get()->row_array();
        //总记录数
        $data['countPage'] = $row['num'];
        $data['company'] = $this->input->post('company') ? trim($this->input->post('company')) : "";
        //list
        $this->db->select('*');
        $this->db->from('company');
        $this->db->where('flag',1);
        if ($this->input->post('company')){
            $this->db->like('name',trim($this->input->post('company')));
        }
        $this->db->limit($numPerPage, ($pageNum - 1) * $numPerPage );
        $this->db->order_by('id', 'desc');
        $data['res_list'] = $this->db->get()->result_array();
        $data['pageNum'] = $pageNum;
        $data['numPerPage'] = $numPerPage;
        return $data;
    }

    public function company_account($page,$company_id){
        // 每页显示的记录条数，默认20条
        $numPerPage = $this->input->post('numPerPage') ? $this->input->post('numPerPage') : 10;
        $pageNum = $this->input->post('pageNum') ? $this->input->post('pageNum') : $page;

        $this->db->select('count(1) as num');
        $this->db->from('sum_log');
        $this->db->where('company_id',$company_id);
        if($this->input->post('style')){
            $this->db->where('style',$this->input->post('style'));
        }
        if($this->input->POST('start_date')) {
            $this->db->where('created >=', date('Y-m-d H:i:s',strtotime($this->input->POST('start_date'))));
        }
        if($this->input->POST('end_date')) {
            $this->db->where('created <=', date('Y-m-d H:i:s',strtotime('+1 day',strtotime($this->input->POST('end_date')))));
        }
        $this->db->where('flag',1);
        $row = $this->db->get()->row_array();
        //总记录数
        $data['countPage'] = $row['num'];
        $data['company_id'] = $company_id;
        $data['style'] = $this->input->post('style');
        $data['start_date'] = $this->input->post('start_date');
        $data['end_date'] = $this->input->post('end_date');
        //list
        $this->db->select('*');
        $this->db->from('sum_log');
        $this->db->where('company_id',$company_id);
        $this->db->where('flag',1);
        if($this->input->post('style')){
            $this->db->where('style',$this->input->post('style'));
        }
        if($this->input->POST('start_date')) {
            $this->db->where('created >=', date('Y-m-d H:i:s',strtotime($this->input->POST('start_date'))));
        }
        if($this->input->POST('end_date')) {
            $this->db->where('created <=', date('Y-m-d H:i:s',strtotime('+1 day',strtotime($this->input->POST('end_date')))));
        }
        $this->db->limit($numPerPage, ($pageNum - 1) * $numPerPage );
        $this->db->order_by('created', 'desc');
        $data['res_list'] = $this->db->get()->result_array();
        $data['company'] = $this->db->select()->from('company')->where('id',$company_id)->get()->row_array();
        $data['pageNum'] = $pageNum;
        $data['numPerPage'] = $numPerPage;
        return $data;
    }

    public function mo_recharge($id)
    {
      $res = $this->db->select()->from('company')->where('id',$id)->get()->row_array();
        return $res;
    }

    public function save_sum(){
        $this->db->trans_start();//--------开始事务
        $company_id = $this->input->post('company_id');
        $qty = $this->input->post('qty');
       // die('wwww'.$qty);
        $style = $this->input->post('style');
        $demo = '人工充值/扣款';
        $this->change_sum($company_id,$qty,$style,$demo);
        $this->db->trans_complete();//------结束事务
        if ($this->db->trans_status() === FALSE) {
            return -1;
        } else {
            return 1;
        }
    }

    public function save_order(){

        $data = array(
            'company_id' => $this->session->userdata('login_company_id'),
            'qty' => $this->input->post('qty'),
            'style' => 1,
            'demo' => '微信充值',
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

    public function check_order($id){
       $res = $this->db->select()->from('sum_log')->where('id',$id)->get()->row_array();
        if($res){
            return $res['flag'];
        }else{
            return -1;
        }

    }

    public function getcommpay(){
            $data=$this->db->select('id,name,towns_id')->from('xiaoqu')->where('flag',1)->get()->result_array();
            if (!$data){
                return 1;
            }else{
                return $data;
            }
    }


}
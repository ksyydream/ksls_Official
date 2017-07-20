<?php
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class Admin_user_model extends MY_Model
{
    
    public function __construct ()
    {
    	parent::__construct();
    }
    

    public function change_status($id,$status){
		$this->db->trans_start();//--------开始事务
		$this->db->where('id',$id);
		$this->db->update('users',array('status'=>$status));
		$this->db->trans_complete();//------结束事务
		if ($this->db->trans_status() === FALSE) {
			return -1;
		} else {
			return 1;
		}
    }


	public function list_users($page)
	{
		$data['limit'] = $this->limit;
		//获取总记录数
		$this->db->select('count(1) num')->from('admin');
		$this->db->where('username !=','admin');
		$num = $this->db->get()->row();
		$data['total'] = $num->num;

		//获取详细列
		$this->db->select()->from('admin');
		$this->db->where('username !=','admin');
		$this->db->limit($this->limit, $offset = ($page - 1) * $this->limit);
		$data['items'] = $this->db->get()->result_array();

		return $data;
	}

	public function get_user_detail($id){
		$this->db->select()->from('admin');
		$this->db->where('id',$id);
		return $this->db->get()->row_array();
	}

	public function get_admin_menu(){
		return $this->db->select()->from('admin_menu')->get()->result_array();
	}

	public function save_user(){
		$data = array(
			'username'=>$this->input->post('username'),
			'rel_name'=>$this->input->post('rel_name'),
			'pwd'=>sha1($this->input->post('password')),
			'status'=>$this->input->post('status'),
			'permission'=>implode('|',$this->input->post('permission')),
			'cdate'=>date('Y-m-d H:i:s'),
		);

		if($this->input->post('id')){
			$this->db->where('id',$this->input->post('id'));
			$rs = $this->db->update('admin',$data);
		}else{
			$rs = $this->db->select()->from('admin')->where('username',$this->input->post('username'))->get()->row();
			if($rs)
				return -2;
			$rs = $this->db->insert('admin',$data);
		}
		if($rs)
			return 1;
		else
			return -1;
	}

}
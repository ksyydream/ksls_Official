<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_user extends MY_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->model('admin_user_model');
	}


	public function list_users($page=1){
		$data = $this->admin_user_model->list_users($page);
		$base_url = "/admin.php/Admin_user/list_users/";
		$pager = $this->pagination->getPageLink_by4($base_url, $data['total'], $data['limit']);
		$this->assign('pager', $pager);
		$this->assign('data', $data);
		$this->show('Admin_user/list_users');
	}

	public function user_detail($id){
		$user_data = $this->admin_user_model->get_user_detail($id);
		$data = $this->admin_user_model->get_admin_menu();
		$permission = explode('|',$user_data['permission']);
		$this->assign('permission', $permission);
		$this->assign('user_data', $user_data);
		$this->assign('data', $data);
		$this->show('Admin_user/add_user');
	}

	public function add_user(){
		$this->assign('user_data', '');
		$data = $this->admin_user_model->get_admin_menu();
		$this->assign('data', $data);
		$this->show('Admin_user/add_user');
	}

	public function save_user(){
		if(!$this->input->post('permission')){
			$this->show_message('请添加权限');
		}
		$rs = $this->admin_user_model->save_user();
		if($rs == 1){
			$this->show_message('操作成功',site_url('Admin_user/list_users'));
		}elseif ($rs == -2){
			$this->show_message('该用户已经存在');
		}else{
			$this->show_message('操作失败');
		}
	}



}
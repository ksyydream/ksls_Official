<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller {

	public function __construct() {
		parent::__construct();
		ini_set('date.timezone','Asia/Shanghai');

		$this->load->model('login_model');
	}
	
	public function index($flag = null) {
		$this->cismarty->assign('flag',$flag);//url路径
		$this->cismarty->display('login.html');
	}
	
	public function check_login(){
		$rs = $this->login_model->check_login();
		if($rs > 0){
			redirect('/index');
		}else{
			redirect('/login/index/'.$rs);
		}
	}
	
	//注销登陆
	public function logout(){
		$this->session->sess_destroy();
		redirect(site_url('login'));
	}
}
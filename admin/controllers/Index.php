<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Index extends MY_Controller {

	/**
	 * Index Page for this controller.
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */
	public function __construct()
	{
		parent::__construct();
		$this->load->model('index_model');
	}

	public function index()
	{

		$this->display('layout/index.html');

	}

	public function main(){
		$this->show('index');
	}

	public function update_password(){
		echo $this->index_model->update_password();
		die;
	}

	public function check_pass($pass) {
		$user_info = $this->session->userdata('user_info');
		echo $user_info['pwd'] == sha1($pass) ? 1 : 0;
		die;
	}
}

<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Messages extends MY_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->model('messages_model');
	}

	public function list_message($page=1){
		$data = $this->messages_model->list_message($page);
		$base_url = "/admin.php/Messages/list_message/";
		$pager = $this->pagination->getPageLink($base_url, $data['total'], $data['limit']);
		$this->assign('pager', $pager);
		$this->assign('data', $data);
		$this->assign('page', $data['page']);
		$this->show('Messages/list_message');
	}

	public function get_detail($id=null){
		if($id){
			$detail = $this->messages_model->get_detail($id);
			echo json_encode($detail);
		}else{
			echo -1;
		}
	}

	public function become_2(){
		echo $this->messages_model->become_flag(2);
	}

	public function become_3(){
		echo $this->messages_model->become_flag(3);
	}

	public function delete_id($id){
		echo $this->messages_model->delete_id($id);
	}
}
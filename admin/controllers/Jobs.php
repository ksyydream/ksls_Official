<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Jobs extends MY_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->model('job_model');
	}

	public function list_job($page=1){
		$data = $this->job_model->list_job($page);
		$base_url = "/admin.php/Jobs/list_job/";
		$pager = $this->pagination->getPageLink($base_url, $data['total'], $data['limit']);
		$this->assign('pager', $pager);
		$this->assign('data', $data);
		$this->assign('page', $page);
		$this->show('job/list_job');
	}

	public function save_job(){
		if(!$this->input->post('title')){
			$this->show_message('新闻标题不能为空!');
		}
		$rs =$this->job_model->save_job();
		if($rs == 1){
			$this->show_message('保存成功',site_url('jobs/list_job'));
		}else{
			$this->show_message('保存失败');
		}
	}
	public function add_job($id=null,$page=1){
		$this->assign('page', $page);
		if($id){
			$detail = $this->job_model->get_job_detail($id);
			$this->assign('detail', $detail);
		}
		$this->show('job/job_detail');
	}
}
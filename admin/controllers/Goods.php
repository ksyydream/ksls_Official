<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Goods extends MY_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->model('goods_model');
		$this->load->library('image_lib');
		$this->load->helper('directory');
	}

	public function list_goods($page=1){
		$data = $this->goods_model->list_goods($page);
		$base_url = "/admin.php/goods/list_goods/";
		$pager = $this->pagination->getPageLink($base_url, $data['total'], $data['limit']);
		$this->assign('pager', $pager);
		$this->assign('data', $data);
		$this->assign('page', $page);
		$this->show('goods/list_goods');
	}

	public function upload_image(){
		$dir = FCPATH . '/upload/goods_logo';
		if(!is_dir($dir)){
			mkdir($dir,0777,true);
		}
		$config['upload_path'] = './upload/goods_logo/';
		$config['allowed_types'] = 'gif|jpg|png|jpeg';
		$config['encrypt_name'] = true;
		$config['max_size'] = '3200';
		$this->load->library('upload', $config);

		if ( ! $this->upload->do_upload('userfile')){
			echo 1;
		}else{
			$pic_arr = $this->upload->data();
			echo $pic_arr['file_name'];
		}
	}

	public function save_good(){
		if(!$this->input->post('good_name')){
			$this->show_message('商品名称不能为空!');
		}
		if(!$this->input->post('logo')){
			$this->show_message('商品logo不能为空!');
		}
		if(!$this->input->post('type_id')){
			$this->show_message('商品类别不能为空!');
		}
		if(!$this->input->post('gg')){
			$this->show_message('商品规格不能为空!');
		}
		if(!$this->input->post('gmxz')){
			$this->show_message('商品购买须知不能为空!');
		}
		if(!$this->input->post('unit')){
			$this->show_message('商品单位不能为空!');
		}
		/*if(!$this->input->post('price')){
			$this->show_message('商品售价不能为空!');
		}
		if(!$this->input->post('old_price')){
			$this->show_message('商品原价不能为空!');
		}*/
		/*if(!$this->input->post('kc')){
			$this->show_message('商品库存不能为空!');
		}*/
		if(!$this->input->post('percent')){
			$this->show_message('商品分销类型不能为空!');
		}
		$rs =$this->goods_model->save_good();
		if($rs == 1){
			$this->show_message('保存成功',site_url('goods/list_goods'));
		}else{
			$this->show_message('保存失败');
		}
	}
	public function add_good($id=null,$page=1){
		$goods_type = $this->goods_model->get_goods_type();
		$this->assign('goods_type', $goods_type);
		$folder_user = $this->session->userdata('user_info');
		$this->assign('time', date('YmdHis'));
		$this->assign('f_user_id', $folder_user['id']);
		$this->assign('page', $page);
		if($id){
			$detail = $this->goods_model->get_good_detail($id);
			$goods_pics = $this->goods_model->get_good_pic($id);
			$goods_gg = $this->goods_model->get_good_gg($id);
			$this->assign('detail', $detail);
			$this->assign('goods_gg', $goods_gg);
			$this->assign('goods_pics', $goods_pics);
		}
		$this->show('goods/goods_detail');
	}

	///////////////////////////////////////////////////////////////////
	public function save_pics($time){
		$dir = FCPATH . '/upload/goods_pic';
		if(!is_dir($dir)){
			mkdir($dir,0777,true);
		}
		if (is_readable('./././upload/goods_pic') == false) {
			mkdir('./././upload/goods_pic',0777,true);
		}
		if (is_readable('./././upload/goods_pic/'.$time) == false) {
			mkdir('./././upload/goods_pic/'.$time,0777,true);
		}

		$path = './././upload/goods_pic/'.$time;

		//设置缩小图片属性
		$config_small['image_library'] = 'gd2';
		$config_small['create_thumb'] = TRUE;
		$config_small['quality'] = 80;
		$config_small['maintain_ratio'] = TRUE; //保持图片比例
		$config_small['new_image'] = $path;
		$config_small['width'] = 300;
		$config_small['height'] = 190;

		//设置原图限制
		$config['upload_path'] = $path;
		$config['allowed_types'] = 'gif|jpg|png|jpeg';
		$config['max_size'] = '10000';
		$config['encrypt_name'] = true;
		$this->load->library('upload', $config);

		if($this->upload->do_upload()){
			$data = $this->upload->data();//返回上传文件的所有相关信息的数组
			$config_small['source_image'] = $data['full_path']; //文件路径带文件名
			$this->image_lib->initialize($config_small);
			$this->image_lib->resize();

			echo 1;
		}else{
			echo -1;
		}
		exit;
	}

	//ajax获取图片信息
	public function get_pics($time){
		$path = './././upload/goods_pic/'.$time;
		$map = directory_map($path);
		$data = array();
		//整理图片名字，取缩略图片
		foreach($map as $v){
			if(substr(substr($v,0,strrpos($v,'.')),-5) == 'thumb'){
				$data['img'][] = $v;
			}
		}
		$data['time'] = $time;
		echo json_encode($data);
	}

	//ajax删除图片
	public function del_pic($folder,$pic,$id=null){
		$data = $this->goods_model->del_pic($folder,$pic,$id);
		echo json_encode($data);
	}
}
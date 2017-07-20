<?php
if (! defined('BASEPATH'))
	exit('No direct script access allowed');

class Messages_model extends MY_Model
{

	public function __construct ()
	{
		parent::__construct();
	}
	public function list_message($page=1){
		$search_date = 'create_time';
		$data['limit'] = $this->limit;
		//获取总记录数
		$this->db->select('count(1) num')->from('message a');
		if($this->input->post('keyword')){
			$this->db->like('a.content',$this->input->post('keyword'));
		}
		if($this->input->post('s_date')){
			$this->db->where("a.{$search_date} >=",$this->input->post('s_date'));
		}

		if($this->input->post('e_date')){
			$this->db->where("a.{$search_date} <=",$this->input->post('e_date')." 23:59:59");
		}
		if($this->input->post('flag')){
			$this->db->where("a.flag",$this->input->post('flag'));
		}
		$num = $this->db->get()->row();
		$data['total'] = $num->num;

		//这里处理如何是在删除情况下 最后一页数据不现实的情况
		if($page > 1){
			$sj_page_count = ceil($data['total']/$data['limit']);
			if($sj_page_count < $page){
				$page-=1;
			}
		}
		//搜索条件
		$data['flag'] = $this->input->post('flag')?$this->input->post('flag'):null;
		$data['keyword'] = $this->input->post('keyword')?$this->input->post('keyword'):null;
		$data['s_date'] = $this->input->post('s_date')?$this->input->post('s_date'):null;
		$data['e_date'] = $this->input->post('e_date')?$this->input->post('e_date'):null;
		//获取详细列
		$this->db->select('a.*')->from('message a');
		if($this->input->post('keyword')){
			$this->db->like('a.content',$this->input->post('keyword'));
		}
		if($this->input->post('s_date')){
			$this->db->where("a.{$search_date} >=",$this->input->post('s_date'));
		}

		if($this->input->post('e_date')){
			$this->db->where("a.{$search_date} <=",$this->input->post('e_date')." 23:59:59");
		}
		if($this->input->post('flag')){
			$this->db->where("a.flag",$this->input->post('flag'));
		}
		$this->db->limit($this->limit, $offset = ($page - 1) * $this->limit);
		$this->db->order_by('a.id','desc');
		$data['items'] = $this->db->get()->result_array();
		$data['page'] = $page;
		return $data;
	}

	public function get_detail($id){
		$this->db->where('id',$id)->update('message',array('flag'=>2));
		$this->db->select('a.*')->from('message a');
		$this->db->where('a.id',$id);
		return $this->db->get()->row_array();
	}

	public function become_flag($flag){
		$requset = $this->db->where('id',$this->input->post('message_id'))->update('message',array('flag'=>$flag));
		if($requset){
			return 1;
		}else{
			return 2;
		}
	}

	public function delete_id($id){
		$requset = $this->db->where('id',$id)->delete('message');
		if($requset){
			return 1;
		}else{
			return 2;
		}
	}

}
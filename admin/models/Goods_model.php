<?php
if (! defined('BASEPATH'))
	exit('No direct script access allowed');

class Goods_model extends MY_Model
{

	public function __construct ()
	{
		parent::__construct();
	}
	public function list_goods($page=1){
		$search_date = 'cdate';
		$data['limit'] = $this->limit;
		//获取总记录数
		$this->db->select('count(1) num')->from('goods a');
		$this->db->join('goods_type b','a.type_id = b.id','left');
		if($this->input->post('keyword')){
			$this->db->like('a.good_name',$this->input->post('keyword'));
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

		//搜索条件
		$data['flag'] = $this->input->post('flag')?$this->input->post('flag'):null;
		$data['keyword'] = $this->input->post('keyword')?$this->input->post('keyword'):null;
		$data['s_date'] = $this->input->post('s_date')?$this->input->post('s_date'):null;
		$data['e_date'] = $this->input->post('e_date')?$this->input->post('e_date'):null;
		//获取详细列
		$this->db->select('a.*,b.type_name')->from('goods a');
		$this->db->join('goods_type b','a.type_id = b.id','left');
		if($this->input->post('keyword')){
			$this->db->like('a.good_name',$this->input->post('keyword'));
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

		return $data;
	}

	public function get_goods_type(){
		$this->db->select();
		$this->db->from('goods_type');
		$this->db->where('flag',1);
		return $this->db->get()->result_array();
	}

	public function save_good(){
		$data = array(
			'good_name'=>$this->input->post('good_name'),
			'logo'=>$this->input->post('logo'),
			//'price'=>(int)$this->input->post('price')*100,
			//'old_price'=>(int)$this->input->post('old_price')*100,
			'type_id'=>$this->input->post('type_id'),
			'unit'=>$this->input->post('unit'),
			'percent'=>$this->input->post('percent'),
			'gmxz'=>$this->input->post('gmxz'),
			//'kc'=>$this->input->post('kc'),
			'flag'=>$this->input->post('flag'),
			'demo'=>$this->input->post('demo',true),
			'cdate'=>date('Y-m-d H:i:s',time())
		);
		$this->db->trans_start();//--------开始事务
		if($this->input->post('good_id')){
			unset($data['cdate']);
			$this->db->where('id',$this->input->post('good_id'))->update('goods',$data);
			$g_id = $this->input->post('good_id');
			$this->db->delete('goods_pic', array('good_id' => $g_id));
			$this->db->where('good_id',$g_id)->where_not_in('id',$this->input->post('gg_id'))->delete('goods_gg');
			$this->db->where('good_id',$g_id)->where_not_in('id',$this->input->post('gg_id'))->delete('user_cart');
		}else{
			$this->db->insert('goods',$data);
			$g_id = $this->db->insert_id();
		}

		$pic_short = $this->input->post('pic_short');
		$folder = $this->input->post('folder');
		if($pic_short){
			foreach($pic_short as $idx => $pic) {
				$goods_pic = array(
					'good_id' => $g_id,
					'folder' => $folder[$idx],
					'pic' => str_replace('_thumb', '', $pic),
					'm_pic' => $pic
				);
				$this->db->insert('goods_pic', $goods_pic);
			}
		}
		$arr_gg = $this->input->post('gg');
		$arr_gg_id = $this->input->post('gg_id');
		$arr_gg_price = $this->input->post('gg_price');
		$arr_gg_old_price = $this->input->post('gg_old_price');
		$arr_gg_kc = $this->input->post('gg_kc');
		foreach($arr_gg as $idx => $pic) {
			$goods_gg = array(
				'good_id' => $g_id,
				'gg_name' => $pic,
				'gg_price'=>((float)$arr_gg_price[$idx])*100,
				'gg_old_price'=>((float)$arr_gg_old_price[$idx])*100,
				'gg_kc'=>$arr_gg_kc[$idx]
			);
			if($arr_gg_id[$idx]){
				$this->db->where('id',$arr_gg_id[$idx])->update('goods_gg',$goods_gg);
			}else{
				$this->db->insert('goods_gg', $goods_gg);
			}
		}
		$this->db->trans_complete();//------结束事务
		if ($this->db->trans_status() === FALSE) {
			return -1;
		} else {
			return 1;
		}
	}

	public function del_pic($folder,$pic,$id){
		//echo $id;die;
		if($id){
			$this->db->where('m_pic',$pic);
			$this->db->where('good_id',$id);
			$this->db->delete('goods_pic');
		}
		@unlink('./././upload/goods_pic/'.$folder.'/'.$pic);
		@unlink('./././upload/goods_pic/'.$folder.'/'.str_replace('_thumb', '', $pic));
		$data = array(
			'flag'=>1,
			'pic'=>$pic
		);
		return $data;
	}

	public function get_good_detail($id){
		$this->db->select('a.*,b.type_name')->from('goods a');
		$this->db->join('goods_type b','a.type_id = b.id','left');
		$this->db->where('a.id',$id);
		return $this->db->get()->row_array();
	}

	public function get_good_gg($id){
		$this->db->select('*')->from('goods_gg');
		$this->db->where('good_id',$id);
		return $this->db->get()->result_array();
	}

	public function get_good_pic($id){
		$this->db->select();
		$this->db->from('goods_pic');
		$this->db->where('good_id',$id);
		return $this->db->get()->result();
	}
}
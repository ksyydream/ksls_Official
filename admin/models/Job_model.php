<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 17/4/6
 * Time: 上午10:33
 */
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class Job_model extends MY_Model
{

    public function __construct ()
    {
        parent::__construct();
    }
    public function list_job($page=1){
        $search_date = 'create_time';
        $data['limit'] = $this->limit;
        //获取总记录数 123

        $this->db->select('count(1) num')->from('job a');
        if($this->input->post('keyword')){
            $this->db->like('a.title',$this->input->post('keyword'));
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
        $this->db->select('a.*')->from('job a');
        if($this->input->post('keyword')){
            $this->db->like('a.title',$this->input->post('keyword'));
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

    public function save_job(){
        $user_info = $this->session->userdata('user_info');
        $data = array(
            'title'=>$this->input->post('title'),
            'content'=>$this->input->post('content'),
            'flag'=>$this->input->post('flag'),
            'create_uid'=>$user_info['id'],
            'create_time'=>date('Y-m-d H:i:s',time())
        );
        $this->db->trans_start();//--------开始事务
        if($this->input->post('job_id')){
            unset($data['create_time']);
            unset($data['create_uid']);
            $data['modify_uid']=$user_info['id'];
            $data['modify_time']=date('Y-m-d H:i:s',time());
            $this->db->where('id',$this->input->post('job_id'))->update('job',$data);

        }else{
            $this->db->insert('job',$data);
            $g_id = $this->db->insert_id();
        }
        $this->db->trans_complete();//------结束事务
        if ($this->db->trans_status() === FALSE) {
            return -1;
        } else {
            return 1;
        }
    }

    public function get_job_detail($id){
        $this->db->select('a.*')->from('job a');
        $this->db->where('a.id',$id);
        return $this->db->get()->row_array();
    }

}
<?php
/**
 * Created by PhpStorm.
 * User: bin.shen
 * Date: 5/31/16
 * Time: 23:00
 */

class Pg_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    public function pg_list($page){
        // 每页显示的记录条数，默认20条
        $numPerPage = $this->input->post('numPerPage') ? $this->input->post('numPerPage') : 10;
        $pageNum = $this->input->post('pageNum') ? $this->input->post('pageNum') : $page;

        $this->db->select('count(1) as num');
        $this->db->from('fj_xiaoqu a');
        $this->db->join('fj_xiaoqu_detail b','a.id = b.xiaoqu_id','inner');
        $this->db->join('fj_xiaoqu_type c','b.type_id = c.id','inner');
        if ($this->input->post('xiaoqu')){
            $this->db->like('a.xiaoqu',trim($this->input->post('xiaoqu')));
        }
        $this->db->where(array(
            'a.flag'=>1,
            'b.pgj >'=>0
        ));
        $row = $this->db->get()->row_array();
        //总记录数
        $data['countPage'] = $row['num'];
        $data['xiaoqu'] = $this->input->post('xiaoqu') ? trim($this->input->post('xiaoqu')) : "";
        //list
        $this->db->select('a.*,b.*,c.type_name,b.id detail_id');
        $this->db->from('fj_xiaoqu a');
        $this->db->join('fj_xiaoqu_detail b','a.id = b.xiaoqu_id','inner');
        $this->db->join('fj_xiaoqu_type c','b.type_id = c.id','inner');
        if ($this->input->post('xiaoqu')){
            $this->db->like('a.xiaoqu',trim($this->input->post('xiaoqu')));
        }
        $this->db->where(array(
            'a.flag'=>1,
            'b.pgj >'=>0
        ));
        $this->db->limit($numPerPage, ($pageNum - 1) * $numPerPage );
        $this->db->order_by('a.id', 'desc');
        $data['res_list'] = $this->db->get()->result_array();
        $data['pageNum'] = $pageNum;
        $data['numPerPage'] = $numPerPage;
        return $data;
    }

    public function get_qq(){
        $data = $this->db->select()->from('fj_pg_qq')->where('flag',1)->get()->result_array();
        return $data;
    }

    public function save_msg(){
        $data = array(
            'username'=>trim($this->input->post('username',true)),
            'mobile'=>trim($this->input->post('mobile',true)),
            'label'=>$this->input->post('label'),
            'demo'=>$this->input->post('demo',true),
            'cdate'=>date('Y-m-d H:m:s')
        );
        //保存信息

        $res = $this->db->insert('fj_msg',$data);

        return 1;
    }

    public function get_detail($id){
        $this->db->select('a.*,b.*,c.type_name,b.id detail_id');
        $this->db->from('fj_xiaoqu a');
        $this->db->join('fj_xiaoqu_detail b','a.id = b.xiaoqu_id','inner');
        $this->db->join('fj_xiaoqu_type c','b.type_id = c.id','inner');
        $this->db->where(array(
            'b.id'=>$id
        ));
        $row= $this->db->get()->row_array();
        return $row;
    }

    public function get_xiaoqu(){
        $this->db->distinct();
        $this->db->select('a.xiaoqu name');
        $this->db->from('fj_xiaoqu a');
        $this->db->join('fj_xiaoqu_detail b','a.id = b.xiaoqu_id','inner');
        $this->db->join('fj_xiaoqu_type c','b.type_id = c.id','inner');
        $this->db->where(array(
            'a.flag'=>1,
            'b.pgj >'=>0
        ));
        $data = $this->db->get()->result_array();
        return $data;
    }
}
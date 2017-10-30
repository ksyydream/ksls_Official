<?php
/**
 * Created by PhpStorm.
 * User: bin.shen
 * Date: 6/2/16
 * Time: 21:22
 */

class News_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    public function list_news($page,$num=4) {
        $numPerPage = $this->input->post('numPerPage') ? $this->input->post('numPerPage') : $num;
        $pageNum = $this->input->post('pageNum') ? $this->input->post('pageNum') : $page;

        //获得总记录数
        $this->db->select('count(1) as num');
        $this->db->from('news');
        $rs_total = $this->db->get()->row();
        //总记录数
        $data['countPage'] = $rs_total->num;

        //list
        $this->db->select('*');
        $this->db->from('news');
        $this->db->limit($numPerPage, ($pageNum - 1) * $numPerPage );
        $this->db->order_by('created', 'desc');
        $data['res_list'] = $this->db->get()->result_array();
        //var_dump($this->db->last_query());
        $data['pageNum'] = $pageNum;
        $data['numPerPage'] = $numPerPage;
        return $data;
    }

    public function view_news($id) {
        return $this->db->get_where('news', array('id' => $id))->row_array();
    }

    public function increase_views($id) {
        $this->db->set('viewed', "`viewed` + 1", false);
        $this->db->where('id', $id);
        $this->db->update('news');
    }

    public function save_user($pic) {
        $data = array(
            'title' => $this->input->post('title'),
            'content' => $this->input->post('content'),
            'pic' => $pic,
            'viewed' => 0,
            'created' => date("Y-m-d H:i:s")
        );
        $this->db->trans_start();//--------开始事务
        $this->db->insert('news', $data);
        $this->db->trans_complete();//------结束事务
        if ($this->db->trans_status() === FALSE) {
            return -1;
        } else {
            return 1;
        }
    }

    public function update_user($pic=null){
        $data = array(
            'title' => $this->input->post('title'),
            'content' => $this->input->post('content'),
        );
        if($pic){
            $data['pic'] = $pic;
        }
        $this->db->where('id',$this->input->post('news_id'))->update('news', $data);
    }

    public function delete_news($id){
        $this->db->where('id',$id)->delete('news');
    }
}
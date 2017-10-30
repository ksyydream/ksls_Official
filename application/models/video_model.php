<?php
/**
 * Created by PhpStorm.
 * User: bin.shen
 * Date: 5/31/16
 * Time: 23:00
 */

class Video_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    public function get_video_type_list() {
        return $this->db->get('video_type')->result_array();
    }

    public function get_top_video_list() {
        $this->db->select('a.*, b.name as type_name');
        $this->db->from('video a');
        $this->db->join('video_type b', 'a.type_id = b.id', 'inner');
        $this->db->where('a.is_top', 1);
        $this->db->order_by('a.created', 'desc');
        $this->db->limit(3);
        $this->db->distinct();
        return $this->db->get('video')->result_array();
    }

    public function get_video($id) {
        $user_id = $this->session->userdata('login_user_id');
        $this->db->select('a.*, b.name as type_name, c.id as likeCount, d.id as collectCount');
        $this->db->from('video a');
        $this->db->join('video_type b', 'a.type_id = b.id', 'inner');
        $this->db->join('video_likes c', "a.id = c.video_id and c.user_id = $user_id", 'left');
        $this->db->join('video_collect d', "a.id = d.video_id and d.user_id = $user_id", 'left');
        $this->db->where('a.id', $id);
        $this->db->order_by('a.created', 'desc');
        $this->db->distinct();
        return $this->db->get('video')->row_array();
    }

    public function get_related_video_list($type_id) {
        return $this->db->order_by('is_top', 'desc')->order_by('created', 'desc')->limit(5)->get_where('video', array('type_id' => $type_id))->result_array();
    }

    public function get_video_list($page, $perPage, $type_id=NULL) {
        $numPerPage = $this->input->post('numPerPage') ? $this->input->post('numPerPage') : $perPage;
        $pageNum = $this->input->post('pageNum') ? $this->input->post('pageNum') : $page;

        if($type_id == -1){
            $this->db->select('count(1) as num');
            $this->db->from('video a');
            $this->db->join('video_type b', 'a.type_id = b.id', 'inner');
            $this->db->join('video_collect c', 'a.id = c.video_id', 'inner');
            $this->db->where('c.user_id', $this->session->userdata('login_user_id'));
            if($this->input->post('title')) {
                $this->db->like('a.title',$this->input->post('title'));
            }
            $rs_total = $this->db->get()->row();
            //总记录数
            $data['countPage'] = $rs_total->num;

            //list
            $this->db->select('a.*, b.name as type_name');
            $this->db->from('video a');
            $this->db->join('video_type b', 'a.type_id = b.id', 'inner');
            $this->db->join('video_collect c', 'a.id = c.video_id', 'inner');
            $this->db->where('c.user_id', $this->session->userdata('login_user_id'));
            if($this->input->post('title')) {
                $this->db->like('a.title',$this->input->post('title'));
            }
            $this->db->limit($numPerPage, ($pageNum - 1) * $numPerPage );
            $this->db->order_by('a.created', 'desc');
            $data['res_list'] = $this->db->get()->result_array();
        }else{
            //获得总记录数
            $this->db->select('count(1) as num');
            $this->db->from('video a');
            $this->db->join('video_type b', 'a.type_id = b.id', 'inner');
            if(!empty($type_id)) {
                $this->db->where('a.type_id', $type_id);
            }
            if($this->input->post('title')) {
                $this->db->like('a.title',$this->input->post('title'));
            }
            $rs_total = $this->db->get()->row();
            //总记录数
            $data['countPage'] = $rs_total->num;

            //list
            $this->db->select('a.*, b.name as type_name');
            $this->db->from('video a');
            $this->db->join('video_type b', 'a.type_id = b.id', 'inner');
            if(!empty($type_id)) {
                $this->db->where('a.type_id', $type_id);
            }
            if($this->input->post('title')) {
                $this->db->like('a.title',$this->input->post('title'));
            }
            $this->db->limit($numPerPage, ($pageNum - 1) * $numPerPage );
            $this->db->order_by('a.created', 'desc');
            $data['res_list'] = $this->db->get()->result_array();
            //var_dump($this->db->last_query());

        }
        $data['pageNum'] = $pageNum;
        $data['numPerPage'] = $numPerPage;
        return $data;
    }

    public function increase_data($id, $field, $table = NULL) {

        $this->db->trans_start();//--------开始事务

        $count = 0;
        if(!empty($table)) {
            $user_id = $this->session->userdata('login_user_id');
            $data = $this->db->get_where($table, array('user_id' => $user_id, 'video_id' => $id))->result_array();
            if(empty($data)) {
                $data = array(
                    'video_id' => $id,
                    'user_id' => $user_id
                );
                $this->db->insert($table, $data);
            }

            $this->db->select('count(1) as num');
            $this->db->from($table);
            $result = $this->db->get()->row();
            $count = $result->num;

            $this->db->set($field, $count, false);
        } else {
            $this->db->set($field, "`$field` + 1", false);
        }
        $this->db->where('id', $id);
        $this->db->update('video');

        $this->db->trans_complete();//------结束事务
        if ($this->db->trans_status() === FALSE) {
            return -1;
        } else {
            return 1;
        }
    }

    public function decrease_data($id, $field, $table = NULL) {

        $this->db->trans_start();//--------开始事务

        if(!empty($table)) {
            $this->db->where('video_id', $id);
            $this->db->where('user_id', $this->session->userdata('login_user_id'));
            $this->db->delete($table);

            $this->db->select('count(1) as num');
            $this->db->from($table);
            $result = $this->db->get()->row();
            $count = $result->num;

            $this->db->set($field, $count, false);
        } else {
            $this->db->set($field, "`$field` - 1", false);
        }
        $this->db->where('id', $id);
        $this->db->update('video');

        $this->db->trans_complete();//------结束事务
        if ($this->db->trans_status() === FALSE) {
            return -1;
        } else {
            return 1;
        }
    }
}
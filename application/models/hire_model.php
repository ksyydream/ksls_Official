<?php
/**
 * Created by PhpStorm.
 * User: bin.shen
 * Date: 5/31/16
 * Time: 23:00
 */

class Hire_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    public function hire_list($page){
        // 每页显示的记录条数，默认20条
        $numPerPage = $this->input->post('numPerPage') ? $this->input->post('numPerPage') : 10;
        $pageNum = $this->input->post('pageNum') ? $this->input->post('pageNum') : $page;

        $this->db->select('count(1) as num');
        $this->db->from('hire');
        $this->db->where('user_id',$this->session->userdata('login_user_id'));
        if ($this->input->post('xiaoqu')){
            $this->db->like('xiaoqu',trim($this->input->post('xiaoqu')));
        }
        $row = $this->db->get()->row_array();
        //总记录数
        $data['countPage'] = $row['num'];
        $data['xiaoqu'] = $this->input->post('xiaoqu') ? trim($this->input->post('xiaoqu')) : "";
        //list
        $this->db->select('*');
        $this->db->from('hire');
        $this->db->where('user_id',$this->session->userdata('login_user_id'));
        if ($this->input->post('xiaoqu')){
            $this->db->like('xiaoqu',trim($this->input->post('xiaoqu')));
        }
        $this->db->limit($numPerPage, ($pageNum - 1) * $numPerPage );
        $this->db->order_by('create_time', 'desc');
        $data['res_list'] = $this->db->get()->result_array();
        $data['pageNum'] = $pageNum;
        $data['numPerPage'] = $numPerPage;
        return $data;
    }

    public function hire_deadline_list($page){
        // 每页显示的记录条数，默认20条
        $numPerPage = $this->input->post('numPerPage') ? $this->input->post('numPerPage') : 10;
        $pageNum = $this->input->post('pageNum') ? $this->input->post('pageNum') : $page;
        $now = date('y-m-d h:i:s');
        $this->db->select('count(1) as num');
        $this->db->from('hire');
        $this->db->where(array(
            'user_id' => $this->session->userdata('login_user_id'),
            'flag' => 1,
            'tixin_time <'=>$now
        ));
        if ($this->input->post('xiaoqu')){
            $this->db->like('xiaoqu',$this->input->post('xiaoqu'));
        }
        $row = $this->db->get()->row_array();
        //总记录数
        $data['countPage'] = $row['num'];
        $data['xiaoqu'] = $this->input->post('xiaoqu') ? trim($this->input->post('xiaoqu')) : "";
        //list
        $this->db->select('*');
        $this->db->from('hire');
        $this->db->where(array(
            'user_id' => $this->session->userdata('login_user_id'),
            'flag' => 1,
            'tixin_time <'=>$now
        ));
        if ($this->input->post('xiaoqu')){
            $this->db->like('xiaoqu',trim($this->input->post('xiaoqu')));
        }
        $this->db->limit($numPerPage, ($pageNum - 1) * $numPerPage );
        $this->db->order_by('tixin_time', 'desc');
        $this->db->order_by('id', 'desc');
        $data['res_list'] = $this->db->get()->result_array();
        $data['pageNum'] = $pageNum;
        $data['numPerPage'] = $numPerPage;
        return $data;
    }

    public function add_hire($id = null) {
        $row = $this->db->select()->from('hire')->where(array(
            'id'=>$id,
            'user_id'=>$this->session->userdata('login_user_id')
        ))->get()->row_array();
        return $row;
    }

    public function save_hire(){
        $now = date('y-m-d h:i:s');
        $data = array(
            'xiaoqu' => $this->input->post('xiaoqu'),
            'mianji' => $this->input->post('mianji'),
            'haoma' => $this->input->post('haoma'),
            'fanghao' => $this->input->post('fanghao'),
            'zhuangxiu' => $this->input->post('zhuangxiu'),
            'zhujing' => $this->input->post('zhujing'),
            'create_time' => $now,
            'tixin_time' => $this->input->post('tixin_time')?$this->input->post('tixin_time'):null,
            'user_id' => $this->session->userdata('login_user_id')
        );
        if($this->input->post('flag') || !$this->input->post('tixin_time')){
            $data['flag']=2;
        }else{
            $data['flag']=1;
            /*if(strtotime($now) >= strtotime($this->input->post('tixin_time'))){
                $data['flag']=2;
            }else{
                $data['flag']=1;
            }*/
        }
        if($this->input->post('id')){
            $this->db->where('id',$this->input->post('id'));
            $this->db->where('user_id',$this->session->userdata('login_user_id'));
            return $this->db->update('hire',$data);
        }else{
           return $this->db->insert('hire',$data);
        }
    }

    public function delete_hire($id=null){
        $this->db->where('id',$id);
        $this->db->where('user_id',$this->session->userdata('login_user_id'));
        $this->db->delete('hire');
    }
}
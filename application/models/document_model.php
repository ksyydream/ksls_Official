<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 16/6/1
 * Time: 下午1:21
 */
class Document_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    function get_forum_type(){
        $res = $this->db->select()->from('forum_type')
            ->where('flag',1)->get()->result_array();
        if(!$res){
            return 1;
        }else{
            return $res;
        }
    }

    public function list_doc($page=1,$typeid=null) {
        // 每页显示的记录条数，默认20条
        $numPerPage = $this->input->post('numPerPage') ? $this->input->post('numPerPage') : 10;
        $pageNum = $this->input->post('pageNum') ? $this->input->post('pageNum') : $page;

        if(($typeid && $typeid ==-1) || ($this->input->post('type') && $this->input->post('type') == -1)){
            $this->db->select('count(1) as num');
            $this->db->from('ticket_house a');
            $this->db->join('ticket b','a.doc_id = b.id','inner');
            $this->db->join('forum_type c','b.type = c.id','inner');
            if($this->input->post('title'))
                $this->db->like('b.title',trim($this->input->post('title')));
            $this->db->where('a.user_id',$this->session->userdata('login_user_id'));
            $this->db->where('b.pass',2);
            $this->db->where('c.flag',1);
            $row = $this->db->get()->row_array();
            //总记录数
            $data['countPage'] = $row['num'];
            //list
            $this->db->select('b.*');
            $this->db->from('ticket_house a');
            $this->db->join('ticket b','a.doc_id = b.id','inner');
            $this->db->join('forum_type c','b.type = c.id','inner');
            if($this->input->post('title'))
                $this->db->like('b.title',trim($this->input->post('title')));

            $this->db->where('c.flag',1);
            $this->db->where('a.user_id',$this->session->userdata('login_user_id'));
            $this->db->where('b.pass',2);
            $this->db->limit($numPerPage, ($pageNum - 1) * $numPerPage );
            $this->db->order_by('a.cdate', 'desc');
            $data['res_list'] = $this->db->get()->result_array();
            $data['pageNum'] = $pageNum;
            $data['numPerPage'] = $numPerPage;
            return $data;
        }
        $this->db->select('count(1) as num');
        $this->db->from('ticket a');
        $this->db->join('forum_type b','a.type = b.id','inner');
        if($typeid && $typeid > 0)
            $this->db->where('a.type',$typeid);
        if($this->input->post('type') && $this->input->post('type') > 0)
            $this->db->where('a.type',$this->input->post('type'));
        if($this->input->post('title'))
            $this->db->like('a.title',trim($this->input->post('title')));
        if(($typeid && $typeid == -2) || ($this->input->post('type') && $this->input->post('type') == -2)){
            $this->db->where('user_id',$this->session->userdata('login_user_id'));
        }else{
            $this->db->where('b.flag',1);
            $this->db->where('a.pass',2);
        }
        $row = $this->db->get()->row_array();
        //总记录数
        $data['countPage'] = $row['num'];
        //list
        $this->db->select('a.*');
        $this->db->from('ticket a');
        $this->db->join('forum_type b','a.type = b.id','inner');
        if($typeid && $typeid > 0)
            $this->db->where('a.type',$typeid);
        if($this->input->post('type') && $this->input->post('type') > 0)
            $this->db->where('a.type',$this->input->post('type'));
        if($this->input->post('title'))
            $this->db->like('a.title',trim($this->input->post('title')));
        if(($typeid && $typeid == -2) || ($this->input->post('type') && $this->input->post('type') == -2)){
            $this->db->where('a.user_id',$this->session->userdata('login_user_id'));
        }else{
            $this->db->where('b.flag',1);
            $this->db->where('a.pass',2);
        }
        $this->db->limit($numPerPage, ($pageNum - 1) * $numPerPage );
        $this->db->order_by('a.cdate', 'desc');


        $data['res_list'] = $this->db->get()->result_array();
        $data['pageNum'] = $pageNum;
        $data['numPerPage'] = $numPerPage;
        return $data;
    }

    public function save_ticket(){
        $data = array(
            'title' => $this->input->post('title'),
            'content' => $this->input->post('elm1'),
            'type' => $this->input->post('type'),
            'user_id' => $this->session->userdata('login_user_id'),
            'pass'=>1
        );
        if(!$this->input->post('id')){
            $data['cdate'] =  date('Y-m-d H:i:s');
            $this->db->insert('ticket',$data);
            if($this->session->userdata('login_user_id')==24){
                $data = array(
                    'first' => array(
                        'value' => $this->input->post('title'),
                        'color' => '#FF0000'
                    ),
                    'keyword1' => array(
                        'value' => $this->input->post('title'),
                        'color' => '#FF0000'
                    ),
                    'keyword2' => array(
                        'value' => date("Y-m-d H:i:s"),
                        'color' => '#FF0000'
                    ),
                    'remark' => array(
                        'value' => '请审核！',
                        'color' => '#FF0000'
                    )
                );
                $this->wxpost($this->config->item('WX_SJTJ'),$data,$this->session->userdata('login_user_id'));
            }

            return 1;
        }else{
            if(in_array(4,$this->session->userdata('login_position_id_array'))){
                unset($data['pass']);
            }
            $this->db->where('id',$this->input->post('id'));
            $this->db->update('ticket',$data);
            $row = $this->db->select()->from('ticket')->where('id',$this->input->post('id'))->get()->row_array();
            if($row){
                return $row['pass'];
            }else{
                return 1;
            }
        }

    }

    public function view_doc($id) {
        $this->look_doc_one_time($id);
        $this->db->select('a.*,b.name type_name')->from('ticket a');
        $this->db->join('forum_type b','a.type = b.id','left');
        $this->db->where('a.id',$id);
        $data = $this->db->get()->row_array();
        //die(var_dump($data));
        return $data;
    }

    public function recomment_doc(){
        $this->db->select('a.*,b.name type_name')->from('ticket a');
        $this->db->join('forum_type b','a.type = b.id','inner');
        $this->db->where('b.flag',1);
        $this->db->where('a.pass',2);
        $this->db->limit(6, 0);
        $data = $this->db->order_by('a.cdate','desc')->get()->result_array();
        if(!$data){
            return 1;
        }
        return $data;
    }

    public function house_likes($id){
        $likes = $this->db->select()->from('ticket_likes')
            ->where(array(
                'doc_id'=>$id,
                'user_id'=>$this->session->userdata('login_user_id')
            ))->get()->row_array();
        if($likes){
            $data['likes']='on';
        }else{
            $data['likes']='';
        }
        $house = $this->db->select()->from('ticket_house')
            ->where(array(
                'doc_id'=>$id,
                'user_id'=>$this->session->userdata('login_user_id')
            ))->get()->row_array();
        if($house){
            $data['house']='on';
        }else{
            $data['house']='';
        }
        return $data;
    }

    public function look_doc_one_time($id){
        $this->db->set('look','look + 1',false);
        $this->db->where('id',$id);
        $this->db->update('ticket');
    }

    public function likes_doc_one_time($id){

        $res = $this->db->select()->from('ticket_likes')
            ->where(array(
                'doc_id'=>$id,
                'user_id'=>$this->session->userdata('login_user_id')
            ))->get()->row_array();
        if($res){
            $this->db->set('likes','likes - 1',false);
            $this->db->where('id',$id);
            $this->db->where('likes >',0);
            $this->db->update('ticket');

            $this->db->delete('ticket_likes',array(
                'doc_id'=>$id,
                'user_id'=>$this->session->userdata('login_user_id'),
            ));
            return 2;
        }else{
            $this->db->set('likes','likes + 1',false);
            $this->db->where('id',$id);
            $this->db->update('ticket');

            $this->db->insert('ticket_likes',array(
                'doc_id'=>$id,
                'user_id'=>$this->session->userdata('login_user_id'),
                'cdate' => date('Y-m-d H:i:s')
            ));
            return 1;
        }
    }

    public function house_doc_one_time($id){
        $res = $this->db->select()->from('ticket_house')
            ->where(array(
                'doc_id'=>$id,
                'user_id'=>$this->session->userdata('login_user_id')
            ))->get()->row_array();
        if($res){
            $this->db->set('house','house - 1',false);
            $this->db->where('id',$id);
            $this->db->where('house >',0);
            $this->db->update('ticket');

            $this->db->delete('ticket_house',array(
                'doc_id'=>$id,
                'user_id'=>$this->session->userdata('login_user_id'),
            ));
            return 2;
        }else{
            $this->db->set('house','house + 1',false);
            $this->db->where('id',$id);
            $this->db->update('ticket');

            $this->db->insert('ticket_house',array(
                'doc_id'=>$id,
                'user_id'=>$this->session->userdata('login_user_id'),
                'cdate' => date('Y-m-d H:i:s')
            ));
            return 1;
        }
    }

    public function get_type_name($typeid){
        if(!$typeid){
            return '全部文档';
        }
        if($typeid == -1){
            return '我的收藏';
        }
        if($typeid == -2){
            return '我的发布';
        }

       $row = $this->db->select()->from('forum_type')->where('id',$typeid)->get()->row_array();
        if($row){
            return $row['name'];
        }else{
            return '未找到类别';
        }
    }

    public function save_file(){
        if (is_readable('./././uploadfiles/doc') == false) {
            mkdir('./././uploadfiles/doc');
        }

        $config['upload_path']="./uploadfiles/doc";
        $config['allowed_types']="jpg|gif|png|jpeg|doc|docx|pdf|xlsx|xls";
        $config['encrypt_name'] = true;
        $config['max_size'] = '20000';
        //$config['encrypt_name']=true;
        $this->load->library('upload',$config);
        if( !$this->upload->do_upload('file')){
           // die(var_dump($this->upload->display_errors()));
            return 3;  //文件上传失败
        }
        $data=$this->upload->data();

        $modeldata=array(
            'title'=>$this->input->post('title'),
            'type'=>$this->input->post('type'),
            'file'=>$data['file_name'],
            'oldfile'=>$data['client_name'],
            'user_id'=>$this->session->userdata('login_user_id'),
            'cdate'=>date("y-m-d H:i:s",time()),
            'pass'=>1
        );
        $res=$this->db->insert('ticket',$modeldata);
        if ($res){
            return 1;
        }
        else{
            return 4;
        }

    }

    public function download($id){

        $this->load->helper('download');
        $this->load->helper('file');
        $data=$this->db->select()->from('ticket')->where('id',$id)->get()->row_array();
        if ($data){
            $string = read_file('./uploadfiles/doc/'.$data['file']);
            //   $file_name='./uploadfiles/'.$data['url'];//需要下载的文件
            force_download($data['oldfile'],$string);
        }
    }
    ////////////////////////////////////////////////////////////////////////////////
    //ajax删除图片
    public function del_pic($folder,$style,$pic,$id){
        //echo $id;die;
        if($id){
            $this->db->where('pic_short',$pic);
            $this->db->delete('agenda_image');
        }
        @unlink('./././uploadfiles/agenda/'.$folder.'/'.$style.'/'.$pic);
        @unlink('./././uploadfiles/agenda/'.$folder.'/'.$style.'/'.str_replace('_thumb', '', $pic));
        $data = array(
            'flag'=>1,
            'pic'=>$pic
        );
        return $data;
    }

    public function del_doc($doc_id) {
        $this->db->trans_start();//--------开始事务
        $row = $this->db->select()->from('ticket')->where('id',$doc_id)->get()->row_array();
        if(in_array(4,$this->session->userdata('login_position_id_array')) || $row['user_id']==$this->session->userdata('login_user_id')){
            $this->db->delete('ticket_likes',array('doc_id' => $doc_id));
            $this->db->delete('ticket_house',array('doc_id' => $doc_id));
            $this->db->delete('ticket', array('id' => $doc_id));
        }


        $this->db->trans_complete();//------结束事务
        if ($this->db->trans_status() === FALSE) {
            return -1;
        } else {
            return 1;
        }
    }

    public function list_doc_nopass($page=1,$typeid=null) {
        // 每页显示的记录条数，默认20条
        $numPerPage = $this->input->post('numPerPage') ? $this->input->post('numPerPage') : 10;
        $pageNum = $this->input->post('pageNum') ? $this->input->post('pageNum') : $page;

        $this->db->select('count(1) as num');
        $this->db->from('ticket a');
        $this->db->join('forum_type b','a.type = b.id','inner');
        if($typeid && $typeid > 0)
            $this->db->where('a.type',$typeid);
        if($this->input->post('type') && $this->input->post('type') > 0)
            $this->db->where('a.type',$this->input->post('type'));
        if($this->input->post('title'))
            $this->db->like('a.title',trim($this->input->post('title')));
        if(($typeid && $typeid == -2) || ($this->input->post('type') && $this->input->post('type') == -2)){
            $this->db->where('user_id',$this->session->userdata('login_user_id'));
        }else{
            $this->db->where('b.flag',1);
            $this->db->where('a.pass',1);
        }
        $row = $this->db->get()->row_array();
        //总记录数
        $data['countPage'] = $row['num'];
        //list
        $this->db->select('a.*');
        $this->db->from('ticket a');
        $this->db->join('forum_type b','a.type = b.id','inner');
        if($typeid && $typeid > 0)
            $this->db->where('a.type',$typeid);
        if($this->input->post('type') && $this->input->post('type') > 0)
            $this->db->where('a.type',$this->input->post('type'));
        if($this->input->post('title'))
            $this->db->like('a.title',trim($this->input->post('title')));
        if(($typeid && $typeid == -2) || ($this->input->post('type') && $this->input->post('type') == -2)){
            $this->db->where('a.user_id',$this->session->userdata('login_user_id'));
        }else{
            $this->db->where('b.flag',1);
            $this->db->where('a.pass',1);
        }
        $this->db->limit($numPerPage, ($pageNum - 1) * $numPerPage );
        $this->db->order_by('a.cdate', 'desc');


        $data['res_list'] = $this->db->get()->result_array();
        $data['pageNum'] = $pageNum;
        $data['numPerPage'] = $numPerPage;
        return $data;
    }

    public function pass_doc($doc_id) {
        $this->db->trans_start();//--------开始事务

        $this->db->where('id',$doc_id)->update('ticket', array('pass' => 2));

        $this->db->trans_complete();//------结束事务
        if ($this->db->trans_status() === FALSE) {
            return -1;
        } else {
            return 1;
        }
    }

    public function get_doc($id){
        if(in_array(4,$this->session->userdata('login_position_id_array'))){
            $this->db->select('a.*,b.name type_name')->from('ticket a');
            $this->db->join('forum_type b','a.type = b.id','left');
            $this->db->where('a.id',$id);
            $this->db->where('a.type <>',6);
            $data = $this->db->get()->row_array();
            return $data;
        }else{
            $this->db->select('a.*,b.name type_name')->from('ticket a');
            $this->db->join('forum_type b','a.type = b.id','left');
            $this->db->where('a.id',$id);
            $this->db->where('a.type <>',6);
            $this->db->where('a.pass',1);
            $this->db->where('a.user_id',$this->session->userdata('login_user_id'));
            $data = $this->db->get()->row_array();
            return $data;
        }
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: bin.shen
 * Date: 6/8/16
 * Time: 17:07
 */

class Examination_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    public function get_question_type_list()
    {
        return $this->db->get('question_type')->result_array();
    }

    public function mark_exam($id){

        $data['exam_main'] = $this->db->select()->from('self_exam')->where('id',$id)->get()->row_array();
        $data['exam_model'] = $this->db->select()->from('exam')->where('id',$data['exam_main']['model_exam_id'])->get()->row_array();
       /*echo $this->db->last_query();
        die(var_dump($data['exam_model']));*/
        $this->db->select('a.id,a.answer,a.score,b.title');
        $this->db->from('self_exam_question a');
        $this->db->join('exam_question b','a.question_id = b.id','inner');
        $this->db->where('a.exam_id',$id);
        $data['exam_detail'] = $this->db->get()->result_array();
        return $data;

    }

    public function mark_list($page){
        $user_id = $this->session->userdata('login_user_id');
        $numPerPage = $this->input->post('numPerPage') ? $this->input->post('numPerPage') : 10;
        $pageNum = $this->input->post('pageNum') ? $this->input->post('pageNum') : $page;
        $this->db->select('count(1) as num');
        $this->db->from('self_exam a');
        $this->db->join('exam b','a.model_exam_id = b.id','inner');
        $this->db->join('user c','a.user_id = c.id','inner');
        $this->db->where('b.user_id',$user_id);
        $this->db->where('a.type_id',-1);
        if($this->input->post('complete')==1){
            $this->db->where_in('a.complete',array(1));
        }elseif($this->input->post('complete')==2){
            $this->db->where_in('a.complete',array(2));
        }else{
            $this->db->where_in('a.complete',array(1,2));
        }
        if($this->input->post('exam_id'))
            $this->db->where('b.id',$this->input->post('exam_id'));
        $this->db->order_by('a.id', 'desc');
        $row = $this->db->get()->row_array();
        //总记录数
        $data['countPage'] = $row['num'];
        $data['complete'] = $this->input->post('complete')?$this->input->post('complete'):'';
        $data['exam_id'] = $this->input->post('exam_id')?$this->input->post('exam_id'):'';
        $this->db->select('b.style,a.*,c.rel_name,IFNULL(b.p_num * b.p_score,100) as allscore',false);
        $this->db->from('self_exam a');
        $this->db->join('exam b','a.model_exam_id = b.id','inner');
        $this->db->join('user c','a.user_id = c.id','inner');
        $this->db->where('b.user_id',$user_id);
        $this->db->where('a.type_id',-1);
        if($this->input->post('complete')==1){
            $this->db->where_in('a.complete',array(1));
        }elseif($this->input->post('complete')==2){
            $this->db->where_in('a.complete',array(2));
        }else{
            $this->db->where_in('a.complete',array(1,2));
        }
        if($this->input->post('exam_id'))
            $this->db->where('b.id',$this->input->post('exam_id'));
        $this->db->order_by('a.id', 'desc');
        $this->db->limit($numPerPage, ($pageNum - 1) * $numPerPage );
        $data['res_list'] = $this->db->get()->result_array();
        $data['pageNum'] = $pageNum;
        $data['numPerPage'] = $numPerPage;
        $this->db->select();
        $this->db->from('exam');
        $this->db->where(array(
            'user_id'=>$user_id,
            'flag'=>2
        ));
        $this->db->order_by('id', 'desc');
        $data['exam_list'] = $this->db->get()->result_array();
        return $data;

    }

    public function get_type(){
        $this->db->from('question_type');
        $data = $this->db->get()->result_array();
        if($data){
            return $data;
        }else{
            return 1;
        }
    }

    public function get_user_exam($user_id, $type_id)
    {
        $this->db->select();
        $this->db->from('self_exam');
        $this->db->where('type_id',$type_id);
        $this->db->where('user_id',$user_id);
        $this->db->where('complete',0);
        if($type_id < 0){
            $this->db->where('model_exam_id',$this->input->post('exam_id'));
        }
        return $this->db->get()->row_array();
    }

    public function gen_exam_data($user_id, $type_id, $limit=20) {
        $this->db->trans_start();//--------开始事务
        if($type_id == -1){
            $title = $this->db->select()->from('exam')->where('id',$this->input->post('exam_id'))->get()->row_array();
            $this->db->from('exam_question');
            $this->db->where('exam_id', $this->input->post('exam_id'));
            $this->db->order_by('style','asc');
            $questions = $this->db->get()->result_array();
        }else{
            $title['title']="";
            $title['id']="";
            $title['style']=1;
            $this->db->from('question');
            $this->db->where('type_id', $type_id);
            $this->db->where_in('style',array(1,2));
            $this->db->order_by('RAND()');
            $this->db->limit($limit);
            $questions = $this->db->get()->result_array();
        }

        $exam_data = array();
        if(!empty($questions)) {
            $data = array(
                'user_id' => $user_id,
                'type_id' => $type_id,
                'title' => $title['title'] ? $title['title'] : '自助考试-' . date('YmdHis'),
                'model_exam_id'=>$title['id'] ? $title['id'] : -1,
                'complete' => 0,
                'style'=>$title['style'],
                'created' => date("Y-m-d H:i:s")
            );
            $this->db->insert('self_exam', $data);
            $exam_id = $this->db->insert_id();

            foreach ($questions as $q) {
                $exam_data[] = array(
                    'exam_id' => $exam_id,
                    'question_id' => $q['id'],
                    'complete' => 0
                );
            }
            $this->db->insert_batch('self_exam_question', $exam_data);
        }

        $this->db->trans_complete();//------结束事务
        if ($this->db->trans_status() === FALSE) {
            return -1;
        } else {
            return $exam_id;
        }
    }

    public function get_exam_by_num($exam_id, $num) {
        $row = $this->db->select()->from('self_exam')->where('id',$exam_id)->get()->row_array();
        if($row['type_id']==-1){
            $this->db->select('d.p_score,a.score allscore,b.score,b.answer,c.style,a.complete,c.title, c.op1, c.op2, c.op3, c.op4, a.type_id, a.title AS question_type,
             c.as1 true_as1,c.as2 true_as2,c.as3 true_as3,c.as4 true_as4,
            b.as1, b.as2, b.as3, b.as4, b.id AS eq_id');
            $this->db->from('self_exam a');
            $this->db->join('self_exam_question b', 'a.id = b.exam_id', 'inner');
            $this->db->join('exam_question c', 'b.question_id = c.id', 'inner');
            $this->db->join('exam d','d.id = a.model_exam_id','left');
            $this->db->where('a.id', $exam_id);
            $this->db->order_by('b.id ASC');
            $this->db->limit(1);
            $this->db->offset($num-1);
            $data['question_detail'] = $this->db->get()->row_array();
        }else{
            $this->db->select('a.type_id,c.style,a.complete,c.title, c.op1, c.op2, c.op3, c.op4, c.type_id, d.name AS question_type,
             c.as1 true_as1,c.as2 true_as2,c.as3 true_as3,c.as4 true_as4,
            b.as1, b.as2, b.as3, b.as4, b.id AS eq_id');
            $this->db->from('self_exam a');
            $this->db->join('self_exam_question b', 'a.id = b.exam_id', 'inner');
            $this->db->join('question c', 'b.question_id = c.id', 'inner');
            $this->db->join('question_type d', 'c.type_id = d.id', 'inner');
            $this->db->where('a.id', $exam_id);
            $this->db->order_by('b.id ASC');
            $this->db->limit(1);
            $this->db->offset($num-1);
            $data['question_detail'] =  $this->db->get()->row_array();
        }

        $this->db->select('count(1) as num');
        $this->db->from('self_exam_question a');
        $this->db->where('a.exam_id',$exam_id);
        $row_count = $this->db->get()->row_array();
        $data['num'] = $row_count['num'];
        return $data;
    }

    public function get_exam_question($exam_id) {
        return $this->db->get_where('self_exam_question', array('exam_id' => $exam_id))->result_array();
    }

    public function take_exam($eq_id) {
        $this->db->trans_start();//--------开始事务
        $data = array();
        if($this->input->post('style')==3){
            $this->db->where('id', $eq_id);
            $this->db->update('self_exam_question', array(
                'answer'=>$this->input->post('answer'),
                'complete'=>1
                ));
        }else{
            if(is_array($this->input->post('option'))){
                $ops = $this->input->post('option');
                foreach($ops as $item){
                    if($item == 'A') {
                        $data['as1'] = 1;
                    } else if($item == 'B') {
                        $data['as2'] = 1;
                    } else if($item == 'C') {
                        $data['as3'] = 1;
                    } else if($item == 'D') {
                        $data['as4'] = 1;
                    }
                }
                $this->db->where('id', $eq_id);
                $this->db->update('self_exam_question', $data);
                $row = $this->db->select()->from('self_exam_question')
                    ->where(array(
                        'id'=>$eq_id,
                        'as1'=>0,
                        'as2'=>0,
                        'as3'=>0,
                        'as4'=>0
                    ))->get()->row_array();
                if(!$row){
                    $this->db->where('id', $eq_id);
                    $this->db->update('self_exam_question', array('complete'=>1));
                }else{
                    $this->db->where('id', $eq_id);
                    $this->db->update('self_exam_question', array('complete'=>0));
                }
            }else{
                if($this->input->post('option') == 'A') {
                    $data['as1'] = 1;
                    $data['complete'] = 1;
                } else if($this->input->post('option') == 'B') {
                    $data['as2'] = 1;
                    $data['complete'] = 1;
                } else if($this->input->post('option') == 'C') {
                    $data['as3'] = 1;
                    $data['complete'] = 1;
                } else if($this->input->post('option') == 'D') {
                    $data['as4'] = 1;
                    $data['complete'] = 1;
                }
                $this->db->where('id', $eq_id);
                $this->db->update('self_exam_question', $data);
            }
        }



        $this->db->trans_complete();//------结束事务
        if ($this->db->trans_status() === FALSE) {
            return -1;
        } else {
            return 1;
        }
    }

    public function complete_examination($exam_id) {
        $this->db->trans_start();//--------开始事务
        $exam_main = $this->db->select()->from('self_exam')->where('id',$exam_id)->get()->row_array();
        if($exam_main['style']==3){
            $this->db->where('id', $exam_id);
            $this->db->update('self_exam', array(
                'complete' => 1
            ));
        }else{
            if($exam_main['type_id'] < 0){
                $model_exam = $this->db->select()->from('exam')->where('id',$exam_main['model_exam_id'])->get()->row_array();
                $all_score = intval($model_exam['p_num']) * intval($model_exam['p_score']);
                $this->db->select('count(1) as num');
                $this->db->from('self_exam_question a');
                $this->db->join('exam_question b','b.id = a.question_id','inner');
                $this->db->where('a.exam_id',$exam_id);
                $q_all_num =$this->db->get()->row_array();
                $this->db->select('count(1) as num');
                $this->db->from('self_exam_question a');
                $this->db->join('exam_question b','b.id = a.question_id and a.as1 = b.as1 and a.as2 = b.as2 and a.as3 = b.as3 and a.as4 = b.as4','inner');
                $this->db->where('a.exam_id',$exam_id);
                $q_true_num =$this->db->get()->row_array();

                $scroe = 0;
                if($q_all_num['num']!=0){
                    if($q_all_num['num']==$q_true_num['num']){
                        $scroe = $all_score;
                    }else{
                        $scroe = floor($all_score * ($q_true_num['num']/$q_all_num['num']));
                    }
                }
            }else{
                $this->db->select('count(1) as num');
                $this->db->from('self_exam_question a');
                $this->db->join('question b','b.id = a.question_id','inner');
                $this->db->where('a.exam_id',$exam_id);
                $q_all_num =$this->db->get()->row_array();
                $this->db->select('count(1) as num');
                $this->db->from('self_exam_question a');
                $this->db->join('question b','b.id = a.question_id and a.as1 = b.as1 and a.as2 = b.as2 and a.as3 = b.as3 and a.as4 = b.as4','inner');
                $this->db->where('a.exam_id',$exam_id);
                $q_true_num =$this->db->get()->row_array();
                $scroe = 0;
                if($q_all_num['num']!=0){
                    if($q_all_num['num']==$q_true_num['num']){
                        $scroe = 100;
                    }else{
                        $scroe = floor(100 * ($q_true_num['num']/$q_all_num['num']));
                    }
                }

            }
            $this->db->where('id', $exam_id);
            $this->db->update('self_exam', array(
                'complete' => 2,
                'score' => $scroe

            ));
        }





        $this->db->trans_complete();//------结束事务
        if ($this->db->trans_status() === FALSE) {
            return -1;
        } else {
            return 1;
        }
    }

    public function get_true_exam_question($exam_id) {
        $row = $this->db->select()->from('self_exam')->where('id',$exam_id)->get()->row_array();
        if($row['type_id']==-1){
            $this->db->select('a.question_id,c.as1,c.as2,c.as3,c.as4,
        a.as1 self_as1,a.as2 self_as2,a.as3 self_as3,a.as4 self_as4');
            $this->db->from('self_exam_question a');
            $this->db->join('exam_question c', 'a.question_id = c.id', 'inner');
            $this->db->where('a.exam_id',$exam_id);
            return $this->db->get()->result_array();
        }else{
            $this->db->select('a.question_id,c.as1,c.as2,c.as3,c.as4,
        a.as1 self_as1,a.as2 self_as2,a.as3 self_as3,a.as4 self_as4');
            $this->db->from('self_exam_question a');
            $this->db->join('question c', 'a.question_id = c.id', 'inner');
            $this->db->where('a.exam_id',$exam_id);
            return $this->db->get()->result_array();
        }

    }

    public function count_question($exam_id){
        $this->db->select('count(1) as num');
        $this->db->from('self_exam_question a');
        $this->db->where('a.exam_id',$exam_id);
        $row_count = $this->db->get()->row_array();
        return $row_count['num'];
    }

    public function chenge_option($eq_id,$val,$as,$style){
        if($style==1){
            $data = array('as1' => 0, 'as2' => 0, 'as3' => 0, 'as4' => 0, 'complete' => 0);
            if($val == 'A') {
                $data['as1'] = $as;
                $data['complete'] = 1;
            } else if($val == 'B') {
                $data['as2'] = $as;
                $data['complete'] = 1;
            } else if($val == 'C') {
                $data['as3'] = $as;
                $data['complete'] = 1;
            } else if($val == 'D') {
                $data['as4'] = $as;
                $data['complete'] = 1;
            }
            $this->db->where('id', $eq_id);
            $this->db->update('self_exam_question', $data);
        }else{
            $data = array();
            if($val == 'A') {
                $data['as1'] = $as;
            } else if($val == 'B') {
                $data['as2'] = $as;
            } else if($val == 'C') {
                $data['as3'] = $as;
            } else if($val == 'D') {
                $data['as4'] = $as;
            }
            $this->db->where('id', $eq_id);
            $this->db->update('self_exam_question', $data);
            $row = $this->db->select()->from('self_exam_question')
                ->where(array(
                    'id'=>$eq_id,
                    'as1'=>0,
                    'as2'=>0,
                    'as3'=>0,
                    'as4'=>0
                ))->get()->row_array();
            if(!$row){
                $this->db->where('id', $eq_id);
                $this->db->update('self_exam_question', array('complete'=>1));
            }else{
                $this->db->where('id', $eq_id);
                $this->db->update('self_exam_question', array('complete'=>0));
            }
        }

    }

    public function get_exam_data(){
        $this->db->select('*')->from('exam')->where(array(
                'user_id' => $this->session->userdata('login_user_id'),
                'flag'=>1
        ));
        $res = $this->db->order_by('id','desc')->get()->row_array();
        if(!$res){
            return -1;
        }
        $data['exam_main'] = $res;
        $this->db->select()->from('exam_question');
        $data['exam_list'] = $this->db->where('exam_id',$res['id'])->order_by('style','asc')->get()->result_array();
        $this->db->select('count(1) as num');
        $this->db->where('exam_id',$res['id']);
        $this->db->from('exam_question');
        $num = $this->db->get()->row_array();
        $data['exam_num'] = $num['num'];
        return $data;
    }

    public function list_question($page=1,$typeid=null) {
        // 每页显示的记录条数，默认20条
        $this->db->select('*')->from('exam')->where(array(
            'user_id' => $this->session->userdata('login_user_id'),
            'flag'=>1
        ));
        $res = $this->db->order_by('id','desc')->get()->row_array();
        if(!$res){
            $style=1;
        }else{
            $style=$res['style'];
        }

        $numPerPage = $this->input->post('numPerPage') ? $this->input->post('numPerPage') : 10;
        $pageNum = $this->input->post('pageNum') ? $this->input->post('pageNum') : $page;

        $exam_id = $this->get_news_exam_id();
        $this->db->select('count(1) as num');
        $this->db->from('question a');
        $this->db->join('exam_question b',"a.id = b.question_id and b.exam_id = {$exam_id}",'left');
        $this->db->where('a.type_id',$typeid);
        $this->db->where('a.flag',1);
        if(in_array($style,array(1,2))){
            $this->db->where_in('a.style',array(1,2));
        }else{
            $this->db->where_in('a.style',array(3));
        }
        $row = $this->db->get()->row_array();
        //总记录数
        $data['countPage'] = $row['num'];

        //list
        $this->db->select('a.*,b.id eq_id');
        $this->db->from('question a');
        $this->db->join('exam_question b',"a.id = b.question_id and b.exam_id = {$exam_id}",'left');
        $this->db->where('a.type_id',$typeid);
        $this->db->where('a.flag',1);
        if(in_array($style,array(1,2))){
            $this->db->where_in('a.style',array(1,2));
        }else{
            $this->db->where_in('a.style',array(3));
        }
        $this->db->limit($numPerPage, ($pageNum - 1) * $numPerPage );
        $this->db->order_by('a.style','asc');
        $this->db->order_by('a.id', 'desc');
        $data['res_list'] = $this->db->get()->result_array();
        $data['pageNum'] = $pageNum;
        $data['numPerPage'] = $numPerPage;
        return $data;
    }

    public function save_question(){
        $data = array(
            'type_id' => $this->input->post('type_id'),
            'style' => $this->input->post('style'),
            'title' => $this->input->post('title'),
            'op1' => $this->input->post('op1'),
            'op2' => $this->input->post('op2'),
            'op3' => $this->input->post('op3'),
            'op4' => $this->input->post('op4')
        );
        if($this->input->post('style')==1){
            $data['as1'] = $this->input->post('as1')?$this->input->post('as1'):0;
            $data['as2'] = $this->input->post('as2')?$this->input->post('as2'):0;
            $data['as3'] = $this->input->post('as3')?$this->input->post('as3'):0;
            $data['as4'] = $this->input->post('as4')?$this->input->post('as4'):0;
        }
        if($this->input->post('style')==2){
            $data['as1'] = $this->input->post('as11')?$this->input->post('as11'):0;
            $data['as2'] = $this->input->post('as12')?$this->input->post('as12'):0;
            $data['as3'] = $this->input->post('as13')?$this->input->post('as13'):0;
            $data['as4'] = $this->input->post('as14')?$this->input->post('as14'):0;
        }

      return  $this->db->insert('question',$data);

    }

    public function save_exam_main(){

        $data = array(
            'user_id' => $this->session->userdata('login_user_id'),
            'company_id' => $this->session->userdata('login_company_id'),
            'permission_id' =>$this->session->userdata('login_permission_id'),
            'title' => $this->input->post('title'),
            'p_num' => $this->input->post('p_num'),
            'style' => $this->input->post('style'),
            'start_time' => $this->input->post('start_date'),
            'end_time' => $this->input->post('end_date'),
            'p_score' => $this->input->post('p_score')
        );
        $this->db->trans_start();//--------开始事务
        $this->db->insert('exam',$data);
        $insert_id = $this->db->insert_id();
        $subsidiary_id_array = $this->session->userdata('login_subsidiary_id_array');
        foreach($subsidiary_id_array as $item){
            $this->db->insert('exam_subsidiary',array(
                'exam_id' => $insert_id,
                'subsidiary_id' => $item
            ));
        }
        $this->db->trans_complete();//------结束事务
        if ($this->db->trans_status() === FALSE) {
            return -1;
        } else {
            return $insert_id;
        }

    }

    public function add_question($id){
        $this->db->select('*')->from('exam')->where(array(
            'user_id' => $this->session->userdata('login_user_id'),
            'flag'=>1
        ));
        $res = $this->db->order_by('id','desc')->get()->row_array();
        if(!$res){
            return -1;
            exit();
        }
        $exam_id = $res['id'];
        $res_row = $this->db->select()->from('exam_question')->where('exam_id',$exam_id)
            ->where('question_id',$id)->get()->row_array();
        if($res_row){
            return -2;
            exit();
        }
        $this->db->select('count(1) as num');
        $this->db->where('exam_id',$exam_id);
        $this->db->from('exam_question');
        $num_old = $this->db->get()->row_array();
        if($num_old['num']>= $res['p_num']){
            return -4;
            exit();
        }
        $this->db->trans_start();//--------开始事务
        $row = $this->db->select()->from('question')->where('id',$id)->get()->row_array();
        $data = array(
            'op1'=>$row['op1'],
            'op2'=>$row['op2'],
            'op3'=>$row['op3'],
            'op4'=>$row['op4'],
            'as1'=>$row['as1'],
            'as2'=>$row['as2'],
            'as3'=>$row['as3'],
            'as4'=>$row['as4'],
            'title'=>$row['title'],
            'question_id'=>$row['id'],
            'exam_id'=>$exam_id,
            'style'=>$row['style']
        );

        $this->db->insert('exam_question',$data);
        $this->db->select('count(1) as num');
        $this->db->where('exam_id',$exam_id);
        $this->db->from('exam_question');
        $num = $this->db->get()->row_array();
        $this->db->trans_complete();//------结束事务
        if ($this->db->trans_status() === FALSE) {
            return -1;
            exit();
        } else {
            if($num){
                return $num['num']==0?-3:$num['num'];
                exit();
            }else{
                return -3;
                exit();
            }
        }
    }

    public function delete_question($id){
        $exam_id = $this->get_news_exam_id();
        if($exam_id == -1){
            return -1;
        }
        $this->db->trans_start();//--------开始事务
        $this->db->where('exam_id',$exam_id)->where('question_id',$id)->delete('exam_question');
        $this->db->select('count(1) as num');
        $this->db->where('exam_id',$exam_id);
        $this->db->from('exam_question');
        $num = $this->db->get()->row_array();
        $this->db->trans_complete();//------结束事务
        if ($this->db->trans_status() === FALSE) {
            return -1;
        } else {
            if($num){
                return $num['num']==0?-3:$num['num'];
            }else{
                return -3;
            }
        }
    }

    public function get_news_exam_id(){
        $this->db->select('*')->from('exam')->where(array(
            'user_id' => $this->session->userdata('login_user_id'),
            'flag'=>1
        ));
        $res = $this->db->order_by('id','desc')->get()->row_array();
        if(!$res){
            $exam_id = -1;
        }else{
            $exam_id = $res['id'];
        }
        return $exam_id;
    }

    public function change_exam_flag(){
        $this->db->select('*')->from('exam')->where(array(
            'user_id' => $this->session->userdata('login_user_id'),
            'flag'=>1
        ));
        $res = $this->db->order_by('id','desc')->get()->row_array();
        if(!$res){
           return -1;
        }
        $p_num = $this->db->select('count(1) as num')->from('exam_question')->where(array(
            'exam_id'=>$res['id']
        ))->get()->row_array();
        if($p_num['num']!=$res['p_num']){
            return -1;
        }
        $this->db->where('id',$res['id'])->update('exam',array('flag'=>2,'created'=>date('Y-m-d H:i:s',time())));
        return 1;
    }

    public function get_my_score_list($page) {
        $user_id = $this->session->userdata('login_user_id');
        $numPerPage = $this->input->post('numPerPage') ? $this->input->post('numPerPage') : 10;
        $pageNum = $this->input->post('pageNum') ? $this->input->post('pageNum') : $page;

        $this->db->select('count(1) as num');
        $this->db->from('self_exam a');
        $this->db->join('exam b','a.model_exam_id = b.id','left');
        $this->db->where(array(
            'a.user_id'=>$user_id,
            'a.complete'=>2
        ));
        $this->db->order_by('a.id', 'desc');
        $row = $this->db->get()->row_array();
        //总记录数
        $data['countPage'] = $row['num'];
        //TODO: 除了自测试卷的分数,还有参加过的所有统一考试的试卷
        $this->db->select('a.id,a.score,a.title,a.created,IFNULL(b.p_num * b.p_score,100) as allscore',false);
        $this->db->from('self_exam a');
        $this->db->join('exam b','a.model_exam_id = b.id','left');
        $this->db->where(array(
            'a.user_id'=>$user_id,
            'a.complete'=>2
        ));
        $this->db->order_by('a.id', 'desc');
        $this->db->limit($numPerPage, ($pageNum - 1) * $numPerPage );
        $data['res_list'] = $this->db->get()->result_array();
        $data['pageNum'] = $pageNum;
        $data['numPerPage'] = $numPerPage;
        return $data;
    }

    public function get_my_exam_list($page) {
        $user_id = $this->session->userdata('login_user_id');
        $numPerPage = $this->input->post('numPerPage') ? $this->input->post('numPerPage') : 10;
        $pageNum = $this->input->post('pageNum') ? $this->input->post('pageNum') : $page;
        $this->db->select('count(1) as num');
        $this->db->from('exam');
        $this->db->where(array(
            'user_id'=>$user_id,
            'flag'=>2
        ));
        $this->db->order_by('id', 'desc');
        $row = $this->db->get()->row_array();
        //总记录数
        $data['countPage'] = $row['num'];

        $this->db->select();
        $this->db->from('exam');
        $this->db->where(array(
            'user_id'=>$user_id,
            'flag'=>2
        ));
        $this->db->order_by('id', 'desc');
        $this->db->limit($numPerPage, ($pageNum - 1) * $numPerPage );
        $data['res_list'] = $this->db->get()->result_array();
        $data['pageNum'] = $pageNum;
        $data['numPerPage'] = $numPerPage;
        return $data;
    }

    public function view_examination($exam_id){
        $data['exam_main'] = $this->db->where('id', $exam_id)->get("exam")->row_array();
        $data['exam_list'] = $this->db->where('exam_id', $exam_id)->get('exam_question')->result_array();
        $this->db->select('count(1) as num');
        $this->db->where('exam_id', $exam_id);
        $this->db->from('exam_question');
        $num = $this->db->get()->row_array();
        $data['exam_num'] = $num['num'];
        return $data;
    }

    public function get_exam_list()
    {
        $string_in='';
        $subsidiary_id = $this->session->userdata('login_subsidiary_id_array');
        $company_id = $this->session->userdata('login_company_id')?$this->session->userdata('login_company_id'):-1;
        if($subsidiary_id || $this->session->userdata('login_permission_id') >= 3){
            if(is_array($subsidiary_id)){
                foreach($subsidiary_id as $key=>$item){
                    if($key==0){
                        $string_in.=$item;
                    }else{
                        $string_in.=','.$item;
                    }
                }
            }else{
                $string_in = $subsidiary_id;
            }
        }else{
            $string_in = -1;
        }

        $sql = "select DISTINCT a.title ,a.id
from exam a
left join exam_subsidiary b on b.exam_id = a.id
left join self_exam c on c.model_exam_id = a.id and c.complete >= 1 and c.user_id = ".$this->session->userdata('login_user_id')."
where (a.permission_id = 1 OR
(a.permission_id = 2 and a.company_id = ".$company_id.") OR
(a.permission_id > 2 and b.subsidiary_id in (".$string_in.")))
and a.flag = 2 and c.id is null and a.start_time < now() and a.end_time > date_add(now(),INTERVAL -1 day)
        ";
        $res = $this->db->query($sql,array($this->session->userdata('login_company_id'),$string_in))->result_array();
       return $res?$res:-1;
    }

    public function check_user_exam($exam_id,$user_id){
        $res = $this->db->select()->from('self_exam')->where(array(
            'user_id'=>$user_id,
            'id'=>$exam_id
        ))->get()->row_array();
        if($res){
            return 1;
        }else{
            return -1;
        }
    }

    public function change_flag_1($id){
        $result = $this->check_flag_date($id);
        if($result!=1){
            return -1;
        }
      $res = $this->db->where('id',$id)->update('exam',array('flag'=>1));
        if($res){
            return 1;
        }else{
            return -1;
        }
    }

    public function check_flag_date($id){
       $res = $this->get_news_exam_id();
        if($res!=-1){
            return 3;
        }
        $row = $this->db->select()->from('exam')
            ->where('id',$id)
            ->where('start_time < now()')
            ->get()->row_array();
        if($row){
            return 2;
        }
        return 1;
    }

    public function save_score(){

        $ids = $this->input->post('self_exam_qus_id');
        $score = $this->input->post('score');
        $allscore=0;
        $this->db->trans_start();//--------开始事务
        if($ids){
            if(is_array($ids)){
                foreach($ids as $key=>$item){
                    $this->db->where('id',$item)->update('self_exam_question',array('score'=>$score[$key]?$score[$key]:0));
                    $allscore += $score[$key]?$score[$key]:0;
                }
            }else{
                $this->db->where('id',$ids)->update('self_exam_question',array('score'=>$score?$score:0));
                $allscore += $score?$score:0;
            }
        }
        $this->db->where('id',$this->input->post('exam_id'))->update('self_exam',array(
            'complete'=>2,
            'score'=>$allscore
            ));
        $this->db->trans_complete();//------结束事务
    }

    public function check_complete($id){
        $this->db->select();
        $this->db->from('self_exam_question');
        $this->db->where('exam_id',$id);
        $this->db->where('complete',0);
        $res = $this->db->get()->row_array();
        if($res){
            return 1;
        }else{
            return 2;
        }
    }

    public function delete_exam(){
        $this->db->where(array(
            'user_id' => $this->session->userdata('login_user_id'),
            'flag'=>1
        ))->update('exam',array('flag'=>-1));
    }
}
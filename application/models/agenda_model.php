<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 16/5/20
 * Time: 上午9:02
 */
class Agenda_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    public function get_company_list($id = NULL) {
        if(empty($id)) {
            return $this->db->get('company')->result_array();
        }
        return $this->db->get_where('company', array('id' => $id))->result_array();
    }

    public function get_subsidiary_list($company_id, $subsidiary_id=NULL) {
        if(empty($subsidiary_id)) {
            return $this->db->get_where('subsidiary', array('company_id' => $company_id))->result_array();
        } else {
            return $this->db->where_in('id', $subsidiary_id)->from('subsidiary')->get()->result_array();
        }
    }

    public function get_subsidiary_user_list($subsidiary_id) {
        $this->db->select('a.*')->from('user a');
        $this->db->join('role b','b.id = a.role_id','inner');
        $this->db->join('user_subsidiary c','c.user_id = a.id','inner');
        $this->db->where_in('c.subsidiary_id',$subsidiary_id);
        $this->db->where(array(
            'b.permission_id >='=>$this->session->userdata('login_permission_id')
        ));
        return $this->db->get()->result_array();

        //return $this->db->get_where('user', array('subsidiary_id' => $subsidiary_id))->result_array();
    }

    public function get_subsidiary_user_list_7($subsidiary_id) {
        $this->db->select('a.*')->from('user a');
        $this->db->join('user_subsidiary c','c.user_id = a.id','inner');
        $this->db->where_in('c.subsidiary_id',$subsidiary_id);
        return $this->db->get()->result_array();
    }

    function list_agenda($page,$user_id = null,$subsidiary_id=null,$company_id=null){
        // 每页显示的记录条数，默认20条
        $numPerPage = $this->input->post('numPerPage') ? $this->input->post('numPerPage') : 10;
        $pageNum = $this->input->post('pageNum') ? $this->input->post('pageNum') : $page;

        //$this->db->distinct('a.id');
        $this->db->select('count(distinct(a.id)) as num',false);
        $this->db->from('agenda a');
        $this->db->join('user b','a.user_id = b.id','inner');
        $this->db->join('role c','c.id = b.role_id','inner');
        $this->db->join('user_subsidiary d','d.user_id = b.id','inner');
        if(!empty($user_id)){
            $this->db->where('a.user_id',$user_id);
        }
        if($this->input->post('status')){
                $this->db->where('a.status',$this->input->post('status'));
        }
        if($this->input->post('course')){
            $this->db->where('a.course',$this->input->post('course'));
        }
        if($this->input->post('num')){
            $this->db->like('a.num',trim($this->input->post('num')));
        }
        if($this->input->post('xq_name')){
            $this->db->like('a.xq_name',trim($this->input->post('xq_name')));
        }
        if($this->input->post('dbgh_id')){
            $this->db->where('a.dbgh_id',$this->input->post('dbgh_id'));
        }
        if($this->input->post('dbyh_id')){
            $this->db->where('a.dbyh_id',$this->input->post('dbyh_id'));
        }
        if($this->input->POST('company')) {
            $this->db->where('b.company_id', $this->input->POST('company'));
        }
        if($this->input->POST('subsidiary')) {
            $this->db->where_in('d.subsidiary_id', $this->input->POST('subsidiary'));
        }
        if($this->input->POST('user')) {
            $this->db->where('b.id', $this->input->POST('user'));
        }
        if(!empty($subsidiary_id)) {
            $this->db->where_in('d.subsidiary_id', $subsidiary_id);
        }
        if(!empty($company_id)) {
            $this->db->where('b.company_id', $company_id);
        }
        if($this->input->POST('Cstart_date')) {
            $this->db->where('a.cdate >=', $this->input->POST('Cstart_date'));
        }
        if($this->input->POST('Cend_date')) {
                $this->db->where('a.cdate <=', $this->input->POST('Cend_date'));
        }
        if($this->input->POST('Estart_date')) {
            $this->db->where('a.edate >=', $this->input->POST('Estart_date'));
        }
        if($this->input->POST('Eend_date')) {
            $this->db->where('a.edate <=', $this->input->POST('Eend_date'));
        }

        if(!in_array(2,$this->session->userdata('login_position_id_array')) && !in_array(8,$this->session->userdata('login_position_id_array')) &&!in_array(9,$this->session->userdata('login_position_id_array'))) {
            $this->db->where('c.permission_id >=', $this->session->userdata('login_permission_id'));
        }
        if(in_array(8,$this->session->userdata('login_position_id_array'))){
            $this->db->where('a.dbgh_id =', $this->session->userdata('login_user_id'));
        }
        if(in_array(9,$this->session->userdata('login_position_id_array'))){
            $this->db->where('a.dbyh_id =', $this->session->userdata('login_user_id'));
        }
        $this->db->where('a.flag',1);
        $row = $this->db->get()->row_array();
        //总记录数
        $data['countPage'] = $row['num'];
        $data['dbgh_id'] = $this->input->post('dbgh_id') ? $this->input->post('dbgh_id') :"";
        $data['dbyh_id'] = $this->input->post('dbyh_id') ? $this->input->post('dbyh_id') :"";
        $data['num'] = $this->input->post('num') ? trim($this->input->post('num')) :"";
        $data['xq_name'] = $this->input->post('xq_name') ? trim($this->input->post('xq_name')) :"";
        $data['Cstart_date'] = $this->input->post('Cstart_date') ? $this->input->post('Cstart_date') :"";
        $data['Cend_date'] = $this->input->post('Cend_date') ? $this->input->post('Cend_date') :"";
        $data['Estart_date'] = $this->input->post('Estart_date') ? $this->input->post('Estart_date') :"";
        $data['Eend_date'] = $this->input->post('Eend_date') ? $this->input->post('Eend_date') :"";
        //list
        $this->db->select('a.*,b.rel_name,b.tel user_tel,f.name course_name,u1.rel_name gh_name,u1.tel gh_tel,u2.rel_name yh_name,u2.tel yh_tel');
        $this->db->distinct('a.id');
        $this->db->from('agenda a');
        $this->db->join('user b','a.user_id = b.id','inner');
        $this->db->join('user u1','a.dbgh_id = u1.id','left');
        $this->db->join('user u2','a.dbyh_id = u2.id','left');
        $this->db->join('role c','c.id = b.role_id','inner');
        $this->db->join('user_subsidiary d','d.user_id = b.id','inner');
        $this->db->join('course f','f.id = a.course','left');
        if(!empty($user_id)){
            $this->db->where('a.user_id',$user_id);
        }
        if($this->input->post('status')){
                $this->db->where('a.status',$this->input->post('status'));
        }
        if($this->input->post('course')){
            $this->db->where('a.course',$this->input->post('course'));
        }
        if($this->input->post('num')){
            $this->db->like('a.num',trim($this->input->post('num')));
        }
        if($this->input->post('xq_name')){
            $this->db->like('a.xq_name',trim($this->input->post('xq_name')));
        }
        if($this->input->post('dbgh_id')){
            $this->db->where('a.dbgh_id',$this->input->post('dbgh_id'));
        }
        if($this->input->post('dbyh_id')){
            $this->db->where('a.dbyh_id',$this->input->post('dbyh_id'));
        }
        if($this->input->POST('company')) {
            $this->db->where('b.company_id', $this->input->POST('company'));
        }
        if($this->input->POST('subsidiary')) {
            $this->db->where_in('d.subsidiary_id', $this->input->POST('subsidiary'));
        }
        if($this->input->POST('user')) {
            $this->db->where('b.id', $this->input->POST('user'));
        }
        if(!empty($subsidiary_id)) {
            $this->db->where_in('d.subsidiary_id', $subsidiary_id);
        }
        if(!empty($company_id)) {
            $this->db->where('b.company_id', $company_id);
        }
        if($this->input->POST('Cstart_date')) {
            $this->db->where('a.cdate >=', $this->input->POST('Cstart_date'));
        }
        if($this->input->POST('Cend_date')) {
            $this->db->where('a.cdate <=', $this->input->POST('Cend_date'));
        }
        if($this->input->POST('Estart_date')) {
            $this->db->where('a.edate >=', $this->input->POST('Estart_date'));
        }
        if($this->input->POST('Eend_date')) {
            $this->db->where('a.edate <=', $this->input->POST('Eend_date'));
        }
        if(!in_array(2,$this->session->userdata('login_position_id_array')) && !in_array(8,$this->session->userdata('login_position_id_array')) &&!in_array(9,$this->session->userdata('login_position_id_array'))) {
            $this->db->where('c.permission_id >=', $this->session->userdata('login_permission_id'));
        }
        if(in_array(8,$this->session->userdata('login_position_id_array'))){
            $this->db->where('a.dbgh_id =', $this->session->userdata('login_user_id'));
        }
        if(in_array(9,$this->session->userdata('login_position_id_array'))){
            $this->db->where('a.dbyh_id =', $this->session->userdata('login_user_id'));
        }
        $this->db->where('a.flag',1);
        $this->db->limit($numPerPage, ($pageNum - 1) * $numPerPage );
        $this->db->order_by('a.id', 'desc');
        //$this->db->order_by('a.user_id', 'desc');
        $data['res_list'] = $this->db->get()->result_array();
        $data['detail'] = 1;
        if($data['res_list']){
            foreach($data['res_list'] as $v){
                $ids[] = $v['id'];
            }
            if (isset($ids)){
                $this->db->select('b.a_id,b.created,c.name')->from('agenda a');
                $this->db->join('agenda_course b','a.id = b.a_id','left');
                $this->db->join('course c','b.c_id = c.id','left');
                $this->db->where_in('a.id',$ids);
                $this->db->order_by('b.created','desc');
                $this->db->order_by('b.id','desc');
                $agenda_detail = $this->db->get()->result_array();
                if ($agenda_detail){
                    $data['detail'] = $agenda_detail;
                    //die(var_dump($agenda_detail));
                }
            }
        }
        $this->db->select('a.id,a.rel_name');
        $this->db->from('user a');
        $this->db->join('user_position b','a.id = b.user_id','left');
        $this->db->where('b.pid',8);
        $this->db->where('a.flag',1);
        $this->db->order_by('a.id');
        $data['gh_list'] = $this->db->get()->result_array();
        $this->db->select('a.id,a.rel_name');
        $this->db->from('user a');
        $this->db->join('user_position b','a.id = b.user_id','left');
        $this->db->where('b.pid',9);
        $this->db->where('a.flag',1);
        $this->db->order_by('a.id');
        $data['yh_list'] = $this->db->get()->result_array();
        $data['pageNum'] = $pageNum;
        $data['numPerPage'] = $numPerPage;
        return $data;

    }

    function get_course(){
        $res = $this->db->select()->from('course')->get()->result_array();
        if(!$res){
            return 1;
        }else{
            return $res;
        }
    }

    public function get_agenda($id) {
        return $this->db->get_where('agenda', array('id' => $id))->row();
    }

    public function get_agenda_course($a_id) {
        $this->db->select('a.*, b.name as course_name');
        $this->db->from('agenda_course a');
        $this->db->join('course b','a.c_id = b.id','left');
        $this->db->where('a.a_id', $a_id);
        return $this->db->get()->result();
    }

    public function get_agenda_image($a_id) {
        return $this->db->get_where('agenda_image', array('a_id' => $a_id))->result();
    }

    public function get_course_list() {
        return $this->db->where('flag',1)->get('course')->result();
    }

    public function save_agenda() {
        if(!$this->input->post('id')){

        }
        $now = date('Y-m-d H:i:s');
        $cdate = date('Y-m-d');
        $company_id = $this->session->userdata('login_company_id');

        $this->db->select_max('max_num');
        $result = $this->db->get_where('agenda', array('company_id' => $company_id))->row_array();
        $max_num = 1;
        if(!empty($result['max_num'])) {
            $max_num += $result['max_num'];
        }
        $num = 'D' . str_pad($company_id, 3, "0", STR_PAD_LEFT) . str_pad($max_num, 4, "0", STR_PAD_LEFT);

        $this->db->trans_start();//--------开始事务

        $agenda = array(
            'time_open'=>$this->input->post('time_open'),
            'user_id' => $this->session->userdata('login_user_id'),
            'xq_name' => $this->input->post('xq_name'),
            'landlord_name' => $this->input->post('landlord_name'),
            'customer_name' => $this->input->post('customer_name'),
            'customer_income' => $this->input->post('customer_income'),
            'acreage' => $this->input->post('acreage'),
            'two_year_flag' => $this->input->post('two_year_flag'),
            'amount' => $this->input->post('amount'),
            'rest_load' => $this->input->post('rest_load'),
            'payment_method' => $this->input->post('payment_method'),
            'down_payment' => $this->input->post('down_payment'),
            'mortgage' => $this->input->post('mortgage'),
            'style' => $this->input->post('style'),
            'payment_node' => $this->input->post('payment_node'),
            'mark' => $this->input->post('mark'),
            'status' => 1,
            'course' => 1,
            'cdate' => $cdate,
            'num' => $num,
            'errtext' => '',
            'company_id' => $company_id,
            'order_status'=> $this->input->post('order_status'),
            'max_num' => $max_num
        );
        if($this->input->post('order_status') == 2){
            $agenda['a1'] = $this->input->post('a1');
            $agenda['a2'] = $this->input->post('a2');
            $agenda['a3'] = $this->input->post('a3');
        }

        if($this->input->post('order_status') == 1){
            $pay_sum = $this->config->item('agenda_pt_sum');
        }else{
            $pay_sum = 0;
            if($this->input->post('a1') == 1)
                $pay_sum += $this->config->item('agenda_jj_a1_sum');
            if($this->input->post('a2') == 1)
                $pay_sum += $this->config->item('agenda_jj_a2_sum');
            if($this->input->post('a3') == 1)
                $pay_sum += $this->config->item('agenda_jj_a3_sum');
        }
        $agenda['pay_sum'] = $pay_sum;
        if($this->input->post('id')){//修改
            $this->db->where('id', $this->input->post('id'));
            unset($agenda['num']);
            unset($agenda['max_num']);
            unset($agenda['cdate']);
            $this->db->update('agenda', $agenda);
            $a_id = $this->input->post('id');

            $this->db->delete('agenda_image', array('a_id' => $a_id));
        } else {
            //这里查找权证人员,进行分单操作
            //第一步查找当前最后一单的权证是谁
            if($this->check_save() == -1){
                return -1;
            }
            $agenda_row = $this->db->select('dbgh_id,dbyh_id')->from('agenda')->order_by('id','desc')->get()->row_array();
            $this->db->select('a.id,a.rel_name');
            $this->db->from('user a');
            $this->db->join('user_position b','a.id = b.user_id','left');
            $this->db->where('b.pid',8);
            $this->db->where('a.flag',1);
            $this->db->order_by('a.id');
            if($agenda_row){
                $this->db->where('a.id >',$agenda_row['dbgh_id']);
            }
            $dbgh = $this->db->get()->row_array();
            if($dbgh){
                $dbgh_id = $dbgh['id'];
                $dbgh_name = $dbgh['rel_name'];
            }else{
                $this->db->select('a.id,a.rel_name');
                $this->db->from('user a');
                $this->db->join('user_position b','a.id = b.user_id','left');
                $this->db->where('b.pid',8);
                $this->db->where('a.flag',1);
                $this->db->order_by('a.id');
                $dbgh_one = $this->db->get()->row_array();
                if($dbgh_one){
                    $dbgh_id = $dbgh_one['id'];
                    $dbgh_name = $dbgh_one['rel_name'];
                }else{
                    $dbgh_id = 0;
                    $dbgh_name = '无 权证(过户) 人员';
                }
            }

            $this->db->select('a.id,a.rel_name');
            $this->db->from('user a');
            $this->db->join('user_position b','a.id = b.user_id','left');
            $this->db->where('b.pid',9);
            $this->db->where('a.flag',1);
            $this->db->order_by('a.id');
            if($agenda_row){
                $this->db->where('a.id >',$agenda_row['dbyh_id']);
            }
            $dbyh = $this->db->get()->row_array();
            if($dbyh){
                $dbyh_id = $dbyh['id'];
                $dbyh_name = $dbyh['rel_name'];
            }else{
                $this->db->select('a.id,a.rel_name');
                $this->db->from('user a');
                $this->db->join('user_position b','a.id = b.user_id','left');
                $this->db->where('b.pid',9);
                $this->db->where('a.flag',1);
                $this->db->order_by('a.id');
                $dbyh_one = $this->db->get()->row_array();
                if($dbyh_one){
                    $dbyh_id = $dbyh_one['id'];
                    $dbyh_name = $dbyh_one['rel_name'];
                }else{
                    $dbyh_id = 0;
                    $dbyh_name = '无 权证(银行) 人员';
                }
            }

            $agenda['dbgh_id'] = $dbgh_id;
            $agenda['dbyh_id'] = $dbyh_id;
            $this->db->insert('agenda', $agenda);
            //die(var_dump($this->db->last_query()));
            $a_id = $this->db->insert_id();

            $agenda_course = array(
                'a_id' => $a_id,
                'c_id' => 1,
                'created' => $now
            );
            $this->db->insert('agenda_course', $agenda_course);
        }

        //$folder = $this->input->post('folder');
        for($i=1; $i<=6; $i++) {
            $pic_short = $this->input->post('pic_short_' . $i);
            $folder = $this->input->post('folder_' . $i);
            foreach($pic_short as $idx => $pic) {
                $agenda_image = array(
                    'a_id' => $a_id,
                    'style' => $i,
                    'folder' => $folder[$idx],
                    'pic' => str_replace('_thumb', '', $pic),
                    'pic_short' => $pic
                );
                $this->db->insert('agenda_image', $agenda_image);
            }
        }

        $this->db->trans_complete();//------结束事务
        if ($this->db->trans_status() === FALSE) {
            return -1;
        } else {
            if(!$this->input->post('id')){
                $data = array(
                    'first' => array(
                        'value' => "新增代办事项成功!",
                        'color' => '#FF0000'
                    ),
                    'keyword1' => array(
                        'value' => $num,
                        'color' => '#FF0000'
                    ),
                    'keyword2' => array(
                        'value' => date('Y-m-d H:i:s'),
                        'color' => '#FF0000'
                    ),
                    'remark' => array(
                        'value' => '感谢你对我们工作的信任',
                        'color' => '#FF0000'
                    )
                );
                //发送给用户自己
                $this->wxpost($this->config->item('WX_SJTJ'),$data,$this->session->userdata('login_user_id'),'www.baidu.com');
                //发送给用户的店长,如果用户本身职级大于等于店长,就不做通知
                if($this->session->userdata('login_permission_id') > 4){
                    $data['remark']['value'] = "你的员工 ".$this->session->userdata('login_rel_name')." 成功提交一单代办业务.";
                    $this->db->select('a.id');
                    $this->db->from('user a');
                    $this->db->join('user_subsidiary b','a.id = b.user_id','left');
                    $this->db->where(array(
                        'a.flag'=>1,
                        'a.company_id'=>$this->session->userdata('login_company_id'),
                        'a.role_id'=>4,
                        'a.openid <>'=>''
                    ));
                    $this->db->where('a.openid is not null');
                    $this->db->where_in('b.subsidiary_id',$this->session->userdata('login_subsidiary_id_array'));
                    $user_list1 = $this->db->get()->result_array();
                    foreach($user_list1 as $item){
                        $this->wxpost($this->config->item('WX_SJTJ'),$data,$item['id']);
                    }
                }
                //发送给权证人员
                $data['first']['value'] = "有一单新的代办业务生成";
                $data['remark']['value'] = "用户 ".$this->session->userdata('login_rel_name')." 成功提交一单代办业务.";

                $this->db->select('a.id');
                $this->db->from('user a');
                $this->db->join('user_position b','a.id = b.user_id','left');
                $this->db->where(array(
                    'a.flag'=>1,
                    'b.pid'=>2,
                    'a.openid <>'=>''
                ));
                $this->db->where('a.openid is not null');
                $user_list2 = $this->db->get()->result_array();
                foreach($user_list2 as $item2){
                    $this->wxpost($this->config->item('WX_SJTJ'),$data,$item2['id']);
                }
                //发送给 权证(过户)
                $this->wxpost($this->config->item('WX_SJTJ'),$data,$dbgh_id,'www.baidu.com');
                //发送给 权证(银行)
                $this->wxpost($this->config->item('WX_SJTJ'),$data,$dbyh_id,'www.baidu.com');

            }


            return 1;
        }
    }

    public function confirm_agenda() {
        $id = $this->input->post('id');
        if(empty($id)) return;

        $now = date('Y-m-d H:i:s');
        $edate = date('Y-m-d');
        $this->db->trans_start();//--------开始事务

        $agenda = array(
            'status' => $this->input->post('status'),
            'errtext' => $this->input->post('errtext')
        );
        if($this->input->post('status')==3){
            $agenda['edate'] = $edate;
            $agenda['sf_sum'] = $this->input->post('sf_sum');
            $agenda['pay_text'] = $this->input->post('pay_text');
        }
        $courses = $this->input->post('course');
        if(!empty($courses)) {
            foreach ($courses as $course) {
                $agenda_course = array(
                    'a_id' => $id,
                    'c_id' => $course,
                    'created' => $now
                );
                $this->db->insert('agenda_course', $agenda_course);
            }
            $agenda['course'] = end($courses);
        }
        $this->db->where('id', $id);
        $this->db->update('agenda', $agenda);
        if($this->input->post('status')==3){
            $age_row = $this->db->select()->from('agenda')->where('id',$id)->get()->row_array();
            $res_sum = $this->change_sum($this->session->userdata('login_company_id'),
                $age_row['pay_sum'],
                2,
                $this->config->item('agenda_sum_name'),
                'age',
                $this->input->post('id'),
                2
            );
            if($res_sum != 1){
                return -3;//金额不足
            }
        }
        $this->db->trans_complete();//------结束事务
        if ($this->db->trans_status() === FALSE) {
            return -1;
        } else {
            $agenda_info = $this->db->select()->from('agenda')->where('id',$this->input->post('id'))->get()->row_array();
            if($agenda_info){
                $course_info = $this->db->select()->from('course')->where('id',end($courses))->get()->row_array();

                $data = array(
                    'first' => array(
                        'value' => "代办事项进程变更提醒",
                        'color' => '#FF0000'
                    ),
                    'keyword1' => array(
                        'value' => $agenda_info['num'],
                        'color' => '#FF0000'
                    ),
                    'keyword2' => array(
                        'value' => date('Y-m-d H:i:s'),
                        'color' => '#FF0000'
                    ),
                    'remark' => array(
                        'value' => '当前进程:'.$course_info['name'].'.当前状态:',
                        'color' => '#FF0000'
                    )
                );
                if($this->input->post('status')==3){
                    $data['first']['value']='代办事项服务完成!';
                    $data['remark']['value']='服务完成,感谢您对我们工作的支持!';
                }else{
                    if($this->input->post('status')==1){
                        $data['remark']['value']='当前进程:'.$course_info['name'].'.当前状态:正常';
                    }else{
                        $data['remark']['value']='当前进程:'.$course_info['name'].'.当前状态:异常';
                    }
                }
                //发送给用户自己
                $this->wxpost($this->config->item('WX_SJTJ'),$data,$agenda_info['user_id'],'www.funmall.com');
            }

            return 1;
        }
    }

    public function get_dbgh_list() {
        $this->db->select('a.id,a.rel_name');
        $this->db->from('user a');
        $this->db->join('user_position b','a.id = b.user_id','left');
        $this->db->where('b.pid',8);
        $this->db->where('a.flag',1);
        $this->db->order_by('a.id');
        return $this->db->get()->result_array();
    }

    public function get_dbyh_list() {
        $this->db->select('a.id,a.rel_name');
        $this->db->from('user a');
        $this->db->join('user_position b','a.id = b.user_id','left');
        $this->db->where('b.pid',9);
        $this->db->where('a.flag',1);
        $this->db->order_by('a.id');
        return $this->db->get()->result_array();
    }

    public function change_dbuser_agenda(){
        $this->db->where('id',$this->input->post('id'));
        $this->db->where('status <>',3);
        $this->db->update('agenda',array(
            'dbgh_id'=>$this->input->post('dbgh_id'),
            'dbyh_id'=>$this->input->post('dbyh_id')
        ));
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

    public function age_check_sum(){
        //先查看公司账户金额
      $company = $this->db->select()->where("id",$this->session->userdata('login_company_id'))->from('company')->get()->row_array();
       if(!$company){
           return -1;
       }
        $company_sum = $company['sum'];
        //再查看正在办理中的权证单据数量
        $this->db->select('SUM(pay_sum) as num',false);
        $this->db->from('agenda');
        $this->db->where('status <>',3);
        $this->db->where('flag',1);
        $this->db->where('company_id',$this->session->userdata('login_company_id'));
        $agenda = $this->db->get()->row_array();
        $agenda_num = $agenda['num'];
        if($this->input->post('R1') == 1){
            $pay_sum = $this->config->item('agenda_pt_sum');
        }else{
            $pay_sum = 0;
            if($this->input->post('C1') == 1)
                $pay_sum += $this->config->item('agenda_jj_a1_sum');
            if($this->input->post('C2') == 1)
                $pay_sum += $this->config->item('agenda_jj_a2_sum');
            if($this->input->post('C3') == 1)
                $pay_sum += $this->config->item('agenda_jj_a3_sum');
        }
        //die($pay_sum);
        //开始计算是否满足新增单据条件
        $pty = $company_sum - $agenda_num - $pay_sum;
        if($this->config->item('Arrears_CK') >= $pty){
            return -2;
        }else{
            return 1;
        }
    }

    public function check_save()
    {
        if($this->input->post('time_open')){
            $this->db->select('*');
            $row = $this->db->from('agenda')->where(array(
                'time_open'=>$this->input->post('time_open'),
                'user_id' => $this->session->userdata('login_user_id'),
                ))->get()->row();
            if($row){
                return -1;
            }else{
                return 1;
            }
        }else{
            return 1;
        }
    }
}
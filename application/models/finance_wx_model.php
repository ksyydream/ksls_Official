<?php
/**
 * Created by PhpStorm.
 * User: bin.shen
 * Date: 5/31/16
 * Time: 23:00
 */

class Finance_wx_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    public function create_finance_num(){

        $company_id = $this->session->userdata('wx_company_id');

        $this->db->select_max('id');
        $result = $this->db->get_where('finance',
            array(
                'company_id' => $company_id,
                "DATE_FORMAT(create_date,'%Y')" => date('Y'),
            )
        )->row_array();
        $max_num = 1;
        if(!empty($result['id'])) {
            $max_num += $result['id'];
        }
        $finance_num = 'FIN' .date('y'). str_pad($company_id, 4, "0", STR_PAD_LEFT) . str_pad($max_num, 4, "0", STR_PAD_LEFT);
        return $finance_num;
    }

    public function save_power($id){
        $data = $this->db->from('finance')->where('id',$id)->get()->row_array();
        if(!$data)
            return -1;
        if($data['user_id'] == $this->session->userdata('wx_user_id')){
            if($data['status'] == 1 || $data['status'] == 5){
                return 1;
            }else{
                return -3;
            }

        }else{
            return -2;
        }
    }

    public function view_power($id){
        $data = $this->db->from('finance')->where('id',$id)->get()->row_array();
        if(!$data)
            return -1;
        $company_id = $this->session->userdata('wx_company_id');
        $subsidiary_id = $this->session->userdata('wx_subsidiary_id_array');
        $position_id = $this->session->userdata('wx_position_id_array');
        $permission_id = $this->session->userdata('wx_permission_id');
        if($permission_id == 1 || in_array(12,$position_id))
            return 1;
        if($permission_id == 2 && $data['company_id'] == $company_id)
            return 1;
        if($permission_id <= 4 && $data['company_id'] == $company_id && in_array($data['subsidiary_id'],$subsidiary_id))
            return 1;
        if($data['user_id'] == $this->session->userdata('wx_user_id'))
            return 1;
        return -2;
    }

    public function logout(){
        $this->db->where('id',$this->session->userdata('wx_user_id'))->update('user',array('openid'=>''));
        $this->db->where('id',$this->session->userdata('wx_finance_id'))->update('finance',array('borrower_openid'=>''));
        $this->session->unset_userdata('wx_user_id');
        $this->session->unset_userdata('wx_finance_id');
        $this->session->sess_destroy();
    }

    public function check_openid(){
        $openid = $this->session->userdata('openid');

        $this->db->select('a.id,a.rel_name,b.name,b.sum,c.name role_name')->from('user a');
        $this->db->join('company b','a.company_id = b.id','left');
        $this->db->join('role c','c.id = a.role_id','left');
        $this->db->where('a.openid',$openid);
        $row=$this->db->get()->row_array();
        if($row){
            $res = $this->set_session_wx($row['id']);
            if($res==1)
                return 1;
        }else{
            $finance_row = $this->db->select("id,finance_num")->from("finance")->where("borrower_openid",$openid)->get()->row_array();
            if($finance_row){
                $this->session->set_userdata('wx_finance_id',$finance_row['id']);
                $this->session->set_userdata('wx_finance_num',$finance_row['finance_num']);
                return 2;
            }
        }
        return -1;
    }

    public function set_session_wx($id){
        $this->db->from('user');
        $this->db->where('id', $id);
        $rs = $this->db->get();
        if ($rs->num_rows() > 0) {
            $res = $rs->row();
            if($res->flag==2){
                return 2;
            }
            $role_p = $this->db->select()->where('id',$res->role_id)->from('role')->get()->row();
            $company_flag = $this->db->where('id',$res->company_id)->from('company')->get()->row_array();
            if($role_p->permission_id !=1){
                if($company_flag){
                    if($company_flag['flag']==2 && $role_p->permission_id !=1){
                        return 3;
                    }
                }else{
                    return 3;
                }
            }
            $token = uniqid();
            //$this->db->where('id',$res->id)->update('user',array('token'=>$token));
            $pids = $this->db->select()->from('user_position')->where('user_id',$res->id)->get()->result_array();
            $ids = array();
            if($pids){
                foreach($pids as $id){
                    $ids[]=$id['pid'];
                }
            }

            $subids = $this->db->select()->from('user_subsidiary')->where('user_id',$res->id)->get()->result_array();
            $sids = array();
            if($subids){
                foreach($subids as $id){
                    $sids[]=$id['subsidiary_id'];
                }
            }

            $user_info['wx_token'] = $token;
            $user_info['wx_user_id'] = $res->id;
            $user_info['wx_username'] = $res->username;
            $user_info['wx_password'] = $res->password;
            $user_info['wx_rel_name'] = $res->rel_name;
            $user_info['wx_role_id'] = $res->role_id;
            $user_info['wx_role_name'] = $role_p->name;
            $user_info['wx_permission_id'] = $role_p->permission_id;
            $user_info['wx_company_id'] = $res->company_id;
            //  $user_info['login_subsidiary_id'] = $res->subsidiary_id;
            $user_info['wx_subsidiary_id_array'] = $sids;
            // $user_info['login_position_id'] = $res->position_id; 此栏位暂不使用
            $user_info['wx_position_id_array'] = $ids;
            $user_info['wx_user_pic'] = $res->pic;
            $this->session->set_userdata($user_info);
            return 1;
        }
        return 0;
    }

    public function user_login(){
        $openid = $this->session->userdata('openid');
        $username = $this->input->post('username');
        $password = $this->input->post('password');
        $this->db->from('user');
        $this->db->where('username', $username);
        $this->db->where('password', sha1($password));
        $rs = $this->db->get();
        if ($rs->num_rows() > 0) {
            $this->db->where('openid',$openid)->set('openid','')->update('user');
            $res = $rs->row();
            $res_ret = $this->set_session_wx($res->id);
            if($res_ret==1){
                $this->db->where('id',$res->id)->update('user',array('openid'=>$openid));
                return 1;
            }
        }
        return -1;
    }

    public function get_main_data(){
        $this->db->select('count(1) num')->from('finance a');
        $this->db->where('a.flag',1);
        $this->db->where("a.user_id",$this->session->userdata('wx_user_id'));
        $data['my_fin_count']=$this->db->get()->row()->num;
        $sql_7 = "select count(a.id) num
from finance a
where a.flag = 1 and a.user_id = ".$this->session->userdata('wx_user_id')."
 and a.tijiao_date >= date_add(NOW(), INTERVAL - 7 DAY) ";
        $query = $this->db->query($sql_7);
        $data['my_fin_count7'] =  $query->row()->num;

        $sql_30 = "select count(a.id) num
from finance a
where a.flag = 1 and a.user_id = ".$this->session->userdata('wx_user_id')."
 and a.tijiao_date >= date_add(NOW(), INTERVAL - 30 DAY) ";
        $query = $this->db->query($sql_30);
        $data['my_fin_count30'] =  $query->row()->num;

        $position_id = $this->session->userdata('wx_position_id_array');
        $permission_id = $this->session->userdata('wx_permission_id');
        $company_id = NULL;
        $subsidiary_id = NULL;
        $user_id = NULL;
        if($permission_id == 1 || in_array(12,$position_id)){ // 如果是管理员,或者金融管理专员

        }elseif($permission_id <= 3){ //总经理 和 区域经理可以查看不同门店
            $company_id = $this->session->userdata('wx_company_id');
            if($permission_id == 2) {

            } else if($permission_id < 5) {
                $subsidiary_id = $this->session->userdata('wx_subsidiary_id_array');
            }
        }else{
            $company_id = $this->session->userdata('wx_company_id');
            $subsidiary_id = $this->session->userdata('wx_subsidiary_id_array');
            $user_id = $this->session->userdata('wx_user_id');

        }
        $data['fin_count'] = $this->get_count_finance($user_id,$subsidiary_id,$company_id,null,null);
        $data['fin_count7'] = $this->get_count_finance($user_id,$subsidiary_id,$company_id,null,1);
        $data['fin_count30'] = $this->get_count_finance($user_id,$subsidiary_id,$company_id,null,2);
        $data['ins_1'] = $this->get_count_finance($user_id,$subsidiary_id,$company_id,1,null);
        $data['ins_2'] = $this->get_count_finance($user_id,$subsidiary_id,$company_id,2,null);
        $data['ins_3'] = $this->get_count_finance($user_id,$subsidiary_id,$company_id,3,null);
        $data['ins_4'] = $this->get_count_finance($user_id,$subsidiary_id,$company_id,4,null);
        $data['ins_5'] = $this->get_count_finance($user_id,$subsidiary_id,$company_id,5,null);
        $data['ins_6'] = $this->get_count_finance($user_id,$subsidiary_id,$company_id,-1,null);

        return $data;
    }

    public function get_count_finance($user_id = null,$subsidiary_id=null,$company_id=null,$status=null,$tj_flag=null){
        $this->db->select('count(distinct(a.id)) as num',false);
        $this->db->from('finance a');
        $this->db->join('user b','a.user_id = b.id','inner');
        $this->db->join('user c','a.create_user = c.id','inner');
        if($user_id){
            $this->db->where('a.user_id',$user_id);
        }
        if($status){
            $this->db->where('a.status',$status);
        }
        if($company_id) {
            $this->db->where('a.company_id', $company_id);
        }
        if(!empty($subsidiary_id)) {
            $this->db->where_in('a.subsidiary_id', $subsidiary_id);
        }
        if($tj_flag==1) {
            $this->db->where('date_format(a.tijiao_date, \'%Y-%m-%d\') >=', date("Y-m-d",strtotime("-7 day")));
        }
        if($tj_flag==2) {
            $this->db->where('date_format(a.tijiao_date, \'%Y-%m-%d\') >=', date("Y-m-d",strtotime("-30 day")));
        }
        $this->db->where('a.flag',1);

        $row = $this->db->get()->row_array();
        if($row)
            return $row['num'];
        return 0;
    }

    public function code_login($id){
        $row = $this->db->select('id,finance_num')->from("finance")->where("id",$id)->get()->row_array();
        if($row){
            $this->db->where('borrower_openid',$this->session->userdata('openid'))->update('finance',array('borrower_openid'=>''));
            $this->db->where('id',$id)->update('finance',array('borrower_openid'=>$this->session->userdata('openid')));
            $this->session->set_userdata('wx_finance_id',$id);
            $this->session->set_userdata('wx_finance_num',$row['finance_num']);
            return 1;
        }else{
            return -1;
        }

    }

    public function get_borrower_openid($id){
        $row=$this->db->select("borrower_openid")->from("finance")->where("id",$id)->get()->row_array();
        if($row)
            return $row['borrower_openid'];
        return -1;
    }

    public function save_finance_1(){
        $id = $this->input->post("id");
        if(!$id){
            $row = $this->db->select('id')->from('finance')->where('create_user',$this->session->userdata('wx_user_id'))->where('finance_wx_num',trim($this->input->post("finance_wx_num")))->get()->row();
            if($row){
                $id = $row->id;
                $res_ = $this->save_power($id);
                if($res_ !=1)
                    return -1;
            }
        }
        $data = array(
            "borrower_name" => trim($this->input->post("borrower_name")),
            "borrower_age" => $this->input->post("borrower_age"),
            "borrower_sex" => $this->input->post("borrower_sex"),
            "borrower_native" => trim($this->input->post("borrower_native")),
            "borrower_qualifications" => $this->input->post("borrower_qualifications"),
            "borrower_marriage" => $this->input->post("borrower_marriage"),
            "borrower_workADD" => trim($this->input->post("borrower_workADD")),
            "borrower_position" => trim($this->input->post("borrower_position")),
            "borrower_income" => trim($this->input->post("borrower_income")),
            "borrower_SSY" => trim($this->input->post("borrower_SSY")),
            "borrower_code" => trim($this->input->post("borrower_code")),
            "borrower_phone" => trim($this->input->post("borrower_phone")),
            "finance_wx_num" => trim($this->input->post("finance_wx_num")),
            "borrower_hasP" => $this->input->post('borrower_hasP'),

            "borrowing_amount" =>trim($this->input->post("borrowing_amount"))?trim($this->input->post("borrowing_amount")):null,
            "repayment" => trim($this->input->post("repayment")),
            "repayment_methods" => trim($this->input->post("repayment_methods")),
            //"explain_XYK" => $this->input->post("explain_XYK",true),
            //"explain_AJ" => $this->input->post("explain_AJ",true),
            //"explain_ZY" => $this->input->post("explain_ZY",true),
            //"explain_SYBX" => $this->input->post("explain_SYBX",true),
            //"explain_SFZC" => $this->input->post("explain_SFZC",true),
            "explain_XYK_radio" => $this->input->post("explain_XYK_radio",true)?$this->input->post("explain_XYK_radio",true):0,
            "explain_AJ_radio" => $this->input->post("explain_AJ_radio",true)?$this->input->post("explain_AJ_radio",true):0,
            "explain_ZY_radio" => $this->input->post("explain_ZY_radio",true)?$this->input->post("explain_ZY_radio",true):0,
            "explain_SYBX_radio" => $this->input->post("explain_SYBX_radio",true)?$this->input->post("explain_SYBX_radio",true):0,
            "explain_SFZC_radio" => $this->input->post("explain_SFZC_radio",true)?$this->input->post("explain_SFZC_radio",true):0



        );
        $this->db->trans_start();
        if(!$id){
            $data["company_id"] = $this->session->userdata('wx_company_id');
            $subsidiary_id_array = $this->session->userdata('wx_subsidiary_id_array');
            $data["subsidiary_id"] = $subsidiary_id_array[0]; //在保存前已经判断用户职级,所以必然存在唯一门店编号
            $data["finance_num"] = $this->create_finance_num();
            $data["user_id"] = $this->session->userdata('wx_user_id');
            $data["create_user"] = $this->session->userdata('wx_user_id');
            $data["create_date"] = date('Y-m-d H:i:s');
            $data["status"] = 1;
            $this->db->insert("finance",$data);
            $id = $this->db->insert_id();

        }else{
            unset($data['finance_wx_num']);
            $this->db->where('id',$id)->update("finance",$data);
        }

        $this->db->trans_complete();//------结束事务
        if ($this->db->trans_status() === FALSE) {
            return -1;
        } else {
            return $id;
        }

    }

    public function save_finance_2(){
        $id = $this->input->post("id");
        $data = array(

            //下面是配偶信息
            "spouse_name" => trim($this->input->post("spouse_name")),
            "spouse_sex" => $this->input->post("spouse_sex"),
            "spouse_native" => $this->input->post("spouse_native"),
            "spouse_age" => $this->input->post("spouse_age")?$this->input->post("spouse_age"):null,
            "spouse_qualifications" => trim($this->input->post("spouse_qualifications")),
            "spouse_workADD" => trim($this->input->post("spouse_workADD")),
            "spouse_position" => trim($this->input->post("spouse_position")),
            "spouse_income" => $this->input->post("spouse_income")?$this->input->post("spouse_income"):null,
            "spouse_SSY" => trim($this->input->post("spouse_SSY")),
            "spouse_code" => trim($this->input->post("spouse_code")),
            "spouse_phone" => trim($this->input->post("spouse_phone")),




        );
        $this->db->trans_start();
        $this->db->where('id',$id)->update("finance",$data);


        $this->db->trans_complete();//------结束事务
        if ($this->db->trans_status() === FALSE) {
            return -1;
        } else {
            return $id;
        }
    }

    public function save_finance_3(){
        $id = $this->input->post("id");
        $data = array(
            "borrower_hasP" => $this->input->post('borrower_hasP'),
            "property_community" => trim($this->input->post("property_community")),
            "property_num" => trim($this->input->post("property_num")),
            "property_estates" => trim($this->input->post("property_estates")),
            "property_area" => trim($this->input->post("property_area")),
            "property_price" => trim($this->input->post("property_price")),
            "property_owner" => trim($this->input->post("property_owner")),
            "property_SF" => trim($this->input->post("property_SF"))?trim($this->input->post("property_SF")):null,
            "property_YG" => trim($this->input->post("property_YG"))?trim($this->input->post("property_YG")):null,
            "property_AJ" => trim($this->input->post("property_AJ"))?trim($this->input->post("property_AJ")):null,


        );
        $this->db->trans_start();

        $this->db->where('id',$id)->update("finance",$data);

        $this->db->trans_complete();//------结束事务
        if ($this->db->trans_status() === FALSE) {
            return -1;
        } else {
            return $id;
        }
    }

    public function save_finance_4($app,$appsecret){
        $id = $this->input->post("id");
        $row_=$this->db->from('finance')->where('id',$id)->get()->row_array();
        $data = array(
            "borrower_img_SFZ1" => $this->input->post('borrower_img_SFZ1')?$this->input->post('borrower_img_SFZ1'):null,
            "borrower_img_SFZ2" => $this->input->post('borrower_img_SFZ2')?$this->input->post('borrower_img_SFZ2'):null,
            "spouse_img_SFZ1" => $this->input->post('spouse_img_SFZ1')?$this->input->post('spouse_img_SFZ1'):null,
            "spouse_img_SFZ2" => $this->input->post('spouse_img_SFZ2')?$this->input->post('spouse_img_SFZ2'):null,
            "img_JHZ1" => $this->input->post('img_JHZ1')?$this->input->post('img_JHZ1'):null,
            "img_JHZ2" => $this->input->post('img_JHZ2')?$this->input->post('img_JHZ2'):null,
            "img_SBZ" => $this->input->post('img_SBZ')?$this->input->post('img_SBZ'):null,
            "img_BDC" => $this->input->post('img_BDC')?$this->input->post('img_BDC'):null,
            "img_ZXBG" => $this->input->post('img_ZXBG')?$this->input->post('img_ZXBG'):null,
            "img_YHLS" => $this->input->post('img_YHLS')?$this->input->post('img_YHLS'):null,
        );
        foreach($data as $key => $v){
            if($v){
                if(file_exists(dirname(SELF).'/uploadfiles/finance/'.$row_['finance_num'].'/'.$v)){

                }else{
                    $data[$key] = $this->getmedia($v,$row_['finance_num'],$app,$appsecret);
                }
            }
        }
        $this->db->trans_start();

        $this->db->where('id',$id)->update("finance",$data);

        $this->db->trans_complete();//------结束事务
        if ($this->db->trans_status() === FALSE) {
            return -1;
        } else {
            return $id;
        }
    }

    public function save_finance_tj(){
        $id = $this->input->post("id");
        $detail = $this->db->from('finance')->where('id',$id)->get()->row_array();
        $data = array(
            "status" => 2,
            "tijiao_date" => date('Y-m-d H:i:s'),
        );
        $this->db->trans_start();

        $this->db->where('id',$this->input->post("id"))->update("finance",$data);

        $this->db->trans_complete();//------结束事务
        if ($this->db->trans_status() === FALSE) {
            return -1;
        } else {
            $msg_type = "新增";
            if($detail['status']==5)
                $msg_type = "重新";
            $data_msg = array(
                'first' => array(
                    'value' => "金融服务".$msg_type."提交成功!",
                    'color' => '#FF0000'
                ),
                'keyword1' => array(
                    'value' => $detail['finance_num'],
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
            $this->wxpost_fin($this->config->item('WX_FIN_SJTJ'),$data_msg,$this->session->userdata('wx_user_id'),"www.funmall.com.cn/finance_wx_user/show_finance_1/{$detail['id']}");
            //发送给用户的店长,如果用户本身职级大于等于店长,就不做通知
            if($this->session->userdata('wx_permission_id') > 4){
                $data_msg['remark']['value'] = "你的员工 ".$this->session->userdata('wx_rel_name')." 成功".$msg_type."提交一单代办业务.";
                $this->db->select('a.id');
                $this->db->from('user a');
                $this->db->join('user_subsidiary b','a.id = b.user_id','left');
                $this->db->where(array(
                    'a.flag'=>1,
                    'a.company_id'=>$this->session->userdata('wx_company_id'),
                    'a.role_id'=>4,
                    'a.openid <>'=>''
                ));
                $this->db->where('a.openid is not null');
                $this->db->where_in('b.subsidiary_id',$this->session->userdata('wx_subsidiary_id_array'));
                $user_list1 = $this->db->get()->result_array();
                foreach($user_list1 as $item){
                    $this->wxpost_fin($this->config->item('WX_FIN_SJTJ'),$data_msg,$item['id'],"www.funmall.com.cn/finance_wx_user/show_finance_1/{$detail['id']}");
                }
            }
            //发送给金融服务绑定微信号
            if($detail['borrower_openid']){
                $data_msg['first']['value'] = "您的金融服务已被提交!";
                $data_msg['remark']['value'] = "感谢您对我们工作的信任和支持,审核结果会在第一时间通过微信反馈.";
                $this->wxpost_finByOpenid($this->config->item('WX_FIN_SJTJ'),$data_msg,$detail['borrower_openid'],"www.funmall.com.cn/finance_wx_borrower/index");
            }
            //发送给金融管理人员
            $data_msg['first']['value'] = "有一单".$msg_type."提交的金融服务";
            $data_msg['remark']['value'] = "用户 ".$this->session->userdata('wx_rel_name')." 成功".$msg_type."提交一单代办业务.";

            $this->db->select('a.id');
            $this->db->from('user a');
            $this->db->join('user_position b','a.id = b.user_id','left');
            $this->db->where(array(
                'a.flag'=>1,
                'b.pid'=>12,
                'a.openid <>'=>''
            ));
            $this->db->where('a.openid is not null');
            $user_list2 = $this->db->get()->result_array();
            foreach($user_list2 as $item2){
                $this->wxpost_fin($this->config->item('WX_FIN_SJTJ'),$data_msg,$item2['id'],"www.funmall.com.cn/finance_wx_user/show_finance_1/{$detail['id']}");
            }
            return 1;
        }
    }

    public function status_finance_save(){
        $old_detail = $this->db->from('finance')->where('id',$this->input->post('finance_id'))->get()->row_array();
        $data = array(
            "status"=>$this->input->post("status"),
            "meno_text"=>$this->input->post("meno_text")
        );
        switch ($data['status']){
            case 3:
            case 5:
                $data['check_date']=date('Y-m-d H:i:s');
                break;
            case 4:
            case -1:
                $data['end_date']=date('Y-m-d H:i:s');
                break;
        }
        $res = $this->db->where('id',$this->input->post('finance_id'))->update('finance',$data);
        $this->db->where('finance_id',$this->input->post('finance_id'))->delete('finance_result');
        $ed_arr = $this->input->post('ed');
        $nh_arr = $this->input->post('nh');
        $minzq_arr = $this->input->post('minzq');
        $maxzq_arr = $this->input->post('maxzq');
        //$type_arr = $this->input->post('type');
        if($ed_arr && is_array($ed_arr)){
            foreach($ed_arr as $idx => $ed) {
                $fin_res = array(
                    'finance_id' => $this->input->post('finance_id'),
                    'ed' => $ed,
                    'nh' => $nh_arr[$idx],
                    'minzq' => $minzq_arr[$idx],
                    'maxzq' => $maxzq_arr[$idx],
                    'type' => $this->input->post('type'.$idx),
                );
                $this->db->insert('finance_result', $fin_res);
            }
        }
        //发送微信通知
        $detail = $this->db->from('finance')->where('id',$this->input->post('finance_id'))->get()->row_array();;
        if($old_detail['status'] != $detail['status']){
            $data_msg = array(
                'first' => array('value' => "金融服务提交成功!", 'color' => '#FF0000'),
                'keyword1' => array('value' => $detail['finance_num'], 'color' => '#FF0000'),
                'keyword2' => array('value' => date('Y-m-d H:i:s'), 'color' => '#FF0000'),
                'remark' => array('value' => '感谢你对我们工作的信任', 'color' => '#FF0000')
            );
            switch($detail['status']){
                case 2://待审核
                    $data_msg['first']['value'] = "您的金融服务已被提交!";
                    $data_msg['remark']['value'] = "感谢你对我们工作的信任和支持!";
                    break;
                case 3://审核通过
                    $data_msg['first']['value'] = "恭喜!您的金融服务已审核通过!";
                    $data_msg['remark']['value'] = "请留意平台的金融方案,我们会尽快与您联系.感谢你对我们工作的信任和支持!";
                    break;
                case 4://结案
                    $data_msg['first']['value'] = "您的金融服务已顺利结案.";
                    $data_msg['remark']['value'] = "感谢你对我们工作的信任和支持!";
                    break;
                case 5://审核不通过
                    $data_msg['first']['value'] = "很抱歉,您提交的金融服务未通过平台审核!";
                    $data_msg['remark']['value'] = "请仔细阅读审核信息,修改申请信息后可再次提交申请.";
                    break;
                case -1://关闭
                    $data_msg['first']['value'] = "很抱歉,您提交的金融服务已被关闭!";
                    $data_msg['remark']['value'] = "";
                    break;
                default:
                    $data_msg['first']['value'] = "金融服务";
                    $data_msg['remark']['value'] = "感谢你对我们工作的信任和支持!";
                    break;
            }
            //发送给金融服务绑定微信号
            if($detail['borrower_openid']){
                $this->wxpost_finByOpenid($this->config->item('WX_FIN_SJTJ'),$data_msg,$detail['borrower_openid'],"www.funmall.com.cn/finance_wx_borrower/index");
            }
            $this->wxpost_fin($this->config->item('WX_FIN_SJTJ'),$data_msg,$detail['user_id'],"www.funmall.com.cn/finance_wx_user/show_finance_1/{$detail['id']}");
        }

        return $res;
    }

    private function getmedia($media_id,$finance_num,$app,$appsecret){
        $accessToken = $this->get_token($app,$appsecret);
        $url = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=".$accessToken."&media_id=".$media_id;

        if (is_readable('./uploadfiles/finance') == false) {
            mkdir('./uploadfiles/finance', 0777, true);
        }
        if (is_readable('./uploadfiles/finance/'.$finance_num) == false) {
            mkdir('./uploadfiles/finance/'.$finance_num, 0777, true);
        }
        $file_name = date('YmdHis').rand(1000,9999).'.jpg';
        $targetName = './uploadfiles/finance/'.$finance_num.'/'.$file_name;
        //file_put_contents('/var/yy.txt', $url);

        $ch = curl_init($url); // 初始化
        $fp = fopen($targetName, 'wb'); // 打开写入
        curl_setopt($ch, CURLOPT_FILE, $fp); // 设置输出文件的位置，值是一个资源类型
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);

        return $file_name;
    }
}
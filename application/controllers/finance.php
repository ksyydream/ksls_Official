<?php
/**
 * Created by PhpStorm.
 * User: bin.shen
 * Date: 5/31/16
 * Time: 16:23
 */

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Finance extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('finance_model');
        $this->load->model('agenda_model');
        $permission_id = $this->session->userdata('login_permission_id');
        $this->assign('permission_id', $permission_id);
        $position_id = $this->session->userdata('login_position_id_array');
        $this->assign('position_id', $position_id);
    }

    function _remap($method,$params = array()) {
        if(!$this->session->userdata('login_user_id') || in_array(1,$this->session->userdata('login_position_id_array'))) {
            redirect(site_url('/'));
        } else {

                if(in_array(12,$this->session->userdata('login_position_id_array'))){
                    if($method == 'add_finance'
                        || $method == 'save_finance_1'
                        || $method == 'save_finance_2'
                        || $method == 'edit_finance_2'
                        || $method == 'edit_finance_2'
                        || $method == 'save_finance_tj'
                        || $method == 'save_finance_3'
                        || $method == 'go_finance_1'
                        || $method == 'finance_list'){
                        redirect(site_url('/finance/finance_list_other'));
                        exit();
                    }
                }else{
                    if($method == 'status_finance_save'){
                        redirect(site_url('/'));
                        exit();
                    }
                    if($this->session->userdata('login_permission_id') < 3){
                        if($method == 'add_finance'
                            || $method == 'save_finance_1'
                            || $method == 'save_finance_2'
                            || $method == 'edit_finance_2'
                            || $method == 'edit_finance_2'
                            || $method == 'save_finance_tj'
                            || $method == 'save_finance_3'
                            || $method == 'go_finance_1'
                            || $method == 'finance_list'){
                            redirect(site_url('/finance/finance_list_other'));
                            exit();
                        }
                    }
                    if($this->session->userdata('login_permission_id') > 4){
                        if($method == 'finance_list_other'){
                            redirect(site_url('/finance/finance_list'));
                            exit();
                        }
                    }
                }



            return call_user_func_array(array($this, $method), $params);
        }
    }
    //上传图片
    public function upload_image($finance_num){
        if (is_readable('./././uploadfiles/finance') == false) {
            mkdir('./././uploadfiles/finance');
        }
        if (is_readable('./././uploadfiles/finance/'.$finance_num) == false) {
            mkdir('./././uploadfiles/finance/'.$finance_num);
        }
        $config['upload_path'] = './uploadfiles/finance/'.$finance_num;
        $config['allowed_types'] = 'gif|jpg|png|jpeg';
        $config['encrypt_name'] = true;
        $config['max_size'] = '1000';
        $this->load->library('upload', $config);

        if ( ! $this->upload->do_upload('userfile')){
            echo 1;
        }else{
            $pic_arr = $this->upload->data();
            echo $pic_arr['file_name'];
        }
    }
    //获取 我的金融 列表
    public function finance_list($page=1){
        $data = $this->finance_model->finance_list($page,$this->session->userdata('login_user_id'));
        $this->assign('finance_list', $data);
        $pager = $this->pagination->getPageLink('/finance/finance_list', $data['countPage'], $data['numPerPage']);
        $this->assign('pager', $pager);
        $this->display('finance/finance-list.html');
    }

    public function finance_list_other($page=1){
        $position_id = $this->session->userdata('login_position_id_array');
        $permission_id = $this->session->userdata('login_permission_id');
        $company_id = NULL;
        $subsidiary_id = NULL;
        $user_id = NULL;
        if($permission_id == 1 || in_array(12,$position_id)){ // 如果是管理员,或者金融管理专员
            $company_list = $this->agenda_model->get_company_list();
            $this->assign('company_list', $company_list);
            if($this->input->POST('company')) {
                $this->assign('company', $this->input->POST('company'));
                $subsidiary_list = $this->agenda_model->get_subsidiary_list($this->input->POST('company'), NULL);
            } else {
                $company_id = null;
                $subsidiary_list = $this->agenda_model->get_subsidiary_list($company_id, NULL);

            }
            $this->assign('subsidiary_list', $subsidiary_list);
            if($this->input->POST('subsidiary')) {
                $this->assign('subsidiary', $this->input->POST('subsidiary'));

                $user_list = $this->agenda_model->get_subsidiary_user_list_7($this->input->POST('subsidiary'));
                $this->assign('user_list', $user_list);
            }
            if($this->input->POST('user')) {
                $this->assign('user', $this->input->POST('user'));
            }
            $company_id = $this->input->post('company')?$this->input->post('company'):NULL;
            $subsidiary_id = $this->input->POST('subsidiary')?$this->input->post('subsidiary'):NULL;
            $user_id = $this->input->POST('user')?$this->input->POST('user'):NULL;

        }elseif($permission_id <= 3){ //总经理 和 区域经理可以查看不同门店
            $company_id = $this->session->userdata('login_company_id');
            if($permission_id == 2) {
                $subsidiary_list = $this->agenda_model->get_subsidiary_list($company_id, NULL);
            } else if($permission_id < 5) {
                $subsidiary_id = $this->session->userdata('login_subsidiary_id_array');
                $subsidiary_list = $this->agenda_model->get_subsidiary_list($company_id, $subsidiary_id);
            }
            $this->assign('subsidiary_list', $subsidiary_list);
            if($this->input->POST('subsidiary')) {
                $this->assign('subsidiary', $this->input->POST('subsidiary'));

                $user_list = $this->agenda_model->get_subsidiary_user_list($this->input->POST('subsidiary'));
                $this->assign('user_list', $user_list);
            }elseif(!$this->input->post('subsidiary') && $permission_id < 5 && $permission_id > 3){
                $subsidiary_id_array = $this->session->userdata('login_subsidiary_id_array');
                $this->assign('subsidiary', $subsidiary_id_array[0]);
                $user_list = $this->agenda_model->get_subsidiary_user_list($subsidiary_id_array[0]);
                $this->assign('user_list', $user_list);
            }
            if($this->input->POST('user')) {
                $this->assign('user', $this->input->POST('user'));
            }
            $subsidiary_id = $this->input->POST('subsidiary')?$this->input->post('subsidiary'):$this->session->userdata('login_subsidiary_id_array');
            $user_id = $this->input->POST('user')?$this->input->POST('user'):NULL;
        }else{
            $company_id = $this->session->userdata('login_company_id');
            $subsidiary_id = $this->session->userdata('login_subsidiary_id_array');
            $this->assign('subsidiary', $subsidiary_id[0]);
            $user_list = $this->agenda_model->get_subsidiary_user_list($subsidiary_id[0]);
            $this->assign('user_list', $user_list);
            if($this->input->POST('user')) {
                $this->assign('user', $this->input->POST('user'));
            }
            $user_id = $this->input->POST('user')?$this->input->POST('user'):NULL;
        }
        $data = $this->finance_model->finance_list($page,$user_id,$subsidiary_id,$company_id);
        //die(var_dump($data));
        $this->assign('finance_list', $data);
        $pager = $this->pagination->getPageLink('/finance/finance_list_other', $data['countPage'], $data['numPerPage']);
        $this->assign('pager', $pager);
        $this->display('finance/finance_list_other.html');
    }
    //获取详情
    public function finance_detail($id){
        $data = $this->finance_model->get_detail($id);
        $this->assign('data', $data);
        $this->display('tax_calculate.html');
    }

    //打开基本信息页面 第一页
    public function add_finance($id = ""){
        if($id != ""){
            $power_ = $this->finance_model->save_power($id);
            if($power_ == 1){
                $data = $this->finance_model->get_detail($id);
                if($data)
                    $this->assign('data', $data);
            }
        }else{
            $finance_wx_num = time()."_".rand(1000000,9000000);
            $this->assign('finance_wx_num',$finance_wx_num);
        }
        $this->display('finance/finance.html');
    }

    public function go_finance_1(){
        $id = $this->input->post("id");
        if(!$id)
            redirect(site_url('/finance/add_finance'));
        $power_ = $this->finance_model->save_power($id);
        if($power_ != 1){
            redirect(site_url('/'));
        }
        $res = $this->finance_model->save_finance_2();
        if($res > 0){
            redirect(site_url('/finance/add_finance')."/".$id );
        }else{
            redirect(site_url('/finance/add_finance'));
        }
    }

    //保存基本信息页面 第一页
    public function save_finance_1(){
        if($id = $this->input->post('id')){
            $power_ = $this->finance_model->save_power($id);
            if($power_ != 1){
                redirect(site_url('/finance/finance_list'));
            }
        }else{
            if(trim($this->input->post("finance_wx_num"))){
                $id = $this->finance_model->getIdByWxNum();
                if($id>0){
                    $power_ = $this->finance_model->save_power($id);
                    if($power_ != 1){
                        redirect(site_url('/finance/finance_list'));
                    }
                }
            }
        }

        $res = $this->finance_model->save_finance_1();
        if($res > 0){
            redirect(site_url('/finance/edit_finance_2')."/".$res);
        }else{
            redirect(site_url('/finance/add_finance'));
        }
    }

    //保存基本信息页面 第二页
    public function save_finance_2(){
        $id = $this->input->post("id");
        if(!$id)
            redirect(site_url('/finance/add_finance'));
        $power_ = $this->finance_model->save_power($id);
        if($power_ != 1){
            redirect(site_url('/'));
        }
        $res = $this->finance_model->save_finance_2();
        if($res > 0){
            redirect(site_url('/finance/edit_finance_3')."/".$id );
        }else{
            redirect(site_url('/finance/add_finance'));
        }
    }

    //打开基本信息页面 第二页
    public function edit_finance_2($id){
        if(!$id)
            redirect(site_url('/finance/add_finance'));
        $power_ = $this->finance_model->save_power($id);
        if($power_ != 1){
            redirect(site_url('/'));
        }
        $data = $this->finance_model->get_detail($id);
        $this->assign('data', $data);
        $this->display('finance/finance - step2.html');
    }

    public function edit_finance_3($id){
        if(!$id)
            redirect(site_url('/finance/add_finance'));
        $power_ = $this->finance_model->save_power($id);
        if($power_ != 1){
            redirect(site_url('/'));
        }
        $check_ = $this->finance_model->check_ym2($id);
        if($check_ != 1){
            redirect(site_url('/finance/edit_finance_2').'/'.$id);
        }
        $data = $this->finance_model->get_detail($id);
        $this->assign('data', $data);
        $this->display('finance/finance - step3.html');
    }

    public function save_finance_tj(){
        $id = $this->input->post("id");
        if(!$id)
            redirect(site_url('/finance/add_finance'));
        $power_ = $this->finance_model->save_power($id);
        if($power_ != 1){
            redirect(site_url('/'));
        }
        $res = $this->finance_model->save_finance_3();
        if($res > 0){
            $tj = $this->finance_model->save_finance_tj();
            if($tj == 1){
                redirect(site_url('/finance/finance_list'));
            }else{
                redirect(site_url('/finance/edit_finance_3').'/'.$id);
            }
        }else{
            redirect(site_url('/finance/add_finance'));
        }
    }

    public function save_finance_3($flag = 1){
        $id = $this->input->post("id");
        if(!$id)
            redirect(site_url('/finance/add_finance'));
        $power_ = $this->finance_model->save_power($id);
        if($power_ != 1){
            redirect(site_url('/'));
        }
        $res = $this->finance_model->save_finance_3();
        if($res > 0){
            if($flag == 1){
                redirect(site_url('/finance/edit_finance_2')."/".$id );
            }else{
                redirect(site_url('/finance/finance_list'));
            }

        }else{
            redirect(site_url('/finance/add_finance'));
        }
    }

    public function view_finance($id){
        $view_p = $this->finance_model->view_power($id);
        if($view_p != 1)
            redirect(site_url('/finance/finance_list'));
        $data = $this->finance_model->get_detail($id);
        $this->assign('data', $data);
        $this->display('finance/finance_detail.html');
    }

    public function view_finance_2($id){
        $view_p = $this->finance_model->view_power($id);
        if($view_p != 1)
            redirect(site_url('/finance/finance_list'));
        $data = $this->finance_model->get_detail($id);
        $this->assign('data', $data);
        $this->display('finance/finance_detail1.html');
    }

    public function status_finance_save(){
        $position_id = $this->session->userdata('login_position_id_array');
        $permission_id = $this->session->userdata('login_permission_id');
        if($permission_id == 1 || in_array(12,$position_id)){

        }else{
            redirect(site_url('/'));
        }
        if(!$id = $this->input->post('finance_id'))
            redirect(site_url('/'));
        if(!in_array($this->input->post("status"),array(2,3,4,5,-1)))
            redirect(site_url('/'));
        $res = $this->finance_model->status_finance_save();
        redirect(site_url('/finance/finance_list_other'));
    }

    public function show_finance_result($id){
        $view_p = $this->finance_model->view_power($id);
        if($view_p != 1)
            redirect(site_url('/finance/finance_list'));
        $data = $this->finance_model->get_detail($id);
        $this->assign('data', $data);
        $this->display('finance/finance_result.html');
    }

    public function set_base_code($id){

            require_once (APPPATH . 'libraries/Base64.php');
            $uid = 'FIN_'.$id.'_'.time();
            //$uid = base64_encode($uid);
            $uid = base64::encrypt($uid, $this->config->item('token_key'));
            return base64_encode($uid);

    }

    public function show_code(){
        $tf_id = $_POST['finance_id'];
        $view_p = $this->finance_model->view_power($tf_id);
        $code = $this->set_base_code($tf_id);
        $this->assign('finance_id', $tf_id);
        if($view_p != 1)
            return -1;
        //$this->assign('result', "http:%2F%2Ffinance_wx%2Fcode_login".'/'.$code);
        $this->assign('result', urlencode($code));
        $this->display('finance/popup_code.html');
    }

    public function show_img($id){
        //$code = $this->set_base_code($id);
        $this->load->config('wxpay_config');
        require_once (APPPATH . 'libraries/phpqrcode.php');
        $value = $this->config->item('base_url_wx').'/finance_wx/code_login/'.$id; //二维码内容
//生成二维码图片
        QRcode::png($value);

    }

    public function test(){
        $data_msg = array(
            'first' => array(
                'value' => "金融服务提交成功!",
                'color' => '#FF0000'
            ),
            'keyword1' => array(
                'value' => "test",
                'color' => '#FF0000'
            ),
            'keyword2' => array(
                'value' => date('Y-m-d H:m:s'),
                'color' => '#FF0000'
            ),
            'remark' => array(
                'value' => '感谢你对我们工作的信任',
                'color' => '#FF0000'
            )
        );
        //发送给用户自己
        $this->finance_model->wxpost_fin($this->config->item('WX_FIN_SJTJ'),$data_msg,$this->session->userdata('login_user_id'),'www.baidu.com');
    }

    public function test2($id=null){
        $check_ = $this->finance_model->check_ym2($id);
        if($check_ != 1){
            redirect(site_url('/finance/edit_finance_2').'/'.$id);
        }
        die(var_dump($check_));
    }


}
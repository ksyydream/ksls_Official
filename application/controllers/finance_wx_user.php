<?php
/**
 * Created by PhpStorm.
 * User: bin.shen
 * Date: 5/31/16
 * Time: 16:23
 */

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once "finwx_Controller.php";
class Finance_wx_user extends Finwx_Controller
{

    public function __construct()
    {
        parent::__construct();
        if(!$this->session->userdata('wx_user_id')){
            redirect('finance_wx/login');
        }
        $this->assign('rel_name',$this->session->userdata('wx_rel_name'));
        $this->assign('role_name',$this->session->userdata('wx_role_name'));
        $permission_id = $this->session->userdata('wx_permission_id');
        $this->assign('permission_id', $permission_id);
        $position_id = $this->session->userdata('wx_position_id_array');
        $this->assign('position_id', $position_id);
        $user_id = $this->session->userdata('wx_user_id');
        $this->assign('user_id', $user_id);
    }

    public function logout(){
        $this->finance_wx_model->logout();
        redirect('finance_wx/login');
    }

    public function index(){
        $main_data = $this->finance_wx_model->get_main_data();
        $this->assign('search_info_hidden',$this->input->post('search_info')?$this->input->post('search_info'):'');
        $this->assign('main_data',$main_data);
        $this->display('finance/weixin/index.html');
    }

    public function index_status($status=null){
        if(!$status)
            $status = $this->input->post('status');
        $main_data = $this->finance_wx_model->get_main_data();
        $this->assign('search_info_hidden',$this->input->post('search_info')?$this->input->post('search_info'):'');
        $this->assign('main_data',$main_data);
        $this->assign('status',$status);
        $this->display('finance/weixin/index-classify.html');
    }

    /*public function list_finance($page=1){
        $main_data = $this->finance_wx_model->get_main_data();
        $this->cismarty->assign('main_data',$main_data);
        // $this->cismarty->assign('jindu_type',$jindu_type);
        $data = $this->finance_model->finance_list($page,$this->session->userdata('wx_user_id'));
        $base_url = "/finance_wx_user/list_finance/";
        $pager = $this->pagination->getPageLink($base_url, $data['countPage'], $data['numPerPage']);
        $this->cismarty->assign('pager',$pager);
        $this->cismarty->assign('data',$data);
        $this->cismarty->display('finance/user_finance_list.html');
    }*/

    public function list_finance_loaddata($page=1){
        $position_id = $this->session->userdata('wx_position_id_array');
        $permission_id = $this->session->userdata('wx_permission_id');
        $company_id = NULL;
        $subsidiary_id = NULL;
        $user_id = NULL;
        if($permission_id == 1 || in_array(12,$position_id)){ // 如果是管理员,或者金融管理专员

        }elseif($permission_id <= 3) { //总经理 和 区域经理可以查看不同门店
            $company_id = $this->session->userdata('wx_company_id');
        }elseif($permission_id == 4) {
            $company_id = $this->session->userdata('wx_company_id');
            $subsidiary_id = $this->session->userdata('wx_subsidiary_id_array');
        }else{
            $user_id = $this->session->userdata('wx_user_id');
        }
        $data = $this->finance_model->finance_list($page,$user_id,$subsidiary_id,$company_id,6);
        $this->cismarty->assign('data',$data);
        $this->cismarty->display('finance/weixin/index_loaddata.html');
    }

    public function add_finance(){
        $finance_wx_num = time()."_".rand(1000000,9000000);
        $this->cismarty->assign('finance_wx_num',$finance_wx_num);
        $this->cismarty->display('finance/weixin/admin-form.html');
    }

    public function save_finance_1(){
        if($this->input->post('id')){
            $power_ = $this->finance_wx_model->save_power($this->input->post('id'));
            if($power_ != 1){
                $this->show_message('服务已提交,或无保存权限！',site_url('finance_wx_user/index'));
            }
        }
        $rs = $this->finance_wx_model->save_finance_1();
        if($rs >= 1){
            redirect(site_url('/finance_wx_user/edit_finance_detail/'.$rs.'/2'));
        }else if($rs == -2){
            $this->show_message('服务已申请！');
        }else{
            $this->show_message('操作失败！',site_url('finance_wx_user/index'));
        }
    }

    public function save_finance_2(){
        if($this->input->post('id')){
            $power_ = $this->finance_wx_model->save_power($this->input->post('id'));
            if($power_ != 1){
                $this->show_message('服务已提交,或无保存权限！',site_url('finance_wx_user/index'));
            }
        }
        $rs = $this->finance_wx_model->save_finance_2();
        if($rs >= 1){
            redirect(site_url('/finance_wx_user/edit_finance_detail/'.$rs.'/3'));
        }else if($rs == -2){
            $this->show_message('服务已申请！');
        }else{
            $this->show_message('操作失败！');
        }
    }

    public function save_finance_3(){
        if($this->input->post('id')){
            $power_ = $this->finance_wx_model->save_power($this->input->post('id'));
            if($power_ != 1){
                $this->show_message('服务已提交,或无保存权限！',site_url('finance_wx_user/index'));
            }
        }
        $rs = $this->finance_wx_model->save_finance_3();
        if($rs >= 1){
            redirect(site_url('/finance_wx_user/edit_finance_detail/'.$rs.'/4'));
        }else if($rs == -2){
            $this->show_message('服务已申请！');
        }else{
            $this->show_message('操作失败！');
        }
    }

    public function save_finance_4(){
        if($this->input->post('id')){
            $power_ = $this->finance_wx_model->save_power($this->input->post('id'));
            if($power_ != 1){
                $this->show_message('服务已提交,或无保存权限！',site_url('finance_wx_user/index'));
            }
        }
        $rs = $this->finance_wx_model->save_finance_4($this->wxconfig['appid'],$this->wxconfig['appsecret']);
        if($rs >= 1){
            redirect(site_url('finance_wx_user/index'));
        }else if($rs == -2){
            $this->show_message('服务已申请！');
        }else{
            $this->show_message('操作失败！');
        }
    }

    public function tj_finance(){
        if($this->input->post('id')){
            $power_ = $this->finance_wx_model->save_power($this->input->post('id'));
            if($power_ != 1){
                $this->show_message('服务已提交,或无保存权限！',site_url('finance_wx_user/index'));
            }
        }
        $rs = $this->finance_wx_model->save_finance_4($this->wxconfig['appid'],$this->wxconfig['appsecret']);
        if($rs >= 1){
            $tj = $this->finance_wx_model->save_finance_tj();
            if($tj == 1){
                redirect(site_url('/finance_wx_user/index'));
            }else{
                redirect(site_url('/finance_wx_user/index'));//预留,如果在提交前需要 判断一些验证可在这里做处理
            }
        }else if($rs == -2){
            $this->show_message('服务已申请！');
        }else{
            $this->show_message('操作失败！');
        }
    }

    public function go_finance($flag){
        if($id = $this->input->post('id')){
            $power_ = $this->finance_wx_model->save_power($id);
            if($power_ != 1){
                $this->show_message('服务已提交,或无保存权限！',site_url('finance_wx_user/index'));
            }
        }else{
            redirect(site_url('finance_wx_user/index'));
        }
        switch($flag){
            case 1:
                $this->finance_wx_model->save_finance_2();
                break;
            case 2:
                $this->finance_wx_model->save_finance_3();
                break;
            case 3:
                $this->finance_wx_model->save_finance_4($this->wxconfig['appid'],$this->wxconfig['appsecret']);
                break;
            default:
                redirect(site_url('finance_wx_user/index'));
        }
        $this->edit_finance_detail($id,$flag,2);
    }

    public function edit_finance_detail($id,$html=null,$order=1){
        if($id){
            $power_ = $this->finance_wx_model->save_power($id);
            if($power_ != 1){
                $this->show_message('服务已提交,或无保存权限！',site_url('finance_wx_user/index'));
            }
        }else{
            redirect(site_url('finance_wx_user/index'));
        }
        //die('asd');
        $data = $this->finance_model->get_detail($id);
        $this->cismarty->assign('data',$data);
        $this->cismarty->assign('finance_wx_num',$data['finance_wx_num']);
        if(!$html)
            redirect(site_url('finance_wx_user/index'));
        if($order == 1){
            if($html == 2 && $data['borrower_marriage']==2)
                $html = 3;
            if($html == 3 && $data['borrower_hasP']==2)
                $html = 4;
        }else{
            if($html == 3 && $data['borrower_hasP']==2)
                $html = 2;
            if($html == 2 && $data['borrower_marriage']==2)
                $html = 1;
        }
        switch($html){
            case 1:
                $this->cismarty->display('finance/weixin/admin-form.html');
                break;
            case 2:
                $this->cismarty->display('finance/weixin/admin-form-1.html');
                break;
            case 3:
                $this->cismarty->display('finance/weixin/admin-form-2.html');
                break;
            case 4:
                $this->buildWxData();
                $this->cismarty->display('finance/weixin/admin-form-4.html');
                break;
            default:
                redirect(site_url('finance_wx_user/index'));
        }

    }

    public function approve($id){
        if(!$id){
            redirect(site_url('finance_wx_user/index'));
        }
        $position_id = $this->session->userdata('wx_position_id_array');
        $permission_id = $this->session->userdata('wx_permission_id');
        if($permission_id == 1 || in_array(12,$position_id)){
            $data = $this->finance_model->get_detail($id);
            $this->cismarty->assign('data',$data);
            $this->cismarty->display('finance/weixin/admin-approve.html');
        }else{
            redirect(site_url('finance_wx_user/index'));
        }
    }

    public function status_finance_save(){
        $position_id = $this->session->userdata('wx_position_id_array');
        $permission_id = $this->session->userdata('wx_permission_id');
        if($permission_id == 1 || in_array(12,$position_id)){

        }else{
            redirect(site_url('finance_wx_user/index'));
        }
        if(!$id = $this->input->post('finance_id'))
            redirect(site_url('finance_wx_user/index'));
        if(!in_array($this->input->post("status"),array(2,3,4,5,-1)))
            redirect(site_url('finance_wx_user/index'));
        $res = $this->finance_wx_model->status_finance_save();
        redirect(site_url('/finance_wx_user/index'));
    }

    public function show_finance_1($id){
        if($id){
            $power_ = $this->finance_wx_model->view_power($id);
            if($power_ != 1){
                $this->show_message('无查看权限!',site_url('finance_wx_user/index'));
            }
        }else{
            redirect(site_url('finance_wx_user/index'));
        }
        $data = $this->finance_model->get_detail($id);
        $this->cismarty->assign('data',$data);
        $this->cismarty->display('finance/weixin/admin-detail.html');

    }

    public function show_finance_2($id){
        if($id){
            $power_ = $this->finance_wx_model->view_power($id);
            if($power_ != 1){
                $this->show_message('无查看权限!',site_url('finance_wx_user/index'));
            }
        }else{
            redirect(site_url('finance_wx_user/index'));
        }
        $data = $this->finance_model->get_detail($id);
        $this->cismarty->assign('data',$data);
        $this->buildWxData();
        $this->cismarty->display('finance/weixin/admin-picture-detail.html');

    }

    public function show_plan($id){
        if($id){
            $power_ = $this->finance_wx_model->view_power($id);
            if($power_ != 1){
                $this->show_message('无查看权限!',site_url('finance_wx_user/index'));
            }
        }else{
            redirect(site_url('finance_wx_user/index'));
        }
        $data = $this->finance_model->get_detail($id);
        $this->cismarty->assign('data',$data);
        $this->cismarty->display('finance/weixin/admin-plan.html');
    }

    public function set_base_code($id){

        require_once (APPPATH . 'libraries/Base64.php');
        $uid = 'FIN_'.$id.'_'.time();
        //$uid = base64_encode($uid);
        $uid = base64::encrypt($uid, $this->config->item('token_key'));
        return base64_encode($uid);

    }

    public function show_code($id){
        if($id){
            $power_ = $this->finance_wx_model->view_power($id);
            if($power_ != 1){
                $this->show_message('无查看权限！',site_url('finance_wx_user/index'));
            }
        }else{
            redirect(site_url('finance_wx_user/index'));
        }
        $code = $this->set_base_code($id);
        $this->cismarty->assign('finance_id', $id);
        $this->cismarty->assign('result', urlencode($code));
        $this->load->config('wxpay_config');
        $this->buildWxData();
        $this->cismarty->assign('url_base', $this->config->item('base_url_wx'));
        $this->cismarty->display('finance/weixin/admin-ewm.html');
    }

    public function show_img($id){
        //$code = $this->set_base_code($id);
        $this->load->config('wxpay_config');
        require_once (APPPATH . 'libraries/phpqrcode.php');
        $value = $this->config->item('base_url_wx').'/finance_wx/code_login/'.$id; //二维码内容
//生成二维码图片
        QRcode::png($value);

    }
}
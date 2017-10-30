<?php
/**
 * Created by PhpStorm.
 * User: bin.shen
 * Date: 5/7/16
 * Time: 13:53
 */

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Account extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('account_model');
    }

    function _remap($method,$params = array()) {
        if(!$this->session->userdata('login_user_id')) {
            redirect(site_url('/'));
        } else {
            if(!in_array(7,$this->session->userdata('login_position_id_array'))){
                if($method == 'recharge_list'){
                    redirect(site_url('/account/company_account'));
                    exit();
                }
                if($method == 'save_sum'){
                    redirect(site_url('/account/company_account'));
                    exit();
                }
                if($method == 'mo_recharge'){
                    redirect(site_url('/account/company_account'));
                    exit();
                }
            }
            $position_id = $this->session->userdata('login_position_id_array');
            $this->assign('position_id', $position_id);
            return call_user_func_array(array($this, $method), $params);
        }
    }

    public function company_account($page=1,$company_id=null)
    {
        $position_array = $this->session->userdata('login_position_id_array');
        if(in_array(7,$position_array)){
            if($this->input->post('company_id')){
                $company_id = $this->input->post('company_id');
            }
        }else{
            $company_id = $this->session->userdata('login_company_id');
        }
        $data = $this->account_model->company_account($page,$company_id);
        $this->assign('company_account', $data);
        $pager = $this->pagination->getPageLink('/account/company_account', $data['countPage'], $data['numPerPage']);
        $this->assign('pager', $pager);
        $this->display('company_account.html');
    }

    public function mo_recharge($id)
     {
        $data = $this->account_model->mo_recharge($id);
         $this->assign('company_id', $id);
         $this->assign('company_info', $data);
         $this->display('mo_recharge.html');
     }

    public function recharge_list($page=1){
        $data = $this->account_model->recharge_list($page);
        $this->assign('recharge_list', $data);
        $pager = $this->pagination->getPageLink('/account/recharge_list', $data['countPage'], $data['numPerPage']);
        $this->assign('pager', $pager);
        $this->display('recharge_list.html');
    }

    public function save_sum(){
        $this->account_model->save_sum();
        redirect(site_url('/account/company_account/1/'.$this->input->post('company_id')));
    }

    public function alipay_recharge($id)
    {
        $data = $this->account_model->mo_recharge($id);
        $this->assign('company_id', $id);
        $this->assign('company_info', $data);
        $this->display('alipay_recharge.html');
    }

    public function save_order(){
        $res = $this->account_model->save_order();
        if($res == -1){
            redirect(site_url('account/recharge_list'));
            exit();
        }
        $this->load->config('wxpay_config');
        $wxconfig['appid']=$this->config->item('appid');
        $wxconfig['mch_id']=$this->config->item('mch_id');
        $wxconfig['apikey']=$this->config->item('apikey');
        $wxconfig['appsecret']=$this->config->item('appsecret');
        $wxconfig['sslcertPath']=$this->config->item('sslcertPath');
        $wxconfig['sslkeyPath']=$this->config->item('sslkeyPath');
        $this->load->library('wxpay/Wechatpay',$wxconfig);
        $result = $this->wechatpay->getCodeUrl(
            '房猫服务中心',
            $res,
            $this->input->post('qty')*100,
            'http://www.funmall.com.cn/wxserver/notify',
            $res
        );
        if($result){
            $this->assign('company_id', $this->input->post('company_id'));
            $this->assign('result', $result);
            $this->assign('res', $res);
            $this->display('wxpay.html');
        }else{
            redirect(site_url('account/recharge_list'));
            exit();
        }
    }

    public function check_order($id){
       $res = $this->account_model->check_order($id);
        echo $res;
        die;
    }

    public function tryxiao(){
        $this->display('wxhtml/test.html');
    }

    public function getcommpay(){

            $data=$this->account_model->getcommpay();
            //var_dump($data);
            echo json_encode($data);

    }

    function choice_xiao() {
        $id = $_POST['x'];
        $this->assign('id', $id);
        $this->display('wxhtml/choice_xiao.html');
    }
}
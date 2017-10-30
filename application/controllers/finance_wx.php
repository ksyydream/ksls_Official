<?php
/**
 * Created by PhpStorm.
 * User: bin.shen
 * Date: 5/31/16
 * Time: 16:23
 */

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once "finwx_Controller.php";
class Finance_wx extends Finwx_Controller
{
    protected $wxconfig = array();
    public function __construct()
    {
        parent::__construct();
        if($this->session->userdata('wx_user_id')){
            redirect('finance_wx_user/index');
        }else{
            if($this->session->userdata('wx_finance_id')){
                redirect('finance_wx_borrower/index');
            }
        }
        $this->buildWxData();
    }

    public function login(){

        $this->assign('tabs',0);
        $this->assign('flag',1);
        //$this->display('finance/login.html');
        $this->display('finance/weixin/admin-login.html');
    }

    public function user_login(){
        $res = $this->finance_wx_model->user_login();
        if($res==1){
            redirect('finance_wx_user/index');
        }else{
            $this->cismarty->assign('tabs',1);
            $this->cismarty->assign('flag',-1);
            //$this->cismarty->display('finance/login.html');
            $this->display('finance/weixin/admin-login.html');
        }

    }

    public function finance_login(){
        $res = $this->finance_wx_model->finance_login();
        if($res==1){
            redirect('finance_wx_borrower/index');
        }else{
            $this->cismarty->assign('tabs',0);
            $this->cismarty->assign('flag',-2);
            //$this->cismarty->display('finance/login.html');
            $this->display('finance/weixin/err_login.html');
        }
    }

    public function code_login($code=null){
        $access_token = $this->finance_wx_model->get_token($this->wxconfig['appid'],$this->wxconfig['appsecret']);
        $rs = file_get_contents("https://api.weixin.qq.com/cgi-bin/user/info?access_token={$access_token}&openid={$this->session->userdata('openid')}&lang=zh_CN");
        $rs = json_decode($rs,true);

        if(!$code){
            $code = $this->input->post('finance_wx_num');
        }
        $code = urldecode($code);
        $finance_id = $this->set_base_code($code);

        if($rs['subscribe'] != 1){
            $res = $this->finance_wx_model->code_login($finance_id);
            $img_url = $this->get_or_create_ticket($access_token);
            //redirect($img_url);
            $this->cismarty->assign('img_url',$img_url);
            $this->cismarty->display('finance/wx_guanzhu.html');
            exit();
        }

        if($finance_id==-1){
            $this->cismarty->assign('tabs',0);
            $this->cismarty->assign('flag',-4);
            //$this->cismarty->display('finance/login.html');
            $this->display('finance/weixin/err_login.html');
        }
        if($finance_id==-2){
            $this->cismarty->assign('tabs',0);
            $this->cismarty->assign('flag',-5);
            //$this->cismarty->display('finance/login.html');
            $this->display('finance/weixin/err_login.html');
        }
        $res = $this->finance_wx_model->code_login($finance_id);
        if($res==1){
            redirect('finance_wx_borrower/index');
        }else{
            $this->cismarty->assign('tabs',0);
            $this->cismarty->assign('flag',-3);
            //$this->cismarty->display('finance/login.html');
            $this->display('finance/weixin/err_login.html');
        }
    }

    private function get_or_create_ticket($access_token,$action_name = 'QR_LIMIT_STR_SCENE') {



        $url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=' . $access_token;
        @$post_data->expire_seconds = 2592000;
        @$post_data->action_name = $action_name;
        @$post_data->action_info->scene->scene_str = 'yy';
        $ticket_data = json_decode($this->post($url, $post_data));
        $ticket = $ticket_data->ticket;
        $img_url = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=".urlencode($ticket);
        return $img_url;
    }

    private function post($url, $post_data, $timeout = 300){
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type:application/json;encoding=utf-8',
                'content' => urldecode(json_encode($post_data)),
                'timeout' => $timeout
            )
        );
        $context = stream_context_create($options);
        return file_get_contents($url, false, $context);
    }

}
<?php
/**
 * Created by PhpStorm.
 * User: bin.shen
 * Date: 5/31/16
 * Time: 16:23
 */

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once "finwx_Controller.php";
class Finance_wx_borrower extends Finwx_Controller
{

    public function __construct()
    {
        parent::__construct();
        if(!$this->session->userdata('wx_finance_id')){
            redirect('finance_wx/login');
        }
        $detail['borrower_openid'] = $this->finance_wx_model->get_borrower_openid($this->session->userdata('wx_finance_id'));
        if($detail['borrower_openid']!=$this->session->userdata('openid')){
            $this->logout();
        }
        $this->assign('finance_num',$this->session->userdata('wx_finance_num'));
    }

    public function logout(){
        $this->finance_wx_model->logout();
        redirect('finance_wx/login');
    }

    public function index(){
        $id = $this->session->userdata('wx_finance_id');
        $data = $this->finance_model->get_detail($id);
        $this->cismarty->assign('data',$data);
        $this->cismarty->display('finance/weixin/borrower-plan.html');
    }

    public function detail(){
        $id = $this->session->userdata('wx_finance_id');
        $data = $this->finance_model->get_detail($id);
        $this->cismarty->assign('data',$data);
        $this->cismarty->display('finance/weixin/borrower-detail.html');
    }
    public function prcture(){
        $id = $this->session->userdata('wx_finance_id');
        $data = $this->finance_model->get_detail($id);
        $this->cismarty->assign('data',$data);
        $this->buildWxData();
        $this->cismarty->display('finance/weixin/borrower-picture-detail.html');
    }



}
<?php
/**
 * Created by PhpStorm.
 * User: bin.shen
 * Date: 5/31/16
 * Time: 16:23
 */

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Hire extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('hire_model');
    }

    function _remap($method,$params = array()) {
        if(!$this->session->userdata('login_user_id') || in_array(1,$this->session->userdata('login_position_id_array'))) {
            redirect(site_url('/'));
        } else {
            return call_user_func_array(array($this, $method), $params);
        }
    }

    public function add_hire($id = null,$pageflag=null) {
        if($id){
            $data = $this->hire_model->add_hire($id);

        }else{
            $data = array();
        }
        $this->assign('data', $data);
        $this->assign('pageflag', $pageflag);
        $this->display('add_hire.html');
    }

    public function hire_list($page=1){
        $data = $this->hire_model->hire_list($page);
        $this->assign('hire_list', $data);
        $pager = $this->pagination->getPageLink('/hire/hire_list', $data['countPage'], $data['numPerPage']);
        $this->assign('pager', $pager);
        $this->display('hire_list.html');
    }

    public function save_hire(){
       $ret = $this->hire_model->save_hire();
        if($this->input->post('page_flag')==1){
            redirect(site_url('/hire/hire_deadline_list'));
        }else{
            redirect(site_url('/hire/hire_list'));
        }

    }

    public function hire_deadline_list($page=1){
        $data = $this->hire_model->hire_deadline_list($page);
        $this->assign('hire_list', $data);
        $pager = $this->pagination->getPageLink('/hire/hire_deadline_list', $data['countPage'], $data['numPerPage']);
        $this->assign('pager', $pager);
        $this->display('hire_deadline_list.html');
    }

    public function delete_hire($id=null,$pageflag=null){
        $this->hire_model->delete_hire($id);
        if($pageflag==1){
            redirect(site_url('/hire/hire_deadline_list'));
        }else{
            redirect(site_url('/hire/hire_list'));
        }
    }
}
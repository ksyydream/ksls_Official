<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 16/8/1
 * Time: 上午11:36
 */

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//t
class Dclc extends CI_Controller {
    public function __construct()
    {
        parent::__construct();
        ini_set('date.timezone','Asia/Shanghai');
        $this->load->model('dclc_model');
    }

    //重载smarty方法assign
    public function assign($key,$val) {
        $this->cismarty->assign($key,$val);
    }

    //重载smarty方法display
    public function display($html) {
        $this->cismarty->display($html);
    }

    public function index(){
        $this->display('dclc/dclc_info.html');
    }

    public function save_info(){
        $res = $this->dclc_model->save_info();
        echo $res;
        /*if($res==1){
            $this->display('dclc/dclc_info.html');
        }else{
            $this->display('dclc/dclc_info.html');
        }*/
    }

}
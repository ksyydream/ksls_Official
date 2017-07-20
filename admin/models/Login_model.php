<?php

if (! defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * 权限模型
 *
 * 权限及用户的处理模型
 * 
 * @package		app
 * @subpackage	core
 * @category	model
 * @author		yaobin
 *        
 */
class Login_model extends MY_Model
{

    public function __construct ()
    {
        parent::__construct();
    }

    public function __destruct ()
    {
        parent::__destruct();
    }
    
    
    /**
     * 用户登录检查
     * 
     * @return boolean
     */
    public function check_login ()
    {
        $username = $this->input->post('username');
        $passwd = $this->input->post('password');

        $rs = $this->db->select()->from('admin')
            ->where('username', $username)
            ->where('pwd', sha1($passwd))
            ->where('status', 1)
            ->get()->row_array();

        if($rs){
            $data['user_info'] = $rs;
            $data['permission'] = explode('|',$rs['permission']);
            $this->session->set_userdata($data);
            return 1;
        }else{
            return -1;//用户名或密码错误
        }
    }
    
    

}
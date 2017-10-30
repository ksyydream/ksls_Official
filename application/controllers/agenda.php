<?php
/**
 * Created by PhpStorm.
 * User: bin.shen
 * Date: 5/19/16
 * Time: 15:59
 */

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Agenda extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();

        $this->load->model('agenda_model');

        $this->load->library('image_lib');
        $this->load->helper('directory');
    }

    function _remap($method,$params = array()) {
        if(!$this->session->userdata('login_user_id')) {
            redirect(site_url('/'));
        } else {
            if(in_array(2,$this->session->userdata('login_position_id_array'))
                || in_array(8,$this->session->userdata('login_position_id_array'))
                || in_array(9,$this->session->userdata('login_position_id_array'))
                || $this->session->userdata('login_permission_id') < 3){
                if($method == 'list_agenda'){
                    redirect(site_url('/agenda/list_agenda_other'));
                    exit();
                }
                if($method == 'add_agenda'){
                    redirect(site_url('/agenda/list_agenda_other'));
                    exit();
                }
            }else{
                if($this->session->userdata('login_permission_id') > 4 ){
                    if($method == 'list_agenda_other'){
                        redirect(site_url('/agenda/list_agenda'));
                        exit();
                    }
                }
            }

            return call_user_func_array(array($this, $method), $params);
        }
    }

    public function list_agenda($page=1) {
        $permission_id = $this->session->userdata('login_permission_id');
        $this->assign('permission_id', $permission_id);
        $position_id = $this->session->userdata('login_position_id_array');
        $this->assign('position_id', $position_id);
        $course_list = $this->agenda_model->get_course();
        $this->assign('course_list', $course_list);
        if($this->input->POST('status')) {
            $this->assign('status', $this->input->POST('status'));
        }
        if($this->input->POST('course')) {
            $this->assign('course', $this->input->POST('course'));
        }
        $data = $this->agenda_model->list_agenda($page, $this->session->userdata('login_user_id'));
        $this->assign('agenda_list', $data);

        $pager = $this->pagination->getPageLink('/agenda/list_agenda', $data['countPage'], $data['numPerPage']);
        $this->assign('pager', $pager);
        $this->display('list_agenda.html');
    }

    function list_agenda_other($page=1){
        $permission_id = $this->session->userdata('login_permission_id');
        $this->assign('permission_id', $permission_id);
        $position_id = $this->session->userdata('login_position_id_array');
        $this->assign('position_id', $position_id);
        if(in_array(2,$position_id)||in_array(8,$position_id)||in_array(9,$position_id)){
            //如果是 权证人员
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

            $company_id = NULL;

            $subsidiary_id = NULL;


        }else{
            ///这里不是权证人员 就安装自己的职级来判断
            if($permission_id == 1) {
                $company_list = $this->agenda_model->get_company_list();
                $this->assign('company_list', $company_list);
            }

            if($this->input->POST('company')) {
                $this->assign('company', $this->input->POST('company'));
                $subsidiary_list = $this->agenda_model->get_subsidiary_list($this->input->POST('company'), NULL);
            } else {
                $company_id = $this->session->userdata('login_company_id');
                if($permission_id < 3) {
                    $subsidiary_list = $this->agenda_model->get_subsidiary_list($company_id, NULL);
                } else if($permission_id < 5) {
                    $subsidiary_id = $this->session->userdata('login_subsidiary_id_array');
                    $subsidiary_list = $this->agenda_model->get_subsidiary_list($company_id, $subsidiary_id);
                }
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

            $company_id = NULL;
            if($permission_id > 1) {
                $company_id = $this->session->userdata('login_company_id');
            }
            $subsidiary_id = NULL;
            if($permission_id >= 3) {
                $subsidiary_id = $this->session->userdata('login_subsidiary_id_array');
            }

        }


        $course_list = $this->agenda_model->get_course();
        $this->assign('course_list',$course_list);
        if($this->input->POST('status')) {
            $this->assign('status', $this->input->POST('status'));
        }
        if($this->input->POST('course')) {
            $this->assign('course', $this->input->POST('course'));
        }
        if($this->input->POST('company')) {
            $this->assign('company', $this->input->POST('company'));
        }
        if($this->input->POST('subsidiary')) {
            $this->assign('subsidiary', $this->input->POST('subsidiary'));
        }
        $data = $this->agenda_model->list_agenda($page,null,$subsidiary_id,$company_id);
        $this->assign('agenda_list', $data);

        $pager = $this->pagination->getPageLink('/agenda/list_agenda_other', $data['countPage'], $data['numPerPage']);
        $this->assign('pager', $pager);
        $this->display('list_agenda_other.html');
    }

    public function add_agenda($id=NULL) {
        $permission_id = $this->session->userdata('login_permission_id');
        $this->assign('permission_id', $permission_id);
        $position_id = $this->session->userdata('login_position_id_array');
        $this->assign('position_id', $position_id);
        if(!empty($id)) {
            $agenda = $this->agenda_model->get_agenda($id);
            if($agenda->course !=1){
                redirect(site_url('/agenda/list_agenda'));
                exit();
            }
            if($agenda->user_id != $this->session->userdata('login_user_id')){
                redirect(site_url('/agenda/list_agenda'));
                exit();
            }
            $this->assign('agenda', $agenda);

            $agenda_image = $this->agenda_model->get_agenda_image($id);
            $agenda_images = array();
            foreach ($agenda_image as $img) {
                $agenda_images[$img->style][] = $img;
            }
            $this->assign('agenda_images', $agenda_images);
        }
        $folder_user = (string)$this->session->userdata('login_user_id');
        $this->assign('time', date('YmdHis'));
        $this->assign('f_user_id', $this->session->userdata('login_user_id'));
        $this->display('add_agenda.html');
    }

    public function view_agenda($id, $style=1) {
        $agenda = $this->agenda_model->get_agenda($id);
        $open_flag = 1;
        if($agenda->flag != 1){
            $open_flag = 2;
        }
        if($agenda->user_id == $this->session->userdata('login_user_id')){
            $open_flag = 2;
        }
        if($agenda->dbyh_id == $this->session->userdata('login_user_id') && in_array(9,$this->session->userdata('login_position_id_array'))){
            $open_flag = 2;
        }
        if($agenda->dbgh_id == $this->session->userdata('login_user_id') && in_array(8,$this->session->userdata('login_position_id_array'))){
            $open_flag = 2;
        }
        if(in_array(2,$this->session->userdata('login_position_id_array'))){
            $open_flag = 2;
        }
        if($this->agenda_model->issubordinates_age($this->session->userdata('login_user_id'),$agenda->user_id)==1){
            $open_flag = 2;
        }
        if($open_flag == 1){
            redirect(site_url('/agenda/list_agenda'));
            exit();
        }
        $this->assign('agenda', $agenda);

        $agenda_course = $this->agenda_model->get_agenda_course($id);
        $this->assign('agenda_course', $agenda_course);

        $agenda_image = $this->agenda_model->get_agenda_image($id);
        $agenda_images = array();
        foreach ($agenda_image as $img) {
            $agenda_images[$img->style][] = $img;
        }
        $this->assign('agenda_images', $agenda_images);
        $dbgh_list = $this->agenda_model->get_dbgh_list();
        $this->assign('dbgh_list', $dbgh_list);
        $dbyh_list = $this->agenda_model->get_dbyh_list();
        $this->assign('dbyh_list', $dbyh_list);
        $course_list = $this->agenda_model->get_course_list();
        $this->assign('course_list', $course_list);

        $permission_id = $this->session->userdata('login_permission_id');
        $this->assign('permission_id', $permission_id);

        $position_id = $this->session->userdata('login_position_id_array');
        $this->assign('position_id', $position_id);

        $this->assign('style', $style);

        $this->display('view_agenda.html');
    }

    public function save_agenda() {

        $this->agenda_model->save_agenda();

        redirect(site_url('agenda/list_agenda'));
    }

    public function confirm_agenda() {
        $agenda = $this->agenda_model->get_agenda($this->input->post('id'));
        $open_flag = 1;
        if($agenda->dbyh_id == $this->session->userdata('login_user_id') && in_array(9,$this->session->userdata('login_position_id_array'))){
            $open_flag = 2;
        }
        if($open_flag == 1){
            redirect(site_url('/agenda/list_agenda_other'));
            exit();
        }
        $this->agenda_model->confirm_agenda();

        redirect(site_url('agenda/list_agenda_other'));
    }

    ///////////////////////////////////////////////////////////////////
    public function save_pics($time, $style){
        if (is_readable('./././uploadfiles/agenda') == false) {
            mkdir('./././uploadfiles/agenda');
        }
        if (is_readable('./././uploadfiles/agenda/'.$time) == false) {
            mkdir('./././uploadfiles/agenda/'.$time);
        }

        if (is_readable('./././uploadfiles/agenda/'.$time.'/'.$style) == false) {
            mkdir('./././uploadfiles/agenda/'.$time.'/'.$style);
        }

        $path = './././uploadfiles/agenda/'.$time.'/'.$style;

        //设置缩小图片属性
        $config_small['image_library'] = 'gd2';
        $config_small['create_thumb'] = TRUE;
        $config_small['quality'] = 80;
        $config_small['maintain_ratio'] = TRUE; //保持图片比例
        $config_small['new_image'] = $path;
        $config_small['width'] = 300;
        $config_small['height'] = 190;

        //设置原图限制
        $config['upload_path'] = $path;
        $config['allowed_types'] = 'gif|jpg|png|jpeg';
        $config['max_size'] = '10000';
        $config['encrypt_name'] = true;
        $this->load->library('upload', $config);

        if($this->upload->do_upload()){
            $data = $this->upload->data();//返回上传文件的所有相关信息的数组
            $config_small['source_image'] = $data['full_path']; //文件路径带文件名
            $this->image_lib->initialize($config_small);
            $this->image_lib->resize();

            echo 1;
        }else{
            echo -1;
        }
        exit;
    }

    //ajax获取图片信息
    public function get_pics($time, $style){
        $path = './././uploadfiles/agenda/'.$time.'/'.$style;
        $map = directory_map($path);
        $data = array();
        //整理图片名字，取缩略图片
        foreach($map as $v){
            if(substr(substr($v,0,strrpos($v,'.')),-5) == 'thumb'){
                $data['img'][] = $v;
            }
        }
        $data['time'] = $time;
        $data['style'] = $style;
        echo json_encode($data);
    }

    //ajax删除图片
    public function del_pic($folder,$style,$pic,$id=null){
        $data = $this->agenda_model->del_pic($folder,$style,$pic,$id);
        echo json_encode($data);
    }

    public function get_subsidiary_user_list($subsidiary_id) {

        $subsidiary_user_list = $this->agenda_model->get_subsidiary_user_list($subsidiary_id);
        echo json_encode($subsidiary_user_list);
        die;
        die;
    }

    public function get_subsidiary_user_list_7($subsidiary_id) {

        $subsidiary_user_list = $this->agenda_model->get_subsidiary_user_list_7($subsidiary_id);
        echo json_encode($subsidiary_user_list);
        die;
        die;
    }

    public function check_sum(){
        $data = $this->agenda_model->age_check_sum();
        echo json_encode($data);
    }

    public function change_dbuser_agenda(){
        $this->agenda_model->change_dbuser_agenda();

        redirect(site_url('agenda/list_agenda_other'));
    }

    public function zysm(){
        $this->display('minhtml/zysm.html');
    }

}
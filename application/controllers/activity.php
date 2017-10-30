<?php
/**
 * Created by PhpStorm.
 * User: bin.shen
 * Date: 5/7/16
 * Time: 13:53
 */

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Activity extends MY_Controller {

    public function __construct()
    {
        parent::__construct();

        //////////////
        //for test only
//        $user_info['login_user_id'] = 5;
//        $user_info['login_username'] = 'test';
//        $user_info['login_rel_name'] = 'Test';
//        $user_info['login_role_id'] = 1;
//        $user_info['login_company_id'] = 1;
//        $user_info['login_subsidiary_id'] = 2;
//        $this->session->set_userdata($user_info);
        //////////////

        $this->load->model('activity_model');
    }

    function _remap($method,$params = array()) {
        if(!$this->session->userdata('login_user_id') || in_array(1,$this->session->userdata('login_position_id_array'))) {
            redirect(site_url('/'));
        } else {
            if($this->session->userdata('login_permission_id') > 4){
                if($method == 'list_review'){
                    redirect(site_url('/activity/list_activity'));
                    exit();
                }
                if($method == 'review_activity'){
                    redirect(site_url('/activity/list_activity'));
                    exit();
                }
                if($method == 'list_noplan'){
                    redirect(site_url('/activity/list_activity'));
                    exit();
                }
            }else{
                if($method == 'list_activity'){
                    redirect(site_url('/activity/list_review/1/1'));
                    exit();
                }
            }
            return call_user_func_array(array($this, $method), $params);
        }
    }

    public function list_activity($page=1) {
        $permission_id = $this->session->userdata('login_permission_id');
        $this->assign('permission_id', $permission_id);

        if($this->input->POST('start_date')) {
            $this->assign('start_date', $this->input->POST('start_date'));
        }
        if($this->input->POST('end_date')) {
            $this->assign('end_date', $this->input->POST('end_date'));
        }

        $data = $this->activity_model->list_activity($page, array(1,2,3), $this->session->userdata('login_user_id'));
        $this->assign('activity_list', $data);

        $this->assign('tomorrow', date('Ymd', strtotime("+1 day")));
        $this->assign('today', date('Ymd'));
        $pager = $this->pagination->getPageLink('/activity/list_activity', $data['countPage'], $data['numPerPage']);
        $this->assign('pager', $pager);

        $this->display('list_activity.html');
    }

    public function list_review($page=1,$flag=null) {


        $permission_id = $this->session->userdata('login_permission_id');
        $this->assign('permission_id', $permission_id);
       // $subsidiary_id_array = $this->session->userdata('login_subsidiary_id_array');
        if($permission_id == 1) {
            $company_list = $this->activity_model->get_company_list();
            $this->assign('company_list', $company_list);
        }

        if($this->input->POST('company')) {
            $this->assign('company', $this->input->POST('company'));
            $subsidiary_list = $this->activity_model->get_subsidiary_list($this->input->POST('company'), NULL);
        } else {
            $company_id = $this->session->userdata('login_company_id');
            if($permission_id < 3) {
                $subsidiary_list = $this->activity_model->get_subsidiary_list($company_id, NULL);
            } else if($permission_id < 5) {
                $subsidiary_id = $this->session->userdata('login_subsidiary_id_array');
                $subsidiary_list = $this->activity_model->get_subsidiary_list($company_id, $subsidiary_id);
            }
        }
        $this->assign('subsidiary_list', $subsidiary_list);

        if($this->input->POST('subsidiary')) {
            $this->assign('subsidiary', $this->input->POST('subsidiary'));
            $user_list = $this->activity_model->get_subsidiary_user_list_7($this->input->POST('subsidiary'));
            $this->assign('user_list', $user_list);
        }elseif(!$this->input->post('subsidiary') && $permission_id < 5 && $permission_id > 3){
            $subsidiary_id_array = $this->session->userdata('login_subsidiary_id_array');
            $this->assign('subsidiary', $subsidiary_id_array[0]);
            $user_list = $this->activity_model->get_subsidiary_user_list_7($subsidiary_id_array[0]);
            $this->assign('user_list', $user_list);
        }
        if($this->input->POST('user')) {
            $this->assign('user', $this->input->POST('user'));
        }
        if($this->input->POST('start_date')) {
            $this->assign('start_date', $this->input->POST('start_date'));
        }
        if($this->input->POST('end_date')) {
            $this->assign('end_date', $this->input->POST('end_date'));
        }
        $this->assign('yesterday', date('Y-m-d', strtotime("-1 day")));
        $this->assign('flag', $flag);
        $company_id = NULL;
        if($permission_id > 1) { //如果不是管理员 就只能查看自己公司下的人员
            $company_id = $this->session->userdata('login_company_id');
        }
        $subsidiary_id = NULL;
        if($permission_id > 2) {//如果不是总经理 就只能查看自己门店的人员
            $subsidiary_id = $this->session->userdata('login_subsidiary_id_array');
        }
        $data = $this->activity_model->list_activity($page, array(1,2,3), NULL, $subsidiary_id, $company_id,$flag);
        $this->assign('activity_list', $data);

        $pager = $this->pagination->getPageLink('/activity/list_review', $data['countPage'], $data['numPerPage']);
        $this->assign('pager', $pager);

        $this->display('list_review.html');

    }

    public function add_activity() {
        $permission_id = $this->session->userdata('login_permission_id');
        $this->assign('permission_id', $permission_id);
        $activity_type_list = $this->activity_model->get_activity_type_list();
        $this->assign('activity_type_list', json_encode($activity_type_list));

        $this->assign('tomorrow', date("Y-m-d",strtotime("+1 day")));

        $this->display('add_activity.html');
    }

    public function edit_activity($id) {
        $activity = $this->activity_model->get_activity_by_id($id);
        if($activity['status'] != 1 || $this->session->userdata('login_user_id') != $activity['user_id']){
            redirect(site_url('/activity/list_activity'));
            exit();
        }
        $permission_id = $this->session->userdata('login_permission_id');
        $this->assign('permission_id', $permission_id);
        $activity_type_list = $this->activity_model->get_activity_type_list();
        $this->assign('activity_type_list', json_encode($activity_type_list));



        $activity['a1t'] = $activity['a1n'] * $activity['a1s'];
        $activity['a2t'] = $activity['a2n'] * $activity['a2s'];
        $activity['a3t'] = $activity['a3n'] * $activity['a3s'];
        $activity['a4t'] = $activity['a4n'] * $activity['a4s'];
        $activity['a5t'] = $activity['a5n'] * $activity['a5s'];
        $activity['att'] = $activity['a1t'] + $activity['a2t'] + $activity['a3t'] + $activity['a4t'] + $activity['a5t'];
        $this->assign('activity', $activity);

        $this->display('add_activity.html');
    }

    public function inspect_activity($id) {
        $activity = $this->activity_model->get_activity_by_id($id);
        if(!in_array($activity['status'],array(1,2)) || $this->session->userdata('login_user_id') != $activity['user_id']){
            redirect(site_url('/activity/list_activity'));
            exit();
        }
        $permission_id = $this->session->userdata('login_permission_id');
        $this->assign('permission_id', $permission_id);
        $activity_type_list = $this->activity_model->get_activity_type_list();
        $this->assign('activity_type_list', json_encode($activity_type_list));


        $status = $activity['status'];
        if($status == 1) {
            $activity['b1'] = $activity['a1'];
            $activity['b1s'] = $activity['a1s'];
            $activity['b1n'] = $activity['a1n'];
            $activity['b1m'] = '';
            $activity['b2'] = $activity['a2'];
            $activity['b2s'] = $activity['a2s'];
            $activity['b2n'] = $activity['a2n'];
            $activity['b2m'] = '';
            $activity['b3'] = $activity['a3'];
            $activity['b3s'] = $activity['a3s'];
            $activity['b3n'] = $activity['a3n'];
            $activity['b3m'] = '';
            $activity['b4'] = $activity['a4'];
            $activity['b4s'] = $activity['a4s'];
            $activity['b4n'] = $activity['a4n'];
            $activity['b4m'] = '';
            $activity['b5'] = $activity['a5'];
            $activity['b5s'] = $activity['a5s'];
            $activity['b5n'] = $activity['a5n'];
            $activity['b5m'] = '';

            $activity['t6u'] = $activity['t1u'];
            $activity['t7u'] = $activity['t2u'];
            $activity['t8u'] = $activity['t3u'];
            $activity['t9u'] = $activity['t4u'];
            $activity['t10u'] = $activity['t5u'];
        }

        $activity['a1t'] = $activity['a1n'] * $activity['a1s'];
        $activity['a2t'] = $activity['a2n'] * $activity['a2s'];
        $activity['a3t'] = $activity['a3n'] * $activity['a3s'];
        $activity['a4t'] = $activity['a4n'] * $activity['a4s'];
        $activity['a5t'] = $activity['a5n'] * $activity['a5s'];
        $activity['att'] = $activity['a1t'] + $activity['a2t'] + $activity['a3t'] + $activity['a4t'] + $activity['a5t'];

        $activity['b1t'] = $activity['b1n'] * $activity['b1s'];
        $activity['b2t'] = $activity['b2n'] * $activity['b2s'];
        $activity['b3t'] = $activity['b3n'] * $activity['b3s'];
        $activity['b4t'] = $activity['b4n'] * $activity['b4s'];
        $activity['b5t'] = $activity['b5n'] * $activity['b5s'];
        $activity['btt'] = $activity['b1t'] + $activity['b2t'] + $activity['b3t'] + $activity['b4t'] + $activity['b5t'];
        $this->assign('activity', $activity);

        $this->display('inspect_activity.html');
    }

    public function review_activity($id) {
        $activity = $this->activity_model->get_activity_by_id($id);
        if($this->activity_model->issubordinates($this->session->userdata('login_user_id'),$activity['user_id']) !=1){
            redirect(site_url('/activity/list_review'));
            exit();
        }
        $activity_type_list = $this->activity_model->get_activity_type_list();
        $this->assign('activity_type_list', json_encode($activity_type_list));
        $permission_id = $this->session->userdata('login_permission_id');
        $this->assign('permission_id', $permission_id);

        $status = $activity['status'];
        if($status == 2) {
            $activity['c1'] = $activity['b1'];
            $activity['c1s'] = $activity['b1s'];
            $activity['c1n'] = $activity['b1n'];
            $activity['c1m'] = '';
            $activity['c2'] = $activity['b2'];
            $activity['c2s'] = $activity['b2s'];
            $activity['c2n'] = $activity['b2n'];
            $activity['c2m'] = '';
            $activity['c3'] = $activity['b3'];
            $activity['c3s'] = $activity['b3s'];
            $activity['c3n'] = $activity['b3n'];
            $activity['c3m'] = '';
            $activity['c4'] = $activity['b4'];
            $activity['c4s'] = $activity['b4s'];
            $activity['c4n'] = $activity['b4n'];
            $activity['c4m'] = '';
            $activity['c5'] = $activity['b5'];
            $activity['c5s'] = $activity['b5s'];
            $activity['c5n'] = $activity['b5n'];
            $activity['c5m'] = '';

            $activity['t11u'] = $activity['t6u'];
            $activity['t12u'] = $activity['t7u'];
            $activity['t13u'] = $activity['t8u'];
            $activity['t14u'] = $activity['t9u'];
            $activity['t15u'] = $activity['t10u'];
        }

        $activity['a1t'] = $activity['a1n'] * $activity['a1s'];
        $activity['a2t'] = $activity['a2n'] * $activity['a2s'];
        $activity['a3t'] = $activity['a3n'] * $activity['a3s'];
        $activity['a4t'] = $activity['a4n'] * $activity['a4s'];
        $activity['a5t'] = $activity['a5n'] * $activity['a5s'];
        $activity['att'] = $activity['a1t'] + $activity['a2t'] + $activity['a3t'] + $activity['a4t'] + $activity['a5t'];

        $activity['b1t'] = $activity['b1n'] * $activity['b1s'];
        $activity['b2t'] = $activity['b2n'] * $activity['b2s'];
        $activity['b3t'] = $activity['b3n'] * $activity['b3s'];
        $activity['b4t'] = $activity['b4n'] * $activity['b4s'];
        $activity['b5t'] = $activity['b5n'] * $activity['b5s'];
        $activity['btt'] = $activity['b1t'] + $activity['b2t'] + $activity['b3t'] + $activity['b4t'] + $activity['b5t'];

        $activity['c1t'] = $activity['c1n'] * $activity['c1s'];
        $activity['c2t'] = $activity['c2n'] * $activity['c2s'];
        $activity['c3t'] = $activity['c3n'] * $activity['c3s'];
        $activity['c4t'] = $activity['c4n'] * $activity['c4s'];
        $activity['c5t'] = $activity['c5n'] * $activity['c5s'];

        if($status == 2) {
            $activity['ctt'] = $activity['c1t'] + $activity['c2t'] + $activity['c3t'] + $activity['c4t'] + $activity['c5t'] + $activity['op'] * $activity['float'];
        } else {
            $activity['ctt'] = $activity['total'];
        }
        $this->assign('activity', $activity);

        $this->display('review_activity.html');
    }

    public function check_activity() {
        $activity_list = $this->activity_model->check_activity();
        echo empty($activity_list) ? true : false;
        die;
    }

    public function save_activity() {
        $this->activity_model->add_activity();

        redirect(site_url('activity/list_activity'));
    }

    public function assess_activity() {
        $this->activity_model->assess_activity();

        redirect(site_url('activity/list_activity'));
    }

    public function confirm_activity() {
        $this->activity_model->review_activity();

        redirect(site_url('activity/list_review/1/1'));
    }

    public function list_ranking($op = 0) {

        $year = date('Y');
        $this->assign('year', $year);
        $this->assign('month', date('m'));
        $year_list = array();
        for($i=0; $i<5; $i++) {
            $year_list[] = $year-$i;
        }
        $this->assign('year_list', $year_list);

        $permission_id = $this->session->userdata('login_permission_id');
        $this->assign('permission_id', $permission_id);
        if($permission_id == 1) {
            $company_list = $this->activity_model->get_company_list();
            $this->assign('company_list', $company_list);
        } else {
            $company_id = $this->session->userdata('login_company_id');
            $subsidiary_id = NULL;
            if($permission_id > 2) {
                $subsidiary_id = $this->session->userdata('login_subsidiary_id_array');
                $this->assign('subsidiary', $subsidiary_id);
            } else {
                $this->assign('company', $company_id);
            }
            $subsidiary_list = $this->activity_model->get_subsidiary_list($company_id, $subsidiary_id);
            $this->assign('subsidiary_list', $subsidiary_list);
        }

        $this->display('list_ranking.html');
    }

    public function show_ranking() {

        $op = $this->input->post('op');
        $company_id = $this->input->post('company_id');
        $subsidiary_id = $this->input->post('subsidiary_id');
        $year = $this->input->post('year');
        $month = $this->input->post('month');
        $permission_id = $this->session->userdata('login_permission_id');
        if($permission_id > 1) {
            $company_id = $this->session->userdata('login_company_id');
        }
        if($permission_id == 3 && empty($subsidiary_id)) {
            $subsidiary_id = $this->session->userdata('login_subsidiary_id_array');
        }else{
            if($permission_id > 3){
                $subsidiary_id = $this->session->userdata('login_subsidiary_id_array');
            }
        }

        if($op < 1) {
            $rank_list = $this->activity_model->get_total_top_list($company_id, $subsidiary_id, $year, $month);
        } else {
            $rank_list = $this->activity_model->get_top_list_by_op($op, $company_id, $subsidiary_id, $year, $month);
        }

        $rank = array();
        $login_user_id = $this->session->userdata('login_user_id');
       /* $rank_list[0]->u_pic='user_photo.gif';
        die(var_dump($rank_list[0]->u_pic));*/
        if(!empty($rank_list)) {
            foreach ($rank_list as $idx => $user){
                if(!@file_get_contents('./uploadfiles/profile/'.$user->u_pic)){
                    $rank_list[$idx]->u_pic='user_photo.gif';
                }
                if($user->u_id == $login_user_id) {
                    $rank['num'] = $idx+1;
                    $rank['u_pic'] = $user->u_pic;
                    //die(var_dump(file_get_contents('./uploadfiles/profile/'.$user->u_pic)));
                    $rank['u_pic'] = $user->u_pic;
                    $rank['u_name'] = $user->u_name;
                    $rank['c_name'] = $user->c_name;
                    $rank['s_name'] = $user->s_name;
                    $rank['total'] = $user->total;
                    break;
                }
            }
        }

        $result = array();
        $result['list'] = array_slice($rank_list, 0, 20);
        $result['rank'] = $rank;
        echo json_encode($result);
        die;
    }

    public function test() {
        $data = $this->activity_model->get_top_list_by_op();
        var_dump($data);
        die;
    }

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function get_subsidiary_list($company_id) {

        $subsidiary_list = $this->activity_model->get_subsidiary_list($company_id);
        echo json_encode($subsidiary_list);
        die;
    }

    public function get_subsidiary_user_list($subsidiary_id) {

        $subsidiary_user_list = $this->activity_model->get_subsidiary_user_list($subsidiary_id);
        echo json_encode($subsidiary_user_list);
        die;
        die;
    }

    public function get_subsidiary_user_list_7($subsidiary_id) {

        $subsidiary_user_list = $this->activity_model->get_subsidiary_user_list_7($subsidiary_id);
        echo json_encode($subsidiary_user_list);
        die;
        die;
    }

    public function list_noplan($page=1,$flag=null){
        $permission_id = $this->session->userdata('login_permission_id');
        $this->assign('permission_id', $permission_id);
        $this->assign('flag', $flag);
        if($permission_id == 1) {
            $company_list = $this->activity_model->get_company_list();
            $this->assign('company_list', $company_list);
        }

        if($this->input->POST('company')) {
            $this->assign('company', $this->input->POST('company'));
            $subsidiary_list = $this->activity_model->get_subsidiary_list($this->input->POST('company'), NULL);
        } else {
            $company_id = $this->session->userdata('login_company_id');
            if($permission_id < 3) {
                $subsidiary_list = $this->activity_model->get_subsidiary_list($company_id, NULL);
            } else if($permission_id < 5) {
                $subsidiary_id = $this->session->userdata('login_subsidiary_id_array');
                $subsidiary_list = $this->activity_model->get_subsidiary_list($company_id, $subsidiary_id);
            }
        }
        $this->assign('subsidiary_list', $subsidiary_list);

        if($this->input->POST('subsidiary')) {
            $this->assign('subsidiary', $this->input->POST('subsidiary'));
            $user_list = $this->activity_model->get_subsidiary_user_list_7($this->input->POST('subsidiary'));
            $this->assign('user_list', $user_list);
        }elseif(!$this->input->post('subsidiary') && $permission_id < 5 && $permission_id > 3){
            $subsidiary_id_array = $this->session->userdata('login_subsidiary_id_array');
            $this->assign('subsidiary', $subsidiary_id_array[0]);
            $user_list = $this->activity_model->get_subsidiary_user_list_7($subsidiary_id_array[0]);
            $this->assign('user_list', $user_list);
        }
        if($this->input->POST('user')) {
            $this->assign('user', $this->input->POST('user'));
        }
        if($this->input->POST('date')) {
            $this->assign('date', $this->input->POST('date'));
        }else{
            if($flag==1){
                $this->assign('date', date('Y-m-d', strtotime("-1 day")));
            }
        }

        $this->assign('yesterday', date('Y-m-d', strtotime("-1 day")));

        $company_id = NULL;
        if($permission_id > 1) {
            $company_id = $this->session->userdata('login_company_id');
        }
        $subsidiary_id = NULL;
        if($permission_id >= 3) {
            $subsidiary_id = $this->session->userdata('login_subsidiary_id_array');
        }
        $data = $this->activity_model->list_onplan($page, $subsidiary_id, $company_id,$flag);
        $this->assign('noplan_list', $data);

        $pager = $this->pagination->getPageLink('/activity/list_noplan', $data['countPage'], $data['numPerPage']);
        $this->assign('pager', $pager);

        $this->display('list_noplan.html');
    }
}
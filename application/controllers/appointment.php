<?php
/**
 * Created by PhpStorm.
 * User: bin.shen
 * Date: 5/26/16
 * Time: 12:57
 */

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Appointment extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();

        $this->load->model('appointment_model');
    }

    function _remap($method, $params = array())
    {
        if(!$this->session->userdata('login_user_id')) {
            redirect(site_url('/'));
        } else {
            return call_user_func_array(array($this, $method), $params);
        }
    }

    function book_room() {

        $position_id = $this->session->userdata('login_position_id_array');

        $week = array("日", "一", "二", "三", "四", "五", "六");
        $dates = array();
        for($i=0; $i<7; $i++) {
            $date = date('Y-m-d', strtotime("+$i day"));
            $dates[] = $date;
            if($i == 0) {
                $this->assign('date_' . $i, '今天 (' . date("m/d", strtotime($date)) . ')');
            } else {
                $this->assign('date_' . $i, '周' . $week[date("w", strtotime($date))] . ' (' . date("m/d", strtotime($date)) . ')');
            }
            $this->assign('week_' . $i, date("w", strtotime($date)) == 0 || date("w", strtotime($date)) == 6);
        }

        $time_frame_list = $this->appointment_model->get_time_frame_list();
        $this->assign('time_frame_list', $time_frame_list);
        $this->assign('dates', $dates);


        $room_data = $this->appointment_model->book_room(date('Y-m-d'), $date);
        $data = array();
        foreach ($room_data as $r1) {
            if(empty($data[$r1->time_frame_id])) {
                $data[$r1->time_frame_id] = array();
            }
            if(empty($data[$r1->time_frame_id][$r1->date])) {
                $data[$r1->time_frame_id][$r1->date] = array();
            }
            $data[$r1->time_frame_id][$r1->date][$r1->room_id] = 1;
        }

        $result = array();
        if(in_array(3,$position_id)) {
            foreach ($time_frame_list as $tf0) {
                $tf0_id = $tf0->id;
                $result[$tf0_id] = array();
                foreach ($dates as $d0) {
                    $result[$tf0_id][$d0] = $tf0->open != 1 ? -1 : (!empty($data[$tf0_id]) && !empty($data[$tf0_id][$d0]) ? count($data[$tf0_id][$d0]) : 0);
                }
            }
            $this->assign('result', $result);
            $this->display('review_room.html');
        } else {
            $room_list = $this->appointment_model->get_room_list();
            foreach ($time_frame_list as $tf1) {
                $tf1_id = $tf1->id;
                $result[$tf1_id] = array();
                foreach ($dates as $d1) {
                    $result[$tf1_id][$d1] = !empty($data[$tf1_id]) && !empty($data[$tf1_id][$d1]) && count($data[$tf1_id][$d1]) == count($room_list) || $tf1->open != 1 ? true : false;
                }
            }

            $my_room_data = $this->appointment_model->book_room(date('Y-m-d'), $date, $this->session->userdata('login_user_id'));
            $my_data = array();
            foreach ($my_room_data as $r2) {
                if(empty($data[$r2->time_frame_id])) {
                    $my_data[$r2->time_frame_id] = array();
                }
                $my_data[$r2->time_frame_id][$r2->date] = 1;
            }

            $my_result = array();
            foreach ($time_frame_list as $tf2) {
                $tf2_id = $tf2->id;
                $my_result[$tf2_id] = array();
                foreach ($dates as $d2) {
                    $my_result[$tf2_id][$d2] = !empty($my_data[$tf2_id]) && !empty($my_data[$tf2_id][$d2]) ? true : false;
                }
            }

            $this->assign('result', $result);
            $this->assign('my_result', $my_result);

            $this->display('booking_room.html');
        }
    }

    function get_appointment_info($date, $tf_id) {
        $appointments = $this->appointment_model->get_appointment_info($date, $tf_id);
        echo json_encode($appointments);
        die;
    }

    function get_appointment_detail($date, $tf_id) {

        $appointments = $this->appointment_model->get_appointment_detail($date, $tf_id);
        echo json_encode($appointments);
        die;
    }

    function popup_room() {

        $tf_id = $_POST['x'];
        $date  = $_POST['y'];

        if($date == date("Y-m-d")) {
            $time_frame = $this->appointment_model->get_time_frame($tf_id);
            $tf_name = $time_frame->name;
            $times = explode('-', $tf_name);
            $hours = explode(':', $times[0]);
            $hour = $hours[0];
            if($hour - 2 <= date("H")) {
                echo -1;
                die;
            }
        }

        $rooms = $this->appointment_model->check_room($date, $this->session->userdata('login_user_id'));
        if(!empty($rooms) && count($rooms) >= 2) {
            echo -2;
            die;
        }
        $res_sum = $this->appointment_model->check_sum($this->session->userdata('login_company_id'));
        if($res_sum == -1){
            echo -3;
            die;
        }
        $appointments = $this->appointment_model->get_appointment_info($date, $tf_id);
        $this->assign('appointments', $appointments);

        $this->assign('time_frame_id', $tf_id);
        $this->assign('date', $date);
        $this->assign('user_id', $this->session->userdata('login_user_id'));

        $this->display('popup_room.html');
    }

    function save_appointment() {

        $this->appointment_model->save_appointment();

        redirect(site_url('/appointment/book_room'));
    }

    function unbook_room() {

        $this->appointment_model->unbook_room(
            $this->input->post('date'),
            $this->input->post('time_frame_id'),
            $this->session->userdata('login_user_id')
        );

        redirect(site_url('/appointment/book_room'));
    }

    function show_room() {

        $tf_id = $_POST['x'];
        $date  = $_POST['y'];

        $appointments = $this->appointment_model->get_appointment_detail($date, $tf_id);
        $this->assign('appointments', $appointments);

        $this->display('show_room.html');
    }
}
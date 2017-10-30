<?php
/**
 * Created by PhpStorm.
 * User: bin.shen
 * Date: 5/31/16
 * Time: 16:23
 */

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Video extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('video_model');
    }

    function _remap($method,$params = array()) {
        if(!$this->session->userdata('login_user_id') || in_array(1,$this->session->userdata('login_position_id_array'))) {
            redirect(site_url('/'));
        } else {
            return call_user_func_array(array($this, $method), $params);
        }
    }

    public function list_video($page=1, $type=NULL) {

        $video_type_list = $this->video_model->get_video_type_list();
        $this->assign('video_type_list', $video_type_list);

        $top_video_list = $this->video_model->get_top_video_list();
        $this->assign('top_video_list', $top_video_list);

        if($this->input->post('type')) {
            $type = $this->input->post('type');
        }

        $perPage = 10;
        if(empty($type)) {
            $perPage = 5;
        }

        $data = $this->video_model->get_video_list($page, $perPage, $type);
        $pager = $this->pagination->getPageLink('/video/list_video', $data['countPage'], $data['numPerPage']);
        $this->assign('pager', $pager);

        $this->assign('video_list', $data);

        $this->assign('video_type_id', $type);

        $this->display('online_class.html');
    }

    public function view_video($id) {

        $video = $this->video_model->get_video($id);
        $this->assign('video', $video);

        $related_video_list = $this->video_model->get_related_video_list($video['type_id']);
        $this->assign('related_video_list', $related_video_list);

        if(!empty($video)) {
            $this->video_model->increase_data($video['id'], 'played');
        }

        $this->display('video_play.html');
    }

    public function like_video($id) {

        echo $this->video_model->increase_data($id, 'likes', 'video_likes');
        die;
    }

    public function unlike_video($id) {
        echo $this->video_model->decrease_data($id, 'likes', 'video_likes');
        die;
    }

    public function collect_video($id) {
        echo $this->video_model->increase_data($id, 'collects', 'video_collect');
        die;
    }

    public function uncollect_video($id) {
        echo $this->video_model->decrease_data($id, 'collects', 'video_collect');
        die;
    }
}
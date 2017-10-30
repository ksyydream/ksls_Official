<?php
/**
 * Created by PhpStorm.
 * User: bin.shen
 * Date: 6/2/16
 * Time: 21:22
 */

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class News extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('news_model');
        $this->load->library('image_lib');
        $this->load->helper('directory');
    }

    function _remap($method,$params = array()) {
        if($method == 'view_news'){
            return call_user_func_array(array($this, $method), $params);
        }else{
            if(!$this->session->userdata('login_user_id') || in_array(1,$this->session->userdata('login_position_id_array'))) {
                redirect(site_url('/'));
            } else {
                if(in_array(6,$this->session->userdata('login_position_id_array'))){
                    return call_user_func_array(array($this, $method), $params);
                }else{
                    redirect(site_url('/'));
                }
            }
        }

    }

    public function publish_news($id=null){
        $news = array();
        if($id){
            $news = $this->news_model->view_news($id);
        }
        $this->assign('news', $news);
        $this->display("publish_news.html");
    }
    
    public function view_news($id) {
        $this->news_model->increase_views($id);
        $news = $this->news_model->view_news($id);
        $this->assign('news', $news);
        $this->display("popup_news.html");
    }

    public function save_news() {
        if (is_readable('./././uploadfiles/news') == false) {
            mkdir('./././uploadfiles/news');
        }
        $config['upload_path'] = './././uploadfiles/news';
        $config['allowed_types'] = 'gif|jpg|png|jpeg';
        $config['max_size'] = '1000';
        $config['encrypt_name'] = true;
        if($this->input->post('news_id')){
            if(!$_FILES["userfile"]['tmp_name']){
                $this->news_model->update_user();
            }else{
                $this->load->library('upload', $config);
                if($this->upload->do_upload()){
                    $img_info = $this->upload->data();
                    $this->news_model->update_user($img_info['file_name']);
                }
            }
        }else{
            $this->load->library('upload', $config);
            if($this->upload->do_upload()){
                $img_info = $this->upload->data();
                $this->news_model->save_user($img_info['file_name']);
            }
        }
        redirect(site_url('/news/news_list'));
    }

    public function upload_news_pic(){
        if (is_readable('./././uploadfiles/news_pic') == false) {
            mkdir('./././uploadfiles/news_pic');
        }
        $path = './././uploadfiles/news_pic/';
        $path_out = '/uploadfiles/news_pic/';
        $msg = '';

        //设置原图限制
        $config['upload_path'] = $path;
        $config['allowed_types'] = 'gif|jpg|png|jpeg';
        $config['max_size'] = '1000';
        $config['encrypt_name'] = true;
        $this->load->library('upload', $config);

        if($this->upload->do_upload('filedata')){
            $data = $this->upload->data();
            $targetPath = $path_out.$data['file_name'];
            $msg="{'url':'".$targetPath."','localname':'','id':'1'}";
            $err = '';
        }else{
            $err = $this->upload->display_errors();
        }
        echo "{'err':'".$err."','msg':".$msg."}";
    }

    public function news_list($page=1){
        $data = $this->news_model->list_news($page,10);
        $this->assign('news_list', $data);
        $pager = $this->pagination->getPageLink('/news/news_list', $data['countPage'], $data['numPerPage']);
        $this->assign('pager', $pager);
        $this->display("news_list.html");
    }

    public function delete_news($id){
        $this->news_model->delete_news($id);
        redirect(site_url('/news/news_list'));
    }
}


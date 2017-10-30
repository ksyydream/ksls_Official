<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 后台画面控制器
 *
 * @package		app
 * @subpackage	core
 * @category	controller
 * @author		yaobin<645894453@qq.com>
 *1
 */
class Manage extends MY_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('manage_model');
		$this->load->library('image_lib');
	}

	function _remap($method,$params = array())
	{
		if(!$this->session->userdata('user_id'))
		{
			if($this->input->is_ajax_request()){
				header('Content-type: text/json');
				echo '{
                        "statusCode":"301",
                        "message":"\u4f1a\u8bdd\u8d85\u65f6\uff0c\u8bf7\u91cd\u65b0\u767b\u5f55\u3002"
                    }';
			}else{
				redirect(site_url('manage_login/login'));
			}

		}else{
			if($this->session->userdata('permission_id')==1){
				return call_user_func_array(array($this, $method), $params);
			}else{
				if($method == 'index' ||
					$method == 'list_company' ||
					$method == 'edit_company' ||
					$method == 'list_subsidiary' ||
					$method == 'add_subsidiary' ||
					$method == 'save_subsidiary' ||
					$method == 'edit_subsidiary' ||
					$method == 'delete_subsidiary' ||
					$method == 'list_user' ||
					$method == 'add_user' ||
					$method == 'save_user' ||
					$method == 'password_reset' ||
					$method == 'edit_user' ||
					$method == 'delete_user' ||
					$method == 'get_subsidiary_list' ||
					$method == 'get_subsidiary_list_2'
				){
					return call_user_func_array(array($this, $method), $params);
				}else{
					if($method == 'list_dclc' || $method == 'edit_dclc' || $method == 'save_dclc'){
						if(in_array(10,$this->session->userdata('position_id_array'))){
							return call_user_func_array(array($this, $method), $params);
						}
					}
					if($method == 'list_pg_msg' ||
						$method == 'edit_pg_msg' ||
						$method == 'save_pg_msg' ||
						$method == 'list_pg' ||
						$method == 'add_pg' ||
						$method == 'save_pg' ||
						$method == 'edit_pg' ||
						$method == 'get_fj_type' ||
						$method == 'list_pg_qq' ||
						$method == 'add_pg_qq' ||
						$method == 'save_pg_qq' ||
						$method == 'delete_pg_qq' ||
						$method == 'edit_pg_qq'
					){
						if(in_array(11,$this->session->userdata('position_id_array'))){
							return call_user_func_array(array($this, $method), $params);
						}
					}
					redirect(site_url('/manage'));
					exit();
				}
			}

		}
	}

	public function index()
	{
		$this->load->view('manage/index.php');
	}

	/**
	 * 公司信息
	 */
	public function list_company() {
		$data = $this->manage_model->list_company();
		$this->load->view('manage/list_company.php',$data);
	}

	public function add_company() {
		$data['menu_list'] = $this->manage_model->get_menu_list();
		$this->load->view('manage/add_company.php', $data);
	}

	public function save_company() {
		$ret = $this->manage_model->save_company();
		if($ret == 1){
			form_submit_json("200", "操作成功", 'list_company');
		} else {
			form_submit_json("300", "保存失败");
		}
	}

	public function edit_company($id) {
		$data = $this->manage_model->get_company($id);
		$data['menu_list'] = $this->manage_model->get_menu_list();
		$this->load->view('manage/add_company.php', $data);
	}

	public function delete_company($id) {
		$ret = $this->manage_model->delete_company($id);
		if($ret == 1) {
			form_submit_json("200", "操作成功", 'list_company', '', '');
		} else {
			form_submit_json("300", "删除失败");
		}
	}

	/**
	 * 分店信息
	 */
	public function list_subsidiary() {
		$data = $this->manage_model->list_subsidiary();
		$this->load->view('manage/list_subsidiary.php', $data);
	}

	public function add_subsidiary() {
		$data = array();
		$data['company_list'] = $this->manage_model->get_company_list();
		$this->load->view('manage/add_subsidiary.php', $data);
	}

	public function save_subsidiary() {
		$ret = $this->manage_model->save_subsidiary();
		if($ret == 1){
			form_submit_json("200", "操作成功", 'list_subsidiary');
		}else{
			form_submit_json("300", "保存失败");
		}
	}

	public function edit_subsidiary($id) {
		$data = $this->manage_model->get_subsidiary($id);
		$data['company_list'] = $this->manage_model->get_company_list();
		$this->load->view('manage/add_subsidiary.php', $data);
	}

	public function delete_subsidiary($id) {
		$ret = $this->manage_model->delete_subsidiary($id);
		if($ret == 1) {
			form_submit_json("200", "操作成功", 'list_subsidiary', '', '');
		} else {
			form_submit_json("300", "删除失败");
		}
	}

	/**
	 * 角色信息
	 */
	public function list_role() {
		$data = $this->manage_model->list_role();
		$this->load->view('manage/list_role.php',$data);
	}

	public function add_role() {
		$this->load->view('manage/add_role.php');
	}

	public function save_role() {
		$ret = $this->manage_model->save_role();
		if($ret == 1){
			form_submit_json("200", "操作成功", 'list_role');
		} else {
			form_submit_json("300", "保存失败");
		}
	}

	public function edit_role($id) {
		$data = $this->manage_model->get_role($id);
		$this->load->view('manage/add_role.php', $data);
	}

	public function delete_role($id) {
		$ret = $this->manage_model->delete_role($id);
		if($ret == 1) {
			form_submit_json("200", "操作成功", 'list_role', '', '');
		} else {
			form_submit_json("300", "删除失败");
		}
	}

	/**
	 * 行程选项
	 */
	public function list_activity_type() {
		$data = $this->manage_model->list_activity_type();
		$this->load->view('manage/list_activity_type.php',$data);
	}

	public function add_activity_type() {
		$this->load->view('manage/add_activity_type.php');
	}

	public function save_activity_type() {
		$ret = $this->manage_model->save_activity_type();
		if($ret == 1){
			form_submit_json("200", "操作成功", 'list_activity_type');
		} else {
			form_submit_json("300", "保存失败");
		}
	}

	public function edit_activity_type($id) {
		$data = $this->manage_model->get_activity_type($id);
		$this->load->view('manage/add_activity_type.php', $data);
	}

	public function delete_activity_type($id) {
		$ret = $this->manage_model->delete_activity_type($id);
		if($ret == 1) {
			form_submit_json("200", "操作成功", 'list_activity_type', '', '');
		} else {
			form_submit_json("300", "删除失败");
		}
	}

	/**
	 * 用户管理
	 */
	public function list_user() {
		$data = $this->manage_model->list_user();
		$data['company_list'] = $this->manage_model->get_company_list();
		if($this->input->post('company_id'))
			$data['subsidiary_list'] = $this->manage_model->get_subsidiary_list_by_company($this->input->post('company_id'));
		$data['position_list'] = $this->manage_model->get_position_list();
		$data['role_list'] = $this->manage_model->get_role_list();
		$this->load->view('manage/list_user.php', $data);
	}

	public function add_user() {
		$data = array();
		$data['company_list'] = $this->manage_model->get_company_list();
		$data['position_list'] = $this->manage_model->get_position_list();
		if(!empty($data['company_list'])) {
			$data['subsidiary_list'] = $this->manage_model->get_subsidiary_list_by_company($data['company_list'][0]->id);
		}
		$data['role_list'] = $this->manage_model->get_role_list();
		$this->load->view('manage/add_user.php', $data);
	}

	public function save_user() {

		if(!$this->input->post('id')){
			$tel = trim($this->input->post('tel'));
			$broker = $this->manage_model->get_user_by_tel($tel);
			if(!empty($broker)) {
				form_submit_json("300", "手机号已经注册过");
				return;
			}
		}else{
			$tel = trim($this->input->post('tel'));
			$broker = $this->manage_model->get_user_by_tel($tel,$this->input->post('id'));
			if(!empty($broker)) {
				form_submit_json("300", "手机号已经注册过");
				return;
			}
		}

		if($_FILES["userfile"]['name'] and $this->input->post('old_img')){//修改上传的图片，需要先删除原来的图片
			@unlink('./././uploadfiles/profile/'.$this->input->post('old_img'));//del old img
		}else if(!$_FILES["userfile"]['name'] and !$this->input->post('old_img')){//未上传图片
			form_submit_json("300", "请添加图片");exit;
		}

		if(!$_FILES["userfile"]['name'] and $this->input->post('old_img')){//不修改图片信息
			$ret = $this->manage_model->save_user();
		}else{
			$config['upload_path'] = './././uploadfiles/profile';
			$config['allowed_types'] = 'gif|jpg|png|jpeg';
			$config['max_size'] = '1000';
			$config['encrypt_name'] = true;
			$this->load->library('upload', $config);
			if($this->upload->do_upload()){
				$img_info = $this->upload->data();
				$ret = $this->manage_model->save_user($img_info['file_name']);
			}else{
				form_submit_json("300", $this->upload->display_errors('<b>','</b>'));
				exit;
			}
		}

		if($ret == 1){
			form_submit_json("200", "操作成功", 'list_user');
		} else {
			form_submit_json("300", "保存失败");
		}
	}

	public function password_reset($id=0){
		if($id==0){
			$res = 2;
		}else{
			$res = $this->manage_model->password_reset($id);
		}

		echo json_encode($res);
	}

	public function edit_user($id) {
		$data = $this->manage_model->get_user($id);
		$data['pids'] = $this->manage_model->get_user_pid($id);
		$data['subids'] = $this->manage_model->get_user_subid($id);
		$data['company_list'] = $this->manage_model->get_company_list();
		$data['position_list'] = $this->manage_model->get_position_list();
		$data['subsidiary_list'] = $this->manage_model->get_subsidiary_list_by_company($data['company_id']);
		$data['role_list'] = $this->manage_model->get_role_list();
		$this->load->view('manage/add_user.php', $data);
	}

	public function delete_user($id) {
		$ret = $this->manage_model->delete_user($id);
		if($ret == 1) {
			form_submit_json("200", "操作成功", 'list_user', '', '');
		} else {
			form_submit_json("300", "删除失败");
		}
	}

	public function get_subsidiary_list($id) {
		$data = $this->manage_model->get_subsidiary_list_by_company($id);
		$subSidiary = array();
		foreach ($data as $s) {
			$subSidiary[] = array($s['id'], $s['name']);
		}
		echo json_encode($subSidiary);
		die;
	}

	public function get_subsidiary_list_2($id=0) {
		$data = $this->manage_model->get_subsidiary_list_by_company($id);
		$subSidiary = array();
		$subSidiary[] = array('','请选择分店');
		foreach ($data as $s) {
			$subSidiary[] = array($s['id'], $s['name']);
		}
		echo json_encode($subSidiary);
		die;
	}

	public function get_subsidiary_list_age($id=0) {
		$data = $this->manage_model->get_subsidiary_list_by_company_age($id);
		$subSidiary = array();
		$subSidiary[] = array('','请选择分店');
		foreach ($data as $s) {
			$subSidiary[] = array($s['id'], $s['name']);
		}
		echo json_encode($subSidiary);
		die;
	}

	public function get_user_list_age($id=0) {
		$data = $this->manage_model->get_user_list_by_subsidiary_age($id);
		$subSidiary = array();
		$subSidiary[] = array('','请选择人员');
		foreach ($data as $s) {
			$subSidiary[] = array($s['id'], $s['rel_name']);
		}
		echo json_encode($subSidiary);
		die;
	}
	/**
	 *
	 * ***************************************以下为职务列表*******************************************************************
	 */

	public function list_position()
	{
		$data = $this->manage_model->list_position();
		$this->load->view('manage/list_position.php',$data);
	}

	public function add_position(){
		$this->load->view('manage/add_position.php');
	}

	public function save_position(){
		$rs = $this->manage_model->save_position();
		if ($rs === 1) {
			form_submit_json("200", "操作成功", "list_position");
		} else {
			form_submit_json("300", $rs);
		}
	}

	public function delete_position($id){
		$rs = $this->manage_model->delete_position($id);
		if ($rs === 1) {
			form_submit_json("200", "操作成功", "list_position", "", "");
		} else {
			form_submit_json("300", $rs);
		}
	}

	public function edit_position($id){
		$data = $this->manage_model->get_position($id);
		$this->load->view('manage/add_position.php',$data);
	}

	/**
	 *
	 * ***************************************以下为代办进程列表*******************************************************************
	 */

	public function list_course()
	{
		$data = $this->manage_model->list_course();
		$this->load->view('manage/list_course.php',$data);
	}

	public function add_course(){
		$this->load->view('manage/add_course.php');
	}

	public function save_course(){
		$rs = $this->manage_model->save_course();
		if ($rs === 1) {
			form_submit_json("200", "操作成功", "list_course");
		} else {
			form_submit_json("300", $rs);
		}
	}

	public function delete_course($id){
		$rs = $this->manage_model->delete_course($id);
		if ($rs === 1) {
			form_submit_json("200", "操作成功", "list_course", "", "");
		} else {
			form_submit_json("300", $rs);
		}
	}

	public function edit_course($id){
		$data = $this->manage_model->get_course($id);
		$this->load->view('manage/add_course.php',$data);
	}

	/**
	 *
	 * ***************************************以下为区镇列表*******************************************************************
	 */

	public function list_towns()
	{
		$data = $this->manage_model->list_towns();
		$this->load->view('manage/list_towns.php',$data);
	}

	public function add_towns(){
		$this->load->view('manage/add_town.php');
	}

	public function save_towns(){
		$rs = $this->manage_model->save_towns();
		if ($rs === 1) {
			form_submit_json("200", "操作成功", "list_towns");
		} else {
			form_submit_json("300", $rs);
		}
	}

	public function delete_towns($id){
		$rs = $this->manage_model->delete_towns($id);
		if ($rs === 1) {
			form_submit_json("200", "操作成功", "list_towns", "", "");
		} else {
			form_submit_json("300", $rs);
		}
	}

	public function edit_towns($id){
		$data = $this->manage_model->get_towns($id);
		$this->load->view('manage/add_town.php',$data);
	}

	/**
	 *
	 * ***************************************以下为小区列表*******************************************************************
	 */

	public function list_xiaoqu()
	{
		$data = $this->manage_model->list_xiaoqu();
		$this->load->view('manage/list_xiaoqu.php',$data);
	}

	public function add_xiaoqu(){
		$data = array();
		$data['towns_list'] = $this->manage_model->get_towns_list();
		$this->load->view('manage/add_xiaoqu.php',$data);
	}

	public function save_xiaoqu(){
		$rs = $this->manage_model->save_xiaoqu();
		if ($rs === 1) {
			form_submit_json("200", "操作成功", "list_xiaoqu");
		} else {
			form_submit_json("300", $rs);
		}
	}

	public function delete_xiaoqu($id){
		$rs = $this->manage_model->delete_xiaoqu($id);
		if ($rs === 1) {
			form_submit_json("200", "操作成功", "list_xiaoqu", "", "");
		} else {
			form_submit_json("300", $rs);
		}
	}

	public function edit_xiaoqu($id){
		$data = $this->manage_model->get_xiaoqu($id);
		$data['towns_list'] = $this->manage_model->get_towns_list();
		$this->load->view('manage/add_xiaoqu.php',$data);
	}
	/**
	 *
	 * ***************************************以下为区镇列表*******************************************************************
	 */

	public function list_menu()
	{
		$data = $this->manage_model->list_menu();
		$this->load->view('manage/list_menu.php',$data);
	}

	public function add_menu(){
		$data['icon_list'] = $this->manage_model->get_icon_list();
		$this->load->view('manage/add_menu.php',$data);
	}

	public function save_menu(){
		$rs = $this->manage_model->save_menu();
		if ($rs === 1) {
			form_submit_json("200", "操作成功", "list_menu");
		} else {
			form_submit_json("300", $rs);
		}
	}

	public function delete_menu($id){
		$rs = $this->manage_model->delete_menu($id);
		if ($rs === 1) {
			form_submit_json("200", "操作成功", "list_menu", "", "");
		} else {
			form_submit_json("300", $rs);
		}
	}

	public function edit_menu($id){
		$data = $this->manage_model->get_menu($id);
		$data['icon_list'] = $this->manage_model->get_icon_list();
		$data['menu_detail'] = $this->manage_model->get_menu_detail($id);
		$this->load->view('manage/add_menu.php',$data);
	}

	/**
	 *
	 * ***************************************以下为文档类别列表*******************************************************************
	 */

	public function list_forum_type()
	{

		$data = $this->manage_model->list_forum_type();
		//die(var_dump($data));
		$this->load->view('manage/list_forum_type.php',$data);
	}

	public function add_forum_type(){
		$this->load->view('manage/add_forum_type.php');
	}

	public function save_forum_type(){
		$rs = $this->manage_model->save_forum_type();
		if ($rs === 1) {
			form_submit_json("200", "操作成功", "list_forum_type");
		} else {
			form_submit_json("300", $rs);
		}
	}

	public function delete_forum_type($id){
		$rs = $this->manage_model->delete_forum_type($id);
		if ($rs === 1) {
			form_submit_json("200", "操作成功", "list_forum_type", "", "");
		} else {
			form_submit_json("300", $rs);
		}
	}

	public function edit_forum_type($id){
		$data = $this->manage_model->get_forum_type($id);
		$this->load->view('manage/add_forum_type.php',$data);
	}

	public function list_ticket(){
		$data = $this->manage_model->list_ticket();
		$this->load->view('manage/list_ticket.php',$data);
	}

	public function edit_ticket($id){

		$data = $this->manage_model->get_ticket($id);
		$this->load->view('manage/edit_ticket.php',$data);
	}

	public function downdoc($id){

		$this->manage_model->download($id);
	}

	public function delete_ticket($id){
		$rs = $this->manage_model->delete_ticket($id);
		if ($rs === 1) {
			form_submit_json("200", "操作成功", "list_ticket", "", "");
		} else {
			form_submit_json("300", $rs);
		}
	}
	/**
	 *
	 * ***************************************以下为新闻列表*******************************************************************
	 */
	public function list_news()
	{
		$data = $this->manage_model->list_news();
		$this->load->view('manage/list_news.php',$data);
	}


	public function delete_news($id){
		$rs = $this->manage_model->delete_news($id);
		if ($rs === 1) {
			form_submit_json("200", "操作成功", "list_news", "", "");
		} else {
			form_submit_json("300", $rs);
		}
	}

	public function edit_news($id){
		$data = $this->manage_model->get_news($id);
		$this->load->view('manage/edit_news.php',$data);
	}
	/*public function save_ticket(){
		$rs = $this->manage_model->save_ticket();
		if ($rs === 1) {
			form_submit_json("200", "操作成功", "list_ticket");
		} else {
			form_submit_json("300", $rs);
		}

	}*/
	/**
	 *
	 * ***************************************以下为试题列表*******************************************************************
	 */
	public function list_questions()
	{
		$data = $this->manage_model->list_questions();
		$this->load->view('manage/list_questions.php',$data);
	}


	public function delete_questions($id){
		$rs = $this->manage_model->delete_questions($id);
		if ($rs === 1) {
			form_submit_json("200", "操作成功", "list_questions", "", "");
		} else {
			form_submit_json("300", $rs);
		}
	}

	public function use_questions($id){
		$rs = $this->manage_model->use_questions($id);
		if ($rs === 1) {
			form_submit_json("200", "操作成功", "list_questions", "", "");
		} else {
			form_submit_json("300", $rs);
		}
	}

	public function edit_questions($id){
		$data = $this->manage_model->get_questions($id);
		$this->load->view('manage/edit_questions.php',$data);
	}
	/*public function save_ticket(){
		$rs = $this->manage_model->save_ticket();
		if ($rs === 1) {
			form_submit_json("200", "操作成功", "list_ticket");
		} else {
			form_submit_json("300", $rs);
		}
	}*/

	/**
	 *
	 * ***************************************以下为资金流水列表*******************************************************************
	 */
	public function list_sum_log(){
		$data = $this->manage_model->list_sum_log();
		$this->load->view('manage/list_sum_log.php',$data);
	}

	public function delete_sum_log($id){
		$rs = $this->manage_model->delete_sum_log($id);
		if ($rs === 1) {
			form_submit_json("200", "操作成功", "list_sum_log", "", "");
		} else {
			form_submit_json("300", $rs);
		}
	}

	/**
	 *
	 * ***************************************以下为资金流水列表*******************************************************************
	 */
	public function list_agenda(){
		$data = $this->manage_model->list_agenda();
		$data['company_list'] = $this->manage_model->get_company_list_age();
		$data['dbyh_list'] = $this->manage_model->get_dbyh_list();
		$data['dbgh_list'] = $this->manage_model->get_dbgh_list();
		if($this->input->post('company_id'))
			$data['subsidiary_list'] = $this->manage_model->get_subsidiary_list_age($this->input->post('company_id'));
		if($this->input->post('subsidiary_id'))
			$data['user_list'] = $this->manage_model->get_user_list_by_subsidiary_age($this->input->post('subsidiary_id'));

		$this->load->view('manage/list_agenda.php',$data);
	}

	public function delete_agenda($id) {
		$ret = $this->manage_model->delete_agenda($id);
		if($ret == 1) {
			form_submit_json("200", "操作成功", 'list_agenda', '', '');
		} else {
			form_submit_json("300", "删除失败");
		}
	}

	/**
	 *
	 * ***************************************以下为大成莲创列表*******************************************************************
	 */
	public function list_dclc(){
		$data = $this->manage_model->list_dclc();
		$this->load->view('manage/list_dclc.php',$data);
	}

	public function edit_dclc($id){
		$data = $this->manage_model->edit_dclc($id);
		$this->load->view('manage/edit_dclc.php',$data);
	}

	public function save_dclc(){
		$res = $this->manage_model->save_dclc();
		if($res == 1) {
			form_submit_json("200", "操作成功", 'list_dclc');
		} else {
			form_submit_json("300", "删除失败");
		}
	}

	/**
	 *
	 * ***************************************以下为房屋评估管理*******************************************************************
	 */

	public function list_pg()
	{
		$data = $this->manage_model->list_pg();
		$this->load->view('manage/list_pg.php',$data);
	}

	public function add_pg(){
		$data['list_area'] = $this->manage_model->get_fj_area();
		$data['list_type'] = $this->manage_model->get_fj_type();
		$this->load->view('manage/add_pg.php',$data);
	}

	public function save_pg(){
		$rs = $this->manage_model->save_pg();
		if ($rs === 1) {
			form_submit_json("200", "操作成功", "list_pg");
		} elseif($rs == -1){
			form_submit_json("300", '存在相同小区名字的信息已经保存!');
		}else {
			form_submit_json("300", $rs);
		}
	}

	public function edit_pg($id){
		$data = $this->manage_model->get_pg($id);
		//die(var_dump($data));
		$data['list_area'] = $this->manage_model->get_fj_area();
		$data['list_type'] = $this->manage_model->get_fj_type();
		$this->load->view('manage/add_pg.php',$data);
	}

	public function get_fj_type(){
		$data = $this->manage_model->get_fj_type();
		return json_encode($data);
	}

	public function list_pg_qq(){
		$data = $this->manage_model->list_pg_qq();
		$this->load->view('manage/list_pg_qq.php',$data);
	}

	public function add_pg_qq(){
		$this->load->view('manage/add_pg_qq.php');
	}

	public function save_pg_qq(){
		$rs = $this->manage_model->save_pg_qq();
		if ($rs === 1) {
			form_submit_json("200", "操作成功", "list_pg_qq");
		} else {
			form_submit_json("300", $rs);
		}
	}

	public function delete_pg_qq($id){
		$rs = $this->manage_model->delete_pg_qq($id);
		if ($rs === 1) {
			form_submit_json("200", "操作成功", "list_pg_qq", "", "");
		} else {
			form_submit_json("300", $rs);
		}
	}

	public function edit_pg_qq($id){
		$data = $this->manage_model->get_pg_qq($id);
		$this->load->view('manage/add_pg_qq.php',$data);
	}
	/**
	 *
	 * ***************************************以下为评估页面留言列表*******************************************************************
	 */
	public function list_pg_msg(){
		$data = $this->manage_model->list_pg_msg();
		$this->load->view('manage/list_pg_msg.php',$data);
	}

	public function edit_pg_msg($id){
		$data = $this->manage_model->edit_pg_msg($id);
		$this->load->view('manage/edit_pg_msg.php',$data);
	}

	public function save_pg_msg(){
		$res = $this->manage_model->save_pg_msg();
		if($res == 1) {
			form_submit_json("200", "操作成功", 'list_pg_msg');
		} else {
			form_submit_json("300", "删除失败");
		}
	}
	/**
	 *
	 * ***************************************以下为金融页面列表*******************************************************************
	 */
	public function list_fin(){
		$data = $this->manage_model->list_fin();
		$data['company_list'] = $this->manage_model->get_company_list_age();
		if($this->input->post('company_id'))
			$data['subsidiary_list'] = $this->manage_model->get_subsidiary_list_age($this->input->post('company_id'));
		if($this->input->post('subsidiary_id'))
			$data['user_list'] = $this->manage_model->get_user_list_by_subsidiary_age($this->input->post('subsidiary_id'));
		$this->load->view('manage/list_fin.php',$data);
	}
}

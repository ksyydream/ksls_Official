<?php
if (! defined('BASEPATH'))
exit('No direct script access allowed');
/**
 * 扩展业务控制器
 *
 * @package		app
 * @subpackage	Libraries
 * @category	controller
 * @author      yaobin<645894453@qq.com>
 *
 */
class MY_Controller extends CI_Controller
{
	public function __construct ()
	{
		parent::__construct();
		ini_set('date.timezone','Asia/Shanghai');
		$this->cismarty->assign('base_url',base_url());//url路径
//		if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
//			if(!$this->session->userdata('openid')){
//				$appid="wxa6a2f25241f8bc87";
//				$secret="3cbf8a0ea011dd71a2fbc95124858804";
//				if(empty($_GET['code'])){
//					$url = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"];
//					$url = urlencode($url);
//					redirect("https://open.weixin.qq.com/connect/oauth2/authorize?appid={$appid}&redirect_uri={$url}&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect");
//				}else{
//					$j_access_token=file_get_contents("https://api.weixin.qq.com/sns/oauth2/access_token?appid={$appid}&secret={$secret}&code={$_GET['code']}&grant_type=authorization_code");
//					$a_access_token=json_decode($j_access_token,true);
//					$access_token=$a_access_token["access_token"];
//					$openid=$a_access_token["openid"];
//					$this->sys_model->get_uid_byopenid($openid);
//					$this->session->set_userdata('openid', $openid);
//				}
//			}else{
//				$this->sys_model->get_uid_byopenid($this->session->userdata('openid'));
//			}

//		}
	}

	//重载smarty方法assign
	public function assign($key,$val) {
		$this->cismarty->assign($key,$val);
	}

	//重载smarty方法display
	public function display($html) {
		$this->cismarty->display($html);
	}
	
	//重载smarty方法show
	public function show($html) {
		$this->cismarty->show($html);
	}

	/**
	 * 树状结构菜单
	 **/
	public function subtree($arr,$id=0,$lev=1)
	{
		static $subs = array();
		foreach($arr as $v){
			if((int)$v['parent_id']==$id){
				$v['lev'] = $lev;
				$subs[]=$v;
				$this->subtree($arr,$v['id'],$lev+1);
			}
		}
		return $subs;
	}

	/**
	 * 获取页码列表
	 * 例如<上一页>...56789<下一页>
	 * @param int $total 总页数
	 * @param int $current 当前页
	 * @param int $page_list_size 显示页码个数
	 * @return array 显示页码的数组
	 **/
	public function get_page_list($total,$current,$page_list_size = '5')
	{
		$page= array();
		if($total<$page_list_size){
			for($i=1;$i<=$total;$i++){
				$page[]=$i;
			}
		}else{
			if($current <= ceil($page_list_size/2)){
				//当前页小于居中页码，则正常打印
				for($i=1;$i<=$page_list_size;$i++){
					$page[]=$i;
				}
			}else if($current > ($total - ceil($page_list_size/2))){
				//最后几页正常打印
				for($i=0;$i<$page_list_size;$i++){
					$page[]=$total-$i;
				}
				$page = array_reverse($page);
			}else{
				for($i=$current-floor($page_list_size/2);$i<=$current+floor($page_list_size/2);$i++){
					$page[]=$i;
				}
			}
		}
		return $page;
	}



	/**
	 * 提示信息
	 * @param varchar $message 提示信息
	 * @param varchar $url 跳转页面，如果为空则后退
	 * @param int $type 提示类型，1是自动关闭的提示框，2是错误提示框
	 **/
	public function show_message($message,$url=null,$type=1){
		if($url){
			$js = "location.href='".$url."';";
		}else{
			$js = "history.back();";
		}

		if($type=='1'){
			echo "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
				<html xmlns='http://www.w3.org/1999/xhtml'>
				<head>
				<meta name=\"viewport\" content=\"width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no\">
				<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
				<title>".$message."</title>
				<script src='".base_url()."statics/amaze/js/jquery.min.js'></script>
				<link rel='stylesheet' href='".base_url()."statics/css/easydialog.css'>
				</head>
				<body>
				<script src='".base_url()."statics/js/easydialog.min.js'></script>
				<script>
				var callFn = function(){
				  ".$js."
				};
				easyDialog.open({
					container : {
						content : '".$message."'
					},
					autoClose : 1200,
					callback : callFn
					
				});
				
				</script>
				</body>
				</html>";
		}
		exit;
	}

	/**
	 * URL重定向
	 * @param string $url 重定向的URL地址
	 * @param integer $time 重定向的等待时间（秒）
	 * @param string $msg 重定向前的提示信息
	 * @return void
	 */
	function redirect($url, $time=0, $msg='') {
		//多行URL地址支持
		$url        = str_replace(array("\n", "\r"), '', $url);
		if (empty($msg))
			$msg    = "系统将在{$time}秒之后自动跳转到{$url}！";
		if (!headers_sent()) {
			// redirect
			if (0 === $time) {
				// window.open($url);
				header('Location: ' . $url);
			} else {
				header("refresh:{$time};url={$url}");
				echo($msg);
			}
			exit();
		} else {
			$str    = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
			if ($time != 0)
				$str .= $msg;
			exit($str);
		}
	}
	
	/**
	 * 发送邮件
	 * @param varchar $from_mail 发件人
	 * @param varchar $to_mail 收件人
	 * @param varchar $title 标题
	 * @param varchar $content 内容
	 * @return int 1成功，-1失败
	 **/
	public function send_mail($from_mail,$to_mail,$title,$content)
	{
	 	$mail =  $to_mail;//需要发送的邮箱
		$name = "191农资人";//发件人姓名
		
		$this->load->library('email');            //加载CI的email类
		
		//以下设置Email参数
		$config['crlf'] = "\r\n";
		$config['newline'] = "\r\n";
		$config['protocol'] = 'smtp';
		$config['smtp_host'] = 'smtp.exmail.qq.com';
		$config['smtp_user'] = $from_mail;
		$config['smtp_pass'] = 'JQ83wOiQ';
		$config['smtp_port'] = 25;
		$config['charset'] = 'utf-8';
		$config['wordwrap'] = TRUE;
		$config['mailtype'] = 'html';
		$this->email->initialize($config);
		
		//以下设置Email内容
		$this->email->from($from_mail, $name);
		$this->email->to($mail);
		$this->email->subject($title);
		$this->email->message($content);
		//$this->email->attach('application\controllers\1.jpeg');			//相对于index.php的路径
		if($this->email->send()){
			return  '1';
		}else{
			return  '-1';
		}

		//return $this->email->print_debugger();		//返回包含邮件内容的字符串，包括EMAIL头和EMAIL正文。用于调试。
	}
	
	/**************************************************************
	*  生成指定长度的随机码。
	*  @param int $length 随机码的长度。
	*  @access public
	**************************************************************/
	function createRandomCode($length)
	{
		$randomCode = "";
		$randomChars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		for ($i = 0; $i < $length; $i++)
		{
			$randomCode .= $randomChars { mt_rand(0, 35) };
		}
		return $randomCode;
	}
	
	/**************************************************************
	*  生成指定长度的随机码。
	*  @param int $length 随机码的长度。
	*  @access public
	**************************************************************/
	function toVirtualPath($physicalPpath)
	{
		$virtualPath = str_replace($_SERVER['DOCUMENT_ROOT'], "", $physicalPpath);
		$virtualPath = str_replace("\\", "/", $virtualPath);
		return $virtualPath;
	}

	public function upload($folder = 'face',$input_name = 'img_input') {
		$base64 = $this->input->post($input_name);
		if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64, $result)){
			$name = date('Y/m/d', time());
			$dir = FCPATH . '/upload/'.$folder.'/' . $name . '/';
			if(!is_dir($dir)){
				mkdir($dir,0777,true);
			}
			$img_name = $this->getRandChar(24).'.jpg';
			$img = base64_decode(str_replace($result[1], '', $base64));
			file_put_contents($dir.$img_name, $img);//返回的是字节数
			return $name.'/'.$img_name;
		}
		return '';
	}

	function getRandChar($length){
		$str = null;
		$strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
		$max = strlen($strPol)-1;

		for($i=0;$i<$length;$i++){
			$str.=$strPol[rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
		}

		return $str;
	}

    protected function json($code, $msg, $data = null) {
        $res = array();
        $res['code'] = $code;
        $res['msg'] = $msg;
        $res['data'] = $data;
        exit(json_encode($res));
    }

	public function sendsms_curl($moblie,$text){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "http://sms-api.luosimao.com/v1/send.json");

		curl_setopt($ch, CURLOPT_HTTP_VERSION  , CURL_HTTP_VERSION_1_0 );
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);

		curl_setopt($ch, CURLOPT_HTTPAUTH , CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD  , 'api:key-e3829a670f2c515ab8befa5096dd135c');

		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, array('mobile' => $moblie,'message' => "{$text}【三客柚】"));

		$res = curl_exec( $ch );
		curl_close( $ch );
		return $res;
	}

	public function buildWxData(){
		$signPackage = @$this->wxjssdk->wxgetSignPackage();
		//变量
		$this->cismarty->assign('wxappId',$signPackage["appId"]);
		$this->cismarty->assign('wxtimestamp',$signPackage["timestamp"]);
		$this->cismarty->assign('wxnonceStr',$signPackage["nonceStr"]);
		$this->cismarty->assign('wxsignature',$signPackage["signature"]);
	}

	/**
	 *设置令牌
	 * 一般在展示表单页面设置令牌
     */
	function set_token()
	{
		$tokenVal = md5($this->getRandChar(10));
		if(isset($_SESSION['token'])){
			$_SESSION['token'][] = $tokenVal;
		}else{
			$_SESSION['token'] = array($tokenVal);
		}

		$this->assign('token',$tokenVal);
	}

	function valid_token()
	{
		//回退的情况，$_POST为空；$_POST['token'] 值为假则是表单没有加token隐藏域
		if(!$_POST || !isset($_POST['token'])) return false;
		if(in_array($_POST['token'],$_SESSION['token'])){
			$return = true;
			$key = array_search($_POST['token'],$_SESSION['token']);
			unset($_SESSION['token'][$key]);
		}else{
			$return = false;
		}
		//重置，避免类似 ajax重复提交
		$this->set_token();

		return $return;
	}
}

/* End of file MY_Controller.php */
/* Location: ./application/core/MY_Controller.php */
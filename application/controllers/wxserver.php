<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 16/8/1
 * Time: 上午11:36
 */

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//t
class Wxserver extends CI_Controller {
    protected $wxconfig = array();
    public function __construct()
    {
        parent::__construct();
        ini_set('date.timezone','Asia/Shanghai');
        $this->load->model('wxserver_model');
        $this->load->helper('url');
        $this->load->config('wxpay_config');
       $this->wxconfig['appid']=$this->config->item('appid');
        $this->wxconfig['mch_id']=$this->config->item('mch_id');
        $this->wxconfig['apikey']=$this->config->item('apikey');
        $this->wxconfig['appsecret']=$this->config->item('appsecret');
        $this->wxconfig['sslcertPath']=$this->config->item('sslcertPath');
        $this->wxconfig['sslkeyPath']=$this->config->item('sslkeyPath');
        if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
            if(!$this->session->userdata('openid')){
                $appid = APP_ID; //我把微信的appid 写成了全局变量,一般放在application/config/constant.php 中
                $secret = APP_SECRET;//同上

                if(empty($_GET['code'])){
                    $url = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"]; //这是要回调地址可以有别的写法
                    redirect("https://open.weixin.qq.com/connect/oauth2/authorize?appid={$appid}&redirect_uri={$url}&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect");
                    //重定向到以上网址,这是微信给的固定地址.必须格式一致
                }else{
                    //回调成功,获取code,再做请求,获取openid
                    $j_access_token=file_get_contents("https://api.weixin.qq.com/sns/oauth2/access_token?appid={$appid}&secret={$secret}&code={$_GET['code']}&grant_type=authorization_code");
                    $a_access_token=json_decode($j_access_token,true);
                    $access_token=$a_access_token["access_token"];//虽然这里 也获取了一个access_token,但是和获取用户详情,还有发送模板信息所使用的access_token不同
                    $openid=$a_access_token["openid"];
                    $this->session->set_userdata('openid', $openid);
                }
            }
        }else{
           // $this->session->set_userdata('openid', '123123');
        }
    }

    //重载smarty方法assign
    public function assign($key,$val) {
        $this->cismarty->assign($key,$val);
    }

    //重载smarty方法display
    public function display($html) {
        $this->cismarty->display($html);
    }

    public function index()
    {
        $echoStr = $_GET["echostr"];

        //valid signature , option
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }
    }

    public function responseMsg()
    {
        //get post data, May be due to the different environments
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

        //extract post data
        if (!empty($postStr)){

            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $fromUsername = $postObj->FromUserName;
            $toUsername = $postObj->ToUserName;
            $keyword = trim($postObj->Content);
            $time = time();
            $textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Content><![CDATA[%s]]></Content>
							<FuncFlag>0</FuncFlag>
							</xml>";
            if(!empty( $keyword ))
            {
                $msgType = "text";
                $contentStr = "Welcome to wechat world!";
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                echo $resultStr;
            }else{
                echo "Input something...";
            }

        }else {
            echo "";
            exit;
        }
    }

    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }

    public function bdwx(){
        $data['res'] = 0;
        $data['user_info'] = array();
        if($this->session->userdata('openid')){
            $res = $this->wxserver_model->check_openid();
            if($res){
                $data['user_info'] = $res;
            }
            $this->assign('data', $data);
            $this->display('wxhtml/login.html');
        }

    }

    public function save_openid(){
        $res = $this->wxserver_model->save_openid();
        $data['res'] = $res;
        $data['user_info'] = array();
        if($this->session->userdata('openid')){
            $res = $this->wxserver_model->check_openid();
            if($res){
                $data['user_info'] = $res;
            }
            $this->assign('data', $data);
            $this->display('wxhtml/login.html');
        }
    }

    public function fasongxc(){
        $this->wxserver_model->fasongxc();
    }

    public function text(){
        /*$dataxml['first'] = array('value'=>'数据提交成功');
        $dataxml['keynote1'] = array('value'=>$this->input->post('title'));
        $dataxml['keynote2'] = array('value'=>date("Y-m-d H:i:s"));
        $dataxml['remark'] = array('value'=>'');

        $data = array(
            "touser"=>'oFzKgwbFEyC40jU6bS_HQ5sxM4X8',
            "template_id"=>'GCLMW8LVj59vIBGfAnoTjo-98pcxBcZak_4eFornX0g',
            "url"=>"http://weixin.qq.com/download",
            'data' => urldecode(json_encode($dataxml))
        );

        die(var_dump(json_encode($data)));*/

        $access_token = 'KS3N4n80ZPeLsxPQIlgicPC5fGfyjhXAILK4Nv5QbV4xm4uuOnoYYJUbu89p1g0fqVmWZjdsg3ypfvnJ3CzcSXUwd7q1K9RPSMsNqRHl_e8';
        $url = '改成接口URL ?access_token=' . $access_token;//access_token改成你的有效值

        $data = array(
            'first' => array(
                'value' => '有一名客户进行了一次预约！',
                'color' => '#FF0000'
            ),
            'keyword1' => array(
                'value' => '2015/10/5 14:00~14:45',
                'color' => '#FF0000'
            ),
            'keyword2' => array(
                'value' => '都会型SPA',
                'color' => '#FF0000'
            ),
            'remark' => array(
                'value' => '请您务必准时到场为客户提供SPA服务！',
                'color' => '#FF0000'
            )
        );
        $template_msg=array('touser'=>'oFzKgwbFEyC40jU6bS_HQ5sxM4X8','template_id'=>'GCLMW8LVj59vIBGfAnoTjo-98pcxBcZak_4eFornX0g','topcolor'=>'#FF0000','data'=>$data);
        die(var_dump($template_msg));
        $curl = curl_init($url);
        $header = array();
        $header[] = 'Content-Type: application/x-www-form-urlencoded';
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
// 不输出header头信息
        curl_setopt($curl, CURLOPT_HEADER, 0);
// 伪装浏览器
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36');
// 保存到字符串而不是输出
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
// post数据
        curl_setopt($curl, CURLOPT_POST, 1);
// 请求数据
        curl_setopt($curl,CURLOPT_POSTFIELDS,json_encode($template_msg));
        $response = curl_exec($curl);
        curl_close($curl);
        echo $response;
    }

    public function test2(){
        phpinfo();
    }

    public function jsapi_wxpay(){
       /* if(!$this->session->userdata('openid')){
            echo APPPATH . 'libraries/wxpay/cert/apiclient_cert.pem';
            echo APPPATH . "libraries/wxpay/cert/apiclient_key.pem";
            die('请使用微信登陆');
        }*/

//error_reporting(E_ERROR);
        require_once(APPPATH ."libraries/wxpay/lib/WxPay.Api.php");
        require_once(APPPATH ."libraries/wxpay/lib/WxPay.JsApiPay.php");

        $res_order = $this->wxserver_model->save_order();
        if($res_order == -1){
            die('订单保存失败');
        }
//①、获取用户openid
        $tools = new JsApiPay();
//②、统一下单
        $input = new WxPayUnifiedOrder();
        $input->SetBody("SetBody");
        $input->SetAttach("SetAttach");
        $input->SetOut_trade_no($res_order);
        $input->SetTotal_fee("1");
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag("SetGoods_tag");
        $input->SetNotify_url("http://www.funmall.com.cn/wxserver/notify");
        $input->SetTrade_type("JSAPI");
        $input->SetOpenid($this->session->userdata('openid'));
        $order = WxPayApi::unifiedOrder($input);
      /*  echo '<font color="#f00"><b>统一下单支付单信息</b></font><br/>';
        printf_info($order);*/
        $data['jsApiParameters'] = $tools->GetJsApiParameters($order);
        $this->load->view('wxhtml/jsapi', $data);
    }

    public function jsapi_wxpay2(){
        if(!$this->session->userdata('openid')){
            die('请使用微信登陆');
        }
        $this->load->library('wxpay/Wechatpay',$this->wxconfig);
        $res_order = $this->wxserver_model->save_order();
        if($res_order == -1){
            die('订单保存失败');
        }
        $param['body'] = '房猫服务中心';
        $param['attach'] = 'attach';
        $param['detail'] = "房猫微信充值——微信支付";
        $param['out_trade_no'] = $res_order;
        $param['total_fee'] = 1;
        $param["spbill_create_ip"] = $_SERVER['REMOTE_ADDR'];
        $param["time_start"] = date("YmdHis");
        $param["time_expire"] = date("YmdHis", time() + 600);
        $param["goods_tag"] = "房猫服务中心";
        $param["notify_url"] = base_url()."/wxserver/notify";
        $param["trade_type"] = "JSAPI";
        $param["openid"] = $this->session->userdata('openid');

        //统一下单，获取结果，结果是为了构造jsapi调用微信支付组件所需参数
        $result = $this->wechatpay->unifiedOrder($param);

        //如果结果是成功的我们才能构造所需参数，首要判断预支付id

        if (isset($result["prepay_id"]) && !empty($result["prepay_id"])) {
            //调用支付类里的get_package方法，得到构造的参数
            $data['parameters'] = json_encode($this->wechatpay->get_package($result['prepay_id']));
            $data['notifyurl'] = $param["notify_url"];
            $data['fee'] = 1;
            $data['pubid'] = $res_order;
            $data['orderid'] = $res_order;
            $this->load->view('wxhtml/jsapi', $data);
        }
    }

    public function notify(){
        $this->load->library('wxpay/Wechatpay',$this->wxconfig);
        $data_array = $this->wechatpay->get_back_data();
        if($data_array['result_code']=='SUCCESS' && $data_array['return_code']=='SUCCESS'){
            if($this->wxserver_model->change_order($data_array['out_trade_no'],'23')==-2){
                return 'FAIL';
            }else{
                return 'SUCCESS';
            }
        }
    }

    public function notify_tb($order_id){
        if($this->session->userdata('openid')) {
            $rs =  $this->wxserver_model->change_order($order_id);
            echo $rs;
        }
    }

    public function xmlToArray($xml){
        $data = simplexml_load_string($xml);
        $array = array();
        foreach($data->children() as $childItem) {
            $array = array_merge($array,array($childItem->getName()=>(string)$childItem));
        }
        return $array;
    }

    public function refund($id="")
    {
        if ($id == "") {
            //方便我手动调用退单
            $id = $this->uri->segment(3);
        }
        if($this->session->userdata('login_permission_id') != 1 ){
            die('no admin');
        }
        if (isset($id) && $id != "") {
            //1、取消订单可以退款。2、失败订单可以退款
            $pub = $this->wxserver_model->order_info($id);
            if ($pub) {
                $listno = $id;
                $fee = $pub['qty'];
                $this->load->library('wxpay/Wechatpay',$this->wxconfig);
                if (isset($listno) && $listno != "") {
                    $out_trade_no = $listno;
                    $total_fee = $fee * 100;
                    $refund_fee = $fee * 100;
                    //自定义商户退单号
                    $out_refund_no = $this->wxconfig['mch_id'] . date("YmdHis");
                    $result = $this->wechatpay->refund($out_trade_no, $out_refund_no, $total_fee, $refund_fee, $this->wxconfig['mch_id']);
                    //log::DEBUG(json_encode($result));
                    if (isset($result["return_code"]) && $result["return_code"] = "SUCCESS" && isset($result["result_code"]) && $result["result_code"] = "SUCCESS") {
                       echo 'YES';
                    }else{
                        echo 'NG';
                    }
                }
            }
        }
    }

}
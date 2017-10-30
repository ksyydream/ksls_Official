<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 16/8/1
 * Time: 上午11:36
 */

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//t
class Alipay extends CI_Controller {

    protected $alipay_config = array();
    public function __construct()
    {
        parent::__construct();
        ini_set('date.timezone','Asia/Shanghai');
        $this->config->load('alipay');

        $this->load->model('alipay_model');
        $this->load->helper('url');
      $this->alipay_config = array(
            "partner"       => $this->config->item('partner'),
            "seller_id"  => $this->config->item('seller_id'),
            "key"	=> $this->config->item('key'),
            "notify_url"	=> $this->config->item('notify_url'),
            "return_url"	=> $this->config->item('return_url'),

            "sign_type"	=> $this->config->item('sign_type'),
            "input_charset"	=> $this->config->item('input_charset'),
            "cacert"	=> $this->config->item('cacert'),
            "transport"	=> $this->config->item('transport'),
            "payment_type"=>$this->config->item('payment_type'),
            "service"=>$this->config->item('service'),
            "anti_phishing_key"	=> $this->config->item('anti_phishing_key'),
            "exter_invoke_ip"	=> $this->config->item('exter_invoke_ip')
        );
    }

    public function test1(){
        echo base_url();
        echo "<br />";
        die(var_dump($this->alipay_config));
        echo $this->config->item('notify_url');
    }

    public function save_order(){
       // header("Content-type:text/html;charset=utf-8");
        require_once(APPPATH.'libraries/alipay/alipay_submit.class.php');
       $res = $this->alipay_model->save_order();
        if($res == -1){
            redirect(site_url('account/recharge_list'));
            exit();
        }
        //商户订单号，商户网站订单系统中唯一订单号
        $out_trade_no = $res;
        //订单名称，必填
        //$subject = 'subject';
        $subject = '房猫服务中心账户充值';
        //付款金额，必填
        $total_fee = $this->input->post('qty');
        //商品描述，可空
        //$body = 'body';
        $body = '公司账户充值';
        //构造要请求的参数数组，无需改动
        $parameter = array(
            "service"       => $this->config->item('service'),
            "partner"       => $this->config->item('partner'),
            "seller_id"  => $this->config->item('seller_id'),
            "payment_type"	=> $this->config->item('payment_type'),
            "notify_url"	=> $this->config->item('notify_url'),
            "return_url"	=> $this->config->item('return_url'),

            "anti_phishing_key"=>$this->config->item('anti_phishing_key'),
            "exter_invoke_ip"=>$this->config->item('exter_invoke_ip'),
            "out_trade_no"	=> $out_trade_no,
            "subject"	=> $subject,
            "total_fee"	=> $total_fee,
            "body"	=> $body,
            "_input_charset"	=> trim(strtolower($this->config->item('input_charset')))
            //其他业务参数根据在线开发文档，添加参数.文档地址:https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.kiX33I&treeId=62&articleId=103740&docType=1
            //如"参数名"=>"参数值"
        );
        //建立请求
        $alipaySubmit = new AlipaySubmit($this->alipay_config);
        $html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
        echo $html_text;
    }

    public function returnpay(){
        require_once(APPPATH.'libraries/alipay/alipay_notify.class.php');
        $alipayNotify = new AlipayNotify($this->alipay_config);
        $verify_result = $alipayNotify->verifyReturn();
        if($verify_result) {//验证成功
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            //请在这里加上商户的业务逻辑程序代码

            //——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
            //获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表

            //商户订单号

            $out_trade_no = $_GET['out_trade_no'];

            //支付宝交易号

            $trade_no = $_GET['trade_no'];

            //交易状态
            $trade_status = $_GET['trade_status'];


            if($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS') {
               $res= $this->alipay_model->change_order($out_trade_no);
               // echo $res;
                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //如果有做过处理，不执行商户的业务程序
            }
            else {
                echo "trade_status=".$_GET['trade_status'];
            }

           redirect(site_url('account/recharge_list'));

            //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——

            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        }
        else {
            //验证失败
            //如要调试，请看alipay_notify.php页面的verifyReturn函数
            redirect(site_url('account/recharge_list'));
            //echo '验证失败';
           // redirect(site_url('account/recharge_list'));
        }
    }

    public function notifypay(){
        require_once(APPPATH.'libraries/alipay/alipay_notify.class.php');
        $alipayNotify = new AlipayNotify($this->alipay_config);
        $verify_result = $alipayNotify->verifyNotify();
        if($verify_result) {//验证成功
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            //请在这里加上商户的业务逻辑程序代


            //——请根据您的业务逻辑来编写程序（以下代码仅作参考）——

            //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表

            //商户订单号

            $out_trade_no = $_POST['out_trade_no'];

            //支付宝交易号

            $trade_no = $_POST['trade_no'];

            //交易状态
            $trade_status = $_POST['trade_status'];


            if($_POST['trade_status'] == 'TRADE_FINISHED') {
                $this->alipay_model->change_order($out_trade_no,'123');
                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //请务必判断请求时的total_fee、seller_id与通知时获取的total_fee、seller_id为一致的
                //如果有做过处理，不执行商户的业务程序

                //注意：
                //退款日期超过可退款期限后（如三个月可退款），支付宝系统发送该交易状态通知

                //调试用，写文本函数记录程序运行情况是否正常
                //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
            }
            else if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
                $this->alipay_model->change_order($out_trade_no,'123');
                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //请务必判断请求时的total_fee、seller_id与通知时获取的total_fee、seller_id为一致的
                //如果有做过处理，不执行商户的业务程序

                //注意：
                //付款完成后，支付宝系统发送该交易状态通知

                //调试用，写文本函数记录程序运行情况是否正常
                //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
            }

            //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——

            echo "success";		//请不要修改或删除

            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        }
        else {
            //验证失败
            echo "fail";

            //调试用，写文本函数记录程序运行情况是否正常
            //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
        }
    }
}
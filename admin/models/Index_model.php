<?php
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class Index_model extends MY_Model
{
    
    public function __construct ()
    {
    	parent::__construct();
    }
    
    public function index(){
    	return $this->db->select()->from('admin')->get()->row_array();
    }

    public function get_index_count(){
        $audit_shop = $this->db->select('count(1) num')->from('shop')->where('status',1)->get()->row();
        $shop = $this->db->select('count(1) num')->from('shop')->where_in('status',array(2,-2))->get()->row();

        $data = array(
            'audit_shop_count'=>$audit_shop->num,
            'shop_count'=>$shop->num,
        );
        return $data;
    }

    public function get_sett_info(){
        $sql = "select * from (select * from settlement order by date desc limit 14) aa order by aa.date asc";
        $rs = $this->db->query($sql)->result_array();

        return $rs;
    }

    public function get_sy(){
        $ym = array(
            0=>date("Y-m",strtotime("-5 months")),
            1=>date("Y-m",strtotime("-4 months")),
            2=>date("Y-m",strtotime("-3 months")),
            3=>date("Y-m",strtotime("-2 months")),
            4=>date("Y-m",strtotime("-1 months")),
            5=>date("Y-m")
        );
        $return_val = array();
        $ym_5 = date("Y-m",strtotime("-5 months"));
        $sql = "select sum(money) allmoney,DATE_FORMAT(cdate,'%Y-%m') ymdate
from money_log
where type in (2,3,4,5,6,7,8,9) AND DATE_FORMAT(cdate,'%Y-%m') >= {$ym_5}
GROUP BY DATE_FORMAT(cdate,'%Y-%m')";
        $rs = $this->db->query($sql)->result_array();

        $sql2 = "select sum((a.total*(REPLACE(REPLACE(REPLACE(b.percent, '24', 24),'12',12),'6',6))/100)) alltotal,DATE_FORMAT(a.sdate,'%Y-%m') ymdate
from `order` a
LEFT JOIN shop b on a.shop_id = b.id
where a.status = 3 AND DATE_FORMAT(a.sdate,'%Y-%m') >= {$ym_5}
GROUP BY DATE_FORMAT(a.sdate,'%Y-%m')";
        $rs2 = $this->db->query($sql2)->result_array();
        foreach($ym as $val){
            $sr_ = 0;
            $zc_ = 0;
            foreach($rs as $v1){
                if($v1['ymdate'] == $val){
                    $zc_ = $v1['allmoney'];
                }
            }
            foreach($rs2 as $v2){
                if($v2['ymdate'] == $val){
                    $sr_ = $v2['alltotal'];
                }
            }
            $return_val[] = array(
                'date'=>$val,
                'val'=>$sr_-$zc_
            );
        }
       // die(var_dump($return_val));
        return $return_val;
    }

    public function ywy(){
        $ysday = date("Y-m-d",strtotime("-1 day"));
        $sql = "select SUM(a.price) allprice,c.parent_id,d.rel_name from order_list a
left join users c on a.uid = c.id
left join `order` b on b.id = a.oid
left join users d on d.id = c.parent_id
where a.`status`=3
and DATE_FORMAT(b.adate,'%Y-%m-%d') = '{$ysday}'
GROUP BY c.parent_id order by allprice desc limit 5";
        $rs = $this->db->query($sql)->result_array();
        return $rs;
    }

    public function index_data(){
        $data = array();
        $data['users_num']=$this->db->select('count(1) num')->from('users')->get()->row()->num;
        $data['shop_num']=$this->db->select('count(1) num')->from('shop')->where('status',2)->get()->row()->num;
        $data['ljxf_6']=$this->db->select('sum(total) alltotal')->from('shop')->where('percent =','6')->get()->row()->alltotal;
       // die(var_dump($this->db->last_query()));
        $data['ljxf_12']=$this->db->select('sum(total) alltotal')->from('shop')->where('percent =','12')->get()->row()->alltotal;
        $data['ljxf_24']=$this->db->select('sum(total) alltotal')->from('shop')->where('percent','24')->get()->row()->alltotal;
        $data['sunflower_user_num_6']=$this->db->select('count(1) num')->from('sunflower')->where('status',1)->where('percent','6')->get()->row()->num;
        $data['sunflower_user_num_12']=$this->db->select('count(1) num')->from('sunflower')->where('status',1)->where('percent','12')->get()->row()->num;
        $data['sunflower_user_num_24']=$this->db->select('count(1) num')->from('sunflower')->where('status',1)->where('percent','24')->get()->row()->num;
        $data['sunflower_shop_num_6']=$this->db->select('count(1) num')->from('sunflower_shop')->where('status',1)->where('percent','6')->get()->row()->num;
        $data['sunflower_shop_num_12']=$this->db->select('count(1) num')->from('sunflower_shop')->where('status',1)->where('percent','12')->get()->row()->num;
        $data['sunflower_shop_num_24']=$this->db->select('count(1) num')->from('sunflower_shop')->where('status',1)->where('percent','24')->get()->row()->num;

        $data['order_pay_total_3_6']=$this->db->select("sum((a.total*(REPLACE(REPLACE(REPLACE(b.percent, '24', 24),'12',12),'6',6))/100)) alltotal",false)
            ->from('order a')
            ->join('shop b','a.shop_id = b.id','left')
            ->where('a.status',3)->where('b.percent','6')->get()->row()->alltotal;
        $data['order_pay_total_3_12']=$this->db->select("sum((a.total*(REPLACE(REPLACE(REPLACE(b.percent, '24', 24),'12',12),'6',6))/100)) alltotal",false)
            ->from('order a')
            ->join('shop b','a.shop_id = b.id','left')
            ->where('a.status',3)->where('b.percent','12')->get()->row()->alltotal;
        $data['order_pay_total_3_24']=$this->db->select("sum((a.total*(REPLACE(REPLACE(REPLACE(b.percent, '24', 24),'12',12),'6',6))/100)) alltotal",false)
            ->from('order a')
            ->join('shop b','a.shop_id = b.id','left')
            ->where('a.status',3)->where('b.percent','24')->get()->row()->alltotal;

        $data['order_pay_total_2_6']=$this->db->select("sum((a.total*(REPLACE(REPLACE(REPLACE(b.percent, '24', 24),'12',12),'6',6))/100)) alltotal",false)
            ->from('order a')
            ->join('shop b','a.shop_id = b.id','left')
            ->where('a.status',2)->where('b.percent','6')->get()->row()->alltotal;
        $data['order_pay_total_2_12']=$this->db->select("sum((a.total*(REPLACE(REPLACE(REPLACE(b.percent, '24', 24),'12',12),'6',6))/100)) alltotal",false)
            ->from('order a')
            ->join('shop b','a.shop_id = b.id','left')
            ->where('a.status',2)->where('b.percent','12')->get()->row()->alltotal;
        $data['order_pay_total_2_24']=$this->db->select("sum((a.total*(REPLACE(REPLACE(REPLACE(b.percent, '24', 24),'12',12),'6',6))/100)) alltotal",false)
            ->from('order a')
            ->join('shop b','a.shop_id = b.id','left')
            ->where('a.status',2)->where('b.percent','24')->get()->row()->alltotal;
       // var_dump($this->db->last_query());
        $data['withdraw_total_2']=$this->db->select('sum(money) alltotal')->from('withdraw')->where('status',2)->get()->row()->alltotal;
        $data['withdraw_total_1']=$this->db->select('sum(money) alltotal')->from('withdraw')->where('status',1)->get()->row()->alltotal;
        return $data;
    }

    public function update_password(){
        $user_info = $this->session->userdata('user_info');
        if($user_info['pwd'] != sha1($this->input->post('old_pass'))) {
            return -1;
        }
        $res = $this->db->where('id',$user_info['id'])->update('admin',array('pwd'=>sha1($this->input->post('password'))));
        if($res){
            return 1;
        }else{
            return -1;
        }
    }

    //前一天 商家营业额 前五
    //这个函数暂时不用
    public function shop_yey(){
        $ysday = date("Y-m-d",strtotime("-1 day"));
        $sql = "select sum(a.total) alltotal,shop_id,shop_name from `order` a
left join shop b on a.shop_id = b.id
where a.`status` = 3
and DATE_FORMAT(a.adate,'%Y-%m-%d') = '{$ysday}'
GROUP BY a.shop_id ORDER BY alltotal desc LIMIT 5";
        $rs = $this->db->query($sql)->result_array();
        return $rs;
    }

    //当月 下级商家营业额 前五
    public function shop2_yey(){
        $ysday = date("Y-m");
        $sql = "select sum(a.total) alltotal,c.id,c.rel_name from `order` a
left join shop b on a.shop_id = b.id
left join users c on c.id = b.parent_uid
where a.`status` = 3
and DATE_FORMAT(a.adate,'%Y-%m') = '{$ysday}'
GROUP BY b.parent_uid ORDER BY alltotal desc LIMIT 5";
        $rs = $this->db->query($sql)->result_array();
        return $rs;
    }

    //前一天 商家营业额 前五
    public function shop_month_yey(){
        $ysday = date("Y-m-d",strtotime("-1 day"));
        $sql = "select sum(a.total) alltotal,shop_id,shop_name from `order` a
left join shop b on a.shop_id = b.id
where a.`status` = 3
and DATE_FORMAT(a.adate,'%Y-%m-%d') = '{$ysday}'
GROUP BY a.shop_id ORDER BY alltotal desc LIMIT 5";
        $rs = $this->db->query($sql)->result_array();
        return $rs;
    }
 
}
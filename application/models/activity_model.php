<?php
/**
 * Created by PhpStorm.
 * User: bin.shen
 * Date: 5/7/16
 * Time: 15:03
 */

class Activity_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    public function get_company_list($id = NULL) {
        if(empty($id)) {
            return $this->db->get('company')->result_array();
        }
        return $this->db->get_where('company', array('id' => $id))->result_array();
    }

    public function get_subsidiary_list($company_id, $subsidiary_id=NULL) {
        if(empty($subsidiary_id)) {
            return $this->db->get_where('subsidiary', array('company_id' => $company_id))->result_array();
        } else {
            return $this->db->where_in('id', $subsidiary_id)->from('subsidiary')->get()->result_array();
        }
    }

    public function get_subsidiary_user_list($subsidiary_id) {
        return $this->db->get_where('user', array('subsidiary_id' => $subsidiary_id))->result_array();
    }

    public function list_activity($page, $status, $user_id=NULL, $subsidiary_id=NULL, $company_id=NULL,$flag=null) {

        // 每页显示的记录条数，默认20条
        $numPerPage = $this->input->post('numPerPage') ? $this->input->post('numPerPage') : 10;
        $pageNum = $this->input->post('pageNum') ? $this->input->post('pageNum') : $page;

        //获得总记录数
        $this->db->select('count(1) as num');
        $this->db->from('activity a');
        $this->db->join('user b', 'a.user_id = b.id', 'inner');
        $this->db->join('role c','c.id = b.role_id','inner');
        $this->db->join('user_subsidiary d','d.user_id = b.id','inner');
        $this->db->where_in('a.status', $status);
        if($this->input->POST('company')) {
            $this->db->where('b.company_id', $this->input->POST('company'));
        }
        if($this->input->POST('subsidiary')) {
            $this->db->where_in('d.subsidiary_id', $this->input->POST('subsidiary'));
        }
        if($this->input->POST('user')) {
            $this->db->where('b.id', $this->input->POST('user'));
        }
        if($this->input->POST('start_date')) {
            $this->db->where('a.date >=', $this->input->POST('start_date'));
        }else{
            if($flag==1){
                //只有在我的审核中,才不会显示状态为1的数据
                $this->db->where('a.date >=', date('Ymd', strtotime("-1 day")));
            }
        }
        if($this->input->POST('end_date')) {
            $this->db->where('a.date <=', $this->input->POST('end_date'));
        }else{
            if($flag==1){
                //只有在我的审核中,才不会显示状态为1的数据
                $this->db->where('a.date <=', date('Ymd', strtotime("-1 day")));
            }
        }
        if(!empty($user_id)) {
            $this->db->where('a.user_id', $user_id);
        }
        if(!empty($subsidiary_id)) {
            $this->db->where_in('d.subsidiary_id', $subsidiary_id);
        }
        if(!empty($company_id)) {
            $this->db->where('b.company_id', $company_id);
        }
        $this->db->where('c.permission_id >=', 5);
        $this->db->where('b.flag',1);
        $rs_total = $this->db->get()->row();
        //总记录数
        $data['countPage'] = $rs_total->num;

       //die(var_dump($this->db->last_query()));
        //list
        $this->db->select('a.*, b.rel_name AS u_name');
        $this->db->select('t1.name AS t1n, t2.name AS t2n, t3.name AS t3n, t4.name AS t4n, t5.name AS t5n');
        $this->db->select('t6.name AS t6n, t7.name AS t7n, t8.name AS t8n, t9.name AS t9n, t10.name AS t10n');
        $this->db->select('t11.name AS t11n, t12.name AS t12n, t13.name AS t13n, t14.name AS t14n, t15.name AS t15n');
        $this->db->select('t1.unit AS t1u, t2.unit AS t2u, t3.unit AS t3u, t4.unit AS t4u, t5.unit AS t5u');
        $this->db->select('t6.unit AS t6u, t7.unit AS t7u, t8.unit AS t8u, t9.unit AS t9u, t10.unit AS t10u');
        $this->db->select('t11.unit AS t11u, t12.unit AS t12u, t13.unit AS t13u, t14.unit AS t14u, t15.unit AS t15u');
        $this->db->select('t1.icon AS t1c, t2.icon AS t2c, t3.icon AS t3c, t4.icon AS t4c, t5.icon AS t5c');
        $this->db->select('t6.icon AS t6c, t7.icon AS t7c, t8.icon AS t8c, t9.icon AS t9c, t10.icon AS t10c');
        $this->db->select('t11.icon AS t11c, t12.icon AS t12c, t13.icon AS t13c, t14.icon AS t14c, t15.icon AS t15c');
        $this->db->select('ROUND(a.a1s*a1n+a.a2s*a2n+a.a3s*a3n+a.a4s*a4n+a.a5s*a5n, 1) AS a1t', false);
        $this->db->select('ROUND(a.b1s*b1n+a.b2s*b2n+a.b3s*b3n+a.b4s*b4n+a.b5s*b5n, 1) AS b1t', false);
        $this->db->select('ROUND(a.c1s*c1n+a.c2s*c2n+a.c3s*c3n+a.c4s*c4n+a.c5s*c5n, 1) AS c1t', false);
        $this->db->select('DATE_FORMAT(a.date, "%Y%m%d") AS date2', false);
        $this->db->select('DATE_FORMAT(a.cdate, "%Y%m%d") AS date3', false);
        $this->db->from('activity a');
        $this->db->join('user b', 'a.user_id = b.id', 'inner');
        $this->db->join('user_subsidiary d','d.user_id = b.id','inner');
        $this->db->join('activity_type t1', 'a.a1 = t1.id', 'left');
        $this->db->join('activity_type t2', 'a.a2 = t2.id', 'left');
        $this->db->join('activity_type t3', 'a.a3 = t3.id', 'left');
        $this->db->join('activity_type t4', 'a.a4 = t4.id', 'left');
        $this->db->join('activity_type t5', 'a.a5 = t5.id', 'left');
        $this->db->join('activity_type t6', 'a.b1 = t6.id', 'left');
        $this->db->join('activity_type t7', 'a.b2 = t7.id', 'left');
        $this->db->join('activity_type t8', 'a.b3 = t8.id', 'left');
        $this->db->join('activity_type t9', 'a.b4 = t9.id', 'left');
        $this->db->join('activity_type t10','a.b5 = t10.id', 'left');
        $this->db->join('activity_type t11','a.c1 = t11.id', 'left');
        $this->db->join('activity_type t12','a.c2 = t12.id', 'left');
        $this->db->join('activity_type t13','a.c3 = t13.id', 'left');
        $this->db->join('activity_type t14','a.c4 = t14.id', 'left');
        $this->db->join('activity_type t15','a.c5 = t15.id', 'left');
        $this->db->join('role c','c.id = b.role_id','inner');
        $this->db->where_in('a.status', $status);
        if($this->input->POST('company')) {
            $this->db->where('b.company_id', $this->input->POST('company'));
        }
        if($this->input->POST('subsidiary')) {
            $this->db->where_in('d.subsidiary_id', $this->input->POST('subsidiary'));
        }
        if($this->input->POST('user')) {
            $this->db->where('b.id', $this->input->POST('user'));
        }
        if($this->input->POST('start_date')) {
            $this->db->where('a.date >=', $this->input->POST('start_date'));
        }else{
            if($flag==1){
                //只有在我的审核中,才不会显示状态为1的数据
                $this->db->where('a.date >=', date('Ymd', strtotime("-1 day")));
            }
        }
        if($this->input->POST('end_date')) {
            $this->db->where('a.date <=', $this->input->POST('end_date'));
        }else{
            if($flag==1){
                //只有在我的审核中,才不会显示状态为1的数据
                $this->db->where('a.date <=', date('Ymd', strtotime("-1 day")));
            }
        }
        if(!empty($user_id)) {
            $this->db->where('a.user_id', $user_id);
        }
        if(!empty($subsidiary_id)) {
            $this->db->where_in('d.subsidiary_id', $subsidiary_id);
        }
        if(!empty($company_id)) {
            $this->db->where('b.company_id', $company_id);
        }
        if(!in_array(1,$status)){
            //只有在我的审核中,才不会显示状态为1的数据
            //$this->db->where('a.date <=',date('Ymd', strtotime("-1 day")));
        }
        $this->db->where('c.permission_id >=', 5);
        $this->db->where('b.flag',1);
        $this->db->limit($numPerPage, ($pageNum - 1) * $numPerPage );
        //$this->db->order_by($this->input->post('orderField') ? $this->input->post('orderField') : 'a.date', $this->input->post('orderDirection') ? $this->input->post('orderDirection') : 'desc');
        $this->db->order_by('a.date', 'desc');
        $this->db->order_by('a.user_id', 'desc');

        $data['res_list'] = $this->db->get()->result();

        $data['pageNum'] = $pageNum;
        $data['numPerPage'] = $numPerPage;
        return $data;
    }

    public function check_activity() {
        $user_id = $this->session->userdata('login_user_id');
        $date = $this->input->post('date');
        return $this->db->get_where('activity', array('user_id' => $user_id, 'date' => $date))->result_array();
    }

    public function add_activity() {
        $data = array(
            'user_id' => $this->session->userdata('login_user_id'),
            'date' => $this->input->post('date'),
            'status' => 1,
            'a1' => $this->input->post('a1'),
            'a1s' => $this->input->post('a1s'),
            'a1n' => $this->input->post('a1n'),
            'a1m' => $this->input->post('a1m'),
            'a2' => $this->input->post('a2'),
            'a2s' => $this->input->post('a2s'),
            'a2n' => $this->input->post('a2n'),
            'a2m' => $this->input->post('a2m'),
            'a3' => $this->input->post('a3'),
            'a3s' => $this->input->post('a3s'),
            'a3n' => $this->input->post('a3n'),
            'a3m' => $this->input->post('a3m'),
            'a4' => $this->input->post('a4'),
            'a4s' => $this->input->post('a4s'),
            'a4n' => $this->input->post('a4n'),
            'a4m' => $this->input->post('a4m'),
            'a5' => $this->input->post('a5'),
            'a5s' => $this->input->post('a5s'),
            'a5n' => $this->input->post('a5n'),
            'a5m' => $this->input->post('a5m')
        );
        $this->db->trans_start();//--------开始事务

        if($this->input->post('id')){//修改
            $this->db->where('id', $this->input->post('id'));
            $this->db->update('activity', $data);
        } else {
            $data['cdate']=date('Y-m-d H:i:s',time());
            $this->db->insert('activity', $data);
        }
        $this->db->trans_complete();//------结束事务
        if ($this->db->trans_status() === FALSE) {
            return -1;
        } else {
            return 1;
        }
    }

    public function assess_activity() {
        if(!$this->input->post('id')){
            return -1;
        }

        $data = array(
            'status' => 2,
            'b1' => $this->input->post('b1'),
            'b1s' => $this->input->post('b1s'),
            'b1n' => $this->input->post('b1n'),
            'b1m' => $this->input->post('b1m'),
            'b2' => $this->input->post('b2'),
            'b2s' => $this->input->post('b2s'),
            'b2n' => $this->input->post('b2n'),
            'b2m' => $this->input->post('b2m'),
            'b3' => $this->input->post('b3'),
            'b3s' => $this->input->post('b3s'),
            'b3n' => $this->input->post('b3n'),
            'b3m' => $this->input->post('b3m'),
            'b4' => $this->input->post('b4'),
            'b4s' => $this->input->post('b4s'),
            'b4n' => $this->input->post('b4n'),
            'b4m' => $this->input->post('b4m'),
            'b5' => $this->input->post('b5'),
            'b5s' => $this->input->post('b5s'),
            'b5n' => $this->input->post('b5n'),
            'b5m' => $this->input->post('b5m')
        );
        $this->db->trans_start();//--------开始事务

        $this->db->where('id', $this->input->post('id'));
        $this->db->update('activity', $data);
        $this->db->trans_complete();//------结束事务
        if ($this->db->trans_status() === FALSE) {
            return -1;
        } else {
            return 1;
        }
    }

    public function review_activity() {
        if(!$this->input->post('id')){
            return -1;
        }

        $data = array(
            'status' => 3,
            'c1' => $this->input->post('c1'),
            'c1s' => $this->input->post('c1s'),
            'c1n' => $this->input->post('c1n'),
            'c1m' => $this->input->post('c1m'),
            'c2' => $this->input->post('c2'),
            'c2s' => $this->input->post('c2s'),
            'c2n' => $this->input->post('c2n'),
            'c2m' => $this->input->post('c2m'),
            'c3' => $this->input->post('c3'),
            'c3s' => $this->input->post('c3s'),
            'c3n' => $this->input->post('c3n'),
            'c3m' => $this->input->post('c3m'),
            'c4' => $this->input->post('c4'),
            'c4s' => $this->input->post('c4s'),
            'c4n' => $this->input->post('c4n'),
            'c4m' => $this->input->post('c4m'),
            'c5' => $this->input->post('c5'),
            'c5s' => $this->input->post('c5s'),
            'c5n' => $this->input->post('c5n'),
            'c5m' => $this->input->post('c5m'),
            'op' => $this->input->post('op'),
            'float' => $this->input->post('float'),
            'mark' => $this->input->post('mark'),
            'total' => $this->input->post('total')
        );
        $this->db->trans_start();//--------开始事务

        $this->db->where('id', $this->input->post('id'));
        $this->db->update('activity', $data);
        $this->db->trans_complete();//------结束事务
        if ($this->db->trans_status() === FALSE) {
            return -1;
        } else {
            return 1;
        }
    }

    public function get_activity_type_list() {
        return $this->db->where('flag',1)->get('activity_type')->result_array();
    }

    public function get_activity_by_id($id) {

        $this->db->select('a.*');
        $this->db->select('b.rel_name AS u_name');
        $this->db->select('t1.name AS t1n, t2.name AS t2n, t3.name AS t3n, t4.name AS t4n, t5.name AS t5n');
        $this->db->select('t6.name AS t6n, t7.name AS t7n, t8.name AS t8n, t9.name AS t9n, t10.name AS t10n');
        $this->db->select('t11.name AS t11n, t12.name AS t12n, t13.name AS t13n, t14.name AS t14n, t15.name AS t15n');
        $this->db->select('t1.unit AS t1u, t2.unit AS t2u, t3.unit AS t3u, t4.unit AS t4u, t5.unit AS t5u');
        $this->db->select('t6.unit AS t6u, t7.unit AS t7u, t8.unit AS t8u, t9.unit AS t9u, t10.unit AS t10u');
        $this->db->select('t11.unit AS t11u, t12.unit AS t12u, t13.unit AS t13u, t14.unit AS t14u, t15.unit AS t15u');
        $this->db->select('t1.icon AS t1c, t2.icon AS t2c, t3.icon AS t3c, t4.icon AS t4c, t5.icon AS t5c');
        $this->db->select('t6.icon AS t6c, t7.icon AS t7c, t8.icon AS t8c, t9.icon AS t9c, t10.icon AS t10c');
        $this->db->select('t11.icon AS t11c, t12.icon AS t12c, t13.icon AS t13c, t14.icon AS t14c, t15.icon AS t15c');
        $this->db->select('ROUND(a.a1s*a1n+a.a2s*a2n+a.a3s*a3n+a.a4s*a4n+a.a5s*a5n, 1) AS a1t', false);
        $this->db->select('ROUND(a.b1s*b1n+a.b2s*b2n+a.b3s*b3n+a.b4s*b4n+a.b5s*b5n, 1) AS b1t', false);
        $this->db->select('ROUND(a.c1s*c1n+a.c2s*c2n+a.c3s*c3n+a.c4s*c4n+a.c5s*c5n, 1) AS c1t', false);
        $this->db->from('activity a');
        $this->db->join('user b', 'a.user_id = b.id', 'inner');
        $this->db->join('activity_type t1', 'a.a1 = t1.id', 'left');
        $this->db->join('activity_type t2', 'a.a2 = t2.id', 'left');
        $this->db->join('activity_type t3', 'a.a3 = t3.id', 'left');
        $this->db->join('activity_type t4', 'a.a4 = t4.id', 'left');
        $this->db->join('activity_type t5', 'a.a5 = t5.id', 'left');
        $this->db->join('activity_type t6', 'a.b1 = t6.id', 'left');
        $this->db->join('activity_type t7', 'a.b2 = t7.id', 'left');
        $this->db->join('activity_type t8', 'a.b3 = t8.id', 'left');
        $this->db->join('activity_type t9', 'a.b4 = t9.id', 'left');
        $this->db->join('activity_type t10','a.b5 = t10.id', 'left');
        $this->db->join('activity_type t11','a.c1 = t11.id', 'left');
        $this->db->join('activity_type t12','a.c2 = t12.id', 'left');
        $this->db->join('activity_type t13','a.c3 = t13.id', 'left');
        $this->db->join('activity_type t14','a.c4 = t14.id', 'left');
        $this->db->join('activity_type t15','a.c5 = t15.id', 'left');
        $this->db->where('a.id', $id);
        return $this->db->get()->row_array();
        //return $this->db->get_where('activity', array('id' => $id))->row_array();
    }

    public function get_total_top_list($company_id, $subsidiary_id, $year, $month) {

        $this->db->select('b.id AS u_id, b.pic AS u_pic, b.rel_name AS u_name, c.name AS c_name, e.name AS s_name, SUM(a.total) AS total');
        $this->db->from('activity a');
        $this->db->join('user b', 'a.user_id = b.id', 'inner');
        $this->db->join('company c', 'b.company_id = c.id', 'left');
        $this->db->join('user_subsidiary d','d.user_id = b.id','inner');
        $this->db->join('subsidiary e', 'd.subsidiary_id = e.id', 'left');
        $this->db->where('a.status', 3);
        if(!empty($year)) {
            $this->db->where('YEAR(a.date)', $year);
        }
        if(!empty($month)) {
            $this->db->where('MONTH(a.date)', $month);
        }
        if(!empty($company_id)) {
            $this->db->where('b.company_id', $company_id);
        }
        if(!empty($subsidiary_id)) {
            $this->db->where_in('d.subsidiary_id', $subsidiary_id);
        }
        $this->db->where('b.flag', 1);
        $this->db->group_by('b.id');
        $this->db->order_by('total', 'desc');
        $this->db->distinct();
        return $this->db->get()->result();
    }

    public function get_top_list_by_op($op = 1, $company_id, $subsidiary_id, $year, $month) {

        $sql = "
            SELECT DISTINCT 
              b.id AS u_id,
              b.pic AS u_pic, 
              b.rel_name AS u_name, 
              c.name AS c_name, 
              e.name AS s_name,
              SUM(a.total) AS total
            FROM
              (
                SELECT user_id, date, c1s * c1n as total FROM activity WHERE status = 3 AND c1 = $op
                UNION
                SELECT user_id, date, c2s * c2n as total FROM activity WHERE status = 3 AND c2 = $op
                UNION
                SELECT user_id, date, c3s * c3n as total FROM activity WHERE status = 3 AND c3 = $op
                UNION
                SELECT user_id, date, c4s * c4n as total FROM activity WHERE status = 3 AND c4 = $op
                UNION
                SELECT user_id, date, c5s * c5n as total FROM activity WHERE status = 3 AND c5 = $op
            ) AS a
            JOIN user b ON b.id = a.user_id
            LEFT JOIN company c ON b.company_id = c.id
            INNER JOIN user_subsidiary d ON d.user_id = b.id
            INNER join subsidiary e ON e.id = d.subsidiary_id
            WHERE b.flag = 1
        ";
        if(!empty($year)) {
            $sql .= " AND YEAR(a.date) = " . $year;
        }
        if(!empty($month)) {
            $sql .= " AND MONTH(a.date) = " . $month;
        }
        if(!empty($company_id)) {
            $sql .= " AND b.company_id = " . $company_id;
        }
        if(!empty($subsidiary_id)) {
            $string_in='';
            if(is_array($subsidiary_id)){
                foreach($subsidiary_id as $key=>$item){
                    if($key==0){
                        $string_in.=$item;
                    }else{
                        $string_in.=','.$item;
                    }

                }
            }else{
                $string_in = $subsidiary_id;
            }

            $sql .= " AND d.subsidiary_id in (".$string_in.")";
        }
        $sql .= " GROUP BY b.id ORDER BY total DESC ";
        return $this->db->query($sql)->result();
    }

    public function list_onplan($page, $subsidiary_id=NULL, $company_id=NULL,$flag=null) {

        // 每页显示的记录条数，默认20条
        $numPerPage = $this->input->post('numPerPage') ? $this->input->post('numPerPage') : 10;
        $pageNum = $this->input->post('pageNum') ? $this->input->post('pageNum') : $page;

        //获得总记录数

        $select_date='';
        if($this->input->POST('date')) {
            $select_date = $this->input->post('date');
        }else{
            if($flag==1){
                $select_date = date('Y-m-d', strtotime("-1 day"));
            }
        }
        $this->db->select('count(1) as num');
        $this->db->from('user a');
        $this->db->join('activity b', "a.id = b.user_id and b.date = '{$select_date}'", 'left');
        $this->db->join('role c','c.id = a.role_id','inner');
        $this->db->join('user_subsidiary d','d.user_id = a.id','inner');
        if($this->input->POST('company')) {
            $this->db->where('a.company_id', $this->input->POST('company'));
        }
        if($this->input->POST('subsidiary')) {
            $this->db->where_in('d.subsidiary_id', $this->input->POST('subsidiary'));
        }
        if($this->input->POST('user')) {
            $this->db->where('a.id', $this->input->POST('user'));
        }

        if(!empty($subsidiary_id)) {
            $this->db->where_in('d.subsidiary_id', $subsidiary_id);
        }
        if(!empty($company_id)) {
            $this->db->where('a.company_id', $company_id);
        }
        $this->db->where('a.flag',1);
        $this->db->where('b.id is null');
        $this->db->where('c.permission_id >=', 5);
        $rs_total_noplan = $this->db->get()->row();

        //总记录数
        $data['countPage'] =  $rs_total_noplan->num;
        //die(var_dump($this->db->last_query()));
        //list


        $this->db->select('a.rel_name');
        $this->db->from('user a');
        $this->db->join('activity b', "a.id = b.user_id and b.date = '{$select_date}'", 'left');
        $this->db->join('role c','c.id = a.role_id','inner');
        $this->db->join('user_subsidiary d','d.user_id = a.id','inner');
        if($this->input->POST('company')) {
            $this->db->where('a.company_id', $this->input->POST('company'));
        }
        if($this->input->POST('subsidiary')) {
            $this->db->where_in('d.subsidiary_id', $this->input->POST('subsidiary'));
        }
        if($this->input->POST('user')) {
            $this->db->where('a.id', $this->input->POST('user'));
        }

        if(!empty($subsidiary_id)) {
            $this->db->where_in('d.subsidiary_id', $subsidiary_id);
        }
        if(!empty($company_id)) {
            $this->db->where('a.company_id', $company_id);
        }
        $this->db->where('a.flag',1);
        $this->db->where('b.id is null');
        $this->db->where('c.permission_id >=', 5);
        $this->db->limit($numPerPage, ($pageNum - 1) * $numPerPage );
        //$this->db->order_by($this->input->post('orderField') ? $this->input->post('orderField') : 'a.date', $this->input->post('orderDirection') ? $this->input->post('orderDirection') : 'desc');


        $data['res_list'] = $this->db->get()->result();
       // die(var_dump($this->db->last_query()));
        /*echo var_dump($data['res_list']);
        echo '</br>';
        die(var_dump($this->db->query()));*/
        $data['pageNum'] = $pageNum;
        $data['numPerPage'] = $numPerPage;
        return $data;
    }

    public function get_subsidiary_user_list_7($subsidiary_id) {

        $this->db->select('a.*')->from('user a');
        $this->db->join('role b','b.id = a.role_id','inner');
        $this->db->join('user_subsidiary c','c.user_id = a.id','inner');
        $this->db->where_in('c.subsidiary_id',$subsidiary_id);
        $this->db->where(array(
            'b.permission_id >'=>'4',
            'a.flag'=>1
        ));
        return $this->db->get()->result_array();
    }
}
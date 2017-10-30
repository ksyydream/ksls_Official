<?php
/**
 * Created by PhpStorm.
 * User: bin.shen
 * Date: 5/26/16
 * Time: 12:59
 */

class Appointment_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    public function get_time_frame_list() {
        return $this->db->get('time_frame')->result();
    }

    public function get_room_list() {
        return $this->db->get('room')->result();
    }

    public function book_room($start, $end, $user_id = NULL) {
        $sql = "
            SELECT * FROM appointment WHERE date >= '$start' AND date <= '$end'
        ";
        if(!empty($user_id)) {
            $sql .= " AND user_id = $user_id";
        }
        return $this->db->query($sql)->result();
    }

    public function get_appointment_info($date, $tf_id) {
        $sql = "
            SELECT 
              a.id, 
              a.name AS room_name,
              b.user_id
            FROM room a
            LEFT JOIN appointment b 
            ON a.id = b.room_id
            AND b.date = '$date'
            AND b.time_frame_id = $tf_id
            WHERE a.open = 1
        ";
        return $this->db->query($sql)->result();
    }

    public function get_appointment_detail($date, $tf_id) {
        $sql = "
            SELECT
              a.name AS room_name,
              a.clz,
              b.date,
              c.rel_name AS user_name,
              c.tel,
              d.name AS company_name,
              e.name AS subsidiary_name
            FROM room a
            LEFT JOIN appointment b ON a.id = b.room_id AND b.date = '$date' AND b.time_frame_id = $tf_id
            LEFT JOIN user c ON b.user_id = c.id
            LEFT JOIN company d ON c.company_id = d.id
            LEFT JOIN subsidiary e ON c.subsidiary_id = e.id
            WHERE a.open = 1
        ";
        return $this->db->query($sql)->result();
    }

    public function save_appointment() {

        $this->db->where('date', $this->input->post('date'));
        $this->db->where('time_frame_id', $this->input->post('time_frame_id'));
        $this->db->where('room_id', $this->input->post('room_id'));
        $data = $this->db->get('appointment')->row();
        if(!empty($data)) {
            return -2;
        }
        $data = array(
            'user_id' => $this->input->post('user_id'),
            'date' => $this->input->post('date'),
            'time_frame_id' => $this->input->post('time_frame_id'),
            'room_id' => $this->input->post('room_id')
        );
        $this->db->trans_start();//--------开始事务
        $this->db->insert('appointment', $data);
        $res_sum = $this->change_sum($this->session->userdata('login_company_id'),
            $this->config->item('appointment_sum'),
            2,
            $this->config->item('appointment_sum_name'),
            'app',
            $this->db->insert_id()
            );
        if($res_sum != 1){
            return -3;//金额不足
        }
        $this->db->trans_complete();//------结束事务

        if ($this->db->trans_status() === FALSE) {
            return -1;
        } else {
            $data = array(
                'first' => array(
                    'value' => "恭喜！您预约已成功啦！",
                    'color' => '#FF0000'
                ),
                'keyword1' => array(
                    'value' => '预约场地',
                    'color' => '#FF0000'
                ),
                'keyword2' => array(
                    'value' => '预约成功',
                    'color' => '#FF0000'
                ),
                'keyword3' => array(
                    'value' => date("Y-m-d H:i:s"),
                    'color' => '#FF0000'
                ),
                'remark' => array(
                    'value' => '预约成功,扣款'.$this->config->item('appointment_sum').'元',
                    'color' => '#FF0000'
                )
            );
            $this->wxpost($this->config->item('WX_YY'),$data,$this->session->userdata('login_user_id'));
            return 1;
        }
    }

    public function check_room($date, $user_id) {
        $this->db->where('date', $date);
        $this->db->where('user_id', $user_id);
        return $this->db->get('appointment')->result();
    }

    public function unbook_room($date, $tf_id, $user_id) {

        $row = $this->db->select()->from('appointment')->where(array(
            'user_id'=>$user_id,
            'date'=>$date,
            'time_frame_id'=>$tf_id
        ))->get()->row_array();
        if(!$row){
            return false;
        }
        $this->db->trans_start();//--------开始事务
        $this->db->where('user_id', $user_id);
        $this->db->where('date', $date);
        $this->db->where('time_frame_id', $tf_id);
        $this->db->delete('appointment');
        $res_sum = $this->change_sum($this->session->userdata('login_company_id'),
            $this->config->item('appointment_tksum'),
            1,
            $this->config->item('appointment_tksum_name'),
            'app',
            $row['id']
        );
        $this->db->trans_complete();//------结束事务

        if ($this->db->trans_status() === FALSE) {
            return -1;
        } else {
            $data = array(
                'first' => array(
                    'value' => "成功取消预约,已退款!",
                    'color' => '#FF0000'
                ),
                'reason' => array(
                    'value' => '取消预约',
                    'color' => '#FF0000'
                ),
                'refund' => array(
                    'value' => $this->config->item('appointment_tksum').'元',
                    'color' => '#FF0000'
                ),
                'remark' => array(
                    'value' => '取消预约成功,退款'.$this->config->item('appointment_tksum').'元至公司账户',
                    'color' => '#FF0000'
                )
            );
            $this->wxpost($this->config->item('WX_TK'),$data,$this->session->userdata('login_user_id'));
            return 1;
        }
    }

    public function get_time_frame($id) {
        $this->db->where('id', $id);
        return $this->db->get('time_frame')->row();
    }

}
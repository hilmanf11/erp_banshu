<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Notifications extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
    }

    public function notificationCount()
    {
        $this->db->select('a.*, b.name as fullname, b.avatar');
        $this->db->from('notifications a');
        $this->db->join('users b', 'a.users_id_to = b.username');
        $this->db->where('b.username', $this->session->username);
        $this->db->group_by('a.table_name');
        $this->db->order_by('a.created_date', 'DESC');
        $totalRows = $this->db->count_all_results('', false);

        if ($totalRows > 0) {
            echo '<span class="badge">' . $totalRows . '</span>';
        } else {
            echo '';
        }
    }

    public function notificationList()
    {
        $this->db->select('a.*, c.name as fullname, c.avatar');
        $this->db->from('notifications a');
        $this->db->join('users b', 'a.users_id_to = b.username');
        $this->db->join('users c', 'a.users_id_from = c.username');
        $this->db->where('b.username', $this->session->username);
        $this->db->group_by('a.table_name');
        $this->db->order_by('a.created_date', 'DESC');
        $records = $this->db->get()->result_object();

        if (count($records) > 0) {
            foreach ($records as $record) {
                if ($record->avatar == "") {
                    $avatar = base_url('assets/image/users/default.png');
                } else {
                    $avatar = $record->avatar;
                }

                $link = "notificationDetail('" . $record->name . "','" . $record->users_id_from . "','" . $record->users_id_to . "','" . $record->table_name . "')";
                echo '<li class="list-isi">
                    <a onclick="' . $link . '">
                        <table style="width: 100%;">
                            <tr>
                                <td>
                                    <div class="icon-container">
                                        <img src="' . $avatar . '" class="user-online" />
                                        <div class="status-circle"></div>
                                    </div>
                                </td>
                                <td style="padding-left: 10px;">
                                    <b>' . $record->fullname . '</b><br>
                                    <small>' . $record->description . '</small>
                                </td>
                            </tr>
                        </table>
                    </a>
                </li>';
            }
        } else {
            echo '  <div class="alert alert-info" role="alert">
                        Notification Not Found
                    </div>';
        }
    }

    public function getData($name, $user_id_from, $user_id_to){
        if (empty($this->session->username)) {
            redirect('error_session');
        } else {
            $data['user_id_from'] = base64_decode($user_id_from);
            $data['user_id_to'] = base64_decode($user_id_to);
            $data['name'] = base64_decode($name);
            $data['table'] = "Notification $name Data";
            $this->load->view('template/header', $data);
            $this->load->view('approval/notifications');
        }
    }

    public function datatables($name, $user_id_from, $user_id_to){
        $name = base64_decode($name);
        $user_id_from = base64_decode($user_id_from);
        $user_id_to = base64_decode($user_id_to);
        
        $this->db->select('*');
        $this->db->from('notifications');
        $this->db->where('name', $name);
        $this->db->where('users_id_from', $user_id_from);
        $this->db->where('users_id_to', $user_id_to);
        $this->db->order_by('id', 'ASC');
        $records = $this->db->get()->result_array();

        die(json_encode($records));
    }

    public function datatableDetails(){
        $id = $this->input->post('id');

        $this->db->select('*');
        $this->db->from('notifications');
        $this->db->where("id", $id);
        $record = $this->db->get()->row();
        $descriptions = json_decode($record->logs);

        $html = '<html><head><title>Print Data</title></head><style>body {font-family: Arial, Helvetica, sans-serif;}#customers {border-collapse: collapse;width: 100%;font-size: 12px;}#customers td, #customers th {border: 1px solid #ddd;padding: 2px;}#customers tr:nth-child(even){background-color: #f2f2f2;}#customers tr:hover {background-color: #ddd;}#customers th {padding-top: 2px;padding-bottom: 2px;text-align: left;color: black;}</style><body>
                <table id="customers" border="1" style="width: 100%;">
                <tr>
                    <th>Title</th>
                    <th>Description</th>
                </tr>';
        foreach ($descriptions as $description => $value) {
            if(!in_array($description, ["approved_data","approved","approved_to","approved_by","approved_date"])){
                $html .= '  <tr>
                                <th>' . strtoupper(str_replace("_", " ", $description)) . '</th>
                                <th>' . $value . '</th>
                            </tr>';
            }
        }
        $html .= '</table>';

        die($html);
    }

    public function delete()
    {
        $data = $this->input->post();
        $logs = $this->db->delete('notifications', $data);
        echo $logs;
    }

    public function datatablesAll($menu){
        $this->db->select('*');
        $this->db->from('notifications');
        $this->db->where('table_name', $menu);
        $this->db->where('name', "Disapproved");
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1000);
        $records = $this->db->get()->result_array();

        $arr = array();
        foreach ($records as $record) {
            $description = json_decode($record['logs']);
            $item_rm = $this->crud->read("item_rm", [], ["id" => $description->item_rm_id]);
            $supplier = $this->crud->read("suppliers", [], ["id" => $description->supplier_id]);

            if($menu == "purchase_requests"){
                $arr[] = array(
                    "period" => $description->p_year.$description->p_month,
                    "revision" => $description->revision,
                    "plant" => $description->plant,
                    "request_no" => $description->request_no,
                    "request_date" => $description->request_date,
                    "item_rm_number" => @$item_rm->number,
                    "item_rm_name" => @$item_rm->name,
                    "supplier_name" => @$supplier->name,
                    "need" => $description->need,
                    "mrp" => $description->mrp,
                    "qty" => $description->qty,
                    "approved_reason" => $record['reason'],
                    "approved_by" => $record['users_id_from'],
                    "approved_date" => $record['created_date'],
                    "created_by" => $description->created_by,
                    "created_date" => $description->created_date,
                    "updated_by" => $description->updated_by,
                    "updated_date" => $description->updated_date,
                );
            }

            if($menu == "purchase_orders"){
                $arr[] = array(
                    "period" => $description->p_year.$description->p_month,
                    "revision" => $description->revision,
                    "plant" => $description->plant,
                    "request_no" => $description->request_no,
                    "po_no" => $description->po_no,
                    "po_date" => $description->po_date,
                    "item_rm_number" => @$item_rm->number,
                    "item_rm_name" => @$item_rm->name,
                    "supplier_name" => @$supplier->name,
                    "price" => $description->price,
                    "total" => $description->total,
                    "qty" => $description->qty,
                    "currency" => $description->currency,
                    "remarks" => $description->remarks,
                    "approved_reason" => $record['reason'],
                    "approved_by" => $record['users_id_from'],
                    "approved_date" => $record['created_date'],
                    "created_by" => $description->created_by,
                    "created_date" => $description->created_date,
                    "updated_by" => $description->updated_by,
                    "updated_date" => $description->updated_date,
                );
            }
        }

        die(json_encode($arr));

    }
}

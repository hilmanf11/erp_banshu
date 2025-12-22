<?php
header('Content-Type: application/json; charset=utf-8');

date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class M_approvals extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->CI = &get_instance(); // Tambahkan ini!
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('crud');
        $this->load->model('emails');
    }

    //HALAMAN UTAMA
    public function index()
    {
        show_error("Cannot Process your request");
    }

    public function approve($api_key = "")
    {
        if ($api_key == "") {
            show_error("API KEY is Empty");
        } else {
            $user = $this->crud->read("users", [], ["api_key" => $api_key]);

            if (!empty($user)) {
                $id = $this->input->post('id');
                $tablename = $this->input->post('tablename');

                $idx = $this->input->post('idx'); // cek idx untuk send notif

                $data = $this->crud->read($tablename, [], ["id" => $id]);
                // $approval = $this->crud->read('approvals', ["sub_department" => $user->sub_department, "department" => $user->department, "division" => $user->division], ["table_name" => $tablename]);
                $approval = $this->crud->read('approvals', ["status" => 0], ["table_name" => $tablename]);

                if ($data->approved == 1) {
                    $users_id = @$approval->user_approval_2;
                    $approved = 2;
                } elseif ($data->approved == 2) {
                    $users_id = @$approval->user_approval_3;
                    $approved = 3;
                } elseif ($data->approved == 3) {
                    $users_id = @$approval->user_approval_4;
                    $approved = 4;
                } elseif ($data->approved == 4) {
                    $users_id = @$approval->user_approval_5;
                    $approved = 5;
                } else {
                    $users_id = "";
                    $approved = 0;
                }

                $values = array(
                    "approved_by" => $user->username,
                    "approved_date" => date('Y-m-d H:i:s'),
                    "approved_to" => $users_id,
                    "approved" => $approved,
                );

                $send = $this->db->update($tablename, $values, ["id" => $id]);

                // SEND NOTIF TO REQUESTER
                $dataRequest = $this->crud->read('users', [], ["username" => $data->created_by]);
                //$messageForReq = "Request data " . strtoupper(str_replace("_", " ", $tablename)) . " has been APPROVED from " . $approval->created_by;
                //$this->pushNotification(@$dataRequest->token_id, @$user->name, $messageForReq, $dataRequest->id);

                // cek apakah ada approval lain
                // SEND NOTIF TO NEXT USER APPROVAL & cek index keberapa data yg dikirim dari FE ? notif hanya dikirim sekali
                if ($approved != 0 && $idx == '0') {
                    $message = "Sent a request to approve data " . strtoupper(str_replace("_", " ", $tablename));
                    $dataApproval = $this->crud->read("users", [], ["username" => $users_id]);
                    $this->pushNotification(@$dataApproval->token_id, $dataRequest->name, $message);
                }

                if ($send) {
                    echo json_encode(array("title" => "Approved", "message" => "Data Approved Successfully", "theme" => "success"));
                } else {
                    echo log_message('error', 'There is an error in your system or data');
                }
            } else {
                show_error("API KEY Cannot Found");
            }
        }
    }

    public function disapprove($api_key = "")
    {
        if ($api_key == "") {
            show_error("API KEY is Empty");
        } else {
            $user = $this->crud->read("users", [], ["api_key" => $api_key]);

            $id = $this->input->post('id');
            $tablename = $this->input->post('tablename');
            $read = $this->crud->read($tablename, [], ["id" => $id]);
            $data = json_decode(@$read->approved_data, false);

            //$dataRequest = $this->crud->read('users', [], ["username" => $read->created_by]);
            //$messageForReq = "Request data " . strtoupper(str_replace("_", " ", $tablename)) . "has been DISAPPROVED from " . $user->name;
            //$this->pushNotification(@$dataRequest->token_id, @$user->name, $messageForReq);

            /* Default */
            if (empty($data)) {
                $this->db->delete($tablename, ["id" => $id]);
                // $this->db->update($tablename, ['status' => 1], ["id" => $id]);
            } else {
                $this->db->update($tablename, $data, ["id" => $id]);
            }

            // die(json_encode(array("title" => "Disapproved", "message" => "Data Disapproved Successfully", "theme" => "success")));

            $this->db->insert("notifications", [
                "created_by" => @$user->username,
                "created_date" => date("Y-m-d H:i:s"),
                "user_id" => @$user->username,
                "table_name" => $tablename,
                "name" => "Disapprove",
                "description" => 'Data in Module ' . strtoupper(str_replace("_", " ", $tablename)) . ' has been disapproved',
                "log" => json_encode($read)
            ]);

            echo json_encode(array("title" => "Disapproved", "message" => "Data Disapproved Successfully", "theme" => "success"));
        }
    }

    public function approvalCount($api_key = "")
    {
        if ($api_key == "") {
            show_error("API KEY is Empty");
        } else {
            $user = $this->crud->read("users", [], ["api_key" => $api_key]);

            if (empty($user)) {
                show_error("API KEY Cannot Found");
            } else {
                $users = $this->crud->reads("users", [], ["approved_to" => $user->username], "", "", "", ["approved_to", "approved_by"]);
                $purchase_orders = $this->crud->reads('purchase_orders', [], ["approved_to" => $user->username], "", "", "", ["approved_to", "approved_by"]);
                $purchase_requests = $this->crud->reads('purchase_requests', [], ["approved_to" => $user->username], "", "", "", ["approved_to", "approved_by"]);

                $totalRows = (count($users) + count($purchase_orders) + count($purchase_requests));
                die(json_encode(array("total" => $totalRows)));
            }
        }
    }

    public function approvalList($api_key = "")
    {
        if ($api_key == "") {
            show_error("API KEY is Empty");
        } else {
            $user = $this->crud->read("users", [], ["api_key" => $api_key]);
            if (empty($user)) {
                show_error("API KEY Cannot Found");
            } else {
                //Users
                // $users = $this->crud->reads("users", [], ["approved_to" => $user->username], "", "", "", ["approved_to", "approved_by"]);
                $purchase_orders = $this->crud->reads('purchase_orders', [], ["approved_to" => $user->username], "", "", "", ["approved_to", "approved_by"]);
                $purchase_requests = $this->crud->reads('purchase_requests', [], ["approved_to" => $user->username], "", "", "", ["approved_to", "approved_by"]);

                $data = array();
                // foreach ($users as $user) {
                //     $name = $this->crud->read("users", [], ["username" => $user->approved_by]);

                //     if (empty($user->updated_date)) {
                //         $created_date = $user->created_date;
                //     } else {
                //         $created_date = $user->updated_date;
                //     }

                //     $data[] = array(
                //         "approved_by" => $user->approved_by,
                //         "approved_to" => $user->approved_to,
                //         "module" => "users",
                //         "name" => $name->name,
                //         "avatar" => $name->avatar,
                //         "message" => "Sent a request to approve data USER",
                //         "created_date" => $created_date,
                //     );
                // }

                foreach ($purchase_orders as $po) {
                    $name = $this->crud->read("users", [], ["username" => $po->approved_by]);

                    if (empty($po->updated_date)) {
                        $created_date = $po->created_date;
                    } else {
                        $created_date = $po->updated_date;
                    }

                    $data[] = array(
                        "approved_by" => $po->approved_by,
                        "approved_to" => $po->approved_to,
                        "module" => "purchase_orders",
                        "name" => $name->name,
                        "avatar" => $name->avatar,
                        "message" => "Sent a request to approve data PURCHASE ORDERS",
                        "created_date" => $created_date,
                    );
                }

                foreach ($purchase_requests as $pr) {
                    $name = $this->crud->read("users", [], ["username" => $pr->approved_by]);

                    if (empty($pr->updated_date)) {
                        $created_date = $pr->created_date;
                    } else {
                        $created_date = $pr->updated_date;
                    }

                    $data[] = array(
                        "approved_by" => $pr->approved_by,
                        "approved_to" => $pr->approved_to,
                        "module" => "purchase_requests",
                        "name" => $name->name,
                        "avatar" => $name->avatar,
                        "message" => "Sent a request to approve data PURCHASE REQUESTS",
                        "created_date" => $created_date,
                    );
                }

                die(json_encode(array("results" => $data)));
            }
        }
    }

    public function approvalUsers()
    {
        if ($this->input->post()) {
            $approved_to = $this->input->post('approved_to');
            $approved_by = $this->input->post('approved_by');

            //Select Query
            $this->db->select('*');
            $this->db->from('users');
            $this->db->where('approved_to', $approved_to);
            $this->db->where('approved_by', $approved_by);
            $this->db->order_by('created_date', 'desc');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            $records = $this->db->get()->result_array();
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        } else {
            show_error("Cannot Process your Request");
        }
    }

    public function approvalPurchaseOrders()
    {
        if ($this->input->post()) {
            $approved_to = $this->input->post('approved_to');
            $approved_by = $this->input->post('approved_by');

            //Select Query
            $this->db->select('a.request_no as purchase_request_no, a.po_no as purchase_order_no, a.po_date as purchase_order_date, e.name as product_family_name, b.name as supplier_name, a.approved_to, a.approved_by, f.name as maker_name, SUM(a.qty) as total_qty, SUM(a.total) as total_amount');
            $this->db->from('purchase_orders a');
            $this->db->join('suppliers b', 'a.supplier_id = b.id');
            $this->db->join('supplier_items c', 'c.supplier_id = b.id and a.item_rm_id = c.item_rm_id');
            $this->db->join('item_rm d', 'c.item_rm_id = d.id');
            $this->db->join('item_categories e', 'd.item_category_id = e.id');
            $this->db->join('makers f', 'f.id = c.maker_id', 'left'); // left untuk mendapatkan name maker
            $this->db->where('a.approved_to', $approved_to);
            $this->db->where('a.approved_by', $approved_by);
            $this->db->group_by('a.po_no');
            $this->db->order_by('a.created_date', 'desc');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Get Data Array
            $records = $this->db->get()->result_array();
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        } else {
            show_error("Cannot Process your Request");
        }
    }

    public function approvalPurchaseOrderDetails()
    {
        if ($this->input->post()) {
            $purchase_order_no = $this->input->post('purchase_order_no');
            $approved_to = $this->input->post('approved_to');
            $approved_by = $this->input->post('approved_by');

            //Select Query
            $this->db->select('a.id, 
                d.number as item_id, 
                d.name as item_name, 
                a.qty, a.price, a.total, b.currency, d.uom, c.leadtime, 
                a.month_1 as forecast_1, 
                a.month_2 as forecast_2, 
                a.month_3 as forecast_3, 
                b.name as supplier_name, 
                f.name as maker_name'); // maker name
            $this->db->from('purchase_orders a');
            $this->db->join('suppliers b', 'a.supplier_id = b.id');
            $this->db->join('supplier_items c', 'c.supplier_id = b.id and a.item_rm_id = c.item_rm_id');
            $this->db->join('item_rm d', 'c.item_rm_id = d.id');
            $this->db->join('item_categories e', 'd.item_category_id = e.id');
            $this->db->join('makers f', 'f.id = c.maker_id', 'left'); // left untuk mendapatkan name maker
            $this->db->where('a.approved_to', $approved_to);
            $this->db->where('a.approved_by', $approved_by);
            $this->db->where('a.po_no', $purchase_order_no);
            $this->db->group_by('a.id');
            $this->db->order_by('a.created_date', 'desc');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Get Data Array
            $records = $this->db->get()->result_array();
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        } else {
            show_error("Cannot Process your Request");
        }
    }

    public function approvalPurchaseRequests()
    {
        if ($this->input->post()) {
            $approved_to = $this->input->post('approved_to');
            $approved_by = $this->input->post('approved_by');

            //Select Query
            $this->db->select("a.approved_to, a.approved_by, 
                DATE_FORMAT(a.request_date, '%m') as p_month, 
                DATE_FORMAT(a.request_date, '%Y') as p_year, 
                '0' as revision, 
                a.request_no as purchase_request_no, 
                a.request_date as purchase_request_date, 
                c.name as product_family, 
                e.name as supplier_name, 
                SUM(a.qty) as total_pr");
            $this->db->from('purchase_requests a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->join('item_categories c', 'b.item_category_id = c.id');
            $this->db->join('(select supplier_id, item_rm_id from supplier_items group by item_rm_id) d', 'b.id = d.item_rm_id');
            $this->db->join('suppliers e', 'd.supplier_id = e.id');
            $this->db->where('a.approved_to', $approved_to);
            $this->db->where('a.approved_by', $approved_by);
            $this->db->group_by('a.request_no');
            $this->db->order_by('a.request_no', 'asc');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Get Data Array
            $records = $this->db->get()->result_array();
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        } else {
            show_error("Cannot Process your Request");
        }
    }

    public function approvalPurchaseRequestDetails()
    {
        if ($this->input->post()) {
            $purchase_request_no = $this->input->post('purchase_request_no');
            $approved_to = $this->input->post('approved_to');
            $approved_by = $this->input->post('approved_by');

            //Select Query
            $this->db->select("a.id, 
                b.number as part_no,
                b.name as part_name,
                d.mpq,
                d.moq,
                d.share_order,
                d.leadtime,
                a.qty as total_need,
                a.qty as purchase_request,
                a.remarks as reason,
                a.status");
            $this->db->from('purchase_requests a');
            $this->db->join('item_rm b', 'a.item_rm_id = b.id');
            $this->db->join('item_categories c', 'b.item_category_id = c.id');
            $this->db->join('(select * from supplier_items group by item_rm_id) d', 'b.id = d.item_rm_id');
            $this->db->join('suppliers e', 'd.supplier_id = e.id');
            $this->db->where('a.approved_to', $approved_to);
            $this->db->where('a.approved_by', $approved_by);
            $this->db->where('a.request_no', $purchase_request_no);
            $this->db->group_by('a.id');
            $this->db->order_by('a.request_no', 'asc');
            //Total Data
            $totalRows = $this->db->count_all_results('', false);
            //Get Data Array
            $records = $this->db->get()->result_array();
            //Mapping Data
            $result['total'] = $totalRows;
            $result = array_merge($result, ['rows' => $records]);
            echo json_encode($result);
        } else {
            show_error("Cannot Process your Request");
        }
    }

    function getAccessToken()
    {
        // Path relatif ke file JSON
        $pathToFile = __DIR__ . '/erp-mobile-askara.json';
        if (!file_exists($pathToFile)) {
            die("File credentials tidak ditemukan: $pathToFile");
        }

        $serviceAccount = json_decode(file_get_contents($pathToFile), true);
        $header = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $iat = time();
        $exp = $iat + 60;
        $claimSet = base64_encode(json_encode([
            'iss' => $serviceAccount['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $exp,
            'iat' => $iat,
        ]));

        $signatureInput = $header . '.' . $claimSet;
        $signature = null;
        openssl_sign($signatureInput, $signature, $serviceAccount['private_key'], 'SHA256');

        $jwt = $signatureInput . '.' . base64_encode($signature);
        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]));

        $response = curl_exec($ch);
        curl_close($ch);

        // die($response);

        $responseArray = json_decode($response, true);
        return $responseArray['access_token'];
    }

    // Token adalah deviceId/TokenId hp
    function pushNotification($token, $title, $messages)
    {
        $url = 'https://fcm.googleapis.com/v1/projects/erp-mobile-askara/messages:send';
        // $tokenUser = 'fjt-9yygSH-kcAiPoMY7cY:APA91bEpNVcIP47v5flZ4nSgJJ97i3pkgDip_zO5wlcuAOwOjSrEHIju-v_jqGruP-aW83O9qHhBVoAdv-w0-BmaXTp5QzUFw0LKfac_S4ZJ-DnoIk6NbCg';

        // Data notifikasi dari input
        $notification = [
            'title' => $title,
            'body' => $messages,
        ];

        // Payload yang akan dikirim ke Firebase
        $payload = [
            'message' => [
                'token' => $token,
                'notification' => $notification,
                // 'data' => [
                //     'title' => $title,
                //     'body' => $messages,
                // ],
            ]
        ];

        $headers = [
            'Authorization: Bearer ' . $this->getAccessToken(),
            'Content-Type: application/json',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    }
}

<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Mrp_banshu extends CI_Controller

{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->model('mrp');
    }

    public function index()
    {
        redirect('error_access');
    }

    function read($table, $like = [], $where = [], $limit = "", $orderfield = "", $orderby = "", $groupby = [])
    {
        $this->db->select('*');
        $this->db->from($table);
        if ($where != []) {
            $this->db->where($where);
        }
        if ($like != []) {
            $this->db->like($like);
        }
        if ($limit != "") {
            $this->db->limit($limit);
        }
        if ($orderby != "") {
            $this->db->order_by($orderfield, $orderby);
        }
        if ($groupby != []) {
            $this->db->group_by($groupby);
        }
        $records = $this->db->get()->row();
        return $records;
    }

    public function getDataFamilys()
    {

        $send = $this->mrp->prodfam();
        $total = count($send);

        if ($total > 0) {
            for ($i = 0; $i < $total; $i++) {
                $arr[] = array(
                    "pfm_id" => $send[$i]->pfm_id,
                    "pfm_name" => $send[$i]->pfm_name,
                );
            }
        }

        $arr['total'] = @count($arr);
        die(json_encode($arr));
    }

    public function getDataItems()
    {

        $send = $this->mrp->master_item();
        $total = count($send);

        if ($total > 0) {
            for ($i = 0; $i < $total; $i++) {
                if (trim($send[$i]->pfm_id) != '06' && trim($send[$i]->stscode_id) == '01') { // type raw material dan yang aktif

                    $item_rm = $this->read('item_rm', [], ["number" => trim($send[$i]->item_id)]);
                    $item_familys = $this->read('item_familys', [], ["number" => trim($send[$i]->pfm_id)]);
                    $uom = $this->read('uom', [], ["number" => trim($send[$i]->um_id)]);
                    if (
                        @$item_rm->item_category_id == @$item_familys->item_category_id &&
                        @$item_rm->item_family_id == @$item_familys->id &&
                        @$item_rm->number == trim($send[$i]->item_id) &&
                        @$item_rm->name == trim($send[$i]->item_name) &&
                        @$item_rm->uom == @$uom->name &&
                        @$item_rm->maker_id == trim($send[$i]->maker_id)
                    ) {
                    } else {
                        $arr[] = array(
                            "item_family_id" => trim($send[$i]->pfm_id),
                            "number" => trim($send[$i]->item_id),
                            "name" => trim($send[$i]->item_name),
                            "um_id" => trim($send[$i]->um_id),
                            "maker_id" => trim($send[$i]->maker_id),
                        );
                    }
                }
            }
        }

        $arr['total'] = @count($arr);
        die(json_encode($arr));
    }

    public function getDataAlias()
    {
        $plant = $this->input->post('sync_plant');
        $send = $this->mrp->master_item_alias($plant);
        $total = count($send);

        if ($total > 0) {
            for ($i = 0; $i < $total; $i++) {
                $item_rm = $this->read("item_rm", [], ["number" => trim($send[$i]->item_id)]);
                $item_rm2 = $this->read("item_rm", [], ["number" => trim($send[$i]->item_alias)]);
                $item_alias = $this->read('item_alias', [], ["number" => @$item_rm->id, "alias" => @$item_rm2->id]);
                if (empty($item_rm->id) || empty($item_rm2->id) || !empty($item_alias->id)) {
                    //
                } else {
                    $arr[] = array(
                        "number" => @$item_rm->id,
                        "alias" => @$item_rm2->id,
                        "plant" => trim($plant),
                    );
                }
            }
        }

        $arr['total'] = @count($arr);
        die(json_encode($arr));
    }


    public function getDataCustomers()
    {

        $send = $this->mrp->master_customer();
        $total = count($send);

        if ($total > 0) {
            for ($i = 0; $i < $total; $i++) {
                $customers = $this->read('customers', [], ["number" => trim($send[$i]->cust_id)]);
                if (@$customers->number == trim($send[$i]->cust_id) && @$customers->name == trim($send[$i]->cust_name)) {
                    continue;
                } else {
                    $arr[] = array(
                        "number" => $send[$i]->cust_id,
                        "name" => $send[$i]->cust_name,
                    );
                }
            }
        }

        $arr['total'] = @count($arr);
        die(json_encode($arr));
    }

    public function getDataItemFgs()
    {

        $send = $this->mrp->master_item_fg();
        $total = count($send);

        if ($total > 0) {
            $arr = [];
            for ($i = 0; $i < $total; $i++) {
                if (trim($send[$i]->pfm_id) == '06' && trim($send[$i]->stscode_id) == '01') {
                    $item_fg = $this->read('item_fg', [], ["number_customer" => trim($send[$i]->item_id)]);
                    // $customers = $this->read('customers', [], ["number" => trim($send[$i]->cust_id)]);
                    // $uom = $this->read('uom', [], ["number" => trim($send[$i]->um_id)]);
                    if (
                        @$item_fg->number_customer == trim($send[$i]->item_id) &&
                        @$item_fg->name == trim($send[$i]->item_name)
                        // @$item_fg->uom == @$uom->name &&
                        // @$item_fg->cct == trim($send[$i]->item_cct) &&
                        // @$item_fg->mpq == trim($send[$i]->item_muloq) &&
                        // @$item_fg->moq == trim($send[$i]->item_minoq) &&
                        // @$item_fg->leadtime == trim($send[$i]->item_prcldtm) &&
                        // @$item_fg->customer_id == @$customers->id &&
                        // @$item_fg->weight == trim($send[$i]->weight) &&
                        // @$item_fg->lot == trim($send[$i]->lotqty)
                    ) {
                    } else {
                        $arr[] = array(
                            "number_customer" => trim($send[$i]->item_id),
                            "cct" => trim($send[$i]->item_cct),
                            "revision" => 00,
                            "mpq" => trim($send[$i]->item_muloq),
                            "moq" => trim($send[$i]->item_minoq),
                            "leadtime" => trim($send[$i]->item_prcldtm),
                            "customer_id" => trim($send[$i]->cust_id),
                            "weight" => trim($send[$i]->weight),
                            "lot" => trim($send[$i]->lotqty),
                            "name" => trim($send[$i]->item_name),
                            "um_id" => trim($send[$i]->um_id),
                        );
                    }
                }
            }
        }

        $arr['total'] = @count($arr);
        die(json_encode($arr));
    }

    public function getDataBoms()
    {
        $item_fg_id = $this->input->post('sync_item_fg_id');
        $plant = $this->input->post('sync_plant');

        $this->db->select('b.number_customer, b.id as item_fg_id');
        $this->db->from('mrp_ltpp a');
        $this->db->join('item_fg b', 'a.item_fg_id = b.id');
        if (!empty($item_fg_id)) {
            $this->db->where('b.id', $item_fg_id);
        }
        if (!empty($plant)) {
            $this->db->where('a.plant', $plant);
        }
        $this->db->group_by('a.item_fg_id');
        $this->db->order_by('b.id', 'ASC');
        // $this->db->limit(100, 300);
        $items = $this->db->get()->result_array();

        $arr = $items;
        $arr['total'] = @count($items);
        $arr['total_assy'] = count($items);
        die(json_encode($arr));
    }

    public function getDataLttp()
    {
        if ($this->input->post()) {
            $filter_month = $this->input->post('filter_month');
            $filter_year = $this->input->post('filter_year');
            $filter_revision = $this->input->post('filter_revision');
            $filter_cutoff = $this->input->post('filter_cutoff');
            $filter_plant = $this->input->post('filter_plant');
            $period = $filter_year . $filter_month;
            
            $data = $this->mrp->lttp($period, $filter_plant, 3);
            // die(json_encode($data));
            $this->db->delete("mrp_ltpp", ["plant" => $filter_plant, "p_month" => $filter_month, "p_year" => $filter_year, "revision" => $filter_revision]);

            $arr = array();
            for ($i = 0; $i < count($data); $i++) {
                // $customer = $this->read('customers', [], ["number" => trim($data[$i]['cust_id'])]);
                $item_rm = $this->read('item_rm', [], ["number" => trim($data[$i]['item_id'])]);

                if (@$item_rm->id != "") {
                    if($data[$i]['prod_plan_1'] > 0 || $data[$i]['prod_plan_2'] > 0 || $data[$i]['prod_plan_3'] > 0 || $data[$i]['prod_plan_4'] > 0 || $data[$i]['prod_plan_5'] > 0 || $data[$i]['prod_plan_6'] > 0){
                        $arr[] = array(
                            "item_rm_id" => @$item_rm->id,
                            // "customer_id" => @$customer->id,
                            "plant" => $filter_plant,
                            "p_month" => $filter_month,
                            "p_year" => $filter_year,
                            "revision" => $filter_revision,
                            "prod_plan_1" => $data[$i]['prod_plan_1'],
                            "prod_plan_2" => $data[$i]['prod_plan_2'],
                            "prod_plan_3" => $data[$i]['prod_plan_3'],
                            "prod_plan_4" => $data[$i]['prod_plan_4'],
                            "prod_plan_5" => $data[$i]['prod_plan_5'],
                            "prod_plan_6" => $data[$i]['prod_plan_6']
                        );
                    }
                }
            }

            $arr['total'] = count($arr);
            die(json_encode($arr));
        }
    }

    public function getDataOsPo()
    {
        if ($this->input->post()) {
            $filter_month = $this->input->post('filter_month');
            $filter_year = $this->input->post('filter_year');
            $filter_revision = $this->input->post('filter_revision');
            $filter_plant = $this->input->post('filter_plant');
            $filter_cutoff = $this->input->post('filter_cutoff');
            $period = $filter_year . $filter_month;

            $data = $this->mrp->ospo($filter_cutoff, $filter_plant);
            $this->db->delete("mrp_ospo", ["plant" => $filter_plant, "p_month" => $filter_month, "p_year" => $filter_year, "revision" => $filter_revision]);

            $arr = array();
            for ($i = 0; $i < count($data); $i++) {
                $item_rm = $this->read('item_rm', [], ["number" => trim($data[$i]['item_id'])]);

                if (@$item_rm->id != "" && $data[$i]['qty_open'] > 0) {
                    $arr[] = array(
                        "item_rm_id" => @$item_rm->id,
                        "plant" => $filter_plant,
                        "p_month" => $filter_month,
                        "p_year" => $filter_year,
                        "revision" => $filter_revision,
                        "qty" => $data[$i]['qty_open'],
                    );
                }
            }

            $arr['total'] = count($arr);
            die(json_encode($arr));
        }
    }

    public function getDataWip()
    {
        if ($this->input->post()) {
            $filter_month = $this->input->post('filter_month');
            $filter_year = $this->input->post('filter_year');
            $filter_revision = $this->input->post('filter_revision');
            $filter_plant = $this->input->post('filter_plant');
            $filter_cutoff = $this->input->post('filter_cutoff');
            $period = $filter_year . $filter_month;

            $data = $this->mrp->wip($filter_cutoff, $filter_plant);
            $this->db->delete("mrp_wip", ["plant" => $filter_plant, "p_month" => $filter_month, "p_year" => $filter_year, "revision" => $filter_revision]);

            $arr = array();
            for ($i = 0; $i < count($data); $i++) {
                $item_rm = $this->read('item_rm', [], ["number" => trim($data[$i]['item_id'])]);

                if (@$item_rm->id != "") {
                    $arr[] = array(
                        "item_rm_id" => @$item_rm->id,
                        "plant" => $filter_plant,
                        "p_month" => $filter_month,
                        "p_year" => $filter_year,
                        "revision" => $filter_revision,
                        "qty" => $data[$i]['stock'],
                    );
                }
            }

            $arr['total'] = count($arr);
            die(json_encode($arr));
        }
    }

    public function getDataMpp()
    {
        if ($this->input->post()) {
            $filter_month = $this->input->post('filter_month');
            $filter_year = $this->input->post('filter_year');
            $filter_revision = $this->input->post('filter_revision');
            $filter_plant = $this->input->post('filter_plant');
            $filter_cutoff = $this->input->post('filter_cutoff');
            // $period = $filter_year . $filter_month;
            $period = date("Ym", strtotime($filter_cutoff));

            $data = $this->mrp->mpp($period, $filter_plant);
            $this->db->delete("mrp_mpp", ["plant" => $filter_plant, "p_month" => $filter_month, "p_year" => $filter_year, "revision" => $filter_revision]);

            $arr = array();
            for ($i = 0; $i < count($data); $i++) {
                $item_rm = $this->read('item_rm', [], ["number" => trim($data[$i]['bom_com_item'])]);

                if (@$item_rm->id != "") {
                    $arr[] = array(
                        "item_rm_id" => @$item_rm->id,
                        "plant" => $filter_plant,
                        "p_month" => $filter_month,
                        "p_year" => $filter_year,
                        "revision" => $filter_revision,
                        "qty" => $data[$i]['total'],
                    );
                }
            }

            $arr['total'] = count($arr);
            die(json_encode($arr));
        }
    }

    public function getDataStock()
    {
        if ($this->input->post()) {
            $filter_month = $this->input->post('filter_month');
            $filter_year = $this->input->post('filter_year');
            $filter_revision = $this->input->post('filter_revision');
            $filter_plant = $this->input->post('filter_plant');
            $filter_cutoff = $this->input->post('filter_cutoff');
            $period = $filter_year . $filter_month;

            $data = $this->mrp->stock($filter_cutoff, $filter_plant);
            $this->db->delete("mrp_stock", ["plant" => $filter_plant, "p_month" => $filter_month, "p_year" => $filter_year, "revision" => $filter_revision]);

            $arr = array();
            for ($i = 0; $i < count($data); $i++) {
                $item_rm = $this->read('item_rm', [], ["number" => trim($data[$i]['item'])]);

                if (@$item_rm->id != "" && $data[$i]['qty'] > 0) {
                    $arr[] = array(
                        "item_rm_id" => @$item_rm->id,
                        "plant" => $filter_plant,
                        "p_month" => $filter_month,
                        "p_year" => $filter_year,
                        "revision" => $filter_revision,
                        "qty" => $data[$i]['qty'],
                    );
                }
            }

            $arr['total'] = count($arr);
            die(json_encode($arr));
        }
    }

    public function getDataWo()
    {
        if ($this->input->post()) {
            $filter_month = $this->input->post('filter_month');
            $filter_year = $this->input->post('filter_year');
            $filter_revision = $this->input->post('filter_revision');
            $filter_plant = $this->input->post('filter_plant');
            $filter_cutoff = $this->input->post('filter_cutoff');
            $periode_1 = date("Ym", strtotime("-1 month", strtotime($filter_year ."-". $filter_month."-01")));
            $periode_2 = date("Ym", strtotime("-2 month", strtotime($filter_year ."-". $filter_month."-01")));
            $periode_3 = date("Ym", strtotime("-3 month", strtotime($filter_year ."-". $filter_month."-01")));

            $data = $this->mrp->wo($periode_1, $periode_2, $periode_3, $filter_plant);
            $this->db->delete("mrp_workorder", ["plant" => $filter_plant, "p_month" => $filter_month, "p_year" => $filter_year, "revision" => $filter_revision]);

            $arr = array();
            for ($i = 0; $i < count($data); $i++) {
                $item_rm = $this->read('item_rm', [], ["number" => trim($data[$i]['item_id'])]);

                if (@$item_rm->id != "") {
                    $arr[] = array(
                        "item_rm_id" => @$item_rm->id,
                        "plant" => $filter_plant,
                        "p_month" => $filter_month,
                        "p_year" => $filter_year,
                        "revision" => $filter_revision,
                        "period_1" => $data[$i]['period_1'],
                        "period_2" => $data[$i]['period_2'],
                        "period_3" => $data[$i]['period_3'],
                    );
                }
            }

            $arr['total'] = count($arr);
            die(json_encode($arr));
        }
    }
}

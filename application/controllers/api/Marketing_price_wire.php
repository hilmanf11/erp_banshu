<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Marketing_price_wire extends CI_Controller
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

    public function index()
    {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($this->db->get("marketing_price_wire")->result_object()));
    }
}

<?php
date_default_timezone_set("Asia/Bangkok");
defined('BASEPATH') or exit('No direct script access allowed');

class Purchase extends CI_Controller
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
        if ($this->session->username != "") {
            $data['config'] = $this->crud->read('config');
            $data['users'] = $this->crud->reads('users', [], ["actived" => 0, "deleted" => 0], "", "name", "asc");
            $data['session_name'] = $this->session->name;

            $this->load->view('template/header');
            $this->load->view('dashboard/purchase', $data);
        } else {
            redirect('error_session');
        }
    }
}

<?php
defined('BASEPATH') or exit('No direct script access allowed');

class LME_prices extends CI_Controller
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
        $uri = $_SERVER['REQUEST_URI'];
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'ok',
                'message' => 'Welcome to the API endpoint',
                'endpoints' => [
                    'POST '.$uri.'/login',
                    'GET '.$uri.'/datas'
                ]
            ]));
    }

    // ------------------------------------------------
    // POST /api/login
    // ------------------------------------------------
    public function login()
    {
        $input = json_decode($this->input->raw_input_stream, true);

        if (!$input || !isset($input['username']) || !isset($input['password'])) {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Username and password are required']));
            return;
        }

        $user = $this->db->get_where('users_api', [
            'username' => $input['username'],
            'password' => $input['password']
        ])->result_object();

        if ($user) {
            $token = bin2hex(random_bytes(16));

            $this->db->insert('tokens_api', [
                'username' => $input['username'],
                'token' => $token,
                'create_date' => date('Y-m-d H:i:s')
            ]);

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => true,
                    'message' => 'Login berhasil',
                    'token' => $token
                ]));
        } else {
            $this->output
                ->set_status_header(401)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Incorrect username or password']));
        }
    }

    public function datas()
    {
        $headers = $this->input->request_headers();

        if (!isset($headers['Authorization'])) {
            $this->output
                ->set_status_header(401)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Token not found, please login first.']));
            return;
        }

        $token = str_replace('Bearer ', '', $headers['Authorization']);

        $query = $this->db->get_where('tokens_api', ['token' => $token]);
        $user = $query->row();

        if (!$user) {
            $this->output
                ->set_status_header(401)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Invalid token']));
            return;
        }

        $time = strtotime($user->create_date.' +30 minutes');
        if ($time < strtotime('now')) {
            $this->output
            ->set_status_header(401)
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'error' => 'Token expired',
                'expired_at' => date('Y-m-d H:i:s', $time)
            ]));
            return;
        }

        $result = $this->db->get("lme_prices")->result_object();

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($result));
    }
}

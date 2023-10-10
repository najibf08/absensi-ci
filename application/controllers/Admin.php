<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Admin extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        // Load model dan library yang diperlukan
        $this->load->model('User_model');
        $this->load->library('form_validation');
    }

    public function admin()
    {
        $this->load->view('admin/admin');
    }
}
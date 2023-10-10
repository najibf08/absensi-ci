<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        // Load model dan library yang diperlukan
        $this->load->model('User_model');
        $this->load->library('form_validation');
        $this->load->library('upload');
    }

    public function index()
    {
        $this->load->view('auth/login');
    }

    public function register()
    {
        $this->load->view('auth/register');
    }

    public function register_admin()
    {
        $this->load->view('auth/register_admin');
    }

    // Fungsi untuk memproses registrasi
    public function aksi_register()
    {
        // Validasi input
        $this->form_validation->set_rules('username', 'Username', 'required');
        $this->form_validation->set_rules(
            'email',
            'Email',
            'required|valid_email|is_unique[User.email]'
        );
        $this->form_validation->set_rules(
            'nama_depan',
            'Nama Depan',
            'required'
        );
        $this->form_validation->set_rules(
            'nama_belakang',
            'Nama Belakang',
            'required'
        );
        $this->form_validation->set_rules(
            'password',
            'Password',
            'required|min_length[6]'
        );

        if ($this->form_validation->run() === false) {
            // Jika validasi gagal, tampilkan kembali halaman registrasi
            $this->load->view('auth/register');
        } else {
            // Jika validasi sukses, ambil data dari form
            $data = [
                'username' => $this->input->post('username'),
                'email' => $this->input->post('email'),
                'nama_depan' => $this->input->post('nama_depan'),
                'nama_belakang' => $this->input->post('nama_belakang'),
                'password' => password_hash(
                    $this->input->post('password'),
                    PASSWORD_DEFAULT
                ),
                'role' => 'karyawan', // Default role adalah karyawan
            ];

            // Cek apakah registrasi ini adalah admin
            if ($this->input->post('admin_code') == 'admin_secret_code') {
                $data['role'] = 'admin'; // Jika kode rahasia admin cocok, set role sebagai admin
            }

            // Simpan data ke dalam database
            $this->User_model->registerUser($data);

            // Redirect pengguna ke halaman login atau halaman lain yang sesuai
            redirect('auth');
        }
    }

    public function aksi_login()
    {
        // Validasi form login
        $this->form_validation->set_rules(
            'email',
            'Email',
            'required|valid_email'
        );
        $this->form_validation->set_rules('password', 'Password', 'required');

        if ($this->form_validation->run() === false) {
            // Jika validasi gagal, tampilkan kembali halaman login
            $this->load->view('auth');
        } else {
            // Ambil data dari form
            $email = $this->input->post('email');
            $password = $this->input->post('password');

            // Periksa apakah pengguna ada dalam database berdasarkan email
            $user = $this->User_model->getUserByEmail($email);

            if (!$user) {
                // Jika pengguna tidak ditemukan, tampilkan pesan kesalahan
                $data['error'] = 'Email or password is incorrect';
                $this->load->view('auth', $data);
            } else {
                // Periksa apakah password yang dimasukkan sesuai
                if (password_verify($password, $user->password)) {
                    // Jika login berhasil, simpan sesi pengguna
                    $this->session->set_userdata('id', $user->id);

                    // Redirect pengguna ke halaman yang sesuai berdasarkan peran (role)
                    if ($user->role == 'admin') {
                        redirect('admin/admin'); // Ganti 'admin_dashboard' dengan halaman dashboard admin
                    } elseif ($user->role == 'karyawan') {
                        redirect('employee/karyawan'); // Ganti 'karyawan_dashboard' dengan halaman dashboard karyawan
                    }
                } else {
                    // Jika password tidak sesuai, tampilkan pesan kesalahan
                    $data['error'] = 'Email or password is incorrect';
                    $this->load->view('auth', $data);
                }
            }
        }
    }

    function logout()
    {
        $this->session->sess_destroy();
        redirect(base_url('auth'));
    }
}
?>
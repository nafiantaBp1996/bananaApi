<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Perhitungan extends CI_Controller {

	public function index()
	{
		$this->load->view('partial/header');
		$this->load->view('Reguler');
		$this->load->view('partial/footer');
		
	}

}

/* End of file Perhitungan.php */
/* Location: ./application/controllers/Perhitungan.php */
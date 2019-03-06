<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reguler_model extends CI_Model {

	public function getSewaModal($pinjaman,$tenor)
	{
		$query = "SELECT reguler.`min_reg`,reguler.`max_reg`,reguler.`sm_reg`,administrasi.`biaya_admin`,administrasi.`bulan` FROM reguler JOIN administrasi ON reguler.`id_admin` = administrasi.`id_admin`WHERE reguler.`min_reg`<= '$pinjaman' AND reguler.`max_reg`>='$pinjaman' AND administrasi.`bulan` = '$tenor'";

		return $this->db->query($query)->result();
	}
}

/* End of file Reguler_model.php */
/* Location: ./application/models/Reguler_model.php */
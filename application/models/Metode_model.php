<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Metode_Model extends CI_Model {
	public function updateMetode($id,$nilai){
		$this->db->where('id_metode', $id);
		$this->db->update('metod_aktif', $nilai);

		if ($nilai['nilai']==0) {
				return "Metode Tidak Aktif";
			}
		else{
			return "Metode Aktif";
		}	
	}

	public function loadmetode(){
		return $this->db->get('metod_aktif')->result();

	}
	
}

/* End of file dataset_Model.php */
/* Location: ./application/models/dataset_Model.php */
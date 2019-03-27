<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dataset_Model extends CI_Model {
	public function insertDataset($object){
		$this->db->insert('dataset', $object);	
	}
	public function loadDataset(){
		return $this->db->get('dataset')->result();	
	}
}

/* End of file dataset_Model.php */
/* Location: ./application/models/dataset_Model.php */
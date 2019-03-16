<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dataset_Model extends CI_Model {

public function insertDataset($object)
{
	$this->db->insert('dataset', $object);	
}
	

}

/* End of file dataset_Model.php */
/* Location: ./application/models/dataset_Model.php */
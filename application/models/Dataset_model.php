<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dataset_Model extends CI_Model {
	public function insertDataset($object){
		$this->db->insert('dataset', $object);	
	}
	public function loadDataset($data){
		$this->db->order_by("id_dataset", "desc");
		return $this->db->get_where('dataset',array('kematangan'=>$data))->result();	
	}
	public function dataKlasifikasi()
	{
		// return $this->db->query("SELECT AVG(hue) AS hue,AVG(saturation) AS saturation,AVG(intensity) AS intensity,AVG(contras) AS contras,AVG(entropy) AS entropy,AVG(energy) AS energy,AVG(homogenity) AS homogenity,AVG(corelation) AS corelation, kematangan,prediksi FROM dataset GROUP BY prediksi")->result();
		return $this->db->query("SELECT hue,saturation,intensity,contras,entropy,energy,homogenity,corelation,kematangan,prediksi FROM dataset")->result();

	}

	public function dataScater()
	{
		return $this->db->query("SELECT hue,saturation,intensity,contras,entropy,energy,homogenity,corelation,kematangan,prediksi FROM dataset")->result();

	}

	public function getResult($prediksi)
	{
		return $this->db->query("SELECT kematangan FROM dataset WHERE prediksi = '$prediksi' LIMIT 1")->result();
	}

	public function sumTotalData(){
		return $this->db->query("SELECT SUM(hue) AS avgHue,SUM(saturation) AS avgSat,SUM(intensity) AS avgInt,
		SUM(contras) AS avgCont,SUM(entropy) AS avgEnt,SUM(energy) AS avgEne,SUM(homogenity) AS avgHom FROM dataset ")->result();
	}
	public function minMaxData()
	{
		return $this->db->query("SELECT MIN(hue)AS minHue, MAX(hue)AS maxHue,
								MIN(saturation)AS minSat, MAX(saturation )AS maxSat,
								MIN(intensity)AS minInt, MAX(intensity)AS maxInt,
								MIN(`contras`)AS minCont, MAX(`contras`)AS maxCont,
								MIN(homogenity)AS minHom, MAX(homogenity)AS maxHom,
								MIN(corelation)AS minCor, MAX(corelation)AS maxCor,
								MIN(energy)AS minEne, MAX(energy)AS maxEne,
								MIN(entropy)AS minEnt, MAX(entropy)AS maxEnt FROM dataset")->result();
	}
}

/* End of file dataset_Model.php */
/* Location: ./application/models/dataset_Model.php */
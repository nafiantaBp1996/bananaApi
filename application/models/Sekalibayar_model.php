<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class sekalibayar_model extends CI_Model {

	public function getSewaModal($up,$tenor)
	{
        if($up <= 10000000 ){
        $query ="select sm_fs from fleksi_sekali where max_fs = '10000000' and tenor_fs = $tenor";
        }else if($up > 10000000 and $up <= 50000000){
        $query ="select sm_fs from fleksi_sekali where min_fs = '10000001' and max_fs = '50000000' and tenor_fs = $tenor";
        }else if($up > 50000000 and $up <= 100000000){
        $query ="select sm_fs from fleksi_sekali where min_fs ='50000001' and max_fs = '100000000' and tenor_fs = $tenor";
        }else if($up > 100000000){
        $query ="select sm_fs from fleksi_sekali where min_fs ='100000001' and tenor_fs = $tenor";
        }
        $hasil = $this->db->query($query)->result();
        foreach($hasil as $data){
            return $data->sm_fs;
        }
    }
    
    public function getAdministrasi($tenor){
        $q = $this->db->query("select biaya_admin from administrasi where bulan = $tenor ")->result();
        foreach($q as $d){
            return $d->biaya_admin;
        }
    }
}

/* End of file Reguler_model.php */
/* Location: ./application/models/Reguler_model.php */
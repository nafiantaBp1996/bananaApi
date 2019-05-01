<?php
 
require APPPATH . '/libraries/REST_Controller.php';
 
class Klasifikasi extends REST_Controller {
    function __construct($config = 'rest') {
        parent::__construct($config);
    }
    function index_get(){

        $this->load->model('Dataset_Model');
        $this->load->model('metode_model');
        $in=$this->metode_model->loadmetode();
        
        ///checkdataglcm
        $statusglcm=$in[0]->nilai;
        $statushsi=$in[1]->nilai;
        $statusknn=$in[2]->nilai;

        $dataKlasifikasi = $this->Dataset_Model->dataKlasifikasi();
        $dataMinMax =  $this->Dataset_Model->minMaxData();

        $hueInp=$this->get('hue');
        $saturationInp=$this->get('saturation');
        $intensityInp=$this->get('intensity');
        $contrasInp=$this->get('contras');
        $entropyInp=$this->get('entropy');
        $energyInp=$this->get('energy');
        $homogenityInp=$this->get('homogenity');
        $corelationInp=$this->get('corelation');
        
        $data;
        $i=0;
        /////LOOPFORLOOKINGDISTANCE
        foreach ($dataKlasifikasi as $key) {
            $distHue = $this->distMinMax($hueInp,$key->hue,$dataMinMax[0]->minHue,$dataMinMax[0]->maxHue);
            $distCon = $this->distMinMax($contrasInp,$key->contras,$dataMinMax[0]->minCont,$dataMinMax[0]->maxCont);
            $distEnt = $this->distMinMax($entropyInp,$key->entropy,$dataMinMax[0]->minEnt,$dataMinMax[0]->maxEnt);
            $distEne = $this->distMinMax($energyInp,$key->energy,$dataMinMax[0]->minEne,$dataMinMax[0]->maxEne);
            $distHom = $this->distMinMax($energyInp,$key->homogenity,$dataMinMax[0]->minHom,$dataMinMax[0]->maxHom);
            $distCor = $this->distMinMax($corelationInp,$key->corelation,$dataMinMax[0]->minCor,$dataMinMax[0]->maxCor);

            $bobothsi=$statushsi*(4/9);
            $bobotglcm=$statusglcm*(1/9);

            $distance = sqrt(pow($distHue*$bobothsi,2)+pow($distCon*$bobotglcm,2)+pow($distEnt*$bobotglcm,2)+pow($distEne*$bobotglcm,2)+pow($distHom*$bobotglcm,2)+pow($distCor*$bobotglcm,2));
            $data[$i]=array('distance'=>$distance,
                            'prediksi'=>$key->prediksi,
                            'keterangan'=>$key->kematangan,
                            'hue'=>$hueInp,
                            'saturation'=>$saturationInp,
                            'intensity'=>$intensityInp,
                            'contras'=>$contrasInp,
                            'energy'=>$entropyInp,
                            'homogenity'=>$energyInp,
                            'entropy'=>$homogenityInp);
            $i++;
        }

        //URUTKANDATA
        //sort($data);
        usort($data, function($a, $b) {
            return $a['distance'] <=> $b['distance'];
        });
        ///KNN
        $k=$statusknn;
        $kmean;
        $klasifikasiKmeans;
        for ($i=0; $i < $k ; $i++) { 
            $kmean[$i]= $data[$i];
        }

        ////RETURNDATA
        $klasifikasiKmeans=array_count_values(array_column($kmean,'prediksi'));
        $hasil = array_search(max($klasifikasiKmeans), $klasifikasiKmeans);
        $index= array_search($hasil,array_column($kmean,'prediksi'));

        $this->response($data[$index], 200);

    }
    public function distMinMax($vinp,$vdata,$min,$max)
    {
        if($vinp < $min){
            $min=$vinp;
        }
        if($vinp > $max){
            $max=$vinp;
        }

        $normalinp = ($vinp-$min)/($max-$min);
        $normalout = ($vdata-$min)/($max-$min);
        return $normalinp-$normalout;
    }
}
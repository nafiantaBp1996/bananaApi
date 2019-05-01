<?php
 
require APPPATH . '/libraries/REST_Controller.php';
 
class ScaterData extends REST_Controller {
    function __construct($config = 'rest') {
        parent::__construct($config);
    }
    function index_get(){

        $this->load->model('Dataset_Model');
        // $dataKlasifikasi = $this->Dataset_Model->dataScater();
        $dataKlasifikasi = $this->Dataset_Model->dataKlasifikasi();
        $dataMinMax =  $this->Dataset_Model->minMaxData();

        $matang="";
        $setengah="";
        $mentah = "";
        $mat=0;
        $men=0;
        $set=0;
        /////LOOPFORLOOKINGDISTANCE
        foreach ($dataKlasifikasi as $key) {
            ////HSI
            $distHue = $this->distMinMax($key->hue,$dataMinMax[0]->minHue,$dataMinMax[0]->maxHue);
            $hsi=sqrt(pow($distHue*(4/9),2));

            ////GLCM
            $distCon = $this->distMinMax($key->contras,$dataMinMax[0]->minCont,$dataMinMax[0]->maxCont);
            $distEnt = $this->distMinMax($key->entropy,$dataMinMax[0]->minEnt,$dataMinMax[0]->maxEnt);
            $distEne = $this->distMinMax($key->energy,$dataMinMax[0]->minEne,$dataMinMax[0]->maxEne);
            $distHom = $this->distMinMax($key->homogenity,$dataMinMax[0]->minHom,$dataMinMax[0]->maxHom);
            $distCor = $this->distMinMax($key->corelation,$dataMinMax[0]->minCor,$dataMinMax[0]->maxCor);

            $glcm = sqrt(pow($distCon*(1/9),2)+pow($distEnt*(1/9),2)+pow($distEne*(1/9),2)+pow($distHom*(1/9),2)+pow($distCor*(1/9),2));
            ////DATAARRAY 
            if ($key->kematangan=="matang") {
                $matang=$matang."[".round($hsi*100,2).",".round($glcm*100,2)."],";
                $mat++;
               }
            elseif ($key->kematangan=="mentah") {
                $mentah=$mentah."[".round($hsi*100,2).",".round($glcm*100,2)."],";
                $men++;
               }
            elseif ($key->kematangan=="setMatang"){
                $setengah=$setengah."[".round($hsi*100,2).",".round($glcm*100,2)."],";
                $set++;
            }   
            
        }
        
        $this->response(array("matang"=>$matang,"mentah"=>$mentah,"setengah"=>$setengah,"jumMatang"=>$mat,"jumSet"=>$set,"jumMentah"=>$men,), 200);

    }
public function distMinMax($vdata,$min,$max){
        $normalout = ($vdata-$min)/($max-$min);
        return $normalout;
    }
}
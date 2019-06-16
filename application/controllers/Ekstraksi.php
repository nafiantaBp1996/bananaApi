<?php
 
require APPPATH . '/libraries/REST_Controller.php';
 
class Ekstraksi extends REST_Controller 
{
    function __construct($config = 'rest') {
        parent::__construct($config);
    }

    function index_post() {
      $data = array('nama_file' =>$this->post('filenameInp'),
                   'hue' => $this->post('hueInp'),
                   'saturation' => $this->post('saturInp'),
                   'intensity' => $this->post('intenInp'),
                   'red' => $this->post('redInp'),
                   'green' => $this->post('greenInp'),
                   'blue' => $this->post('blueInp'),
                   'contras' => $this->post('contrasInp'),
                   'entropy' => $this->post('entroInp'),
                   'energy' => $this->post('energyInp'),
                   'homogenity'=>$this->post('homogenInp'),
                   'corelation'=>$this->post('corelationInp'),
                   'kematangan' => $this->post('kematanganInp'),
                   'prediksi' => $this->post('prediksiInp'));
      $this->load->model('Dataset_model');
      $this->Dataset_model->insertDataset($data);
      $this->response(array('status' => "input sukses"), 200);
    }

    function index_get() {
        $images = $this->get('image');
        $id = $this->get('id');

        if ($images==null) {
          $this->response(array('status' => 'no Image'));
        }
        else{
          $im = ImageCreateFromJpeg($images); 
          $imgw = imagesx($im);
          $imgh = imagesy($im);
          // $tres = $this->tres($im,$imgw,$imgh,100);
          // $hsi = $this->getHsi($im,$imgw,$imgh);
          $glcm = $this->glcm($im,$imgw,$imgh);
          $avgRgb= $this->avgRgb($im,$imgw,$imgh,90);
          $hsi = $this->getHsiAvg($avgRgb['red'],$avgRgb['green'],$avgRgb['blue']);
          //$this->response($dataHsi, 200);
          if ($id=='android') {
            $datas=array('hue'=>$hsi['H'],
                                  'saturation'=>$hsi['S'],
                                  'intensity'=>$hsi['I'],
                                  'contras'=>$glcm['glcmavg']['contras'],
                                  'energy'=>$glcm['glcmavg']['energy'],
                                  'homogenity'=>$glcm['glcmavg']['homogen'],
                                  'entropy'=>$glcm['glcmavg']['entropy'],
                                  'corelation'=>$glcm['glcmavg']['korelasi']);
            $json_respnse=$this->curl->simple_get('http://localhost/bananaApi/index.php/Klasifikasi', $datas, array(CURLOPT_BUFFERSIZE => 10));
            $data= json_decode($json_respnse);
            $this->response($data, 200);
          }
          else{
            //$this->response($avgRgb, 200);
            $this->response(array('glcm'=>$glcm,'hsi'=>$hsi), 200);
          }
        }
    }

    function avgRgb($im,$imgw,$imgh,$batas)
      {
        $pixels=0;
        $redd=0;
        $greenn=0;
        $bluee=0;
        for ($i=0; $i<$imgw; $i++)
          {
            for ($j=0; $j<$imgh; $j++)
            {
              $rgb = ImageColorAt($im, $i, $j); 
              $rr = ($rgb >> 16) & 0xFF;
              $gg = ($rgb >> 8) & 0xFF;
              $bb = $rgb & 0xFF;
              $g = round(($rr + $gg + $bb) / 3);
              if (($rr>=$batas && $gg>=$batas && $bb<$batas)||$g<$batas) {
                      $redd+=$rr;
                      $bluee+=$bb;
                      $greenn+=$gg;
                      $pixels+=1;
                      //$val = imagecolorallocate($im, $rr, $gg, $bb);     
              }
            }
          } 
          
          return array("red"=>$redd/$pixels,"green"=>$greenn/$pixels,"blue"=>$bluee/$pixels,"pixel"=>$pixels); 
      }

    function tres($im,$imgw,$imgh,$batas)
      {
        for ($i=0; $i<$imgw; $i++)
          {
                  for ($j=0; $j<$imgh; $j++)
                  {
                  $rgb = ImageColorAt($im, $i, $j); 
                  $rr = ($rgb >> 16) & 0xFF;
                  $gg = ($rgb >> 8) & 0xFF;
                  $bb = $rgb & 0xFF;
                  $g = round(($rr + $gg + $bb) / 3);
                  if (($rr>=$batas && $gg>=$batas && $bb<$batas)||$g<$batas) {
                          $val = imagecolorallocate($im, $rr, $gg, $bb);     
                  }
                  else{
                          $val = imagecolorallocate($im, 255, 255, 255);     
                  }
                  imagesetpixel($im, $i, $j, $val);
                  }
          } 
          
          return $im; 
      }

    function getHsiAvg($rr,$gg,$bb){
        $Red = 0; $Green = 0; $Blue = 0;
        $Hue = 0; $Satur = 0; $Inten = 0;
        $pixels = 0;
        $rt= $rr + $gg + $bb;
        $gray=round(($rr + $gg + $bb) / 3,6);
        if ($rt==0) {
            $r = 0;
            $g = 0;
            $b = 0;
        }
        else
        {
            $r = $rr/$rt;
            $g = $gg/$rt;
            $b = $bb/$rt;
        }
        
        $minData = min($rr,$gg,$bb);
        $maxData = max($rr,$gg,$bb);
        $delta = $maxData-$minData;

        $I = $gray;

        if ($r==$g && $g==$b) {
            $H = 0;
            $S = 0;
        }
        else{
            $w = 0.5 *(($r-$g)+($r-$b))/sqrt((($r-$g)*($r-$g))+(($r-$b)*($g-$b)));

            if ($w>1) {
                $w = 1;
            }
            if ($w<-1) {
                $w=-1;
            }

            $H = acos($w);

            if ($b>$g) {
                $H=2*pi()-$H;
                $H=$H*180/pi();
            }
            else
            {
                $H=$H*180/pi(); 
            }
            $S = (1-(3*min($r,$g,$b)))*100;
        }
        $Red = $r;
        $Green = $g;
        $Blue = $b;

        $Hue = $H;
        $Satur = $S;
        $Inten = $I;            
        $dataHsi = array('H' => round($Hue,6),
                         'S' => round($Satur,6),
                         'I' => round($Inten,6),
                         'R' => round($Red,6),
                         'G' => round($Green,6),
                         'B' => round($Blue,6));
        return $dataHsi;
    }

    function getHsi($im,$imgw,$imgh){
        $Red = 0; $Green = 0; $Blue = 0;
        $Hue = 0; $Satur = 0; $Inten = 0;
        $pixels = 0;

        for ($i=0; $i<$imgw; $i++)
          {
            for ($j=0; $j<$imgh; $j++)
            {
            $rgb = ImageColorAt($im, $i, $j); 
            $rr = ($rgb >> 16) & 0xFF;
            $gg = ($rgb >> 8) & 0xFF;
            $bb = $rgb & 0xFF;

            $rt= $rr + $gg + $bb;
            $gray=round(($rr + $gg + $bb) / 3); 

              if ($gray!=255) {
                $pixels+=1;
                    if ($rt==0) {
                        $r = 0;
                        $g = 0;
                        $b = 0;
                    }
                    else
                    {
                        $r = $rr/$rt;
                        $g = $gg/$rt;
                        $b = $bb/$rt;
                    }
                    
                    $minData = min($rr,$gg,$bb);
                    $maxData = max($rr,$gg,$bb);
                    $delta = $maxData-$minData;

                    $I = $gray;

                    if ($r==$g && $g==$b) {
                        $H = 0;
                        $S = 0;
                    }
                    else{
                        $w = 0.5 *(($r-$g)+($r-$b))/sqrt((($r-$g)*($r-$g))+(($r-$b)*($g-$b)));

                        if ($w>1) {
                            $w = 1;
                        }
                        if ($w<-1) {
                            $w=-1;
                        }

                        $H = acos($w);

                        if ($b>$g) {
                            $H=2*pi()-$H;
                            $H=$H*180/pi();
                        }
                        else
                        {
                            $H=$H*180/pi(); 
                        }
                        $S = (1-(3*min($r,$g,$b)))*100;
                    }
                    $Red += $r;
                    $Green += $g;
                    $Blue += $b;

                    $Hue += $H;
                    $Satur += $S;
                    $Inten += $I;            
              }
            }
          } 
        $dataHsi = array('H' => round($Hue/$pixels,4),
                         'S' => round($Satur/$pixels,4),
                         'I' => round($Inten/$pixels,4),
                         'R' => round($Red/$pixels,4),
                         'G' => round($Green/$pixels,4),
                         'B' => round($Blue/$pixels,4),
                         'pixel' => $pixels);
        return $dataHsi;
    }

    function glcm($im,$imgw,$imgh){
      $imgw=$imgw-1;
      $imgh=$imgh-1;
      $glcm0=null;
      $glcm45=null;
      $glcm90=null;
      $glcm135=null;
      $totalpixel=0;
      $eksglcm0=array('indeks'=>'glcm0','contras' => 0,'homogen' => 0,'energy' => 0,'entropy' => 0,'korelasi' => 0);
      $eksglcm45=array('indeks'=>'glcm45','contras' => 0,'homogen' => 0,'energy' => 0,'entropy' => 0,'korelasi' => 0);  
      $eksglcm90=array('indeks'=>'glcm90','contras' => 0,'homogen' => 0,'energy' => 0,'entropy' => 0,'korelasi' => 0);
      $eksglcm135=array('indeks'=>'glcm135','contras' => 0,'homogen' => 0,'energy' => 0,'entropy' => 0,'korelasi' => 0);
      $eksglcmavg=array('indeks'=>'glcmavgs','contras' => 0,'homogen' => 0,'energy' => 0,'entropy' => 0,'korelasi' => 0);
      for ($i=0; $i < 16; $i++) {
        for ($j=0; $j < 16; $j++) { 
          $glcm0[$i][$j]=0;
          $glcm45[$i][$j]=0;
          $glcm90[$i][$j]=0;
          $glcm135[$i][$j]=0;
         } 
      }

      for ($i=1; $i<$imgw; $i++){
          for ($j=1; $j<$imgh; $j++){
              $rgb = ImageColorAt($im, $i, $j); 
              $rgb0 = ImageColorAt($im, $i+1, $j);
              $rgb45 = ImageColorAt($im, $i+1, $j+1);
              $rgb90 = ImageColorAt($im, $i, $j+1);
              $rgb135 = ImageColorAt($im, $i-1, $j+1);

              $rr = ($rgb >> 16) & 0xFF;
              $gg = ($rgb >> 8) & 0xFF;
              $bb = $rgb & 0xFF;

              $r0 = ($rgb0 >> 16) & 0xFF;
              $g0 = ($rgb0 >> 8) & 0xFF;
              $b0 = $rgb0 & 0xFF;

              $r45 = ($rgb45 >> 16) & 0xFF;
              $g45 = ($rgb45 >> 8) & 0xFF;
              $b45 = $rgb45 & 0xFF;

              $r90 = ($rgb90 >> 16) & 0xFF;
              $g90 = ($rgb90 >> 8) & 0xFF;
              $b90 = $rgb90 & 0xFF;

              $r135 = ($rgb135 >> 16) & 0xFF;
              $g135 = ($rgb135 >> 8) & 0xFF;
              $b135 = $rgb135 & 0xFF;

              $g = round(($rr + $gg + $bb) / 3);
              $gray = $this->quantisasi16(round(($rr + $gg + $bb) / 3));
              $gray0 = $this->quantisasi16(round(($r0 + $g0 + $b0) / 3));
              $gray45 = $this->quantisasi16(round(($r45 + $g45 + $b45) / 3));
              $gray90 = $this->quantisasi16(round(($r90 + $g90 + $b90) / 3));
              $gray135 = $this->quantisasi16(round(($r135 + $g135 + $b135) / 3));                

              if ($gray!=255) {
                $glcm0[$gray][$gray0]+=1;
                $totalpixel+=2;
                $glcm45[$gray][$gray45]+=1;
                $glcm90[$gray][$gray90]+=1;
                $glcm135[$gray][$gray135]+=1; 
              }
          }
      }

      for ($i=0; $i < 16; $i++) {
        for ($j=0; $j < 16; $j++) {
          //////////0derajat
          $glcmTrans0[$i][$j] = ($glcm0[$i][$j]+$glcm0[$j][$i])/$totalpixel;
          $eksglcm0['contras'] += $glcmTrans0[$i][$j]*pow($i-$j,2);
          $eksglcm0['homogen'] += $glcmTrans0[$i][$j]/(1+pow($i-$j,2));
          $eksglcm0['energy'] += pow($glcmTrans0[$i][$j],2);
          if ($glcmTrans0[$i][$j]!=0) {
            $eksglcm0['entropy'] += -(log($glcmTrans0[$i][$j])*$glcmTrans0[$i][$j]);
            
            // $eksglcm0['korelasi'] += $glcmTrans0[$i][$j] * (($i-$glcmTrans0[$i][$j])*($j-$glcmTrans0[$i][$j]))/(pow($i-$glcmTrans0[$i][$j],2)*pow($j-$glcmTrans0[$i][$j],2));
          }
          //////////45derajat
          $glcmTrans45[$i][$j]= ($glcm45[$i][$j]+$glcm45[$j][$i])/$totalpixel;
          $eksglcm45['contras'] += $glcmTrans45[$i][$j]*pow($i-$j,2);
          $eksglcm45['homogen'] += $glcmTrans45[$i][$j]/(1+pow($i-$j,2));
          $eksglcm45['energy'] += pow($glcmTrans45[$i][$j],2);
          if ($glcmTrans45[$i][$j]!=0) {
            $eksglcm45['entropy'] += -(log($glcmTrans45[$i][$j])*$glcmTrans45[$i][$j]);
            
            // $eksglcm45['korelasi'] += $glcmTrans45[$i][$j] * (($i-$glcmTrans45[$i][$j])*($j-$glcmTrans45[$i][$j]))/(pow($i-$glcmTrans45[$i][$j],2)*pow($j-$glcmTrans45[$i][$j],2));
          }
          //////////90derajat
          $glcmTrans90[$i][$j]= ($glcm90[$i][$j]+$glcm90[$j][$i])/$totalpixel;
          $eksglcm90['contras'] += $glcmTrans90[$i][$j]*pow($i-$j,2);
          $eksglcm90['homogen'] += $glcmTrans90[$i][$j]/(1+pow($i-$j,2));
          $eksglcm90['energy'] += pow($glcmTrans90[$i][$j],2);
          if ($glcmTrans90[$i][$j]!=0) {
            $eksglcm90['entropy'] += -(log($glcmTrans90[$i][$j])*$glcmTrans90[$i][$j]);
            
            // $eksglcm90['korelasi'] += $glcmTrans90[$i][$j] * (($i-$glcmTrans90[$i][$j])*($j-$glcmTrans90[$i][$j]))/(pow($i-$glcmTrans90[$i][$j],2)*pow($j-$glcmTrans90[$i][$j],2));
          }
          //////////135derajat
          $glcmTrans135[$i][$j]= ($glcm135[$i][$j]+$glcm135[$j][$i])/$totalpixel;
          $eksglcm135['contras'] += $glcmTrans135[$i][$j]*pow($i-$j,2);
          $eksglcm135['homogen'] += $glcmTrans135[$i][$j]/(1+pow($i-$j,2));
          $eksglcm135['energy'] += pow($glcmTrans135[$i][$j],2);
          if ($glcmTrans135[$i][$j]!=0) {
            $eksglcm135['entropy'] += -(log($glcmTrans135[$i][$j])*$glcmTrans135[$i][$j]);
            
            // $eksglcm135['korelasi'] += $glcmTrans135[$i][$j] * (($i-$glcmTrans135[$i][$j])*($j-$glcmTrans135[$i][$j]))/(pow($i-$glcmTrans135[$i][$j],2)*pow($j-$glcmTrans135[$i][$j],2));
          }
        } 
      }
      for ($x=0; $x < 16; $x++) {
        $imean0=0;
        $imean45=0;
        $imean90=0;
        $imean135=0;
        for ($y=0; $y < 16; $y++) {
          $imean0+=$glcmTrans0[$x][$y];
          $imean45+=$glcmTrans45[$x][$y];
          $imean90+=$glcmTrans90[$x][$y];
          $imean135+=$glcmTrans135[$x][$y];
        }
          $mean0[$x]=round($x*$imean0,10);
          $mean45[$x]=round($x*$imean45,10);
          $mean90[$x]=round($x*$imean90,10);
          $mean135[$x]=round($x*$imean135,10);
      }
      $meansum0=array_sum($mean0);
      $meansum45=array_sum($mean45);
      $meansum90=array_sum($mean90);
      $meansum135=array_sum($mean135);

      for ($x=0; $x < 16; $x++) {
        $var0=0;
        $var45=0;
        $var90=0;
        $var135=0;
        for ($y=0; $y < 16; $y++) {
        $var0+=$glcmTrans0[$x][$y]*pow($x-$meansum0,2);
        $var45+=$glcmTrans45[$x][$y]*pow($x-$meansum45,2);
        $var90+=$glcmTrans90[$x][$y]*pow($x-$meansum90,2);
        $var135+=$glcmTrans135[$x][$y]*pow($x-$meansum135,2);
        }
        $variance0[$x]=$var0;
        $variance45[$x]=$var45;
        $variance90[$x]=$var90;
        $variance135[$x]=$var135;
      }
      $vari0=array_sum($variance0);
      $vari45=array_sum($variance45);
      $vari90=array_sum($variance90);
      $vari135=array_sum($variance135);

      // $cor0;$cor45;$cor90;$cor135;
      for ($x=0; $x < 16; $x++) {
        for ($y=0; $y < 16; $y++) {
        $eksglcm0['korelasi']+=($glcmTrans0[$x][$y]*(($x-$meansum0)*($y-$meansum0)))/sqrt($vari0*$vari0);
        $eksglcm45['korelasi']+=($glcmTrans45[$x][$y]*(($x-$meansum45)*($y-$meansum45)))/sqrt($vari45*$vari45);
        $eksglcm90['korelasi']+=($glcmTrans90[$x][$y]*(($x-$meansum90)*($y-$meansum90)))/sqrt($vari90*$vari90);
        $eksglcm135['korelasi']+=($glcmTrans135[$x][$y]*(($x-$meansum135)*($y-$meansum135)))/sqrt($vari135*$vari135);
        }
      }
      

      $eksglcmavg['contras']=($eksglcm0['contras']+$eksglcm45['contras']+$eksglcm90['contras']+$eksglcm135['contras'])/4;
      $eksglcmavg['homogen']=($eksglcm0['homogen']+$eksglcm45['homogen']+$eksglcm90['homogen']+$eksglcm135['homogen'])/4;
      $eksglcmavg['energy']=($eksglcm0['energy']+$eksglcm45['energy']+$eksglcm90['energy']+$eksglcm135['energy'])/4;
      $eksglcmavg['entropy']=($eksglcm0['entropy']+$eksglcm45['entropy']+$eksglcm90['entropy']+$eksglcm135['entropy'])/4;
      $eksglcmavg['korelasi']=($eksglcm0['korelasi']+$eksglcm45['korelasi']+$eksglcm90['korelasi']+$eksglcm135['korelasi'])/4;

      return array('glcm0'=>$eksglcm0,'glcm45'=>$eksglcm45,'glcm90'=>$eksglcm90,'glcm135'=>$eksglcm135,'glcmavg'=>$eksglcmavg);
    }

  function quantisasi16($g){
    $qu = floor($g/16);    
    return $qu; 
  }

    function histo($im,$imgw,$imgh)
    {
            for ($i=0; $i <256 ; $i++) { 
                    $histo[$i] = 0;
            }

            for ($i=0; $i<$imgw; $i++)
                    {
                    for ($j=0; $j<$imgh; $j++)
                    {
                            $rgb = ImageColorAt($im, $i, $j); 
                            $rr = ($rgb >> 16) & 0xFF;
                            $gg = ($rgb >> 8) & 0xFF;
                            $bb = $rgb & 0xFF;
                            $g = round(($rr + $gg + $bb) / 3);
                            $histo[$g]+=1;
                    }
            }
            return $histo;
    }
    function outsuOut($arrayHisto)
      {
          $otsuValue = 130;
          $fmax = -1.0;
          $m1;
          $m2;
          $s;
          $toplam1 = 0.0;
          $toplam2 = 0.0;
          $nTop = 0;
          $n1 = 0;
          $n2;

          for ($i =0; $i < 256; $i++) {
                $toplam1 += $i*floatval($arrayHisto[$i]);
                $nTop += floatval($arrayHisto[$i]);
              }

              for ($i =0; $i < 256; $i++) {
                $n1 += floatval($arrayHisto[$i]);
                if ($n1==0)
                {

                }
                $n2 = $nTop - $n1;
                if ($n2==0)
                {
                  break;
                }
                $toplam2 += $i*floatval($arrayHisto[$i]);
                $m1 = $toplam2 / ($n1+0.00000000000000001);
                $m2 = ($toplam1 - $toplam2) / $n2;
                $s = $n1*$n2*($m1-$m2)*($m1-$m2);

                if ($s>$fmax)
                {
                  $fmax = $s;
                  $otsuValue = $i;
                }
            }
            return $otsuValue;
      }
}
?>
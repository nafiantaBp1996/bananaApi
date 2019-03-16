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
                   'kematangan' => $this->post('kematanganInp'),
                   'prediksi' => $this->post('prediksiInp'));
      $this->load->model('Dataset_model');
      $this->Dataset_model->insertDataset($data);
      $this->response(array('status' => "input sukses"), 200);
    }

    function index_get() {
        $images = $this->get('image');
        if ($images==null) {
          $this->response(array('status' => 'no Image'));
        }
        else{
          $im = ImageCreateFromJpeg($images); 
          $imgw = imagesx($im);
          $imgh = imagesy($im);
          $tres = $this->tres($im,$imgw,$imgh,100);
          $hsi = $this->getHsi($im,$imgw,$imgh);
          $this->response($hsi, 200);
        }
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
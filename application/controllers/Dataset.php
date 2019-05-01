<?php
 
require APPPATH . '/libraries/REST_Controller.php';
 
class Dataset extends REST_Controller {
    function __construct($config = 'rest') {
        parent::__construct($config);
    }

    function index_post() {
        $images = $this->post('string_encoded');

        $images_name = $this->post('name');

        $decodeString = base64_decode($images);
        $this->load->helper('file');

        if (write_file('./assets/dataset/'.$images_name, $decodeString)){
                $im = ImageCreateFromJpeg('./assets/dataset/'.$images_name);
                $w = imagesx($im); //current width
                $h = imagesy($im);
                imagedestroy($im);
                if ($w>$h) {
                    $img =$this->resize_image_max('./assets/dataset/'.$images_name, 300,224 );
                }
                else
                {
                    $img =$this->resize_image_max('./assets/dataset/'.$images_name, 224,300 );
                }
                
                imagejpeg($img,'./assets/dataset/'.$images_name);
                
                $this->response(array(
                "status"=>"sukses",
                "filename"=>base_url().'assets/dataset/'.$images_name), 200);
            }
            else{
                $this->response(array(
                "status"=>"Failed Try Again"), 200);
            }
    }

    function index_get(){
        $kat=$this->get('kategori');
        $this->load->model('Dataset_Model');
        $data = $this->Dataset_Model->loadDataset($kat);
        $this->response($data, 200);
    }
    ///////////////function tambahan
    function resize_image_max($image,$max_width,$max_height) {
        $im = ImageCreateFromJpeg($image);
        $w = imagesx($im); //current width
        $h = imagesy($im); //current height
        if ((!$w) || (!$h)) { $GLOBALS['errors'][] = 'Image couldn\'t be resized because it wasn\'t a valid image.'; return false; }

        if (($w <= $max_width) && ($h <= $max_height)) { return $im; } //no resizing needed
        
        else{
            //try max width first...
            $ratio = $max_width / $w;
            $new_w = $max_width;
            $new_h = $h * $ratio;
            
            //if that didn't work
            if ($new_h > $max_height) {
                $ratio = $max_height / $h;
                $new_h = $max_height;
                $new_w = $w * $ratio;
            }
            
            $new_image = imagecreatetruecolor ($new_w, $new_h);
            imagecopyresampled($new_image,$im, 0, 0, 0, 0, $new_w, $new_h, $w, $h);
            return $new_image;
        }
    }
}
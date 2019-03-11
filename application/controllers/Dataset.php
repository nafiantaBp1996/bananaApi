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

        if (write_file('./assets/dataset/'.$images_name, $decodeString))
                {
                    $this->response(array(
                    "status"=>"sukses",
                    "filename"=>base_url().'assets/dataset/'.$images_name), 200);
                }
                else
                {
                    $this->response(array(
                    "status"=>"Failed Try Again"), 200);
                }
    }

    function index_get() {
        $this->response(array(
            "image"=>"hai.jpg",
            ), 200);
    }
}
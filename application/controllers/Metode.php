<?php
 
require APPPATH . '/libraries/REST_Controller.php';
 
class Metode extends REST_Controller 
{
  function __construct($config = 'rest') {
      parent::__construct($config);
  }

  function index_post() {
    $id =$this->post('id_metode');
    $data = array('nilai' =>$this->post('nilai'));
    $this->load->model('metode_model');
    $in=$this->metode_model->updateMetode($id,$data);
    $this->response(array('status' => $in), 200);
  }
  function index_get() {
    $this->load->model('metode_model');
    $in=$this->metode_model->loadmetode();
    $this->response($in, 200);
  }
}
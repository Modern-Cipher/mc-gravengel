<?php
class PagesController extends Controller {
    public function __construct(){
      
    }
    
    public function index(){
        $data = [
            'title' => 'MC-Gravengel',
            'description' => 'Smart Records. Sacred Grounds.'
        ];
       
        $this->view('pages/index', $data);
    }

// /pages/qr_scanner  (public)
public function qr_scanner() {
    $this->view('pages/qr_scanner');
}



}
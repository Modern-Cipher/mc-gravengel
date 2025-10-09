<?php
/*
 * Base Controller - Loads the models and views
 */
class Controller {
    // [BAGO] Dito natin ilalagay ang mga URL parameters
    public $params = [];

    // [BAGO] Tatanggapin na ng constructor ang params mula sa Core.php
    public function __construct($params = []) {
        $this->params = $params;
    }

    // Function para i-load ang model
    public function model($model){
      require_once APPROOT . '/models/' . $model . '.php';
      return new $model();
    }

    // Function para i-load ang view
    public function view($view, $data = [], $return_content = false){
      if(file_exists(APPROOT . '/views/' . $view . '.php')){
        if($return_content){
            ob_start();
            require_once APPROOT . '/views/' . $view . '.php';
            return ob_get_clean();
        } else {
            require_once APPROOT . '/views/' . $view . '.php';
        }
      } else {
        die('View does not exist: ' . $view);
      }
    }
}
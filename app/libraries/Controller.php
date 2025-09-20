<?php
/*
 * Base Controller - Loads the models and views
 */
class Controller {
    // Function para i-load ang model
    public function model($model){
      // GAGAMITIN NA NATIN ANG 'APPROOT' PARA SIGURADONG TAMA ANG PATH
      require_once APPROOT . '/models/' . $model . '.php';

      // Gumawa ng instance ng model
      return new $model();
    }

    // Function para i-load ang view
    public function view($view, $data = [], $return_content = false){
      // Tignan kung existing ang view file
      if(file_exists(APPROOT . '/views/' . $view . '.php')){
        if($return_content){
            // Gamitin ang output buffering para i-capture ang content ng view
            ob_start();
            require_once APPROOT . '/views/' . $view . '.php';
            return ob_get_clean();
        } else {
            require_once APPROOT . '/views/' . $view . '.php';
        }
      } else {
        // Kapag wala, itigil ang app
        die('View does not exist');
      }
    }
}
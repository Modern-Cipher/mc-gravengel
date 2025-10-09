<?php
/*
 * Base Router - Maps URL to controller methods
 * FINAL ARCHITECTURAL FIX
 */
class Core {
    protected $currentController = 'PagesController';
    protected $currentMethod = 'index';
    protected $params = []; 

    public function __construct(){
      $url = $this->getUrl();

      // Step 1: Tukuyin ang Controller
      $controllerName = $this->currentController;
      if(isset($url[0]) && file_exists('../app/controllers/' . ucwords($url[0]) . 'Controller.php')){
        $controllerName = ucwords($url[0]) . 'Controller';
        unset($url[0]);
      }
      require_once '../app/controllers/'. $controllerName . '.php';
      
      // Step 2: Kunin ang mga Parameters bago gumawa ng instance
      $this->params = $url ? array_values($url) : [];

      // Step 3: Gumawa ng instance ng Controller at IPASA ang params
      if (class_exists($controllerName)) {
          // Dito na ipinapasa ang params sa constructor
          $this->currentController = new $controllerName($this->params);
      } else {
          die("Fatal Error: Controller class '{$controllerName}' not found.");
      }

      // Step 4: Hanapin ang Method
      if(isset($url[1])){
        if(method_exists($this->currentController, $url[1])){
          $this->currentMethod = $url[1];
          unset($url[1]);
        }
        // I-recalculate ang params para sa method call (wala nang controller at method)
        $this->params = $url ? array_values($url) : [];
      } else {
        // Kung walang method sa URL, walang parameters para sa method
        $this->params = [];
      }
      
      // Step 5: Tawagin ang method na may tamang parameters
      call_user_func_array([$this->currentController, $this->currentMethod], $this->params);
    }

    public function getUrl(){
      if(isset($_GET['url'])){
        $url = rtrim($_GET['url'], '/');
        $url = filter_var($url, FILTER_SANITIZE_URL);
        $url = explode('/', $url);
        return $url;
      }
      return [];
    }
}
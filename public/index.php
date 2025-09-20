<?php
// I-load ang Config
require_once '../app/config/config.php';

// I-load ang autoloader ng Composer
require_once '../vendor/autoload.php';

// I-load ang mga Core Libraries
require_once '../app/libraries/Core.php';
require_once '../app/libraries/Controller.php';
require_once '../app/libraries/Database.php';
 require_once '../app/bootstrap.php';

// I-instantiate ang Core class (Router)
$init = new Core();
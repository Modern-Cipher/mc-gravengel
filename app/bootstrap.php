<?php
  // Load Config
  require_once 'config/config.php';

  // Load Helpers
  require_once 'helpers/url_helper.php';
  require_once 'helpers/Email.php'; // BAGONG LINYA

  // Autoload Core Libraries (FIXED)
  spl_autoload_register(function($className){
    $file = __DIR__ . '/libraries/' . $className . '.php';
    if (file_exists($file)) {
      require_once $file;
    }
  });

  // Load PHPMailer files
  // Load PHPMailer files
  require_once APPROOT . '/../vendor/autoload.php'; // BINAGO PARA SA TAMANG PATH

  // Start Session para sa SweetAlert
  if (session_status() === PHP_SESSION_NONE) {
      session_start();
  }
?>
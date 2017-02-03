<?php
require_once('config.php');

class Database {

  private static $instance;

  private function __construct() {
    if(!isset($instance)) {
      try {
        self::$instance = new PDO(
          'mysql:host=' . DB_SERVERNAME . ';dbname=' . DB_SCHEMA,
          DB_USERNAME,
          DB_PASSWORD
        );
      } catch(PDOException $e) {
        die($e->getMessage());
      }
    }
  }
  public static function getInstance() {
    if(!self::$instance) {
      new Database();
    }
    return self::$instance;
  }
}


?>

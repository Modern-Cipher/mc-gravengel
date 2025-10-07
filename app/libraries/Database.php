<?php
/*
 * PDO Database Class - Connect to database, create prepared statements, bind values, return rows and results
 */
class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;

    private $dbh; // database handler
    private $stmt;
    private $error;

    public function __construct(){
      $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname . ';charset=utf8mb4';
      $options = array(
        PDO::ATTR_PERSISTENT => true,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
      );

      try{
        $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
      } catch(PDOException $e){
        $this->error = $e->getMessage();
        echo $this->error;
      }
    }

    public function query($sql){
      $this->stmt = $this->dbh->prepare($sql);
    }

    public function bind($param, $value, $type = null){
      if(is_null($type)){
        switch(true){
          case is_int($value):
            $type = PDO::PARAM_INT;
            break;
          case is_bool($value):
            $type = PDO::PARAM_BOOL;
            break;
          case is_null($value):
            $type = PDO::PARAM_NULL;
            break;
          default:
            $type = PDO::PARAM_STR;
        }
      }
      $this->stmt->bindValue($param, $value, $type);
    }

    public function execute(){
      return $this->stmt->execute();
    }

    public function resultSet(){
      $this->execute();
      $results = $this->stmt->fetchAll(PDO::FETCH_OBJ);
      if ($this->stmt) {
          $this->stmt->closeCursor();
      }
      return $results;
    }

    public function single(){
      $this->execute();
      $result = $this->stmt->fetch(PDO::FETCH_OBJ);
      if ($this->stmt) {
          $this->stmt->closeCursor();
      }
      return $result;
    }
    
    /**
     * Safely quotes a string for use in a query.
     * This is the critical function for creating a valid backup.
     */
    public function quote($value) {
        return $this->dbh->quote($value);
    }

    public function lastInsertId(){
      return $this->dbh->lastInsertId();
    }

    public function rowCount(){
      return $this->stmt->rowCount();
    }
}
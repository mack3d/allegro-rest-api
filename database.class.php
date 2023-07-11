<?php
class db
{
    private $dsn = "mysql:host=localhost;dbname=satserwis";
    private $user = "root";
    private $pass = "";
    private $options = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,);
    protected $con;

    public function __construct() {
        try
        {
            $this->con = new PDO($this->dsn, $this->user,$this->pass,$this->options);
            return $this->con;
        }
        catch (PDOException $e)
        {
            echo "There is some problem in connection: " . $e->getMessage();
        }
    }

    public function close() {
        $this->con = null;
    }
}
<?php
class db
{
    private $dsn = "mysql:host=localhost;dbname=satserwis";
    private $user = "";
    private $pass = "";
    private $options = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,);
    protected $con;

    public function __construct()
    {
        $this->user = getenv('DBU');
        $this->pass = getenv('DBP');
        try {
            $this->con = new PDO($this->dsn, $this->user, $this->pass, $this->options);
            return $this->con;
        } catch (PDOException $e) {
            echo "There is some problem in connection: " . $e->getMessage();
        }
    }

    public function close()
    {
        $this->con = null;
    }
}


class DBconn
{
    private $host = '';
    private $username = '';
    private $password = '';
    private $database = '';

    private $conn;

    public function __construct()
    {
        $this->host = getenv('DBH');
        $this->database = getenv('DBN');
        $this->username = getenv('DBU');
        $this->password = getenv('DBP');
        try {
            $this->conn = new PDO("mysql:host={$this->host};dbname={$this->database}", $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo 'Connection failed: ' . $e->getMessage();
        }
    }

    public function query($sql)
    {
        return $this->conn->query($sql);
    }

    public function exec($sql)
    {
        return $this->conn->exec($sql);
    }

    public function prepare($sql)
    {
        return $this->conn->prepare($sql);
    }

    public function bindValue($stmt, $param, $value, $type = null)
    {
        if ($type === null) {
            switch (true) {
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
        $stmt->bindValue($param, $value, $type);
    }

    public function bindParam($stmt, $param, &$variable, $type = PDO::PARAM_STR)
    {
        $stmt->bindParam($param, $variable, $type);
    }

    public function close()
    {
        $this->conn = null;
    }
}

<?php
class database
{
        protected $config;
        private $connection;
        private $pdoString;
        
        function __construct()
        {
                $this->config = (object) parse_ini_file('config.ini');
                $this->pdoString = $this->config->db_type;
                $this->pdoString .= ':dbname='.$this->config->db_name;
                $this->pdoString .= ';host='.$this->config->db_host;                
                try
                {
                        $this->connection = new PDO($this->pdoString, $this->config->db_username, $this->config->db_password);
                        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                }
                catch(PDOException $e)
                {
                        echo 'ERROR: ' . $e->getMessage();
                }
        }

        function query($q)
        {
                try
                {
                        $stmt = $this->connection->prepare($q);
                        $stmt->execute();
                        return $stmt->fetchAll(PDO::FETCH_OBJ);
                }
                catch(PDOException $e)
                {
                        echo 'ERROR: ' . $e->getMessage();
                }
        }
        
        function __destruct()
        {
                $this->connection = NULL;
        }
}
?>
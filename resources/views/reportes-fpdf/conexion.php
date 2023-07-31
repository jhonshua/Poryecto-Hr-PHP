<?php
//    require __DIR__.'/../vendor/autoload.php';
//    $dotenv = Dotenv\Dotenv::create(__DIR__.'/../../');
//    $dotenv->load();

    class Database {
        private $connection;
        private $servername;
        private $username;
        private $password;
        private $dbname;
        private static $instance;

        function __construct($db) {
            /*$this->servername = 'localhost';       
            $this->username = 'root';          
            $this->password = 'root';*/

            $this->servername = '34.68.93.200';         
            $this->username = 'Singh';           
            $this->password = 't*PcjmJGQ63';

            $this->dbname = $db;

            $this->connection = mysqli_connect($this->servername,$this->username,$this->password,$this->dbname);

            if (mysqli_connect_errno()) {
				var_dump(mysqli_connect_errno());
                $this->connection = null;
            }
        }

        public function conexion(){
            return $this->connection;
        }

        public function command($cmd) {
            $rowresultresulbase = null;
            if ($this->connection) {
                $rowquerybase=mysqli_query($this->connection, $cmd);
                /*if($_SESSION['email']=='desarrollo@singh.com.mx'){
                echo $cmd.'<br>';
                }*
                echo $cmd.'<br>$%$%$$%';
                 var_dump($cmd);
                 var_dump($rowquerybase);
                 */
               // $rowresultresulbase=mysqli_fetch_array($rowquerybase);
                if($rowquerybase){
                    $rowresultresulbase=mysqli_fetch_array($rowquerybase);
                }
            }
            return $rowresultresulbase;
        }

        public function ejecuta($cmd) {
            $resul = null;
            if ($this->connection) {
                if(mysqli_query($this->connection, $cmd)){
                $resul=1;
                }else{
                $resul=0;
                }
                return $resul;
            }
        }


        public function query($cmd) {
            $rowquerybase = null;
            if ($this->connection) {
                $rowquerybase=mysqli_query($this->connection, $cmd);
            }

            return $rowquerybase;
        }


        /* public function __clone() { }*/

   }     /* Database */

?>

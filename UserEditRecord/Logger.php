<?php
include("/var/www/services/log4php-2.3.0/Logger.php");
Logger::configure("log-config.xml");
class EditRecordLogger{

     private $log;
   
     public function __construct()
     {
	$this->log = Logger::getLogger("myLogger");

     }


     public function record($message)
     {
        $this->log->info($message);
     }
}
$logger = new EditRecordlogger();
$logger->record("Logger.php");

?>


<?php

/**
 * PHPIRC Class
 *
 * A Simple IRC Bot
 *
 * @author		Ferdinand E. Silva (six519@phpugph.com)
 * @version		Version 1.0
 */

class PHPIRC {

	private $IrcServer = "";
	private $IrcPort = 6667;
    

	private $IrcNick = "";
	private $IrcRoom = "";

	private $socket;
	private $isConnected = false;
	private $isAuthenticated = false;


	public function __construct() {
		$this->main();
	}

	private function main() {
 		//get IRC Server
		$this->IrcServer = $this->getUserInput("Please Enter Irc Server Address");

		//get Port
		if($this->getUserInput("Do You Want To Change The Irc Port? The Default Port is " . $this->IrcPort . ". Enter y to change") == "y") {
			$this->IrcPort = (int)$this->getUserInput("Please Enter Port Number");
		}

		//get Nick
		$this->IrcNick = $this->getUserInput("Please Enter Irc Nick");
 		//get Irc Channel
		$this->IrcRoom = $this->getUserInput("Please Enter Irc Channel");

		$this->connect(); //connect to irc server
	
	}

	private function getUserInput($msg) {
		$endInput = false;

 		while(!$endInput) {
 			echo "\n" . $msg . ": ";
  			$handle = fopen ("php://stdin","r");
			$line = fgets($handle);

			if(trim($line) != "") {
				$endInput = true;
				return trim($line);
 			}

			fclose($handle);
		}
    
	}

	private function connect() {
        
		$this->socket = socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
        
		if(@socket_connect($this->socket, $this->IrcServer,$this->IrcPort)) {
 			//connected
			//read messages
			$this->isConnected = true;    
 			$this->receiveMessages();
            
		} else { 
  			//disconnected
   		echo "Cannot Connect To Server. ";
     		echo socket_strerror(socket_last_error($this->socket)) . ".";

    		//restart
     		if($this->getUserInput("Restart Application? Enter y to restart") == "y") {
     			$this->main();
  			}
		}
	}

	private function receiveMessages() {

		while($this->isConnected) {

    		$buffer = "";
   		$flag = socket_recv($this->socket, $buffer, 1024,0);

			if($flag < 0) {
       		//error
     		}elseif($flag == 0) {
      		//disconnected
         	$this->isConnected = false;
         	$this->isAuthenticated = false;
         	echo "\nClient Disconnected.\n";
      	}else{
        		//messages
        		echo $buffer;

      		if(preg_match("/Checking Ident/",$buffer) && !$this->isAuthenticated) {
         		$this->sendMessage("NICK " . $this->IrcNick . "\r\n");
     				$this->sendMessage("USER " . $this->IrcNick . " \"" . $this->IrcNick . ".com\" \"" . $this->IrcServer . "\" :" . $this->IrcNick . " robot\r\n");
      		}elseif(preg_match("/Nickname is already in use/",$buffer) && !$this->isAuthenticated) {
          		$this->IrcNick=$this->getUserInput("Please Enter New Irc Nick");
      			$this->sendMessage("NICK " . $this->IrcNick . "\r\n");
    				$this->sendMessage("USER " . $this->IrcNick . " \"" . $this->IrcNick . ".com\" \"" . $this->IrcServer . "\" :" . $this->IrcNick . " robot\r\n");
      		}elseif(preg_match("/Erroneous Nickname/",$buffer) && !$this->isAuthenticated) {
    				$this->IrcNick=$this->getUserInput("Please Enter New Irc Nick");
     				$this->sendMessage("NICK " . $this->IrcNick . "\r\n");
    				$this->sendMessage("USER " . $this->IrcNick . " \"" . $this->IrcNick . ".com\" \"" . $this->IrcServer . "\" :" . $this->IrcNick . " robot\r\n");
    			}elseif(preg_match("/This nickname is registered/",$buffer) && !$this->isAuthenticated) {
     				$this->IrcNick=$this->getUserInput("Please Enter New Irc Nick");
     				$this->sendMessage("NICK " . $this->IrcNick . "\r\n");
     				$this->sendMessage("USER " . $this->IrcNick . " \"" . $this->IrcNick . ".com\" \"" . $this->IrcServer . "\" :" . $this->IrcNick . " robot\r\n");
       		}elseif(preg_match("/End of \/MOTD command/",$buffer) && !$this->isAuthenticated) {
     				$this->isAuthenticated=true;
    				$this->sendMessage("JOIN #" . $this->IrcRoom . "\r\n");
      		}elseif(preg_match("/PING :/",$buffer)) {
          		$this->sendMessage(preg_replace("/PING/", "PONG", $buffer) . "\r\n");
				}elseif(preg_match("/PRIVMSG \#" . $this->IrcRoom . "/i", $buffer)) {
       			//room message
         		//dito ilalagay yung mga commands
                    					
      			//add command handler below
					
					
         		//end of command handler
					                    
				}elseif(preg_match("/JOIN :\#" . $this->IrcRoom . "/i", $buffer)) {
					$nick = "";
					$this->extractNickAndMessage($buffer,$nick);
					
					if($this->IrcNick != $nick) {
						$this->sendMessage("PRIVMSG #" . $this->IrcRoom . " :Magandang " . $this->getMeridiem() . " sa iyo " . $nick	. "\r\n");	
					}
				}

			}
		}
	}
	
	private function extractNickAndMessage($str,&$nick,&$message = "") {
		$tmpStr = preg_split("/:/", $str);
		$tmpStr = preg_split("/!/", $tmpStr[1]);
  		$nick = $tmpStr[0]; //nick of the sender
					
   	$tmpStr = preg_split("/PRIVMSG #" . $this->IrcRoom . " :/i", $str); //got to fix the PRIVMSG thingy.... (add the join.. etc.. ) 
   	$message = (isset($tmpStr[1]))?$tmpStr[1]:""; //message received

   	$tmpStr = NULL;
   	$nickSender = "";
  		$messageReceived = "";   				
	}

	private function sendMessage($msg) {
		socket_write($this->socket,$msg,strlen($msg));
	}


	private function getMeridiem() {

		$nowHour = date("H");

		if($nowHour == 0) {
			return "hatinggabi";
		} elseif($nowHour >= 1 && 	$nowHour <= 5) {
			return "madaling araw";
		} elseif($nowHour >=6 && $nowHour <= 11) {
			return "umaga";
		} elseif($nowHour == 12) {
			return "tanghali";
		} elseif($nowHour >= 13 && $nowHour <= 17) {
			return "hapon";
		} elseif($nowHour >= 18 && $nowHour <= 23) {
			return "gabi";
		}
	
	}

}


//run PHPIRC
$run = new PHPIRC();

?>

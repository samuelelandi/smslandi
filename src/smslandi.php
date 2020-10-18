<?php
//**************************************************************************
//** GATEWAY SERVER TO FORWARD TEXT MESSAGES BY SIP TO ASTERISK EXTENSIONS
//**************************************************************************

// Check dependencies
if( ! extension_loaded('sockets' ) ) {
	echo "This program requires sockets extension for php\n";
	exit(-1);
}
if( ! extension_loaded('pcntl' ) ) {
	echo "This program  requires PCNTL extension for php\n";
	exit(-1);
}
// Socket opening on 127.0.0.1 port 4444
$server = new SocketServer();
$server->init();
$server->setConnectionHandler( 'onConnect' );
$server->listen();

//Main loop after the connectio
function onConnect( $client ) {
	// fork the process to allow multiple connections 
	$pid = pcntl_fork();
	if ($pid == -1) {
		 die('could not fork');
	} else if ($pid) {
		// parent process
		return;
	}
	$read = '';
	printf( "[%s] Connected at port %d\n", $client->getAddress(), $client->getPort() );
	
	while( true ) {
		// read the GET request
		$read = $client->read();
		if( $read == '' )  break;
		if( $read === null ) {
			printf( "[%s] Disconnected 001\n", $client->getAddress() );
			break;
		}
		else {
			printf( "[%s] received: %s", $client->getAddress(), $read );
		}
		// process the GET request
		if(strstr($read,"Host:")!=NULL){
			$answer=send_text_message($read);
			echo "Answering: ".$answer."\n";
			$client->send($answer);		
			sleep(1);
			break;	
		}
		// end processing GET request
	}
	$client->close();
	printf( "[%s] Disconnected 002\n", $client->getAddress() );
	exit(true);
	
}
//*** FUNCTION TO SEND THE TEXT MESSAGE BY SIP
function send_text_message($read){
	// CUSTOMIZATION
	$ASTERISKSIPDESTINATION='103.51.3.219:5073';
	$ASTERISKSIPORIGIN='128.199.249.184:5073';
	$ASTERISKAMIIP='127.0.0.1';
	$ASTERISKAMIPORT='5038';
	$ASTERISKAMIUSER='amiuser';
	$ASTERISKAMIPWD='qwaszx12';
	// END CUSTOMIZATION	
	// GET THE DATA
	$x=strpos($read,"GET /mt/");
	$rr=substr($read,$x+8);
	$x=strpos($rr," HTTP/1.1");
	$r=substr($rr,0,$x);
	$v=array();
	parse_str($r,$v);
	$msgid=$v['msgid'];
	$to=str_replace("+","",$v['to']);
	$from=substr(md5($v['msgid']),0,10)."-".str_replace("+","",$v['from']);
	$txt=$v['txt'];
	$hash=substr(md5($v['msgid']),0,10);
	file_put_contents("/tmp/".$hash,$v['msgid']);
	$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
	if ($socket === false) echo "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
	$result = socket_connect($socket, $ASTERISKAMIIP, $ASTERISKAMIPORT);
	if ($result === false) echo "socket_connect() failed.\nReason: ($result) " . socket_strerror(socket_last_error($socket)) . "\n";
	// authenticaition to AMI	
	echo "0100 - Authenticating to AMI\n";
	$am="Action: Login\n";
	$am.="Username: ".$ASTERISKAMIUSER."\n";
	$am.="Secret: ".$ASTERISKAMIPWD."\n";
	$am.="\n";
	echo $am;
	socket_write($socket, $am, strlen($am));
	sleep(1);
	$r= socket_read($socket, 2048);
	echo "Answer to Authentication: ".$r."\n";
	
	echo "0200 - Sending Text Message to AMI\n";
	$t="Action: MessageSend\n";
	$t.="ActionID: ".$msgid."\n";
	$t.="To: sip:".$to."@".$ASTERISKSIPDESTINATION."\n";
	//$t.='From: sip:'.$from."@".$ASTERISKSIPORIGIN."\n";
	$t.='From: "'.$from.'"<sip:'.$from."@".$ASTERISKSIPORIGIN.">\n";
	$t.="Body: ".$txt."\n";
	$t.="\n";
	echo $t;
	socket_write($socket, $t, strlen($t));
        sleep(1);
	$r= socket_read($socket, 2048);
        echo "Answer to text message sending: ".$r."\n";
	socket_close($socket);
	// generate http answer
	$a="";
	$a="HTTP/1.0 200 OK\r\n";
	$a.="Date: ".gmdate('D, d M Y H:i:s T')."\r\n";
	$a.="Server: Apache/2.2.14\r\n";
	$a.="Last-Modified: ".gmdate('D, d M Y H:i:s T')."\r\n";
	$a.="Content-Length: ".(12+strlen($msgid))."\r\n";
	$a.="Content-Type: text/html; charset=UTF-8\r\n\r\n";
	$a.="ok <id>".$msgid."</id>";
	$a.="\n";
	echo "0300 - sending Http answer\n";
	echo $a;
	return($a);
}
//END FUNCTION TO SENT TEXT MESSAGE

//************************************************************
// SOCKET CLASS
//************************************************************
class SocketClient {

	private $connection;
	private $address;
	private $port;

	public function __construct( $connection ) {
		$address = ''; 
		$port = '';
		socket_getsockname($connection, $address, $port);
		$this->address = $address;
		$this->port = $port;
		$this->connection = $connection;
	}
	
	public function send( $message ) {	
		socket_write($this->connection, $message, strlen($message));
	}
	
	public function read($len = 1024) {
		if ( ( $buf = @socket_read( $this->connection, $len, PHP_BINARY_READ  ) ) === false ) {
				return null;
		}
		
		return $buf;
	}

	public function getAddress() {
		return $this->address;
	}
	
	public function getPort() {
		return $this->port;
	}
	
	public function close() {
		socket_shutdown( $this->connection );
		socket_close( $this->connection );
	}
}

class SocketException extends \Exception {

	const CANT_CREATE_SOCKET = 1;
	const CANT_BIND_SOCKET = 2;
	const CANT_LISTEN = 3;
	const CANT_ACCEPT = 4;
	
	public $messages = array(
		self::CANT_CREATE_SOCKET => 'Can\'t create socket: "%s"',
		self::CANT_BIND_SOCKET => 'Can\'t bind socket: "%s"',
		self::CANT_LISTEN => 'Can\'t listen: "%s"',
		self::CANT_ACCEPT => 'Can\'t accept connections: "%s"',
	);
	
	public function __construct( $code, $params = false ) {
		if( $params ) {
			$args = array( $this->messages[ $code ], $params );
			$message = call_user_func_array('sprintf', $args );
		}
		else {
			$message = $this->messages[ $code ];
		}
		
		parent::__construct( $message, $code );
	}
}
class SocketServer {
	
	protected $sockServer;
	protected $address;
	protected $port;
	protected $_listenLoop;
	protected $connectionHandler;
	
	public function __construct( $port = 4444, $address = '0.0.0.0' ) {
		$this->address = $address;
		$this->port = $port;
		$this->_listenLoop = false;
	}
	
	public function init() {
		$this->_createSocket();
		$this->_bindSocket();
	}
	
	private function _createSocket() {
		$this->sockServer = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if( $this->sockServer === false ) {
			throw new SocketException( 
				SocketException::CANT_CREATE_SOCKET, 
				socket_strerror(socket_last_error()) );
		}
		
		socket_set_option($this->sockServer, SOL_SOCKET, SO_REUSEADDR, 1);
	}
	
	private function _bindSocket() {
		if( socket_bind($this->sockServer, $this->address, $this->port) === false ) {
			throw new SocketException( 
				SocketException::CANT_BIND_SOCKET, 
				socket_strerror(socket_last_error( $this->sockServer ) ) );
		}
	}
	
	public function setConnectionHandler( $handler ) {
		$this->connectionHandler = $handler;
	}
	
	public function listen() {
		if( socket_listen($this->sockServer, 5) === false) {
			throw new SocketException( 
				SocketException::CANT_LISTEN, 
				socket_strerror(socket_last_error( $this->sockServer ) ) );
		}

		$this->_listenLoop = true;
		$this->beforeServerLoop();
		$this->serverLoop();
		
		socket_close( $this->sockServer );
	}
	
	protected function beforeServerLoop() {
		printf( "Listening on %s:%d...\n", $this->address, $this->port );
	}
	
	protected function serverLoop() {
		while( $this->_listenLoop ) {
		        $status=0;
		        pcntl_wait($status,WNOHANG);
			if( ( $client = @socket_accept( $this->sockServer ) ) === false ) {
				throw new SocketException(
						SocketException::CANT_ACCEPT,
						socket_strerror(socket_last_error( $this->sockServer ) ) );
				continue;
			}
				
			$socketClient = new SocketClient( $client );
				
			if( is_array( $this->connectionHandler ) ) {
				$object = $this->connectionHandler[0];
				$method = $this->connectionHandler[1];
				$object->$method( $socketClient );
			}
			else {
				$function = $this->connectionHandler;
				$function( $socketClient );
			}
		}
	}

}




?>
<?php
/**
 * Author:  Jonathan Galpin - jonathan@iqzero.net, May 2014
 * 
 * Feb 2014
 * Description: denkovi controller's primary interface
 * 
 * $hardware_type			-- DENKOVI_RELAY, DENKOVI_INPUT_ANALOG, DENKOVI_INPUT_DIGITAL
 * $device_number			-- 1-16 for relays, 1-8 for inputs		
 * $board_ip				-- 
 * board_port				-- default is 1010
 * $denkovi_serial_address	-- default is 00 
 * 
 * do not use this class directly, rather, use the relay class for relays, see the README
 * 
 */
require_once 'config.php';

class denkovi_controller
{
	/**
	 * DENKOVI_RELAY, DENKOVI_INPUT_ANALOG, DENKOVI_INPUT_DIGITAL
	 * 
	 * set in config 
	 * 
	 * @var type int
	 */
	private $hardware_type;

	/**
	 * 
	 * device identifiers correspond to Denkovi ethernet board DAEnetIP3 
	 * serially attached to a 12 relay/8 analog input/8 digital input board --- DAE-PB-RO12/DI8/AI8
	 * serially attached to a 4 relay board --- DAE-Ro4-12V
	 * 
	 * device identifier...internal identifier [ 0-9 A,B,C,D,E,F ] - in this case, relay 1-16 equates to device 0-9, then A,B,C,D,E,F
	 * 
	 * set the device_identifier using a number, the device_number 1 - 16 also referd to as relay number 1-16
	 * 
	 * @var type varchar
	 */
	private $board_device_identifier;

	/**
	 * 3 bytes, denkovi function code
	 */
	private $board_device_function_code;    

	/**
	 * default is 192.168.0.100
	 */
	private $board_ip;

	/**
	 * default is 1010
	 */
	private $board_port;

	/**
	 * default is 00
	 */
	private $board_serial_address;

	/**
	 * defined as DENKOVI_BOARD_NOT_AVAILABLE_FOR_USE in config, set to TRUE for testing purposes when the board is not available 
	 * @var type boolean, set in config
	 */
	private $is_board_in_test_mode;			

	/**
	 * used when the board is in test mode
	 * @var type int
	 */
	private $current_relay_state;

	/**
	 * @var type varchar
	 */
	private	$name;

	/**
	 * holds last error information
	 * @var type varchar
	 */
	public  $last_error;  

	public function __construct() 
	{
		if( defined('DENKOVI_BOARD_IP') ) $this->board_ip = DENKOVI_BOARD_IP;	
		$this->board_port = '1010';												// factory default
		$this->board_serial_address = '00';										// factory default
		$this->current_relay_state = FALSE;										// off
		$this->name = '';

		$this->is_board_in_test_mode = DENKOVI_BOARD_NOT_AVAILABLE_FOR_USE;
	}

	/**
	 * returns the device identifier, 0-9 A,B,C,D,E,F
	 * @return type int
	 */
	public function get_board_device_identifier()
	{
		$this->is_board_ready();

		return $this->board_device_identifier;
	}
	
	/**
	 * returns the number of the relay set by the user 1-16
	 * @return int
	 */
	public function get_board_device_number()
	{
		$this->is_board_ready();

		if( is_integer( $this->board_device_identifier ) ) return $this->board_device_identifier + 1;

		switch( $this->board_device_identifier ) // string identifier
		{
			case 'A': return 11;
			case 'B': return 12;
			case 'C': return 13;
			case 'D': return 14;
			case 'E': return 15;
			case 'F': return 16;
		}		
	}
	
	public function get_device_name()
	{
		return $this->name;
	}

	public function get_hardware_type()
	{
		return $this->hardware_type;
	}
	
	// to test
	public function get_analog_digital_input_reading()
	{
		$this->is_board_ready();

		// NOT available to a relay
		if( $this->hardware_type == DENKOVI_RELAY )            throw new Exception( "This is not an analog or digital input device." );

		// init last error
		$this->last_error = '';

		// build the command string
		$command = $this->board_serial_address . $this->board_device_function_code . $this->board_device_identifier . "=";

		try 
		{
			$answer = $this->send_command_and_get_reply($command . "?;");
		} 
		catch( Exception $e )
		{
			throw $e;
		}

		if( $this->hardware_type == DENKOVI_INPUT_DIGITAL )
		{
			// digital
			// open closed, on off type answer
			switch( $answer )
			{
				case $command . "0;": return 0;		// 0 = connection is open ( switch OFF )
				case $command . "1;": return 1;		// 1 = connection is closed, button pressed etc ( switch ON )
				default:
					$this->last_error = $answer_from_board;
					throw new Exception( $answer_from_board );    // error           
			}
		}
		else
		{
			// analog
			// temp style sensor
			// remove the semicolon
			$answer = str_replace( ";", "", $answer );

			//separate out the parts
			$answer = explode( "=", $answer );

			//send the reading back
			if( isset( $answer[1] ) )
			{
				return $answer[1];
			}
			else
			{
				$this->last_error = "Analog Answer Not Set";
				throw new Exception( "Analog Answer Not Set" );    // error 
			}
		}
	}

	public function get_is_board_in_test_mode()
	{
		return $this->is_board_in_test_mode;
	}

	public function set_relay_on()
	{
		return $this->set_relay_state_on_or_off( TRUE );
	}

	public function set_relay_off()
	{
		return $this->set_relay_state_on_or_off( FALSE );
	}

	/**
	 * three hardware types: DENKOVI_RELAY, DENKOVI_INPUT_ANALOG, DENKOVI_INPUT_DIGITAL
	 * @param type $hardware_type
	 * @throws Exception
	 */
	public function set_hardware_type( $hardware_type )
	{
		// whitelist the hardware type
		switch( $hardware_type )
		{
			case DENKOVI_RELAY: 
				$this->board_device_function_code = 'AS';
				break;
			case DENKOVI_INPUT_ANALOG: 
				$this->board_device_function_code = 'CV';
				break;
			case DENKOVI_INPUT_DIGITAL: 
				$this->board_device_function_code = 'BV';
				break;
			default:
				throw new Exception( "Device $hardware_type unknown!" );
		}

		// set the device type
		$this->hardware_type = $hardware_type;
	}

	/**
	 * 1-16 for relays, 1-8 for inputs - depends on your board configuration
	 * 
	 * @param type $device_number
	 * @throws Exception
	 */
	public function set_board_device_identifier_from_device_number( $device_number )
	{
		if( (int)$device_number < 1 || (int)$device_number > 16 )		throw new Exception("Device ID's must be between 1 and 16.");	// specific to board configuration

		if( (int)$device_number < 11 )
		{
			$this->board_device_identifier = (int)$device_number - 1;	// devices are zero based
		}
		else
		{
			switch( (int)$device_number )
			{
				case 11: $this->board_device_identifier = 'A'; break;
				case 12: $this->board_device_identifier = 'B'; break;
				case 13: $this->board_device_identifier = 'C'; break;
				case 14: $this->board_device_identifier = 'D'; break;
				case 15: $this->board_device_identifier = 'E'; break;
				case 16: $this->board_device_identifier = 'F'; break;
			}
		}		
	}

	public function set_device_name( $friendly_name )
	{
		if( !gettype( $friendly_name ) == "string" )			throw new Exception( "Please provide a string for the name." );
		$this->name = $friendly_name;
	}

	public function set_board_ip( $ip_address )
	{
		if( !filter_var( $ip_address, FILTER_VALIDATE_IP ) )	throw new Exception( "Please provide a valid IP address." );
		$this->board_ip = $ip_address;			
	}

	public function set_board_port( $board_port )
	{
		if( !gettype( $board_port ) == "string" )				throw new Exception( "Please provide a string for the board port." );
		$this->board_port = $board_port;
	}

	public function set_board_serial_address( $serial_address )
	{
		if( !gettype( $serial_address ) == "string" )			throw new Exception( "Please provide a string for the serial address." );
		$this->board_serial_address = $serial_address;	
	}

	public function set_board_test_mode_on()
	{
		$this->is_board_in_test_mode = TRUE;
	}

	public function set_board_test_mode_off()
	{
		$this->is_board_in_test_mode = FALSE;
	}
	
	/**
	 * returns the current state of a relay
	 * 
	 * 1 = TRUE = on, 0 = FALSE = off
	 * 
	 * if not 1/0, the last error is set
	 * 
	 * @return string|1|1 
	 */
	public function get_relay_state_on_or_off()
	{
		$this->is_board_ready();

		// only available to a relay
		if( $this->hardware_type != DENKOVI_RELAY )            throw new Exception("This is not a relay.");

		// init last error
		$this->last_error = '';

		// build the command string
		$command_to_board = $this->board_serial_address . $this->board_device_function_code . $this->board_device_identifier . "=";

		try 
		{
			$answer_from_board = $this->send_command_and_get_reply( $command_to_board . "?;" );
		}
		catch( Exception $e )
		{
			throw $e;
		}

		switch ( $answer_from_board )
		{
			case $command_to_board . "0;": return 0;	// relay is off
			case $command_to_board . "1;": return 1;	// relay is on
			default:
				if( $this->is_board_in_test_mode ) return ( $this->current_relay_state ? 1 : 0 );				// not an error

				$this->last_error = $answer_from_board;
				throw new Exception( "The answer from the board was not understood: " . $answer_from_board );    // error
		}
	}

	/**
	 * sets the relay to on or off
	 * 
	 * @param type $on -- TRUE or FALSE
	 * @return boolean true == worked, false == error, check last error 
	 */
	private function set_relay_state_on_or_off( $bool_on_or_off )
	{
		$this->is_board_ready();

		// only available to a relay
		if( $this->hardware_type != DENKOVI_RELAY )            throw new Exception("This is not a relay.");

		// init last error
		$this->last_error = '';

		// build the command string
		$command = $this->board_serial_address . $this->board_device_function_code . $this->board_device_identifier 
				. "=" . ( $bool_on_or_off === TRUE ? '1;' : '0;' );

		// send the command, receive the reply
		try
		{
			$answer = $this->send_command_and_get_reply( $command );
		}
		catch( Exception $e )
		{
			throw $e;
		}
		// the board repeats a successful command back
		if( $answer == $command )
		{
			$this->current_relay_state = (bool)$bool_on_or_off;

			// was set sucessfully
			return 1;    
		}
		else
		{
			$this->last_error = $answer;
			return $answer;
		}
	}
	
	private function send_command_and_get_reply( $command ) 
	{
		$this->is_board_ready();

		$board_reply = "";

		// testing
		if( $this->is_board_in_test_mode )
		{
			return $command;
		}

		try
		{
			// Create a TCP Stream Socket
			$socket = socket_create( AF_INET, SOCK_STREAM, SOL_TCP );
			if( $socket === FALSE )
			{
				throw new Exception( "Socket Creation Failed" );
			}

			// Connect to the server.
			$result = socket_connect( $socket, $this->board_ip, $this->board_port );
			if( $result === FALSE )
			{
				// we have a connectivity problem
				throw new Exception( "Connection Failed -- could not connect to the board, is it on?" );
			}

			// Write to socket!
			socket_write( $socket, $command, strlen( $command ) );

			// Read from socket!
			do 
			{
				// read up to 1024 bytes
				$line = socket_read( $socket, 1024, PHP_BINARY_READ );

				// if we have a result, add to output
				if( $line != "" ) 
				{
					$board_reply .= $line;
				}

				// if the read data was less than the read, exit, there is no more data coming
				if( $line == NULL || strlen( $line ) < 1024 )                break;

			} while ( $line != "" );

			// healthy to stop traffic if any left
			socket_shutdown( $socket );

			// if socket is not already closed
			if( socket_last_error( $socket ) != 104 ) 
			{
				// Close socket
				socket_close( $socket );
			}

			// clean up
			unset( $socket );

			// delay of 1/4 of a second to prevent mixed signals
			usleep( 250000 );

			// return reply
			return $board_reply;
		}
		catch( Exception $e )
		{
			throw new Exception( "We have some sort of communication issue with the board, is it on? [ {$e->getMessage()} ]" );
		}
	}
	
	private function is_board_ready()
	{
		if( !isset( $this->hardware_type ) )			throw new Exception("Board Hardware Type must be set.");
		if( !isset( $this->board_device_identifier ) )	throw new Exception("Board Device ID must be set.");
		if( !isset( $this->board_ip ) )					throw new Exception("Board Device IP must be set.");
		if( !isset( $this->board_port ) )				throw new Exception("Board Port must be set.");
		if( !isset( $this->board_serial_address ) )		throw new Exception("Board Serial Address must be set.");

		return;
	}
	
}   // end class controller

?>

<?php
/**
 * Author:  Jonathan Galpin - jonathan@iqzero.net
 * 
 * Feb 2014
 * Description: denkovi controller's primary interface
 * 
 * $hardware_type           -- DENKOVI_RELAY, DENKOVI_INPUT_ANALOG, DENKOVI_INPUT_DIGITAL
 * $device_id				-- 1-16 for relays, 1-8 for inputs		
 * $board_ip        -- default is 192.168.0.100
 * board_port		-- default is 1010
 * $denkovi_serial_address  -- default is 00 
 * 
 */
require_once 'config.php';

class denkovi_controller
{
    private $hardware_type;                 // example: relay, analog_input, digital_input, see config
    private $board_device_id;               // device id...ie relay # 1  -- in this case, relay 1-16 equates to device 0-15
    private $board_device_function_code;    // 3 bytes, denkovi function code
    private $board_ip;
    private $board_port;
    private $board_serial_address;
	private $is_board_in_test_mode;			// defined as DENKOVI_BOARD_NOT_AVAILABLE_FOR_USE in config, set here for testing purposes
	private $current_relay_state;			// for test mode
    public  $last_error;                    
    
    public function __construct() 
    {
		if( defined('DENKOVI_BOARD_IP') ) $this->board_ip = DENKOVI_BOARD_IP;	
		$this->board_port = '1010';												// factory default
		$this->board_serial_address = '00';										// factory default
		$this->current_relay_state = FALSE;										// off
		
		$this->is_board_in_test_mode = DENKOVI_BOARD_NOT_AVAILABLE_FOR_USE;
    }
    
	public function is_relay_on()
	{
		return $this->get_relay_state_on_or_off();
	}
	
	public function is_relay_off()
	{
		return( $this->get_relay_state_on_or_off() === 0 ? 1 : 0 );
	}
	
    public function get_analog_digital_input_reading()
    {
		$this->is_board_ready();
		
        // NOT available to a relay
        if( $this->hardware_type == DENKOVI_RELAY )            throw new Exception( "This is not an analog or digital input device." );
        
        // init last error
        $this->last_error = '';
        
        // build the command string
        $command = $this->board_serial_address . $this->board_device_function_code . $this->board_device_id . "=";

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
                case $command . "0;": return 0; // 0 = connection is open ( switch OFF )
                case $command . "1;": return 1;  // 1 = connection is closed, button pressed etc ( switch ON )
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

	// ==============================================================================================
	// Setters
	// ==============================================================================================    
	public function set_relay_on()
	{
		return $this->set_relay_state_on_or_off( TRUE );
	}
	
	public function set_relay_off()
	{
		return $this->set_relay_state_on_or_off( FALSE );
	}

	/**
	 * Three hardware types: DENKOVI_RELAY, DENKOVI_INPUT_ANALOG, DENKOVI_INPUT_DIGITAL
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
	 * 1-16 for relays, 1-8 for inputs
	 * @param type $device_id
	 * @throws Exception
	 */
	public function set_device_id( $device_id )
	{
		if( (int)$device_id == 0 )			throw new Exception("Device ID's must be 1 and above.");
		
        if( (int)$device_id < 11 )
        {
            $this->board_device_id = (int)$device_id - 1;           // devices are zero based
        }
		else
		{
            switch( (int)$device_id )
            {
                case 11: $this->board_device_id = 'A'; break;
                case 12: $this->board_device_id = 'B'; break;
                case 13: $this->board_device_id = 'C'; break;
                case 14: $this->board_device_id = 'D'; break;
                case 15: $this->board_device_id = 'E'; break;
                case 16: $this->board_device_id = 'F'; break;
            }
        }		
	}

	public function set_board_ip( $ip_address )
	{
		if( !filter_var( $ip_address, FILTER_VALIDATE_IP ) )	throw new Exception( "Please provide a valid IP address." );
		$this->board_ip = $ip_address;			
	}
	
	public function set_board_port( $board_port )
	{
		if( !gettype( $board_port ) == "string" )	throw new Exception( "Please provide a string for the board port." );
		$this->board_port = $board_port;
	}
	
	public function set_board_serial_address( $serial_address )
	{
		if( !gettype( $serial_address ) == "string" )	throw new Exception( "Please provide a string for the serial address." );
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
	
	// ==============================================================================================
	// Private functions
	// ==============================================================================================
	private function is_board_ready()
	{
		if( !isset( $this->hardware_type ) )			throw new Exception("Board Hardware Type must be set.");
		if( !isset( $this->board_device_id ) )			throw new Exception("Board Device ID must be set.");
		if( !isset( $this->board_ip ) )					throw new Exception("Board Device IP must be set.");
		if( !isset( $this->board_port ) )				throw new Exception("Board Port must be set.");
		if( !isset( $this->board_serial_address ) )		throw new Exception("Board Serial Address must be set.");

		return;
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
    private function get_relay_state_on_or_off()
    {
		$this->is_board_ready();
		
        // only available to a relay
        if( $this->hardware_type != DENKOVI_RELAY )            throw new Exception("This is not a relay.");

        // init last error
        $this->last_error = '';
        
        // build the command string
        $command_to_board = $this->board_serial_address . $this->board_device_function_code . $this->board_device_id . "=";

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
            case $command_to_board . "0;": return 0; // relay is off
            case $command_to_board . "1;": return 1;  // relay is on
            default:
				if( $this->is_board_in_test_mode ) return ( $this->current_relay_state ? 1 : 0 );
				
				$this->last_error = $answer_from_board;
                throw new Exception( $answer_from_board );    // error
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
        $command = $this->board_serial_address . $this->board_device_function_code . $this->board_device_id 
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
			$this->current_relay_state = $bool_on_or_off;
			
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
    
}   // end class

?>

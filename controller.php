<?php
/**
 *	Author:  Jonathan Galpin - jonathan@iqzero.net
 * 
 *	Description:
 *	denkovi controller's primary interface
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
    public  $last_error;                    
    
    /**
     * sets up the denkovi controller
     * 
     * @param type $hardware_type           -- DENKOVI_RELAY, DENKOVI_INPUT_ANALOG, DENKOVI_INPUT_DIGITAL
     * @param type $device_id               -- 1-16 for relays, 1-8 for inputs
     * @param type $denkovi_board_ip        -- default is 192.168.0.100
     * @param type $denkovi_board_port      -- default is 1010
     * @param type $denkovi_serial_address  -- default is 00
     * @throws Exception 
     */
    public function __construct( $hardware_type, $device_id, $denkovi_board_ip = CONTROL_BOARD_IP, $denkovi_board_port = '1010', $denkovi_serial_address = '00' ) 
    {
        // set the device type
        $this->hardware_type = $hardware_type;

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
        
        // set the board information
        if( $device_id < 11 )
        {
            $this->board_device_id = (int)$device_id - 1;           // devices are zero based
        }
		else
		{
            switch( $device_id )
            {
                case 11: $this->board_device_id = 'A'; break;
                case 12: $this->board_device_id = 'B'; break;
                case 13: $this->board_device_id = 'C'; break;
                case 14: $this->board_device_id = 'D'; break;
                case 15: $this->board_device_id = 'E'; break;
                case 16: $this->board_device_id = 'F'; break;
            }
        }
        $this->board_ip = $denkovi_board_ip;
        $this->board_port = $denkovi_board_port;
        $this->board_serial_address = $denkovi_serial_address;  // command prefix
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
                $this->last_error = $answer_from_board;    // error
                return $answer_from_board;            
        }
        
    }
    
    public function get_analog_digital_input_reading()
    {
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
                    $this->last_error = $answer;    // error
                    return $answer;            
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
        }
    }
    
    /**
     * sets the relay to on or off
     * 
     * @param type $on -- TRUE or FALSE
     * @return boolean true == worked, false == error, check last error 
     */
    public function set_relay_state_on_or_off( $bool_on_or_off )
    {
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
		$this->last_error = "";
        $board_reply = "";

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

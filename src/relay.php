<?php
/**
 * relay class object
 *
 * @author Jonathan Galpin, May 2014 jonathan@iqzero.net
 */
require_once 'base/device.php';

class relay extends device
{
	/**
	 * stores the current on/off state of a relay locally
	 * @var type boolean 
	 */
	private $is_on = FALSE;     // defaults to off

	/**
	 * required, device_number corresponding to the board relay you need to control
	 * @param $device_number integer
	 */
	public function __construct( $device_number )
	{
		parent::__construct( $device_number );
		$this->controller->set_hardware_type( DENKOVI_RELAY );	
	}

	/**
	 * turns the relay on
	 * @param type $force (optional) if TRUE, and the device is already on, the command will be sent anyway
	 */
	public function on( $force = FALSE )
	{
		// turn on if off
		if( !$this->is_on || $force )
		{
			// turn device on
			$result = $this->controller->set_relay_on();

			// set status
			$this->is_on  = ( $result === 1 ? TRUE : FALSE );
		}   
	}

	/**
	 * turns the relay off if on
	 * @param type $force (optional) if TRUE, and the device is already off, the command will be sent anyway
	 */
	public function off( $force = FALSE )
	{
		// turn off if on
		if( $this->is_on || $force )
		{
			// turn off the relay
			$result = $this->controller->set_relay_off();

			// set status
			$this->is_on = ( $result === 1 ? FALSE : TRUE );
		}
	}

	/**
	 * reports the status of the relay
	 * @return type 
	 */
	public function status( $force = FALSE )
	{
		if( $force )
		{
			$status = $this->controller->get_relay_state_on_or_off();
			$this->is_on = $status;
		}
		return ( $this->is_on ? "On" : "Off" );
	}
    
	public function is_relay_on()
	{
		return $this->controller->get_relay_state_on_or_off();
	}
	
	public function is_relay_off()
	{
		return( $this->controller->get_relay_state_on_or_off() === 0 ? 1 : 0 );
	}

}	// end class relay

?>

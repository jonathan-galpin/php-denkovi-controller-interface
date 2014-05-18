<?php
/**
 * digital sensor class
 *
 * example, on off switch
 * 
 * @author Jonathan Galpin, May 2014, jonathan@iqzero.net
 */
require_once 'base/input_output.php';

class digital_sensor extends input_output
{
	public function __construct( $device_number )
	{
		parent::__construct( $device_number );
		
		$this->set_input_output_as_digital();
	}
	/**
	 * tells us the reading of the sensor
	 * @return type 
	 */
	public function status()
	{
		return ( $this->get_sensor_state() ? "On" : "Off");
	}
	
	public function is_on()
	{
		return ( $this->status() == "On" ? TRUE : FALSE );
	}
	
	public function is_off()
	{
		return ( $this->status() == "Off" ? TRUE : FALSE );
	}

	public function set_is_reverse_input_output( $boolean_value )
	{
		$this->reverse_behaviour = (bool)$boolean_value;
	}

	public function get_is_reverse_input_output()
	{
		return $this->reverse_behaviour;
	}
	
	
}	// end class digital_sensor

?>

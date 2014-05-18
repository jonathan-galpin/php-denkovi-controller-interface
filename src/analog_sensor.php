<?php
/**
 * analog sensor class
 *
 * example, temperature sensor
 * 
 * @author Jonathan Galpin, May 2014, jonathan@iqzero.net
 */
require_once 'base/input_output.php';

class analog_sensor extends input_output
{
	public function __construct( $device_number )
	{
		parent::__construct( $device_number );
		
		$this->set_input_output_as_analog();
	}

	/**
	 * tells us the reading of the sensor
	 * @return type 
	 */
	public function status()
	{
		return $this->get_sensor_state();
	}

}	// end class input_output

?>

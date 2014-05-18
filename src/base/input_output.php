<?php
/**
 * input output base class
 *
 * @author Jonathan Galpin, April 2012
 * jonathan@iqzero.net
 */
require_once 'device.php';

class input_output extends device
{
	/**
	 * only applies to a digitial input/output sensor
	 * 
	 * when set to TRUE using the setter, this sensor reacts opposite from normally expected
	 * 
	 * @var type boolean
	 */
	protected $reverse_behaviour = FALSE;				

	public function __construct( $device_number )
	{
		parent::__construct( $device_number );
	}

	public function set_input_output_as_analog()
	{
		$this->controller->set_hardware_type( DENKOVI_INPUT_ANALOG );	// required
	}

	public function set_input_output_as_digital()
	{
		$this->controller->set_hardware_type( DENKOVI_INPUT_DIGITAL );	// required
	}

	public function get_input_output_type_as_int()
	{
		return $this->get_hardware_type_as_int();
	}

	public function get_is_analog_input()
	{
		return ( $this->get_hardware_type_as_int() === DENKOVI_INPUT_ANALOG ? TRUE : FALSE );
	}

	public function get_is_digital_input()
	{
		return ( $this->get_hardware_type_as_int() === DENKOVI_INPUT_DIGITAL ? TRUE : FALSE );
	}

	/**
	 * reads the sensor 
	 */
	protected function get_sensor_state()
	{
		$result = "";

		if( $this->get_hardware_type_as_int() == DENKOVI_INPUT_ANALOG )
		{
			$result = $this->controller->get_analog_digital_input_reading();
		}
		else	// digital
		{
			$result = ( $this->controller->get_analog_digital_input_reading() == 'on' ? TRUE : FALSE );

			if( $this->reverse_behaviour ) $result = !$result;
		}

		return $result;
	}
	
}	// end class input_output

?>

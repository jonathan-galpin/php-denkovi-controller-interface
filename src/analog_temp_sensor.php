<?php
/**
 * analog temperature sensor class
 *
 * @author Jonathan Galpin, May 2014, jonathan@iqzero.net
 */
require_once 'analog_sensor.php';

class analog_temp_sensor extends analog_sensor
{
	/**
	 * is the display farenheight or celsius
	 * 
	 * @var type boolean
	 */
	private $is_farenheight_display;
	
	/**
	 * to smooth out the fluctuations, this is averaged
	 * see the config.php for settings
	 * 
	 * @var type boolean 
	 */
	private $is_temp_reading_averaged = TRUE;
	
	/**
	 * stores multiple temp readings in this variable to average the results
	 * 
	 * @var type 
	 */
	private $temp_reading_array = array();
	
	
	public function __construct( $device_number )
	{
		parent::__construct( $device_number );
		
		$this->is_farenheight_display = ( TEMPERATURE_READING_DISPLAY_DEFAULT === "farenheight" );
	}

	public function is_display_in_farenheight()
	{
		return $this->is_farenheight_display;
	}

	public function is_display_in_celsius()
	{
		return !$this->is_farenheight_display;
	}
	
	public function is_display_averaged()
	{
		return $this->is_temp_reading_averaged;
	}
	
	/**
	 * temperature reading of the sensor
	 * @return type double
	 */
	public function get_temperature()
	{
		return ( $this->is_farenheight_display ?  $this->get_temperature_reading() : $this->get_celsius_from_farenheight( $this->get_temperature_reading() ) );
	}

	public function status()
	{
		return $this->get_temperature();
	}

	public function set_temp_display_to_farenheight()
	{
		$this->is_farenheight_display = TRUE;
	}
	
	public function set_temp_display_to_celsius()
	{
		$this->is_farenheight_display = FALSE;
	}
	
	public function set_display_averaged( $boolean )
	{
		$this->is_temp_reading_averaged = (bool)$boolean;
	}

	/**
	* stores TEMPERATURE_READINGS_COUNT_FOR_AVERAGE_CALCULATION (config file) temp readings, 
	* returns an average when the $this->is_temp_reading_averaged is TRUE (default)
	* 
	* @param type $this->temp_reading_array
	* @param type $new_reading
	* @return int 
	*/
	private function get_temperature_reading()
	{
		// new temp reading
		$new_reading = $this->get_sensor_state();

		// always store the readings to average the temp regardless of the $this->is_temp_reading_averaged setting
		if( count($this->temp_reading_array) >= TEMPERATURE_READINGS_COUNT_FOR_AVERAGE_CALCULATION )
		{
			// remove the last item
			array_pop( $this->temp_reading_array );

			// add the new item to the front
			array_unshift( $this->temp_reading_array, $new_reading );               
		}else{
			$this->temp_reading_array[] = $new_reading;
		}

		if( !$this->is_temp_reading_averaged )	return round( ( ( ($new_reading + TEMPERATURE_READING_ADJUSTMENT )*9)/5 ) + 32, 2 );	// returned in farenheight - yep convoluted!

		if( count( $this->temp_reading_array ) > 0 )
		{
			$averaged_reading = array_sum( $this->temp_reading_array )/count( $this->temp_reading_array );

			return round( ( ( ($averaged_reading + TEMPERATURE_READING_ADJUSTMENT )*9)/5 ) + 32, 2 );		// returned in farenheight - yep convoluted!
		}else{
			return 0;
		}
	}    

	private function get_celsius_from_farenheight( $farenheight_value )
	{
		return intval( ( 5/9 ) * ( $farenheight_value-32 ) );
	}
	
}	// end class analog_temp_sensor

?>

<?php 
/**
 * Author:  Jonathan Galpin, jonathan@iqzero.net May 2014
 * 
 * Description: analog sensor unit tests
 * 
 */
require_once( 'src/analog_sensor.php' );
require_once( 'src/digital_sensor.php' );
require_once( 'src/analog_temp_sensor.php' );

class inputOutputSensorTest extends PHPUnit_Framework_TestCase
{

	public function testAnalogInput()
	{
		$analog_sensor = new analog_sensor(1);
		
		// is analog
		$this->assertEquals( DENKOVI_INPUT_ANALOG, $analog_sensor->get_hardware_type_as_int() );
		$this->assertTrue( $analog_sensor->get_is_analog_input() );
		
		// is digital
		$this->assertNotEquals( DENKOVI_INPUT_DIGITAL, $analog_sensor->get_hardware_type_as_int() );
		$this->assertFalse( $analog_sensor->get_is_digital_input() );
		
		// status
		if( !$analog_sensor->is_board_in_test_mode() )
		{
			$this->assertTrue(is_numeric( $analog_sensor->status() ) );
		}
		else
		{
			echo "The board needs to be on with sensors connected to test the inputs\nSkipping\n";
		}
	}
	
	public function testDigitalInput()
	{
		// 1 is On
		$digital_sensor = new digital_sensor(1);
				
		// is analog
		$this->assertNotEquals( DENKOVI_INPUT_ANALOG, $digital_sensor->get_hardware_type_as_int() );
		$this->assertFalse( $digital_sensor->get_is_analog_input() );
		
		// is digital
		$this->assertEquals( DENKOVI_INPUT_DIGITAL, $digital_sensor->get_hardware_type_as_int() );
		$this->assertTrue( $digital_sensor->get_is_digital_input() );
		
		// status
		if( !$digital_sensor->is_board_in_test_mode() )
		{
			$this->assertEquals( "On", $digital_sensor->status() );
			$this->assertNotEquals( "Off", $digital_sensor->status() );
			$this->assertTrue( $digital_sensor->is_on() );
			$this->assertFalse( $digital_sensor->is_off() );
			$this->assertFalse( $digital_sensor->get_is_reverse_input_output() );
			

			// reverse the readings
			$digital_sensor->set_is_reverse_input_output( TRUE );
			
			$this->assertEquals( "Off", $digital_sensor->status() );
			$this->assertNotEquals( "On", $digital_sensor->status() );
			$this->assertFalse( $digital_sensor->is_on() );
			$this->assertTrue( $digital_sensor->is_off() );
			$this->assertTrue( $digital_sensor->get_is_reverse_input_output() );
		}

		unset( $digital_sensor );
	}

	public function testAnalogTempSensor()
	{
		$temp_sensor = new analog_temp_sensor( 1 );
		
		// farenheight is set in the config
		$this->assertTrue( $temp_sensor->is_display_in_farenheight() );
		$this->assertFalse( $temp_sensor->is_display_in_celsius() );
		$this->assertTrue( $temp_sensor->is_display_averaged() );
		
		if( !$temp_sensor->is_board_in_test_mode() )
		{
			for( $i=1 ; $i < 150 ; $i++ )
			{
				$this->assertTrue( is_double( $temp_sensor->status() ) );
			}
			//echo "Farenheight: " . $temp_sensor->get_temperature() . "\n";
			$temp_sensor->set_display_averaged( FALSE );
			$this->assertFalse( $temp_sensor->is_display_averaged() );
			//echo "Non Averaged Temp (F): " . $temp_sensor->get_temperature() . "\n";
			
			$temp_sensor->set_display_averaged( TRUE );
			$temp_sensor->set_temp_display_to_celsius();
			$this->assertFalse( $temp_sensor->is_display_in_farenheight() );
			$this->assertTrue( $temp_sensor->is_display_in_celsius() );
			//echo "Celcius: " . $temp_sensor->get_temperature() . "\n";
			$temp_sensor->set_display_averaged( FALSE );
			//echo "Non Averaged Temp (C): " . $temp_sensor->get_temperature() . "\n";
			
			$temp_sensor->set_display_averaged( TRUE );
			$temp_sensor->set_temp_display_to_farenheight();
		}
		
		unset( $temp_sensor );
	}
	
}
?>

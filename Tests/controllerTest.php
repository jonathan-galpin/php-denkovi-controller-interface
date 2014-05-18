<?php 
/**
 * Author:  Jonathan Galpin, jonathan@iqzero.net May 2014
 * 
 * Description: Controller unit tests
 * 
 */
require_once( 'src/controller.php' );
require_once( 'relay.php' );

class controllerRelaySensorTest extends PHPUnit_Framework_TestCase
{
	public function testRelay()
	{
		//sleep(10);
		for( $i=1; $i < 17; $i++ )
		{
			$relay = new relay( $i );
			
			$this->assertEquals( DENKOVI_RELAY, $relay->get_hardware_type_as_int() );
			
			if( !$relay->is_board_in_test_mode() ) sleep(1);
			
			$relay->set_name("MyRelay-$i");

			$this->assertEquals( $i, $relay->id );
			$this->assertEquals( "MyRelay-$i", $relay->name );	// tests get device name & set_name

			$this->assertEquals( 1, $relay->is_relay_off() );
			$this->assertEquals( "Off", $relay->status() );	

			$relay->on();
			$this->assertEquals( 1, $relay->is_relay_on() );		
			$this->assertEquals( 0, $relay->is_relay_off() );		
			$this->assertEquals( "On", $relay->status() );		

			$relay->off();
			$this->assertEquals( 0, $relay->is_relay_on() );		
			$this->assertEquals( 1, $relay->is_relay_off() );
			$this->assertEquals( "Off", $relay->status() );	

			unset( $relay );
		}
	}
	
	public function testControlBoardNotAvailable()
	{
		$controller = new denkovi_controller();				
		$controller->set_hardware_type( DENKOVI_RELAY );					// required
		$controller->set_board_device_identifier_from_device_number( 1 );	// required
		$controller->set_board_ip( DENKOVI_BOARD_IP );
		$controller->set_board_port( '1010' );
		$controller->set_board_serial_address( '00' );
		$controller->set_board_test_mode_on();
		
		$answer = $controller->set_relay_on();
		$this->assertEquals( 1, $answer );
		
		$answer = $controller->set_relay_off();
		$this->assertEquals( 1, $answer );

		unset( $controller );
	}

	public function testControlBoardDeviceNameAndIdentifyer()
	{
		$controller = new denkovi_controller();				
		$controller->set_hardware_type( DENKOVI_RELAY );	// required
		$controller->set_board_ip( DENKOVI_BOARD_IP );
		$controller->set_board_port( '1010' );
		$controller->set_board_serial_address( '00' );
		$controller->set_board_test_mode_on();

		for( $i=1; $i < 17; $i++ )
		{
			$controller->set_board_device_identifier_from_device_number( $i );		// required
			
			if( $i < 11 )
			{
				$this->assertEquals( $i - 1, $controller->get_board_device_identifier() );
				$this->assertEquals( $i, $controller->get_board_device_number() );
				$this->assertNotEquals( $i - 1, $controller->get_board_device_number() );
			}
			else
			{
				$this->assertEquals( $i, $controller->get_board_device_number() );
				$this->assertNotEquals( $i - 1, $controller->get_board_device_number() );
				
				switch( $i )
				{
					case 11: 
						$this->assertEquals( "A", $controller->get_board_device_identifier() ); 
						break;
					case 12: 
						$this->assertEquals( "B", $controller->get_board_device_identifier() ); 
						break;
					case 13: 
						$this->assertEquals( "C", $controller->get_board_device_identifier() ); 
						break;
					case 14: 
						$this->assertEquals( "D", $controller->get_board_device_identifier() ); 
						break;
					case 15: 
						$this->assertEquals( "E", $controller->get_board_device_identifier() ); 
						break;
					case 16: 
						$this->assertEquals( "F", $controller->get_board_device_identifier() ); 
						break;
				}
			}
			
		}
		unset( $controller );
	}

	public function testControlBoardIsAvailable()
	{
		$controller = new denkovi_controller();				
		$controller->set_hardware_type( DENKOVI_RELAY );					// required
		$controller->set_board_device_identifier_from_device_number( 1 );	// required
		$controller->set_board_ip( DENKOVI_BOARD_IP );
		$controller->set_board_port( '1010' );
		$controller->set_board_serial_address( '00' );
		
		if( DENKOVI_BOARD_NOT_AVAILABLE_FOR_USE === FALSE ) 
		{
			$controller->set_board_test_mode_off();
		}
			
		$answer = $controller->set_relay_on();
		$this->assertEquals( 1, $answer );
		
		$answer = $controller->set_relay_off();
		$this->assertEquals( 1, $answer );

		unset( $controller );
	}

	public function testBoardTestMode()
	{
		$relay = new relay( 1 );
			
		if( DENKOVI_BOARD_NOT_AVAILABLE_FOR_USE === FALSE ) 
		{
			$this->assertFalse(	$relay->is_board_in_test_mode() );
		}
		else 
		{
			$this->assertTrue(	$relay->is_board_in_test_mode() );
		}
	}
	
	public function tearDown()
	{
		// turn off any relay that may be on
		for( $i=1; $i < 17; $i++ )
		{	
			$relay = new relay( $i );
			$relay->off();
			unset( $relay );
			usleep( 250 );
		}
	}
	
}
?>

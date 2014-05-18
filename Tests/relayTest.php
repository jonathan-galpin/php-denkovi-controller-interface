<?php 
/**
 * Author:  Jonathan Galpin, jonathan@iqzero.net May 2014
 * 
 * Description: Controller unit tests
 * 
 */
require_once( 'src/base/controller.php' );
require_once( 'src/relay.php' );

class relayTest extends PHPUnit_Framework_TestCase
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

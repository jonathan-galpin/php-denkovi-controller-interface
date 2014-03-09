<?php 
/**
 * Author:  Jonathan Galpin, jonathan@iqzero.net
 * 
 * Description: Controller unit tests
 * 
 */
require_once( 'controller.php' );
class controllerTest extends PHPUnit_Framework_TestCase
{
	public function testRelayOnBoardNotAvailable()
	{
		$controller = new denkovi_controller();				
		$controller->set_hardware_type( DENKOVI_RELAY );	// required
		$controller->set_device_id( 1 );					// required
		$controller->set_board_ip( DENKOVI_BOARD_IP );
		$controller->set_board_port( '1010' );
		$controller->set_board_serial_address( '00' );
		$controller->set_board_test_mode_on();
		
		$answer = $controller->set_relay_on();
		$this->assertEquals( 1, $answer );
		
		$answer = $controller->is_relay_on();
		$this->assertEquals( 1, $answer );

		$answer = $controller->is_relay_off();
		$this->assertEquals( 0, $answer );
		
		$answer = $controller->set_relay_off();
		$this->assertEquals( 1, $answer );

		$answer = $controller->is_relay_off();
		$this->assertEquals( 1, $answer );
		
		$answer = $controller->is_relay_on();
		$this->assertEquals( 0, $answer );
		
		
	}


}
?>

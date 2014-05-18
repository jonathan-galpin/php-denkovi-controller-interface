<?php
/**
 * Description of device
 * 
 * common device object
 * 
 * implements common functions
 *
 * @author Jonathan Galpin, May 2014
 * jonathan@iqzero.net
 */
require_once 'controller.php';

interface device_interface
{
	public function status( $force = FALSE );
}

class device implements device_interface
{
	protected $controller;             // controller device interface
	
	public $name;
	public $id;

	/**
	 * device number is required, corresponds to the denkovi board configuration you have
	 * 
	 * @param type $device_number
	 */
	public function __construct( $device_number )
	{
		// set up controller
		$this->controller = new denkovi_controller();				
		$this->controller->set_board_device_identifier_from_device_number( $device_number );		// required
		$this->id = (int)$device_number;
	}
	/**
	 * reports the status of the device
	 * 
	 * @var $force boolean, 
	 *		true = send command to board regardless of local stored state variable
	 *		false (default) = check the current locally stored state and return this
	 * @return type varchar 
	 */
	public function status( $force = FALSE )
	{
		// implement in the respective device
	}
    
	public function is_board_in_test_mode()
	{
		return $this->controller->get_is_board_in_test_mode();
	}
	
	public function set_name( $friendly_name )
	{
		$this->controller->set_device_name( $friendly_name );
		$this->name = $this->controller->get_device_name();
	}	
	
	public function get_hardware_type_as_int()
	{
		return $this->controller->get_hardware_type();
	}
	
}	// end class device

?>

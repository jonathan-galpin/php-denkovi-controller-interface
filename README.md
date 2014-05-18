php-denkovi-controller-interface
================================
/**
 * Description of controller
 * 
 * PHP class to control an IP based relay and input control board from denkovi.com
 * 
 * controls a Denkovi ethernet board DAEnetIP3 
 * serially attached to a 12 relay/8 analog input/8 digital input board --- DAE-PB-RO12/DI8/AI8
 * serially attached to a 4 relay board --- DAE-Ro4-12V
 * 
 * www.denkovi.com
 * 
 * error codes
 * E1 - invalid function code
 * E2 - invalid data
 * E3 - communication error
 * 
 * example error 00E1 - 00 = the serial address of the board
 *
 * @author Jonathan Galpin, May 2014
 * jonathan@iqzero.net
 */

How to use this project:

Know your board, this project was created and tested with the boards described above. You should be able to use if for your setup with minor changes.

Adjust the src/base/config.php to suit your needs.

To Control a Relay
==================

require_once( 'src/relay.php' );

$relay = new relay( 1 );

$relay->set_name("Front Porch Light");

$relay->on();	// turn the relay on	

echo "The {$relay->name} is now {$relay->status()}.";		// The Front Porch Light is now On.

$relay->off();	// turn relay off

$relay->is_relay_off()	// is it off?

$relay->is_relay_on()	// is it on?


To Read a Temperature Sensor
======================================

require_once( 'src/analog_temp_sensor.php' );

$temp_sensor = new analog_temp_sensor( 1 );	// 1 corresponds to the input/output number on the denkovi board - yours will vary

// display type is set in the config, and defaults to farenheight

// change to celsius

$temp_sensor->set_temp_display_to_celsius();

// read the temperature in celsius

$temp_sensor->get_temperature()

// see the Tests/inputOutputSensorTest.php


To Read a connected Digital Sensor ( example switch )
=====================================================

require_once( 'src/digital_sensor.php' );

$digital_sensor = new digital_sensor(1); // 1 corresponds to the input/output number on the denkovi board - yours will vary

// is the switch on?

echo ( $digital_sensor->is_on() ? "it is on" : "it is off" );

// is the switch off?

if( $digital_sensor->is_off() ) echo "The switch is off";

// status

echo "The switch is " . $digital_sensor->status(); // either "The switch is On" or "The switch is Off"

// the functioning of the input could be reversed:

$digital_sensor->set_is_reverse_input_output( TRUE );

$digital_sensor->get_is_reverse_input_output();


To run the unit tests
=====================
install phpUnit

cd into the project folder

`>phpunit Tests/`

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

Adjust the src/config.php to suit your needs.

To Control a Relay
==================
require_once( 'relay.php' );

$relay = new relay( 1 );
$relay->set_name("Front Porch Light");

$relay->on();	// turn the relay on	

echo "The {$relay->name} is now {$relay->status()}.";		// The Front Porch Light is now On.

$relay->off();	// turn relay off

$relay->is_relay_off()	// is it off?, returns true if off, else false
$relay->is_relay_on()	// is it on, returns true if on, else false



To run the unit tests
=====================
install phpUnit

cd into the project folder

#>phpunit Tests/

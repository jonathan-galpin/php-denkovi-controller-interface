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
 * @author Jonathan Galpin, April 2012
 * jonathan@iqzero.net
 */

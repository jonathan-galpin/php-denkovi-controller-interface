<?php
/**
 *	Author:  Jonathan Galpin - jonathan@iqzero.net
 * 
 *	Description:
 *	configuration settings for the Denkovi Controller
 */

/**
 * controller IP: fatory default is 192.168.0.100
 * 
 * if you have modified this on the board, change it here
 */
define("DENKOVI_BOARD_IP", '192.168.0.100');

/**
 * denkovi board functionality roles, Relay, Analog Input, Digital Input
 */
define("DENKOVI_RELAY",         0);
define("DENKOVI_INPUT_ANALOG",  1);     // example, analog temp sensor
define("DENKOVI_INPUT_DIGITAL", 2);     // example, digital on off switch / float

/**
 * DENKOVI_BOARD_NOT_AVAILABLE_FOR_USE
 * 
 * test mode -- use this to develop without the board being connected to the network
 * TRUE = testing without the board being available
 * FALSE = board is available
 */
define("DENKOVI_BOARD_NOT_AVAILABLE_FOR_USE", FALSE);

?>

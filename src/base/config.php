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
 * DENKOVI_BOARD_IS_AVAILABLE_FOR_USE
 * 
 * test mode -- use this to develop without the board being connected to the network
 * TRUE = testing without the board being available
 * FALSE = board is available
 */
define("DENKOVI_BOARD_IS_AVAILABLE_FOR_USE", TRUE);		// default = TRUE

/**
 * temp reading display default "farenheight" or "celsius"
 */
define("TEMPERATURE_READING_DISPLAY_DEFAULT", "farenheight");

/**
 * temperature sensor readings to store over which an average is calculated 
 */
define("TEMPERATURE_READINGS_COUNT_FOR_AVERAGE_CALCULATION", 100);

/**
 * temperature reading adjustment -- configure as you require to correct the temp 
 */
define("TEMPERATURE_READING_ADJUSTMENT", -52);
		

?>

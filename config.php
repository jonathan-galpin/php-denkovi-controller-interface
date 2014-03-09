<?php
/**
 *	Author:  Jonathan Galpin - jonathan@iqzero.net
 * 
 *	Description:
 *	configuration settings for the Denkovi Controller
 */

// controller IP: fatory default is 192.168.0.100
define("DENKOVI_BOARD_IP", '192.168.0.100');

// denkovi board functionality roles
define("DENKOVI_RELAY",         0);
define("DENKOVI_INPUT_ANALOG",  1);     // example, analog temp sensor
define("DENKOVI_INPUT_DIGITAL", 2);     // example, digital on off switch

// test mode -- use this to develop without the board being connected to the network
define("DENKOVI_BOARD_NOT_AVAILABLE_FOR_USE", TRUE);	// TRUE = testing without the board being on 


?>

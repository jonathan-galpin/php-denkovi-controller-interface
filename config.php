<?php
/**
 *	Author:  Jonathan Galpin - jonathan@iqzero.net
 * 
 *	Description:
 *	configuration settings for the Denkovi Controller
 */

// controller IP: fatory default is 192.168.0.100
define("CONTROL_BOARD_IP", '192.168.0.100');

// denkovi board functionality roles
define("DENKOVI_RELAY",         0);
define("DENKOVI_INPUT_ANALOG",  1);     // example, analog temp sensor
define("DENKOVI_INPUT_DIGITAL", 2);     // example, digital on off switch
?>

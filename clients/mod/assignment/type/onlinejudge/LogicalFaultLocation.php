<?php

// /////////////////////////////////////////////////////////////////////////
// //
// NOTICE OF COPYRIGHT //
// //
// Online Judge for Moodle //
// https://github.com/hit-moodle/moodle-local_onlinejudge //
// //
// Copyright (C) 2009 onwards Sun Zhigang http://sunner.cn //
// //
// This program is free software; you can redistribute it and/or modify //
// it under the terms of the GNU General Public License as published by //
// the Free Software Foundation; either version 3 of the License, or //
// (at your option) any later version. //
// //
// This program is distributed in the hope that it will be useful, //
// but WITHOUT ANY WARRANTY; without even the implied warranty of //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the //
// GNU General Public License for more details: //
// //
// http://www.gnu.org/copyleft/gpl.html //
// //
// /////////////////////////////////////////////////////////////////////////

/**
 * Testcase management form
 *
 * @package local_onlinejudge
 * @copyright 2011 Britain Wei (http://britainwei@163.com)
 * @author Britain Wei
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined ( 'MOODLE_INTERNAL' ) || die ();
function faultlocation($inputs, $correctoutputs, $source) {
	try {
		// Initialize webservice
		$client = new SoapClient ( 'http://localhost:9000/WebFaultLocation.asmx?wsdl', array (
				'encoding' => 'UTF-8' 
		) );
		
		// Set your parameters for the request
// 		$inputs = array (
// 				"4 5 2",
// 				"4 3 2",
// 				"4 3 10",
// 				"3 2 6",
// 				"3 1 8" 
// 		);
// 		$correctoutputs = array (
// 				"4",
// 				"4",
// 				"5",
// 				"0",
// 				"0" 
// 		);
// 		$source = file_get_contents ( "E:\\code\\C#\\FaultLocation\\FaultLocation\\bin\\Debug\\source\\testphp.c" );
		
		// Invoke webservice method with parameters
		$response = $client->FaultLocation ( array (
				'inputs' => $inputs,
				'correctoutputs' => $correctoutputs,
				'source' => $source 
		) );
		
		return $response->FaultLocationResult;
	} catch ( Exception $e ) {
		return 'Caught exception: ' .$e->getMessage ();
	}
}
?>
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
 * Testcases management
 *
 * @package local_onlinejudge
 * @copyright 2015 britain wei (britainwei@163.com)
 * @author britain wei
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once (dirname ( __FILE__ ) . '/../../../../config.php');
require_once ("$CFG->dirroot/mod/assignment/lib.php");
require_once ("$CFG->dirroot/mod/assignment/type/onlinejudge/echart.class.php");
require_once ($CFG->dirroot . '/local/onlinejudge/judgelib.php');

$id = optional_param ( 'id', 0, PARAM_INT ); // Course Module ID
$a = optional_param ( 'a', 0, PARAM_INT ); // Assignment ID

global $context, $OUTPUT, $PAGE, $DB;

$url = new moodle_url ( '/mod/assignment/type/onlinejudge/statisticschart.php' );
if ($id) {
	if (! $cm = get_coursemodule_from_id ( 'assignment', $id )) {
		print_error ( 'invalidcoursemodule' );
	}
	
	if (! $assignment = $DB->get_record ( "assignment", array (
			"id" => $cm->instance 
	) )) {
		print_error ( 'invalidid', 'assignment' );
	}
	
	if (! $course = $DB->get_record ( "course", array (
			"id" => $assignment->course 
	) )) {
		print_error ( 'coursemisconf', 'assignment' );
	}
	$url->param ( 'id', $id );
} else {
	if (! $assignment = $DB->get_record ( "assignment", array (
			"id" => $a 
	) )) {
		print_error ( 'invalidid', 'assignment' );
	}
	if (! $course = $DB->get_record ( "course", array (
			"id" => $assignment->course 
	) )) {
		print_error ( 'coursemisconf', 'assignment' );
	}
	if (! $cm = get_coursemodule_from_instance ( "assignment", $assignment->id, $course->id )) {
		print_error ( 'invalidcoursemodule' );
	}
	$url->param ( 'a', $a );
}

require_login ( $course, true, $cm );
$context = get_context_instance ( CONTEXT_MODULE, $cm->id );
require_capability ( 'mod/assignment:grade', $context );

$PAGE->set_url ( $url );
$PAGE->set_context ( $context );
$title = strip_tags ( $course->fullname );
$PAGE->set_title ( $title );
$PAGE->set_heading ( $title );
// $assignmentinstance = new assignment_onlinejudge ( $cm->id, $assignment, $cm, $course );
// $assignmentinstance->view_header ();

echo $OUTPUT->header ();
echo $OUTPUT->box_start ( 'generalbox' );

// testcase graph.
echo "<div id='chart' style='height:400px;width:100%;'></div>";
$cases = onlinejudge_get_submissions_info ( $assignment->id );

if (isset ( $cases )) {
	$keys = array_keys ( $cases );
	$xaxis = array();
	foreach ($keys as $key) {
		if($tmp = onlinejudge_get_testcase_info($key)) {
			$xaxis[] = 'input:' .$tmp->input;
		}
	}
	
	$data = array (
			'xaxis' => array (
					'name' => '测试用例',
					'type' => 'category',
					'data' => $xaxis 
			),
			'series' => array (
					array (
							'name' => '通过人数',
							'type' => 'bar',
							'barMaxWidth'=> 40,
							'data' => array_values ( $cases ) 
					) 
			) 
	);
	$title = get_string ( 'testcasegragh', 'assignment_onlinejudge' );
	$subtitle = get_string('submissioncount', 'assignment_onlinejudge') .":" .onlinejudge_get_testcase_submission_count ( $keys [0] );
	$a = new Echarts ();
	$a->show ( 'chart', $data, $title, $subtitle);
}
echo $OUTPUT->box_end ();

echo $OUTPUT->box_start ( 'generalbox' );
echo "<div id='chart1' style='height:400px;width:100%;'></div>";
$tests = onlinejudge_get_cource_assignments($course->id);

if (isset ( $tests )) {
	$xdata = array();
	$ydata = array();
	foreach ($tests as $test) {
		if(($tmp = onlinejudge_get_pass_rate($test->id)) != null) {
			$xdata[] = $test->name.'(' . $tmp[1] . '/' . $tmp[2] . ')';
			$ydata[] = $tmp[0];
		}
	}
	$data1 = array (
			'xaxis' => array (
					'name' => 'OJ作业',
					'type' => 'category',
					'data' => $xdata
			),
			'series' => array (
					array (
							'name' => '通过率',
							'type' => 'bar',
							'barMaxWidth'=> 40,
							'data' => $ydata
					)
			)
	);
	$title = get_string ( 'allassignments', 'assignment_onlinejudge' );
	$a = new Echarts ();
	$a->show ( 'chart1', $data1, $title, '');
}
echo $OUTPUT->box_end ();
echo $OUTPUT->footer ();
// $assignmentinstance->view_footer ();

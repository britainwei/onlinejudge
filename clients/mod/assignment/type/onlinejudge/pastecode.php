<?php
/**
 * This file is used to store the pastecode into a file.
 */

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @package mod-assignment
 * @copyright 2015 britainwei <britainwei@163.com>
 */
require_once (dirname ( dirname ( dirname ( dirname ( dirname ( __FILE__ ) ) ) ) ) . '/config.php');
require_once ($CFG->dirroot . "/mod/assignment/type/onlinejudge/assignment.class.php");
global $PAGE, $OUTPUT, $DB, $CFG;

$contextid = required_param ( 'contextid', PARAM_INT );
$id = optional_param ( 'id', null, PARAM_INT );

$formdata = new stdClass ();
$formdata->userid = required_param ( 'userid', PARAM_INT );
$formdata->codelanguage = required_param ( 'codelanguage', PARAM_ALPHA );
$formdata->pastecode = required_param ( 'pastecode', PARAM_RAW );

list ( $context, $course, $cm ) = get_context_info_array ( $contextid );

// TODO judge if the code has changed. If change nothing don't operate.
if (! empty ( $formdata->pastecode )) {
	require_login ( $course, true, $cm );
	if (isguestuser ()) {
		die ();
	}
	
	if (! $assignment = $DB->get_record ( 'assignment', array (
			'id' => $cm->instance 
	) )) {
		print_error ( 'invalidid', 'assignment' );
	}
	
	$instance = new assignment_onlinejudge ( $cm->id, $assignment, $cm, $course );
	$submission = $instance->get_submission ( $formdata->userid, true );
	$fs = get_file_storage (); // Prepare file record object
	$fs->delete_area_files ( $contextid, 'mod_assignment', 'submission', $submission->id );
	$fileinfo = array (
			'contextid' => $contextid,
			'component' => 'mod_assignment',
			'filearea' => 'submission',
			'userid' => $formdata->userid,
			'itemid' => $submission->id, // usually = ID of row in table
			'filepath' => '/', // any path beginning and ending in /
			'filename' => 'myfile.' . $formdata->codelanguage 
	);
	$fs->create_file_from_string ( $fileinfo, $formdata->pastecode );
	
	$updates = new stdClass ();
	$updates->id = $submission->id;
	$updates->numfiles = count ( $fs->get_area_files ( $contextid, 'mod_assignment', 'submission', $submission->id, 'sortorder', false ) );
	$updates->timemodified = time ();
	
	$DB->update_record ( 'assignment_submissions', $updates );
	
	$instance->update_grade ( $submission );
	$instance->request_judge ( $submission ); // Added by onlinejudge
	                                          
	// send files to event system
	$files = $fs->get_area_files ( $contextid, 'mod_assignment', 'submission', $submission->id );
	// Let Moodle know that assessable files were uploaded (eg for plagiarism detection)
	$eventdata = new stdClass ();
	$eventdata->modulename = 'assignment';
	$eventdata->cmid = $cm->id;
	$eventdata->itemid = $submission->id;
	$eventdata->courseid = $course->id;
	$eventdata->userid = $formdata->userid;
	if ($files) {
		$eventdata->files = $files;
	}
	events_trigger ( 'assessable_file_uploaded', $eventdata );
}
$returnurl = new moodle_url ( $CFG->wwwroot . '/mod/assignment/view.php', array (
		'id' => $cm->id 
) );
redirect ( $returnurl );

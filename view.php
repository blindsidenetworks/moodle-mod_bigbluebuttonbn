<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * View a BigBlueButton room.
 *
 * @package   mod_bigbluebuttonbn
 * @copyright 2010 onwards, Blindside Networks Inc
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Jesus Federico  (jesus [at] blindsidenetworks [dt] com)
 * @author    Fred Dixon  (ffdixon [at] blindsidenetworks [dt] com)
 * @author    Darko Miletic  (darko.miletic [at] gmail [dt] com)
 */

use mod_bigbluebuttonbn\plugin;

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/locallib.php');
require_once(__DIR__.'/viewlib.php');

$id = required_param('id', PARAM_INT);
$bn = optional_param('bn', 0, PARAM_INT);
$group = optional_param('group', 0, PARAM_INT);

$viewinstance = bigbluebuttonbn_view_validator($id, $bn); // In locallib.
if (!$viewinstance) {
    print_error('view_error_url_missing_parameters', plugin::COMPONENT);
}

$cm = $viewinstance['cm'];
$course = $viewinstance['course'];
$bigbluebuttonbn = $viewinstance['bigbluebuttonbn'];
$context = context_module::instance($cm->id);

require_login($course, true, $cm);

// In locallib.
bigbluebuttonbn_event_log(\mod_bigbluebuttonbn\event\events::$events['view'], $bigbluebuttonbn);

// Additional info related to the course.
$bbbsession['course'] = $course;
$bbbsession['coursename'] = $course->fullname;
$bbbsession['cm'] = $cm;
$bbbsession['bigbluebuttonbn'] = $bigbluebuttonbn;
// In locallib.
bigbluebuttonbn_view_bbbsession_set($context, $bbbsession);

// Validates if the BigBlueButton server is working.
$serverversion = bigbluebuttonbn_get_server_version();  // In locallib.
if ($serverversion === null) {
    $errmsg = 'view_error_unable_join_student';
    $errurl = '/course/view.php';
    $errurlparams = ['id' => $bigbluebuttonbn->course];
    if ($bbbsession['administrator']) {
        $errmsg = 'view_error_unable_join';
        $errurl = '/admin/settings.php';
        $errurlparams = ['section' => 'modsettingbigbluebuttonbn'];
    } else if ($bbbsession['moderator']) {
        $errmsg = 'view_error_unable_join_teacher';
    }
    print_error($errmsg, plugin::COMPONENT, new moodle_url($errurl, $errurlparams));
}
$bbbsession['serverversion'] = (string) $serverversion;

// Mark viewed by user (if required).
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

// Print the page header.
$PAGE->set_context($context);
$PAGE->set_url('/mod/bigbluebuttonbn/view.php', ['id' => $cm->id]);
$PAGE->set_title($bigbluebuttonbn->name);
$PAGE->set_cacheable(false);
$PAGE->set_heading($course->fullname);
$PAGE->set_pagelayout('incourse');

/** @var core_renderer $OUTPUT */
$OUTPUT;

// Validate if the user is in a role allowed to join.
if (!has_all_capabilities(['moodle/category:manage', 'mod/bigbluebuttonbn:join'], $context)) {
    echo $OUTPUT->header();
    $msgtext = isguestuser() ? 'view_noguests' : 'view_nojoin';
    echo $OUTPUT->confirm(
        sprintf('<p>%s</p>%s', get_string($msgtext, plugin::COMPONENT), get_string('liketologin')),
        get_login_url(),
        new moodle_url('/course/view.php', ['id' => $course->id])
    );
    echo $OUTPUT->footer();
    exit;
}

// Operation URLs.
$bbbsession['bigbluebuttonbnURL'] = plugin::necurl(
    '/mod/bigbluebuttonbn/view.php', ['id' => $bbbsession['cm']->id]
);
$bbbsession['logoutURL'] = plugin::necurl(
    '/mod/bigbluebuttonbn/bbb_view.php',
    ['action' => 'logout', 'id' => $id, 'bn' => $bbbsession['bigbluebuttonbn']->id]
);
$bbbsession['recordingReadyURL'] = plugin::necurl(
    '/mod/bigbluebuttonbn/bbb_broker.php',
    ['action' => 'recording_ready', 'bigbluebuttonbn' => $bbbsession['bigbluebuttonbn']->id]
);
$bbbsession['meetingEventsURL'] = plugin::necurl(
    '/mod/bigbluebuttonbn/bbb_broker.php',
    ['action' => 'meeting_events', 'bigbluebuttonbn' => $bbbsession['bigbluebuttonbn']->id]
);
$bbbsession['joinURL'] = plugin::necurl(
    '/mod/bigbluebuttonbn/bbb_view.php',
    ['action' => 'join', 'id' => $id, 'bn' => $bbbsession['bigbluebuttonbn']->id]
);

// Check status and set extra values.
$activitystatus = bigbluebuttonbn_view_get_activity_status($bbbsession);  // In locallib.
if ($activitystatus == 'ended') {
    $bbbsession['presentation'] = bigbluebuttonbn_get_presentation_array(
        $bbbsession['context'], $bbbsession['bigbluebuttonbn']->presentation);
} else if ($activitystatus == 'open') {
    $bbbsession['presentation'] = bigbluebuttonbn_get_presentation_array(
        $bbbsession['context'], $bbbsession['bigbluebuttonbn']->presentation, $bbbsession['bigbluebuttonbn']->id);
}

// Output starts.
echo $OUTPUT->header();

bigbluebuttonbn_view_groups($bbbsession);

bigbluebuttonbn_view_render($bbbsession, $activitystatus);

// Output finishes.
echo $OUTPUT->footer();

// Shows version as a comment.
echo '<!-- '.$bbbsession['originTag'].' -->'."\n";

// Initialize session variable used across views.
$SESSION->bigbluebuttonbn_bbbsession = $bbbsession;
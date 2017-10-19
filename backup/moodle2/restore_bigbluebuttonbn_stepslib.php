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
 * Class for the structure used for restore BigBlueButtonBN.
 *
 * @author    Fred Dixon  (ffdixon [at] blindsidenetworks [dt] com)
 * @author    Jesus Federico  (jesus [at] blindsidenetworks [dt] com)
 * @copyright 2010-2017 Blindside Networks Inc
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v2 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Define all the restore steps that will be used by the restore_url_activity_task.
 */
class restore_bigbluebuttonbn_activity_structure_step extends restore_activity_structure_step
{
    /**
     * Structure step to restore one bigbluebuttonbn activity.
     *
     * @return array
     */
    protected function define_structure() {
        $paths = array();
        $paths[] = new restore_path_element('bigbluebuttonbn', '/activity/bigbluebuttonbn');
        $paths[] = new restore_path_element('bigbluebuttonbn_logs', '/activity/bigbluebuttonbn/logs/log');
        // Return the paths wrapped into standard activity structure.
        return $this->prepare_activity_structure($paths);
    }

    /**
     * @param data
     * @return array
     */
    protected function process_bigbluebuttonbn($data) {
        global $DB;
        $data = (object) $data;
        $data->course = $this->get_courseid();
        $data->timemodified = $this->apply_date_offset($data->timemodified);
        // Insert the bigbluebuttonbn record.
        $newitemid = $DB->insert_record('bigbluebuttonbn', $data);
        // Immediately after inserting "activity" record, call this.
        $this->apply_activity_instance($newitemid);
    }

    /**
     *
     * @param data
     * @return array
     */
    protected function process_bigbluebuttonbn_logs($data) {
        global $DB;
        $data = (object) $data;
        // Apply modifications.
        $data->courseid = $this->get_mappingid('course', $data->courseid);
        $data->bigbluebuttonbnid = $this->get_new_parentid('bigbluebuttonbn');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        // Insert the bigbluebuttonbn_logs record.
        $newitemid = $DB->insert_record('bigbluebuttonbn_logs', $data);
        // Immediately after inserting associated record, call this.
        $this->set_mapping('bigbluebuttonbn_logs', $data->id, $newitemid);
    }

    /**
     * Actions to be executed after the restore is completed
     *
     * @return array
     */
    protected function after_execute() {
        // Add bigbluebuttonbn related files, no need to match by itemname (just internally handled context).
        $this->add_related_files('mod_bigbluebuttonbn', 'intro', null);
    }
}

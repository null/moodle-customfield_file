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

namespace customfield_file\task;

use core\task\adhoc_task;

/**
 * Runs file context update.
 *
 * @package    customfield_file
 * @author     Tomo Tsuyuki <tomotsuyuki@catalyst-au.net>
 * @copyright  2024 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class update_file_context extends adhoc_task {

    /**
     * Run the task.
     *
     * @return void
     */
    public function execute() {
        global $DB;

        // Collect records which have wrong contextid in the file data.
        $sql = "SELECT mf.id mfid, mcd.contextid mcdcontextid, mf.itemid, mf.filepath, mf.filename
                  FROM {customfield_data} mcd
                  JOIN {customfield_field} mcf ON mcf.id = mcd.fieldid
                  JOIN {customfield_category} mcc ON mcc.id = mcf.categoryid
                  JOIN {files} mf ON mf.itemid = mcd.id
                 WHERE mcc.component = 'core_course' AND mcc.area = 'course'
                       AND mcf.type = 'file'
                       AND mf.component = 'customfield_file' AND mf.filearea = 'value'
                       AND mcd.contextid != mf.contextid";
        $records = $DB->get_records_sql($sql);
        $fs = get_file_storage();

        // Update records with correct contextid and pathnamehash.
        foreach ($records as $record) {
            $DB->set_field('files', 'contextid', $record->mcdcontextid, ['id' => $record->mfid]);
            $pathnamehash = $fs->get_pathname_hash($record->mcdcontextid, 'customfield_file', 'value',
                $record->itemid, $record->filepath, $record->filename);
            $DB->set_field('files', 'pathnamehash', $pathnamehash, ['id' => $record->mfid]);
        }
    }
}

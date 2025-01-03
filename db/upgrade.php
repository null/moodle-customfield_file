<?php
// This file is part of Moodle - https://moodle.org/
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
 * Database upgrade script
 *
 * @package   customfield_file
 * @author    Tomo Tsuyuki <tomotsuyuki@catalyst-au.net>
 * @copyright 2024 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\task\manager;
use customfield_file\task\update_file_context;

/**
 * Function to upgrade customfield database
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_customfield_file_upgrade($oldversion) {

    if ($oldversion < 2023121401) {
        // Add adhoc task to update contextid in file records.
        manager::queue_adhoc_task(new update_file_context());
        upgrade_plugin_savepoint(true, 2023121401, 'customfield', 'file');
    }

    return true;
}

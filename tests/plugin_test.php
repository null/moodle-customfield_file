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

namespace customfield_file;

use core_customfield_generator;
use core_customfield_test_instance_form;

/**
 * Functional test for customfield_file
 *
 * @package    customfield_file
 * @copyright  2024 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class plugin_test extends \advanced_testcase {

    /** @var stdClass[]  */
    private $courses = [];
    /** @var \core_customfield\category_controller */
    private $cfcat;
    /** @var \core_customfield\field_controller[] */
    private $cfields;
    /** @var \core_customfield\data_controller[] */
    private $cfdata;

    /**
     * Tests set up.
     */
    public function setUp(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $this->cfcat = $this->get_generator()->create_category();
        $this->cfields[1] = $this->get_generator()->create_field(
            ['categoryid' => $this->cfcat->get('id'), 'shortname' => 'myfield1', 'type' => 'file']);
        $this->courses[1] = $this->getDataGenerator()->create_course();
    }

    /**
     * Get generator
     * @return core_customfield_generator
     */
    protected function get_generator() : core_customfield_generator {
        return $this->getDataGenerator()->get_plugin_generator('core_customfield');
    }

    /**
     * Test if the context is correctly set
     *
     * @covers ::instance_form_save
 */
    public function test_context() {
        global $DB, $USER;
        $contextcourse1 = \context_course::instance($this->courses[1]->id);
        $usercontext = \context_user::instance($USER->id);
        // Create a user draft file for file customfield.
        $fs = get_file_storage();
        $userfilerecord = new \stdClass;
        $userfilerecord->contextid = $usercontext->id;
        $userfilerecord->component = 'user';
        $userfilerecord->filearea  = 'draft';
        $userfilerecord->itemid    = 123456;
        $userfilerecord->filepath  = '/';
        $userfilerecord->filename  = 'customfield.txt';
        $userfilerecord->source    = 'test';
        $userfile = $fs->create_file_from_string($userfilerecord, 'Test content');

        $this->cfdata[1] = $this->get_generator()->add_instance_data($this->cfields[1],
            $this->courses[1]->id, $userfile->get_itemid());

        // Check customfield_data context.
        $this->assertEquals($contextcourse1->id, $this->cfdata[1]->get('contextid'));
        // Check file context.
        $params = ['component' => 'customfield_file', 'filearea' => 'value', 'itemid' => $this->cfdata[1]->get('id')];
        $where = "component = :component AND filearea = :filearea AND itemid = :itemid AND filename != '.'";
        $files = $DB->get_records_select('files', $where, $params);
        $this->assertCount(1, $files);
        $file = reset($files);
        $this->assertEquals($contextcourse1->id, $file->contextid);
    }
}

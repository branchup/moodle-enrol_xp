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
 * Adhoc sync task.
 *
 * @package    enrol_xp
 * @copyright  2017 Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_xp\task;
defined('MOODLE_INTERNAL') || die();

use context_course;
use moodle_exception;

/**
 * Adhoc sync task class.
 *
 * @package    enrol_xp
 * @copyright  2017 Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class adhoc_sync extends \core\task\adhoc_task {

    /**
     * Execute.
     */
    public function execute() {
        $data = $this->get_custom_data();
        $db = \block_xp\di::get('db');

        $instance = $db->get_record('enrol', ['id' => $data->instanceid]);
        $courseid = $instance->courseid;
        $context = context_course::instance($courseid);

        $factory = \block_xp\di::get('course_world_factory');
        $world = $factory->get_world($instance->customint2);
        $store = $world->get_store();

        // We only support this implementation so far.
        if (!($store instanceof \block_xp\local\xp\course_user_state_store)) {
            return;
        }

        // Get the amount of XP needed.
        $levels = $world->get_levels_info();
        try {
            $levelobj = $levels->get_level($instance->customint1);
            $xprequired = $levelobj->get_xp_required();
        } catch (moodle_exception $e) {
            // The level does not exist, or who knows...
            return;
        }

        list($enrolledsql, $enrolledparams) = get_enrolled_sql($context);

        // Now hardcode the fetching.
        // TODO Add an interface to block_xp which allows this to happen.
        $sql = "
            SELECT x.userid
              FROM {block_xp} x
             WHERE x.courseid = :courseid
               AND x.xp >= :xp
               AND x.userid NOT IN ($enrolledsql)";
        $params = $enrolledparams + [
            'courseid' => $instance->customint2,
            'xp' => $xprequired
        ];

        $plugin = enrol_get_plugin('xp');
        $recordset = $db->get_recordset_sql($sql, $params);
        foreach ($recordset as $record) {
            $plugin->enrol_user($instance, $record->userid, $instance->roleid, 0, 0, ENROL_USER_ACTIVE);
            $plugin->send_welcome_message($instance, $record->userid);
        }
        $recordset->close();
    }

}

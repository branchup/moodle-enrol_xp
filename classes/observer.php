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
 * Enrol observer.
 *
 * @package    enrol_xp
 * @copyright  2017 Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_xp;
defined('MOODLE_INTERNAL') || die();

use context_course;

/**
 * Enrol observer class.
 *
 * @package    enrol_xp
 * @copyright  2017 Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class observer {

    /**
     * Observe the event of a level up.
     *
     * @param \block_xp\event\user_leveledup $event The event.
     * @return void
     */
    public static function user_leveledup(\block_xp\event\user_leveledup $event) {
        $db = \block_xp\di::get('db');
        $userid = $event->relateduserid;

        $sql = "
            SELECT e.*
              FROM {enrol} e
         LEFT JOIN {user_enrolments} ue
                ON e.id = ue.enrolid
               AND ue.userid = :userid
             WHERE e.customint1 <= :level
               AND e.customint2 = :courseid
               AND ue.id IS NULL";

        $params = [
            'userid' => $userid,
            'courseid' => empty($event->courseid) ? SITEID : $event->courseid,
            'level' => $event->other['level']
        ];

        $instances = $db->get_records_sql($sql, $params);
        if (!$instances) {
            return;
        }

        $plugin = enrol_get_plugin('xp');
        foreach ($instances as $instance) {
            $context = context_course::instance($instance->courseid, IGNORE_MISSING);
            if (!$context || is_enrolled($context, $userid)) {
                // The course does not exist, or the user is already enrolled.
                continue;
            }
            $plugin->enrol_user($instance, $userid, $instance->roleid, 0, 0, ENROL_USER_ACTIVE);
            $plugin->send_welcome_message($instance, $userid);
        }
    }

}

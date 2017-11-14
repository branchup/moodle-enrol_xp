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
 * Sync enrolments.
 *
 * @package    enrol_xp
 * @copyright  2017 Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

$instanceid = optional_param('instanceid', 0, PARAM_INT);

$db = \block_xp\di::get('db');
$instance = $db->get_record('enrol', ['id' => $instanceid]);
$courseid = $instance->courseid;
$context = context_course::instance($courseid);

$PAGE->set_url('/enrol/xp/sync.php', ['instanceid' => $instanceid]);

require_login($courseid, false);
require_sesskey();
require_capability('enrol/xp:config', $context);

$adhocktask = new \enrol_xp\task\adhoc_sync($instanceid);
$adhocktask->set_component('enrol_xp');
$adhocktask->set_userid($USER->id);
$adhocktask->set_custom_data([
    'instanceid' => $instanceid
]);
$adhocktask->execute();
\core\task\manager::queue_adhoc_task($adhocktask);

$message = get_string('adhocsyncscheduled', 'enrol_xp');
redirect(new moodle_url('/enrol/instances.php', ['id' => $courseid]), $message);

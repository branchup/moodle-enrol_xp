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
 * Language file.
 *
 * @package    enrol_xp
 * @copyright  2017 Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['course'] = 'Go to course';
$string['adhocsyncscheduled'] = 'Missing enrolments will be synchronised in the following minutes (during cron).';
$string['invalidlevel'] = 'The level must be a number equal or higher than 2.';
$string['levelincourse'] = 'Course to attain level in';
$string['levelninvalid'] = '{$a} (invalid)';
$string['leveltoattain'] = 'Level to attain';
$string['messageprovider:welcomemessage'] = 'Level up! enrolment welcome message';
$string['notethat'] = 'Note';
$string['noteaboutexistingenrolment'] = 'This enrolment method has no effect when the user is already enrolled in the course, even if their enrolment is outdated, suspended or disabled.';
$string['pluginname'] = 'Level enrolment';
$string['privacy:metadata'] = 'The plugin does not store any user information.';
$string['syncpossiblemissing'] = 'Sync and look for possible missing enrolments.';
$string['welcomemessage'] = 'A welcome message';
$string['welcomemessage_help'] = 'A message that will be sent to users when they are enrolled in the course. Leave this empty if you do not wish to send a message.

The following placeholders are available:

* [level]: The level they had to attain
* [fullname]: The user\'s full name
* [firstname]: The user\'s first name

The format supported is [Markdown](https://en.wikipedia.org/wiki/Markdown).
';
$string['wholesite'] = 'Whole site';
$string['xp:config'] = 'Configure enrolment instances';
$string['xp:unenrol'] = 'Unenrol users';
$string['youhavebeenenrolled'] = 'You have been enrolled in a new course.';

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
 * Enrol class.
 *
 * @package    enrol_xp
 * @copyright  2017 Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_xp;
defined('MOODLE_INTERNAL') || die();

use context_course;
use core_user;
use moodle_url;
use MoodleQuickForm;
use stdClass;

/**
 * Enrol plugin class.
 *
 * @package    enrol_xp
 * @copyright  2017 Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class plugin extends \enrol_plugin {

    /**
     * Add new instance.
     *
     * @param object $course The course.
     * @param array $fields The data.
     * @return int|null
     */
    public function add_instance($course, array $fields = null) {
        $fields = $this->preprocess_data($fields);
        return parent::add_instance($course, $fields);
    }

    /**
     * Does this plugin allow manual unenrolment of users?
     *
     * @param stdClass $instance Course enrol instance.
     * @return bool
     */
    public function allow_unenrol(stdClass $instance) {
        return true;
    }

    /**
     * Return true if we can add a new instance to this course.
     *
     * @param int $courseid
     * @return bool
     */
    public function can_add_instance($courseid) {
        $context = context_course::instance($courseid);
        return has_capability('moodle/course:enrolconfig', $context) && has_capability('enrol/xp:config', $context);
    }

    /**
     * Can the user delete the instance?
     *
     * @param object $instance
     * @return bool
     */
    public function can_delete_instance($instance) {
        $context = context_course::instance($instance->courseid);
        return has_capability('moodle/course:enrolconfig', $context) && has_capability('enrol/xp:config', $context);
    }

    /**
     * Is it possible to hide/show enrol instance via standard UI?
     *
     * @param stdClass $instance
     * @return bool
     */
    public function can_hide_show_instance($instance) {
        $context = context_course::instance($instance->courseid);
        return has_capability('moodle/course:enrolconfig', $context) && has_capability('enrol/lti:config', $context);
    }

    /**
     * Adds form elements to add/edit instance form.
     *
     * @param object $instance Enrol instance or null if does not exist yet.
     * @param MoodleQuickForm $mform
     * @param context $context
     * @return void
     */
    public function edit_instance_form($instance, MoodleQuickForm $mform, $context) {
        $nameattribs = ['maxlength' => '255', 'placeholder' => get_string('pluginname', 'enrol_xp')];
        $mform->addElement('text', 'name', get_string('custominstancename', 'enrol'), $nameattribs);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'server');

        if (!$this->is_levelup_set_for_whole_site()) {
            // The level to attain.
            $mform->addElement('text', 'customint1', get_string('leveltoattain', 'enrol_xp'));
            $mform->setType('customint1', PARAM_INT);

            // The place where the level must be attained.
            $mform->addElement('course', 'customint2', get_string('levelincourse', 'enrol_xp'), [
                'exclude' => $instance->courseid
            ]);
            $mform->setType('customint2', PARAM_INT);

        } else {
            $factory = \block_xp\di::get('course_world_factory');
            $world = $factory->get_world($instance->courseid);
            $levels = $world->get_levels_info();
            $range = range(2, $levels->get_count());
            $options = array_combine($range, $range);

            // Make sure we keep the current setting.
            if (!empty($instance->customint1) && !in_array($instance->customint1, $range)) {
                $options[$instance->customint1] = get_string('levelninvalid', 'enrol_xp', $instance->customint1);
            }

            // The level to attain.
            $mform->addElement('select', 'customint1', get_string('leveltoattain', 'enrol_xp'), $options);
            $mform->setType('customint1', PARAM_INT);

            // The course where it should be attained. Note that if the value of customint2 was another
            // course, and this is being edited and save, the initial value is lost. That could happen
            // when the block is switched from course mode, to whole site mode.
            $mform->addElement('hidden', 'customint2');
            $mform->setType('customint2', PARAM_INT);
            $mform->setConstant('customint2', SITEID);
        }

        // The role to give.
        $roles = $this->get_assignable_roles($context, $instance->roleid);
        $mform->addElement('select', 'roleid', get_string('role', 'core'), $roles);

        // Welcome message.
        $mform->addElement('textarea', 'customtext1', get_string('welcomemessage', 'enrol_xp'), ['cols' => 60, 'rows' => 8]);
        $mform->addHelpButton('customtext1', 'welcomemessage', 'enrol_xp');

        // Info.
        $mform->addElement('static', 'info', get_string('notethat', 'enrol_xp'),
            get_string('noteaboutexistingenrolment', 'enrol_xp'));
    }

    /**
     * Perform custom validation of the data used to edit the instance.
     *
     * @param array $data Array of ("fieldname"=>value) of submitted data.
     * @param array $files Array of uploaded files "element_name"=>tmp_file_path.
     * @param object $instance The instance loaded from the DB.
     * @param context $context The context of the instance we are editing.
     * @return array Where keys are fields and values are error message.
     */
    public function edit_instance_validation($data, $files, $instance, $context) {
        $errors = [];

        if (empty($data['customint1']) || $data['customint1'] < 2) {
            $errors['customint1'] = get_string('invalidlevel', 'enrol_xp');
        }

        if (empty($data['customint2'])) {
            $errors['customint2'] = get_string('invaliddata', 'error');
        }

        return $errors;
    }

    /**
     * Returns action icons.
     *
     * @param stdClass $instance The instance.
     * @return array
     */
    public function get_action_icons(stdClass $instance) {
        global $OUTPUT;
        $icons = parent::get_action_icons($instance);

        if ($this->can_add_instance($instance->courseid)) {
            $url = new \moodle_url('/enrol/xp/sync.php', [
                'instanceid' => $instance->id,
                'sesskey' => sesskey()
            ]);
            $icons[] = $OUTPUT->action_icon($url, new \pix_icon('t/reset', get_string('syncpossiblemissing', 'enrol_xp'),
                'core', ['class' => 'iconsmall']));
        }

        return $icons;
    }

    /**
     * Gets a list of roles that this user can assign for the course as the default for self-enrolment.
     *
     * @param context $context the context.
     * @param integer $defaultrole the id of the role that is set as the default for self-enrolment
     * @return array index is the role id, value is the role name
     */
    public function get_assignable_roles($context, $defaultrole) {
        global $DB;
        $roles = get_assignable_roles($context, ROLENAME_BOTH);
        if (!isset($roles[$defaultrole])) {
            if ($role = $DB->get_record('role', ['id' => $defaultrole])) {
                $roles[$defaultrole] = role_get_name($role, $context, ROLENAME_BOTH);
            }
        }
        return $roles;
    }

    /**
     * Returns defaults for new instances.
     *
     * @return array
     */
    public function get_instance_defaults() {
        global $DB;

        $roleid = $DB->get_field('role', 'id', ['archetype' => 'student'], IGNORE_MULTIPLE);

        return [
            'roleid' => $roleid
        ];
    }

    /**
     * Returns localised name of enrol instance
     *
     * @param object $instance (null is accepted too)
     * @return string
     */
    public function get_instance_name($instance) {
        if (empty($instance->name)) {
            $db = \block_xp\di::get('db');
            $enrol = $this->get_name();
            $name = get_string('pluginname', 'enrol_' . $enrol);
            $course = get_course($instance->customint2);
            $context = context_course::instance($course->id);
            $role = $db->get_record('role', ['id' => $instance->roleid]);
            $rolename = role_get_name($role, $context);

            $coursename = get_string('wholesite', 'enrol_xp');
            if ($instance->customint2 != SITEID) {
                $coursename = format_string($course->fullname, true, ['context' => $context]);
            }

            return sprintf("%s (%s at level %s+ in %s)", $name, $rolename, $instance->customint1, $coursename);
        } else {
            $context = context_course::instance($instance->courseid);
            return format_string($instance->name, true, ['context' => $context]);
        }
    }

    /**
     * Is Level up! set for the whole site?
     *
     * @return bool
     */
    protected function is_levelup_set_for_whole_site() {
        $config = \block_xp\di::get('config');
        return $config->get('context') == CONTEXT_SYSTEM;;
    }

    /**
     * Preprocess data.
     *
     * @param array|null $data The data.
     * @return array|null
     */
    protected function preprocess_data(array $data = null) {
        if (!$data) {
            return;
        }

        $text = $data['customtext1'];
        $stripped = trim(str_replace('&nbsp;', '', strip_tags($text)));
        if (!empty($stripped)) {
            $data['customtext1'] = $text;
        } else {
            $data['customtext1'] = '';
        }

        return $data;
    }

    /**
     * Send message.
     *
     * @param stdClass $instance The instance.
     * @param int|stdClass $userid The user ID.
     * @return void
     */
    public function send_welcome_message($instance, $userorid) {
        if (empty($instance->customtext1)) {
            return;
        }

        $user = $userorid;
        if (!is_object($user)) {
            $user = core_user::get_user($userorid);
        }

        $text = $instance->customtext1;
        $text = str_replace('[level]', $instance->customint1, $text);
        $text = str_replace('[fullname]', fullname($user), $text);
        $text = str_replace('[firstname]', $user->firstname, $text);

        $message = new \core\message\message();
        $message->courseid = $instance->courseid;
        $message->component = 'enrol_xp';
        $message->name = 'welcomemessage';
        $message->notification = 1;
        $message->userfrom = core_user::get_noreply_user();
        $message->userto = $user->id;
        $message->subject = get_string('youhavebeenenrolled', 'enrol_xp');
        $message->fullmessage = $text;
        $message->fullmessageformat = FORMAT_MARKDOWN;
        $message->fullmessagehtml = format_text($text, FORMAT_MARKDOWN, [
            'context' => context_course::instance($instance->courseid)
        ]);
        $message->smallmessage = '';
        $message->contexturl = new moodle_url('/course/view.php', ['id' => $instance->courseid]);
        $message->contexturlname = get_string('course', 'core');
        message_send($message);
    }

    /**
     * Attempt to automatically enrol current user in course without any interaction,
     *
     * This should return either a timestamp in the future or false.
     *
     * @param stdClass $instance Course enrol instance.
     * @return bool|int False means not enrolled, integer means timeend, 0 means forever.
     */
    public function try_autoenrol(stdClass $instance) {
        global $USER;

        if (true) {
            // Toggle this feature off for now, more testing needed.
            return false;
        }

        if (!enrol_is_enabled('xp') || $instance->status == ENROL_INSTANCE_DISABLED) {
            return false;
        }

        if (!class_exists('block_xp\di')) {
            return false;
        }

        $level = $instance->customint1;
        $courseid = $instance->customint2;

        $factory = \block_xp\di::get('course_world_factory');
        $world = $factory->get_world($courseid);
        $store = $world->get_store();
        $state = $store->get_state($USER->id);

        if ($state->get_level()->get_level() >= $level) {
            // This does not actually enrol the user, so more testing is needed to determine
            // whether we really want to force enrol the user at this point or not. Technically
            // they should have been enrolled already.
            return 0;
        }

        return false;
    }

    /**
     * Update instance of enrol plugin.
     *
     * @param stdClass $instance The instance.
     * @param stdClass $data The data.
     * @return boolean
     */
    public function update_instance($instance, $data) {
        $data = $this->preprocess_data((array) $data);
        return parent::update_instance($instance, (object) $data);
    }

    /**
     * Use standard editing UI.
     *
     * @return bool
     */
    public function use_standard_editing_ui() {
        return true;
    }
}

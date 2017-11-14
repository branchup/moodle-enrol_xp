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
 * Enrol install.
 *
 * @package    enrol_xp
 * @copyright  2017 Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Enrol install function.
 *
 * @return true
 */
function xmldb_enrol_xp_install() {

    // Enable the method by default.
    // Code copied from admin/enrol.php.
    $enabled = enrol_get_plugins(true);
    $enabled = array_keys($enabled);
    $enabled[] = 'xp';
    set_config('enrol_plugins_enabled', implode(',', $enabled));
    core_plugin_manager::reset_caches();
    $syscontext = context_system::instance();
    $syscontext->mark_dirty(); // Resets all enrol caches.

}

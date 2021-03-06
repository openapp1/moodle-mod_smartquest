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
 * Setting page for questionaire module
 *
 * @package    mod
 * @subpackage smartquest
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $options = array(0 => get_string('no'), 1 => get_string('yes'));
    $str = get_string('configusergraphlong', 'smartquest');
    $settings->add(new admin_setting_configselect('smartquest/usergraph',
                                    get_string('configusergraph', 'smartquest'),
                                    $str, 0, $options));
    $settings->add(new admin_setting_configtext('smartquest/maxsections',
                                    get_string('configmaxsections', 'smartquest'),
                                    '', 10, PARAM_INT));
    $choices = array(
        'response' => get_string('response', 'smartquest'),
        'submitted' => get_string('submitted', 'smartquest'),
        'course' => get_string('course'),
        'group' => get_string('group'),
        'id' => get_string('id', 'smartquest'),
        //'numberid'=>  get_string('numberid', 'smartquest'),
        'fullname' => get_string('fullname'),
        'username' => get_string('username'),
        //'phonenumber'=>  get_string('phonenumber', 'smartquest'),
        // 'brigade'=>  get_string('brigade', 'smartquest'),
        // 'class'=>  get_string('class', 'smartquest'),
        'aboutusercsv' => get_string('aboutusercsv', 'smartquest'),
        'department' => get_string('department'),
        // 'team'=>  get_string('team', 'smartquest'),
        // 'manage'=>  get_string('manage', 'smartquest'),
        'institution' => get_string('institution'),
       

    );

    $settings->add(new admin_setting_configmultiselect('smartquest/downloadoptions',
            get_string('textdownloadoptions', 'smartquest'), '', array_keys($choices), $choices));
}

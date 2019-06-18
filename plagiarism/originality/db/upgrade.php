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
 * Created by JetBrains PhpStorm.
 * Author: Eliad Carmi
 */


if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

function xmldb_plagiarism_originality_upgrade($oldversion = 0) {
    global $DB, $CFG;
    $dbman = $DB->get_manager();

    if ($oldversion < 2018121303) {
        // Define table plagiarism_originality_resp to be created.
        $table = new xmldb_table('plagiarism_originality_resp');

        // Adding fields to table plagiarism_originality_resp.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('assignment', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('grade', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('file', XMLDB_TYPE_TEXT, 'medium', null, null, null);
        // Version 3.1.7 adding field file identifier.
        $table->add_field('file_identifier', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);

        // Version 4.0.5 adding field moodle_file_id
        $table->add_field('moodle_file_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);

        // Adding keys to table plagiarism_originality_resp.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for plagiarism_originality_resp.
        if (!$dbman->table_exists($table)) {
        	$dbman->create_table($table);
        } else {
            $field = new xmldb_field('file_identifier', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'file');
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
            $field = new xmldb_field('moodle_file_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'file_identifier');
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }

        $table = new xmldb_table('plagiarism_originality_req');

        // Adding fields to table plagiarism_originality_resp.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, 0);
        $table->add_field('assignment', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('file', XMLDB_TYPE_TEXT, 'medium', null, null, null);
        // Version 3.1.7 adding field file identifier.
        $table->add_field('file_identifier', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('moodle_file_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);

        // Version 4.0.7 adding field resubmit_date
        $table->add_field('submit_date', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);


        // Adding keys to table plagiarism_originality_resp.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for plagiarism_originality_req.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        } else {
            $field = new xmldb_field('file', XMLDB_TYPE_TEXT, 'medium', null, null, null);

            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }

            $field = new xmldb_field('file_identifier', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'file');
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }

            $field = new xmldb_field('moodle_file_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'file_identifier');
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }

            $field = new xmldb_field('submit_date', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'moodle_file_id');
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }

        }

        $table = new xmldb_table('plagiarism_originality_conf');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, 0);
        $table->add_field('cm', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('student_view_report', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        } else {
            $field = new xmldb_field('student_view_report', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }

        // Originality savepoint reached.
        upgrade_plugin_savepoint(true, 2018121303, 'plagiarism', 'originality');

    }

    return true;
}


<?php
/**
 * Created by JetBrains PhpStorm.
 * Author: Eliad Carmi
 */

function xmldb_plagiarism_originality_upgrade($oldversion = 0)
{
	global $DB, $CFG;
	$dbman = $DB->get_manager();

	if ($oldversion < 2018010200) {
		// Define table plagiarism_originality_resp to be created
		$table = new xmldb_table('plagiarism_originality_resp');

		// Adding fields to table plagiarism_originality_resp
		$table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
		$table->add_field('assignment', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
		$table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
		$table->add_field('grade', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
		$table->add_field('file', XMLDB_TYPE_TEXT, 'medium', null, null, null);
        //$table->add_field('file_identifier', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, null, 0);   //Version 3.1.7 adding field file identifier

		// Adding keys to table plagiarism_originality_resp
		$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

		// Conditionally launch create table for plagiarism_originality_resp
		if (!$dbman->table_exists($table)) {
			$dbman->create_table($table);
		}else{
            //parameters for new xmldb_field: name, type=null, precision=null, unsigned=null, notnull=null, sequence=null, default=null, previous=null
            /*
            $field = new xmldb_field('file_identifier', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'file');//Version 3.1.7 adding field file identifier
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
            */
        }

        $table = new xmldb_table('plagiarism_originality_req');

        // Adding fields to table plagiarism_originality_resp
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, 0);
        $table->add_field('assignment', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('file', XMLDB_TYPE_TEXT, 'medium', null, null, null);
        //$table->add_field('file_identifier', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, null, 0); //Version 3.1.7 adding field file identifier

        // Adding keys to table plagiarism_originality_resp
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for plagiarism_originality_req
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }else{
            $field = new xmldb_field('grade', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
            if ($dbman->field_exists($table, $field)) {
                $dbman->drop_field($table, $field);
            }

            $field = new xmldb_field('file', XMLDB_TYPE_TEXT, 'medium', null, null, null);

            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }

            //parameters for new xmldb_field: name, type=null, precision=null, unsigned=null, notnull=null, sequence=null, default=null, previous=null
            /*
            $field = new xmldb_field('file_identifier', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'file');  //Version 3.1.7 adding field file identifier
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
            */
        }


        $table = new xmldb_table('plagiarism_originality_conf');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, 0);
        $table->add_field('cm', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('student_view_report', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }else{
            $field = new xmldb_field('student_view_report', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }

		// originality savepoint reached
		upgrade_plugin_savepoint(true, 2018010200, 'plagiarism', 'originality');

    }

	return true;
}



?>
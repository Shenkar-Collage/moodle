<?php



function xmldb_plagiarism_originality_uninstall()
{
    global $DB;

    $DB->delete_records('config_plugins',array("name"=>'originality_key', 'plugin'=>'plagiarism'));
    $DB->delete_records('config_plugins',array("name"=>'originality_server', 'plugin'=>'plagiarism'));
    $DB->delete_records('config_plugins',array("name"=>'originality_use', 'plugin'=>'plagiarism'));
    $DB->delete_records('config_plugins',array("name"=>'originality_view_report', 'plugin'=>'plagiarism'));
    /*
     * It goes with file_identifier for next version (probably 3.1.8)
     */
    //$DB->delete_records('config_plugins',array("name"=>'originality_allow_mutiple_file_submission', 'plugin'=>'plagiarism'));
}


?>
<?php
defined ( 'MOODLE_INTERNAL' ) || die ();

function xmldb_local_ws_rashim_upgrade($oldversion) {
    global $DB;
    
    $dbman = $DB->get_manager ();
    
    $table = new xmldb_table ( 'meetings' );
    $field = new xmldb_field ( 'section_id', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'section_num' );
    
    if (! $dbman->field_exists ( $table, $field )) {
        $dbman->add_field ( $table, $field );
    }
    
    $table = new xmldb_table ( 'question_trace' );
    if (! $dbman->table_exists ( $table )) {
        $dbman->install_one_table_from_xmldb_file ( __DIR__ . '/install.xml', 'question_trace' );
    }
    
    return true;
}

?>
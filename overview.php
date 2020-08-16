<?php
require_once ("../../config.php");
require_once ("../../lib/moodlelib.php");

global $DB;

$PAGE->set_url ( '/local/ws_rashim/overview.php' );
$PAGE->set_context ( context_system::instance () );
$PAGE->set_pagelayout ( 'admin' );
$PAGE->set_cacheable ( false );
$PAGE->set_heading ( $SITE->fullname );
$PAGE->set_title ( $SITE->fullname . ': ' . get_string ( 'overview_page', 'local_ws_rashim' ) );

echo $OUTPUT->header ();
echo $OUTPUT->heading ( get_string ( 'overview_page', 'local_ws_rashim' ) );

echo html_writer::tag ( 'hr', '' );

$os_version = php_uname ( 's' ) . '<br/>' . php_uname ( 'r' ) . '<br/>' . php_uname ( 'v' );
$php_version = phpversion ();
$moodle_version = get_config ( 'moodle', 'version' ) . '<br/>' . get_config ( 'moodle', 'release' );
$db_server = $DB->get_server_info ();
$db_version = $DB->get_name () . '<br/>' . $db_server ['description'] . '<br/>' . $db_server ['version'] . '<br/>' .
        $DB->export_dbconfig ()->dbname;
$soap = get_soap_info ();

global_info_block ( $os_version, 'configsectionoperatingsystem', 'admin' );
global_info_block ( $php_version, 'phpversion', 'install' );
global_info_block ( $moodle_version, 'moodleversion' );
global_info_block ( $db_version, 'databasehead', 'install' );
global_info_block ( $soap, 'overview_soap', 'local_ws_rashim' );

echo html_writer::tag ( 'hr', '' );

echo $OUTPUT->heading ( get_string ( 'plugincheck' ), 4 );

$table = new html_table ();
$table->head = array (
    '',
    get_string ( 'displayname', 'plugin' ),
    get_string ( 'version' ),
    get_string ( 'release', 'plugin' ),
    get_string ( 'overview_notes', 'local_ws_rashim' )
);

plugin_info ( '/auth/rashat_ron', 'auth_rashat_ron', $table );
plugin_info ( '/blocks/exam_aprove', 'block_exam_aprove', $table );
plugin_info ( '/blocks/link_rashim', 'block_link_rashim', $table );
plugin_info ( '/blocks/question_sync', 'block_question_sync', $table );
plugin_info ( '/blocks/rashim_ktree', 'block_rashim_ktree', $table );
plugin_info ( '/course/format/rashim_collapse', 'format_rashim_collapse', $table );
plugin_info ( '/local/exams_report', 'local_exams_report', $table );
plugin_info ( '/local/grades_by_categories', 'local_grades_by_categories', $table );
plugin_info ( '/local/rashim_quiz_ex', 'local_rashim_quiz_ex', $table );
plugin_info ( '/local/ws_rashim', 'local_ws_rashim', $table );
plugin_info ( '/mod/readandsign', 'mod_readandsign', $table );
plugin_info ( '/question/behaviour/deferredfeedback_with_opinion', 'qbehaviour_deferredfeedback_with_opinion', $table );

echo html_writer::table ( $table );

echo $OUTPUT->footer ();

/*
 * helper functions
 */
function global_info_block($content, $string, $component = '') {
    global $OUTPUT;

    echo $OUTPUT->heading ( get_string ( $string, $component ), 4 );
    echo $OUTPUT->box_start ( 'generalbox', null, array (
        'style' => 'padding-left: 2rem; padding-right: 2rem;'
    ) );
    echo $content;
    echo $OUTPUT->box_end ();
}

function plugin_info($path, $component, $table) {
    global $CFG, $OUTPUT;

    $plugin = new stdClass ();

    $version_path = $CFG->dirroot . $path . '/version.php';

    if (file_exists ( $version_path )) {
        include ($version_path);

        $write = $OUTPUT->pix_icon ( 'i/settings', get_string ( 'overview_caninstall', 'local_ws_rashim' ), 'moodle',
                array (
                    'class' => 'iconlarge'
                ) );
        $read = $OUTPUT->pix_icon ( 'i/configlock', get_string ( 'overview_cantinstall', 'local_ws_rashim' ), 'moodle',
                array (
                    'class' => 'iconlarge'
                ) );

        $table->data [] = array (
            html_writer::img ( new moodle_url ( $path . '/pix/icon.png' ), get_string ( 'pluginname', $component ),
                    array (
                        'class' => 'iconlarge pluginicon',
                        'width' => '24px'
                    ) ),
            get_string ( 'pluginname', $component ),
            $plugin->version,
            $plugin->release,
            is_writeable ( dirname ( $CFG->dirroot . $path ) ) ? $write : $read
        );
    } else {
        $table->data [] = array (
            $OUTPUT->pix_icon ( 'i/invalid', get_string ( 'dependencymissing', 'plugin' ), 'moodle',
                    array (
                        'class' => 'iconlarge pluginicon'
                    ) ),
            $component,
            get_string ( 'dependencymissing', 'plugin' ),
            get_string ( 'dependencymissing', 'plugin' ),
            null
        );
    }
}

function get_soap_info() {
    $config = get_config ( 'local_ws_rashim' );
    $soap = true;

    if (empty ( $config->api_url )) {
        $content = '<b>' . get_string ( 'overview_soap_empty', 'local_ws_rashim' ) . '</b></br>';
    } else {
        $content = $config->api_url . '<br/>';
        $content .= '<b>' . get_string ( 'overview_soap_result', 'local_ws_rashim' ) . '</b></br>';

        try {
            $client = new SoapClient ( $config->api_url . '/MichlolApi.asmx?WSDL',
                    array (
                        'exceptions' => true,
                        'trace' => true,
                        'soap_version' => SOAP_1_2,
                        'encoding' => 'UTF-8',
                        'features' => SOAP_SINGLE_ELEMENT_ARRAYS,
                        'cache_wsdl' => WSDL_CACHE_NONE
                    ) );
        } catch ( SoapFault $ex ) {
            $content .= "&nbsp;&nbsp;&nbsp; " . get_string ( 'overview_soap_code', 'local_ws_rashim' ) . " $ex->faultcode <br/>" .
                    "&nbsp;&nbsp;&nbsp; " . get_string ( 'overview_soap_text', 'local_ws_rashim' ) . " $ex->faultstring <br/>";

            $soap = false;
        }

        if ($soap) {
            $content .= "&nbsp;&nbsp;&nbsp; " . get_string ( 'overview_soap_done', 'local_ws_rashim' ) . " <br/>";
        }
    }

    return ($content);
}

?>

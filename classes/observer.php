<?php
defined ( 'MOODLE_INTERNAL' ) || die ();

function grade_update_112($updated_grade) {
    return <<<EOXML
<?xml version="1.0" encoding="utf-8" ?>
<PARAMS>
    <ZHT>$updated_grade->user_idnumber</ZHT>
    <BHN_KRS>$updated_grade->michlol_krs_bhn_krs</BHN_KRS>
    <BHN_SMS>$updated_grade->michlol_krs_bhn_sms</BHN_SMS>
    <BHN_SID>$updated_grade->michlol_krs_bhn_sid</BHN_SID>
    <ZIN>$updated_grade->finalgrade</ZIN>
</PARAMS>
EOXML;
}

function grade_update_113($updated_grade) {
    return <<<EOXML
<?xml version="1.0" encoding="utf-8" ?>
<PARAMS>
	<ZHT>$updated_grade->user_idnumber</ZHT>
	<TASKID>$updated_grade->course_idnumber</TASKID>
	<ZIN>$updated_grade->finalgrade</ZIN>
</PARAMS>
EOXML;
}

function bhn_for_print_119($exam, $students) {
    return <<<EOXML
<?xml version="1.0" encoding="utf-8" ?>
<PARAMS>
	<EXAM>$exam</EXAM>
	<STUDENTS>$students</STUDENTS>
</PARAMS>
EOXML;
}

class local_ws_rashim_observer {

    protected static function trace_message($message, $context) {
        $event = \local_ws_rashim\event\msg_logged::create ( 
                array (
                    'context' => isset ( $context ) ? $context : context_system::instance (),
                    'other' => array (
                        'message' => $message 
                    ) 
                ) );
        
        $event->trigger ();
    }

    protected static function send_grade($course_id, $mod, $mod_id, $user_idnumber, $course_idnumber, $finalgrade) {
        global $CFG, $DB;
        
        $soap = true;
        
        $config = get_config ( 'local_ws_rashim' );
        
        if (empty ( $config->api_url )) {
            return;
        }
        
        $version_path = $CFG->dirroot . '/local/rashim_quiz_ex/version.php';
        
        if (file_exists ( $version_path )) {
            // quiz extensions should be installed only on the moodle
            // used for it, in which case grades are returned in a different way
            
            local_ws_rashim_observer::trace_message ( "send_grade - No grade sent, because of rashim_quiz_ex...",
                    context_course::instance ( $course_id ) );
            
            return;
        }
        
        $send112 = true;
        
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
            local_ws_rashim_observer::trace_message ( 
                    "send_grade - $ex->faultcode, $ex->faultstring, $ex->faultactor, $ex->detail, $ex->headerfault",
                    context_course::instance ( $course_id ) );
            
            $soap = false;
        }
        
        if ($soap) {
            $updated_grade = $DB->get_record ( 'matalot',
                    array (
                        'course_id' => $course_id,
                        'moodle_type' => $mod,
                        'moodle_id' => $mod_id 
                    ) );
            
            if (! $updated_grade) {
                $updated_grade = new stdClass ();
                
                $send112 = false;
            }
            
            $updated_grade->finalgrade = $finalgrade;
            $updated_grade->user_idnumber = $user_idnumber;
            $updated_grade->course_idnumber = $course_idnumber;
            
            if ($send112) {
                // call 112
                $param = array (
                    'P_RequestParams' => array (
                        'RequestID' => 112,
                        'InputData' => grade_update_112 ( $updated_grade ) 
                    ),
                    'Authenticator' => array (
                        'UserName' => $config->api_user,
                        'Password' => $config->api_psw 
                    ) 
                );
            } else {
                // call 113
                $param = array (
                    'P_RequestParams' => array (
                        'RequestID' => 113,
                        'InputData' => grade_update_113 ( $updated_grade ) 
                    ),
                    'Authenticator' => array (
                        'UserName' => $config->api_user,
                        'Password' => $config->api_psw 
                    ) 
                );
            }
            
            $result = $client->ProcessRequest ( $param );
        }
    }

    public static function grades_export_handler(\core\event\user_graded $eventdata) {
        global $DB;
        
        $grade = $eventdata->get_grade ();
        $grade_item = $grade->grade_item;
        
        $user = $DB->get_record ( 'user', array (
            'id' => $grade->userid 
        ) );
        
        $course = $DB->get_record ( 'course', array (
            'id' => $grade_item->courseid 
        ) );
        
        local_ws_rashim_observer::send_grade ( $course->id, $grade_item->itemmodule, $grade_item->iteminstance, $user->idnumber,
                $course->idnumber, $grade->finalgrade );
        
        return true;
    }

    public static function course_deleted_handler(\core\event\course_deleted $eventdata) {
        global $DB;
        
        if (! isset ( $eventdata->other ['nodelete'] ) || $eventdata->other ['nodelete'] == 0) {
            $conditions = array (
                "course_id" => $eventdata->courseid 
            );
            $DB->delete_records ( 'matalot', $conditions );
            $DB->delete_records ( 'meetings', $conditions );
        }
        
        return true;
    }

    public static function course_module_deleted_handler(\core\event\course_module_deleted $eventdata) {
        global $DB;
        
        $conditions = array (
            "course_id" => $eventdata->courseid,
            "moodle_type" => $eventdata->other ['modulename'],
            "moodle_id" => $eventdata->other ['instanceid'] 
        );
        $DB->delete_records ( 'matalot', $conditions );
        
        return true;
    }

    public static function send_bhn_for_print($exam, $students) {
        $config = get_config ( 'local_ws_rashim' );
        
        if (empty ( $config->api_url )) {
            return false;
        }
        
        $client = new SoapClient ( $config->api_url . '/MichlolApi.asmx?WSDL',
                array (
                    'exceptions' => true,
                    'trace' => true,
                    'soap_version' => SOAP_1_2,
                    'encoding' => 'UTF-8',
                    'features' => SOAP_SINGLE_ELEMENT_ARRAYS,
                    'cache_wsdl' => WSDL_CACHE_NONE 
                ) );
        
        $param = array (
            'P_RequestParams' => array (
                'RequestID' => 119,
                'InputData' => bhn_for_print_119 ( $exam, $students ) 
            ),
            'Authenticator' => array (
                'UserName' => $config->api_user,
                'Password' => $config->api_psw 
            ) 
        );
        
        $result = $client->ProcessRequest ( $param );
        
        return ($result->ProcessRequestResult->Result == 'Success');
    }

    public static function question_updated(\core\event\question_updated $event) {
        global $DB;
        
        $id = $event->get_data () ['objectid'];
        $course = $event->get_data () ['courseid'];
        
        $meeting = $DB->get_record ( 'meetings', array (
            'course_id' => $course 
        ), '*', IGNORE_MULTIPLE );
        
        if ($meeting && $meeting->snl == '9999') {
            $question = $DB->get_record ( 'question', array (
                'id' => $id 
            ) );
            
            if (! $trace = $DB->get_record ( 'question_trace',
                    array (
                        'question_id' => $id,
                        'done' => 0 
                    ) )) {
                $trace = new stdClass ();
                $trace->question_id = $id;
                $trace->question_text = $question->name;
                $trace->question_stamp = $question->stamp;
                $trace->trace_type = 1; // for update
                $trace->done = 0;
                $trace->timecreated = time ();
                $trace->timemodified = time ();
                
                $DB->insert_record ( 'question_trace', $trace );
            } else {
                $trace->question_text = $question->name;
                $trace->timemodified = time ();
                
                $DB->update_record ( 'question_trace', $trace );
            }
        }
    }

    public static function question_deleted(\core\event\question_deleted $event) {
        global $DB;
        
        $id = $event->get_data () ['objectid'];
        $course = $event->get_data () ['courseid'];
        
        $meeting = $DB->get_record ( 'meetings', array (
            'course_id' => $course 
        ), '*', IGNORE_MULTIPLE );
        
        if ($meeting && $meeting->snl == '9999') {
            $question = $event->get_record_snapshot ( 'question', $id );
            
            if (! $trace = $DB->get_record ( 'question_trace',
                    array (
                        'question_id' => $id,
                        'done' => 0 
                    ) )) {
                $trace = new stdClass ();
                $trace->question_id = $event->get_data () ['objectid'];
                $trace->question_text = $question->name;
                $trace->question_stamp = $question->stamp;
                $trace->trace_type = 2; // for delete
                $trace->done = 0;
                $trace->timecreated = time ();
                $trace->timemodified = time ();
                
                $DB->insert_record ( 'question_trace', $trace );
            } else {
                $trace->question_text = $question->name;
                $trace->trace_type = 2; // for delete
                $trace->timemodified = time ();
                
                $DB->update_record ( 'question_trace', $trace );
            }
        }
    }
}

?>
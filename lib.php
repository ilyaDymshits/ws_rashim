<?php
defined ( 'MOODLE_INTERNAL' ) || die ();

require_once (__DIR__ . '../../../course/lib.php');
require_once (__DIR__ . '../../../course/format/lib.php');
require_once (__DIR__ . '../../../course/modlib.php');
require_once (__DIR__ . '../../../user/lib.php');
require_once (__DIR__ . '../../../user/profile/lib.php');
require_once (__DIR__ . '../../../group/lib.php');
require_once (__DIR__ . '../../../mod/assign/lib.php');
require_once (__DIR__ . '../../../mod/quiz/lib.php');
require_once (__DIR__ . '../../../mod/url/lib.php');
require_once (__DIR__ . '../../../lib/coursecatlib.php');
require_once (__DIR__ . '../../../lib/moodlelib.php');
require_once (__DIR__ . '../../../lib/enrollib.php');
require_once (__DIR__ . '../../../backup/util/includes/backup_includes.php');
require_once (__DIR__ . '../../../backup/util/includes/restore_includes.php');
require_once (__DIR__ . '../../../lib/filelib.php');
require_once (__DIR__ . '../../../mod/quiz/attemptlib.php');
require_once (__DIR__ . '../../../mod/quiz/accessmanager.php');

require_once ('locallib.php');

class error_msg {
    static $E5001 = 'Could not get validated client session.';
    // static $E5002 = '';
    // static $E5003 = '';
    static $E5004 = 'Failed to close session.';
    static $E5005 = 'Could not find session.';
    static $E5006 = 'Access is denied.';
    static $E5007 = 'Missing required fields.';
    static $E5008 = 'Invalid username and / or password.';
    static $E5009 = 'User cannot open more than one sessions.';
    static $E5010 = 'Could not add user.';
    static $E5011 = 'User does not exists.';
    static $E5012 = 'API call failed for exam printing.';
    static $E5013 = 'Exam has no questions.';
    static $E5014 = 'Course does not exists.';
    static $E5015 = 'Can not find connected exam.';
    static $E5016 = 'Connected exam is of unsupported type.';
    static $E5017 = 'Parent category does not exists.';
    static $E5018 = 'Category does not exists.';
    static $E5019 = 'Could not delete course.';
    static $E5020 = 'Could not add category.';
    static $E5021 = 'All the permitted attempts were done.';
    // static $E5022 = '';
    // static $E5023 = '';
    // static $E5024 = '';
    // static $E5025 = '';
    static $E5026 = 'Could not add/update syllabus.';
    static $E5027 = 'Could not create group.';
    static $E5028 = 'Could not create grouping.';
    static $E5029 = 'Could not add group to grouping.';
    static $E5030 = 'Could not add exam module.';
    // static $E5031 = '';
    static $E5032 = 'Exam does not exists.';
    static $E5033 = 'Module does not exists.';
    // static $E5034 = '';
    static $E5035 = 'Could not create course section.';
    static $E5036 = 'Course section does not exists.';
    // static $E5037 = '';
    // static $E5038 = '';
    // static $E5039 = '';
    static $E5040 = 'Course for read & sign already exists.';
    static $E5041 = 'Meeting does not exists.';
    static $E5042 = 'Error reading XML.';
    static $E5043 = 'Role does not exists.';
    static $E5044 = 'Can not add user to group.';
}

class exam_answer {
    public $exam;
    public $question;
    public $id;
    public $text;

    public function __construct($exam, $question, $id, $text) {
        $this->exam = $exam;
        $this->question = $question;
        $this->id = $id;
        $this->text = $text;
    }
}

class exam_matchsubanswer {
    public $exam;
    public $question;
    public $id;
    public $text;

    public function __construct($exam, $question, $id, $text) {
        $this->exam = $exam;
        $this->question = $question;
        $this->id = $id;
        $this->text = $text;
    }
}

class exam_matchanswer {
    public $id;
    public $left;
    public $right;

    public function __construct($id) {
        $this->id = $id;

        $this->left = array ();
        $this->right = array ();
    }
}

class exam_question {
    public $exam;
    public $id;
    public $type;
    public $title;
    public $text;
    public $weight;
    public $maxmark;
    public $section;
    public $answers;

    public function __construct($exam, $id, $type, $title, $text, $weight, $maxmark, $section) {
        $this->exam = $exam;
        $this->id = $id;
        $this->type = $type;
        $this->title = $title;
        $this->text = $text;
        $this->weight = $weight;
        $this->maxmark = $maxmark;
        $this->section = $section;
    }
}

class exam_section {
    public $exam;
    public $id;
    public $title;
    public $text;
    public $weight;
    public $requested;

    public function __construct($exam, $id, $title, $text, $weight, $requested) {
        $this->exam = $exam;
        $this->id = $id;
        $this->title = $title;
        $this->text = $text;
        $this->weight = $weight;
        $this->requested = $requested;
    }
}

class exam {
    public $michlol_krs;
    public $michlol_sms;
    public $michlol_sid;
    public $moodle_course;
    public $moodle_type;
    public $moodle_id;
    public $title;
    public $text;
    public $questions;
    public $sections;

    public function __construct($krs, $sms, $sid, $course, $type, $id, $title, $text) {
        $this->michlol_krs = $krs;
        $this->michlol_sms = $sms;
        $this->michlol_sid = $sid;
        $this->moodle_course = $course;
        $this->moodle_type = $type;
        $this->moodle_id = $id;
        $this->title = $title;
        $this->text = $text;

        $this->questions = array ();
        $this->sections = array ();
    }
}

class student_matchanswer {
    public $left;
    public $right;
}

class student_answer {
    public $mana;
    public $exam;
    public $student;
    public $question;
    public $id;
    public $order;

    public function __construct($mana, $exam, $student, $question, $id, $order) {
        $this->mana = $mana;
        $this->exam = $exam;
        $this->student = $student;
        $this->question = $question;
        $this->id = $id;
        $this->order = $order;
    }
}

class student_question {
    public $mana;
    public $exam;
    public $student;
    public $id;
    public $order;
    public $answers;

    public function __construct($mana, $exam, $student, $id, $order) {
        $this->mana = $mana;
        $this->exam = $exam;
        $this->student = $student;
        $this->id = $id;
        $this->order = $order;
    }
}

class student {
    public $mana;
    public $exam;
    public $id;
    public $count;
    public $questions;

    public function __construct($mana, $exam, $id, $count) {
        $this->mana = $mana;
        $this->exam = $exam;
        $this->id = $id;
        $this->count = $count;

        $this->questions = array ();
    }
}

class student_exam {
    public $mana;
    public $moodle_id;
    public $students;

    public function __construct($mana, $id) {
        $this->mana = $mana;
        $this->moodle_id = $id;

        $this->students = array ();
    }
}

class server {
    protected $DB;
    protected $CFG;
    protected $config;
    protected $local;
    protected $admin;
    protected $sessiontimeout = 1800;
    protected $xml_map = array (
        'answers' => 'a',
        'count' => 'c',
        'exam' => 'x',
        'exam_answer' => 'xa',
        'exam_matchanswer' => 'xm',
        'exam_matchsubanswer' => 'xs',
        'exam_question' => 'xq',
        'exam_section' => 'es',
        'id' => 'i',
        'left' => 'l',
        'mana' => 'm',
        'michlol_krs' => 'mk',
        'michlol_sid' => 'ms',
        'michlol_sms' => 'mm',
        'moodle_course' => 'mc',
        'moodle_id' => 'mi',
        'moodle_type' => 'my',
        'order' => 'o',
        'question' => 'q',
        'questions' => 'qs',
        'requested' => 'rq',
        'right' => 'r',
        'sections' => 'ss',
        'section' => 'sc',
        'student' => 's',
        'students' => 'st',
        'student_answer' => 'sa',
        'student_exam' => 'sx',
        'student_matchanswer' => 'sm',
        'student_question' => 'sq',
        'text' => 't',
        'title' => 'e',
        'type' => 'y',
        'weight' => 'w',
        'maxmark' => 'k'
    );

    public function __construct($local = false) {
        global $DB;
        global $CFG;

        $this->DB = $DB;
        $this->CFG = $CFG;
        $this->config = get_config ( 'local_ws_rashim' );
        $this->local = $local;
    }

    public function server($local = false) {
        self::__construct ( $local );
    }

    protected function error($err, $msg = '') {
        if ($msg == '') {
            $errno = 'E' . $err;
            $msg = error_msg::$$errno;
        }

        if ($this->local) {
            $loc_err->err = $err;
            $loc_err->msg = $msg;

            return $loc_err;
        } else {
            throw new SoapFault ( 'Server', "{$err}~{$msg}" );
        }
    }

    protected function valid_login($session_key, $admin_name, $admin_psw) {
        return $this->admin_login ( $admin_name, $admin_psw );
    }

    protected function admin_login($admin_name, $admin_psw) {
        $conditions = array (
            'username' => $admin_name
        );
        if (! $admin = $this->DB->get_record ( 'user', $conditions )) {
            return $this->error ( 5008 );
        }

        // we do not use this function in the first place
        // because the functon creates the user if does not exists!
        $admin = authenticate_user_login ( $admin_name, $admin_psw );

        complete_user_login ( $admin );

        if (($admin === false) || ($admin && $admin->id == 0)) {
            return $this->error ( 5008 );
        } else {
            if (! is_siteadmin ( $admin->id )) {
                return $this->error ( 5006 );
            } else {
                $event = \local_ws_rashim\event\user_loggedin::create ( 
                        array (
                            'userid' => $admin->id,
                            'objectid' => $admin->id,
                            'other' => array (
                                'username' => $admin->username
                            )
                        ) );

                $event->trigger ();

                $this->admin = $admin;

                return true;
            }
        }
    }

    protected function object_to_xml($object, $xml = null, $level = 1) {
        if ($xml === null) {
            $xml_key = get_class ( $object );
            $xml_key = isset ( $this->xml_map [$xml_key] ) ? $this->xml_map [$xml_key] : $xml_key;

            $xml = new SimpleXMLElement ( '<' . $xml_key . '/>' );
        }

        foreach ( $object as $key => $value ) {
            $dive_in = false;
            $xml_value = htmlspecialchars ( $value );
            $xml_key = ( string ) $key;

            if (is_object ( $value )) {
                $xml_key = strtolower ( get_class ( $value ) );
                $xml_value = null;

                $dive_in = true;
            }

            if (is_array ( $value )) {
                $xml_value = null;

                $dive_in = true;
            }

            $xml_key = isset ( $this->xml_map [$xml_key] ) ? $this->xml_map [$xml_key] : $xml_key;

            $child = $xml->addChild ( $xml_key, $xml_value );

            if ($dive_in) {
                $this->object_to_xml ( $value, $child, ++ $level );
            }
        }

        return ($xml->asXML ());
    }

    protected function get_course_condition($course_id) {
        if (isset ( $this->config->michlol_useid ) && $this->config->michlol_useid) {
            return (array (
                'id' => $course_id
            ));
        } else {
            return (array (
                'idnumber' => $course_id
            ));
        }
    }

    protected function user_unenroll($course, $user) {
        $auth = 'manual';

        $manualenrol = enrol_get_plugin ( $auth );
        $enrolinstance = $this->DB->get_record ( 'enrol',
                array (
                    'courseid' => $course,
                    'status' => ENROL_INSTANCE_ENABLED,
                    'enrol' => $auth
                ), '*', MUST_EXIST );
        $manualenrol->unenrol_user ( $enrolinstance, $user );

        $event = \local_ws_rashim\event\user_enrolment_deleted::create ( 
                array (
                    'userid' => $this->admin->id,
                    'courseid' => $course,
                    'context' => context_course::instance ( $course ),
                    'relateduserid' => $user,
                    'objectid' => $user,
                    'other' => array (
                        'userenrolment' => '',
                        'enrol' => $auth
                    )
                ) );

        $event->trigger ();
    }

    protected function user_enroll($course, $user, $role, $role_start = 0, $role_end = 0) {
        $auth = 'manual';

        $manualenrol = enrol_get_plugin ( $auth );
        $enrolinstance = $this->DB->get_record ( 'enrol',
                array (
                    'courseid' => $course,
                    'status' => ENROL_INSTANCE_ENABLED,
                    'enrol' => $auth
                ), '*', MUST_EXIST );
        $manualenrol->enrol_user ( $enrolinstance, $user, $role, $role_start, $role_end );

        $event = \local_ws_rashim\event\user_enrolment_created::create ( 
                array (
                    'userid' => $this->admin->id,
                    'courseid' => $course,
                    'relateduserid' => $user,
                    'objectid' => $user,
                    'context' => context_course::instance ( $course ),
                    'other' => array (
                        'enrol' => $auth
                    )
                ) );

        $event->trigger ();
    }

    protected function add_syllabus_url($course, $url) {
        $conditions = array (
            'course' => $course->id,
            'name' => 'סילבוס'
        );
        if (! $syl = $this->DB->get_record ( 'url', $conditions )) {
            if (! empty ( $url )) {

                $syl = new stdClass ();
                $syl->course = $course->id;
                $syl->name = 'סילבוס';
                $syl->intro = 'קישור לסילבוס במכלול';
                $syl->introformat = 0;
                $syl->externalurl = $url;
                $syl->display = 0;
                $syl->timemodified = time ();

                $syl->modulename = 'url';
                $syl->module = $this->DB->get_field ( 'modules', 'id', array (
                    'name' => 'url'
                ), MUST_EXIST );
                $syl->section = 0;
                $syl->visible = true;

                $module = add_moduleinfo ( $syl, $course );
                $syl->id = $module->instance;

                if (empty ( $syl->id )) {
                    return $this->error ( 5026 );
                }

                $event = \local_ws_rashim\event\course_module_created::create ( 
                        array (
                            'userid' => $this->admin->id,
                            'context' => context_course::instance ( $course->id ),
                            'objectid' => $module->id,
                            'other' => array (
                                'modulename' => 'url',
                                'instanceid' => $module->instance,
                                'name' => $module->name
                            )
                        ) );

                $event->trigger ();
            }
        } else {
            $conditions = array (
                'course' => $course->id,
                'instance' => $syl->id
            );
            $cm = $this->DB->get_record ( 'course_modules', $conditions );

            if (empty ( $url )) {
                course_delete_module ( $cm->id );

                $event = \local_ws_rashim\event\course_module_deleted::create ( 
                        array (
                            'userid' => $this->admin->id,
                            'courseid' => $cm->course,
                            'context' => context_course::instance ( $cm->course ),
                            'objectid' => $cm->id,
                            'other' => array (
                                'modulename' => 'url',
                                'instanceid' => $cm->instance
                            )
                        ) );

                $event->trigger ();
            } else {
                $cm->modname = 'url';

                $syl->modulename = 'url';
                $syl->coursemodule = $cm->id;
                $syl->externalurl = $url;
                $syl->introeditor ['text'] = 'קישור לסילבוס במכלול';
                $syl->introeditor ['format'] = 0;
                $syl->visible = true;

                update_moduleinfo ( $cm, $syl, $course );

                $event = \local_ws_rashim\event\course_module_updated::create ( 
                        array (
                            'userid' => $this->admin->id,
                            'context' => context_course::instance ( $cm->course ),
                            'objectid' => $cm->id,
                            'other' => array (
                                'modulename' => 'url',
                                'instanceid' => $cm->instance,
                                'name' => $cm->name
                            )
                        ) );

                $event->trigger ();
            }
        }
    }

    protected function meeting2course_section($meeting, $with_error = true) {
        if (! is_null ( $meeting->section_id )) {
            $section_conditions = array (
                'id' => $meeting->section_id
            );
        } else {
            $section_conditions = array (
                'course' => $meeting->course_id,
                'section' => $meeting->section_num
            );
        }

        $section = $this->DB->get_record ( 'course_sections', $section_conditions );

        if (! $section && $with_error) {
            return $this->error ( 5036 );
        }

        return ($section);
    }

    protected function course_update_section($course_id, $section, $section_name, $visible) {
        // pre 3.1 hack
        if (function_exists ( 'course_update_section' )) {
            $new = array (
                'name' => $section_name,
                'visible' => $visible
            );

            course_update_section ( $course_id, $section, $new );
        } else {
            $section->name = $section_name;
            $section->visible = $visible;

            $this->DB->update_record ( 'course_sections', $section );
            rebuild_course_cache ( $course_id, true );
        }
    }

    protected function handle_course_section($course_id, $section_num, $section_name = null, $visible = 1) {
        course_create_sections_if_missing ( $course_id, $section_num );

        $conditions = array (
            'course' => $course_id,
            'section' => $section_num
        );
        $section = $this->DB->get_record ( 'course_sections', $conditions );

        if (empty ( $section->id )) {
            return $this->error ( 5035 );
        }

        $this->course_update_section ( $course_id, $section, $section_name, $visible );

        $event = \local_ws_rashim\event\course_section_updated::create ( 
                array (
                    'userid' => $this->admin->id,
                    'courseid' => $course_id,
                    'context' => context_course::instance ( $course_id ),
                    'objectid' => $section->id,
                    'other' => array (
                        'sectionnum' => $section->section
                    )
                ) );

        $event->trigger ();

        return ($section->id);
    }

    protected function user_extra($user, $extra) {
        $save = false;
        $update = false;

        $arr1 = explode ( ';', $extra );

        $profile = profile_user_record ( null, false );

        foreach ( $arr1 as $key1 => $value1 ) {
            $arr2 = explode ( '=', $value1 );

            $field = $arr2 [0];
            $value = $arr2 [1];

            if (! empty ( $field )) {
                if (property_exists ( $profile, $field )) {
                    $user->{'profile_field_' . $field} = $value;

                    $save = true;
                } else {
                    if ($field == 'username') {
                        $value = strtolower ( $value );
                    }

                    $user->{$field} = $value;

                    $update = true;

                    $event = \local_ws_rashim\event\user_profile_field_missing::create ( 
                            array (
                                'userid' => $this->admin->id,
                                'objectid' => $user->id,
                                'relateduserid' => $user->id,
                                'context' => context_user::instance ( $user->id ),
                                'other' => array (
                                    'field' => $field
                                )
                            ) );

                    $event->trigger ();
                }
            }
        }

        if ($update) {
            user_update_user ( $user );
        }

        if ($save) {
            profile_save_data ( $user );
        }
    }

    protected function update_assignment($course, $bhn, $bhn_shm, $moodle_type, $start, $end) {
        if ($moodle_type == 'scorm') {
            $bhn->timeopen = $start == 0 ? 0 : $start;
            $bhn->timeclose = $end == 0 ? 0 : $end;

            $bhn->quizpassword = '';
        } else if ($moodle_type == 'quiz') {
            $bhn->timeopen = $start == 0 ? 0 : $start;
            $bhn->timeclose = $end == 0 ? 0 : $end;

            $bhn->quizpassword = '';
        } else if ($moodle_type == 'assign') {
            if ($bhn->name == $bhn->description) {
                $bhn->description = $bhn_shm;
            }

            $bhn->duedate = $start == 0 ? 0 : $start;
            $bhn->cutoffdate = $end == 0 ? 0 : $end;

            $plugins = $this->DB->get_records ( 'assign_plugin_config', array (
                'assignment' => $bhn->id
            ) );

            foreach ( $plugins as $key => $value ) {
                if (isset ( $value->value )) {
                    if ($value->plugin == 'file') {
                        if ($value->name == 'maxfilesubmissions')
                            $value->name = 'maxfiles';
                        if ($value->name == 'maxsubmissionsizebytes')
                            $value->name = 'maxsizebytes';
                    }

                    $bhn->{$value->subtype . '_' . $value->plugin . '_' . $value->name } = $value->value;
                }
            }
        }

        $conditions = array (
            'course' => $course->id,
            'instance' => $bhn->id
        );
        $cm = $this->DB->get_record ( 'course_modules', $conditions );

        $cm->modname = $moodle_type;

        $bhn->name = $bhn_shm;

        $bhn->modulename = $moodle_type;
        $bhn->coursemodule = $cm->id;
        $bhn->introeditor ['text'] = $bhn->intro;
        $bhn->introeditor ['format'] = 0;
        $bhn->visible = $cm->visible;

        $rv = update_moduleinfo ( $cm, $bhn, $course );
        $moduleinfo = $rv [1];

        $event = \local_ws_rashim\event\course_module_updated::create ( 
                array (
                    'userid' => $this->admin->id,
                    'courseid' => $course->id,
                    'context' => context_course::instance ( $course->id ),
                    'objectid' => $moduleinfo->id,
                    'other' => array (
                        'modulename' => $bhn->modulename,
                        'instanceid' => $moduleinfo->instance,
                        'name' => $bhn->name
                    )
                ) );

        $event->trigger ();

        return $moduleinfo;
    }

    protected function add_assignment($course_id, $section_num, $bhn_shm, $michlol_krs, $michlol_sms, $michlol_sid, $moodle_type,
            $start, $end) {
        // scorm - s
        // quiz - b
        // assignment/online - m
        // assignment/offline - t
        // assignment/upload - k
        $course = $this->DB->get_record ( 'course', array (
            'id' => $course_id
        ) );

        $conditions = array (
            'course_id' => $course_id,
            'michlol_krs_bhn_krs' => $michlol_krs,
            'michlol_krs_bhn_sms' => $michlol_sms,
            'michlol_krs_bhn_sid' => $michlol_sid
        );
        if (! $matala = $this->DB->get_record ( 'matalot', $conditions )) {
            $bhn = new stdClass ();

            if ($moodle_type == 's') {
                $bhn->modulename = 'scorm';

                $bhn->timeopen = $start == 0 ? 0 : $start;
                $bhn->timeclose = $end == 0 ? 0 : $end;
            } else if ($moodle_type == 'b') {
                $bhn->modulename = 'quiz';

                $bhn->timeopen = $start == 0 ? 0 : $start;
                $bhn->timeclose = $end == 0 ? 0 : $end;

                $bhn->quizpassword = '';
                $bhn->preferredbehaviour = 'deferredfeedback';
                $bhn->shuffleanswers = true;
            } else if (($moodle_type == 'm') || ($moodle_type == 't') || ($moodle_type == 'k')) {
                $bhn->modulename = 'assign';

                $bhn->duedate = $start == 0 ? 0 : $start;
                $bhn->cutoffdate = $end == 0 ? 0 : $end;

                if ($moodle_type == 'm') {
                    $bhn->assignsubmission_onlinetext_enabled = true;
                }

                if ($moodle_type == 'k') {
                    $bhn->assignsubmission_file_maxfiles = 3;
                    $bhn->assignsubmission_file_enabled = true;
                }

                $bhn->submissiondrafts = 0;
                $bhn->requiresubmissionstatement = 0;
                $bhn->sendnotifications = 0;
                $bhn->sendlatenotifications = 0;
                $bhn->allowsubmissionsfromdate = 0;
                $bhn->teamsubmission = 0;
                $bhn->requireallteammemberssubmit = 0;
                $bhn->blindmarking = 0;
                $bhn->markingworkflow = 0;

                // Post 3.3 hack for assign.gradingduedate, that not allows NULL
                $dbman = $this->DB->get_manager ();

                $table = new xmldb_table ( 'assign' );
                $field = new xmldb_field ( 'gradingduedate' );

                if ($dbman->field_exists ( $table, $field )) {
                    $bhn->gradingduedate = 0;
                }

                // feedback plugin defaults
                $assign = new assign ( context_course::instance ( $course_id ), null, null );
                $plugins = $assign->get_feedback_plugins ();

                foreach ( $plugins as $plugin ) {
                    $type = $plugin->get_subtype () . '_' . $plugin->get_type ();
                    $name = $type . '_enabled';

                    $bhn->$name = get_config ( $type, 'default' );
                }
            }

            $bhn->course = $course_id;
            $bhn->name = $bhn_shm;
            $bhn->intro = 'מטלה נוצרה ממכלול';
            $bhn->introformat = 0;

            $bhn->module = $this->DB->get_field ( 'modules', 'id', array (
                'name' => $bhn->modulename
            ), MUST_EXIST );
            ;
            $bhn->visible = 0;
            $bhn->section = $section_num;
            $bhn->grade = 100;

            $moduleinfo = add_moduleinfo ( $bhn, $course );

            if (empty ( $moduleinfo->instance )) {
                return $this->error ( 5030 );
            }

            $matala = new stdClass ();
            $matala->course_id = $course_id;
            $matala->michlol_krs_bhn_krs = $michlol_krs;
            $matala->michlol_krs_bhn_sms = $michlol_sms;
            $matala->michlol_krs_bhn_sid = $michlol_sid;
            $matala->moodle_type = $bhn->modulename;
            $matala->moodle_id = $moduleinfo->instance;

            $this->DB->insert_record ( 'matalot', $matala );

            $event = \local_ws_rashim\event\course_module_created::create ( 
                    array (
                        'userid' => $this->admin->id,
                        'courseid' => $matala->course_id,
                        'context' => context_course::instance ( $matala->course_id ),
                        'objectid' => $moduleinfo->id,
                        'other' => array (
                            'modulename' => $bhn->modulename,
                            'instanceid' => $moduleinfo->instance,
                            'name' => $bhn->name
                        )
                    ) );

            $event->trigger ();

            return $moduleinfo;
        } else {
            $conditions = array (
                'id' => $matala->moodle_id
            );
            if ($bhn = $this->DB->get_record ( $matala->moodle_type, $conditions )) {
                return $this->update_assignment ( $course, $bhn, $bhn_shm, $matala->moodle_type, $start, $end );
            }
        }
    }

    protected function add_assignment_link($course_id, $section_num, $bhn_shm, $michlol_krs, $michlol_sms, $michlol_sid,
            $moodle_type, $start, $end) {
        // quiz - b
        // assignment/online - m
        // assignment/offline - t
        // assignment/upload - k
        $course = $this->DB->get_record ( 'course', array (
            'id' => $course_id
        ) );

        if ($moodle_type == 's') {
            $type = 'scorm';
        } else if ($moodle_type == 'b') {
            $type = 'quiz';
        } else if (($moodle_type == 'm') || ($moodle_type == 't') || ($moodle_type == 'k')) {
            $type = 'assign';
        }

        $conditions = array (
            'course_id' => $course_id,
            'michlol_krs_bhn_krs' => $michlol_krs,
            'michlol_krs_bhn_sms' => $michlol_sms,
            'michlol_krs_bhn_sid' => $michlol_sid
        );
        if (! $matala = $this->DB->get_record ( 'matalot', $conditions )) {
            $conditions = array (
                'course' => $course_id,
                'section' => $section_num
            );
            if (! $section = $this->DB->get_record ( 'course_sections', $conditions )) {
                return $this->error ( 5036 );
            }

            $conditions = array (
                'course' => $course_id,
                'section' => $section->id,
                'module' => $this->DB->get_field ( 'modules', 'id', array (
                    'name' => $type
                ), MUST_EXIST )
            );
            if (! $module = $this->DB->get_record ( 'course_modules', $conditions )) {
                return $this->error ( 5033 );
            }

            $conditions = array (
                'course' => $course_id,
                'id' => $module->instance
            );
            $bhn = $this->DB->get_record ( $type, $conditions );

            if (empty ( $bhn->id )) {
                return $this->error ( 5030 );
            }

            $this->update_assignment ( $course, $bhn, $bhn_shm, $type, $start, $end );

            $matala->course_id = $course_id;
            $matala->michlol_krs_bhn_krs = $michlol_krs;
            $matala->michlol_krs_bhn_sms = $michlol_sms;
            $matala->michlol_krs_bhn_sid = $michlol_sid;
            $matala->moodle_type = $type;
            $matala->moodle_id = $bhn->id;

            $this->DB->insert_record ( 'matalot', $matala );
        } else {
            $conditions = array (
                'id' => $matala->moodle_id
            );
            $bhn = $this->DB->get_record ( $matala->moodle_type, $conditions );

            if (empty ( $bhn->id )) {
                return $this->error ( 5030 );
            }

            $this->update_assignment ( $course, $bhn, $bhn_shm, $matala->moodle_type, $start, $end );
        }
    }

    protected function xml2meetings($course_id, $xml) {
        $section_num = 1;

        foreach ( $xml->MEETINGS->children () as $meeting ) {
            if (! isset ( $meeting->WEEK )) {
                $meeting->WEEK = - 1;
            }

            if (! isset ( $meeting->DAY )) {
                $meeting->DAY = - 1;
            }

            if (! isset ( $meeting->MEETING_DATE )) {
                $meeting->MEETING_DATE = - 1;
            }

            $conditions = array (
                'snl' => ( string ) $xml->DATA->SNL,
                'shl' => ( integer ) $xml->DATA->SHL,
                'hit' => ( integer ) $xml->DATA->MIS,
                'krs' => ( integer ) $meeting->MIS,
                'mfgs' => ( integer ) $meeting->SID
            );
            if (! $meeting_old = $this->DB->get_record ( 'meetings', $conditions )) {
                $section = $this->handle_course_section ( $course_id, $section_num, ( string ) $meeting->SHM );

                // write record to the help table anable sorting
                $meeting_new->snl = ( string ) $xml->DATA->SNL;
                $meeting_new->shl = ( integer ) $xml->DATA->SHL;
                $meeting_new->hit = ( integer ) $xml->DATA->MIS;
                $meeting_new->krs = ( integer ) $meeting->MIS;
                $meeting_new->mfgs = ( integer ) $meeting->SID;

                $meeting_new->course_id = $course_id;
                $meeting_new->section_num = $section_num;

                $meeting_new->section_id = $section;

                $meeting_new->subject = ( string ) $meeting->SUB;
                $meeting_new->week = ( integer ) $meeting->WEEK;
                $meeting_new->day = ( integer ) $meeting->DAY;
                $meeting_new->meeting_date = ( integer ) $meeting->MEETING_DATE;
                $meeting_new->hour_begin = ( integer ) $meeting->BEGIN;
                $meeting_new->hour_end = ( integer ) $meeting->END;

                $this->DB->insert_record ( 'meetings', $meeting_new );

                $section_num ++;

                $event = \local_ws_rashim\event\meeting_created::create ( 
                        array (
                            'userid' => $this->admin->id,
                            'objectid' => $section,
                            'courseid' => $meeting_new->course_id,
                            'context' => context_course::instance ( $meeting_new->course_id ),
                            'other' => array (
                                'sectionnum' => $meeting_new->section_num
                            )
                        ) );

                $event->trigger ();
            } else {
                $meeting_old->subject = ( string ) $meeting->SUB;
                $meeting_old->week = ( integer ) $meeting->WEEK;
                $meeting_old->day = ( integer ) $meeting->DAY;
                $meeting_old->meeting_date = ( integer ) $meeting->MEETING_DATE;
                $meeting_old->hour_begin = ( integer ) $meeting->BEGIN;
                $meeting_old->hour_end = ( integer ) $meeting->END;

                $section = $this->meeting2course_section ( $meeting_old );

                if ($section->section != $meeting_old->section_num) {
                    $meeting_old->section_num = $section->section;
                }

                $this->DB->update_record ( 'meetings', $meeting_old );

                $this->handle_course_section ( $meeting_old->course_id, $meeting_old->section_num, ( string ) $meeting->SHM );

                $event = \local_ws_rashim\event\meeting_updated::create ( 
                        array (
                            'userid' => $this->admin->id,
                            'objectid' => $section->id,
                            'courseid' => $meeting_old->course_id,
                            'context' => context_course::instance ( $meeting_old->course_id ),
                            'other' => array (
                                'sectionnum' => $meeting_old->section_num
                            )
                        ) );

                $event->trigger ();

                $section_num = $meeting_old->section_num + 1;
            }
        }

        course_get_format ( $course_id )->update_course_format_options ( array (
            'numsections' => $section_num - 1
        ) );
    }

    protected function xml2assignments($course_id, $xml) {
        foreach ( $xml->ASSIGNMENTS->children () as $assignment ) {
            $conditions = array (
                'snl' => ( string ) $xml->DATA->SNL,
                'shl' => ( integer ) $xml->DATA->SHL,
                'hit' => ( integer ) $xml->DATA->MIS,
                'krs' => ( integer ) $assignment->MIS,
                'mfgs' => ( integer ) $assignment->SID
            );
            if (! $meeting = $this->DB->get_record ( 'meetings', $conditions )) {
                return $this->error ( 5041 );
            }

            if (( integer ) $assignment->ORG_KRS != - 1) {
                $this->xml2assignments_modula ( $course_id, $assignment, $meeting );
            } else {
                $section = $this->meeting2course_section ( $meeting );

                if ($meeting->meeting_date == - 1) {
                    $this->add_assignment ( $course_id, $section->section, ( string ) $assignment->BHN_SHM,
                            ( integer ) $assignment->BHN_KRS, ( string ) $assignment->BHN_SMS, ( integer ) $assignment->BHN_SID,
                            ( string ) $assignment->BHN_MOODLETYPE, 0, 0 );
                } else {
                    if (isset ( $assignment->DATE_END )) {
                        $this->add_assignment ( $course_id, $section->section, ( string ) $assignment->BHN_SHM,
                                ( integer ) $assignment->BHN_KRS, ( string ) $assignment->BHN_SMS, ( integer ) $assignment->BHN_SID,
                                ( string ) $assignment->BHN_MOODLETYPE, $meeting->meeting_date + $meeting->hour_begin,
                                $assignment->DATE_END + $meeting->hour_end );
                    } else {
                        $this->add_assignment ( $course_id, $section->section, ( string ) $assignment->BHN_SHM,
                                ( integer ) $assignment->BHN_KRS, ( string ) $assignment->BHN_SMS, ( integer ) $assignment->BHN_SID,
                                ( string ) $assignment->BHN_MOODLETYPE, $meeting->meeting_date + $meeting->hour_begin,
                                $meeting->meeting_date + $meeting->hour_end );
                    }
                }
            }
        }
    }

    protected function xml2assignments_link($course_id, $xml) {
        foreach ( $xml->ASSIGNMENTS->children () as $assignment ) {
            $conditions = array (
                'snl' => ( string ) $xml->DATA->SNL,
                'shl' => ( integer ) $xml->DATA->SHL,
                'hit' => ( integer ) $xml->DATA->MIS,
                'krs' => ( integer ) $assignment->MIS,
                'mfgs' => ( integer ) $assignment->SID
            );
            if (! $meeting = $this->DB->get_record ( 'meetings', $conditions )) {
                return $this->error ( 5041 );
            }

            if (( integer ) $assignment->ORG_KRS != - 1) {
                $this->xml2assignments_modula ( $course_id, $assignment, $meeting );
            } else {
                $section = $this->meeting2course_section ( $meeting );

                if ($meeting->meeting_date == - 1) {
                    $this->add_assignment_link ( $course_id, $section->section, ( string ) $assignment->BHN_SHM,
                            ( integer ) $assignment->BHN_KRS, ( string ) $assignment->BHN_SMS, ( integer ) $assignment->BHN_SID,
                            ( string ) $assignment->BHN_MOODLETYPE, 0, 0 );
                } else {
                    $this->add_assignment_link ( $course_id, $section->section, ( string ) $assignment->BHN_SHM,
                            ( integer ) $assignment->BHN_KRS, ( string ) $assignment->BHN_SMS, ( integer ) $assignment->BHN_SID,
                            ( string ) $assignment->BHN_MOODLETYPE, $meeting->meeting_date + $meeting->hour_begin,
                            $meeting->meeting_date + $meeting->hour_end );
                }
            }
        }
    }

    protected function xml2assignments_modula($course_id, $assignment, $meeting) {
        $conditions = array (
            'krs' => ( integer ) $assignment->MIS,
            'mfgs' => ( integer ) $assignment->SID,
            'course_id' => $course_id
        );
        if (! $mfgs = $this->DB->get_record ( 'meetings', $conditions )) {
            return $this->error ( 5041 );
        } else {
            $dest_sec = $this->meeting2course_section ( $mfgs );

            $conditions = array (
                'michlol_krs_bhn_krs' => ( integer ) $assignment->ORG_KRS,
                'michlol_krs_bhn_sms' => ( string ) $assignment->ORG_SMS,
                'michlol_krs_bhn_sid' => ( integer ) $assignment->ORG_SID
            );
            if (! $bhn = $this->DB->get_record ( 'matalot', $conditions )) {
                return $this->error ( 5032 );
            } else {
                $conditions = array (
                    'course' => $bhn->course_id,
                    'instance' => $bhn->moodle_id
                );
                if (! $module = $this->DB->get_record ( 'course_modules', $conditions )) {
                    return $this->error ( 5033 );
                } else {
                    $this->copy_section ( $module->section, $dest_sec->id );

                    $conditions = array (
                        'course' => $course_id,
                        'section' => $dest_sec->id
                    );
                    if (! $module = $this->DB->get_record ( 'course_modules', $conditions )) {
                        return $this->error ( 5033 );
                    } else {
                        if ($meeting->meeting_date == - 1) {
                            $this->add_assignment_link ( $course_id, $dest_sec->section, ( string ) $assignment->BHN_SHM,
                                    ( integer ) $assignment->BHN_KRS, ( string ) $assignment->BHN_SMS,
                                    ( integer ) $assignment->BHN_SID, ( string ) $assignment->BHN_MOODLETYPE, 0, 0 );
                        } else {
                            $this->add_assignment_link ( $course_id, $dest_sec->section, ( string ) $assignment->BHN_SHM,
                                    ( integer ) $assignment->BHN_KRS, ( string ) $assignment->BHN_SMS,
                                    ( integer ) $assignment->BHN_SID, ( string ) $assignment->BHN_MOODLETYPE,
                                    $meeting->meeting_date + $meeting->hour_begin, $meeting->meeting_date + $meeting->hour_end );
                        }
                    }
                }
            }
        }
    }

    protected function add_user($user_id, $user_name, $user_psw, $user_firstname, $user_lastname, $user_email, $user_phone1,
            $user_phone2, $user_address, $user_lang, $user_extra) {
        $conditions = array (
            'idnumber' => $user_id
        );
        if (! $user = $this->DB->get_record ( 'user', $conditions )) {
            if (! isset ( $user_name ) || ! isset ( $user_psw ) || ! isset ( $user_id ) || ! isset ( $user_firstname ) ||
                    ! isset ( $user_lastname )) {
                return $this->error ( 5007 );
            }

            $user->confirmed = true;
            $user->mnethostid = $this->CFG->mnet_localhost_id;

            if (isset ( $user_lang )) {
                $user->lang = $user_lang;
            }

            $user->password = $user_psw;

            $user->idnumber = $user_id;
            $user->username = $user_name;
            $user->firstname = $user_firstname;
            $user->lastname = $user_lastname;
            $user->email = isset ( $user_email ) ? $user_email : '';
            $user->phone1 = isset ( $user_phone1 ) ? $user_phone1 : '';
            $user->phone2 = isset ( $user_phone2 ) ? $user_phone2 : '';
            $user->address = $user_address;

            if (isset ( $this->config->michlolauth )) {
                $user->auth = $this->config->michlolauth;
            }

            if (isset ( $this->config->def_city )) {
                $user->city = $this->config->def_city;
            }

            if (isset ( $this->config->def_country )) {
                $user->country = $this->config->def_country;
            }

            $user->id = user_create_user ( $user );

            if (empty ( $user->id )) {
                return $this->error ( 5010 );
            }

            $event = \local_ws_rashim\event\user_created::create ( 
                    array (
                        'userid' => $this->admin->id,
                        'objectid' => $user->id,
                        'context' => context_user::instance ( $user->id ),
                        'relateduserid' => $user->id
                    ) );

            $event->trigger ();
        } else {
            if (! isset ( $user_id )) {
                return $this->error ( 5007 );
            }

            $user->password = $user_psw;

            $user->username = $user_name;
            $user->firstname = $user_firstname;
            $user->lastname = $user_lastname;
            $user->email = isset ( $user_email ) ? $user_email : '';
            $user->phone1 = isset ( $user_phone1 ) ? $user_phone1 : '';
            $user->phone2 = isset ( $user_phone2 ) ? $user_phone2 : '';
            $user->address = $user_address;

            user_update_user ( $user );

            $event = \local_ws_rashim\event\user_updated::create ( 
                    array (
                        'userid' => $this->admin->id,
                        'objectid' => $user->id,
                        'context' => context_user::instance ( $user->id ),
                        'relateduserid' => $user->id
                    ) );

            $event->trigger ();
        }

        $this->user_extra ( $user, $user_extra );

        return $user;
    }

    protected function category_add($category_parent, $category_code, $category_name) {
        $category->visible = true;
        $category->timemodified = time ();
        $category->name = $category_name;
        $category->parent = $category_parent;
        $category->idnumber = $category_code;

        $coursecat = coursecat::create ( $category );

        $category->id = $coursecat->id;

        if (empty ( $category->id )) {
            return $this->error ( 5020 );
        } else {
            $event = \local_ws_rashim\event\category_created::create ( 
                    array (
                        'userid' => $this->admin->id,
                        'objectid' => $category->id
                    ) );

            $event->trigger ();

            return $category->id;
        }
    }

    protected function category_tree($category_snlcode, $category_snlname, $category_shlcode, $category_shlname, $category_mslcode,
            $category_mslname) {
        $conditions = array (
            'idnumber' => $category_snlcode
        );
        if (! ($category_snl = $this->DB->get_record ( 'course_categories', $conditions ))) {
            $conditions = array (
                'name' => $category_snlname
            );
            $category_snl = $this->DB->get_record ( 'course_categories', $conditions );
        }

        if ($category_snl != null) {
            $conditions = array (
                'idnumber' => $category_shlcode
            );
            if (! ($category_shl = $this->DB->get_record ( 'course_categories', $conditions ))) {
                $conditions = array (
                    'name' => $category_shlname,
                    'parent' => $category_snl->id
                );
                $category_shl = $this->DB->get_record ( 'course_categories', $conditions );
            }

            if ($category_shl != null) {
                $conditions = array (
                    'idnumber' => $category_mslcode
                );
                if (! ($category_msl = $this->DB->get_record ( 'course_categories', $conditions ))) {
                    $conditions = array (
                        'name' => $category_mslname,
                        'parent' => $category_shl->id
                    );
                    $category_msl = $this->DB->get_record ( 'course_categories', $conditions );
                }
            }
        }

        if ($category_msl == null) {
            if ($category_shl == null) {
                if ($category_snl == null) {
                    $category_snl->id = $this->category_add ( 0, $category_snlcode, $category_snlname );
                }

                $category_shl->id = $this->category_add ( $category_snl->id, $category_shlcode, $category_shlname );
            }

            if ($category_mslcode != - 1) {
                $category_msl->id = $this->category_add ( $category_shl->id, $category_mslcode, $category_mslname );
            }
        }

        if ($category_mslcode != - 1) {
            return $category_msl->id;
        } else {
            return $category_shl->id;
        }
    }

    protected function copy_course_section($src, $dest) {
        $conditions = array (
            'course_id' => $src
        );
        $tikyesod = $this->DB->get_records ( 'meetings', $conditions, 'krs, mfgs' );

        $conditions = array (
            'course_id' => $dest
        );
        $machzor = $this->DB->get_records ( 'meetings', $conditions, 'krs, mfgs' );

        foreach ( $tikyesod as $t_mfgs ) {
            foreach ( $machzor as $m_mfgs ) {
                if (($m_mfgs->krs == $t_mfgs->krs) && ($m_mfgs->mfgs == $t_mfgs->mfgs)) {
                    $src_sec = $this->meeting2course_section ( $t_mfgs );
                    $dest_sec = $this->meeting2course_section ( $m_mfgs );

                    $this->copy_section ( $src_sec->id, $dest_sec->id );
                }
            }
        }
    }

    protected function copy_course_section_tik($xml) {
        foreach ( $xml->MEETINGS->children () as $meeting ) {
            $from_modula = $xml->xpath ( "//ASSIGNMENT[MIS=$meeting->MIS and SID=$meeting->SID and not(ORG_KRS=-1)]" );

            if (empty ( $from_modula )) {
                $conditions = array (
                    'snl' => '9999',
                    'shl' => ( integer ) $xml->DATA->SHL,
                    'hit' => ( integer ) $xml->DATA->PREV_VERSION,
                    'krs' => ( integer ) $meeting->PREV_VERSION,
                    'mfgs' => ( integer ) $meeting->SID
                );
                if ($src = $this->DB->get_record ( 'meetings', $conditions )) {
                    $conditions = array (
                        'snl' => '9999',
                        'shl' => ( integer ) $xml->DATA->SHL,
                        'hit' => ( integer ) $xml->DATA->MIS,
                        'krs' => ( integer ) $meeting->MIS,
                        'mfgs' => ( integer ) $meeting->SID
                    );
                    if ($dest = $this->DB->get_record ( 'meetings', $conditions )) {
                        $src_sec = $this->meeting2course_section ( $src );
                        $dest_sec = $this->meeting2course_section ( $dest );

                        $this->copy_section ( $src_sec->id, $dest_sec->id );
                    }
                }
            }
        }
    }

    protected function duplicate_module($dest_course_id, $module) {
        return (local_duplicate_module ( $dest_course_id, $module ));
    }

    protected function copy_module2section($src_module, $dest_sec) {
        local_copy_model2section ( $src_module, $dest_sec );
    }

    protected function copy_section($src, $dest) {
        $conditions = array (
            'id' => $dest
        );
        if ($section_dest = $this->DB->get_record ( 'course_sections', $conditions )) {
            $conditions = array (
                'section' => $src
            );
            if ($modules = $this->DB->get_records ( 'course_modules', $conditions )) {
                $pluginman = core_plugin_manager::instance ();

                foreach ( $modules as $module ) {
                    if (! $section = $this->DB->get_record ( 'course_sections',
                            array (
                                'id' => $module->section,
                                'course' => $module->course
                            ) )) {
                        $this->trace_message ( "Skip disconnected module... $module->id",
                                context_course::instance ( $section_dest->course ) );
                    } else {
                        $module->modname = $this->DB->get_field ( 'modules', 'name', array (
                            'id' => $module->module
                        ), MUST_EXIST );

                        $plugin_info = $pluginman->get_plugin_info ( $module->modname );
                        if (! $plugin_info || ! $plugin_info->is_enabled ()) {
                            $this->trace_message ( "Skip disabled module... $module->id",
                                    context_course::instance ( $section_dest->course ) );
                        } else {

                            $module_new = $this->duplicate_module ( $section_dest->course, $module );

                            delete_mod_from_section ( $module_new->id, $src );

                            course_add_cm_to_section ( $section_dest->course, $module_new->id, $section_dest->section );

                            $event = \local_ws_rashim\event\course_section_copied::create ( 
                                    array (
                                        'userid' => $this->admin->id,
                                        'courseid' => $section_dest->course,
                                        'objectid' => $section_dest->id,
                                        'context' => context_course::instance ( $section_dest->course ),
                                        'other' => array (
                                            'sectionnum' => $section_dest->section,
                                            'old_moduleid' => $module->id,
                                            'old_sectionid' => $src,
                                            'new_moduleid' => $module_new->id,
                                            'new_sectionid' => $dest
                                        )
                                    ) );

                            $event->trigger ();
                        }
                    }
                }
            }
        }
    }

    protected function trace_message($message, $context) {
        $event = \local_ws_rashim\event\msg_logged::create ( 
                array (
                    'context' => isset ( $context ) ? $context : context_system::instance (),
                    'other' => array (
                        'message' => $message
                    )
                ) );

        $event->trigger ();
    }

    protected function create_readandsign_form($id) {
        $html = html_writer::start_tag ( 'form',
                array (
                    'method' => 'POST',
                    'action' => new moodle_url ( '/mod/readandsign/confirm.php' ),
                    'class' => 'readandsign'
                ) );

        $html .= html_writer::label ( get_string ( 'readandsign::done', 'local_ws_rashim' ), null, true, array (
            'id' => 'done'
        ) );

        $html .= html_writer::checkbox ( 'sign', 'on', false, get_string ( 'readandsign::text', 'local_ws_rashim' ),
                array (
                    'id' => 'sign'
                ) );

        $html .= html_writer::empty_tag ( 'br' );
        $html .= html_writer::empty_tag ( 'br' );

        $html .= html_writer::empty_tag ( 'input',
                array (
                    'type' => 'submit',
                    'value' => get_string ( 'readandsign::button', 'local_ws_rashim' )
                ) );

        $html .= html_writer::empty_tag ( 'input', array (
            'type' => 'hidden',
            'name' => 'sesskey',
            'value' => sesskey ()
        ) );
        $html .= html_writer::empty_tag ( 'input', array (
            'type' => 'hidden',
            'name' => 'id',
            'value' => $id
        ) );

        $html .= html_writer::end_tag ( 'form' );

        return ($html);
    }

    protected function quiz_prepare(quiz $quizobj, $attemptnumber, $lastattempt, $offlineattempt = false, $user) {
        global $DB;

        // Delete any previous preview attempts belonging to this user.
        quiz_delete_previews ( $quizobj->get_quiz (), $user->id );

        $quba = question_engine::make_questions_usage_by_activity ( 'mod_quiz', $quizobj->get_context () );
        $quba->set_preferred_behaviour ( $quizobj->get_quiz ()->preferredbehaviour );

        // Create the new attempt and initialize the question sessions
        $timenow = time (); // Update time now, in case the server is running really slowly.
        $attempt = quiz_create_attempt ( $quizobj, $attemptnumber, $lastattempt, $timenow, false, $user->id ); // never preview

        if (! ($quizobj->get_quiz ()->attemptonlast && $lastattempt)) {
            $attempt = quiz_start_new_attempt ( $quizobj, $quba, $attempt, $attemptnumber, $timenow );
        } else {
            $attempt = quiz_start_attempt_built_on_last ( $quba, $attempt, $lastattempt );
        }

        $transaction = $DB->start_delegated_transaction ();

        // Init the timemodifiedoffline for offline attempts.
        if ($offlineattempt) {
            $attempt->timemodifiedoffline = $attempt->timemodified;
        }
        $attempt = quiz_attempt_save_started ( $quizobj, $quba, $attempt );

        $transaction->allow_commit ();

        return $attempt;
    }

    protected function find_section_from_slot($sections, $slot) {
        foreach ( $sections as $section ) {
            if (($section->firstslot <= $slot) && ($section->lastslot >= $slot)) {
                return $section;
            }
        }

        return null;
    }

    protected function img_to_base64($question, $html, $filearea = 'questiontext', $itemid = null) {
        $questiontext = $html;
        $doc = new DOMDocument ();
        $doc->loadHTML ( $html );
        $images = $doc->getElementsByTagName ( 'img' );

        $itemid = $itemid == null ? $question->id : $itemid;

        $fs = get_file_storage ();

        foreach ( $images as $img ) {
            $src = $img->getAttribute ( 'src' );

            if (strpos ( $src, '@@PLUGINFILE@@' ) !== false) {
                $src_new = str_replace ( '@@PLUGINFILE@@/', '', explode ( '?', $src ) [0] );

                $qcategory = $this->DB->get_record ( 'question_categories', array (
                    'id' => $question->category
                ) );

                $file = $fs->get_file ( $qcategory->contextid, 'question', $filearea, $itemid, '/', $src_new );

                $content = $file->get_content ();
            } else {
                $src_new = question_rewrite_question_preview_urls ( $src, $question->id, $question->contextid, 'question',
                        'questiontext', $itemid, $question->contextid, 'core_question' );

                $content = download_file_content ( $src_new );
            }

            $questiontext = str_replace ( $src, 'data:image;base64,' . base64_encode ( $content ), $questiontext );
        }

        return ($questiontext);
    }

    public function session_login($admin_name, $admin_psw) {
        return md5 ( ( string ) time () ^ ( string ) random_string ( 10 ) );
    }

    public function session_logout($session_key) {
        return true;
    }

    public function ktree_content($admin_name, $admin_psw, $session_key, $codes) {
        if ($this->valid_login ( $session_key, $admin_name, $admin_psw )) {
            $excludes = $this->config->ktree_exclude;

            if (empty ( $codes )) {
                $codes = $this->config->ktree_root;
            }

            return (get_ktree ( $codes, $excludes ));
        }
    }

    public function readandsign_add($admin_name, $admin_psw, $session_key, $course_id, $course_psw, $course_name, $course_shortname,
            $category_code, $summary, $startdate, $enddate, $keepopen, $modules) {
        if ($this->valid_login ( $session_key, $admin_name, $admin_psw )) {
            $conditions = $this->get_course_condition ( $course_id );
            if (! $course = $this->DB->get_record ( 'course', $conditions )) {
                if (! isset ( $course_name ) || ! isset ( $course_id )) {
                    return $this->error ( 5007 );
                }

                $course->idnumber = $course_id;
                $course->fullname = $course_name;
                $course->shortname = $course_shortname . '@' . time ();
                $course->category = $category_code;
                $course->startdate = isset ( $startdate ) ? $startdate : time ();

                $course->password = md5 ( $course_psw );

                $courseconfig = get_config ( 'moodlecourse' );
                foreach ( $courseconfig as $key => $value ) {
                    $course->$key = $value;
                }

                //$course->enablecompletion = 1;
                $course->format = 'topics';
                $course->maxsections = 1;
                $course->numsections = 1;
                $course->newsitems = 0;

                if (isset ( $this->config->michlol_course_visible )) {
                    $course->visible = ( int ) $this->config->michlol_course_visible;
                }

                $course = create_course ( $course );
                
                $event = \local_ws_rashim\event\course_created::create (
                        array (
                            'userid' => $this->admin->id,
                            'courseid' => $course->id,
                            'context' => context_course::instance ( $course->id ),
                            'objectid' => $course->id,
                            'other' => array (
                                'fullname' => $course->fullname
                            )
                        ) );
                
                $event->trigger ();

                $section_id = $this->handle_course_section ( $course->id, 1, $course->fullname );

                $conditions = array (
                    'id' => $section_id
                );
                $section = $this->DB->get_record ( 'course_sections', $conditions );

                $xml = new SimpleXMLElement ( $modules );

                foreach ( $xml->Item as $item ) {
                    $this->copy_module2section ( ( integer ) $item->attributes ()->ID, $section->id );
                }

                $section->summary = $summary;

                $this->course_update_section ( $course->id, $section, $section->name, 1 );

                require_once (__DIR__ . '../../../mod/readandsign/lib.php');

                $readandsign = new stdClass ();

                $readandsign->modulename = 'readandsign';

                $readandsign->course = $course->id;
                $readandsign->name = $this->create_readandsign_form ( $course->id );
                $readandsign->section = $section->section;
                $readandsign->visible = 1;

                $readandsign->module = $this->DB->get_field ( 'modules', 'id', array (
                    'name' => 'readandsign'
                ), MUST_EXIST );

                add_moduleinfo ( $readandsign, $course );

                $mod_list = $this->DB->get_records ( 'course_modules', array (
                    'course' => $course->id
                ), 'id', 'id' );

                $mod_list = array_map ( create_function ( '$item', 'return $item->id;' ), $mod_list );

                $section->sequence = implode ( ",", $mod_list );

                $this->DB->update_record ( 'course_sections', $section );

                $event = \local_ws_rashim\event\course_created::create ( 
                        array (
                            'userid' => $this->admin->id,
                            'courseid' => $course->id,
                            'context' => context_course::instance ( $course->id ),
                            'objectid' => $course->id,
                            'other' => array (
                                'fullname' => $course->fullname
                            )
                        ) );

                $event->trigger ();
            } else {
                return $this->error ( 5040 );
            }

            return true;
        }
    }

    public function course_add($admin_name, $admin_psw, $session_key, $course_id, $course_psw, $course_name, $course_shortname,
            $course_sylurl, $category_code, $category_snlcode, $category_snlname, $category_shlcode, $category_shlname,
            $category_mslcode, $category_mslname) {
        if ($this->valid_login ( $session_key, $admin_name, $admin_psw )) {
            if (! isset ( $category_code )) {
                $category_code = $this->category_tree ( $category_snlcode, $category_snlname, $category_shlcode, $category_shlname,
                        $category_mslcode, $category_mslname );
            }

            $conditions = $this->get_course_condition ( $course_id );
            if (! $course = $this->DB->get_record ( 'course', $conditions )) {
                if (isset ( $this->config->michlol_useid ) && $this->config->michlol_useid) {
                    return $this->error ( 5014 );
                }

                if (! isset ( $course_name ) || ! isset ( $course_id )) {
                    return $this->error ( 5007 );
                }

                $course->idnumber = $course_id;
                $course->fullname = $course_name;
                $course->shortname = $course_shortname;
                $course->category = $category_code;
                $course->startdate = time ();

                $course->password = md5 ( $course_psw );

                $courseconfig = get_config ( 'moodlecourse' );
                foreach ( $courseconfig as $key => $value ) {
                    $course->$key = $value;
                }

                if (isset ( $this->config->michlol_course_visible )) {
                    $course->visible = ( int ) $this->config->michlol_course_visible;
                }

                $course = create_course ( $course );

                /*
                 * // workaround for cases when enrolment methods not created
                 * // as we do not know the exact reason why 'create_course' didn't created the methods
                 * // we have no way to be sure it will work here
                 * // the only sure benefit is that we can log the results...
                 * $plugins = enrol_get_plugins ( true );
                 *
                 * foreach ( $plugins as $plugin ) {
                 * if ($plugin->can_add_instance ( $course->id )) {
                 * $plugin->add_default_instance ( $course );
                 *
                 * if ($plugin->can_add_instance ( $course->id )) {
                 * $event = \local_ws_rashim\event\msg_logged::create ( array (
                 * 'context' => context_course::instance ( $course->id ),
                 * 'other' => array (
                 * 'message' => "Failed to create '$plugin->get_name()' enrolment for curse!"
                 * )
                 * ) );
                 *
                 * $event->trigger ();
                 * }
                 * } else {
                 * $event = \local_ws_rashim\event\msg_logged::create ( array (
                 * 'context' => context_course::instance ( $course->id ),
                 * 'other' => array (
                 * 'message' => "Can't add '$plugin->get_name()' enrolment for curse (it may have been added earlier)."
                 * )
                 * ) );
                 *
                 * $event->trigger ();
                 * }
                 * }
                 */

                $event = \local_ws_rashim\event\course_created::create ( 
                        array (
                            'userid' => $this->admin->id,
                            'courseid' => $course->id,
                            'context' => context_course::instance ( $course->id ),
                            'objectid' => $course->id,
                            'other' => array (
                                'fullname' => $course->fullname
                            )
                        ) );

                $event->trigger ();
            } else {
                if (! isset ( $course_id )) {
                    return $this->error ( 5007 );
                }

                $course->idnumber = $course_id;
                $course->fullname = $course_name;
                $course->shortname = $course_shortname;
                $course->category = $category_code;

                $course->password = md5 ( $course_psw );

                update_course ( $course );

                $event = \local_ws_rashim\event\course_updated::create ( 
                        array (
                            'userid' => $this->admin->id,
                            'courseid' => $course->id,
                            'context' => context_course::instance ( $course->id ),
                            'objectid' => $course->id
                        ) );

                $event->trigger ();
            }

            $this->add_syllabus_url ( $course, $course_sylurl );

            return true;
        }
    }

    public function course_delete($admin_name, $admin_psw, $course_id, $nodelete) {
        if ($this->admin_login ( $admin_name, $admin_psw )) {
            if (empty ( $course_id )) {
                return $this->error ( 5007 );
            }

            $conditions = $this->get_course_condition ( $course_id );
            if ($course = $this->DB->get_record ( 'course', $conditions )) {
                $context = context_course::instance ( $course->id );

                if ($nodelete) {
                    $course->visible = ( int ) $this->config->michlol_course_no_hide;

                    update_course ( $course );
                } else {
                    if (! delete_course ( $course, false )) {
                        return $this->error ( 5019 );
                    }
                }
            }

            $event = \local_ws_rashim\event\course_deleted::create ( 
                    array (
                        'userid' => $this->admin->id,
                        'courseid' => $course->id,
                        'context' => $context,
                        'objectid' => $course->id,
                        'other' => array (
                            'fullname' => $course->fullname,
                            'nodelete' => $nodelete
                        )
                    ) );

            $event->trigger ();

            return true;
        }
    }

    public function user_add($admin_name, $admin_psw, $session_key, $user_id, $user_name, $user_psw, $user_firstname, $user_lastname,
            $user_email, $user_phone1, $user_phone2, $user_address, $user_lang, $user_extra, $course_id, $course_role, $group_id,
            $group_name, $role_start, $role_end) {
        if ($this->valid_login ( $session_key, $admin_name, $admin_psw )) {
            $user = $this->add_user ( $user_id, $user_name, $user_psw, $user_firstname, $user_lastname, $user_email, $user_phone1,
                    $user_phone2, $user_address, $user_lang, $user_extra );

            if (isset ( $course_id )) {
                $conditions = $this->get_course_condition ( $course_id );
                if (! $course = $this->DB->get_record ( 'course', $conditions )) {
                    return $this->error ( 5014 );
                }

                if (isset ( $course_role )) {
                    $conditions = array (
                        'shortname' => $course_role
                    );
                    if (! $role = $this->DB->get_record ( 'role', $conditions )) {
                        return $this->error ( 5043 );
                    }

                    $this->user_enroll ( $course->id, $user->id, $role->id, $role_start, $role_end );
                }

                if (isset ( $group_id ) && isset ( $group_name )) {
                    $conditions = array (
                        'courseid' => $course->id,
                        'enrolmentkey' => $group_id
                    );
                    if (! $group = $this->DB->get_record ( 'groups', $conditions )) {
                        $group = new stdClass ();
                        $group->courseid = $course->id;
                        $group->name = $group_name;
                        $group->enrolmentkey = $group_id;

                        if (! $group->id = groups_create_group ( $group )) {
                            return $this->error ( 5027 );
                        }
                    }

                    $conditions = array (
                        'courseid' => $course->id,
                        'idnumber' => $group_id
                    );
                    if (! $grouping = $this->DB->get_record ( 'groupings', $conditions )) {
                        $conditions = array (
                            'courseid' => $course->id,
                            'name' => $group_name
                        );
                        if (! $grouping = $this->DB->get_record ( 'groupings', $conditions )) {
                            $grouping = new stdClass ();
                            $grouping->courseid = $course->id;
                            $grouping->name = $group_name;
                            $grouping->idnumber = $group_id;
                            $grouping->description = 'נוצר ממכלול';

                            if (! $grouping->id = groups_create_grouping ( $grouping )) {
                                return $this->error ( 5028 );
                            }
                        }
                    }

                    if (! groups_assign_grouping ( $grouping->id, $group->id )) {
                        return $this->error ( 5029 );
                    }

                    if (! groups_add_member ( $group->id, $user->id )) {
                        return $this->error ( 5044 );
                    }
                }
            }

            return true;
        }
    }

    public function user_remove($admin_name, $admin_psw, $user_id, $course_id, $course_role) {
        if ($this->admin_login ( $admin_name, $admin_psw )) {
            $conditions = array (
                'idnumber' => $user_id
            );
            if (! $user = $this->DB->get_record ( 'user', $conditions )) {
                return $this->error ( 5011 );
            }

            $conditions = $this->get_course_condition ( $course_id );
            if (! $course = $this->DB->get_record ( 'course', $conditions )) {
                return $this->error ( 5014 );
            }

            $this->user_unenroll ( $course->id, $user->id );

            return true;
        }
    }

    public function bhn_add($admin_name, $admin_psw, $session_key, $course_id, $bhn_shm, $michlol_krs, $michlol_sms, $michlol_sid,
            $moodle_type) {
        if ($this->valid_login ( $session_key, $admin_name, $admin_psw )) {
            if (! isset ( $course_id ) || ! isset ( $bhn_shm ) || ! isset ( $michlol_krs ) || ! isset ( $michlol_sms ) ||
                    ! isset ( $michlol_sid ) || ! isset ( $moodle_type )) {
                return $this->error ( 5007 );
            }

            $conditions = $this->get_course_condition ( $course_id );
            if ($course = $this->DB->get_record ( 'course', $conditions )) {
                $modinfo = $this->add_assignment ( $course->id, 0, $bhn_shm, $michlol_krs, $michlol_sms, $michlol_sid, $moodle_type,
                        0, 0 );
            } else {
                return $this->error ( 5014 );
            }

            return true;
        }
    }

    public function bhn_delete($admin_name, $admin_psw, $michlol_krs, $michlol_sms, $michlol_sid) {
        if ($this->admin_login ( $admin_name, $admin_psw )) {
            if (! isset ( $michlol_krs ) || ! isset ( $michlol_sms ) || ! isset ( $michlol_sid )) {
                return $this->error ( 5007 );
            }

            $conditions = array (
                'michlol_krs_bhn_krs' => $michlol_krs,
                'michlol_krs_bhn_sms' => $michlol_sms,
                'michlol_krs_bhn_sid' => $michlol_sid
            );
            if ($matala = $this->DB->get_record ( 'matalot', $conditions )) {

                $conditions = array (
                    'course' => $matala->course_id,
                    'instance' => $matala->moodle_id
                );
                if ($module = $this->DB->get_record ( 'course_modules', $conditions )) {
                    course_delete_module ( $module->id );
                }

                $conditions = array (
                    'id' => $matala->id
                );
                $this->DB->delete_records ( 'matalot', $conditions );
            } else {
                return $this->error ( 5032 );
            }

            $modname = $this->DB->get_field ( 'modules', 'name', array (
                'id' => $module->module
            ), MUST_EXIST );

            $event = \local_ws_rashim\event\course_module_deleted::create ( 
                    array (
                        'userid' => $this->admin->id,
                        'courseid' => $course->id,
                        'context' => context_course::instance ( $matala->course_id ),
                        'objectid' => $module->id,
                        'other' => array (
                            'modulename' => $modname,
                            'instanceid' => $module->instance
                        )
                    ) );

            $event->trigger ();

            return true;
        }
    }

    public function tikyesod_add($admin_name, $admin_psw, $session_key, $xml) {
        if ($this->valid_login ( $session_key, $admin_name, $admin_psw )) {
            if (! isset ( $xml )) {
                return $this->error ( 5007 );
            }

            $xml = new SimpleXMLElement ( $xml );

            if ($xml) {
                $conditions = array (
                    'idnumber' => "{$xml->DATA->SNL}_{$xml->DATA->SHL}_{$xml->DATA->MIS}"
                );
                if ($course = $this->DB->get_record ( 'course', $conditions )) {
                    $course->fullname = ( string ) $xml->DATA->SHM;
                    $course->shortname = ( string ) $xml->DATA->SHM_UNIQUE;

                    if (isset ( $xml->DATA->FORMAT )) {
                        $course->format = ( string ) $xml->DATA->FORMAT;
                    }

                    update_course ( $course );

                    $event = \local_ws_rashim\event\course_updated::create ( 
                            array (
                                'userid' => $this->admin->id,
                                'courseid' => $course->id,
                                'context' => context_course::instance ( $course->id ),
                                'objectid' => $course->id
                            ) );

                    $event->trigger ();
                } else {
                    $course = new stdClass ();
                    $course->fullname = ( string ) $xml->DATA->SHM;
                    $course->shortname = ( string ) $xml->DATA->SHM_UNIQUE;
                    $course->idnumber = "{$xml->DATA->SNL}_{$xml->DATA->SHL}_{$xml->DATA->MIS}";
                    $course->category = $this->DB->get_field_sql ( 'SELECT MIN(id) FROM {course_categories}', null, MUST_EXIST );

                    $courseconfig = get_config ( 'moodlecourse' );
                    foreach ( $courseconfig as $key => $value ) {
                        $course->$key = $value;
                    }

                    if (isset ( $xml->DATA->FORMAT )) {
                        $course->format = ( string ) $xml->DATA->FORMAT;
                    }

                    if (isset ( $this->config->michlol_course_visible )) {
                        $course->visible = ( int ) $this->config->michlol_course_visible;
                    }

                    $course = create_course ( $course );

                    $event = \local_ws_rashim\event\course_created::create ( 
                            array (
                                'userid' => $this->admin->id,
                                'courseid' => $course->id,
                                'context' => context_course::instance ( $course->id ),
                                'objectid' => $course->id,
                                'other' => array (
                                    'fullname' => $course->fullname
                                )
                            ) );

                    $event->trigger ();
                }

                $this->xml2meetings ( $course->id, $xml );

                if (( integer ) $xml->DATA->PREV_VERSION != - 1) {
                    $this->copy_course_section_tik ( $xml );

                    $this->xml2assignments_link ( $course->id, $xml );
                } else {
                    $this->xml2assignments ( $course->id, $xml );
                }
            } else {
                return $this->error ( 5042 );
            }

            return true;
        }
    }

    public function tikyesod_delete($admin_name, $admin_psw, $shl, $hit) {
        if ($this->admin_login ( $admin_name, $admin_psw )) {
            if (! isset ( $shl ) || ! isset ( $hit )) {
                return $this->error ( 5007 );
            }

            $conditions = array (
                'snl' => '9999',
                'shl' => $shl,
                'hit' => $hit
            );
            if ($tikyesod = $this->DB->get_record ( 'meetings', $conditions )) {
                $conditions = array (
                    'id' => $tikyesod->course_id
                );
                if ($course = $this->DB->get_record ( 'course', $conditions )) {
                    $course->visible = 0;

                    update_course ( $course );
                } else {
                    return $this->error ( 5014 );
                }
            } else {
                return $this->error ( 5041 );
            }

            $event = \local_ws_rashim\event\course_deleted::create ( 
                    array (
                        'userid' => $this->admin->id,
                        'courseid' => $course->id,
                        'context' => context_course::instance ( $course->id ),
                        'objectid' => $course->id,
                        'other' => array (
                            'nodelete' => true,
                            'fullname' => $course->fullname
                        )
                    ) );

            $event->trigger ();

            return true;
        }
    }

    public function machzor_add($admin_name, $admin_psw, $session_key, $xml) {
        if ($this->valid_login ( $session_key, $admin_name, $admin_psw )) {
            if (! isset ( $xml )) {
                return $this->error ( 5007 );
            }

            $xml = new SimpleXMLElement ( $xml );

            if ($xml) {
                $conditions = array (
                    'idnumber' => "{$xml->DATA->SNL}_{$xml->DATA->SHL}_{$xml->DATA->MIS}"
                );
                if ($course = $this->DB->get_record ( 'course', $conditions )) {
                    $course->fullname = ( string ) $xml->DATA->SHM;
                    $course->shortname = ( string ) $xml->DATA->SHM_UNIQUE;

                    if (isset ( $xml->DATA->FORMAT )) {
                        $course->format = ( string ) $xml->DATA->FORMAT;
                    }

                    update_course ( $course );

                    $event = \local_ws_rashim\event\course_updated::create ( 
                            array (
                                'userid' => $this->admin->id,
                                'courseid' => $course->id,
                                'context' => context_course::instance ( $course->id ),
                                'objectid' => $course->id,
                                'other' => array (
                                    'fullname' => $course->fullname
                                )
                            ) );

                    $event->trigger ();
                } else {
                    $course = new stdClass ();
                    $course->fullname = ( string ) $xml->DATA->SHM;
                    $course->shortname = ( string ) $xml->DATA->SHM_UNIQUE;
                    $course->idnumber = "{$xml->DATA->SNL}_{$xml->DATA->SHL}_{$xml->DATA->MIS}";
                    $course->category = 1;

                    $courseconfig = get_config ( 'moodlecourse' );
                    foreach ( $courseconfig as $key => $value ) {
                        $course->$key = $value;
                    }

                    if (isset ( $xml->DATA->FORMAT )) {
                        $course->format = ( string ) $xml->DATA->FORMAT;
                    }

                    if (isset ( $this->config->michlol_course_visible )) {
                        $course->visible = ( int ) $this->config->michlol_course_visible;
                    }

                    $course = create_course ( $course );

                    $event = \local_ws_rashim\event\course_created::create ( 
                            array (
                                'userid' => $this->admin->id,
                                'courseid' => $course->id,
                                'context' => context_course::instance ( $course->id ),
                                'objectid' => $course->id,
                                'other' => array (
                                    'fullname' => $course->fullname
                                )
                            ) );

                    $event->trigger ();
                }

                $this->xml2meetings ( $course->id, $xml );

                $conditions = array (
                    'snl' => '9999',
                    'shl' => ( integer ) $xml->DATA->SHL,
                    'hit' => ( integer ) $xml->DATA->TAVNIT
                );
                if ($tikyesod = $this->DB->get_record ( 'meetings', $conditions )) {
                    $this->copy_course_section ( $tikyesod->course_id, $course->id );
                } else {
                    return $this->error ( 5041 );
                }

                $this->xml2assignments_link ( $course->id, $xml );
            } else {
                return $this->error ( 5042 );
            }

            return true;
        }
    }

    public function machzor_delete($admin_name, $admin_psw, $snl, $shl, $hit) {
        if ($this->admin_login ( $admin_name, $admin_psw )) {
            if (! isset ( $snl ) || ! isset ( $shl ) || ! isset ( $hit )) {
                return $this->error ( 5007 );
            }

            $conditions = array (
                'snl' => $snl,
                'shl' => $shl,
                'hit' => $hit
            );
            if ($machzor = $this->DB->get_record ( 'meetings', $conditions )) {
                $conditions = array (
                    'id' => $machzor->course_id
                );
                if ($course = $this->DB->get_record ( 'course', $conditions )) {
                    $course->visible = 0;

                    update_course ( $course );
                } else {
                    return $this->error ( 5014 );
                }
            } else {
                return $this->error ( 5041 );
            }

            $event = \local_ws_rashim\event\course_deleted::create ( 
                    array (
                        'userid' => $this->admin->id,
                        'courseid' => $course->id,
                        'context' => context_course::instance ( $course->id ),
                        'objectid' => $course->id,
                        'other' => array (
                            'nodelete' => true,
                            'fullname' => $course->fullname
                        )
                    ) );

            $event->trigger ();

            return true;
        }
    }

    public function machzor_user_add($admin_name, $admin_psw, $session_key, $user_id, $user_name, $user_psw, $user_firstname,
            $user_lastname, $user_email, $user_phone1, $user_phone2, $user_address, $user_lang, $user_extra, $snl, $shl, $hit,
            $course_role) {
        if ($this->valid_login ( $session_key, $admin_name, $admin_psw )) {
            $user = $this->add_user ( $user_id, $user_name, $user_psw, $user_firstname, $user_lastname, $user_email, $user_phone1,
                    $user_phone2, $user_address, $user_lang, $user_extra );

            $conditions = array (
                'snl' => $snl,
                'shl' => $shl,
                'hit' => $hit
            );
            if (! $meeting = $this->DB->get_record ( 'meetings', $conditions )) {
                return $this->error ( 5041 );
            }

            $conditions = array (
                'shortname' => $course_role
            );
            if (! $role = $this->DB->get_record ( 'role', $conditions )) {
                return $this->error ( 5044 );
            }

            $this->user_enroll ( $meeting->course_id, $user->id, $role->id );

            return true;
        }
    }

    public function machzor_user_remove($admin_name, $admin_psw, $user_id, $snl, $shl, $hit, $course_role) {
        if ($this->admin_login ( $admin_name, $admin_psw )) {
            $conditions = array (
                'idnumber' => $user_id
            );
            if (! $user = $this->DB->get_record ( 'user', $conditions )) {
                return $this->error ( 5011 );
            }

            $conditions = array (
                'snl' => $snl,
                'shl' => $shl,
                'hit' => $hit
            );
            if (! $meeting = $this->DB->get_record ( 'meetings', $conditions )) {
                return $this->error ( 5041 );
            }

            $this->user_unenroll ( $meeting->course_id, $user->id );

            return true;
        }
    }

    public function machzormfgs_upd($admin_name, $admin_psw, $session_key, $xml) {
        if ($this->valid_login ( $session_key, $admin_name, $admin_psw )) {
            if (! isset ( $xml )) {
                return $this->error ( 5007 );
            }

            $section_num = 0;

            $xml = new SimpleXMLElement ( $xml );

            if ($xml) {
                if (! isset ( $xml->MEETING->WEEK )) {
                    $xml->MEETING->WEEK = - 1;
                }

                if (! isset ( $xml->MEETING->DAY )) {
                    $xml->MEETING->DAY = - 1;
                }

                if (! isset ( $xml->MEETING->MEETING_DATE )) {
                    $xml->MEETING->MEETING_DATE = - 1;
                }

                $conditions = array (
                    'snl' => ( string ) $xml->MEETING->SNL,
                    'shl' => ( string ) $xml->MEETING->SHL,
                    'hit' => ( string ) $xml->MEETING->HIT,
                    'krs' => ( string ) $xml->MEETING->MIS,
                    'mfgs' => ( string ) $xml->MEETING->SID
                );
                if (! $meeting = $this->DB->get_record ( 'meetings', $conditions )) {
                    $mbase = new stdClass ();

                    $conditions = array (
                        'snl' => ( string ) $xml->MEETING->SNL,
                        'shl' => ( string ) $xml->MEETING->SHL,
                        'hit' => ( string ) $xml->MEETING->HIT
                    );
                    if (! $meeting_base = $this->DB->get_records ( 'meetings', $conditions, 'section_num DESC' )) {
                        $conditions = array (
                            'idnumber' => "{$xml->DATA->SNL}_{$xml->DATA->SHL}_{$xml->DATA->MIS}"
                        );
                        if (! $coursebase = $this->DB->get_record ( 'course', $conditions )) {
                            return $this->error ( 5014 );
                        }

                        $mbase->course_id = $coursebase->id;
                        $mbase->section_num = 0;
                        $mbase->snl = ( string ) $xml->MEETING->SNL;
                        $mbase->shl = ( integer ) $xml->MEETING->SHL;
                        $mbase->hit = ( integer ) $xml->MEETING->HIT;
                    } else {
                        $index = array_shift ( array_keys ( $meeting_base ) );

                        $mbase->course_id = $meeting_base [$index]->course_id;
                        $mbase->section_num = $meeting_base [$index]->section_num;
                        $mbase->snl = $meeting_base [$index]->snl;
                        $mbase->shl = $meeting_base [$index]->shl;
                        $mbase->hit = $meeting_base [$index]->hit;
                    }

                    $section = $this->handle_course_section ( $mbase->course_id, $mbase->section_num + 1,
                            ( string ) $xml->MEETING->SHM );

                    $meeting_new->snl = $mbase->snl;
                    $meeting_new->shl = $mbase->shl;
                    $meeting_new->hit = $mbase->hit;
                    $meeting_new->krs = ( integer ) $xml->MEETING->MIS;
                    $meeting_new->mfgs = ( integer ) $xml->MEETING->SID;

                    $meeting_new->course_id = $mbase->course_id;
                    $meeting_new->section_num = $mbase->section_num + 1;

                    $meeting_new->section_id = $section;

                    $meeting_new->subject = ( string ) $xml->MEETING->SUB;

                    $meeting_new->week = ( integer ) $xml->MEETING->WEEK;
                    $meeting_new->day = ( integer ) $xml->MEETING->DAY;

                    $meeting_new->meeting_date = ( integer ) $xml->MEETING->MEETING_DATE;

                    $meeting_new->hour_begin = ( integer ) $xml->MEETING->BEGIN;
                    $meeting_new->hour_end = ( integer ) $xml->MEETING->END;

                    $this->DB->insert_record ( 'meetings', $meeting_new );

                    $course_id = $meeting_new->course_id;
                    $section_num = $meeting_new->section_num;

                    $event = \local_ws_rashim\event\meeting_created::create ( 
                            array (
                                'userid' => $this->admin->id,
                                'objectid' => $section,
                                'courseid' => $meeting_new->course_id,
                                'context' => context_course::instance ( $meeting_new->course_id ),
                                'other' => array (
                                    'sectionnum' => $meeting_new->section_num
                                )
                            ) );

                    $event->trigger ();
                } else {
                    $meeting->week = ( integer ) $xml->MEETING->WEEK;
                    $meeting->day = ( integer ) $xml->MEETING->DAY;

                    $meeting->meeting_date = ( string ) $xml->MEETING->MEETING_DATE;

                    $meeting->hour_begin = ( integer ) $xml->MEETING->BEGIN;
                    $meeting->hour_end = ( integer ) $xml->MEETING->END;

                    $meeting->subject = ( string ) $xml->MEETING->SUB;

                    $section = $this->meeting2course_section ( $meeting );

                    if ($section->section != $meeting->section_num) {
                        $meeting->section_num = $section->section;
                    }

                    $this->handle_course_section ( $meeting->course_id, $meeting->section_num, ( string ) $xml->MEETING->SHM );

                    $this->DB->update_record ( 'meetings', $meeting );

                    $course_id = $meeting->course_id;
                    $section_num = $meeting->section_num;

                    $event = \local_ws_rashim\event\meeting_updated::create ( 
                            array (
                                'userid' => $this->admin->id,
                                'objectid' => $section->id,
                                'courseid' => $meeting->course_id,
                                'context' => context_course::instance ( $meeting->course_id ),
                                'other' => array (
                                    'sectionnum' => $meeting->section_num
                                )
                            ) );

                    $event->trigger ();
                }

                course_get_format ( $course_id )->update_course_format_options ( array (
                    'numsections' => $section_num
                ) );

                $this->xml2assignments ( $course_id, $xml );
            } else {
                return $this->error ( 5042 );
            }

            return true;
        }
    }

    public function machzormfgs_del($admin_name, $admin_psw, $session_key, $xml) {
        if ($this->valid_login ( $session_key, $admin_name, $admin_psw )) {
            if (! isset ( $xml )) {
                return $this->error ( 5007 );
            }

            $xml = new SimpleXMLElement ( $xml );

            if ($xml) {
                $conditions = array (
                    'snl' => ( string ) $xml->MEETING->SNL,
                    'shl' => ( string ) $xml->MEETING->SHL,
                    'hit' => ( string ) $xml->MEETING->HIT,
                    'krs' => ( string ) $xml->MEETING->MIS,
                    'mfgs' => ( string ) $xml->MEETING->SID
                );
                if (! $meeting = $this->DB->get_record ( 'meetings', $conditions )) {
                    return $this->error ( 5041 );
                } else {
                    if ($section = $this->meeting2course_section ( $meeting )) {
                        if (( string ) $xml->MEETING->SNL == '9999') {
                            course_delete_section ( $section->course, $section );
                        } else {
                            $this->handle_course_section ( $section->course, $section->section, $section->name, 0 );
                        }
                    }

                    if (( string ) $xml->MEETING->SNL == '9999') {
                        $this->DB->delete_records ( 'meetings', $conditions );
                    }
                }
            } else {
                return $this->error ( 5042 );
            }

            $event = \local_ws_rashim\event\meeting_deleted::create ( 
                    array (
                        'userid' => $this->admin->id,
                        'objectid' => $section->id,
                        'courseid' => $section->course,
                        'context' => context_course::instance ( $section->course ),
                        'other' => array (
                            'sectionnum' => $section->section,
                            'sectionname' => $section->name
                        )
                    ) );

            $event->trigger ();

            return true;
        }
    }

    public function tikyesod_shl_change($admin_name, $admin_psw, $session_key, $xml) {
        if ($this->valid_login ( $session_key, $admin_name, $admin_psw )) {
            if (! isset ( $xml )) {
                return $this->error ( 5007 );
            }

            $xml = new SimpleXMLElement ( $xml );

            if ($xml) {
                $sql_krs = 'UPDATE {meetings} SET shl = ?, krs = ? WHERE shl = ? AND krs = ?';

                foreach ( $xml->KRS_LIST->children () as $krs ) {
                    $this->DB->execute ( $sql_krs,
                            array (
                                ( integer ) $krs->NEW_SHL,
                                ( integer ) $krs->NEW_MIS,
                                ( integer ) $krs->OLD_SHL,
                                ( integer ) $krs->OLD_MIS
                            ) );
                }

                $sql_hit = 'UPDATE {meetings} SET shl = ? WHERE snl = ? AND shl = ? AND hit = ?';

                foreach ( $xml->HIT_LIST->children () as $hit ) {
                    $this->DB->execute ( $sql_hit,
                            array (
                                ( integer ) $hit->NEW_SHL,
                                ( integer ) $hit->SNL,
                                ( integer ) $hit->OLD_SHL,
                                ( integer ) $hit->MIS
                            ) );

                    $conditions = array (
                        'idnumber' => ( string ) $hit->OLD_KEY
                    );
                    if ($course = $this->DB->get_record ( 'course', $conditions )) {
                        $course->idnumber = ( string ) $hit->NEW_KEY;

                        update_course ( $course );

                        $event = \local_ws_rashim\event\tikyesod_shl_changed::create ( 
                                array (
                                    'userid' => $this->admin->id,
                                    'objectid' => $course->id,
                                    'courseid' => $course->id,
                                    'context' => context_course::instance ( $course->id ),
                                    'other' => array (
                                        'old_shl' => ( integer ) $hit->OLD_SHL,
                                        'new_shl' => ( integer ) $hit->NEW_SHL
                                    )
                                ) );

                        $event->trigger ();
                    }
                }
            } else {
                return $this->error ( 5042 );
            }

            return true;
        }
    }

    public function course_update_key($admin_name, $admin_psw, $course_old_id, $course_new_id, $course_shortname) {
        if ($this->admin_login ( $admin_name, $admin_psw )) {
            $conditions = array (
                'idnumber' => $course_old_id
            );
            if (! $course = $this->DB->get_record ( 'course', $conditions )) {
                return $this->error ( 5014 );
            }

            $course->idnumber = $course_new_id;
            $course->shortname = $course_shortname;

            update_course ( $course );

            $event = \local_ws_rashim\event\course_idnumber_updated::create ( 
                    array (
                        'userid' => $this->admin->id,
                        'objectid' => $course->id,
                        'courseid' => $course->id,
                        'context' => context_course::instance ( $course->id ),
                        'other' => array (
                            'old_idnumber' => $course_old_id,
                            'new_idnumber' => $course_new_id
                        )
                    ) );

            $event->trigger ();

            return true;
        }
    }

    public function manage_ktree($admin_name, $admin_psw, $session_key, $category_id, $parent_id, $category_name, $user_id, $role,
            $add) {
        if ($this->valid_login ( $session_key, $admin_name, $admin_psw )) {
            $conditions = array (
                'idnumber' => $category_id
            );
            $category = $this->DB->get_record ( 'course_categories', $conditions );

            if (! empty ( $parent_id )) {
                $conditions = array (
                    'idnumber' => $parent_id
                );
                $parent = $this->DB->get_record ( 'course_categories', $conditions );

                if (empty ( $parent->id )) {
                    return $this->error ( 5017 );
                }

                $parent_id = $parent->id;
            }

            if (empty ( $category->id )) {
                if ($add) {
                    $cartegory_id = $this->category_add ( $parent_id, $category_id, $category_name );
                } else {
                    return $this->error ( 5018 );
                }
            } else {
                $category_id = $category->id;

                if (empty ( $user_id )) {
                    $cat = coursecat::get ( $category_id );

                    $category->name = ! empty ( $category_name ) ? $category_name : $category->name;
                    $category->parent = ! empty ( $parent_id ) ? $parent_id : null;
                    $cat->update ( $category );
                }
            }

            if (! empty ( $user_id )) {
                $conditions = array (
                    'idnumber' => $user_id
                );
                $user = $this->DB->get_record ( 'user', $conditions );

                if (empty ( $user->id )) {
                    return $this->error ( 5011 );
                }

                $conditions = array (
                    'shortname' => $role
                );

                $roleid = $this->DB->get_field ( 'role', 'id', $conditions, MUST_EXIST );
                if (empty ( $roleid )) {
                    return $this->error ( 5043 );
                }

                if ($add) {
                    role_assign ( $roleid, $user->id, context_coursecat::instance ( $category_id ) );
                } else {
                    role_unassign ( $roleid, $user->id, context_coursecat::instance ( $category_id )->id );
                }
            }
        }

        return true;
    }

    public function course_update_syllabus($admin_name, $admin_psw, $session_key, $course_id, $course_sylurl) {
        if ($this->valid_login ( $session_key, $admin_name, $admin_psw )) {
            $conditions = $this->get_course_condition ( $course_id );
            if (! $course = $this->DB->get_record ( 'course', $conditions )) {
                return $this->error ( 5014 );
            } else {
                $this->add_syllabus_url ( $course, $course_sylurl );

                return true;
            }
        }
    }

    public function bhn_print($admin_name, $admin_psw, $session_key, $idnumber, $krs, $sms, $sid, $mana, $zht_list, $all_same) {
        if ($this->valid_login ( $session_key, $admin_name, $admin_psw )) {
            $question_list = array ();
            $zht_count = 1;
            // REMARK:
            // At this point we extract only 'multichoice' questions
            $supported_question_types = array (
                // 'match',
                // 'truefalse',
                'multichoice'
            );

            // some validations of data
            if (! $course = $this->DB->get_record ( 'course', array (
                'idnumber' => $idnumber
            ) )) {
                return $this->error ( 5014 );
            }

            if (! $matala = $this->DB->get_record ( 'matalot',
                    array (
                        'course_id' => $course->id,
                        'michlol_krs_bhn_krs' => $krs,
                        'michlol_krs_bhn_sms' => $sms,
                        'michlol_krs_bhn_sid' => $sid
                    ) )) {

                $this->trace_message ( "Error... - $krs, $sms, $sid", context_course::instance ( $course->id ) );

                return $this->error ( 5015 );
            }

            if (($matala->moodle_type != 'quiz') || ! $bhn = $this->DB->get_record ( 'quiz', array (
                'id' => $matala->moodle_id
            ) )) {
                return $this->error ( 5016 );
            }

            $this->trace_message ( "Starting... - " . time (), context_course::instance ( $course->id ) );

            $exam = new exam ( $krs, $sms, $sid, $course->id, $matala->moodle_type, $matala->moodle_id, $bhn->name, $bhn->intro );
            $student_exam = new student_exam ( $mana, $matala->moodle_id );

            $zht_list = explode ( ',', $zht_list );

            // in case all exams should be the same
            if ($all_same) {
                $zht_count = count ( $zht_list );
                $zht_list = array (
                    '-1'
                );
            }

            // clean previous attempts for user
            $this->DB->delete_records ( 'quiz_attempts', array (
                'quiz' => $matala->moodle_id,
                'userid' => $this->admin->id
            ) );

            // create new quiz...
            $quiz = quiz::create ( $matala->moodle_id, $this->admin->id );

            if (! $quiz->has_questions ()) {
                return $this->error ( 5013 );
            }

            $quiz_sections = $quiz->get_sections ();
            $indx = 0;

            foreach ( $quiz_sections as $section ) {
                $ex = $this->DB->get_record ( 'quiz_sections_extra', array (
                    'sectionid' => $section->id
                ) );

                $section->id = $indx;
                $section->intro = $ex->intro;
                $section->weight = $ex->weight;
                $section->required = $ex->required;

                $indx ++;

                // record sections on global level
                $exam->sections [$section->id] = new exam_section ( $matala->moodle_id, $section->id, $section->heading,
                        $section->intro, $section->weight, $section->required );
            }

            // ...and initialize attempt
            $attempt = quiz_prepare_and_start_new_attempt ( $quiz, 1, 0 );
            $attempt = quiz_attempt::create ( $attempt->id );

            $slots = $attempt->get_slots ();

            if (empty ( $slots )) {
                return $this->error ( 5013 );
            }

            // remove unsupported questions
            foreach ( $slots as $key => $slot ) {
                $question_attempt = $attempt->get_question_attempt ( $slot );
                $question = $question_attempt->get_question ();
                $question_type = $question->get_type_name ();

                if (! in_array ( $question_type, $supported_question_types )) {
                    unset ( $slots [$key] );

                    $this->trace_message ( "Skip unsupported question of type '$question_type' and id '$question->id'...",
                            context_course::instance ( $course->id ) );
                }
            }

            foreach ( $zht_list as $zht ) {
                $student_exam->students [$zht] = new student ( $mana, $matala->moodle_id, $zht, $zht_count );
                $question_order = 0;

                // shuffle slots
                if ($bhn->shuffleanswers) {
                    shuffle ( $slots );
                }

                $this->trace_message ( "Running for... $zht - " . time (), context_course::instance ( $course->id ) );

                // record question on global level
                // record answers on global level
                foreach ( $slots as $key => $slot ) {
                    $question_attempt = $attempt->get_question_attempt ( $slot );
                    $question = $question_attempt->get_question ();
                    $question_type = $question->get_type_name ();
                    $section = $this->find_section_from_slot ( $quiz_sections, $slot );

                    if (! isset ( $exam->questions [$question->id] )) {
                        $questiontext = $this->img_to_base64 ( $question, $question->questiontext );

                        $exam->questions [$question->id] = new exam_question ( $matala->moodle_id, $question->id, $question_type,
                                $question->name, $questiontext, $question_attempt->get_max_mark (),
                                $question_attempt->get_max_fraction (), $section->id );

                        switch ($question_type) {
                            case 'multichoice' :
                                {
                                    $exam->questions [$question->id]->answers = array ();

                                    foreach ( $question->answers as $answer ) {
                                        $answertext = $this->img_to_base64 ( $question, $answer->answer, 'answer', $answer->id );
                                        $exam->questions [$question->id]->answers [$answer->id] = new exam_answer ( 
                                                $matala->moodle_id, $question->id, $answer->id, $answertext );
                                    }
                                }
                                break;

                            case 'truefalse' :
                                {
                                    $answers = array (
                                        $question->trueanswerid => get_string ( 'yes' ),
                                        $question->falseanswerid => get_string ( 'no' )
                                    );

                                    $exam->questions [$question->id]->answers = array ();

                                    foreach ( $answers as $id => $answer ) {
                                        $exam->questions [$question->id]->answers [$id] = new exam_answer ( $matala->moodle_id,
                                                $question->id, $id, $answer );
                                    }
                                }
                                break;

                            case 'match' :
                                {
                                    $exam->questions [$question->id]->answers = new exam_matchanswer ( null );

                                    foreach ( $question->stems as $id => $stem ) {
                                        $exam->questions [$question->id]->answers->left [$id] = new exam_matchsubanswer ( 
                                                $matala->moodle_id, $question->id, $id, $stem );
                                    }

                                    foreach ( $question->choices as $id => $choice ) {
                                        $exam->questions [$question->id]->answers->right [$id] = new exam_matchsubanswer ( 
                                                $matala->moodle_id, $question->id, $id, $choice );
                                    }
                                }
                                break;
                        }
                    }

                    // record exam for student
                    $student_question = new student_question ( $mana, $matala->moodle_id, $zht, $question->id, $question_order ++ );

                    switch ($question_type) {
                        case 'multichoice' :
                            {
                                $student_question->answers = array_keys ( $question->answers );

                                if ($question->shuffleanswers) {
                                    shuffle ( $student_question->answers );
                                }

                                foreach ( $student_question->answers as $key => $answer ) {
                                    $student_question->answers [$key] = new student_answer ( $mana, $matala->moodle_id, $zht,
                                            $question->id, $answer, $key );
                                }
                            }
                            break;

                        case 'truefalse' :
                            {
                                $student_question->answers = array_keys ( 
                                        array (
                                            $question->trueanswerid => 0,
                                            $question->falseanswerid => 1
                                        ) );

                                if ($bhn->shuffleanswers) {
                                    shuffle ( $student_question->answers );
                                }

                                foreach ( $student_question->answers as $key => $answer ) {
                                    $student_question->answers [$key] = new student_answer ( $mana, $matala->moodle_id, $zht,
                                            $question->id, $answer, $key );
                                }
                            }
                            break;

                        case 'match' :
                            {
                                $student_question->answers = new student_matchanswer ();

                                $student_question->answers->left = array_keys ( $question->stems );
                                $student_question->answers->right = array_keys ( $question->choices );

                                if ($question->shufflestems) {
                                    shuffle ( $student_question->answers->left );
                                    shuffle ( $student_question->answers->right );
                                }

                                foreach ( $student_question->answers->left as $key => $answer ) {
                                    $student_question->answers->left [$key] = new student_answer ( $mana, $matala->moodle_id, $zht,
                                            $question->id, $answer, $key );
                                }

                                foreach ( $student_question->answers->right as $key => $answer ) {
                                    $student_question->answers->right [$key] = new student_answer ( $mana, $matala->moodle_id, $zht,
                                            $question->id, $answer, $key );
                                }
                            }
                            break;
                    }

                    array_push ( $student_exam->students [$zht]->questions, $student_question );
                }
            }

            // clean this attempt for user
            $this->DB->delete_records ( 'quiz_attempts', array (
                'quiz' => $matala->moodle_id,
                'userid' => $this->admin->id
            ) );

            $this->trace_message ( "Building XML... - " . time (), context_course::instance ( $course->id ) );

            $xml_exam = $this->object_to_xml ( $exam );
            $xml_exam = str_replace ( '\'', '&#x27;', substr ( $xml_exam, strpos ( $xml_exam, '?>' ) + 2 ) );

            $xml_student_exam = $this->object_to_xml ( $student_exam );
            $xml_student_exam = substr ( $xml_student_exam, strpos ( $xml_student_exam, '?>' ) + 2 );

            $this->trace_message ( "Calling API... - " . time (), context_course::instance ( $course->id ) );

            if (! local_ws_rashim_observer::send_bhn_for_print ( $xml_exam, $xml_student_exam )) {
                return $this->error ( 5012 );
            }

            $this->trace_message ( "Finishing... - " . time (), context_course::instance ( $course->id ) );

            return true;
        }
    }

    function bhn_save_answer($admin_name, $admin_psw, $session_key, $idnumber, $krs, $sms, $sid, $zht, $answers) {
        if ($this->valid_login ( $session_key, $admin_name, $admin_psw )) {
            // some validations of data
            if (! $course = $this->DB->get_record ( 'course', array (
                'idnumber' => $idnumber
            ) )) {
                return $this->error ( 5014 );
            }

            if (! $user = $this->DB->get_record ( 'user', array (
                'idnumber' => $zht
            ) )) {
                return $this->error ( 5011 );
            }

            if (! $matala = $this->DB->get_record ( 'matalot',
                    array (
                        'course_id' => $course->id,
                        'michlol_krs_bhn_krs' => $krs,
                        'michlol_krs_bhn_sms' => $sms,
                        'michlol_krs_bhn_sid' => $sid
                    ) )) {
                return $this->error ( 5015 );
            }

            if (($matala->moodle_type != 'quiz') || ! $bhn = $this->DB->get_record ( 'quiz', array (
                'id' => $matala->moodle_id
            ) )) {
                return $this->error ( 5016 );
            }

            $xml = new SimpleXMLElement ( $answers );
            $ans = array ();

            foreach ( $xml->RESULTS->RESULT as $result ) {
                $ans [( int ) $result->QUESTION] = ( int ) $result->ANSWER;
            }

            // create new quiz...
            $quiz = quiz::create ( $matala->moodle_id, $user->id );

            if (! $quiz->has_questions ()) {
                return $this->error ( 5013 );
            }

            // ...and initialize attempt
            $prev_attempts = quiz_get_user_attempts ( $quiz->get_quizid (), $user->id, 'all', true );
            $allowed = $quiz->get_num_attempts_allowed ();

            if ($allowed > 0) {
                if (count ( $prev_attempts ) >= $allowed) {
                    return $this->error ( 5021 );
                }
            }

            if ($last_attempt = end ( $prev_attempts )) {
                $last_attempt = $last_attempt->attempt;
            } else {
                $last_attempt = 0;
            }

            $attempt = $this->quiz_prepare ( $quiz, $last_attempt + 1, $last_attempt, true, $user );
            $attempt = quiz_attempt::create ( $attempt->id );

            $slots = $attempt->get_slots ();

            if (empty ( $slots )) {
                return $this->error ( 5013 );
            }

            foreach ( $slots as $key => $slot ) {
                $question_attempt = $attempt->get_question_attempt ( $slot );
                $question = $question_attempt->get_question ();
                $question_type = $question->get_type_name ();

                // REMARK:
                // At this point we assume that these external exams have only 'multichoice' questions
                // so we have a single answer, which is the zero based index of the answer the user picked
                if ($question_type == 'multichoice') {
                    $order = array_flip ( explode ( ',', $question_attempt->get_step ( 0 )->get_all_data () ['_order'] ) );

                    $question_attempt->process_action ( array (
                        'answer' => $order [$ans [$question->id]]
                    ), time (), $user->id );

                    $question_attempt->finish ();
                }
            }

            $attempt->process_finish ( time (), true );

            return true;
        }
    }
}
?>

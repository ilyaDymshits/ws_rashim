<?php
defined ( 'MOODLE_INTERNAL' ) || die ();

require_once (__DIR__ . '../../../course/lib.php');
require_once (__DIR__ . '../../../lib/coursecatlib.php');
require_once (__DIR__ . '../../../lib/moodlelib.php');

function local_duplicate_module($dest_course_id, $module) {
    global $DB, $USER;

    if (! plugin_supports ( 'mod', $module->modname, FEATURE_BACKUP_MOODLE2 )) {
        $err = new stdClass ();
        $err->modtype = get_string ( 'modulename', $module->modname );
        $err->modname = format_string ( $module->name );

        throw new moodle_exception ( 'duplicatenosupport', 'error', '', $err );
    }

    $bc = new backup_controller ( backup::TYPE_1ACTIVITY, $module->id, backup::FORMAT_MOODLE, backup::INTERACTIVE_NO,
            backup::MODE_IMPORT, $USER->id );

    $backupid = $bc->get_backupid ();
    $backupbasepath = $bc->get_plan ()->get_basepath ();

    $bc->execute_plan ();
    $bc->destroy ();

    $rc = new restore_controller ( $backupid, $dest_course_id, backup::INTERACTIVE_NO, backup::MODE_IMPORT, $USER->id,
            backup::TARGET_CURRENT_ADDING );

    $cmcontext = context_module::instance ( $module->id );
    if (! $rc->execute_precheck ()) {
        $precheckresults = $rc->get_precheck_results ();
        if (is_array ( $precheckresults ) && ! empty ( $precheckresults ['errors'] )) {
            if (empty ( $CFG->keeptempdirectoriesonbackup )) {
                fulldelete ( $backupbasepath );
            }
        }
    }

    $rc->execute_plan ();

    $newcmid = null;
    $tasks = $rc->get_plan ()->get_tasks ();
    foreach ( $tasks as $task ) {
        if (is_subclass_of ( $task, 'restore_activity_task' )) {
            if ($task->get_old_contextid () == $cmcontext->id) {
                $newcmid = $task->get_moduleid ();
                break;
            }
        }
    }

    if ($newcmid) {
        $info = get_fast_modinfo ( $dest_course_id );
        $newcm = $info->get_cm ( $newcmid );
        $section = $DB->get_record ( 'course_sections', array (
            'id' => $module->section,
            'course' => $module->course
        ) );
        moveto_module ( $newcm, $section, $module );
        moveto_module ( $module, $section, $newcm );
    }

    rebuild_course_cache ( $module->course );

    $rc->destroy ();

    fulldelete ( $backupbasepath );

    return isset ( $newcm ) ? $newcm : null;
}

function local_copy_model2section($src_module, $dest_sec, $ext = false) {
    global $DB, $USER;

    $conditions = array (
        'id' => $dest_sec
    );
    if ($section_dest = $DB->get_record ( 'course_sections', $conditions )) {
        $conditions = array (
            'id' => $src_module
        );
        if ($module = $DB->get_record ( 'course_modules', $conditions )) {
            $module->modname = $DB->get_field ( 'modules', 'name', array (
                'id' => $module->module
            ), MUST_EXIST );

            $module_new = local_duplicate_module ( $section_dest->course, $module );

            if (! $ext) {
                $conditions = array (
                    'id' => $module_new->id
                );
                $module_new = $DB->get_record ( 'course_modules', $conditions );

                $module_new->completion = 2;
                $module_new->completionview = 1;

                $DB->update_record ( 'course_modules', $module_new );
            }

            delete_mod_from_section ( $module_new->id, $module->section );

            course_add_cm_to_section ( $section_dest->course, $module_new->id, $section_dest->section );

            $event = \local_ws_rashim\event\course_section_copied::create ( 
                    array (
                        'userid' => $USER->id,
                        'courseid' => $section_dest->course,
                        'objectid' => $section_dest->id,
                        'context' => context_course::instance ( $section_dest->course ),
                        'other' => array (
                            'sectionnum' => $section_dest->section,
                            'old_moduleid' => $module->id,
                            'old_sectionid' => $module->section,
                            'new_moduleid' => $module_new->id,
                            'new_sectionid' => $dest_sec
                        )
                    ) );

            $event->trigger ();

            if ($ext) {
                return ($module_new);
            }
        }
    }
}

function _cmp($a, $b) {
    return (($a->added == $b->added) ? 0 : ($a->added > $b->added) ? - 1 : 1);
}

function _sort_latest_category($categories) {
    if ($categories && array_key_exists ( - 1, $categories )) {
        usort ( $categories [- 1]->module, '_cmp' );
    }
}

function load_category(&$root, $parent, $excludes, $days) {
    if (file_exists ( __DIR__ . '../../../tag/classes/tag.php' )) {
        include_once (__DIR__ . '../../../tag/classes/tag.php');

        $is31 = true;
    } else {
        // pre 3.1
        include_once (__DIR__ . '../../../tag/lib.php');

        $is31 = false;
    }

    $config = get_config ( 'local_ws_rashim' );

    $exclude = explode ( ',', $excludes );
    $exclude = array_unique ( $exclude );

    $category = coursecat::get ( $parent );
    $children = $category->get_children ();

    $cat = new stdClass ();

    $cat->id = $category->id;
    $cat->name = $category->name;
    $cat->type = 'category';

    $cat->course = get_courses ( $category->id, 'fullname', 'c.id, c.fullname as name, c.visible' );

    foreach ( $cat->course as $id => $course ) {
        if ($course->visible) {
            $cat->course [$id]->type = 'course';

            if ($is31) {
                $cat->course [$id]->tags = core_tag_tag::get_item_tags_array ( 'core', 'course', $id );
            } else {
                $cat->course [$id]->tags = tag_get_tags_array ( 'course', $id );
            }

            $mod_info = get_fast_modinfo ( $id );
            $sections = $mod_info->get_section_info_all ();
            $mods = $mod_info->get_cms ();

            foreach ( $sections as $section ) {
                $section->name = get_section_name ( $section->course, $section->section );

                if ($section->visible && ! empty ( $section->name )) {
                    $cat->course [$id]->section [$section->id] = new stdClass ();

                    $cat->course [$id]->section [$section->id]->id = $section->id;
                    $cat->course [$id]->section [$section->id]->name = $section->name;
                    $cat->course [$id]->section [$section->id]->type = 'section';

                    $modules = 0;

                    foreach ( $mods as $cmid => $cm ) {
                        $context = context_module::instance ( $cm->id );
                        if (! has_capability ( 'moodle/course:manageactivities', $context )) {
                            continue;
                        }

                        if ($cm->visible && ! $cm->deletioninprogress && ($cm->section == $section->id) &&
                                ! in_array ( $cm->modname, $exclude )) {
                            $cat->course [$id]->section [$section->id]->module [$cmid] = new stdClass ();

                            $cat->course [$id]->section [$section->id]->module [$cmid]->id = $cm->id;
                            $cat->course [$id]->section [$section->id]->module [$cmid]->name = $cm->name;
                            $cat->course [$id]->section [$section->id]->module [$cmid]->modtype = $cm->modname;
                            $cat->course [$id]->section [$section->id]->module [$cmid]->type = 'module';
                            $cat->course [$id]->section [$section->id]->module [$cmid]->icon = $cm->get_icon_url ();

                            $diff = 1;

                            if ($days > 0) {
                                $added = new DateTime ();
                                $added->setTimestamp ( $cm->added );
                                $diff = $added->diff ( new DateTime () )->days;

                                if ($diff <= $days) {
                                    if (! isset ( $root [- 1] )) {
                                        $root [- 1] = new stdClass ();
                                        $root [- 1]->id = - 1;
                                        $root [- 1]->name = $config->ktree_latest_text .
                                                get_string ( 'ktree_latest_days', 'local_ws_rashim', $days );
                                        $root [- 1]->type = 'category';
                                    }

                                    if (! isset ( $root [- 1]->module )) {
                                        $root [- 1]->module = array ();
                                    }

                                    $root [- 1]->module [$cmid] = unserialize ( 
                                            serialize ( $cat->course [$id]->section [$section->id]->module [$cmid] ) );
                                    $root [- 1]->module [$cmid]->added = $cm->added;
                                    // $root [- 1]->module [$cmid]->name .= ' [' . $cat->name . '\\' . $cat->course [$id]->name . '\\' . $cat->course [$id]->section [$section->id]->name . ']';
                                }
                            }

                            $modules ++;
                        }
                    }

                    if ($modules == 0) {
                        unset ( $cat->course [$id]->section [$section->id] );
                    }
                }
            }
        } else {
            unset ( $cat->course [$id] );
        }
    }

    foreach ( $children as $child ) {
        $cat->category [$child->id] = load_category ( $root, $child->id, $excludes, $days );
    }

    return ($cat);
}

function array_to_xml($array, $root = null, $xml = null) {
    $new_xml = $xml;

    if ($new_xml === null) {
        $new_xml = new SimpleXMLElement ( $root !== null ? $root : '<root fileurl="obsolate"/>' );
    }

    foreach ( $array as $key => $value ) {
        $child = $new_xml->addChild ( $value->type );

        $child->addAttribute ( 'name', $value->name );

        if ($value->type == 'module') {
            $child->addAttribute ( 'id', $value->id );
            $child->addAttribute ( 'type', $value->modtype );
        }

        if (($root == null) && isset ( $value->module )) {
            array_to_xml ( $value->module, null, $child );
        } else if ($root == 'course') {
            array_to_xml ( $value->section, 'section', $child );
        } else if ($root == 'section') {
            array_to_xml ( $value->module, 'module', $child );
        } else {
            array_to_xml ( $value->category, 'category', $child );
            array_to_xml ( $value->course, 'course', $child );
        }
    }

    return ($new_xml->asXML ());
}

function get_ktree($codes, $excludes) {
    global $DB;

    $config = get_config ( 'local_ws_rashim' );

    $codes = explode ( ';', $codes );
    $codes = array_unique ( $codes );

    $categories = array ();

    foreach ( $codes as $code ) {
        $category = $DB->get_record ( 'course_categories', array (
            'idnumber' => $code
        ) );

        $categories [$category->id] = load_category ( $categories, $category->id, $excludes, $config->ktree_age );
    }

    _sort_latest_category ( $categories );

    return (array_to_xml ( $categories ));
}

function get_ktree_array($codes, $excludes) {
    global $DB;

    $config = get_config ( 'local_ws_rashim' );

    $codes = explode ( ';', $codes );
    $codes = array_unique ( $codes );

    $categories = array ();

    foreach ( $codes as $code ) {
        if ($category = $DB->get_record ( 'course_categories', array (
            'idnumber' => $code
        ) )) {
            $categories [$category->id] = load_category ( $categories, $category->id, $excludes, $config->ktree_age );
        }
    }

    _sort_latest_category ( $categories );

    return ($categories);
}

function get_ktree_array_by_tag($codes, $excludes) {
    $ktree = get_ktree_array ( $codes, $excludes );
    $new = array ();

    _traverse_tree ( $new, $ktree );

    return ($new);
}

function _traverse_tree(&$new, $root) {
    foreach ( $root as $item ) {
        if ($item->type == 'category') {
            _traverse_tree ( $new, $item );

            foreach ( $item->course as $course ) {
                foreach ( $course->tags as $key => $tag ) {
                    if (! array_key_exists ( $key, $new )) {
                        $new [$key]->id = $key;
                        $new [$key]->name = $tag;
                        $new [$key]->type = 'tag';

                        $new [$key]->course = array ();
                    }

                    $new [$key]->course [$course->id] = $course;
                }
            }
        }
    }
}

?>
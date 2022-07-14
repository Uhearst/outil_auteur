<?php

namespace format_udehauthoring;

use core_form\util;
use format_udehauthoring\model\course_plan;
use format_udehauthoring\model\evaluation_plan;
use format_udehauthoring\model\exploration_plan;
use format_udehauthoring\model\resource_plan;
use format_udehauthoring\model\section_plan;
use format_udehauthoring\model\subquestion_plan;
use stdClass;

class utils
{
    /**
     * For all media files of a file area, render an HTML snippet intended to be used to include the media in a content
     *
     * @param $contextid
     * @param $component
     * @param $filearea
     * @param $itemid
     * @return array
     * @throws \coding_exception
     */
    public static function renderFileAreaHTML($contextid, $component, $filearea, $itemid) {
        $fs = get_file_storage();

        $files = $fs->get_area_files($contextid, $component, $filearea, $itemid);

        $render_array = [];
        foreach ($files as $file) {
            if ($file->get_filename() === '.') {
                continue;
            }

            $render_array[$file->get_filepath() . $file->get_filename()] = self::renderMediaHTML($file);
        }

        return $render_array;
    }

    /**
     * For a single stored_file, render an HTML snippet intended to be used to include the media in a content
     * @param \stored_file $file
     * @return string
     */
    public static function renderMediaHTML(\stored_file $file) {
        $filepath = $file->get_filepath();
        $filename = $file->get_filename();

        // Using double quotes around href attribute is needed for media filter to kick in

        $mimetype = $file->get_mimetype();
        if ($file->is_valid_image()) {
            return "<img src='@@PLUGINFILE@@{$filepath}{$filename}'>";
        } else if(0 === strpos($file->get_mimetype(), 'video')) {
            return "<a href=\"@@PLUGINFILE@@{$filepath}{$filename}#d=1024x768\">@@PLUGINFILE@@{$filepath}{$filename}</a>";
        } else {
            return "@@PLUGINFILE@@{$filepath}{$filename}";
        }

    }

    /**
     * Copy all files from a filearea to another filearea
     *
     * @param $srccontextid
     * @param $srccomponent
     * @param $srcfilearea
     * @param $srcitemid
     * @param $destcontextid
     * @param $destcomponent
     * @param $destfilearea
     * @param $destitemid
     * @throws \coding_exception
     * @throws \file_exception
     * @throws \stored_file_creation_exception
     */
    public static function copyToFilearea(
        $srccontextid, $srccomponent, $srcfilearea, $srcitemid,
        $destcontextid, $destcomponent, $destfilearea, $destitemid
    ) {
        $fs = get_file_storage();

        $srcfiles = [];
        $srcareafiles = $fs->get_area_files($srccontextid, $srccomponent, $srcfilearea, $srcitemid);
        foreach ($srcareafiles as $file) {
            $srcfiles[$file->get_filepath() . $file->get_filename()] = $file;
        }
        $srcfilepaths = array_keys($srcfiles);

        $destfiles = [];
        $destareafiles = $fs->get_area_files($destcontextid, $destcomponent, $destfilearea, $destitemid);
        foreach ($destareafiles as $file) {
            $destfiles[$file->get_filepath() . $file->get_filename()] = $file;
        }
        $destfilepaths = array_keys($destfiles);

        $toinsert = array_diff($srcfilepaths, $destfilepaths);
        $toupdate = array_intersect($srcfilepaths, $destfilepaths);
        $todelete = array_diff($destfilepaths, $srcfilepaths);
        $haschanges = false;

        foreach ($toinsert as $path) {
            $srcfile = $srcfiles[$path];
            if ($srcfile->get_filename() === '.') {
                if($srcfile->get_filepath() === '/') {
                    continue;
                }

                $fs->create_directory($destcontextid, $destcomponent, $destfilearea, $destitemid, $srcfile->get_filepath());
            } else {
                $fs->create_file_from_storedfile([
                    'contextid' => $destcontextid,
                    'component' => $destcomponent,
                    'filearea' => $destfilearea,
                    'itemid' => $destitemid,
                    'filepath' => $srcfile->get_filepath(),
                    'filename' => $srcfile->get_filename()],
                    $srcfile);
                $haschanges = true;
            }
        }

        foreach ($toupdate as $path) {
            $srcfile = $srcfiles[$path];
            $destfile = $destfiles[$path];

            if ($destfile->get_timemodified() < $srcfile->get_timemodified()) {
                $destfile->replace_file_with($srcfile);
                $haschanges = true;
            }
        }

        foreach (array_reverse($todelete) as $path) {
            $destfile = $destfiles[$path];

            $destfile->delete();
            $haschanges = true;
        }

        return $haschanges;
    }

    public static function copyToFileareaMultiple($sources, $destcontextid, $destcomponent, $destfilearea, $destitemid) {
        // sources: folder, contextid, component, filearea, itemid

        $fs = get_file_storage();

        $srcfiles = [];
        foreach ($sources as $source) {
            $srcareafiles = $fs->get_area_files($source->contextid, $source->component, $source->filearea, $source->itemid);
            foreach ($srcareafiles as $file) {
                $srcfiles['/' . $source->folder . $file->get_filepath() . $file->get_filename()] = (object)[
                    'source' => $source,
                    'file' => $file
                ];
            }
        }
        $srcfilepaths = array_keys($srcfiles);

        $destfiles = [];
        $destareafiles = $fs->get_area_files($destcontextid, $destcomponent, $destfilearea, $destitemid);
        foreach ($destareafiles as $file) {
            $destfiles[$file->get_filepath() . $file->get_filename()] = $file;
        }
        $destfilepaths = array_keys($destfiles);

        $toinsert = array_diff($srcfilepaths, $destfilepaths);
        $toupdate = array_intersect($srcfilepaths, $destfilepaths);
        $todelete = array_diff($destfilepaths, $srcfilepaths);
        $haschanges = false;

        // make missing folders
        foreach ($sources as $source) {
            $folder = $fs->get_file($destcontextid, $destcomponent, $destfilearea, $destitemid, '/' . $source->folder, '.');
            if (!$folder) {
                $fs->create_directory($destcontextid, $destcomponent, $destfilearea, $destitemid, "/{$source->folder}/");
            }
        }

        // delete obsolete folders
        $destdirs = $fs->get_directory_files($destcontextid, $destcomponent, $destfilearea, $destitemid, '/');
        $neededdirs = array_map(function ($source) { return '/' . $source->folder; }, $sources);
        foreach ($destdirs as $destdir) {
            if ($destdir === '.') {
                continue;
            }

            if (!in_array($destdir->get_filepath(), $neededdirs)) {
                $destdir->delete();
            }
        }

        foreach ($toinsert as $path) {
            $srcfile = $srcfiles[$path]->file;
            $folder = $srcfiles[$path]->source->folder;
            if ($srcfile->get_filename() === '.') {
                if($srcfile->get_filepath() === '/') {
                    continue;
                }

                $fs->create_directory($destcontextid, $destcomponent, '/' . $folder . $destfilearea, $destitemid, $srcfile->get_filepath());
            } else {

                $fs->create_file_from_storedfile([
                    'contextid' => $destcontextid,
                    'component' => $destcomponent,
                    'filearea' => $destfilearea,
                    'itemid' => $destitemid,
                    'filepath' => '/' . $folder . $srcfile->get_filepath(),
                    'filename' => $srcfile->get_filename()],
                    $srcfile);
                $haschanges = true;
            }
        }

        foreach ($toupdate as $path) {
            $srcfile = $srcfiles[$path]->file;
            $destfile = $destfiles[$path];

            if ($destfile->get_timemodified() < $srcfile->get_timemodified()) {
                $destfile->replace_file_with($srcfile);
                $haschanges = true;
            }
        }

        foreach (array_reverse($todelete) as $path) {
            $destfile = $destfiles[$path];

            $destfile->delete();
            $haschanges = true;
        }

        return $haschanges;
    }

    /**
     * Update a record in the database and indicate if this results in an actual change.
     * The timemodified column will only be updated if an actual change took place.
     * This is useful for determining which part of the exported course has become obsolete.
     *
     * @param string $table table name without prefix, must have timemodified field
     * @param \stdClass $record new record values, must have id field
     * @return bool true if differences are detected with stored values
     * @throws \dml_exception
     */
    public static function db_update_if_changes($table, $record) {
        global $DB;

        $oldrecord = $DB->get_record($table, ['id' => $record->id]);

        // ignore fields omitted in the record update
        foreach ($oldrecord as $key => $val) {
            if (!property_exists($record, $key)) {
                unset($oldrecord->$key);
            }
        }

        // ignore superfluous fields in the record update
        foreach ($record as $key => $val) {
            if (!property_exists($oldrecord, $key)) {
                unset($record->$key);
            }
        }

        if($oldrecord != $record) {
            $record->timemodified = time();
            $DB->update_record($table, $record);
            return true;
        }

        return false;
    }

    /**
     * Calls the Moodle native function file_save_draft_area_files() AND figures out if an actual change
     * has taken place. Returns true if it is the case.
     *
     * @param $draftitemid
     * @param $contextid
     * @param $component
     * @param $filearea
     * @param $itemid
     * @return bool
     * @throws \coding_exception
     */
    public static function file_save_draft_area_files($draftitemid, $contextid, $component, $filearea, $itemid) {
        global $DB;

        $fs = get_file_storage();

        $beforefiles = $fs->get_area_files($contextid, $component, $filearea, $itemid);
        $beforehash = [];
        foreach($beforefiles as $file) {
            $beforehash[$file->get_filepath() . $file->get_filename()] = $file->get_contenthash();
        }

        call_user_func_array('file_save_draft_area_files', func_get_args());

        $afterfiles = $fs->get_area_files($contextid, $component, $filearea, $itemid);
        $afterhash = [];
        foreach($afterfiles as $file) {
            $afterhash[$file->get_filepath() . $file->get_filename()] = $file->get_contenthash();
        }

        $changed = $beforehash != $afterhash;

        return $changed;
    }

    public static function refreshPreview($courseid) {
        rebuild_course_cache($courseid);
        $context = \context_course::instance($courseid);
        $courseplan = \format_udehauthoring\model\course_plan::instance_by_courseid($courseid, $context);
        $publish_target = new \format_udehauthoring\publish\target\preview();
        $publish_structure = new \format_udehauthoring\publish\structure($courseplan, $publish_target);
        $publish_content = new \format_udehauthoring\publish\content($courseplan, $publish_target);
        $publish_structure->publish();
        $publish_content->publish();
    }

    public static function breadCrumb($courseplan) {
        $nav = \html_writer::start_tag('div', ['class' => 'breadcrumb-container p-1']);
        $nav .= \html_writer::start_tag('a', ['href' => (new \moodle_url('/course/view.php', ['id' => $courseplan->courseid]))->out()]);
        $nav .= \html_writer::tag('img', '', [
            'src' => '../assets/logo-moodle-couleur-40x40.png',
            'alt' => 'Visualiser',
            'class' => 'mr-2',
            'style' => 'margin-bottom: 0.25rem !important;']);
        $nav .= \html_writer::end_tag('a');
        $nav .= \html_writer::tag('span', ' > Outil Auteur > ' . strip_tags($courseplan->title));
        $nav .= \html_writer::end_tag('div');
        return $nav;
    }

    public static function navBar($coursetitle, $courseid, $previewurl) {

        $nav = \html_writer::start_tag('div', ['class' => 'custom-navbar']);

        $previewbtnattrs = ['id' => 'preview-button', 'class' => 'btn navbar-btn', 'type' => 'button',
            'onmouseover' => 'this.firstElementChild.setAttribute("src", "../assets/visualiser-actif-icon-40x40.png")',
            'onmouseout' => 'this.firstElementChild.setAttribute("src", "../assets/visualiser-passif-icon-40x40.png")'];

        if (is_array($previewurl)) {
            $previewbtnattrs['data-urls'] = json_encode($previewurl);
        } else {
            $previewbtnattrs['data-url'] = $previewurl;
        }

        $nav .= \html_writer::start_tag('button', $previewbtnattrs);
        $nav .= \html_writer::tag('img', '', [
            'src' => '../assets/visualiser-passif-icon-40x40.png',
            'alt' => 'Visualiser',
            'class' => 'mr-2']);
        $nav .= \html_writer::tag('span', 'Visualiser');
        $nav .= \html_writer::end_tag('button');

        $nav .= \html_writer::start_tag('button', ['id' => 'save-button', 'class' => 'btn navbar-btn', 'type' => 'submit', 'form' => 'udeh-form',
            'onmouseover' => 'this.firstElementChild.setAttribute("src", "../assets/sauvegarder-icon-actif-25x25.png")',
            'onmouseout' => 'this.firstElementChild.setAttribute("src", "../assets/sauvegarder-icon-passif-25x25.png")']);
        $nav .= \html_writer::tag('img', '', [
            'src' => '../assets/sauvegarder-icon-passif-25x25.png',
            'alt' => 'Sauvegarder',
            'class' => 'mr-2']);
        $nav .= \html_writer::tag('span', 'Sauvegarder');
        $nav .= \html_writer::end_tag('button');

        $nav .= \html_writer::start_tag('div', ['class' => 'dropdown navbar-dropdown-btn']);
        $nav .= \html_writer::start_tag('button', ['id' => 'publish-button', 'class' => 'btn dropdown-toggle dropdown-toggle-menu navbar-btn', 'type' => 'button',
            'onmouseover' => 'this.firstElementChild.setAttribute("src", "../assets/publier-actif-icon-25x25.png")',
            'onmouseout' => 'this.firstElementChild.setAttribute("src", "../assets/publier-passif-icon-25x25.png")',
            'data-toggle' => 'dropdown', 'aria-haspopup' => 'true', 'aria-expanded' => 'false']);
        $nav .= \html_writer::tag('img', '', [
            'src' => '../assets/publier-passif-icon-25x25.png',
            'alt' => get_string('publish', 'format_udehauthoring'),
            'class' => 'mr-2']);
        $nav .= \html_writer::tag('span', get_string('publish', 'format_udehauthoring'));
        $nav .= \html_writer::end_tag('button');
        $nav .= \html_writer::start_tag('div', ['class' => 'dropdown-menu dropdown-menu-right', 'aria-labelledby' => 'publish-button']);
        $nav .= \html_writer::tag('button', get_string('publishcourseplan', 'format_udehauthoring'), ['class' => 'dropdown-item', 'id' => 'publishCoursePlan']);
        $nav .= \html_writer::tag('button', get_string('publishcourse', 'format_udehauthoring'), ['class' => 'dropdown-item', 'id' => 'publishCourse']);
        if (has_capability('format/udehauthoring:flushpublish', \context_course::instance($courseid))) {
            $nav .= \html_writer::tag('button', get_string('btnflush', 'format_udehauthoring'), ['class' => 'dropdown-item', 'id' => 'publishFlushCourse']);
        }
        $nav .= \html_writer::end_tag('div');
        $nav .= \html_writer::end_tag('div');

        $nav .= \html_writer::start_tag('div', ['class' => 'dropdown navbar-dropdown-btn']);
        $nav .= \html_writer::start_tag('button', ['id' => 'zoom-button', 'class' => 'btn dropdown-toggle dropdown-toggle-menu navbar-btn', 'type' => 'button',
            'onmouseover' => 'this.firstElementChild.setAttribute("src", "../assets/zoom-actif-icon-25x25.png")',
            'onmouseout' => 'this.firstElementChild.setAttribute("src", "../assets/zoom-passif-icon-25x25.png")',
            'data-toggle' => 'dropdown', 'aria-haspopup' => 'true', 'aria-exppanded' => 'false']);
        $nav .= \html_writer::tag('img', '', [
            'src' => '../assets/zoom-passif-icon-25x25.png',
            'alt' => 'Zoom',
            'class' => 'mr-2']);
        $nav .= \html_writer::tag('span', 'Zoom');
        $nav .= \html_writer::end_tag('button');
        $nav .= \html_writer::start_tag('div', ['class' => 'dropdown-menu dropdown-menu-right', 'aria-labelledby' => 'publish-button']);
        $nav .= \html_writer::tag('button', '200%', ['class' => 'dropdown-item zoom-level', 'id' => 'zoom_200', 'type' => 'button', 'value' => 200]);
        $nav .= \html_writer::tag('button', '175%', ['class' => 'dropdown-item zoom-level', 'id' => 'zoom_175', 'type' => 'button', 'value' => 175]);
        $nav .= \html_writer::tag('button', '150%', ['class' => 'dropdown-item zoom-level', 'id' => 'zoom_150', 'type' => 'button', 'value' => 150]);
        $nav .= \html_writer::tag('button', '125%', ['class' => 'dropdown-item zoom-level', 'id' => 'zoom_125', 'type' => 'button', 'value' => 125]);
        $nav .= \html_writer::tag('button', '100%', ['class' => 'dropdown-item zoom-level', 'id' => 'zoom_100', 'type' => 'button', 'value' => 100]);
        $nav .= \html_writer::tag('button', '75%', ['class' => 'dropdown-item zoom-level', 'id' => 'zoom_75', 'type' => 'button', 'value' => 75]);
        $nav .= \html_writer::tag('button', '50%', ['class' => 'dropdown-item zoom-level', 'id' => 'zoom_50', 'type' => 'button', 'value' => 50]);
        $nav .= \html_writer::tag('button', '25%', ['class' => 'dropdown-item zoom-level', 'id' => 'zoom_25', 'type' => 'button', 'value' => 25]);
        $nav .= \html_writer::end_tag('div');
        $nav .= \html_writer::end_tag('div');

        $nav .= \html_writer::start_tag('button', ['id' => 'close-button', 'class' => 'btn navbar-btn', 'type' => 'button', 'onclick' => "location.href='". (new \moodle_url('/course/view.php', ['id' => $courseid]))->out() ."'",
            'onmouseover' => 'this.firstElementChild.setAttribute("src", "../assets/moodle-logo-actif-40x40.png")',
            'onmouseout' => 'this.firstElementChild.setAttribute("src", "../assets/moodle-logo-passif-40x40.png")']);
        $nav .= \html_writer::tag('img', '', [
            'src' => '../assets/moodle-logo-passif-40x40.png',
            'alt' => 'retour Moodle',
            'style'=> 'width:35px;height:35px;',
            'class' => 'mr-2']);
        $nav .= \html_writer::tag('span', 'Retour sur Moodle');
        $nav .= \html_writer::end_tag('button');
        $nav .= \html_writer::end_tag('div');

        return $nav;
    }

    public static function mainProgress($courseplan) {

        // Get first element of each category
        $section_instances = section_plan::instance_all_by_course_plan_id($courseplan->id);

        $firstsectionid = null;
        $firstsubquestionid = null;

        if($section_instances) {
            $firstsectionid = $section_instances[0]->id;
            foreach ($section_instances as $section_instance) {
                if(property_exists($section_instance, 'subquestions') && !empty($section_instance->subquestions)) {
                    $firstsubquestionid = $section_instance->subquestions[0]->id;
                    break;
                }
            }
        }

        $elementArray = array( 1 =>
            [get_string('courseplan', 'format_udehauthoring'), $courseplan->courseid, 'course'],
            [get_string('sectionstructure', 'format_udehauthoring'), $firstsectionid, 'section'],
            [get_string('subquestionandexploration', 'format_udehauthoring'), $firstsubquestionid, 'subquestion']);

        $nav = \html_writer::start_tag('div', ['id' => 'progress']);
        $nav .= \html_writer::start_tag('div', ['id' => 'progress-bar']);
        $nav .= \html_writer::tag('div', '', ['id' => 'inner-progress-bar']);
        $nav .= \html_writer::end_tag('div');
        $nav .= \html_writer::start_tag('ul', ['id' => 'progress-num']);

        foreach($elementArray as $key=>$element) {
            $nav .= \html_writer::start_tag('div', ['id' => 'element-container']);
            $nav .= \html_writer::start_tag('li', ['class' => 'step']);
            if($element[1] !== null) {
                $nav .= \html_writer::start_tag('a', ['class' => 'progression-step', 'href' => (
                new \moodle_url('/course/format/udehauthoring/redact/' . $element[2] . '.php',
                    [$element[2] === 'course' ? 'course_id' : 'id' => intval($element[1])]))->out()]);
                $nav .= \html_writer::tag('span', $key, ['class' => 'step-number']);
                $nav .= \html_writer::end_tag('a');
            } else {
                $nav .= \html_writer::tag('span', $key, ['class' => 'step-number']);
            }

            $nav .= \html_writer::end_tag('li');
            $nav .= \html_writer::tag('span', $element[0], ['class' => 'step-name']);
            $nav .= \html_writer::end_tag('div');
        }

        $nav .= \html_writer::end_tag('ul');
        $nav .= \html_writer::end_tag('div');
        return $nav;
    }

    public static function mainMenu(course_plan $courseplan, string $currentelement) {
        global $DB;
        $teachingObjs = $DB->get_records('udehauthoring_teaching_obj', ['audehcourseid' => $courseplan->id]);
        $hasLearningEval = false;
        if($teachingObjs !== []) {
            foreach ($teachingObjs as $teachingObj) {
                $learningObj = $DB->get_records('udehauthoring_learning_obj', ['audehteachingobjectiveid' => $teachingObj->id]);
                if($learningObj !== []) {
                    $hasLearningEval = true;
                    break;
                }
            }
        }

        $elementArray = utils::buildMainMenuArray($courseplan, $hasLearningEval);
        $currentPage = strtok($currentelement, '.php');
        $currentId = substr($currentelement, (strpos($currentelement, '=') + 1));

        $nav = \html_writer::start_tag('div', ['id' => 'main-container', 'class' => 'row']);
        $nav .= \html_writer::start_tag('div', ['id' => 'sidebar-container', 'class' => 'col-3']);
        $nav .= \html_writer::start_tag('div', ['id' => 'menu-wrapper', 'class' => 'sticky-top']);
        $nav .= \html_writer::start_tag('div', ['class' => 'element-menu', 'id' => 'element-menu']);
        foreach($elementArray as $key=>$element) {
            if($key == 0) {
                $nav .= utils::buildMainMenuElementDisplay('../assets/plan-de-cours-icon-', get_string('courseplan', 'format_udehauthoring'), 'collapse-course-container',
                    $currentPage === 'course', false);
                $nav .= \html_writer::start_tag('ul', ['id' => 'collapse-course-container', 'class' => ($currentPage === 'course') ? 'collapse show' : 'collapse', 'data-parent' => "#element-menu"]);
                foreach($element->subitems as $item) {
                    $nav .= \html_writer::start_tag('li', ['id' => 'item-' . $item->tagname,
                        'class' => $item->disabled ? 'mb-3 disabled-menu-element' : 'mb-3',
                        'onclick' => 'if(!window.location.href.includes("course.php")) { 
                            this.firstElementChild.firstElementChild.setAttribute("src", "' . $item->imgsrc . 'actif-40x40.png"); 
                            this.setAttribute("onmouseout", "") 
                        }',
                        'onmouseover' => 'this.firstElementChild.firstElementChild.setAttribute("src", "' . $item->imgsrc . 'actif-40x40.png")',
                        'onmouseout' => 'this.firstElementChild.firstElementChild.setAttribute("src", "' . $item->imgsrc . 'passif-40x40.png")']);
                    $nav .= \html_writer::start_tag('a', ['href' => (new \moodle_url('/course/format/udehauthoring/redact/course.php', ['course_id' => $element->courseid], $item->anchor))->out(),
                        'class' => $item->disabled ? 'disabled-menu-element' : '']);
                    $nav .= \html_writer::tag('img', '', [
                        'src' => $item->imgsrc . 'passif-40x40.png',
                        'alt' => $item->name,
                        'class' => 'mr-2']);
                    $nav .= \html_writer::tag('span', $item->name);
                    $nav .= \html_writer::end_tag('a');
                    $nav .= \html_writer::end_tag('li');
                }
                $nav .= \html_writer::end_tag('ul');
            } else if($key == 1) {
                $nav .= utils::buildMainMenuElementDisplay('../assets/structure-des-modules-icon-', get_string('sectionstructure', 'format_udehauthoring'), 'collapse-section-container',
                    $currentPage === 'section', (count($element) == 0) || !$hasLearningEval);
                $nav .= \html_writer::start_tag('ul', ['id' => 'collapse-section-container', 'class' => ($currentPage === 'section') ? 'collapse show' : 'collapse' , 'data-parent' => "#element-menu"]);
                foreach($element as $section) {
                    $nav .= \html_writer::start_tag('li', ['id' => 'item-' . $item->name, 'class' => $currentPage === 'section' && $currentId === $section->id ? 'mb-3 active-menu-element' : 'mb-3',
                        'onclick' => 'this.firstElementChild.firstElementChild.setAttribute("src", "../assets/module-icon-actif-40x40.png"); this.setAttribute("onmouseout", "")',
                        'onmouseover' => 'this.firstElementChild.firstElementChild.setAttribute("src", "../assets/module-icon-actif-40x40.png")',
                        'onmouseout' => $currentPage === 'section' && $currentId === $section->id ?
                            'this.firstElementChild.firstElementChild.setAttribute("src", "../assets/module-icon-actif-40x40.png")' :
                            'this.firstElementChild.firstElementChild.setAttribute("src", "../assets/module-icon-passif-40x40.png")']);
                    $nav .= \html_writer::start_tag('a', ['href' => (new \moodle_url('/course/format/udehauthoring/redact/section.php', ['id' => $section->id]))->out(),
                        'class' => $currentPage === 'section' && $currentId === $section->id ? 'active' : '']);
                    $nav .= \html_writer::tag('img', '', [
                        'src' => $currentPage === 'section' && $currentId === $section->id ? '../assets/module-icon-actif-40x40.png' : '../assets/module-icon-passif-40x40.png',
                        'alt' => 'Module du cours',
                        'class' => 'mr-2']);
                    $nav .= \html_writer::tag('span', $section->name);
                    $nav .= \html_writer::end_tag('a');
                    $nav .= \html_writer::end_tag('li');
                }
                $nav .= \html_writer::end_tag('ul');
            } else if($key == 2) {
                $isactive = $currentPage === 'subquestion' || $currentPage === 'evaluation' || $currentPage === 'globalevaluation';
                $isEmpty = utils::checkIfHasElement($element, $courseplan);
                $nav .= utils::buildMainMenuElementDisplay('../assets/trames-activites-icon-', get_string('subquestionandexploration', 'format_udehauthoring'), 'collapse-subquestion-container',
                    $isactive, $isEmpty);
                $nav .= \html_writer::start_tag('ul', ['id' => 'collapse-subquestion-container', 'class' => $isactive ? 'collapse show' : 'collapse' , 'data-parent' => "#element-menu"]);
                foreach ($element as $key=>$section) {
                    $isCurrentSection = false;
                    if($currentPage === 'subquestion') {
                        foreach ($section->subquestions as $subq) {
                            if($subq->id === $currentId) {
                                $isCurrentSection = true;
                            }
                        }
                    } else if($currentPage === 'evaluation') {
                        if(property_exists($section, 'evaluation') && $section->evaluation->id === $currentId) {
                            $isCurrentSection = true;
                        }
                    }
                    if($section->subquestions == [] && !property_exists($section, 'evaluation')) {
                        $nav .= \html_writer::start_tag('li', ['class' =>'mb-3 disabled-menu-element',
                            'disabled' => true]);
                        $nav .= \html_writer::start_tag('a', [
                            'class' => 'collapsed section_header collapse-link disabled-menu-element',
                            'data-toggle' => 'collapse',
                            'role' => 'button',
                            'aria-expanded' => 'false',
                            'aria-controls' => 'subquestion_' . $key,
                            'style' => 'font-weight: bold']);
                        $nav .= \html_writer::tag('img', '', [
                            'src' => '../assets/module-icon-passif-40x40.png',
                            'alt' => 'Module du cours',
                            'class' => 'mr-2']);
                    } else {
                        $nav .= \html_writer::start_tag('li', ['class' => $isCurrentSection ? 'mb-3 active-menu-element' : 'mb-3',
                            'onclick' => 'this.firstElementChild.firstElementChild.setAttribute("src", "../assets/module-icon-actif-40x40.png"); this.setAttribute("onmouseout", "")',
                            'onmouseover' => 'this.firstElementChild.firstElementChild.setAttribute("src", "../assets/module-icon-actif-40x40.png")',
                            'onmouseout' => $isCurrentSection ?
                                'this.firstElementChild.firstElementChild.setAttribute("src", "../assets/module-icon-actif-40x40.png")' :
                                'this.firstElementChild.firstElementChild.setAttribute("src", "../assets/module-icon-passif-40x40.png")']);
                        $nav .= \html_writer::start_tag('a', [
                            'class' => $isCurrentSection ? 'collapsed section_header collapse-link active' : 'collapsed section_header collapse-link',
                            'href' => '#subquestion_' . $key,
                            'data-toggle' => 'collapse',
                            'role' => 'button',
                            'aria-expanded' => 'false',
                            'aria-controls' => 'subquestion_' . $key,
                            'style' => 'font-weight: bold']);
                        $nav .= \html_writer::tag('img', '', [
                            'src' => $isCurrentSection ? '../assets/module-icon-actif-40x40.png' : '../assets/module-icon-passif-40x40.png',
                            'alt' => 'Module du cours',
                            'class' => 'mr-2']);
                    }



                    $nav .= \html_writer::tag('span', $section->name);
                    $nav .= \html_writer::end_tag('a');
                    $nav .= \html_writer::end_tag('li');
                    $nav .= \html_writer::start_tag('div', ['id' => 'subquestion_' . $key, 'class' => $isCurrentSection ? 'collapse show' : 'collapse', 'data-parent' => "#collapse-subquestion-container"]);
                    if(property_exists($section, 'subquestions')) {
                        $nav .= \html_writer::start_tag('ul', ['class' => 'subquestion-container']);
                        foreach ($section->subquestions as $subquestion) {
                            $nav .= \html_writer::start_tag('li', ['class' => $currentPage === 'subquestion' && $currentId === $subquestion->id ? 'mb-3 active-menu-element' : 'mb-3',
                                'onclick' => 'this.firstElementChild.firstElementChild.setAttribute("src", "../assets/trame-icon-actif-40x40.png"); this.setAttribute("onmouseout", "")',
                                'onmouseover' => 'this.firstElementChild.firstElementChild.setAttribute("src", "../assets/trame-icon-actif-40x40.png")',
                                'onmouseout' => $currentPage === 'subquestion' && $currentId === $subquestion->id ?
                                    'this.firstElementChild.firstElementChild.setAttribute("src", "../assets/trame-icon-actif-40x40.png")' :
                                    'this.firstElementChild.firstElementChild.setAttribute("src", "../assets/trame-icon-passif-40x40.png")']);
                            $nav .= \html_writer::start_tag('a', ['href' => (new \moodle_url('/course/format/udehauthoring/redact/subquestion.php', ['id' => $subquestion->id]))->out(),
                                'class' => $currentPage === 'subquestion' && $currentId === $subquestion->id ? 'active sub-section-link' : 'sub-section-link']);
                            $nav .= \html_writer::tag('img', '', [
                                'src' => $currentPage === 'subquestion' && $currentId === $subquestion->id ? '../assets/trame-icon-actif-40x40.png' : '../assets/trame-icon-passif-40x40.png',
                                'alt' => 'Trame du module',
                                'class' => 'mr-2']);
                            $nav .= \html_writer::tag('span', $subquestion->name);
                            $nav .= \html_writer::end_tag('a');
                            $nav .= \html_writer::end_tag('li');
                        }
                        $nav .= \html_writer::end_tag('ul');
                    }
                    if(property_exists($section, 'evaluation')) {
                        $nav .= \html_writer::start_tag('li', ['class' => $currentPage === 'evaluation' && $currentId === $section->evaluation->id ? 'mb-3 active-menu-element' : 'mb-3',
                            'onclick' => 'this.firstElementChild.firstElementChild.setAttribute("src", "../assets/evaluation-apprentissage-icon-actif-40x40.png"); this.setAttribute("onmouseout", "")',
                            'onmouseover' => 'this.firstElementChild.firstElementChild.setAttribute("src", "../assets/evaluation-apprentissage-icon-actif-40x40.png")',
                            'onmouseout' => $currentPage === 'evaluation' && $currentId === $section->evaluation->id ?
                                'this.firstElementChild.firstElementChild.setAttribute("src", "../assets/evaluation-apprentissage-icon-actif-40x40.png")' :
                                'this.firstElementChild.firstElementChild.setAttribute("src", "../assets/evaluation-apprentissage-icon-passif-40x40.png")']);
                        $nav .= \html_writer::start_tag('a', ['href' => (new \moodle_url('/course/format/udehauthoring/redact/evaluation.php', ['id' => $section->evaluation->id]))->out(),
                            'class' => $currentPage === 'evaluation' && $currentId === $section->evaluation->id ? 'active sub-section-link' : 'sub-section-link']);
                        $nav .= \html_writer::tag('img', '', [
                            'src' => $currentPage === 'evaluation' && $currentId === $section->evaluation->id ? '../assets/evaluation-apprentissage-icon-actif-40x40.png' : '../assets/evaluation-apprentissage-icon-passif-40x40.png',
                            'alt' => 'Evaluation du module',
                            'class' => 'mr-2']);
                        $nav .= \html_writer::tag('span', $section->evaluation->name);
                        $nav .= \html_writer::end_tag('a');
                        $nav .= \html_writer::end_tag('li');
                    }
                    $nav .= \html_writer::end_tag('div');
                }
                if(evaluation_plan::instance_all_global_by_course_plan_id($courseplan->id)) {
                    $nav .= \html_writer::start_tag('li', ['class' => $currentPage === 'globalevaluation' ? 'mb-3 active-menu-element' : 'mb-3',
                        'onclick' => 'this.firstElementChild.firstElementChild.setAttribute("src", "../assets/evaluation-globale-icon-actif-40x40.png"); this.setAttribute("onmouseout", "")',
                        'onmouseover' => 'this.firstElementChild.firstElementChild.setAttribute("src", "../assets/evaluation-globale-icon-actif-40x40.png")',
                        'onmouseout' => $currentPage === 'globalevaluation' ?
                            'this.firstElementChild.firstElementChild.setAttribute("src", "../assets/evaluation-globale-icon-actif-40x40.png")' :
                            'this.firstElementChild.firstElementChild.setAttribute("src", "../assets/evaluation-globale-icon-passif-40x40.png")']);
                    $nav .= \html_writer::start_tag('a', ['href' => (new \moodle_url('/course/format/udehauthoring/redact/globalevaluation.php', ['course_id' => $courseplan->courseid]))->out(),
                        'class' => $currentPage === 'globalevaluation' ? 'active' : '']);
                    $nav .= \html_writer::tag('img', '', [
                        'src' => $currentPage === 'globalevaluation' ? '../assets/evaluation-globale-icon-actif-40x40.png' : '../assets/evaluation-globale-icon-passif-40x40.png',
                        'alt' => 'Evaluation globale',
                        'class' => 'mr-2']);
                    $nav .= \html_writer::tag('span', 'Evaluation globale');
                    $nav .= \html_writer::end_tag('a');
                    $nav .= \html_writer::end_tag('li');
                }

                $nav .= \html_writer::end_tag('ul');

            }

        }
        $nav .= \html_writer::end_tag('div');
        $nav .= \html_writer::end_tag('div');

        $nav .= \html_writer::end_tag('div');
        $nav .= \html_writer::start_tag('div', ['id' => 'content-container', 'class' => 'col']);
        return $nav;
    }

    public static function checkIfHasElement($elements, $courseplan) {
        if(count($elements) > 0) {
            for($i = 0; $i < count($elements); $i++) {
                if(property_exists($elements[$i], 'subquestions') && $elements[$i]->subquestions != [] && $elements[$i]->subquestions != null) {
                    return false;
                }
                if(property_exists($elements[$i], 'evaluation') && $elements[$i]->evaluation != null) {
                    return false;
                }
            }
        }
        if(evaluation_plan::instance_all_global_by_course_plan_id($courseplan->id) != []) {
            return false;
        }
        return true;

    }

    public static function checkEmptySubElement($elements, $courseplan) {
        if(count($elements) > 0) {
            for($i = 0; $i < count($elements); $i++) {
                if(property_exists($elements[$i], 'subquestions') && $elements[$i]->subquestions != [] && $elements[$i]->subquestions != null) {
                    return false;
                }
                if(property_exists($elements[$i], 'evaluation') && $elements[$i]->evaluation != null) {
                    return false;
                }
            }
        }
        if(evaluation_plan::instance_all_global_by_course_plan_id($courseplan->id) != []) {
            return false;
        }
        return true;

    }

    public static function buildMainMenuElementDisplay($url, $content, $target, $isactive, $isdisabled = false) {
        if($isdisabled) {
            $nav = \html_writer::start_tag('div', ['class' => 'menu-main-element disabled-menu-element',
                'disabled' => true]);
            $nav .= \html_writer::start_tag('a', [
                'class' => 'collapsed collapse-link disabled-menu-element',
                'disabled' => true,
                'data-toggle' => 'collapse',
                'role' => 'button',
                'aria-expanded' => 'false',
                'href' => 'javascript:void(0);',
                'aria-controls' => $target,
                'style' => 'font-weight: bold']);
            $nav .= \html_writer::tag('img', '', [
                'src' => $url . 'passif-40x40.png',
                'alt' => 'altimg',
                'class' => 'mr-2']);
            $nav .= \html_writer::tag('span', $content);
            $nav .= \html_writer::end_tag('a');
            $nav .= \html_writer::end_tag('div');
        } else {
            $nav = \html_writer::start_tag('div', ['class' => $isactive ? 'menu-main-element active-menu-element' : 'menu-main-element',
                'onmouseover' => 'this.firstElementChild.firstElementChild.setAttribute("src", "' . $url . 'actif-40x40.png")',
                'onmouseout' => !$isactive ? 'this.firstElementChild.firstElementChild.setAttribute("src", "' . $url . 'passif-40x40.png")' : 'this.firstElementChild.firstElementChild.setAttribute("src", "' . $url . 'actif-40x40.png")']);
            $nav .= \html_writer::start_tag('a', [
                'class' => $isactive ? 'collapsed collapse-link active' : 'collapsed collapse-link',
                'data-target' => '#' . $target,
                'data-toggle' => 'collapse',
                'role' => 'button',
                'aria-expanded' => $isactive ? 'true' : 'false',
                'href' => 'javascript:void(0);',
                'aria-controls' => $target,
                'style' => 'font-weight: bold']);
            $nav .= \html_writer::tag('img', '', [
                'src' => $isactive ? $url . 'actif-40x40.png' : $url . 'passif-40x40.png',
                'alt' => 'altimg',
                'class' => 'mr-2']);
            $nav .= \html_writer::tag('span', $content);
            $nav .= \html_writer::end_tag('a');
            $nav .= \html_writer::end_tag('div');
        }

        return $nav;
    }

    public static function buildMainMenuArray(course_plan $courseplan, $hasLearningEval) {

        $elementArray = [];
        $sections = [];
        $subquestionsParent = [];
        $subquestions = [];
        $section_instances = section_plan::instance_all_by_course_plan_id($courseplan->id);
        for($i = 0; $i < 3; $i++) {
            if($i == 0) {
                $obj = new stdClass();
                $obj->courseid = $courseplan->courseid;
                $obj->name = get_string('courseplan', 'format_udehauthoring');
                $obj->subitems = utils::buildCourseItems($courseplan->id, $hasLearningEval);
                $elementArray[] = $obj;
            } else if($i == 1) {
                if ($section_instances) {
                    foreach ($section_instances as $key=>$section) {
                        $obj = new stdClass();
                        $obj->id = $section->id;
                        $obj->name = get_string('section', 'format_udehauthoring') . ' ' . ($key + 1);
                        $sections[] = $obj;
                    }
                    $elementArray[] = $sections;
                } else {
                    $elementArray[] = $sections;
                }
            } else if($i == 2) {
                foreach ($sections as $key=>$section) {
                    $obj = new stdClass();
                    $obj->id = $section->id;
                    $obj->name = get_string('section', 'format_udehauthoring') . ' ' . ($key + 1);
                    $subquestion_instances = subquestion_plan::instance_all_by_section_plan_id($section->id);
                    if ($subquestion_instances) {
                        foreach ($subquestion_instances as $innerkey=>$subquestion) {
                            $objSubQuestion = new stdClass();
                            $objSubQuestion->id = $subquestion->id;
                            $objSubQuestion->name = get_string('subquestion', 'format_udehauthoring') . ' ' . ($key + 1) . '.' . ($innerkey + 1);
                            $subquestions[] = $objSubQuestion;
                        }
                        $obj->subquestions = $subquestions;
                        $subquestions = [];
                    } else {
                        $obj->subquestions = [];
                    }
                    $evaluation_instance = evaluation_plan::instance_by_section_plan_id($section->id);
                    if ($evaluation_instance) {
                        $objEvaluation = new stdClass();
                        $objEvaluation->id = $evaluation_instance->id;
                        $objEvaluation->name = get_string('evaluation', 'format_udehauthoring');
                        $obj->evaluation = $objEvaluation;
                    }
                    $subquestionsParent[] = $obj;
                }
                $elementArray[] = $subquestionsParent;
            }
        }

        return $elementArray;

    }

    public static function buildCourseItems($id, $hasLearningEval) {
        global $DB;
        $elementArray = [];
        for($i = 0; $i < 4; $i++) {
            $obj = new stdClass();
            switch ($i) {
                case 0:
                    $obj->name = get_string('generalinformations', 'format_udehauthoring');
                    $obj->anchor = 'displayable-form-informations-container';
                    $obj->imgsrc = '../assets/information-generale-icon-';
                    $obj->disabled = false;
                    $obj->tagname = 'generalinformations';
                    break;
                case 1:
                    $obj->name = get_string('teachingobjectives', 'format_udehauthoring');
                    $obj->anchor = 'displayable-form-objectives-container';
                    $obj->imgsrc = '../assets/objectif-enseignement-icon-';
                    $obj->disabled = false;
                    $obj->tagname = 'teachingobjectives';
                    break;
                case 2:
                    $obj->name = get_string('coursesections', 'format_udehauthoring');
                    $obj->anchor = 'displayable-form-sections-container';
                    $obj->imgsrc = '../assets/modules-du-cours-icon-';
                    $obj->disabled = false;
                    $obj->tagname = 'coursesections';
                    break;
                case 3:
                    $obj->name = get_string('learningevaluations', 'format_udehauthoring');
                    $obj->anchor = 'displayable-form-evaluations-container';
                    $obj->imgsrc = '../assets/evaluation-apprentissage-icon-';
                    $obj->disabled = false;
                    $obj->tagname = 'learningevaluations';
                    break;
            }
            $elementArray[] = $obj;
        }

        if($id == null) {
            $elementArray[1]->disabled = true;
            $elementArray[2]->disabled = true;
            $elementArray[3]->disabled = true;
        } else {
            if(!$DB->get_records('udehauthoring_section', ['audehcourseid' => $id]) || !$hasLearningEval) {
                $elementArray[3]->disabled = true;
            }
        }
        return $elementArray;
    }

    public static function get_dynamic_help_string($name) {
        global $DB;
        $element =  $DB->get_record('config', ['name' => $name]);
        if ($element) {
            return $element->value;
        }
        return null;
    }

    public static function formatGlobalEvalDataForJs($globalevals, $toolList) {

        $formattedevals = [];
        foreach ($globalevals as $index => $eval) {
            $obj = new stdClass();
            $obj->audehsectionid = $eval->audehsectionid;
            $obj->audehcourseid = $eval->audehcourseid;
            $obj->title = $eval->title;
            $obj->description = $eval->description;
            $obj->weight = $eval->weight;
            $obj->associatedobjtext = $eval->associatedobjtext;
            $obj->toolname = $toolList[$index];
            $formattedevals[] = $obj;
        }

        return $formattedevals;

    }

    public static function getPreviewUrl($courseid, $sectionindex=false, $subquestionindex=false, $isevaluations=false) {
        global $DB;

        // build cmidnumber
        $target = new \format_udehauthoring\publish\target\preview();
        $cmidnumber = $target->make_cmidnumber($courseid, $sectionindex, $subquestionindex, $isevaluations);

        // find cm
        $cmid = $DB->get_field('course_modules', 'id', ['idnumber' => $cmidnumber], IGNORE_MISSING);

        if (!$cmid) {
            return false;
        }

        // make url
        return (new \moodle_url('/mod/page/view.php', ['id' => $cmid]))->out(false);

    }

}
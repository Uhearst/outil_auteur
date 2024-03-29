<?php

use format_udehauthoring\publish\target;

/**
 * Restores all data from the UdeH authoring tool.
 */
class restore_format_udehauthoring_plugin extends restore_format_plugin {

    /**
     * Lists all backed up elements to be processed during restoration.
     *
     * @return restore_path_element[]
     */
    protected function define_course_plugin_structure() {
        $paths = [
            new restore_path_element('udeha_course',    $this->get_pathfor('/udeha_course')),
            new restore_path_element('udeha_section',   $this->get_pathfor('/udeha_course/udeha_sections/udeha_section')),
            new restore_path_element('udeha_teaching_obj',   $this->get_pathfor('/udeha_course/udeha_teaching_objs/udeha_teaching_obj')),
            new restore_path_element('udeha_learning_obj',   $this->get_pathfor('/udeha_course/udeha_teaching_objs/udeha_teaching_obj/udeha_learning_objs/udeha_learning_obj')),
            new restore_path_element('udeha_unit',   $this->get_pathfor('/udeha_course/udeha_units/udeha_unit')),
            new restore_path_element('udeha_evaluation',   $this->get_pathfor('/udeha_course/udeha_evaluations/udeha_evaluation')),
            new restore_path_element('udeha_eval_tool',   $this->get_pathfor('/udeha_course/udeha_evaluations/udeha_evaluation/udeha_eval_tool')),
            new restore_path_element('udeha_subquestion',   $this->get_pathfor('/udeha_course/udeha_sections/udeha_section/udeha_subquestions/udeha_subquestion')),
            new restore_path_element('udeha_exploration',   $this->get_pathfor('/udeha_course/udeha_sections/udeha_section/udeha_subquestions/udeha_subquestion/udeha_explorations/udeha_exploration')),
            new restore_path_element('udeha_resource',   $this->get_pathfor('/udeha_course/udeha_sections/udeha_section/udeha_subquestions/udeha_subquestion/udeha_resources/udeha_resource')),
            new restore_path_element('udeha_evaluation_obj',   $this->get_pathfor('/udeha_course/udeha_evaluation_objs/udeha_evaluation_obj')),
            new restore_path_element('udeha_exp_tool',   $this->get_pathfor('/udeha_course/udeha_sections/udeha_section/udeha_subquestions/udeha_subquestion/udeha_explorations/udeha_exploration/udeha_exp_tool')),
        ];

        return $paths;
    }

    /**
     * Restores the global course information and files
     *
     * @param $data
     * @return void
     * @throws dml_exception
     */
    public function process_udeha_course($data) {
        global $DB;

        $cache = \cache::make_from_params(cache_store::MODE_SESSION, 'format_udehauthoring', 'restore');
        $cache->purge();

        $record = (object)$data;
        $oldid = $record->id;
        $record->courseid = $this->task->get_courseid();

        $course = $DB->get_record('course', ['id' => $record->courseid]);
        $record->title = $course->fullname;
        $record->code = $course->shortname;
        $newid = $DB->insert_record('udehauthoring_course', $record);
        $this->set_mapping('udeha_course', $oldid, $newid);
        $this->add_related_files('format_udehauthoring', 'courseintroduction', null);
    }

    /**
     * Restores a teaching objective information
     *
     * @param $data
     * @return void
     * @throws dml_exception
     */
    public function process_udeha_teaching_obj($data) {
        global $DB;
        $record = (object)$data;
        $oldid = $record->id;
        $record->audehcourseid = $this->get_new_parentid('udeha_course');
        $newid = $DB->insert_record('udehauthoring_teaching_obj', $record);
        $this->set_mapping('udeha_teaching_obj', $oldid, $newid);
    }

    /**
     * Restores a learning objective information
     *
     * @param $data
     * @return void
     * @throws dml_exception
     */
    public function process_udeha_learning_obj($data) {
        global $DB;
        $record = (object)$data;
        $oldid = $record->id;
        $record->audehteachingobjectiveid = $this->get_new_parentid('udeha_teaching_obj');
        $newid = $DB->insert_record('udehauthoring_learning_obj', $record);
        $this->set_mapping('udeha_learning_obj', $oldid, $newid);
    }

    public function process_udeha_unit($data) {
        global $DB;
        $record = (object)$data;
        $oldid = $record->id;
        $record->audehcourseid = $this->get_new_parentid('udeha_course');
        $newid = $DB->insert_record('udehauthoring_unit', $record);
        $this->set_mapping('udehauthoring_unit', $oldid, $newid);
    }


    /**
     * Restores a section information and files
     *
     * @param $data
     * @return void
     * @throws dml_exception
     */
    public function process_udeha_section($data) {
        global $DB;
        $record = (object)$data;
        $oldid = $record->id;
        $record->audehcourseid = $this->get_new_parentid('udeha_course');
        $newid = $DB->insert_record('udehauthoring_section', $record);
        $this->set_mapping('udeha_section', $oldid, $newid, true);
        $this->add_related_files('format_udehauthoring', 'sectionintroduction', 'udeha_section');
        $this->add_related_files('format_udehauthoring', 'sectionvignette', 'udeha_section');
    }

    /**
     * Restores an evaluation information and files
     *
     * @param $data
     * @return void
     * @throws dml_exception
     */
    public function process_udeha_evaluation($data) {
        global $DB;

        $record = (object)$data;
        $oldid = $record->id;
        $record->audehcourseid = $this->get_new_parentid('udeha_course');
        $record->audehsectionid = $this->get_mappingid('udeha_section', $record->audehsectionid, 0);
        $newid = $DB->insert_record('udehauthoring_evaluation', $record);
        $this->set_mapping('udeha_evaluation', $oldid, $newid, true);
        $this->add_related_files('format_udehauthoring', 'evaluationintroduction', 'udeha_evaluation');
    }

    /**
     * Restores a subquestion information and files
     *
     * @param $data
     * @return void
     * @throws dml_exception
     */
    public function process_udeha_subquestion($data) {
        global $DB;

        $record = (object)$data;
        $oldid = $record->id;
        $record->audehsectionid = $this->get_new_parentid('udeha_section');
        $record->learningobjectiveid = $this->get_mappingid('udeha_learning_obj', $record->learningobjectiveid, 0);
        $newid = $DB->insert_record('udehauthoring_sub_question', $record);
        $this->set_mapping('udeha_subquestion', $oldid, $newid, true);
        $this->add_related_files('format_udehauthoring', 'subquestionvignette', 'udeha_subquestion');
    }

    /**
     * Restores an exploration information and files
     *
     * @param $data
     * @return void
     * @throws dml_exception
     */
    public function process_udeha_exploration($data) {
        global $DB;

        $record = (object)$data;
        $oldid = $record->id;
        $record->audehsubquestionid = $this->get_new_parentid('udeha_subquestion');
        $newid = $DB->insert_record('udehauthoring_exploration', $record);
        $this->set_mapping('udeha_exploration', $oldid, $newid, true);
        $this->add_related_files('format_udehauthoring', 'explorationmedia', 'udeha_exploration');
    }

    /**
     * Restores a resource information and files
     *
     * @param $data
     * @return void
     * @throws dml_exception
     */
    public function process_udeha_resource($data) {
        global $DB;

        $record = (object)$data;
        $oldid = $record->id;
        $record->audehsubquestionid = $this->get_new_parentid('udeha_subquestion');
        $newid = $DB->insert_record('udehauthoring_resource', $record);
        $this->set_mapping('udeha_resource', $oldid, $newid, true);
        $this->add_related_files('format_udehauthoring', 'resourcevignette', 'udeha_resource');
    }

    public function process_udeha_evaluation_obj($data) {
        global $DB;

        $record = (object)$data;
        $record->audehcourseid = $this->get_new_parentid('udeha_course');
        $record->audehevaluationid = $this->get_mappingid('udeha_evaluation', $record->audehevaluationid, 0);
        $record->audehlearningobjectiveid = $this->get_mappingid('udeha_learning_obj', $record->audehlearningobjectiveid, 0);
        $DB->insert_record('udehauthoring_evaluation_obj', $record);
    }

    public function process_udeha_exp_tool($data) {
        $cache = \cache::make_from_params(cache_store::MODE_SESSION, 'format_udehauthoring', 'restore');

        $record = (object)$data;
        $record->courseid = $this->task->get_courseid();
        $record->audehexplorationid = $this->get_new_parentid('udeha_exploration');

        $cachekey = 'exptoolrecord-' . $record->tooltype . '-' . $record->toolid;
        $cache->set($cachekey, $record);
   }

    public function process_udeha_eval_tool($data) {
        $cache = \cache::make_from_params(cache_store::MODE_SESSION, 'format_udehauthoring', 'restore');

        $record = (object)$data;
        $record->courseid = $this->task->get_courseid();
        $record->audehevaluationid = $this->get_new_parentid('udeha_evaluation');

        $cachekey = 'evaltoolrecord-' . $record->tooltype . '-' . $record->toolid;
        $cache->set($cachekey, $record);
    }

    /**
     * Necessary for after_restore_module to get called
     * @return restore_path_element[]
     */
    protected function define_module_plugin_structure() {
        return [ new restore_path_element('udeha_dummy',    '/activity'), ];
    }
    public function process_udeha_dummy() {}

    /**
     * Rewrites all course modules ID numbers managed by the UdeH authoring tool.
     *
     * @return void
     * @throws dml_exception
     */
    public function after_restore_module() {
        global $DB;

        // Rewrites IDNUMBERS

        $cm = $DB->get_record('course_modules', ['id' => $this->task->get_moduleid()]);

        if ($cm->idnumber) {
            $target = target::get_target_by_cm($cm);
            $idnumberdata = $target->unpack_cmidnumber($cm->idnumber);
            if ($idnumberdata) {
                $cm->idnumber = $target->make_cmidnumber($cm->course, $idnumberdata->moduleindex, $idnumberdata->subquestionindex, $idnumberdata->isevaluations, $idnumberdata->toolindex);
            }
        }

        $DB->update_record('course_modules', $cm);

        // Map exploration tools

        $cache = \cache::make_from_params(cache_store::MODE_SESSION, 'format_udehauthoring', 'restore');

        $mod = $DB->get_record('modules', ['id' => $cm->module]);

        $key = 'exptoolrecord-' . $mod->name . '-' . $this->task->get_old_activityid();

        if ($cache->has($key)) {
            $record = $cache->get($key);
            $record->toolid = $this->task->get_activityid();
            $DB->insert_record('udehauthoring_exp_tool', $record);
        }

        // Map evaluation tool tools

        $mod = $DB->get_record('modules', ['id' => $cm->module]);

        $key = 'evaltoolrecord-' . $mod->name . '-' . $this->task->get_old_activityid();

        if ($cache->has($key)) {
            $record = $cache->get($key);
            $record->toolid = $this->task->get_activityid();
            $DB->insert_record('udehauthoring_eval_tool', $record);
        }
    }
}
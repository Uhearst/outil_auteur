<?php

/**
 * Backs up all data from the UdeH authoring tool.
 */
class backup_format_udehauthoring_plugin extends backup_format_plugin {

    /**
     * Called once when doing a course backup.
     *
     * @return backup_plugin_element
     * @throws base_element_struct_exception
     */
    protected function define_course_plugin_structure() {
        $plugin = $this->get_plugin_element();

        $format_udehauthoring = new backup_nested_element($this->get_recommended_name());
        $plugin->add_child($format_udehauthoring);

        // Course backup
        // todo what to do with units???
        $course_plan = new backup_nested_element('udeha_course', ['id'], [
            'unit', 'code', 'credit', 'bloc', 'teachername', 'teacherphone', 'teachercellphone', 'teacheremail',
            'teachercontacthours', 'teacherzoomlink', 'coursezoomlink',
            'title', 'question', 'embed', 'isembed', 'description', 'problematic', 'place', 'method', 'bibliography',
            'attendance', 'plagiarism', 'disponibility', 'annex', 'timemodified'
        ]);
        $format_udehauthoring->add_child($course_plan);
        $course_plan->set_source_table('udehauthoring_course', ['courseid' => backup::VAR_COURSEID]);
        $course_plan->annotate_files('format_udehauthoring', 'courseintroduction', null);

        // Teaching objectives backup

        $teaching_objs = new backup_nested_element('udeha_teaching_objs');
        $course_plan->add_child($teaching_objs);

        $teaching_obj = new backup_nested_element('udeha_teaching_obj', ['id'], [
            'teachingobjective', 'timemodified'
        ]);
        $teaching_objs->add_child($teaching_obj);
        $teaching_obj->set_source_table('udehauthoring_teaching_obj', ['audehcourseid' => backup::VAR_PARENTID]);

        // Learning objectives backup

        $learning_objs = new backup_nested_element('udeha_learning_objs');
        $teaching_obj->add_child($learning_objs);

        $learning_obj = new backup_nested_element('udeha_learning_obj', ['id'], [
            'learningobjective', 'learningobjectivecompetency', 'audehevaluationid', 'timemodified'
        ]);
        $learning_objs->add_child($learning_obj);
        $learning_obj->set_source_table('udehauthoring_learning_obj', ['audehteachingobjectiveid' => backup::VAR_PARENTID]);

        // Sections backup

        $section_plans = new backup_nested_element('udeha_sections');
        $course_plan->add_child($section_plans);

        $section_plan = new backup_nested_element('udeha_section', ['id'], [
            'title', 'description', 'introductiontext', 'question', 'comments', 'timemodified'
        ]);
        $section_plans->add_child($section_plan);
        $section_plan->set_source_table('udehauthoring_section', ['audehcourseid' => backup::VAR_PARENTID]);
        $section_plan->annotate_files('format_udehauthoring', 'sectionintroduction', 'id');
        $section_plan->annotate_files('format_udehauthoring', 'sectionvignette', 'id');

        // Evaluations backup

        $evaluation_plans = new backup_nested_element('udeha_evaluations');
        $course_plan->add_child($evaluation_plans);

        $evaluation_plan = new backup_nested_element('udeha_evaluation', ['id', 'audehsectionid'], [
            'title', 'description', 'descriptionfull', 'instructions', 'criteria', 'weight', 'timemodified'
        ]);
        $evaluation_plans->add_child($evaluation_plan);
        $evaluation_plan->set_source_table('udehauthoring_evaluation', ['audehcourseid' => backup::VAR_PARENTID]);
        $evaluation_plan->annotate_files('format_udehauthoring', 'evaluationintroduction', 'id');

        // Subquestions backup

        $subquestion_plans = new backup_nested_element('udeha_subquestions');
        $section_plan->add_child($subquestion_plans);

        $subquestion_plan = new backup_nested_element('udeha_subquestion', ['id'], [
            'title', 'enonce', 'learningobjectiveid', 'timemodified'
        ]);
        $subquestion_plans->add_child($subquestion_plan);
        $subquestion_plan->set_source_table('udehauthoring_sub_question', ['audehsectionid' => backup::VAR_PARENTID]);
        $subquestion_plan->annotate_files('format_udehauthoring', 'subquestionvignette', 'id');

        // Explorations backup

        $exploration_plans = new backup_nested_element('udeha_explorations');
        $subquestion_plan->add_child($exploration_plans);

        $exploration_plan = new backup_nested_element('udeha_exploration', ['id'], [
            'title', 'question', 'activitytype', 'temporality', 'location', 'grouping', 'ismarked', 'evaluationtype',
            'length', 'instructions', 'timemodified'
        ]);
        $exploration_plans->add_child($exploration_plan);
        $exploration_plan->set_source_table('udehauthoring_exploration', ['audehsubquestionid' => backup::VAR_PARENTID]);
        $exploration_plan->annotate_files('format_udehauthoring', 'explorationmedia', 'id');

        // Resources backup

        $resource_plans = new backup_nested_element('udeha_resources');
        $subquestion_plan->add_child($resource_plans);

        $resource_plan = new backup_nested_element('udeha_resource', ['id'], [
            'title', 'link', 'timemodified'
        ]);
        $resource_plans->add_child($resource_plan);
        $resource_plan->set_source_table('udehauthoring_resource', ['audehsubquestionid' => backup::VAR_PARENTID]);
        $resource_plan->annotate_files('format_udehauthoring', 'resourcevignette', 'id');

        // Learning objectives of evaluations backup

        $evaluation_objs = new backup_nested_element('udeha_evaluation_objs');
        $course_plan->add_child($evaluation_objs);

        $evaluation_obj = new backup_nested_element('udeha_evaluation_obj', ['id'], [
            'audehevaluationid', 'audehlearningobjectiveid', 'timemodified'
        ]);
        $evaluation_obj->set_source_table('udehauthoring_evaluation_obj', ['audehcourseid' => backup::VAR_PARENTID]);

        // todo exp_tool

        return $plugin;
    }
}
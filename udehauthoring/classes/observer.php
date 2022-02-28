<?php

namespace format_udehauthoring;

class observer
{
    /**
     * Delete all authoring tool records. Order deletion respecting relational integrity.
     *
     * @param \core\event\course_deleted $event
     * @return void
     * @throws \dml_exception
     */
    public static function course_deleted(\core\event\course_deleted $event) {
        global $DB;

        // delete resources
        $DB->execute('DELETE ur
        FROM {udehauthoring_resource} ur
        JOIN {udehauthoring_sub_question} usq ON ur.audehsubquestionid = usq.id
        JOIN {udehauthoring_section} us ON usq.audehsectionid = us.id
        JOIN {udehauthoring_course} uc ON us.audehcourseid = uc.id
        WHERE uc.courseid = ?
        ', [$event->courseid]);

        // delete explorations
        $DB->execute('DELETE ue
        FROM {udehauthoring_exploration} ue
        JOIN {udehauthoring_sub_question} usq ON ue.audehsubquestionid = usq.id
        JOIN {udehauthoring_section} us ON usq.audehsectionid = us.id
        JOIN {udehauthoring_course} uc ON us.audehcourseid = uc.id
        WHERE uc.courseid = ?
        ', [$event->courseid]);

        // delete subquestions
        $DB->execute('DELETE usq
        FROM {udehauthoring_sub_question} usq
        JOIN {udehauthoring_section} us ON usq.audehsectionid = us.id
        JOIN {udehauthoring_course} uc ON us.audehcourseid = uc.id
        WHERE uc.courseid = ?
        ', [$event->courseid]);

        // delete evaluations
        $DB->execute('DELETE ue
        FROM {udehauthoring_evaluation} ue
        JOIN {udehauthoring_course} uc ON ue.audehcourseid = uc.id
        WHERE uc.courseid = ?
        ', [$event->courseid]);

        // delete sections
        $DB->execute('DELETE us
        FROM {udehauthoring_section} us
        JOIN {udehauthoring_course} uc ON us.audehcourseid = uc.id
        WHERE uc.courseid = ?
        ', [$event->courseid]);

        // delete learning objectives
        $DB->execute('DELETE ulo
        FROM {udehauthoring_learning_obj} ulo
        JOIN {udehauthoring_teaching_obj} uto ON ulo.audehteachingobjectiveid = uto.id
        JOIN {udehauthoring_course} uc ON uto.audehcourseid = uc.id
        WHERE uc.courseid = ?
        ', [$event->courseid]);

        // delete teaching objectives
        $DB->execute('DELETE uto
        FROM {udehauthoring_teaching_obj} uto
        JOIN {udehauthoring_course} uc ON uto.audehcourseid = uc.id
        WHERE uc.courseid = ?
        ', [$event->courseid]);

        // delete course plan
        $DB->delete_records('udehauthoring_course', ['courseid' => $event->courseid]);
    }
}
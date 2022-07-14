<?php


namespace format_udehauthoring\model;

use format_udehauthoring\utils;

class evaluationtool_plan
{

    public $id = null;
    public $courseid = null;
    public $audehevaluationid = null;
    public $toolid = null;
    public $tooltype = null;
    public $timemodified = null;

    /**
     * Instantiate an object by querying the database with the exploration_plan ID. An error is raised if no such subquestion
     * plan exists.
     * @param $audehexplorationid
     * @return Int
     * @throws \dml_exception
     */
    public static function get_related_tool_type($audehevaluationid) {
        global $DB;

        $toolrecord = $DB->get_record('udehauthoring_eval_tool', ['audehevaluationid' => $audehevaluationid]);
        $toreturn = 0;

        if($toolrecord) {
            $availableTools = evaluation_plan::get_available_tools();

            switch ($toolrecord->tooltype) {
                case 'assign': {
                    break;
                }
                case 'quiz': {
                    $toreturn = 1;
                    break;
                }
                case 'journal': {
                    $toreturn = 4;
                    break;
                }
                case 'zoom': {
                    $toreturn = 5;
                    break;
                }
                default:
                    $toreturn = array_search(ucfirst($toolrecord->tooltype), $availableTools);
            }
        }
        return $toreturn;
    }

    /**
     * @param {string} type
     */
    protected static function formatActivityType($type) {
        $toReturn = '';
        $availableTools = evaluation_plan::get_available_tools();
        $val = strtolower($availableTools[$type]);
        switch ($val) {
            case 'assignment':
            case 'devoir':
                $toReturn = 'assign';
                break;
            case 'h5p activity':
            case 'activité h5p':
                $toReturn = 'h5pactivity';
                break;
            case 'zoom meeting':
            case 'réunion zoom':
                $toReturn = 'zoom';
                break;
            case 'test':
                $toReturn = 'quiz';
                break;
            case 'diary':
                $toReturn = 'journal';
                break;
            default:
                $toReturn = $val;
        }
        return $toReturn;
    }

    /**
     * Instantiate an object by querying the database with the exploration_plan ID. An error is raised if no such subquestion
     * plan exists.
     * @param $audehexplorationid
     * @return evaluationtool_plan
     * @throws \dml_exception
     */
    public static function instance_by_audehevaluationid($audehevaluationid) {
        global $DB;

        $record = $DB->get_record('udehauthoring_eval_tool', ['audehevaluationid' => $audehevaluationid]);

        if($record) {
            $evaluationtool_plan = new self();
            foreach($evaluationtool_plan as $key => $_) {
                $evaluationtool_plan->$key = $record->$key;
            }

            return $evaluationtool_plan;
        }
        return null;

    }

    /**
     * Instantiate an object by querying the database with the exploration_plan ID. An error is raised if no such subquestion
     * plan exists.
     * @param $audehexplorationid
     * @return Int
     * @throws \dml_exception
     */
    public static function get_related_cmid($audehevaluationid) {
        global $DB;

        $toolrecord = $DB->get_record('udehauthoring_eval_tool', ['audehevaluationid' => $audehevaluationid]);

        if($toolrecord) {
            $moduletypeid = $DB->get_record('modules', ['name' => $toolrecord->tooltype]);
            $cmid = $DB->get_record('course_modules', ['course' => $toolrecord->courseid,
                "module" => $moduletypeid->id,
                "instance" => $toolrecord->toolid]);
            if($cmid) {
                return $cmid->id;
            }

        }
        return null;

    }

    public static function buildToolUrl($toolType, $courseId, $evaluationId, $isGlobal = false) {
        $activityName = self::formatActivityType($toolType);
        if($isGlobal) {
            return '/course/format/udehauthoring/udeh_modedit.php?add=' . $activityName . '&type=&course=' . $courseId .
                '&section=0&return=0&sr=&evaluationid=' . $evaluationId . '&isglobal=1';
        } else {
            return '/course/format/udehauthoring/udeh_modedit.php?add=' . $activityName . '&type=&course=' . $courseId .
                '&section=0&return=0&sr=&evaluationid=' . $evaluationId;
        }

    }

    public function save() {
        global $DB;

        $record = new \stdClass();
        foreach ($this as $key => $value) {
            if (!is_null($value)) {
                $record->$key = $value;
            }
        }

        if (isset($record->id)) {
            utils::db_update_if_changes('udehauthoring_eval_tool', $record);
        } else {
            $record->timemodified = time();
            $this->id = $DB->insert_record('udehauthoring_eval_tool', $record);
        }
    }

    public function delete() {
        global $DB;

        return $DB->delete_records('udehauthoring_eval_tool', ['audehevaluationid' => $this->audehevaluationid]);
    }

}
<?php


namespace format_udehauthoring\model;

use format_udehauthoring\utils;

class explorationtool_plan
{

    public $id = null;
    public $courseid = null;
    public $audehexplorationid = null;
    public $toolid = null;
    public $tooltype = null;
    public $timemodified = null;

    /**
     * Instantiate an object by querying the database with the exploration_plan ID. An error is raised if no such subquestion
     * plan exists.
     * @param $audehexplorationid
     * @return explorationtool_plan
     * @throws \dml_exception
     */
    public static function instance_by_audehexplorationid($audehexplorationid) {
        global $DB;

        $record = $DB->get_record('udehauthoring_exp_tool', ['audehexplorationid' => $audehexplorationid]);

        if($record) {
            $explorationtool_plan = new self();
            foreach($explorationtool_plan as $key => $_) {
                $explorationtool_plan->$key = $record->$key;
            }

            return $explorationtool_plan;
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
    public static function get_related_tool_type($audehexplorationid) {
        global $DB;

        $toolrecord = $DB->get_record('udehauthoring_exp_tool', ['audehexplorationid' => $audehexplorationid]);
        $toreturn = 0;

        if($toolrecord) {
            $availableTools = exploration_plan::get_available_tools();

            switch ($toolrecord->tooltype) {
                case 'assign': {
                    break;
                }
                case 'h5pactivity': {
                    $toreturn = 1;
                    break;
                }
                case 'quiz': {
                    $toreturn = 2;
                    break;
                }
                case 'scorm': {
                    $toreturn = 5;
                    break;
                }
                case 'journal': {
                    $toreturn = 6;
                    break;
                }
                case 'zoom': {
                    $toreturn = 7;
                    break;
                }
                case 'glossary': {
                    $toreturn = 8;
                    break;
                }
                case 'workshop': {
                    $toreturn = 10;
                    break;
                }
                case 'survey': {
                    $toreturn = 12;
                    break;
                }
                case 'lesson': {
                    $toreturn = 13;
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
        $availableTools = exploration_plan::get_available_tools();
        $val = strtolower($availableTools[$type]);
        switch ($val) {
            case 'assignment':
            case 'devoir':
                $toReturn = 'assign';
                break;
            case 'glossaire':
                $toReturn = 'glossary';
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
            case 'paquetage scorm':
            case 'scorm package':
                $toReturn = 'scorm';
                break;
            case 'atelier':
                $toReturn = 'workshop';
                break;
            case 'consultation':
                $toReturn = 'survey';
                break;
            case 'leçon':
                $toReturn = 'lesson';
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
     * @return Int
     * @throws \dml_exception
     */
    public static function get_related_cmid($audehexplorationid) {
        global $DB;

        $toolrecord = $DB->get_record('udehauthoring_exp_tool', ['audehexplorationid' => $audehexplorationid]);

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

    public static function buildToolUrl($toolType, $courseId, $explorationId, $subquestionId) {
        $activityName = self::formatActivityType($toolType);
        return '/course/format/udehauthoring/udeh_modedit.php?add=' . $activityName . '&type=&course=' . $courseId .
            '&section=0&return=0&sr=&subquestionid=' . $subquestionId . '&explorationid=' . $explorationId;
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
            utils::db_update_if_changes('udehauthoring_exp_tool', $record);
        } else {
            $record->timemodified = time();
            $this->id = $DB->insert_record('udehauthoring_exp_tool', $record);
        }
    }

    public function delete() {
        global $DB;

        utils::db_bump_timechanged('udehauthoring_exploration', $this->audehexplorationid);

        return $DB->delete_records('udehauthoring_exp_tool', ['audehexplorationid' => $this->audehexplorationid]);
    }

}
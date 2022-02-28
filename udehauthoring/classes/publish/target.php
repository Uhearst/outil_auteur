<?php

namespace format_udehauthoring\publish;

/**
 * Abstract class for representing a publish target. There are two possible publish targets: preview and official.
 * It has NOT been designed to accomodate future publish formats that could come up in the future.
 */
abstract class target
{
    protected $idnumberprefix;
    protected $idnumbermodulepart = 'M';
    protected $idnumbersubquestionpart = 'T';

    public static function get_target_by_cm($cm) {
        if ($cm->visible) {
            return new target\official();
        } else {
            return new target\preview();
        }
    }

    public function get_idnumberprefix() {
        return $this->idnumberprefix;
    }

    /**
     * @param int $courseid
     * @param int|false $moduleindex
     * @param int|false $subquestionindex
     * @return string
     */
    public function make_cmidnumber($courseid, $moduleindex=false, $subquestionindex=false, $isevaluations=false) {
        $idnumber = $this->idnumberprefix . $courseid;
        if ($isevaluations) {
            return "{$idnumber}E";
        }
        if (false !== $moduleindex && !is_null($moduleindex)) {
            $idnumber .= $this->idnumbermodulepart . $moduleindex;
        }
        if (false !== $subquestionindex && !is_null($subquestionindex)) {
            $idnumber .= $this->idnumbersubquestionpart . $subquestionindex;
        }
        return $idnumber;
    }

    public function unpack_cmidnumber($idnumber) {
        $result = preg_match('/^' . $this->idnumberprefix . '(?P<courseid>\d+)(?P<isevaluations>E)?(M(?P<moduleindex>\d+))?(T(?P<subquestionindex>\d+))?/', $idnumber, $matches);

        if (1 !== $result) {
            return false;
        }

        $data = new \stdClass();

        if (array_key_exists('courseid', $matches)) {
            $data->courseid = intval($matches['courseid']);
        } else {
            $data->courseid = null;
        }

        if (array_key_exists('moduleindex', $matches)) {
            $data->moduleindex = intval($matches['moduleindex']);
        } else {
            $data->moduleindex = null;
        }

        if (array_key_exists('subquestionindex', $matches)) {
            $data->subquestionindex = intval($matches['subquestionindex']);
        } else {
            $data->subquestionindex = null;
        }

        $data->isevaluations = array_key_exists('isevaluations', $matches) && $matches['isevaluations'] === 'E';

        return $data;
    }

    /**
     * @param array $idnumbers
     * @param string $courseid Regular expression fragment
     * @param string $moduleindex Regular expression fragment
     * @param string $subquestionindex Regular expression fragment
     * @return array
     */
    public function filter_cmidnumbers($idnumbers, $courseid, $moduleindex=false, $subquestionindex=false) {
        $regexp = '/^' . $this->idnumberprefix . $courseid;

        if (false !== $moduleindex) {
            $regexp .= $this->idnumbermodulepart . $moduleindex;
        }

        if(false !== $subquestionindex) {
            $regexp .= $this->idnumbersubquestionpart . $subquestionindex;
        }

        $regexp .= '$/';

        return preg_grep($regexp, $idnumbers);
    }

    /**
     * True if this publish target entails rewriting the course information.
     *
     * @return boolean
     */
    abstract public function rewrite_courseinfo();

    /**
     * Visible setting for sections and course modules.
     *
     * @return 0|1
     */
    abstract public function sections_visible();

    /**
     * Section position offset for sections of this publish target.
     *
     * @param $courseid
     * @return mixed
     */
    abstract public function get_sections_offset($courseid);

    /**
     * Get existing sections for a particular target.
     *
     * @param $courseid
     * @return array Indexed from 0, not IDs
     * @throws \dml_exception
     */
    public function get_existing_sections($courseid)
    {
        global $DB;
        return array_values($DB->get_records('course_sections', [
            'course' => $courseid,
            'visible' => $this->sections_visible()
        ], 'section ASC'));
    }
}
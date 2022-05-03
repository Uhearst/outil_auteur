<?php

namespace format_udehauthoring;

use format_udehauthoring\publish\target;

/**
 * Composite pattern class for representing the course menu
 *
 * Each node is course module. Structure is deduced from the idnumbers (see ::build_tree)
 */
class menu_node
{
    public static $TYPE_ROOT = 0;
    public static $TYPE_SYLLABUS = 1;
    public static $TYPE_SYLLABUSPART = 2;
    public static $TYPE_MODULE = 3;
    public static $TYPE_SUBQUESTION = 4;
    public static $TYPE_EVALUATIONSLIST = 5;
    public static $TYPE_EVALUATION = 6;


    public $url;
    public $parent;
    public $courseid;
    public $moduleindex;
    public $subquestionindex;
    public $isevaluations;
    public $children = [];

    private function __construct($url, $parent, $courseid, $moduleindex = null, $subquestionindex = null, $isevaluations = false) {

        $this->url = $url;
        $this->parent = $parent;
        $this->courseid = $courseid;
        $this->moduleindex = $moduleindex;
        $this->subquestionindex = $subquestionindex;
        $this->isevaluations = $isevaluations;

        if (!is_null($this->parent)) {
            $this->parent->children[] = $this;
        }
    }

    /**
     * Builds the menu tree structure based on course module id numbers
     *
     * @param \course_modinfo $course_modinfo
     * @param target $target
     * @return menu_node
     * @throws \moodle_exception
     */
    static function build_tree(\course_modinfo $course_modinfo, target $target) {
        $rooturlparams = ['id' => $course_modinfo->get_course_id()];
        if ($target instanceof target\preview) {
            $rooturlparams['preview'] = 1;
        }

        $root = new self(
            new \moodle_url(
                '/course/view.php',
                $rooturlparams
            ),
            null,
            $course_modinfo->get_course_id()
        );

        $cms = [];
        foreach ($course_modinfo->cms as $cm) {
            $cms[$cm->idnumber] = $cm;
        }
        ksort($cms, SORT_NATURAL);

        // Build first menu level (modules)
        $modules_idnumbers = $target->filter_cmidnumbers(array_keys($cms), $course_modinfo->get_course_id(), '\d+');
        foreach ($modules_idnumbers as $idnumber) {
            $cm = $cms[$idnumber];
            $data = $target->unpack_cmidnumber($idnumber);
            new self(
                (new \moodle_url("/mod/{$cm->modname}/view.php", ['id' => $cm->id]))->out(),
                $root,
                $data->courseid,
                $data->moduleindex
            );
        }

        // Evaluations list page
        $idnumber = $target->make_cmidnumber($course_modinfo->get_course_id(), false, false, true);

        if(isset($cms[$idnumber])) {
            $cm = $cms[$idnumber];
            new self(
                (new \moodle_url("/mod/{$cm->modname}/view.php", ['id' => $cm->id]))->out(),
                $root,
                $data->courseid,
                null, null, true
            );
        }

        // Build second menu level (subquestions)
        $subquestions_idnumbers = $target->filter_cmidnumbers(array_keys($cms), $course_modinfo->get_course_id(), '\d+', '\d+');
        foreach ($subquestions_idnumbers as $idnumber) {
            $cm = $cms[$idnumber];

            $data = $target->unpack_cmidnumber($idnumber);
            $parent = $root->children[$data->moduleindex];

            new self(
                (new \moodle_url("/mod/{$cm->modname}/view.php", ['id' => $cm->id]))->out(),
                $parent,
                $data->courseid,
                $data->moduleindex,
                $data->subquestionindex
            );
        }

        // Evaluations
        $evaluations_idnumbers = $target->filter_cmidnumbers(array_keys($cms), $course_modinfo->get_course_id(), '\d+', false, true);

        foreach ($evaluations_idnumbers as $idnumber) {
            $cm = $cms[$idnumber];

            $data = $target->unpack_cmidnumber($idnumber);
            $parent = $root->children[count($root->children) - 1];

            new self(
                (new \moodle_url("/mod/{$cm->modname}/view.php", ['id' => $cm->id]))->out(),
                $parent,
                $data->courseid,
                $data->moduleindex,
                $data->subquestionindex,
                $data->isevaluations
            );
        }

        return $root;
    }

    /**
     * Search through this node’s descendants for a node with $idnumber.
     * Result may be $this as well.
     *
     * @param $idnumber
     * @return menu_node|null
     */
    public function find($courseid, $moduleindex, $subquestionindex, $isevaluations) {
        if ($this->courseid === $courseid && $this->moduleindex === $moduleindex && $this->subquestionindex === $subquestionindex && $this->isevaluations === $isevaluations) {
            return $this;
        }

        foreach ($this->children as $child) {
            $result = $child->find($courseid, $moduleindex, $subquestionindex, $isevaluations);
            if (!is_null($result)) {
                return $result;
            }
        }

        return null;
    }

    /**
     * Returns all children of this node’s parent, including $this.
     *
     * @return array
     */
    public function siblings() {
        $siblings = [];
        if (is_null($this->parent)) {
            return $siblings;
        }

        return $this->parent->children;
    }

    /**
     * Returns this node’s type. May be any of self::$TYPE_*
     * Type is deduced from the idnumber. Used for custom rendering of menu nodes.
     *
     * @return int
     */
    public function type() {
        if (is_null($this->parent)) {
            return self::$TYPE_ROOT;

        } else if($this->isevaluations) {
            return is_null($this->moduleindex) ?
                self::$TYPE_EVALUATIONSLIST :
                self::$TYPE_EVALUATION ;

        } else if(is_null($this->parent->parent)) {
            if (0 === $this->moduleindex) {
                return self::$TYPE_SYLLABUS;
            }

            return self::$TYPE_MODULE;
        }

        if(0 === $this->moduleindex) {
            return self::$TYPE_SYLLABUSPART;
        }

        return self::$TYPE_SUBQUESTION;
    }
}
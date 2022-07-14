<?php

namespace format_udehauthoring\publish\target;
/**
 * Publish target for the course preview. When passed to \format_udehauthoring\publish\structure and
 * \format_udehauthoring\publish\content, hidden course content intended as preview is changed.
 */
class preview extends \format_udehauthoring\publish\target
{
    protected $idnumberprefix = 'P';

    public function rewrite_courseinfo()
    {
        return false;
    }

    public function sections_visible() {
        return 0;
    }

    public function get_sections_offset($courseid)
    {
        global $DB;
        return $DB->count_records('course_sections', [
            'course' => $courseid,
            'visible' => 1
        ]);
    }
}
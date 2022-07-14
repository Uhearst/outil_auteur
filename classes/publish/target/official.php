<?php

namespace format_udehauthoring\publish\target;

/**
 * Publish target for the official course. When passed to \format_udehauthoring\publish\structure and
 * \format_udehauthoring\publish\content, the actual course content is changed.
 */
class official extends \format_udehauthoring\publish\target
{
    protected $idnumberprefix = 'C';

    public function rewrite_courseinfo()
    {
        return true;
    }

    public function sections_visible() {
        return 1;
    }

    public function get_sections_offset($courseid)
    {
        return 1;
    }

    public function get_existing_sections($courseid) {
        $sections = parent::get_existing_sections($courseid);

        return array_values( array_filter($sections, function($section) {
            return 0 != $section->section;
        }) );
    }
}
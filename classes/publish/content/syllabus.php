<?php

namespace format_udehauthoring\publish\content;

use DOMDocument;
use format_udehauthoring\model\course_plan;
use Dompdf\Dompdf;
use format_udehauthoring\model\unit_config;
use format_udehauthoring\model\unit_plan;

require_once $CFG->dirroot . '/course/format/udehauthoring/dompdf/autoload.inc.php';

/**
 * Produces the syllabus in HTML and PDF format.
 */
class syllabus
{
    private $courseplan;
    private $content;

    public function __construct($courseplan) {
        $this->courseplan = $courseplan;
        $this->content = new \stdClass();
    }

    public function get_presentation_content_syllabus() {
        $units = unit_plan::instance_all_by_course_plan_id($this->courseplan->id);

        $unit_names = [];

        foreach($units as $unit) {
            $unit_names[] = unit_config::getValueById($unit->audehunitid);
        }

        ob_start();
        ?>
        <br />
        <br />
        <div class="udeha-syllabus-header">
            <div><?php echo $this->courseplan->code; ?></div><div class="h1title"><?php echo $this->courseplan->title; ?></div>
            <br />
            <br />
            <div><?php if(count($unit_names) > 0) echo implode(', ', $unit_names); ?></div><div class="bloc"><?php echo get_string('syllabusblock', 'format_udehauthoring'); echo ' ' . ++$this->courseplan->bloc; ?></div>
        </div>
        <br />
        <br />
        <div class="udeha-teacher-info">
            <div class="info">
                <div class="info-section"><?php print_string('syllabusteachername', 'format_udehauthoring') ?></div>
                <div class="info-data"><?php echo $this->courseplan->teachername; ?></div>
            </div>
            <div class="info">
                <div class="info-section"><?php print_string('syllabusemail', 'format_udehauthoring') ?></div>
                <div class="info-data"><a href="mailto:<?php echo trim($this->courseplan->teacheremail); ?>"><?php echo trim($this->courseplan->teacheremail); ?></a></div>
            </div>
            <?php if(!empty($this->courseplan->teacherphone)): ?>
                <div class="info">
                    <div class="info-section"><?php print_string('syllabusphone', 'format_udehauthoring') ?></div>
                    <div class="info-data"><?php echo $this->courseplan->teacherphone; ?></div>
                </div>
            <?php endif; ?>
            <?php if (!empty($this->courseplan->teachercellphone)): ?>
                <div class="info">
                    <div class="info-section"><?php print_string('syllabusmobile', 'format_udehauthoring') ?></div>
                    <div class="info-data"><?php echo $this->courseplan->teachercellphone; ?></div>
                </div>
            <?php endif; ?>
            <?php if (!empty($this->courseplan->teachercontacthours)): ?>
                <div class="info">
                    <div class="info-section"><?php print_string('syllabuscontact', 'format_udehauthoring') ?></div>
                    <div class="info-data"><?php echo $this->courseplan->teachercontacthours; ?></div>
                </div>
            <?php endif; ?>
            <?php if (!empty($this->courseplan->teacherzoomlink)): ?>
                <div class="info">
                    <div class="info-section"><?php print_string('syllabuszoomteacher', 'format_udehauthoring') ?></div>
                    <div class="info-data"><a href="<?php echo $this->courseplan->teacherzoomlink; ?>"><?php echo $this->courseplan->teacherzoomlink; ?></a></div>
                </div>
            <?php endif; ?>
            <?php if (!empty($this->courseplan->coursezoomlink)): ?>
                <div class="info">
                    <div class="info-section"><?php print_string('syllabuszoomcourse', 'format_udehauthoring') ?></div>
                    <div class="info-data"><a href="<?php echo $this->courseplan->coursezoomlink; ?>"><?php echo $this->courseplan->coursezoomlink; ?></a></div>
                </div>
            <?php endif; ?>
        </div>
        <?php
            $this->content->presentation = ob_get_clean();

        return $this->content->presentation;
    }

    public function get_presentation_content() {
        $units = unit_plan::instance_all_by_course_plan_id($this->courseplan->id);

        $unit_names = [];
        foreach($units as $unit) {
            $unit_names[] = unit_config::getValueById($unit->audehunitid);
        }

        ob_start();
        ?>
        <br />
        <br />
        <div class="udeha-presentation">
            <div class="presentation">
                <div class="presentation-section"><?php print_string('syllabustitle', 'format_udehauthoring') ?></div>
                <div class="presentation-data"><?php echo $this->courseplan->title; ?></div>
            </div>
            <div class="presentation">
                <div class="presentation-section"><?php print_string('syllabuscote', 'format_udehauthoring') ?></div>
                <div class="presentation-data"><?php echo $this->courseplan->code; ?></div>
            </div>
            <div class="presentation">
                <div class="presentation-section"><?php print_string('syllabusblock', 'format_udehauthoring') ?></div>
                <div class="presentation-data"><?php echo ++$this->courseplan->bloc; ?></div>
            </div>
            <div class="presentation">
                <div class="presentation-section"><?php print_string('syllabuscredit', 'format_udehauthoring') ?></div>
                <div class="presentation-data"><?php echo $this->courseplan->credit; ?></div>
            </div>
            <?php if(count($unit_names) > 0) { ?>
                <div class="presentation">
                    <div class="presentation-section"><?php print_string('syllabusunit', 'format_udehauthoring') ?></div>
                    <div class="presentation-data"><?php echo implode(', ', $unit_names); ?></div>
                </div>
            <?php } ?>
            <div class="presentation">
                <div class="presentation-section"><?php print_string('syllabusteachername', 'format_udehauthoring') ?></div>
                <div class="presentation-data"><?php echo $this->courseplan->teachername; ?></div>
            </div>
            <div class="presentation">
                <div class="presentation-section"><?php print_string('syllabusemail', 'format_udehauthoring') ?></div>
                <div class="presentation-data"><a href="mailto:<?php echo trim($this->courseplan->teacheremail); ?>"><?php echo trim($this->courseplan->teacheremail); ?></a></div>
            </div>
            <?php if (!empty($this->courseplan->teachercontacthours)) { ?>
                <div class="presentation">
                    <div class="presentation-section"><?php print_string('syllabuscontact', 'format_udehauthoring') ?></div>
                    <div class="presentation-data"><?php echo $this->courseplan->teachercontacthours; ?></div>
                </div>
            <?php } ?>
            <?php if (!empty($this->courseplan->teacherzoomlink)) { ?>
                <div class="presentation">
                    <div class="presentation-section"><?php print_string('syllabuszoomteacher', 'format_udehauthoring') ?></div>
                    <div class="presentation-data"><a href="<?php echo $this->courseplan->teacherzoomlink; ?>"><?php echo $this->courseplan->teacherzoomlink; ?></a></div>
                </div>
            <?php } ?>
            <?php if (!empty($this->courseplan->coursezoomlink)) { ?>
                <div class="presentation">
                    <div class="presentation-section"><?php print_string('syllabuszoomcourse', 'format_udehauthoring') ?></div>
                    <div class="presentation-data"><a href="<?php echo $this->courseplan->coursezoomlink; ?>"><?php echo $this->courseplan->coursezoomlink; ?></a></div>
                </div>
            <?php } ?>
        </div>
        <?php
            $this->content->presentation = ob_get_clean();

        return $this->content->presentation;
    }

    public function prepare_files_for_pdf($value, $field, $fileArea) {
        global $CFG;
        $fs = get_file_storage();
        $chroot = [$CFG->dirroot . '/course/format/udehauthoring/'];
        $foundFile = null;
        if (str_contains($value->{$field}, '<img')) {
            $context = \context_course::instance($this->courseplan->courseid);
            $files = $fs->get_area_files(
                $context->id,
                'format_udehauthoring',
                $fileArea
            );
        } else {
            return self::cleanEditorContentForCoursePlan($value->{$field});
        }
        if (isset($files)) {
            foreach ($files as $file) {
                if ($file->get_filename() === '.') {
                    continue;
                }
                $foundFile = $file;
            }
        }

        if (!is_null($foundFile)) {
            $rawpath = $foundFile->copy_content_to_temp('udeh', $fileArea);
            $path = $rawpath . '.' . pathinfo($foundFile->get_filename())['extension'];
            rename($rawpath, $path);
            if (!in_array(pathinfo($path)['dirname'], $chroot)) {
                $chroot[] = pathinfo($path)['dirname'];
            }

            $str = $value->{$field};
            $start = 'src="';
            $end = '"';

            $startPos = strpos($str, $start);
            if ($startPos === false) { return $str; }
            $endPos = strpos($str, $end, $startPos + strlen($start));

            if ($endPos === false) { return $str; }

            $length = $endPos - ($startPos + strlen($start));

            return substr_replace(
                $str,
                $path,
                $startPos + strlen($start),
                $length + strlen($end) - 1
            );
        }
        return $value->{$field};
    }

    public function get_desc_content() {
        $this->content->desc = file_rewrite_pluginfile_urls(
            $this->courseplan->description,
            'pluginfile.php',
            \context_course::instance($this->courseplan->courseid)->id,
            'format_udehauthoring',
            'course_description',
            0
        );

        return $this->content->desc;
    }

    public function get_objectives_content() {
        $lblteachingobjective  = get_string('teachingobjective', 'format_udehauthoring');
        $lbllearningobjectives = get_string('learningobjectivestarget', 'format_udehauthoring');
        $bytheend = get_string('bytheendstudentable', 'format_udehauthoring');

        $content = '';
        foreach($this->courseplan->teachingobjectives as $ii => $teachingobjective) {
            if (0 !== $ii) {
                $content .= '<hr>';
            }
            $index = $ii + 1;
            $totitle = file_rewrite_pluginfile_urls(
                $teachingobjective->teachingobjective,
                'pluginfile.php',
                \context_course::instance($this->courseplan->courseid)->id,
                'format_udehauthoring',
                'course_teachingobjective_' . $teachingobjective->id,
                0
            );

            $content .= "<h4>{$lblteachingobjective} {$index}</h4>";
            $content .= "<p>{$totitle}</p>";
            $content .= "<div class='udeha-syllabus-lo'>";
            $content .= "<h5>{$lbllearningobjectives}</h5>";
            $content .= "<p>{$bytheend}</p>";
            $content .= "<ol>";

            foreach ($teachingobjective->learningobjectives as $learningobjective) {
                $lotitle = file_rewrite_pluginfile_urls(
                    $learningobjective->learningobjective,
                    'pluginfile.php',
                    \context_course::instance($this->courseplan->courseid)->id,
                    'format_udehauthoring',
                    'course_learningobjective_' . $learningobjective->id,
                    0
                );
                $content .= "<li>{$lotitle}</li>";
            }

            $content .= "</ol>";
            $content .= "</div>";
        }

        $this->content->objectives = $content;

        return $this->content->objectives;
    }

    public function get_objectives_content_syllabus($needFormatting = false) {
        $lblteachingobjective  = get_string('teachingobjective', 'format_udehauthoring');
        $lbllearningobjectives = get_string('learningobjectivestarget', 'format_udehauthoring');
        $bytheend = get_string('bytheendstudentable', 'format_udehauthoring');

        $content = '';

        foreach($this->courseplan->teachingobjectives as $ii => $teachingobjective) {
            if (0 !== $ii) {
                $content .= '<div class="brsep"></div>';
            }

            $index = $ii + 1;
            $totitle = file_rewrite_pluginfile_urls(
                $teachingobjective->teachingobjective,
                'pluginfile.php',
                \context_course::instance($this->courseplan->courseid)->id,
                'format_udehauthoring',
                'course_teachingobjective_' . $teachingobjective->id,
                0
            );
            if ($needFormatting) {
                $totitle = $this->prepare_files_for_pdf(
                    $teachingobjective,
                    'teachingobjective',
                    'course_teachingobjective_' . $teachingobjective->id
                );
            }

            $content .= "<table class='inner'><tr>
                            <td class='col-title'>{$lblteachingobjective} {$index}</td>
                        </tr>";
            $content .= "<tr><td>";
            $content .= "{$totitle}";
            $content .= "<div class='udeha-syllabus-lo'>";
            $content .= "<h3>{$lbllearningobjectives}</h3>";
            $content .= "<p>{$bytheend}</p>";
            $content .= "<ul>";

            foreach($teachingobjective->learningobjectives as $learningobjective) {
                $lotitle = file_rewrite_pluginfile_urls(
                    $learningobjective->learningobjective,
                    'pluginfile.php',
                    \context_course::instance($this->courseplan->courseid)->id,
                    'format_udehauthoring',
                    'course_learningobjective_' . $learningobjective->id,
                    0
                );
                if ($needFormatting) {
                    $lotitle = $this->prepare_files_for_pdf(
                        $learningobjective,
                        'learningobjective',
                        'course_learningobjective_' . $learningobjective->id
                    );
                }
                $content .= "<li>{$lotitle}</li>";
            }

            $content .= "</ul>";
            $content .= "</div>";
            $content .= "</td></tr></table>";
        }

        $this->content->objectives = $content;

        return $this->content->objectives;
    }

    public function get_problematic_content() {
        $this->content->problematic = file_rewrite_pluginfile_urls(
            $this->courseplan->problematic,
            'pluginfile.php',
            \context_course::instance($this->courseplan->courseid)->id,
            'format_udehauthoring',
            'course_problematic',
            0
        );

        return $this->content->problematic;
    }

    public function get_place_content() {

        $context_course = \context_course::instance($this->courseplan->courseid);
        $placeContent = file_rewrite_pluginfile_urls(
            $this->courseplan->place,
            'pluginfile.php',
            $context_course->id,
            'format_udehauthoring',
            'course_place',
            0
        );
        $this->content->place = $placeContent;

        return $this->content->place;
    }

    public function get_method_content() {
        $this->content->method = file_rewrite_pluginfile_urls(
            $this->courseplan->method,
            'pluginfile.php',
            \context_course::instance($this->courseplan->courseid)->id,
            'format_udehauthoring',
            'course_method',
            0
        );

        return $this->content->method;
    }

    public function get_modules_content($needFormatting = false) {
        global $DB;
        $sectionslist = '';
        foreach ($this->courseplan->sections as $ii => $section) {
            if (intval($section->isvisible) === 1) {

                $values = [];
                $editors = ['title', 'description'];
                foreach ($editors as $editor) {
                    $values[$editor] = file_rewrite_pluginfile_urls(
                        $section->{$editor},
                        'pluginfile.php',
                        \context_course::instance($this->courseplan->courseid)->id,
                        'format_udehauthoring',
                        'course_section_' . $editor . '_' . $section->id,
                        0
                    );
                }

                $sectionevaluations = array_filter($this->courseplan->evaluations, function($evaluation) use ($section) {
                    return in_array($section->id, array_keys($evaluation->audehsectionids));
                });

                if (!empty($sectionevaluations)) {
                    $evaluation = current($sectionevaluations);
                }

                if (!isset($evaluation) || !isset($evaluation->title)) {
                    $evaluation = new \stdClass();
                    $evaluation->title = '';
                }
                $summativeEval = get_string('summativeevaluation', 'format_udehauthoring');

                if ($needFormatting) {
                    foreach ($editors as $area) {
                        $values[$area] = $this->prepare_files_for_pdf(
                            $section,
                            $area,
                            'course_section_' . $area . '_' . $section->id
                        );
                    }
                }

                $moduleLabel = $DB->get_record('udehauthoring_title', ['id' => $this->courseplan->id])->module;
                $prefix = $moduleLabel ?: get_string('section', 'format_udehauthoring');
                $values['title'] = $prefix . ' ' . ($ii + 1) . ' : ' . $values['title'];

                if($evaluation->title !== '') {
                    $evalTitle = file_rewrite_pluginfile_urls(
                        $evaluation->title,
                        'pluginfile.php',
                        \context_course::instance($this->courseplan->courseid)->id,
                        'format_udehauthoring',
                        'course_evaluation_title_' . $evaluation->id,
                        0
                    );
                } else {
                    $evalTitle = $evaluation->title;
                }

                $sectionslist .= <<<EOD
                    <table class="table">
                        <tr>
                            <td class="col-title">{$values['title']}</td>
                            <td class="col-grading">{$summativeEval}</td>
                        </tr>
                        <tr>
                            <td class="col-title">{$values['description']}</td>
                            <td class="col-grading">{$evalTitle}</td>
                        </tr>
                    </table>
                EOD;
            }
        }

        $this->content->modules = $sectionslist;

        return $this->content->modules;
    }

    public function get_evaluations_content($needFormatting = false) {
        if (0 === count($this->courseplan->evaluations)) {
            $this->content->evaluations = '';
        } else {
            $evaluationslist = '';

            foreach($this->courseplan->evaluations as $evaluation) {

                $title = file_rewrite_pluginfile_urls(
                    $evaluation->title,
                    'pluginfile.php',
                    \context_course::instance($this->courseplan->courseid)->id,
                    'format_udehauthoring',
                    'course_evaluation_title_' . $evaluation->id,
                    0
                );

                if ($needFormatting) {
                    $title = $this->prepare_files_for_pdf(
                        $evaluation,
                        'title',
                        'course_evaluation_title_' . $evaluation->id
                    );
                }


                $evaluationslist = <<<EOD
                {$evaluationslist}
                <tr>
                    <td class="col-title">{$title}</td>
                    <td class="col-grading">{$evaluation->weight}%</td>
                </tr>
            EOD;
            }

            $evaluationsintro = get_string('evaluationsintro', 'format_udehauthoring');
            $evalgrading = get_string('evalgrading', 'format_udehauthoring');
            $evalworks = get_string('evalworks', 'format_udehauthoring');
            $this->content->evaluations =
                <<<EOD
                <div class="info-data full">
                    <p>{$evaluationsintro}</p>
                    <br>
                    <h4>{$evalgrading}</h4>
                    <p>{$evalworks}</p>
                </div>
                <table class="table">
                    <tr>
                        <td class="col-title">Tâches à effectuer</td>
                        <td class="col-grading">Pondération</td>
                    </tr>
                    {$evaluationslist}
                </table>
                EOD;
        }

        return $this->content->evaluations;
    }

    public function get_extra_content($needFormatting = false) {
        if (is_array($this->courseplan->additionalinformation) && count($this->courseplan->additionalinformation) > 0) {
            $str = '';
            foreach ($this->courseplan->additionalinformation as $additionalinformation) {
                $content = file_rewrite_pluginfile_urls(
                    $additionalinformation->content,
                    'pluginfile.php',
                    \context_course::instance($this->courseplan->courseid)->id,
                    'format_udehauthoring',
                    'course_additional_info_content_' . $additionalinformation->id,
                    0
                );

                if ($needFormatting === true) {
                    $content = $this->prepare_files_for_pdf(
                        $additionalinformation,
                        'content',
                        'course_additional_info_content_' . $additionalinformation->id
                    );
                }

                $str .=
                    <<<EOD
                    <h4>{$additionalinformation->title}</h4>
                    {$content}
                EOD;
            }
            $this->content->extra = $str;
        } else {
            $this->content->extra = '';
        }

        return $this->content->extra;
    }

    public function get_pdf_filename() {
        return get_string('courseplan', 'format_udehauthoring') . ' ' .
            substr($this->courseplan->title, 0, 100) . ' ' .
            substr($this->courseplan->teachername, 0, 75) . ' ' .
            date('Y', $this->courseplan->timemodified) .
            ".pdf";
    }

    public function get_pdf_content() {
        global $CFG, $DB;

        $fs = get_file_storage();
        $chroot = [ $CFG->dirroot . '/course/format/udehauthoring/' ];
        $fileAreas = ['syllabusheaderlogo', 'description', 'problematic', 'place', 'method'];
        $formattedValues = [];
        foreach ($fileAreas as $area) {
            $foundFile = null;
            $files = null;
            if ($area === 'syllabusheaderlogo') {
                $files = $fs->get_area_files(\context_system::instance()->id, 'core', 'syllabusheaderlogo');
            } elseif (str_contains($this->courseplan->{$area}, '<img')) {
                $context = \context_course::instance($this->courseplan->courseid);
                $files = $fs->get_area_files($context->id, 'format_udehauthoring', 'course_'.$area);
            } else {
                $formattedValues[$area] = self::cleanEditorContentForCoursePlan($this->courseplan->{$area});
            }
            if (isset($files)) {
                foreach ($files as $file) {
                    if ($file->get_filename() === '.' || !str_contains($file->get_mimetype(), 'image')) {
                        continue;
                    }
                    $foundFile = $file;
                }
            }

            if (!is_null($foundFile)) {
                $rawpath = $foundFile->copy_content_to_temp('udeh', $area === 'syllabusheaderlogo' ? 'logo' : $area);
                $path = $rawpath . '.' . pathinfo($foundFile->get_filename())['extension'];
                rename($rawpath, $path);
                if (!in_array(pathinfo($path)['dirname'], $chroot)) {
                    $chroot[] = pathinfo($path)['dirname'];
                }

                if ($area === 'syllabusheaderlogo') {
                    $formattedValues['logo'] = $path;
                } else {
                    $str = $this->courseplan->{$area};
                    $start = 'src="';
                    $end = '"';

                    $startPos = strpos($str, $start);
                    if ($startPos === false) { continue; }
                    $endPos = strpos($str, $end, $startPos + strlen($start));

                    if ($endPos === false) { continue; }

                    $length = $endPos - ($startPos + strlen($start));

                    $formattedValues[$area] =
                        substr_replace(
                            $str,
                            $path,
                            $startPos + strlen($start),
                            $length + strlen($end) - 1
                        );
                }
            }
        }

        $options = new \Dompdf\Options();
        $options->setChroot($chroot);
        $options->set('defaultFont', 'Poppins');
        $dompdf = new Dompdf($options);


        ob_start();
        require($CFG->dirroot . '/course/format/udehauthoring/pdf-template.php');
        $html = ob_get_clean();

        $dompdf->setBasePath($CFG->dirroot . '/course/format/udehauthoring/');
        $dompdf->loadHtml($html);
        $dompdf->render();

        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($html);
        libxml_use_internal_errors(false);

        $imgTags = $doc->getElementsByTagName('img');

        foreach ($imgTags as $imgTag) {
            $srcValues[] = $imgTag->getAttribute('src');
        }

        foreach ($srcValues as $value) {
            if(str_contains($value, 'temp')) {
                unlink($value);
            }
        }

        return $dompdf->output();
    }

    public static function cleanEditorContentForCoursePlan($value) {
        return match (true) {
            str_contains($value, 'h5p') => self::removeTagAndContent($value, '', 'h5p-placeholder'),
            str_contains($value, 'video') => self::removeTagAndContent($value, 'video'),
            str_contains($value, 'audio') => self::removeTagAndContent($value, 'audio'),
            default => $value,
        };
    }

    private static function removeTagAndContent($html, $tagName = '', $class = '') {
        // Construct the regular expression pattern to match the specified tag and its content
        if($class !== '') {
            $pattern = "/<div\s+class=[\"']{$class}[\"'].*?<\/div>/si";
        } else {
            $pattern = "/<{$tagName}.*?<\/{$tagName}>/si";
        }

        // Use preg_replace to remove the tag and its content from the HTML string
        $html = preg_replace($pattern, '', $html);

        return $html;
    }

    /**
     * For debugging purposes only
     *
     * @return false|string
     */
    public function get_html_content() {
        global $CFG, $DB;

        // get header logo
        $fs = get_file_storage();
        $files = $fs->get_area_files(\context_system::instance()->id, 'core', 'syllabusheaderlogo');

        $logofile = null;
        foreach ($files as $file) {
            if ($file->get_filename() === '.') {
                continue;
            }
            $logofile = $file;
        }

        $chroot = [ $CFG->dirroot . '/course/format/udehauthoring/' ];

        if (!is_null($logofile)) {
            $rawlogopath = $logofile->copy_content_to_temp('udeh', 'logo');
            $logopath = $rawlogopath . '.' . pathinfo($logofile->get_filename())['extension'];
            rename($rawlogopath, $logopath);
            $chroot[] = pathinfo($logopath)['dirname'];
        } else {
            $logopath = null;
        }

        ob_start();
        require($CFG->dirroot . '/course/format/udehauthoring/pdf-template.php');
        return ob_get_clean();
    }

}
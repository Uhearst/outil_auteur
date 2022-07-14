<?php

namespace format_udehauthoring\publish\content;

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

    public function get_presentation_content() {
        if (!isset($this->content->presentation)) {
            $units = unit_plan::instance_all_by_course_plan_id($this->courseplan->id);

            $loopResult = "";
            foreach($units as $unit) {
                $loopResult .= unit_config::getValueById($unit->audehunitid);
            }

            ob_start();
            ?>
            <hr>
            <div class="udeha-syllabus-header">
                <div><?php echo $this->courseplan->code; ?></div>
                <h1><?php echo $this->courseplan->title; ?></h1>
                <div>
                    <?php echo $loopResult; ?><br>
                    <?php print_string('syllabusblock', 'format_udehauthoring', $this->courseplan->bloc+1); ?>
                </div>
            </div>
            <hr>
            <div class="udeha-teacher-info">

                <div>
                    <span><?php print_string('syllabusteachername', 'format_udehauthoring') ?></span>
                    <?php echo $this->courseplan->teachername; ?>
                </div>

                <div>
                    <span><?php print_string('syllabusemail', 'format_udehauthoring'); ?></span>
                    <a href="mailto:<?php echo $this->courseplan->teacheremail; ?>"><?php echo $this->courseplan->teacheremail; ?></a>
                </div>

                <?php if(!empty($this->courseplan->teacherphone)): ?>
                    <div>
                        <span><?php print_string('syllabusphone', 'format_udehauthoring'); ?></span>
                        <?php echo $this->courseplan->teacherphone; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($this->courseplan->teachercellphone)): ?>
                    <div>
                        <span><?php print_string('syllabusmobile', 'format_udehauthoring'); ?></span>
                        <?php echo $this->courseplan->teachercellphone; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($this->courseplan->teachercontacthours)): ?>
                    <div>
                        <span><?php print_string('syllabuscontact', 'format_udehauthoring'); ?></span>
                        <?php echo $this->courseplan->teachercontacthours; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($this->courseplan->teacherzoomlink)): ?>
                    <div>
                        <span><?php print_string('syllabuszoomteacher', 'format_udehauthoring'); ?></span>
                        <a href="<?php echo $this->courseplan->teacherzoomlink; ?>"><?php echo $this->courseplan->teacherzoomlink; ?></a>
                    </div>
                <?php endif; ?>

                <?php if (!empty($this->courseplan->coursezoomlink)): ?>
                    <div>
                        <span><?php print_string('syllabuszoomcourse', 'format_udehauthoring'); ?></span>
                        <a href="<?php echo $this->courseplan->coursezoomlink; ?>"><?php echo $this->courseplan->coursezoomlink; ?></a>
                    </div>
                <?php endif; ?>
            </div>
            <?php
            $this->content->presentation = ob_get_clean();
        }

        return $this->content->presentation;
    }

    public function get_desc_content() {
        if (!isset($this->content->desc)) {
            $this->content->desc = $this->courseplan->description;
        }
        return $this->content->desc;
    }

    public function get_objectives_content() {
        if (!isset($this->content->objectives)) {
            $lblteachingobjective  = get_string('teachingobjective', 'format_udehauthoring');
            $lbllearningobjectives = get_string('learningobjectivestarget', 'format_udehauthoring');
            $bytheend = get_string('bytheendstudentable', 'format_udehauthoring');

            $content = '';
            foreach($this->courseplan->teachingobjectives as $ii => $teachingobjective) {
                if (0 !== $ii) {
                    $content .= '<hr>';
                }
                $index = $ii + 1;
                $totitle = strip_tags($teachingobjective->teachingobjective, '<strong><em><sup><sub>');


                $content .= "<h4>{$lblteachingobjective} {$index}</h4>";
                $content .= "<p>{$totitle}</p>";
                $content .= "<div class='udeha-syllabus-lo'>";
                $content .= "<h5>{$lbllearningobjectives}</h5>";
                $content .= "<p>{$bytheend}</p>";
                $content .= "<ol>";
                foreach($teachingobjective->learningobjectives as $learningobjective) {
                    $lotitle = strip_tags($learningobjective->learningobjective, '<strong><em><sup><sub>');
                    $content .= "<li>{$lotitle}</li>";
                }
                $content .= "</ol>";
                $content .= "</div>";
            }

            $this->content->objectives = $content;
        }

        return $this->content->objectives;
    }

    public function get_problematic_content() {
        if (!isset($this->content->problematic)) {
            $this->content->problematic = $this->courseplan->problematic;
        }

        return $this->content->problematic;
    }

    public function get_place_content() {
        if (!isset($this->content->place)) {
            $this->content->place = $this->courseplan->place;
        }

        return $this->content->place;
    }

    public function get_method_content() {
        if (!isset($this->content->method)) {
            $this->content->method = $this->courseplan->method;
        }

        return $this->content->method;
    }

    public function get_modules_content() {

        if (!isset($this->content->modules)) {
            $sectionslist = '';
            foreach($this->courseplan->sections as $ii => $section) {
                if (0 !== $ii) {
                    $sectionslist .= '<hr>';
                }
                $sectiontitle = get_string('titlemodule', 'format_udehauthoring', (object)[
                    'index' => $ii + 1,
                    'title' => strip_tags($section->title, '<strong><em><sup><sub>')
                ]);
                $sectionslist .= <<<EOD
                    <h4>{$sectiontitle}</h4>
                    <div>{$section->description}</div>
                EOD;
                $sectionevaluations = array_filter($this->courseplan->evaluations, function($evaluation) use ($section) {
                    return $evaluation->audehsectionid == $section->id;
                });
                if (!empty($sectionevaluations)) {
                    $evaluation = current($sectionevaluations);
                    $evaluationtitle = get_string('titleevaluationsection', 'format_udehauthoring', $ii + 1);
                    $sectionslist .= <<<EOD
                        <h5 class="udeha-title-section-evaluation">{$evaluationtitle}</h5>
                        <div>{$evaluation->title}</div>
                    EOD;
                }
            }

            $this->content->modules = $sectionslist;
        }

        return $this->content->modules;
    }

    public function get_evaluations_content() {
        if (!isset($this->content->evaluations)) {
            if (0 === count($this->courseplan->evaluations)) {
                $this->content->evaluations = '';
            } else {

                $evaluationslist = '';
                foreach($this->courseplan->evaluations as $evaluation) {
                    $evaluationslist = <<<EOD
                    {$evaluationslist}
                    <tr>
                        <td class="col-title">{$evaluation->title}</td>
                        <td class="col-grading">{$evaluation->weight}%</td>
                    </tr>
                EOD;
                }
                $evaluationsintro = get_string('evaluationsintro', 'format_udehauthoring');
                $evalgrading = get_string('evalgrading', 'format_udehauthoring');
                $evalworks = get_string('evalworks', 'format_udehauthoring');
                $this->content->evaluations =
                    <<<EOD
                    <p>{$evaluationsintro}</p>
                    <br>
                    <h4>{$evalgrading}</h4>
                    <p>{$evalworks}</p>
                    <table class="table">
                        {$evaluationslist}
                    </table>
                EOD;
            }
        }

        return $this->content->evaluations;
    }

    public function get_extra_content() {
        if (!isset($this->content->extra)) {
            $this->content->extra = $this->courseplan->annex;
        }

        return $this->content->extra;
    }

    public function get_pdf_filename() {
        return get_string('courseplan', 'format_udehauthoring') . ' ' .
            $this->courseplan->title . ' ' .
            $this->courseplan->teachername . ' ' .
            date('Y', $this->courseplan->timemodified) .
            ".pdf";
    }

    public function get_pdf_content() {
        global $CFG;

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

        $options = new \Dompdf\Options();
        $options->setChroot($chroot);
        $dompdf = new Dompdf($options);


        ob_start();
        require($CFG->dirroot . '/course/format/udehauthoring/pdf-template.php');
        $html = ob_get_clean();

        $dompdf->setBasePath($CFG->dirroot . '/course/format/udehauthoring/');
        $dompdf->loadHtml($html);
        $dompdf->render();

        if (!is_null($logopath)) {
            unlink($logopath);
        }

        return $dompdf->output();
    }

    /**
     * For debugging purposes only
     *
     * @return false|string
     */
    public function get_html_content() {
        global $CFG;

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
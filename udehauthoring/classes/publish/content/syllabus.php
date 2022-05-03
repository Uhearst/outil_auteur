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

            $strblock = get_string('syllabusblock', 'format_udehauthoring', $this->courseplan->bloc);

            $lblteachername = get_string('syllabusteachername', 'format_udehauthoring');
            $lblemail       = get_string('syllabusemail', 'format_udehauthoring');
            $lblphone       = get_string('syllabusphone', 'format_udehauthoring');
            $lblmobile      = get_string('syllabusmobile', 'format_udehauthoring');
            $lblcontact     = get_string('syllabuscontact', 'format_udehauthoring');
            $lblzoomteacher = get_string('syllabuszoomteacher', 'format_udehauthoring');
            $lblzoomcourse  = get_string('syllabuszoomcourse', 'format_udehauthoring');

            $loopResult = "";
            foreach($units as $unit) {
                $loopResult .= unit_config::getValueById($unit->audehunitid);
            }

            $this->content->presentation = <<<EOD
                <hr>
                <div class="udeha-syllabus-header">
                    <div>{$this->courseplan->code}</div>
                    <h1>{$this->courseplan->title}</h1>
                    <div>{$loopResult}<br>{$strblock}</div>
                </div>
                <hr>
                <div class="udeha-teacher-info">
                <div><span>{$lblteachername}</span> {$this->courseplan->teachername}</div>
                <div><span>{$lblemail}</span> <a href="mailto:{$this->courseplan->teacheremail}">{$this->courseplan->teacheremail}</a></div>
                <div><span>{$lblphone}</span> {$this->courseplan->teacherphone}</div>
                <div><span>{$lblmobile}</span> {$this->courseplan->teachercellphone}</div>
                <div><span>{$lblcontact}</span> {$this->courseplan->teachercontacthours}</div>
                <div><span>{$lblzoomteacher}</span> <a href="{$this->courseplan->teacherzoomlink}">{$this->courseplan->teacherzoomlink}</a></div>
                <div><span>{$lblzoomcourse}</span> <a href="{$this->courseplan->coursezoomlink}">{$this->courseplan->coursezoomlink}</a></div>
                </div>
            EOD;
        }

        return $this->content->presentation;
    }

    public function get_desc_content() {
        if (!isset($this->content->desc)) {
            $this->content->desc = "<p>{$this->courseplan->description}</p>";
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

    public function get_place_content() {
        if (!isset($this->content->place)) {
            $this->content->place = $this->courseplan->place;
        }

        return $this->content->place;
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

            $this->content->modules = "{$sectionslist}";
        }

        return $this->content->modules;
    }

    public function get_evaluations_content() {
        if (!isset($this->content->evaluations)) {
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

        return $this->content->evaluations;
    }

    public function get_extra_content() {
        if (!isset($this->content->extra)) {
            $this->content->extra =
                <<<EOD
                <div>
                    <p>{$this->courseplan->annex}</p>
                </div>
                EOD;
        }

        return $this->content->extra;
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
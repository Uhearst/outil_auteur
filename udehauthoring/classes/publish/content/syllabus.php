<?php

namespace format_udehauthoring\publish\content;

use format_udehauthoring\model\course_plan;

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
            $unittitle = course_plan::get_teaching_units()[$this->courseplan->unit];

            $this->content->presentation = <<<EOD
                <p>Nom du programme : {$unittitle}</p>
                <br>
                <h1>{$this->courseplan->title}</h1>
                <br>
                <h3>{$this->courseplan->code}</h3>
                <br>
                <h3>Bloc {$this->courseplan->bloc}</h3>
                <br>
                <p><strong>Professeur :</strong> {$this->courseplan->teachername}</p>
                <p><strong>Courriel :</strong> {$this->courseplan->teacheremail}</p>
                <p><strong>Téléphone :</strong> {$this->courseplan->teacherphone}</p>
                <p><strong>Cellulaire :</strong> {$this->courseplan->teachercellphone}</p>
                <p><strong>Horaires de contact :</strong> {$this->courseplan->teachercontacthours}</p>
                <p><strong>Lien Zoom personnel du professeur :</strong> {$this->courseplan->teacherzoomlink}</p>
                <p><strong>Lien Zoom du cours :</strong> {$this->courseplan->coursezoomlink}</p>
            EOD;
        }

        return $this->content->presentation;
    }

    public function get_place_content() {
        if (!isset($this->content->place)) {
            $teachingobjectiveslist = '';
            $learingobjectiveslist = '';
            foreach($this->courseplan->teachingobjectives as $teachingobjective) {
                $teachingobjectiveslist .= "<li>{$teachingobjective->teachingobjective}</li>";
                foreach($teachingobjective->learningobjectives as $learningobjective) {
                    $learingobjectiveslist .= "<li>{$learningobjective->learningobjective}</li>";
                }
            }

            $this->content->place =
                <<<EOD
            <div>
                <h3>Description du cours dans l’annuaire</h3>
                <p>{$this->courseplan->description}</p>
                <br>
                <h3>Objectifs d’enseignement</h3>
                <p>Les objectifs d’enseignement du cours sont :</p>
                <ul>{$teachingobjectiveslist}</ul>
                <br>
                <h3>Objectifs d’apprentissage visés</h3>
                <p>À la fin de ce cours, l’étudiante ou l’étudiant sera en mesure :</p>
                <ul>{$learingobjectiveslist}</ul>
                <br>
                <h3>Place du cours dans la programmation</h3>
                <p>{$this->courseplan->place}</p>
                <br>
            </div>
            EOD;
        }

        return $this->content->place;
    }

    public function get_modules_content() {

        if (!isset($this->content->modules)) {
            $sectionslist = '';
            foreach($this->courseplan->sections as $section) {
                $sectionslist = <<<EOD
                {$sectionslist}
                <tr>
                    <th>{$section->title}</th>
                </tr>
                <tr>
                    <td>{$section->description}</td>
                </tr>
            EOD;
            }

            $this->content->modules = "<table>{$sectionslist}</table>";
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
                        <td>{$evaluation->title}</td>
                        <td>{$evaluation->weight}%</td>
                    </tr>
                EOD;
            }

            $this->content->evaluations =
                <<<EOD
                    <table class="table">
                        <tr>
                          <th>Tâches à effectuer</th>
                          <th>Pondération</th>
                        </tr>
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
        // create new PDF document
        $pdf = new syllabus_pdf_generator(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor($this->courseplan->teachername);
        $pdf->SetTitle($this->courseplan->title);

        // print header
        $pdf->setPrintHeader(false);

        // set default header data
        // $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
        $pdf->setFooterData(array(0,64,0), array(0,64,128));

        // set header and footer fonts
        // $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, 10, PDF_MARGIN_RIGHT);
        // $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // ---------------------------------------------------------

        // set default font subsetting mode
        $pdf->setFontSubsetting(true);

        // Set font
        // dejavusans is a UTF-8 Unicode font, if you only need to
        // print standard ASCII chars, you can use core fonts like
        // helvetica or times to reduce file size.
        $pdf->SetFont('dejavusans', '', 14, '', true);

        // set text shadow effect
        $pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));

        $pdf->AddPage();
        $pdf->writeHTMLCell('', '', '', '', $this->get_presentation_content(), 0, 1, 0, true, '', true);
        $pdf->lastPage();

        $pdf->AddPage();
        $pdf->writeHTMLCell('', '', '', '', $this->get_place_content(), 0, 1, 0, true, '', true);
        $pdf->lastPage();

        $modules_content = "<div>
            <h3>" . get_string('modulescontent', 'format_udehauthoring') . "</h3>
            <table>
                " . $this->get_modules_content() . "
            </table>
            <br>
        </div>";
        $pdf->AddPage();
        $pdf->writeHTMLCell('', '', '', '', $modules_content, 0, 1, 0, true, '', true);
        $pdf->lastPage();

        $evaluations_content = "<div>
            <h3>" . get_string('evaluations', 'format_udehauthoring') . "</h3>
            " . $this->get_evaluations_content() . "
            <br>
        </div>";
        $pdf->AddPage();
        $pdf->writeHTMLCell('', '', '', '', $evaluations_content, 0, 1, 0, true, '', true);
        $pdf->lastPage();

        $pdf->AddPage();
        $pdf->writeHTMLCell('', '', '', '', $this->get_extra_content(), 0, 1, 0, true, '', true);
        $pdf->lastPage();

        return $pdf->Output('', 'S');
    }


}
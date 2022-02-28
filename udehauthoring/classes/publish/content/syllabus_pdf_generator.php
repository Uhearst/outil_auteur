<?php

namespace format_udehauthoring\publish\content;

require_once($CFG->dirroot . '/course/format/udehauthoring/tcpdf/tcpdf.php');

use TCPDF;

class syllabus_pdf_generator extends TCPDF
{
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}
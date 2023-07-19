<?php
//importing required libaries 
require_once($CFG->libdir.'/filelib.php');
require_once($CFG->libdir.'/tcpdf/tcpdf.php');

//Setting up tcpdf class and creating a header and footer
class MYPDF extends TCPDF{
    public function Header(){
        $this->Image('../../classes/img/nocn.jpg', $this->GetPageWidth() - 32, $this->GetPageHeight() - 32, 30, 30, 'JPG', '', '', true, 150, '', false, false, 0, false, false, false);
    }
    public function Footer(){
        $this->setY(-15);
        $this->Cell($this->getPageWidth(), 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

//Creating the pdf
$pdf = new MyPDF('P', 'mm', 'A4');

$pdf->AddPage('P');
$pdf->setPrintHeader(true);
$pdf->setPrintFooter(true);
$pdf->setFont('Times', 'B', 25);
$pdf->MultiCell($pdf->GetPageWidth(), 13, "Unit Assessment & Feedback", 0, 'C', 0, 0, 0, 2.5);
$pdf->Ln();
$pdf->setFont('Times', '', 14);

$formData->metcriteria = ($formData->metcriteria == 1) ? 'yes' : 'no';

//Get signatures and sign dates if available
if($lib->check_signature_exists_fromid($learnerid)){
    $formData->learnersignature = ($formData->learnersignature == 1) ? '<img width="100" height="50" src="@'.str_replace(" ","+",substr_replace($formData->learnersignatureimg, '', 0, 23)).'">': '';
} else {
    $formData->learnersignature = "";
}
$formData->learnersignaturedate = ($formData->learnersignaturedate) ? date('d-m-Y',$formData->learnersignaturedate) : "";

if($lib->check_signature_exists_fromid($formData->assessorid)){
    $formData->assessorsignature = ($formData->assessorsignature == 1) ? '<img width="100" height="50" src="@'.str_replace(" ","+",substr_replace($formData->assessorsignatureimg, '', 0, 23)).'">' : '';
} else {
    $formData->assessorsignature = "";
}
$formData->assessorsignaturedate = ($formData->assessorsignaturedate) ? date('d-m-Y',$formData->assessorsignaturedate) : "";

if($lib->check_signature_exists_fromid($formData->iqaid)){
    $formData->iqasignature = ($formData->iqasignature == 1) ? '<img width="100" height="50" src="@'.str_replace(" ","+",substr_replace($formData->iqasignatureimg, '', 0, 23)).'">' : '';
} else {
    $formData->iqasignature = "";
}
$formData->iqasignaturedate = ($formData->iqasignature) ? date('d-m-Y',$formData->iqasignaturedate) : "";

//Put data into an array and then loop through the array using a foreach to create the pdf content
$tableArray = [
    [['Learner Name', 'Qualification', 'Unit Number'],[$formData->learnername, $formData->qualification, $formData->unitnumber]],
    [['Level', 'Tutor/Assessor', 'Date'],[$formData->level, $formData->assessorname, date('d-m-Y',$formData->date)]],
    [['Feedback from Assessor to Learner'],[str_replace(chr(10), '<br />', $formData->feedbackassessor)]],
    [['Comments from Learner'],[str_replace(chr(10), '<br />', $formData->commentlearner)]],
    [['Have all assessment criteria for the unit been met?'],[$formData->metcriteria]],
    [['Learner Signature', 'Date'],[$formData->learnersignature, $formData->learnersignaturedate]],
    [['Tutor/Assessor Signature', 'Date'],[$formData->assessorsignature, $formData->assessorsignaturedate]],
    [['IQA Signature', 'Date'],[$formData->iqasignature, $formData->iqasignaturedate]]
];
foreach($tableArray as $tableAr){
    $html = '<table border="1" cellpadding="2"><thead><tr>';
    foreach($tableAr[0] as $tbar){
        $html .= '<th bgcolor="#E5E4E2"><b>'.$tbar.'</b></th>';
    }
    $html .= '</tr></thead><tbody><tr>';
    foreach($tableAr[1] as $tabar){
        $html .= '<th>'.$tabar.'</th>';
    }
    $html .= '</tr></tbody></table>';
    $pdf->writeHTML($html, true, false, false, false, '');
    if($pdf->getY() > 250){
        $pdf->addPage();
    }
}

$pdf->Output(preg_replace("/[ ]/","-",$coursename)."_".preg_replace("/[ ]/","-",$userName)."_".str_replace("---","-",preg_replace("/[ ]/","-",$formTitle)).".pdf", $type);
?>
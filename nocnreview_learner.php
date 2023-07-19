<?php
/**
 * @package     local_nocnreview
 * @author      Robert Tyrone Cullen
 * @var stdClass $plugin
 */
require_once(__DIR__.'/../../config.php');

use local_nocnreview\lib;

require_login();
$lib = new lib();
$errorMessage = "";
$type = '';

//Check for id and verify user capability
if(!isset($_GET['id'])){
    $errorMessage = get_string('noCourseID', 'local_nocnreview');
} else if(isset($_GET['id'])){
    $id = $_GET['id'];
    if(!preg_match("/^[0-9]*$/", $id)){
        $errorMessage = get_string('invalidCourseID', 'local_nocnreview');
    } else {
        $context = context_course::instance($id);
        require_capability('local/nocnreview:learner', $context);
        $_SESSION['nocnCourseId'] = $id;
        $type = 'learner';
        $PAGE->set_context($context);
        $PAGE->set_course($lib->get_course_record($id));
        if(isset($_GET['mid'])){
            $mid = $_GET['mid'];
            if(!preg_match("/^[0-9]*$/",$mid)){
                $errorMessage = get_string('invalidModuleID', 'local_nocnreview');
            } else{
                $_SESSION['nocnModuleId'] = $mid;
            }
        }
        if(isset($_GET['fid'])){
            $fid = $_GET['fid'];
            if(!preg_match("/^[0-9]*$/",$fid)){
                $errorMessage .= get_string('invalidFormID', 'local_nocnreview');
            } else {
                $_SESSION['nocnFormId'] = $fid;
            }
        }
    }
}

//Get the data and output the relevant data after certain checks are performed.
$PAGE->set_title("NOCN Review Learner");
$PAGE->set_heading("NOCN Review Learner");
$PAGE->set_pagelayout('incourse');
echo $OUTPUT->header();
if($errorMessage != ""){
    echo($errorMessage);
} else {
    echo("<link rel='stylesheet' href='./classes/css/nocn.css'>");
    if($lib->check_signature()){
        //For when a signature is already created
        echo("<h1 class='text-center'>".get_string('nocn', 'local_nocnreview')." - <span class='nocn-c-pointer' onclick='window.location.href=`./../../course/view.php?id=".$id."`'>".$lib->get_course_name_id($id)."</span> - ".get_string('unitAandF', 'local_nocnreview')."</h1>");
        if($lib->nocn_form_exists_learner()){
            $template = (object)[
                'array' => array_values($lib->get_nocn_formids_learner()),
                'nocn_select_error_style' => 'none',
                'courseid' => $id,
                'viewform' => get_string('viewform', 'local_nocnreview'),
                'viewpdf' => get_string('viewpdf', 'local_nocnreview'),
                'downloadpdf' => get_string('downloadpdf', 'local_nocnreview'),
                'chooseFTE' => get_string('chooseFTE', 'local_nocnreview')
            ];
            if($fid){
                if($lib->check_formid_learner($fid)){
                    echo("<script defer>window.onload=function(){document.querySelectorAll('.nocn-form-select-btn')[".($fid-1)."].click()}</script>");
                }else{
                    $template->nocn_error_text = get_string('invalidFormIDProvided', 'local_nocnreview');
                    $template->nocn_select_error_style = 'block';
                }

            }
            echo $OUTPUT->render_from_template('local_nocnreview/course_menu_learner',$template);
            //Form template
            $template = (object)[
                'learner' => $lib->get_user_fullname(),
                'learnerStar' => '*',
                'learnerDisabled' => 'disabled',
                'learnerRequired' => 'required',
                'nocn_form_style' => 'style=display:none;',
                'learnerName' => get_string('learnerName', 'local_nocnreview'),
                'qualification' => get_string('qualification', 'local_nocnreview'),
                'unitNumber' => get_string('unitNumber', 'local_nocnreview'),
                'level' => get_string('level', 'local_nocnreview'),
                'tutororAssessor' => get_string('tutororAssessor', 'local_nocnreview'),
                'date' => get_string('date', 'local_nocnreview'),
                'feedbackFromAtoL' => get_string('feedbackFromAtoL', 'local_nocnreview'),
                'commentsFromL' => get_string('commentsFromL', 'local_nocnreview'),
                'criteriaMet' => get_string('criteriaMet', 'local_nocnreview'),
                'chooseAOpt' => get_string('chooseAOpt', 'local_nocnreview'),
                'yes' => get_string('yes', 'local_nocnreview'),
                'no' => get_string('no', 'local_nocnreview'),
                'learnersign' => get_string('learnersign', 'local_nocnreview'),
                'tutororAssessorSign' => get_string('tutororAssessorSign', 'local_nocnreview'),
                'iqaSign' => get_string('iqaSign', 'local_nocnreview'),
                'submit' => get_string('submit', 'local_nocnreview'),
                'learnerClicked' => 'learnerSignature',
                'learnerstartdate' => date('Y-m-d', time()),
                'learnerenddate' => date('Y-m-d', time())
            ];
            echo $OUTPUT->render_from_template('local_nocnreview/nocn_form',$template);
            echo('<script src="./classes/js/nocn_form_learner.js"></script>');
        } else {
            echo('<div class="nocn-border"><h2 class="text-center">'.get_string('noNOCNForm', 'local_nocnreview').'</h2></div>');
        }
        \local_nocnreview\event\viewed_nocn_page_learner::create(array('context' => \context_course::instance($id), 'courseid' => $id))->trigger();
    } else {
        //For when a creation of a signature is required
        include('./classes/inc/signature.php');
    }
}
echo $OUTPUT->footer();
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
$users = [];
$menu = true;
$type = '';

if(!isset($_GET['id'])){
    $errorMessage = get_string('noCourseID', 'local_nocnreview');
} elseif(isset($_GET['id'])){
    $id = $_GET['id'];
    if(!preg_match("/^[0-9]*$/", $id)){
        $errorMessage = get_string('invalidCourseID', 'local_nocnreview');
    } else {
        $context = context_course::instance($id);
        require_capability('local/nocnreview:coach', $context);
        $_SESSION['nocnCourseId'] = $id;
        $PAGE->set_context($context);
        $PAGE->set_course($lib->get_course_record($id));
        $errorGet = false;
        $type = 'coach';
        if(isset($_GET['mid'])){
            $mid = $_GET['mid'];
            if(!preg_match("/^[0-9]*$/",$mid)){
                $errorMessage = get_string('invalidModuleID', 'local_nocnreview');
                $errorGet = true;
            } else {
                $_SESSION['nocnModuleId'] = $mid;
            }
        }
        if(isset($_GET['fid'])){
            $fid = $_GET['fid'];
            if(!preg_match("/^[0-9]*$/",$fid)){
                $errorMessage .= get_string('invalidFormID', 'local_nocnreview');
                $errorGet = true;
            } else {
                $_SESSION['nocnFormId'] = $fid;
            }
        }
        if(!$errorGet){
            $users = $lib->get_coaches_students($_SESSION['nocnCourseId']);
            if(isset($_GET['uid'])){
                $uid = $_GET['uid'];
                if(!preg_match("/^[0-9]*$/", $uid)){
                    $errorMessage = get_string('invalidUserID', 'local_nocnreview');
                } else {
                    $menu = false;
                    $uniquearray = [];
                    foreach($users as $user){
                        array_push($uniquearray, $user[1]);
                    }
                    if(!in_array($uid, $uniquearray)){
                        $errorMessage = get_string('invalidUserIDProvided', 'local_nocnreview');
                    } else {
                        $_SESSION['nocnUserId'] = $uid;
                    }
                }
            }
        }
    }
}

//Get the data and output the relevant data after certain checks are performed
$PAGE->set_title("NOCN Review Coach");
$PAGE->set_heading("NOCN Review Coach");
$PAGE->set_pagelayout('incourse');
echo $OUTPUT->header();
if($errorMessage != ""){
    echo($errorMessage);
} else {
    echo("<link rel='stylesheet' href='./classes/css/nocn.css'>");
    if($lib->check_signature()){
        $courseName = $lib->get_course_name_id($id);
        if($menu){
            echo("<h1 class='text-center'>".get_string('nocn', 'local_nocnreview')." - <span class='nocn-c-pointer' onclick='window.location.href=`./../../course/view.php?id=".$id."`'>".$courseName."</span> - ".get_string('menu', 'local_nocnreview')."</h1>");
            $users = $lib->get_nocn_totals_coach_page($users, $id);
            $template = (object)[
                'array' => array_values($users),
                'id' => $id,
                'fid' => ($fid) ? '&fid='.$fid : "",
                'mid' => ($mid) ? '&mid='.$mid : "",
                'viewforms' => get_string('viewforms', 'local_nocnreview'),
                'courseMenuTitle' => get_string('courseMenuTitle', 'local_nocnreview')
            ];
            echo $OUTPUT->render_from_template('local_nocnreview/course_menu',$template);
            \local_nocnreview\event\viewed_nocn_menu_coach::create(array('context' => \context_course::instance($id), 'courseid' => $id))->trigger();
        } else {
            $learnerName = $lib->get_user_fullname_id($_SESSION['nocnUserId']);
            echo("<h1 class='text-center'>".get_string('nocn', 'local_nocnreview')." - <span class='nocn-c-pointer' onclick='window.location.href=`./../../course/view.php?id=".$id."`'>".$courseName."</span> - <span class='nocn-c-pointer' onclick='window.location.href=`./../../user/view.php?id=".$_SESSION['nocnUserId']."&course=".$id."`'>".$learnerName."</span></h1>");
            $exists = false;
            $template = (object)[
                'courseid' => $id,
                'fid' => ($fid) ? '&fid='.$fid : "",
                'mid' => ($mid) ? '&mid='.$mid : "",
                'form_menu_error_style' => 'none',
                'uid' => $uid,
                'btnocncm' => get_string('btnocncm', 'local_nocnreview'),
                'viewpdf' => get_string('viewpdf', 'local_nocnreview'),
                'downloadpdf' => get_string('downloadpdf', 'local_nocnreview'),
                'createnf' => get_string('createnf', 'local_nocnreview'),
                'viewform' => get_string('viewform', 'local_nocnreview')
            ];
            if($lib->nocn_form_exists($_SESSION['nocnUserId'])){
                $template->form_menu_title = get_string('chooseFTEorC', 'local_nocnreview');
                $template->forms = array_values($lib->get_nocn_formids_coach());
                $exists = true;
            } else {
                $template->form_menu_title = get_string('noNOCNForms', 'local_nocnreview');
            }
            if($fid){
                $formCheck = $lib->check_formid_coach($fid);
                if($formCheck == 1){
                    echo("<script defer>window.onload=function(){document.querySelectorAll('.nocn-form-select-btn')[".($fid-1)."].click();}</script>");
                } elseif($formCheck == 2){
                    echo("<script defer>window.onload=function(){document.getElementById('nocn_create_new_form').click();}</script>");
                } elseif($formCheck == 3){
                    $template->errorText = get_string('invalidFormIDProvided', 'local_nocnreview');
                    $template->form_menu_error_style = 'block';
                }
            }
            echo $OUTPUT->render_from_template('local_nocnreview/nocn_form_menu',$template);
            $learnerNameS = get_string('learnerName', 'local_nocnreview');
            $qualS = get_string('qualification', 'local_nocnreview');
            $unitNumS = get_string('unitNumber', 'local_nocnreview');
            $levelS = get_string('level', 'local_nocnreview');
            $tutororAS = get_string('tutororAssessor', 'local_nocnreview');
            $dateS = get_string('date', 'local_nocnreview');
            $feedFAtoLS = get_string('feedbackFromAtoL', 'local_nocnreview');
            $commFL = get_string('commentsFromL', 'local_nocnreview');
            $criteriaMetS = get_string('criteriaMet', 'local_nocnreview');
            $chooseAOptS = get_string('chooseAOpt', 'local_nocnreview');
            $yesS = get_string('yes', 'local_nocnreview');
            $noS = get_string('no', 'local_nocnreview');
            $learnSignS = get_string('learnersign', 'local_nocnreview');
            $tutorSignS = get_string('tutororAssessorSign', 'local_nocnreview');
            $iqaSignS = get_string('iqaSign', 'local_nocnreview');
            $submitS = get_string('submit', 'local_nocnreview');
            if($exists){
                $template = (object)[
                    'nocn_form_style' => 'style=display:none;',
                    'coachRequired' => 'required',
                    'coachDisabled' => 'disabled',
                    'coachStar' => '*',
                    'exists' => '_exists',
                    'formType' => '('.get_string('edit', 'local_nocnreview').')',
                    'learnerName' => $learnerNameS,
                    'qualification' => $qualS,
                    'unitNumber' => $unitNumS,
                    'level' => $levelS,
                    'tutororAssessor' => $tutororAS,
                    'date' => $dateS,
                    'feedbackFromAtoL' => $feedFAtoLS,
                    'commentsFromL' => $commFL,
                    'criteriaMet' => $criteriaMetS,
                    'chooseAOpt' => $chooseAOptS,
                    'yes' => $yesS,
                    'no' => $noS,
                    'learnersign' => $learnSignS,
                    'tutororAssessorSign' => $tutorSignS,
                    'iqaSign' => $iqaSignS,
                    'submit' => $submitS,
                    'coachstartdate' => date('Y-m-d', time()),
                    'coachenddate' => date('Y-m-d', time()),
                    'tutorAssessorClicked' => 'tutorAssessorSignatureDate_exists',
                    'iqaClicked' => 'iqaSignatureDate_exists'
                ];
                echo $OUTPUT->render_from_template('local_nocnreview/nocn_form',$template);
                echo('<script defer src="./classes/js/nocn_form_coach_exists.js"></script>');
            }
            $qualValue = "";
            $levelValue = "";
            $qualFile= "./classes/json/".explode(" v", $courseName)[0].".json";
            if(file_exists($qualFile)){
                $jsonFile = file_get_contents($qualFile);
                $jsonFile = json_decode($jsonFile);
                $qualValue = $jsonFile->qualification;
                $levelValue = $jsonFile->level;
            }
            $template = (object)[
                'learner' => $learnerName,
                'coach' => $lib->get_user_fullname(),
                'nocn_form_style' => 'style=display:none;',
                'coachRequired' => 'required',
                'coachDisabled' => 'disabled',
                'coachStar' => '*',
                'formType' => '('.get_string('new', 'local_nocnreview').')',
                'newFormNumber' => ($lib->get_max_number() == 0) ? 1 : $lib->get_max_number() + 1,
                'learnerName' => $learnerNameS,
                'qualification' => $qualS,
                'unitNumber' => $unitNumS,
                'level' => $levelS,
                'tutororAssessor' => $tutororAS,
                'date' => $dateS,
                'feedbackFromAtoL' => $feedFAtoLS,
                'commentsFromL' => $commFL,
                'criteriaMet' => $criteriaMetS,
                'chooseAOpt' => $chooseAOptS,
                'yes' => $yesS,
                'no' => $noS,
                'learnersign' => $learnSignS,
                'tutororAssessorSign' => $tutorSignS,
                'iqaSign' => $iqaSignS,
                'submit' => $submitS,
                'qualvalue' => $qualValue,
                'levelvalue' => $levelValue,
                'coachstartdate' => date('Y-m-d', time()),
                'coachenddate' => date('Y-m-d', time()),
                'tutorAssessorClicked' => 'tutorAssessorSignature',
                'iqaClicked' => 'iqaSignature'
            ];
            $template->title = $lib->get_nocn_formid_title($template->newFormNumber, $id);
            echo $OUTPUT->render_from_template('local_nocnreview/nocn_form',$template);
            echo('<script defer src="./classes/js/nocn_form_coach.js"></script>');
            \local_nocnreview\event\viewed_nocn_page_coach::create(array('context' => \context_course::instance($id), 'courseid' => $id, 'relateduserid' => $_SESSION['nocnUserId']))->trigger();
        }
    } else {
        //For when a creation of a signature is required
        include('./classes/inc/signature.php');
    }
}

echo $OUTPUT->footer();
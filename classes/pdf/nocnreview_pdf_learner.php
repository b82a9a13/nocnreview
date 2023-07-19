<?php
/**
 * @package     local_nocnreview
 * @author      Robert Tyrone Cullen
 * @var stdClass $plugin
 */
require_once(__DIR__.'/../../../../config.php');

use local_nocnreview\lib;

require_login();
$lib = new lib();

//Getting url values and validating the values
$courseid = $_GET['cid'];
$formid = $_GET['fid'];
$type = $_GET['t'];
$formData = new stdClass();
if(!preg_match("/^[0-9]*$/", $courseid) || empty($courseid)){
    echo("Invalid course id.");
    exit();
} elseif(!preg_match("/^[0-9]*$/", $formid) || empty($formid)){
    echo("Invalid form id.");
    exit();
} elseif($type != 'D' && $type != 'I'){
    echo("Invalid type value.");
    exit();
}else {
    $context = context_course::instance($courseid);
    require_capability('local/nocnreview:learner',$context);
    if(!$lib->check_formid_exists_learner($courseid, $formid)){
        echo("Form does not exist.");
        exit();
    }
}

//Setting parameters used in ./../inc/pdf.php
$formData = $lib->get_form_data_learner_params($courseid, $formid);
$userName = $lib->get_user_fullname();
$coursename = $lib->get_course_name_id($courseid);
$learnerid = $formData->learnerid;
$formTitle = $lib->get_nocn_formid_title($formid, $courseid);

include("./../inc/pdf.php");
\local_nocnreview\event\viewed_nocn_pdf_learner::create(array('context' => $context, 'courseid' => $courseid, 'other' => $formid))->trigger();

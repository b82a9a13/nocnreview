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
$userid = $_GET['uid'];
$formid = $_GET['fid'];
$type = $_GET['t'];
$formData = new stdClass();
if(!preg_match("/^[0-9]*$/",$courseid) || empty($courseid)){
    echo("Invalid course id.");
    exit();
} elseif(!preg_match("/^[0-9]*$/", $userid) || empty($userid)){
    echo('Invalid user id.');
    exit();
} elseif(!preg_match("/^[0-9]*$/",$formid) || empty($formid)){
    echo("Invalid form id.");
    exit();
} elseif($type != 'D' && $type != 'I'){
    echo("Invalid type value.");
    exit();
}else{
    $context = context_course::instance($courseid);
    require_capability('local/nocnreview:coach',$context);
    if(!$lib->check_formid_exists_coach($userid, $formid, $courseid)){
        echo("Form does not exist.");
        exit();
    }
}

//Setting parameters used in ./../inc/pdf.php
$formData = $lib->get_form_data_coach_params($userid, $formid, $courseid);
$learnerid = $userid;
$userName = $lib->get_user_fullname_id($userid);
$coursename = $lib->get_course_name_id($courseid);
$formTitle = $lib->get_nocn_formid_title($formid, $courseid);

include("./../inc/pdf.php");

\local_nocnreview\event\viewed_nocn_pdf_coach::create(array('context' => $context, 'relateduserid' => $userid, 'courseid' => $courseid, 'other' => $formid))->trigger();
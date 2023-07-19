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
$errorMessage = '';

if(!isset($_GET['id'])){
    $errorMessage = get_string('noCourseID', 'local_nocnreview');
} else if(isset($_GET['id'])){
    $id = $_GET['id'];
    if(!preg_match("/^[0-9]*$/",$id)){
        $errorMessage = get_string('invalidCourseID', 'local_nocnreview');
    } else {
        if(!isset($_GET['mid'])){
            $errorMessage = get_string('noModuleID', 'local_nocnreview');
        } else {
            $mid = $_GET['mid'];
            if(!preg_match("/^[0-9]*$/",$mid)){
                $errorMessage = get_string('invalidModuleID', 'local_nocnreview');
            } else {
                $fid = $_GET['fid'];
                if(!preg_match("/^[0-9]*$/",$fid)){
                    $errorMessage = get_string('invalidFormID', 'local_nocnreview');
                } else {
                    $context = context_course::instance($id);
                    if(has_capability('local/nocnreview:coach', $context)){
                        header("Location: ./nocnreview_coach.php?id=".$id."&mid=".$mid."&fid=".$fid);
                    } else if(has_capability('local/nocnreview:learner', $context)){
                        $lib->update_invalid_completion($id, $mid, $fid);
                        header("Location: ./nocnreview_learner.php?id=".$id."&mid=".$mid."&fid=".$fid);
                    } else {
                        $errorMessage = get_string('noRequiredCapability', 'local_nocnreview');
                    }
                }
            }
        }
    }
}

$PAGE->set_title("NOCN Review Redirect");
$PAGE->set_heading("NOCN Review Redirect");
$PAGE->set_pagelayout('incourse');
echo $OUTPUT->header();
if($errorMessage != ''){
    echo $errorMessage;
}
echo $OUTPUT->footer();
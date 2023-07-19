<?php
require_once(__DIR__.'/../../../../config.php');
use local_nocnreview\lib;
$lib = new lib();
$returnText = new stdClass();
$error = false;

if(isset($_POST['learner']) && isset($_POST['qual']) && isset($_POST['unitNum']) && isset($_POST['level']) && isset($_POST['tutorAssess']) && isset($_POST['date']) && isset($_POST['criteria']) && isset($_POST['formNum'])){
    $learner = $_POST['learner'];
    $qual = $_POST['qual'];
    $unitNum = $_POST['unitNum'];
    $level = $_POST['level'];
    $tutorAssess = $_POST['tutorAssess'];
    $date = $_POST['date'];
    $feedbackAssess = $_POST['feedbackAssess'];
    $criteria = $_POST['criteria'];
    $tutorAssessSign = $_POST['tutorAssessSign'];
    $tutorAssessSignD = $_POST['tutorAssessSignD'];
    $iqaSign = $_POST['iqaSign'];
    $iqaSignD = $_POST['iqaSignD'];
    $formNum = $_POST['formNum'];
    if(!preg_match("/^[a-zA-Z '\-]*$/", $learner) || empty($learner)){
        $returnText->learner = preg_replace("/[a-zA-Z '\-]/",'',$learner);
        $error = true;
    }
    if(!preg_match("/^[a-zA-Z 0-9\-()]*$/", $qual) || empty($qual)){
        $returnText->qual = preg_replace("/[a-z A-Z\-()]/",'',$qual);
        $error = true;
    }
    if(!preg_match("/^[a-zA-Z 0-9\-]*$/", $unitNum) || empty($unitNum)){
        $returnText->unitNum = preg_replace("/[a-zA-Z 0-9\-]/",'',$unitNum);
        $error = true;
    }
    if(!preg_match("/^[0-9]*$/", $level) || empty($level)){
        $returnText->level = preg_replace("/[0-9]/",'',$level);
        $error = true;
    }
    if(!preg_match("/^[a-zA-Z '\-]*$/", $tutorAssess) || empty($tutorAssess)){
        $returnText->tutorAssess = preg_replace("/[a-zA-Z '\-]/",'',$tutorAssess);
        $error = true;
    }
    $date = new DateTime($date);
    $date = $date->format('U');
    if(!preg_match("/^[0-9]*$/", $date)){
        $returnText->date = preg_replace("/[0-9]/",'',$date);
        $error = true;
    }
    if(!preg_match("/^[a-zA-Z0-9 ,.!'():;\s\-#]*$/", $feedbackAssess) || empty($feedbackAssess)){
        $returnText->feedbackAssess = preg_replace("/[a-zA-Z0-9 ,.!'():;\s\-#]/",'',$feedbackAssess);
        $error = true;
    }
    $criteriaFilter = ['yes','no'];
    if(!in_array($criteria, $criteriaFilter)){
        $returnText->criteria = $criteria;
        $error = true;
    }
    if($tutorAssessSign == 'false' || empty($tutorAssessSign)){
        $returnText->tutorAssessSign = "No Signature";
        $error = true;
    }
    if(!empty($tutorAssessSignD)){
        $tutorAssessSignD = new DateTime($tutorAssessSignD);
        $tutorAssessSignD = $tutorAssessSignD->format('U');
        if(!preg_match("/^[0-9]*$/", $tutorAssessSignD)){
            $returnText->tutorAssessSignD = preg_replace("/[0-9]/",'',$tutorAssessSignD);
            $error = true;
        }
    } else {
        $returnText->tutorAssessSignD = "Invalid Date";
        $error = true;
    }
    if(!in_array($iqaSign, ['true','false'])){
        $returnText->iqaSign = $iqaSign;
        $error = true;
    }
    if(!empty($iqaSignD)){
        $iqaSignD = new DateTime($iqaSignD);
        $iqaSignD = $iqaSignD->format('U');
        if(!preg_match("/^[0-9]*$/", $iqaSignD)){
            $returnText->iqaSignD = preg_replace("/[0-9]/",'',$iqaSignD);
            $error = true;
        }
    }
    if(!preg_match("/^[0-9]*$/", $formNum) || empty($formNum)){
        $returnText->formNum = preg_replace("/[0-9]/","",$formNum);
        $error = true;
    }
    if(!$error){
        $array = [
            $learner,
            $qual,
            $unitNum,
            $level,
            $tutorAssess,
            $date,
            $feedbackAssess,
            $criteria,
            $tutorAssessSign,
            $tutorAssessSignD,
            $iqaSign,
            $iqaSignD,
        ];
        $recordUpdate = $lib->create_update_nocn_form($array, $formNum);
        if($recordUpdate == 'true'){
            $returnText->error = false;
        } else {
            $returnText->error = true;
            $returnText->errorType = $recordUpdate;
        }
    } else {
        $returnText->error = true;
    }
} else {
    $returnText->error = true;
}
echo(json_encode($returnText));
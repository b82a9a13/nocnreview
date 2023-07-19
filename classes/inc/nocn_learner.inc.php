<?php
require_once(__DIR__.'/../../../../config.php');
use local_nocnreview\lib;
$lib = new lib();
$returnText = new stdClass();
$error = false;

if(isset($_POST['name']) && isset($_POST['comment']) && isset($_POST['formid'])){
    $name = $_POST['name'];
    $comment = $_POST['comment'];
    $signed = $_POST['signed'];
    $signedD = $_POST['signedD'];
    $formid = $_POST['formid'];
    if(!preg_match("/^[a-zA-Z '\-]*$/", $name) || empty($name)){
        $returnText->name = preg_replace("/[a-zA-Z '\-]/","",$name);
        $error = true;
    }
    if(!preg_match("/^[a-zA-Z0-9 ,.!'():;\s\-#]*$/", $comment) || empty($comment)){
        $returnText->comment = preg_replace("/[a-zA-Z0-9 ,.!'():;\s\-#]/","",$comment);
        $error = true;
    }
    if($signed == 'false' || empty($signed)){
        $returnText->signed = 'No Signature';
        $error = true;
    }
    if(!empty($signedD)){
        $signedD = new DateTime($signedD);
        $signedD = $signedD->format('U');
        if(!preg_match("/^[0-9]*$/", $signedD)){
            $returnText->signedD = preg_replace("/[0-9]/","",$signedD);
            $error = true;
        }
    } else {
        $returnText->signedD = 'Invalid Date';
        $error = true;
    }
    if(!preg_match("/^[0-9]*$/", $formid) || empty($formid)){
        $returnText->formid = 'Invalid Form ID';
        $error = true;
    }
    if(!$error){
        $array = [
            $name,
            $comment,
            $signed,
            $signedD
        ];
        $update = $lib->update_nocn_form_learner($formid, $array);
        if($update == 'true'){
            $returnText->error = false;
        } else {
            $returnText->errorType = $update;
            $returnText->error = true;
        }
    } else {
        $returnText->error = true;
    }
} else {
    $returnText->error = true;
}
echo(json_encode($returnText));
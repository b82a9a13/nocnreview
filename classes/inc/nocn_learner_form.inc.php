<?php
require_once(__DIR__.'/../../../../config.php');
use local_nocnreview\lib;
$lib = new lib();
$returnText = new stdClass();

if(isset($_POST['id'])){
    $id = $_POST['id'];
    if(!preg_match("/^[0-9]*$/", $id) || empty($id)){
        $returnText->error = true;
    } else {
        $returnText->error = false;
        $returnText->array = $lib->get_form_data_learner($id);
    }
} else {
    $returnText->error = true;
}
echo(json_encode($returnText));
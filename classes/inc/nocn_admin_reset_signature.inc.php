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
        $returnText->result = $lib->del_user_sign($id);
        $returnText->list = $lib->get_sign_user_list();
    }
} else {
    $returnText->error = true;
}
echo(json_encode($returnText));
<?php
require_once(__DIR__.'/../../../../config.php');
use local_nocnreview\lib;
$lib = new lib();
$error = new stdClass();

if(isset($_POST['data'])){
    $data = $_POST['data'];
    if(!preg_match("/^[a-zA-Z0-9 +:;\/,=]*$/", $data) || empty($data)){
        $error->error = true;
    } else{
        if($lib->create_signature($data)){
            $error->error = false;
        } else {
            $error->error = true;
        }
    }
} else {
    $error->error = true;
}
echo(json_encode($error));
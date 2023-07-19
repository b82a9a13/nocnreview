<?php
/**
 * @package     local_nocnreview
 * @author      Robert Tyrone Cullen
 * @var stdClass $plugin
 */
namespace local_nocnreview;
use stdClass;

class lib{
    //Gets the users full name
    function get_user_fullname(){
        global $USER;
        return $USER->firstname." ".$USER->lastname;
    }

    //Gets the current users id
    function current_userid(){
        global $USER;
        return $USER->id;
    }

    //Checks if a singature is created for a user
    function check_signature(){
        global $DB;
        return $DB->record_exists('nocn_signatures', [$DB->sql_compare_text('userid') => $this->current_userid()]);
    }

    //Create signature record for a user
    function create_signature($data){
        global $DB;
        if(!($this->check_signature())){
            $record = new stdClass();
            $record->userid = $this->current_userid();
            $record->signature = $data;
            $DB->insert_record('nocn_signatures', $record, false);
            if(isset($_SESSION['nocnUserId'])){
                \local_nocnreview\event\created_signature::create(array('context' => \context_course::instance($_SESSION['nocnCourseId']), 'courseid' => $_SESSION['nocnCourseId'], 'other' => 'coach'))->trigger();
                unset($_SESSION['nocnUserId']);
            } else {
                \local_nocnreview\event\created_signature::create(array('context' => \context_course::instance($_SESSION['nocnCourseId']), 'courseid' => $_SESSION['nocnCourseId'], 'other' => 'learner'))->trigger();
            }
            unset($_SESSION['nocnCourseId']);
            return true;
        }
        return false;
    }

    //Gets users for a certain course for a coach
    function get_coaches_students($courseid){
        global $DB;
        $records = $DB->get_records_sql('SELECT {user_enrolments}.userid, {user}.firstname, {user}.lastname FROM {enrol} INNER JOIN {user_enrolments} ON {enrol}.id = {user_enrolments}.enrolid INNER JOIN {role_assignments} ON {role_assignments}.userid = {user_enrolments}.userid INNER JOIN {context} ON {context}.id = {role_assignments}.contextid INNER JOIN {user} ON {user}.id = {user_enrolments}.userid WHERE {enrol}.courseid = ? AND {user_enrolments}.status = 0 AND {role_assignments}.roleid = 5',[$courseid]);
        $array = [];
        foreach($records as $record){
            array_push($array, [$record->firstname.' '.$record->lastname, $record->userid]);
        }
        asort($array);
        return $array;
    }

    //Check if a learner has NOCN forms created
    function nocn_form_exists($learnerid){
        global $DB;
        if($DB->record_exists('nocn_reviews', [$DB->sql_compare_text('learnerid') => $learnerid, $DB->sql_compare_text('courseid') => $_SESSION['nocnCourseId']])){
            return true;
        } else {
            return false;
        }
    }

    //Check if a learner has their own forms created
    function nocn_form_exists_learner(){
        global $DB;
        if($DB->record_exists('nocn_reviews', [$DB->sql_compare_text('learnerid') => $this->current_userid(), $DB->sql_compare_text('courseid') => $_SESSION['nocnCourseId']])){
            return true;
        } else {
            return false;
        }
    }

    //Get users full name from a user id
    function get_user_fullname_id($userid){
        global $DB;
        $record = $DB->get_record_sql('SELECT firstname, lastname FROM {user} WHERE id = ?',[$userid]);
        return $record->firstname." ".$record->lastname;
    }

    //Gets course name for a course id
    function get_course_name_id($courseid){
        global $DB;
        return $DB->get_record_sql('SELECT fullname FROM {course} WHERE id = ?',[$courseid])->fullname;
    }

    //Create or update a NOCN form
    function create_update_nocn_form($array, $formNum){
        global $DB;
        $currentUid = $this->current_userid();
        $record = new stdClass();
        $record->number = $formNum;
        $record->courseid = $_SESSION['nocnCourseId'];
        $record->learnerid = $_SESSION['nocnUserId'];
        $record->assessorid = $currentUid;
        $record->learnername = $array[0];
        $record->qualification = $array[1];
        $record->unitnumber = $array[2];
        $record->level = $array[3];
        $record->assessorname = $array[4];
        $record->date = $array[5];
        $record->feedbackassessor = $array[6];
        $record->metcriteria = ($array[7] == 'yes') ? 1 : 0;
        $record->assessorsignature = ($array[8] == 'true') ? 1 : 0;
        $record->assessorsignatureimg = $this->get_signature($currentUid);
        $record->assessorsignaturedate = $array[9];
        if($array[10] == 'true'){
            $record->iqaid = $currentUid;
            $record->iqasignature = 1;
            $record->iqasignatureimg = $this->get_signature($currentUid);
            $record->iqasignaturedate = $array[11];
        }
        $exists = $DB->record_exists('nocn_reviews', [$DB->sql_compare_text('number') => $record->number, $DB->sql_compare_text('courseid') => $record->courseid, $DB->sql_compare_text('learnerid') => $record->learnerid]);
        if(!$exists){
            if($record->iqaid == $record->assessorid){
                return "You can't sign as IQA and assessor";
            }elseif($this->get_nocn_formid_title($record->number, $record->courseid) != 'NO NAME!'){
                if($DB->insert_record('nocn_reviews', $record, false)){
                    \local_nocnreview\event\created_nocn_form::create(array('context' => \context_course::instance($_SESSION['nocnCourseId']), 'courseid' => $_SESSION['nocnCourseId'], 'relateduserid' => $_SESSION['nocnUserId']))->trigger();
                    $history = new stdClass();
                    $history->reviewid = $DB->get_record_sql('SELECT id FROM {nocn_reviews} WHERE number = ? AND courseid = ? AND learnerid = ?', [$record->number, $record->courseid, $record->learnerid])->id;
                    $history->ipaddress = $_SERVER['REMOTE_ADDR'];
                    $history->date = time();
                    $history->browserdata = $_SERVER['HTTP_USER_AGENT'];
                    $history->nocndata = json_encode($record);
                    $history->userid = $currentUid;
                    $history->signid = $DB->get_record_sql('SELECT id FROM {nocn_signatures} WHERE userid = ?',[$currentUid])->id;
                    $DB->insert_record('nocn_history', $history, false);
                    unset($_SESSION['nocnCourseId']);
                    unset($_SESSION['nocnUserId']);
                    return 'true';
                } else {
                    return 'Creation Error';
                }
            } else {
                return 'Form is not required';
            }
        } elseif($exists){
            $recordTemp = $DB->get_record_sql('SELECT id FROM {nocn_reviews} WHERE number = ? AND courseid = ? AND learnerid = ? AND assessorid != ? AND iqaid IS NULL AND learnername = ? AND qualification = ? AND unitnumber = ? AND level = ? AND assessorname = ? AND date = ? AND feedbackassessor = ? AND metcriteria = 1 AND learnersignature = 1 AND assessorsignature = 1 AND iqasignature IS NULL',[$record->number, $record->courseid, $record->learnerid, $record->assessorid, $record->learnername, $record->qualification, $record->unitnumber, $record->level, $record->assessorname, $record->date, $record->feedbackassessor]);
            if(isset($recordTemp->id) && $record->iqasignature = 1 && $record->iqasignaturedate != null){
                unset($record->assessorsignaturedate);
                unset($record->assessorsignature);
                unset($record->assessorsignatureimg);
                unset($record->assessorid);
                $record->id = $recordTemp->id;
                if($DB->update_record('nocn_reviews', $record, false)){
                    \local_nocnreview\event\updated_nocn_form_iqa::create(array('context' => \context_course::instance($_SESSION['nocnCourseId']), 'courseid' => $_SESSION['nocnCourseId'], 'relateduserid' => $_SESSION['nocnUserId']))->trigger();
                    $history = new stdClass();
                    $history->reviewid = $record->id;
                    $history->ipaddress = $_SERVER['REMOTE_ADDR'];
                    $history->date = time();
                    $history->browserdata = $_SERVER['HTTP_USER_AGENT'];
                    $history->nocndata = json_encode($record);
                    $history->userid = $currentUid;
                    $history->signid = $DB->get_record_sql('SELECT id FROM {nocn_signatures} WHERE userid = ?',[$currentUid])->id;
                    $DB->insert_record('nocn_history', $history, false);
                    unset($_SESSION['nocnCourseId']);
                    unset($_SESSION['nocnUserId']);
                    return 'true';
                } else {
                    return 'Update Error';
                }
            } elseif(isset($DB->get_record_sql('SELECT id FROM {nocn_reviews} WHERE number = ? AND courseid = ? AND learnerid = ? AND assessorid IS NOT NULL AND learnername IS NOT NULL AND qualification IS NOT NULL AND unitnumber IS NOT NULL AND level IS NOT NULL AND assessorname IS NOT NULL AND date IS NOT NULL AND feedbackassessor IS NOT NULL AND commentlearner IS NOT NULL AND metcriteria = 1 AND learnersignature IS NOT NULL AND assessorsignature',[$record->number, $record->courseid, $record->learnerid])->id)){
                return 'Form is locked and cannot be changed';
            }else {
                $recordTemp = $DB->get_record_sql('SELECT id, assessorid, iqaid, iqasignaturedate, assessorsignaturedate FROM {nocn_reviews} WHERE number = ? AND courseid = ? AND learnerid = ?',[$record->number, $record->courseid, $record->learnerid]);
                $record->id = $recordTemp->id;  
                if($record->assessorid != $recordTemp->assessorid){
                    return "You are not the assessor for this form";
                }
                if(isset($record->iqaid) && isset($record->assessorid)){
                    if($record->assessorid == $recordTemp->iqaid || $record->iqaid == $recordTemp->assessorid){
                        return "You can't sign as IQA and assessor";
                    } 
                }
                if($recordTemp->assessorsignaturedate == $record->assessorsignaturedate){
                    unset($record->assessorsignaturedate);
                    unset($record->assessorsignature);
                    unset($record->assessorsignatureimg);
                    unset($record->assessorid);
                }
                if(isset($record->assessorid)){
                    if($recordTemp->assessorid == $record->assessorid){
                        return "You can't change your signature";
                    } elseif($recordTemp->assessorid != $record->assessorid){
                        return "You can't change the signature";
                    }
                }
                if(isset($record->iqasignaturedate)){
                    if($recordTemp->iqasignaturedate == $record->iqasignaturedate){
                        unset($record->iqaid);
                        unset($record->iqasignature);
                        unset($record->iqasignatureimg);
                        unset($record->iqasignaturedate);
                    }
                }
                if(isset($record->iqaid)){
                    if($recordTemp->iqaid == $record->iqaid){
                        return "You can't change your signature";
                    } elseif($recordTemp->iqaid != $record->iqaid && $recordTemp->iqaid != null){
                        return "You can't change the signature";
                    }
                }
                if($DB->update_record('nocn_reviews', $record, false)){
                    \local_nocnreview\event\updated_nocn_form_coach::create(array('context' => \context_course::instance($_SESSION['nocnCourseId']), 'courseid' => $_SESSION['nocnCourseId'], 'relateduserid' => $_SESSION['nocnUserId']))->trigger();
                    $this->update_nocn_form_completion($record->id, $_SESSION['nocnUserId']);
                    $history = new stdClass();
                    $history->reviewid = $DB->get_record_sql('SELECT id FROM {nocn_reviews} WHERE number = ? AND courseid = ? AND learnerid = ?', [$record->number, $record->courseid, $record->learnerid])->id;
                    $history->ipaddress = $_SERVER['REMOTE_ADDR'];
                    $history->date = time();
                    $history->browserdata = $_SERVER['HTTP_USER_AGENT'];
                    $history->nocndata = json_encode($record);
                    $history->userid = $currentUid;
                    $history->signid = $DB->get_record_sql('SELECT id FROM {nocn_signatures} WHERE userid = ?',[$currentUid])->id;
                    $DB->insert_record('nocn_history', $history, false);
                    unset($_SESSION['nocnCourseId']);
                    unset($_SESSION['nocnUserId']);
                    return 'true';
                } else {
                    return 'Update Error';
                }
            }
        } else {
            return 'Read Error';
        }
    }

    //Get nocn form ids for a learner
    function get_nocn_formids_learner(){
        global $DB;
        $records = $DB->get_records_sql('SELECT id, number FROM {nocn_reviews} WHERE learnerid = ? AND courseid = ?',[$this->current_userid(),$_SESSION['nocnCourseId']]);
        $array = [];
        foreach($records as $record){
            $name = $DB->get_record_sql('SELECT name FROM {url} WHERE course = ? and externalurl LIKE ("%/local/nocnreview/nocnreview.php%fid='.$record->number.'")',[$_SESSION['nocnCourseId']])->name;
            if($name == null){
                $name = 'NO NAME!';
            }
            $completion = $this->check_nocn_form_completion($record->id);
            array_push($array, [$record->id, $record->number, $name, $completion]);
        }
        return $array;
    }

    //Get nocn form data from a form id for a learner
    function get_form_data_learner($id){
        global $DB;
        $record = $DB->get_record_sql('SELECT * FROM {nocn_reviews} WHERE learnerid = ? AND courseid = ? AND id = ?',[$this->current_userid(), $_SESSION['nocnCourseId'], $id]);
        $record->date = ($record->date) ? date('Y-m-d', $record->date) : "";
        $record->assessorsignaturedate = ($record->assessorsignaturedate) ? date('Y-m-d', $record->assessorsignaturedate) : "";
        $record->iqasignaturedate = ($record->iqasignaturedate) ? date('Y-m-d', $record->iqasignaturedate) : "";
        $record->learnersignaturedate = ($record->learnersignaturedate) ? date('Y-m-d', $record->learnersignaturedate) : "";
        $name = $DB->get_record_sql('SELECT name FROM {url} WHERE course = ? AND externalurl LIKE ("%/local/nocnreview/nocnreview.php%fid='.$record->number.'")', [$_SESSION['nocnCourseId']])->name;
        $record->title = ($name == null) ? 'NO NAME!' : $name;
        return $record;
    }

    //update a nocn form by a leanrer
    function update_nocn_form_learner($id, $array){
        global $DB;
        $currentUid = $this->current_userid();
        $tempRecord = $DB->get_record_sql('SELECT id FROM {nocn_reviews} WHERE learnerid = ? AND courseid = ? AND id = ? AND metcriteria = 1 AND learnersignature IS NOT NULL',[$currentUid, $_SESSION['nocnCourseId'], $id]);
        if(isset($tempRecord->id)){
            return "Form is locked and cannot be changed";
        } elseif($DB->record_exists('nocn_reviews', [$DB->sql_compare_text('learnerid') => $currentUid, $DB->sql_compare_text('courseid') => $_SESSION['nocnCourseId'], $DB->sql_compare_text('id') => $id])){
            $signDate = $DB->get_record_sql('SELECT learnersignaturedate FROM {nocn_reviews} WHERE learnerid = ? AND courseid = ? AND id = ?',[$currentUid, $_SESSION['nocnCourseId'], $id])->learnersignaturedate;
            $record = new stdClass();
            $record->id = $id;
            $record->learnername = $array[0];
            $record->commentlearner = $array[1];
            if($array[2] == 'false'){
                $record->learnersignature = 0;
            } elseif($array[2] == 'true'){
                $record->learnersignature = 1;
            }
            if($signDate != $array[3] && !empty($signDate)){
                return "You can't change your signature";
            }
            $record->learnersignaturedate = $array[3];
            $record->learnersignatureimg = $this->get_signature($currentUid);
            if($DB->update_record('nocn_reviews', $record, false)){
                \local_nocnreview\event\updated_nocn_form_learner::create(array('context' => \context_course::instance($_SESSION['nocnCourseId']), 'courseid' => $_SESSION['nocnCourseId']))->trigger();
                $this->update_nocn_form_completion($id, $currentUid);
                $history = new stdClass();
                $history->reviewid = $id;
                $history->ipaddress = $_SERVER['REMOTE_ADDR'];
                $history->date = time();
                $history->browserdata = $_SERVER['HTTP_USER_AGENT'];
                $history->nocndata = json_encode($record);
                $history->userid = $currentUid;
                $history->signid = $DB->get_record_sql('SELECT id FROM {nocn_signatures} WHERE userid = ?',[$currentUid])->id;
                $DB->insert_record('nocn_history', $history, false);
                unset($_SESSION['nocnCourseId']);
                return 'true';
            } else {
                return 'Update Error';
            }
        } else {
            return 'Form does not exists';
        }
    }

    //Get forms for a user from a learner id and course id
    function get_nocn_formids_coach(){
        global $DB;
        $array = [];
        if($DB->record_exists('nocn_reviews', [$DB->sql_compare_text('learnerid') => $_SESSION['nocnUserId'], $DB->sql_compare_text('courseid') => $_SESSION['nocnCourseId']])){
            $records = $DB->get_records_sql('SELECT number, id FROM {nocn_reviews} WHERE learnerid = ? AND courseid = ?',[$_SESSION['nocnUserId'], $_SESSION['nocnCourseId']]);
            foreach($records as $record){
                $name = $DB->get_record_sql('SELECT name FROM {url} WHERE course = ? AND externalurl LIKE ("%/local/nocnreview/nocnreview.php%fid='.$record->number.'")',[$_SESSION['nocnCourseId']])->name;
                if($name == null){
                    $name = 'NO NAME!';
                }
                $completion = $this->check_nocn_form_completion($record->id);
                array_push($array, [$record->number, $record->id, $name, $completion]);
            }
        }
        return $array;
    }

    //Get a form based on the id for a coach
    function get_form_data_coach($id){
        global $DB;
        $record = $DB->get_record_sql('SELECT * FROM {nocn_reviews} WHERE learnerid = ? AND courseid = ? AND id = ?',[$_SESSION['nocnUserId'],$_SESSION['nocnCourseId'],$id]);
        $record->date = ($record->date) ? date('Y-m-d', $record->date) : "";
        $record->assessorsignaturedate = ($record->assessorsignaturedate) ? date('Y-m-d', $record->assessorsignaturedate) : "";
        $record->iqasignaturedate = ($record->iqasignaturedate) ? date('Y-m-d', $record->iqasignaturedate) : "";
        $record->learnersignaturedate = ($record->learnersignaturedate) ? date('Y-m-d', $record->learnersignaturedate) : "";
        $name = $DB->get_record_sql('SELECT name FROM {url} WHERE course = ? and externalurl LIKE ("%/local/nocnreview/nocnreview.php%fid='.$record->number.'")', [$_SESSION['nocnCourseId']])->name;
        $record->title = ($name == null) ? 'NO NAME!' : $name;
        return $record;
    }

    //Get the number of forms for a specific user and course
    function get_max_number(){
        global $DB;
        $record = $DB->get_record_sql('SELECT MAX(number) as number FROM {nocn_reviews} WHERE learnerid = ? AND courseid = ?',[$_SESSION['nocnUserId'],$_SESSION['nocnCourseId']])->number;
        $record = ($record == null) ? 0 : $record++;
        return $record;
    }

    //Get course record
    function get_course_record($id){
        global $DB;
        return $DB->get_record_sql('SELECT * FROM {course} WHERE id = ?',[$id]);
    }

    //Check if form id provided by a coach is valid
    function check_formid_coach($id){
        global $DB;
        if($DB->record_exists('nocn_reviews', [$DB->sql_compare_text('learnerid') => $_SESSION['nocnUserId'], $DB->sql_compare_text('courseid') => $_SESSION['nocnCourseId'], $DB->sql_compare_text('number') => $id])){
            //1 = record exists
            return 1;
        } elseif($this->get_max_number()+1 == $id){
            //2 = new record required
            return 2;
        } else {
            //3 = Invalid form id
            return 3;
        }
    }

    //CHeck if form id provided by a leanrer is valid
    function check_formid_learner($id){
        global $DB;
        return $DB->record_exists('nocn_reviews', [$DB->sql_compare_text('learnerid') => $this->current_userid(), $DB->sql_compare_text('courseid') => $_SESSION['nocnCourseId'], $DB->sql_compare_text('number') => $id]);
    }

    //Check if a form exists using data provided by a coach page
    function check_formid_exists_coach($uid, $fid, $cid){
        global $DB;
        return $DB->record_exists('nocn_reviews', [$DB->sql_compare_text('learnerid') => $uid, $DB->sql_compare_text('courseid') => $cid, $DB->sql_compare_text('number') => $fid]);
    }

    //Get a nocn form record using data provided by a coach page
    function get_form_data_coach_params($uid, $fid, $cid){
        global $DB;
        return $DB->get_record_sql('SELECT * FROM {nocn_reviews} WHERE learnerid = ? AND number = ? AND courseid = ?',[$uid, $fid, $cid]);
    }

    //Check if a learner has a signature
    function check_signature_exists_fromid($uid){
        global $DB;
        return $DB->record_exists('nocn_signatures',[$DB->sql_compare_text('userid') => $uid]);
    }

    //Get signature from a user id
    function get_signature($uid){
        global $DB;
        return $DB->get_record_sql('SELECT signature FROM {nocn_signatures} WHERE userid = ?',[$uid])->signature;
    }

    //Check if the form exists for learner pdf
    function check_formid_exists_learner($cid, $fid){
        global $DB;
        return $DB->record_exists('nocn_reviews',[$DB->sql_compare_text('learnerid') => $this->current_userid(), $DB->sql_compare_text('courseid') => $cid, $DB->sql_compare_text('number') => $fid]);
    }
    
    //Get form data for learner pdf
    function get_form_data_learner_params($cid, $fid){
        global $DB;
        return $DB->get_record_sql('SELECT * FROM {nocn_reviews} WHERE learnerid = ? AND number = ? AND courseid = ?',[$this->current_userid(), $fid, $cid]);
    }

    //Delete invalid completion for a module
    function update_invalid_completion($cid, $mid, $fid){
        global $DB;
        $update = false;
        $completionstate = 0;
        $viewed = 0;
        if($DB->record_exists('nocn_reviews',[$DB->sql_compare_text('number') => $fid, $DB->sql_compare_text('courseid') => $cid, $DB->sql_compare_text('learnerid') => $this->current_userid()])){
            $record = $DB->get_record_sql('SELECT id FROM {nocn_reviews} WHERE number = ? AND courseid = ? AND learnerid = ? AND assessorid IS NOT NULL AND learnername IS NOT NULL AND qualification IS NOT NULL AND unitnumber IS NOT NULL AND level IS NOT NULL AND assessorname IS NOT NULL AND date IS NOT NULL AND feedbackassessor IS NOT NULL AND commentlearner IS NOT NULL AND metcriteria = 1 AND learnersignature = 1 AND learnersignaturedate IS NOT NULL AND assessorsignature = 1 AND assessorsignaturedate IS NOT NULL',[$fid,$cid,$this->current_userid()]);
            if($record != null){$completionstate = 1;$viewed = 1;}
            $update = true;
        } else {$update = true;}
        if($update){
            $updateRecord = new stdClass();
            $updateRecord->id = $DB->get_record_sql('SELECT id FROM {course_modules_completion} WHERE userid = ? AND coursemoduleid = ?',[$this->current_userid(), $mid])->id;
            if($updateRecord->id != null){
                $updateRecord->completionstate = $completionstate;
                $updateRecord->viewed = $viewed;
                $updateRecord->timemodified = time();
                $DB->update_record('course_modules_completion', $updateRecord, false);
            }
        }
        return;
    }

    //Get total number of NOCN forms for a specific course on the coach page
    function get_nocn_totals_coach_page($userarray, $courseid){
        global $DB;
        for($i = 0; $i < count($userarray); $i++){
            $result = $DB->get_record_sql('SELECT MAX(number) as number FROM {nocn_reviews} WHERE learnerid = ? AND courseid = ?',[$userarray[$i][1],$courseid])->number;
            $userarray[$i][2] = ($result) ? $result : 0;
            $results = $DB->get_records_sql('SELECT id FROM {nocn_reviews} WHERE courseid = ? AND learnerid = ? AND assessorid IS NOT NULL AND learnername IS NOT NULL AND qualification IS NOT NULL AND unitnumber IS NOT NULL AND level IS NOT NULL AND assessorname IS NOT NULL AND date IS NOT NULL AND feedbackassessor IS NOT NULL AND commentlearner IS NOT NULL AND metcriteria = 1 AND learnersignature = 1 AND learnersignaturedate IS NOT NULL AND assessorsignature = 1 AND assessorsignaturedate IS NOT NULL',[$courseid,$userarray[$i][1]]);
            $userarray[$i][3] = count($results);
        }
        return $userarray;
    }

    //Check if a form submitted by a learner is complete : then set the module as complete
    function update_nocn_form_completion($id, $userid){
        global $DB;
        $record = $DB->get_record_sql('SELECT id, number FROM {nocn_reviews} WHERE id = ? AND courseid = ? AND learnerid = ? AND assessorid IS NOT NULL AND learnername IS NOT NULL AND qualification IS NOT NULL AND unitnumber IS NOT NULL AND level IS NOT NULL AND assessorname IS NOT NULL AND date IS NOT NULL AND feedbackassessor IS NOT NULL AND commentlearner IS NOT NULL AND metcriteria = 1 AND learnersignature = 1 AND learnersignaturedate IS NOT NULL AND assessorsignature = 1 AND assessorsignaturedate IS NOT NULL',[$id,$_SESSION['nocnCourseId'],$userid]);
        $updateRecord = new stdClass();
        $value = 0;
        if($record != null){
            $value = 1;
        } else {
            $record = $DB->get_record_sql('SELECT id, number FROM {nocn_reviews} WHERE id = ? AND courseid = ? AND learnerid = ?',[$id,$_SESSION['nocnCourseId'],$userid]);
        }
        $id = $DB->get_record_sql("SELECT {course_modules_completion}.id as id FROM {url} INNER JOIN {course_modules} ON {url}.id = {course_modules}.instance INNER JOIN {course_modules_completion} ON {course_modules}.id = {course_modules_completion}.coursemoduleid WHERE {url}.course = ? AND {url}.externalurl LIKE ('%/local/nocnreview/nocnreview.php%fid=".$record->number."') AND {course_modules}.module = 21 AND {course_modules_completion}.userid = ? AND {url}.course = {course_modules}.course",[$_SESSION['nocnCourseId'], $userid])->id;
        if($id != null){
            $updateRecord->id = $id;
            $updateRecord->completionstate = $value;
            $updateRecord->viewed = $value;
            $updateRecord->timemodified = time();
            $DB->update_record('course_modules_completion', $updateRecord, false);
        }
    }

    //Get form title from fromid and courseid
    function get_nocn_formid_title($formid, $courseid){
        global $DB;
        $name = $DB->get_record_sql('SELECT name FROM {url} WHERE course = ? AND externalurl LIKE ("%/local/nocnreview/nocnreview.php%fid='.$formid.'")',[$courseid]);
        $name = (!isset($name->name)) ? 'NO NAME!' : $name->name;
        return $name;
    }

    //Check completion for a NOCN form by id
    function check_nocn_form_completion($formid){
        global $DB;
        $record = $DB->get_record_sql('SELECT id FROM {nocn_reviews} WHERE id = ? AND assessorid IS NOT NULL AND learnername IS NOT NULL AND qualification IS NOT NULL AND unitnumber IS NOT NULL AND level IS NOT NULL AND assessorname IS NOT NULL AND date IS NOT NULL AND feedbackassessor IS NOT NULL AND commentlearner IS NOT NULL AND metcriteria = 1 AND learnersignature = 1 AND learnersignaturedate IS NOT NULL AND assessorsignature = 1 AND assessorsignaturedate IS NOT NULL',[$formid]);
        if($record != null){
            return "&#x2713;";
        } else{
            return "&#x2717;";
        }
    }

    //Get list of all users with a signature
    function get_sign_user_list(){
        global $DB;
        $records = $DB->get_records_sql('SELECT {nocn_signatures}.userid, {user}.firstname, {user}.lastname FROM {nocn_signatures} INNER JOIN {user} ON {nocn_signatures}.userid = {user}.id');
        $array = [];
        foreach($records as $record){
            array_push($array, [$record->userid, $record->firstname.' '.$record->lastname]);
        }
        return $array;
    }

    //Delete user signature from user id
    function del_user_sign($id){
        global $DB;
        if($DB->delete_records('nocn_signatures',[$DB->sql_compare_text('userid') => $id])){
            \local_nocnreview\event\deleted_signature::create(array('context' => \context_system::instance(), 'relateduserid' => $id))->trigger();
            return true;
        } else {
            return false;
        }
    }

    //Get all users who have incomplete forms
    function get_incomplete_user_list(){
        global $DB;
        $records = $DB->get_records_sql('SELECT learnerid, learnername FROM {nocn_reviews} WHERE metcriteria = 0 OR learnersignature IS NULL');
        $array = [];
        foreach($records as $record){
            if(!in_array([$record->learnerid, $record->learnername], $array)){
                array_push($array, [$record->learnerid, $record->learnername]);
            }
        }
        return $array;
    }

    //Get incomplete forms for a user id
    function get_incomplete_forms_uid($uid){
        global $DB;
        $records = $DB->get_records_sql('SELECT * FROM {nocn_reviews} WHERE (metcriteria = 0 OR learnersignature IS NULL) AND learnerid = ?',[$uid]);
        $array = [];
        foreach($records as $record){
            $record->learnersignaturedate = ($record->learnersignaturedate == null) ? $record->learnersignaturedate : date('d/m/Y', $record->learnersignaturedate);
            $record->iqasignaturedate = ($record->iqasignaturedate == null) ? $record->iqasignaturedate : date('d/m/Y', $record->iqasignaturedate);
            array_push($array, [$record->assessorid, $record->assessorname, $record->assessorsignature, date('d/m/Y',$record->assessorsignaturedate), $record->commentlearner, $record->courseid, date('d/m/Y',$record->date), $record->feedbackassessor, $record->id, $record->iqaid, $record->iqasignature, $record->iqasignaturedate, $record->learnerid, $record->learnername, $record->learnersignature, $record->learnersignaturedate, $record->level, $record->metcriteria, $record->number, $record->qualification, $record->unitnumber, $this->get_nocn_formid_title($record->number, $record->courseid)]);
        }
        return $array;
    }

    //Get a list of all learners with a NOCN form history
    function get_history_user_list(){
        global $DB;
        $records = $DB->get_records_sql('SELECT {user}.id as id, {user}.lastname as lastname, {user}.firstname as firstname FROM {nocn_history} INNER JOIN {nocn_reviews} ON {nocn_history}.reviewid = {nocn_reviews}.id INNER JOIN {user} ON {nocn_reviews}.learnerid = {user}.id');
        $array = [];
        foreach($records as $record){
            array_push($array, [$record->id, $record->firstname.' '.$record->lastname]);
        }
        return $array;
    }

    //Get a list of all the forms which have a history
    function get_history_list_uid($uid){
        global $DB;
        $records = $DB->get_records_sql('SELECT {nocn_reviews}.number as number, {nocn_history}.reviewid as reviewid, {nocn_reviews}.courseid as courseid FROM {nocn_history} INNER JOIN {nocn_reviews} ON {nocn_reviews}.id = {nocn_history}.reviewid WHERE {nocn_reviews}.learnerid = ? ',[$uid]);
        $array = [];
        foreach($records as $record){
            $nocnTitle = $this->get_nocn_formid_title($record->number, $record->courseid);
            if(!in_array([$record->reviewid, $nocnTitle], $array)){
                array_push($array, [$record->reviewid, $nocnTitle]);
            }
        }
        return $array;
    }

    //Get history data for a specific review id
    function get_history_data_rid($rid){
        global $DB;
        $records = $DB->get_records_sql('SELECT {nocn_history}.date as date, {nocn_history}.reviewid as reviewid, {nocn_history}.id as id, {nocn_history}.browserdata as browserdata, {nocn_history}.nocndata as nocndata, {nocn_history}.userid as userid, {nocn_history}.signid as signid, {nocn_reviews}.courseid as courseid, {nocn_reviews}.number as number, {nocn_history}.ipaddress as ipaddress, {user}.firstname as firstname, {user}.lastname as lastname FROM {nocn_history} INNER JOIN {nocn_reviews} ON {nocn_history}.reviewid = {nocn_reviews}.id INNER JOIN {user} ON {nocn_history}.userid = {user}.id WHERE {nocn_history}.reviewid = ?',[$rid]);
        $array = [];
        foreach($records as $record){
            array_push($array, [$record->date, $record->reviewid, $record->id, $record->browserdata, $record->nocndata, $record->userid, $record->signid, $this->get_nocn_formid_title($record->number, $record->courseid), $record->ipaddress, $record->firstname.' '.$record->lastname]);
        }
        return $array;
    }
}
<?php
// This file is part of the nocnreview plugin
/**
 * @package     local_manuals
 * @author      Robert Tyrone Cullen
 * @var stdClass $plugin
 */

namespace local_nocnreview\task;

defined('MOODLE_INTERNAL') || die();


class signed_and_incomplete extends \core\task\scheduled_task {
    public function get_name(){
        return get_string('SandIname', 'local_nocnreview');
    }
    public function execute(){
        global $DB;
        $learnerRequired = $DB->get_records_sql('SELECT learnerid, qualification, unitnumber, level FROM {nocn_reviews} WHERE learnersignature IS NULL AND commentlearner IS NULL AND assessorsignature = 1');
        foreach($learnerRequired as $lRequired){
            $qualTitle = $lRequired->qualification.' Level '.$lRequired->level.' - Unit '.$lRequired->unitnumber;
            $htmlMessage = '<p>Please fill out your required fields for the '.$qualTitle.' NOCN form</p>';
            email_to_user($DB->get_record_sql('SELECT email, id, username FROM {user} WHERE id = ?',[$lRequired->learnerid]), 'noreply@northerntrainingacademy.co.uk', 'NOCN Form Notification', 'Please fill out your required fields for the '.$qualTitle.' NOCN form', $htmlMessage);
        }
        $coachRequired = $DB->get_records_sql('SELECT assessorid, qualification, unitnumber, level FROM {nocn_reviews} WHERE metcriteria = 0 AND learnersignature = 1 AND commentlearner IS NOT NULL AND assessorsignature = 1');
        foreach($coachRequired as $cRequired){
            $qualTitle = $cRequired->qualification.' Level '.$cRequired->level.' - Unit '.$cRequired->unitnumber;
            $htmlMessage = '<p>Please fill out your required fields for the '.$qualTitle.' NOCN form</p>';
            email_to_user($DB->get_record_sql('SELECT email, id, username FROM {user} WHERE id = ?',[$cRequired->assessorid]), 'noreply@northerntrainingacademy.co.uk', 'NOCN Form Notification', 'Please fill out your required fields for the '.$qualTitle.' NOCN form', $htmlMessage);
        }
        return true;
    }
}
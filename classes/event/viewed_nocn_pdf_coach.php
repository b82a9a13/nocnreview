<?php
/**
 * @package     local_nocnreview
 * @author      Robert Tyrone Cullen
 * @var stdClass $plugin
 */
namespace local_nocnreview\event;
use core\event\base;
defined('MOODLE_INTERNAL') || die();

class viewed_nocn_pdf_coach extends base {
    protected function init(){
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }
    public static function get_name(){
        return "NOCN coach pdf viewed";
    }
    public function get_description(){
        return "The user with id '".$this->userid."' viewed the NOCN pdf for the learner with id '".$this->relateduserid."' and for the course with id '".$this->courseid."'";
    }
    public function get_url(){
        return new \moodle_url('/local/nocnreview/classes/pdf/nocnreview_pdf_coach.php?uid='.$this->relateduserid.'&fid='.$this->other.'&cid='.$this->courseid);
    }
    public function get_id(){
        return $this->objectid;
    }
}
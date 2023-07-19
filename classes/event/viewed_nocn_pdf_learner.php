<?php
/**
 * @package     local_nocnreview
 * @author      Robert Tyrone Cullen
 * @var stdClass $plugin
 */
namespace local_nocnreview\event;
use core\event\base;
defined('MOODLE_INTERNAL') || die();

class viewed_nocn_pdf_learner extends base {
    protected function init(){
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }
    public static function get_name(){
        return "NOCN learner pdf viewed";
    }
    public function get_description(){
        return "The user with id '".$this->userid."' viewed one of their NOCN pdf's and for the course with id '".$this->courseid."'";
    }
    public function get_url(){
        return new \moodle_url('/local/nocnreview/classes/pdf/nocnreview_pdf_learner.php?cid='.$this->courseid.'&fid='.$this->other);
    }
    public function get_id(){
        return $this->objectid;
    }
}
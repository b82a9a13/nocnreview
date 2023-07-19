<?php
/**
 * @package     local_nocnreview
 * @author      Robert Tyrone Cullen
 * @var stdClass $plugin
 */
namespace local_nocnreview\event;
use core\event\base;
defined('MOODLE_INTERNAL') || die();

class created_nocn_form extends base {
    protected function init(){
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }
    public static function get_name(){
        return "NOCN form created";
    }
    public function get_description(){
        return "The user with id '".$this->userid."' created a NOCN form for the user with id '".$this->relateduserid."' for the course with id '".$this->courseid."'";
    }
    public function get_url(){
        return new \moodle_url('/local/nocnreview/nocnreview_coach.php?id='.$this->courseid.'&uid='.$this->relateduserid);
    }
    public function get_id(){
        return $this->objectid;
    }
}
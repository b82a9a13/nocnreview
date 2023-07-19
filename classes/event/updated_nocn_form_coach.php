<?php
/**
 * @package     local_nocnreview
 * @author      Robert Tyrone Cullen
 * @var stdClass $plugin
 */
namespace local_nocnreview\event;
use core\event\base;
defined('MOODLE_INTERNAL') || die();

class updated_nocn_form_coach extends base {
    protected function init(){
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
    }
    public static function get_name(){
        return "NOCN form updated by coach";
    }
    public function get_description(){
        return "The user with id '".$this->userid."' updated a NOCN form for the user with id '".$this->relateduserid."' and for the course with id '".$this->courseid."'";
    }
    public function get_url(){
        return new \moodle_url('/local/nocnreview/nocnreview_coach.php?id='.$this->courseid.'&uid='.$this->relateduserid);
    }
    public function get_id(){
        return $this->objectid;
    }
}
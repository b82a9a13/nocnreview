<?php 
/**
 * @package     local_nocnreview
 * @author      Robert Tyrone Cullen
 * @var stdClass $plugin
 */
namespace local_nocnreview\event;
use core\event\base;
defined('MOODLE_INTERNAL') || die();

class updated_nocn_form_iqa extends base {
    protected function init(){
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }
    public static function get_name(){
        return "NOCN form signed by iqa";
    }
    public function get_description(){
        return "The user with id '".$this->userid."' signed as iqa for a NOCN form for the user with id '".$this->relateduserid."' and for the course with id '".$this->courseid."'";
    }
    public function get_url(){
        return new \moodle_url('/local/nocnreview/nocnreview_coach.php?id='.$this->courseid.'&uid='.$this->relateduserid);
    }
    public function get_id(){
        return $this->object;
    }
}
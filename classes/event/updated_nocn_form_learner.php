<?php
/**
 * @package     local_nocnreview
 * @author      Robert Tyrone Cullen
 * @var stdClass $plugin
 */
namespace local_nocnreview\event;
use core\event\base;
defined('MOODLE_INTERNAL') || die();

class updated_nocn_form_learner extends base {
    protected function init(){
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }
    public static function get_name(){
        return "NOCN form updated by learner";
    }
    public function get_description(){
        return "The user with id '".$this->userid."' updated one of their NOCN forms for the course with the id '".$this->courseid."'";
    }
    public function get_url(){
        return new \moodle_url('/local/nocnreview/nocnreview_learner.php?id='.$this->courseid);
    }
    public function get_id(){
        return $this->objectid;
    }
}
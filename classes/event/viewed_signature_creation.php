<?php
/**
 * @package     local_nocnreview
 * @author      Robert Tyrone Cullen
 * @var stdClass $plugin
 */
namespace local_nocnreview\event;
use core\event\base;
defined('MOODLE_INTERNAL') || die();

class viewed_signature_creation extends base {
    protected function init(){
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }
    public static function get_name(){
        return "Signature creation viewed";
    }
    public function get_description(){
        return "The user with id '".$this->userid."' viewed signature creation";
    }
    public function get_url(){
        return new \moodle_url('/local/nocnreview/nocnreview_'.$this->other.'.php?id='.$this->courseid);
    }
    public function get_id(){
        return $this->objectid;
    }
}
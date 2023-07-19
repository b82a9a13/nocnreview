<?php
/**
 * @package     local_nocnreview
 * @author      Robert Tyrone Cullen
 * @var stdClass $plugin
 */
namespace local_nocnreview\event;
use core\event\base;
defined('MOODLE_INTERNAL') || die();

class deleted_signature extends base {
    protected function init(){
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }
    public static function get_name(){
        return "Signature deleted";
    }
    public function get_description(){
        return "The user with id '".$this->userid."' deleted the signature for the user with id '".$this->relateduserid."'";
    }
    public function get_url(){
        return new \moodle_url('/local/nocnreview/nocnreview_admin.php');
    }
    public function get_id(){
        return $this->objectid;
    }
}
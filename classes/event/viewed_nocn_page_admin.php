<?php
/**
 * @package     local_nocnreview
 * @author      Robert Tyrone Cullen
 * @var stdClass $plugin
 */
namespace local_nocnreview\event;
use core\event\base;
defined('MOODLE_INTERNAL') || die();

class viewed_nocn_page_admin extends base {
    protected function init(){
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }
    public static function get_name(){
        return "NOCN admin page viewed";
    }
    public function get_description(){
        return "The user with id '".$this->userid."' viewed the NOCN admin page";
    }
    public function get_url(){
        return new \moodle_url('/local/nocnreview/nocnreview_admin.php');
    }
    public function get_id(){
        return $this->objectid;
    }
}
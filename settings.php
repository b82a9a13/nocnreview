<?php
/**
 * @package     local_nocnreview
 * @author      Robert Tyrone Cullen
 * @var stdClass $plugin
 */

defined('MOODLE_INTERNAL') || die();

if($hassiteconfig){
    $ADMIN->add('localplugins', new admin_externalpage('local_nocnreview', 'Manage NOCN Review',
        $CFG->wwwroot . '/local/nocnreview/nocnreview_admin.php'));
}
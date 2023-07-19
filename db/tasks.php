<?php
// This file is part of the nocnreview plugin
/**
 * @package     local_manuals
 * @author      Robert Tyron Cullen
 * @var stdClass $plugin
 */

defined('MOODLE_INTERNAL') || die();

$tasks = array(
    array(
        'classname' => 'local_nocnreview\task\signed_and_incomplete',
        'blocking' => 0,
        'minute' => '30',
        'hour' => '07',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '1-5'
    )
);
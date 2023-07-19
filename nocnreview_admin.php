<?php
/**
 * @package     local_nocnreview
 * @author      Robert Tyrone Cullen
 * @var stdClass $plugin
 */
require_once(__DIR__.'/../../config.php');

require_login();
use local_nocnreview\lib;
$lib = new lib();

$context = context_system::instance();
require_capability('local/nocnreview:admin', $context);
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/nocnreview/nocnreview_admin.php'));
$PAGE->set_title('NOCN Review Admin');
$PAGE->set_heading('NOCN Review Admin');
$PAGE->set_pagelayout('admin');

echo $OUTPUT->header();
echo("<link rel='stylesheet' href='./classes/css/nocn.css'>");

$template = (object)[
    'resetArray' => $lib->get_sign_user_list(),
    'incompleteArray' => $lib->get_incomplete_user_list(),
    'historyArray' => $lib->get_history_user_list()
];
echo $OUTPUT->render_from_template('local_nocnreview/nocn_admin', $template);

echo("<script src='./classes/js/nocn_admin.js'></script>");
echo $OUTPUT->footer();
\local_nocnreview\event\viewed_nocn_page_admin::create(array('context' => \context_system::instance()))->trigger();
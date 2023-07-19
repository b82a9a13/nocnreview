<?php
$template = (object)[
    'createSign' => get_string('createSign', 'local_nocnreview'),
    'drawSign' => get_string('drawSign', 'local_nocnreview'),
    'clear' => get_string('clear', 'local_nocnreview'),
    'submit' => get_string('submit', 'local_nocnreview')
];
echo $OUTPUT->render_from_template('local_nocnreview/signature',$template);
echo('<script src="./classes/js/signature.js"></script>');
if($type == 'coach'){
    \local_nocnreview\event\viewed_signature_creation::create(array('context' => \context_course::instance($id), 'courseid' => $id, 'other' => 'coach'))->trigger();
} elseif($type == 'learner') {
    \local_nocnreview\event\viewed_signature_creation::create(array('context' => \context_course::instance($id), 'courseid' => $id, 'other' => 'learner'))->trigger();
}
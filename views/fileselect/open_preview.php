<?php

/* Get additional classes for the wrap */
if ($namespace === 'fileselect') {
    $typeClass = wp_attachment_is_image($id) ? 'tm-fileselect_wrap_image' : 'tm-fileselect_wrap_file';
} else {
    $typeClass = trim(do_action(
        "xiphe_themaster_fileselect_getwrapclassfor_$namespace",
        $id
    ));
}

/* Start the List entry */
$HTML->s_li(array(
    'data-namespace' => $namespace,
    'data-id' => $id,
    'data-fullid' => "{$namespace}_{$id}",
    'class' => "tm-fileselect_wrap tmfs_{$namespace}_wrap $typeClass",
    'id' => "tmfs_{$namespace}_id_{$id}"
))
->s_div('.tm-fileselect_buttons_wrap')
->s_div('.tm-fileselect_buttons');

/* Save the buttons for manipulation */
$b = $HTML->sr_div('.tm-fileselect_removewrap');
$b .= $HTML->r_button(__('Remove', 'themaster'), '.button button-secondary tm-fileselect_remove');
$b .= $HTML->r_end();

if ($namespace === 'fileselect') {
    /* Default behavior */
    $b .= $HTML->sr_div('.tm-fileselect_detailswrap');
    $b .= $HTML->r_a(__('Details', 'themaster'), array(
        'class' => 'button button-secondary tm-fileselect_details',
        'href' => sprintf('\./post.php?post=%s&action=edit', $id),
        'target' => '_blank'
    ));
    $b .= $HTML->r_end('.tm-fileselect_detailswrap');
} 
/* Let plugins embed their own detail buttons */
ob_start();
do_action(
    "xiphe_themaster_fileselect_detailbuttonfor_$namespace",
    $id
);
$b .= ob_get_clean();

/* Let others add additional buttons */
$b = apply_filters('xiphe_themaster_fileselect_buttons', $b, $namespace, $id);
if (is_string($b)) {
    echo $b;
}

/* close the button wrap */
$HTML->_end('.tm-fileselect_buttons_wrap');
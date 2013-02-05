<?php

/* Default preview for own attachments */
if (wp_attachment_is_image($id)) {
    $HTML->s_a(array(
        'href' => admin_url('admin-ajax.php?').http_build_query(array(
            'action' => 'tm_fileselect_getfullsize',
            'id' => $namespace.'_'.$id,
            'nonce' => wp_create_nonce('tm-fileselect_fullsize')
        )),
        'class' => 'thickbox tm-fileselect_attachmentwrap',
        'data-id' => $id
    ));
    echo wp_get_attachment_image($id, $size);
    $HTML->end('.thickbox');
} else {
    $HTML->s_span('.tm-fileselect_attachmentwrap|data-id='.$id);
    echo $this->get_link($id);
    $HTML->end();
}
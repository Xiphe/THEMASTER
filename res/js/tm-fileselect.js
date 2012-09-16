/* SLT Image Select script */
var tm_checkedAttachments = [];
jQuery(document).ready(function($) {
    
    // Parse URL variables
    // See: http://papermashup.com/read-url-get-variables-withjavascript/
    function get_url_vars(s) {
        var vars = {};
        var parts = s.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
            vars[key] = value;
        });
        return vars;
    }

    function sortableize() {
        $('.tm-fileselect_buttonwrap').each(function() {
            if ($(this).find('input.tm-fileselect_multiple').val()) {
                $val = $(this).find('input.tm-fileselect_value');
                $(this).siblings('.tm-fileselect_preview').sortable({
                    stop: function() {
                        $val.val($(this).sortable("serialize").replace(/&tm-fileselect_id\[\]=/g, ',').replace(/tm-fileselect_id\[\]=/g, ''));
                    }
                });
                $(this).siblings('.tm-fileselect_preview').disableSelection();
            }
        });
    }

    // Actions for screens with the file select button
    if ($('.tm-fileselect_button').length) {

        // Invoke Media Library interface on button click
        $('.tm-fileselect_button').click(function(e) {
            e.preventDefault();
            if ($(this).siblings('input.tm-fileselect_value').val().length) {
                parent.tm_checkedAttachments = $(this).siblings('input.tm-fileselect_value').val().split(',');
            } else {
                parent.tm_checkedAttachments = [];
            }
            $('html').addClass( 'File' );
            tb_show('', 'media-upload.php?post_id=0&tm-fileselect_field='+
                $(this).siblings('input.tm-fileselect_value').attr('id')+
                '&tm-fileselect_previewsize='+$(this).siblings('input.tm-fileselect_previewsize').val()+
                '&tm-fileselect_validation='+$(this).siblings('input.tm-fileselect_validation').val()+
                '&tm-fileselect_multiple='+$(this).siblings('input.tm-fileselect_multiple').val()+
                '&type=file&TB_iframe=true&test=drrt'
            );
            return false;
        });
    
        // Wipe form values when remove checkboxes are checked
        $('.tm-fileselect_remove').live('click', function(e){
            e.preventDefault();
            var $wrp = $(this).closest('.tm-fileselect_wrap'),
                $inpt = $(this).closest('.tm-fileselect_preview')
                    .siblings('.tm-fileselect_buttonwrap')
                    .find('input.tm-fileselect_value'),
                vals = $inpt.val().split(','),
                id = $wrp.find('.tm-fileselect_attachmentwrap').attr('data-id');

            if(vals.indexOf(id) >= 0) {
                vals.splice(vals.indexOf(id), 1);
            }
            $inpt.val(vals.join(','));
            $wrp.fadeOut(500, function() {
                $(this).remove();
            });
        });


        sortableize();
    }


    
    // Actions for the Media Library overlay
    if ($( "body" ).attr('id') == 'media-upload') {
        
        // Make sure it's an overlay invoked by this plugin
        var parent_doc, parent_src, parent_src_vars, current_tab, multiple;
        parent_doc = parent.document;
        parent_src = parent_doc.getElementById('TB_iframeContent').src;
        parent_src_vars = get_url_vars(parent_src);
        if ('tm-fileselect_field' in parent_src_vars) {
            multiple = false;

            var select_button = '<a href="#" class="tm-fileselect_insert button-secondary">'+tm_fileselect_selectfile+'</a>';
            if (typeof parent_src_vars['tm-fileselect_multiple'] !== 'undefined' &&
                parent_src_vars['tm-fileselect_multiple']
            ) {
                multiple = true;
                select_button = '<input type="checkbox" class="tm-fileselect_checkbox" name="tm-fileselect_checkbox" style="float: right; margin: 0.9em 1.2em 0 0;"/>'+
                    select_button;
            }

            current_tab = $('ul#sidemenu a.current').parent('li').attr('id');
            $( 'ul#sidemenu li#tab-type_url' ).remove();
            $('p.ml-submit').remove();

            switch (current_tab) {
                case 'tab-type':
                    var val = parent_src_vars['tm-fileselect_validation'],
                        accept = [];
                    $.each(val.split('|'), function(k, v) {
                        if(v.indexOf('/') <= -1) {
                            accept.push(v+'/*');
                        } else {
                            accept.push(v);
                        }
                    });
                    $('input[type="file"]').attr('accept', accept.join(','));

                    $.each(['field', 'previewsize', 'validation'], function(k, v) {
                            $('input#async-upload').after($('<input />')
                                .attr('type', 'hidden')
                                .attr('name', 'tm-fileselect'+v)
                                .attr('value', parent_src_vars['tm-fileselect'+v])
                            );
                            uploader.settings.multipart_params['tm-fileselect'+v] = parent_src_vars['tm-fileselect'+v];
                    });

                    // File upload
                    $( 'table.describe tbody tr:not(.submit)' ).remove();
                    //$( 'table.describe tr.submit td.savesend input' ).replaceWith( select_button );
                    $( 'table.describe tr.submit td.savesend input' ).remove();

                    $('table.describe tr.submit td.savesend').prepend(select_button);

                    /* Remove Additional inputs */
                    $('#media-items').on('DOMNodeInserted', function(e) {
                        if ($(e.target).hasClass('pinkynail') &&
                            $(e.relatedNode).hasClass('media-item')
                        ) {
                            var $ctnr = $(e.target).closest('.media-item');
                            window.setTimeout(function() {
                                $ctnr.removeClass('open');
                                $ctnr.children('a.toggle').remove();
                                $ctnr.prepend(select_button);
                                $ctnr.children('table.slidetoggle').css({'display' : 'none'});
                                $ctnr.children('.tm-fileselect_checkbox').attr('checked', 'checked');
                                $ctnr.children('a.tm-fileselect_insert').css({
                                    'display': 'block',
                                    'float':   'right',
                                    'margin':  '7px 20px 0 0'
                                });

                                var id = $ctnr.find('input[type="hidden"]').first().attr('id').replace('type-of-', '');
                                $ctnr.attr('id', 'media-item-'+id);
                                if (parent.tm_checkedAttachments.indexOf(id) < 0) {
                                    parent.tm_checkedAttachments.push(id);
                                }
                            }, 0);
                        }
                    });
                    break;
                case 'tab-library':
                    window.setTimeout(function() {
                        // Media Library
                        $('#media-items .media-item a.toggle').remove();
                        $('#media-items table.slidetoggle').css({'display' : 'none'});
                        $('#media-items .media-item').each( function() {
                            $( this ).prepend( select_button );
                        });
                        $( 'a.tm-fileselect_insert' ).css({
                            'display': 'block',
                            'float':   'right',
                            'margin':  '7px 20px 0 0'
                        });
                    }, 0);
                    break;
                default:
                    break;
            }

            if (multiple) {
                window.setTimeout(function() {
                    $.each(parent.tm_checkedAttachments, function(k, v) {
                        $('#media-item-'+v).find('.tm-fileselect_checkbox').attr('checked', 'checked');
                    });
                }, 0);

                $('.tm-fileselect_checkbox, .media-item').live('click', function(e){
                    var $wrp = $(this).closest('.media-item'),
                        $cbx = $wrp.find('.tm-fileselect_checkbox'),
                        id = $wrp.attr('id');

                    id = id.match( /media\-item\-([0-9]+)/ );
                    id = id[1];


                    if ($cbx.attr('checked') === 'checked') {
                        $cbx.removeAttr('checked');
                        parent.tm_checkedAttachments.splice(
                            parent.tm_checkedAttachments.indexOf(id),
                            1
                        );
                    } else {
                        $cbx.attr('checked', 'checked');
                        if (parent.tm_checkedAttachments.indexOf(id) < 0) {
                            parent.tm_checkedAttachments.push(id);
                        }
                    }
                    e.preventDefault();
                    return false;
                });
            }

            // Select functionality
            $('a.tm-fileselect_insert').live('click', function() {
                var id;
                if ( $( this ).parent().attr( 'class' ) == 'savesend' ) {
                    id = $( this ).siblings( '.del-attachment' ).attr( 'id' );
                    id = id.match( /del_attachment_([0-9]+)/ );
                    id = id[1];
                } else {
                    id = $( this ).parent().attr( 'id' );
                    id = id.match( /media\-item\-([0-9]+)/ );
                    id = id[1];
                }
                if (multiple) {
                    if (parent.tm_checkedAttachments.indexOf(id) < 0) {
                        parent.tm_checkedAttachments.push(id);
                    }
                    id = parent.tm_checkedAttachments;
                }
                parent.tm_fileselect_select_item(id, parent_src_vars['tm-fileselect_field']);
                return false;
            });
        }
    
    }
});

/**
 * Function that gets called when the media uploader ends by clicking
 * A "Select"-Button.
 *
 * @param  mixed  item_id   the id or array of id's of the selected media
 * @param  string field_id  the id of the targeted input field.
 * @return void
 */
function tm_fileselect_select_item(item_id, field_id) {
    var $ = jQuery,
    /* the target input */
    $field = $('#'+field_id),
    /* the container of preview images */
    $preview_div = $field.parent().siblings('.tm-fileselect_preview'),
    /* the size of preview images */
    preview_size = $field.siblings('.tm-fileselect_previewsize').val(),
    /* the nonce */
    nonce = $field.siblings('.tm-fileselect_nonce').val();

    /* Load preview image/s */
    $preview_div.html('').load(ajaxurl, {
        id:     item_id,
        size:   preview_size,
        action: 'tm_fileselect_getfile',
        nonce: nonce
    });

    /* convert list to string if its an array */
    if (typeof item_id === 'array') {
        item_id = item_id.join(',');
    }

    /* Pass ID/s to form field */
    $field.val(item_id);

    /* Close interface down */
    tb_remove();
    jQuery('html').removeClass('File');
}
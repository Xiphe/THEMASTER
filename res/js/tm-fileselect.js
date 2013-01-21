if(typeof xiphe==='undefined'){var xiphe={};}xiphe=jQuery.extend(true,{},xiphe,{themaster:{fileselect:(function($){var

    /* PRIVATE VARS */
    self = this,
    $select_button,
    $single_select,
    parent_src_vars


    /* PUBLIC VARS */;
    this.checkedAttachments = [];

    /* PRIVATE METHODS */ var

    /**
     * Initiation
     * 
     * @return {void}
     */
    _init = function() {

    },

    /**
     * Second initiation when the document is ready
     * 
     * @return {void}
     */
    _ready = function() {
        _buttonAnimation();
        _initFileselectButton();
        _initSortables();

        if ($("body").attr('id') === 'media-upload') {
            _manipulateMediaUpload();
        }
    },


    /**
     * Injects check-boxes etcetera.
     * 
     * @param  {object} $target the target list
     * @return {void}
     */
    _manipulateColumns = function($target) {
        window.setTimeout(function() {
            if (typeof $target === 'undefined') {
                $target = $('.media-item');
            }
            $target.each(function() {
                if ($(this).hasClass('tm-changed')) {
                    return;
                }
                
                $(this).find('tr.url, tr.align, tr.image-size').css({'display' : 'none'});
                $(this).find('tr.submit .savesend input.button[type="submit"]').css({'display' : 'none'});
                $(this).find('tr.submit .savesend .del-link').before('<button class="button tm-savechanges" style="margin-right: 5px;">'+xiphe.themaster.fileselecttext.save+'</button>');

                $(this).prepend($select_button.clone());

                $(this).find('a.tm-fileselect_insert').css({
                    'display': 'block',
                    'float':   'right',
                    'margin':  '7px 20px 0 0'
                });
                $(this).addClass('tm-changed');
            });
        }, 0);
    },

    /**
     * Things to do in the upload tab
     * 
     * @return {void}
     */
    _manipulateUploadTab = function() {
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

        $('table.describe tr.submit td.savesend').prepend($select_button.clone());

        /* Remove Additional inputs */
        $('#media-items').on('DOMNodeInserted', function(e) {
            if ($(e.target).hasClass('pinkynail') &&
                $(e.relatedNode).hasClass('media-item')
            ) {
                _manipulateColumns($(e.target).closest('.media-item'));
            }
        });
    },

    /**
     * Sets/Resets the basic buttons that will be appended to the uploader.
     * 
     * @param  {boolean} multiple
     * @return {void}
     */
    _prepareButtons = function(multiple) {
        $select_button = $('<a/>')
                .attr('href', '#')
                .addClass('tm-fileselect_insert button-secondary')
                .html(xiphe.themaster.fileselecttext.select);

        if (multiple) {
            multiple = true;

            $select_button.removeClass('button-secondary').addClass('button-primary')

            $single_select = $('<div/>')
                .addClass('tm-fileselect_allandsavewrap')
                .css({
                    'margin' : '10px 21px 10px 10px',
                    'text-align' : 'right'
                })
                .append($select_button)
                .append(
                    $('<a/>').addClass('button-secondary tm-fileselect_all')
                        .html(xiphe.themaster.fileselecttext.selectAll)
                        .css('margin-left', '10px')
                );

            $select_button = $('<input/>')
                .attr({
                    'type': 'checkbox',
                    'class': 'tm-fileselect_checkbox',
                    'name': 'tm-fileselect_checkbox'
                }).css({
                    'float': 'right',
                    'margin': '0.9em 1.2em 0 0'
                });
        } else {
            $single_select = $('');
        }
    },

    _addAllSave = function() {
        $('#media-items').after($single_select);
    }

    /**
     * The actual change to the fileupload dialoge
     * 
     * @return {void}
     */
    _manipulateMediaUpload = function() {
        /*
         * Initiate Vars.
         */
        var parent_doc, parent_src, current_tab, multiple;

        parent_doc = parent.document;
        parent_src = parent_doc.getElementById('TB_iframeContent').src;
        parent_src_vars = _getUrlVars(parent_src);

        /*
         * Stop when this is not called by a fileselect button.
         */
        if (!('tm-fileselect_field' in parent_src_vars)) {
            return false;
        }

        /*
         * Determine if multiple uploads are allowed
         */
        if (typeof parent_src_vars['tm-fileselect_multiple'] !== 'undefined' &&
            parent_src_vars['tm-fileselect_multiple']
        ) {
            multiple = true;
        } else {
            multiple = false;
        }

        /*
         * reset Button sources.
         */
        _prepareButtons(multiple);

        /*
         * Set the current tab title
         */
        current_tab = $('ul#sidemenu a.current').parent('li').attr('id');

        /*
         * Remove distracting elements.
         */
        $('ul#sidemenu li#tab-type_url, div#gallery-settings, p.ml-submit').remove();

        /*
         * Do additional stuff for specific tabs.
         */
        switch (current_tab) {
            case 'tab-type':
                _manipulateUploadTab();
                break;
            case 'tab-library':
            case 'tab-gallery':
                _manipulateColumns();
                if (multiple) {
                    _addAllSave();
                }
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

            $('.tm-fileselect_checkbox, .media-item').live('click', function(e) {

                if ($(e.target).closest('table.slidetoggle').length
                 || $(e.target).hasClass('toggle')
                ) {
                    return true;
                }

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
        } // ENDIF multiple

        // Select functionality
        $('a.tm-fileselect_insert').live('click', function(e) {
            var id;
            if ( $( this ).parent().attr( 'class' ) == 'savesend' ) {
                id = $( this ).siblings( '.del-attachment' ).attr( 'id' );
                id = id.match(/del_attachment_([0-9]+)/);
                id = id[1];
            } else {
                id = $( this ).closest('.media-item').find('td.savesend .del-link').attr( 'onclick' );
                id = id.match(/del_attachment_([0-9]+)/);
                id = id[1];
            }
            if (multiple) {
                if (parent.tm_checkedAttachments.indexOf(id) < 0) {
                    parent.tm_checkedAttachments.push(id);
                }
                id = parent.tm_checkedAttachments;
            }

            parent.xiphe.themaster.fileselect.selectItem(id, parent_src_vars['tm-fileselect_field']);
            return false;
        });

        $('.submit button.tm-savechanges').live('click', function(e) {
            if (current_tab === 'tab-type') {
                return true;
            }

            var $cntr  = $(this).closest('.media-item'),
                cID    = $cntr.attr('id'),
                chckd  = $cntr.find('.tm-fileselect_checkbox').attr('checked')
                $frm   = $(this).closest('form.media-upload-form'),
                $inpts = $cntr.find('input')
                    .add($cntr.find('textarea'))
                    .add($cntr.find('select'))
                    .add($frm.find('#_wpnonce'));

            e.preventDefault();
            
            $.post($frm.attr('action'), $inpts.serialize(), function(c) {
                s = self.regexIndexOf(c, /<body(.*)>/i);
                e = self.regexIndexOf(c, /<\/body>/i);
                c = c.substr(s,e-s).replace(/<!--(.*)-->/g, '').replace(/<body(.*)>/g, '').replace(/(\r\n)|(\r)/g, '').replace(/\t/g, '');
                
                var $ncntr = $(c).find('#'+cID);
                if ($ncntr.length) {
                    $cntr.replaceWith($ncntr);
                    _manipulateColumns($ncntr);
                    window.setTimeout(function() {
                        if (chckd === 'checked') {
                            $ncntr.find('.tm-fileselect_checkbox').attr('checked', 'checked');
                        }   
                    }, 0);
                }
            });
        });
    },

    /**
     * Initiate actions for the fileselect buttons
     * 
     * @return {void}
     */
    _initFileselectButton = function() {
        /* Invoke Media Library interface on button click */
        $('.tm-fileselect_button').click(function(e) {
            e.preventDefault();
            if ($(this).siblings('input.tm-fileselect_value').val().length) {
                parent.tm_checkedAttachments = $(this).siblings('input.tm-fileselect_value').val().split(',');
            } else {
                parent.tm_checkedAttachments = [];
            }
            $('html').addClass( 'File' );
            tb_show('', xiphe.themaster.fileselectbaseurl+
                '&tm-fileselect_field='+$(this).siblings('input.tm-fileselect_value').attr('id')+
                '&tm-fileselect_previewsize='+$(this).siblings('input.tm-fileselect_previewsize').val()+
                '&tm-fileselect_validation='+$(this).siblings('input.tm-fileselect_validation').val()+
                '&tm-fileselect_multiple='+$(this).siblings('input.tm-fileselect_multiple').val()+
                '&type=file&TB_iframe=true&test=drrt'
            );
            return false;
        });
    
        /* Wipe form values when remove checkboxes are checked */
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
    },

    /**
     * Parses the get query from an url
     * 
     * @param  {string} s url
     * @return {object}   get variables
     */
    _getUrlVars = function(s) {
        var vars = {};
        var parts = s.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
            vars[key] = value;
        });
        return vars;
    },

    /**
     * Adds active state animations to FileSelect Buttons.
     * 
     * @return {void}
     */
    _buttonAnimation = function() {
        $(document).mousedown(function(e) {
            $('.tm-fileselect_buttons_wrap').find('a, button').removeClass('active');
            if ($(e.target).hasClass('button') && $(e.target).closest('.tm-fileselect_buttons').length) {
                $(e.target).addClass('active');
            }
        });
    },

    /**
     * Initiates the jquery ui sortable module on fileselect lists.
     * 
     * @return {void}
     */
    _initSortables = function() {
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

    /* PUBLIC METHODS */;

    /**
     * Return the position of the first occurrence of the given regex
     * in the given string.
     * 
     * @param  {string}  str      haystack
     * @param  {RegExp}  regex    needle
     * @param  {integer} startpos offset
     * @return {integer}
     */
    this.regexIndexOf = function(str, regex, startpos) {
        var indexOf = str.substring(startpos || 0).search(regex);
        return (indexOf >= 0) ? (indexOf + (startpos || 0)) : indexOf;
    };

    /**
     * Function that gets called when the media uploader ends by clicking
     * A "Select"-Button.
     *
     * @param  {mixed}  item_id   the id or array of id's of the selected media
     * @param  {string} field_id  the id of the targeted input field.
     * @return {void}
     */
    this.selectItem = function(item_id, field_id) {
        var $ = jQuery,
        /* the target input */
        $field = $('#'+field_id),
        /* the container of preview images */
        $preview_div = $field.parent().next('.tm-fileselect_preview'),
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
        }, function() {
            $preview_div.trigger('received_items');
        });

        /* convert list to string if its an array */
        if (typeof item_id === 'array') {
            item_id = item_id.join(',');
        }

        /* Pass ID/s to form field */
        $field.val(item_id);

        /* Close interface down */
        tb_remove();
        $('html').removeClass('File');
    }

;(function(){_init();$(document).ready(_ready);})();return this;})(jQuery)}});
/**
 * FileSelect plugin for the Wordpress media uploader.
 * Meant to be used aside with the Xiphe\THEMASTER\classes\FileSelect PHP class
 *
 * [Original Plugin](http://sltaylor.co.uk/wordpress/plugins/slt-file-select/)
 *
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 *
 * @author  Hannes Diercks <info@xiphe.net>
 *          Original Plugin by Steve Taylor (http://sltaylor.co.uk)
 * @license GPLv2
 */
/*global ajaxurl, uploader, tb_show, tb_remove, console */
if(typeof xiphe==='undefined'){var xiphe={};}xiphe=jQuery.extend(true,{},xiphe,{themaster:{fileselect:(function($){var

    /* PRIVATE VARS */
    self = this,
    $select_button,
    $single_select_wrap,
    $single_select_btn,
    $allButton,
    parentFS,
    selectAllClicked = false


    /* PUBLIC VARS */;
    this.$current = false;
    this.checkedAttachments = [];
    this.foreignAttachments = {};
    this.$selection = $('');
    this.previewsize = false;
    this.validation = false;
    this.validation_nonce = false;
    this.multiple = false;
    this.parent_id = false;

    /* PRIVATE METHODS */ var

    /**
     * Initiation
     *
     * @return {void}
     */
    _init = function() {
        if (parent && typeof parent.xiphe !== 'undefined') {
            parentFS = parent.xiphe.themaster.fileselect;
        }
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
    _manipulateColumns = function($target, cb) {
        var thiz = this;

        /*
         * Wait for other tasks to finish
         */
        window.setTimeout(function() {
            /*
             * Default target to .media-item
             */
            if (typeof $target === 'undefined') {
                $target = $('.media-item');
            }

            /*
             * Loop through targets
             */
            $target.each(function() {
                /*
                 * Ignore previously changed items.
                 */
                if ($(this).hasClass('tm-fileselect_changed')) {
                    return;
                }
                
                /*
                 * Hide unnecessarily stuff
                 */
                $(this).find('tr.url, tr.align, tr.image-size').css({'display' : 'none'});
                $(this).find('tr.submit .savesend input.button[type="submit"]').css({'display' : 'none'});

                /*
                 * Add a save button to the detail view
                 */
                $(this).find('tr.submit .savesend .del-link').before('<button class="button tm-savechanges" style="margin-right: 5px;">'+xiphe.themaster.fileselecttext.save+'</button>');
                
                /*
                 * Add the select button / check-box
                 */
                $(this).prepend($select_button.clone());

                /*
                 * Add Class to prevent double changing.
                 */
                $(this).addClass('tm-fileselect_changed');
            });

            if (typeof cb === 'function') {
                window.setTimeout(function() {
                    cb.call(thiz);
                }, 0);
            }
        }, 0);
    },

    /**
     * Things to do in the upload tab
     *
     * @return {void}
     */
    _manipulateUploadTab = function() {
        /*
         * initiate variables
         */
        var accept = [],
            saveAppended = false;

        /*
         * populate the accept var
         */
        $.each(parentFS.validation.split('|'), function(k, v) {
            if(v.indexOf('/') <= -1) {
                accept.push(v+'/*');
            } else {
                accept.push(v);
            }
        });

        /*
         * Set accept attribute to the file uploader.
         */
        $('input[type="file"]').attr('accept', accept.join(','));

        /*
         * Set validation and validation nonce to be passed to php aside the file
         */
        $.each(['validation', 'validation_nonce', 'parent_id'], function(i, v) {
            $('input#async-upload').after($('<input />')
                .attr('type', 'hidden')
                .attr('name', 'tm-fileselect_'+v)
                .attr('value', parentFS[v])
            );
            uploader.settings.multipart_params['tm-fileselect_'+v] = parentFS[v];
        });

        /*
         * Remove unnecessary stuff
         */
        $('table.describe tbody tr:not(.submit)').remove();
        $('table.describe tr.submit td.savesend input').remove();

        /*
         * Manipulate media-items if some are present (Browser-Uploader)
         */
        $('#media-items .media-item').each(function() {
            _manipulateColumns.call(this, $(this), function() {
                $(this).click();
            });
            if (!saveAppended) {
                $('#media-items').after($single_select_wrap);
                saveAppended = true;
            }
        });

        /*
         * Manipulate added nodes (Multidata Uploader).
         */
        $('#media-items').on('DOMNodeInserted', function(e) {
            if ($(e.target).hasClass('pinkynail') &&
                $(e.relatedNode).hasClass('media-item')
            ) {
                /*
                 * Manipulate the newly added row
                 */
                _manipulateColumns.call(e.target, $(e.target).closest('.media-item'), function() {
                    /*
                     * New uploads are checked by default
                     */
                    $(this).closest('.media-item').click();
                });


                if (!saveAppended) {
                    $('#media-items').after($single_select_wrap);
                    saveAppended = true;
                }
            }
        });
    },

    /**
     * Sets/Resets the basic buttons that will be appended to the uploader.
     *
     * @return {void}
     */
    _prepareButtons = function() {
        $select_button = $('<a/>')
                .attr('href', '#')
                .addClass('tm-fileselect_insert button-secondary')
                .html(xiphe.themaster.fileselecttext.select);

        if (parentFS.multiple) {

            /*
             * Emphasize the select Button
             */
            $single_select_btn = $select_button.removeClass('button-secondary').addClass('button-primary');

            /*
             * Generate the Select All Button.
             */
            $allButton = $('<a/>').addClass('button-secondary tm-fileselect_all')
                .html(xiphe.themaster.fileselecttext.selectAll)
                .css('margin-left', '10px')
                .click(_selectAllCB);

            /*
             * Put the single select div together.
             */
            $single_select_wrap = $('<div/>')
                .addClass('tm-fileselect_allandsavewrap')
                .css({
                    'margin' : '10px 21px 10px 10px',
                    'text-align' : 'right'
                })
                .append($single_select_btn)
                .append($allButton);

            /*
             * Original Select button now is a checkbox
             */
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
            $single_select_wrap = $('');
            $select_button.css({
                'display': 'block',
                'float':   'right',
                'margin':  '7px 20px 0 0'
            });
        }
    },

    /**
     * If all rows are selected, change the Select all button to
     * the Remove Selection button
     *
     * @return {void}
     */
    _checkSelectAllButton = function() {
        window.setTimeout(function() {
            if (!$('.tm-fileselect_checkbox:not(:checked)').length) {
                $allButton.addClass('tm-fileselect_unselect')
                    .html(xiphe.themaster.fileselecttext.unselectAll);
            }
        });
    },

    /**
     * Callback function for when the Select all/Remove selection button
     * is pressed
     *
     * @param  {event}  e
     * @return {void}
     */
    _selectAllCB = function(e) {
        e.preventDefault();
        selectAllClicked = true;
        if ($(this).hasClass('tm-fileselect_unselect')) {
            $('.tm-fileselect_checkbox:checked').click();
            $(this).removeClass('tm-fileselect_unselect')
                .html(xiphe.themaster.fileselecttext.selectAll);
        } else {
            $('.tm-fileselect_checkbox:not(:checked)').click();
            $(this).addClass('tm-fileselect_unselect')
                .html(xiphe.themaster.fileselecttext.unselectAll);
        }
        selectAllClicked = false;
    },

    /**
     * The actual change to the fileupload dialoge
     *
     * @return {void}
     */
    _manipulateMediaUpload = function() {
        /*
         * Validate the parent
         */
        if (!parent.document.getElementsByClassName('tm-fileSelect_active').length) {
            return false;
        }

        /*
         * Initiate Vars.
         */
        var current_tab;

        /*
         * reset Button sources.
         */
        _prepareButtons();

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
                if (parentFS.multiple) {
                    $('#media-items').after($single_select_wrap);
                }
                break;
            default:
                break;
        }

        /*
         * Delete id's from list if attachment got deleted.
         */
        $('.del-attachment a[id^="del"]').live('click', function() {
            var id = parseInt($(this).attr('id').match(/del\[([0-9]+)\]/)[1], 10);
            if (parentFS.checkedAttachments.indexOf(id) >= 0) {
                parentFS.checkedAttachments.splice(
                    parentFS.checkedAttachments.indexOf(id),
                    1
                );
            }
        });

        if (parentFS.multiple) {
            /*
             * Check active boxes
             */
            window.setTimeout(function() {
                $.each(parentFS.checkedAttachments, function(k, v) {
                    $('#media-item-'+v).find('.tm-fileselect_checkbox').attr('checked', 'checked');
                });
            }, 0);

            /*
             * Change Select all button to Remove selection button if all boxes are checked.
             */
            _checkSelectAllButton();

            /*
             * The selection process.
             * Live because element may be reloaded through detail adjustments (new name etc.)
             */
            $('.tm-fileselect_checkbox, .media-item').live('click', _checkboxClicked);

            $single_select_btn.click(parentFS.selectItems);
        } else {
            /*
             * Single.
             */
            $('a.tm-fileselect_insert').live('click', _selectSingleClicked);
        }
    },

    /**
     * Callback for select buttons in single selections.
     *
     * @param  {Event} e
     * @return {boolean}
     */
    _selectSingleClicked = function(e) {
        e.preventDefault();

        /*
         * Extract the id from the current row.
         */
        var id;
        if ($(this).parent().attr('class') === 'savesend') {
            id = $(this).siblings('.del-attachment').attr('id');
        } else {
            id = $(this).closest('.media-item').find('td.savesend .del-link').attr('onclick');
        }
        id = parseInt(id.match(/del_attachment_([0-9]+)/)[1], 10);

        /*
         * Pass it to the parent
         */
        parentFS.checkedAttachments = [id];

        /*
         * Call parent to update.
         */
        parentFS.selectItems();
        return false;
    },

    /**
     * Callback for when a check-box or container of a check-box has been clicked.
     *
     * @param  {Event} e
     * @return {void}
     */
    _checkboxClicked = function(e) {
        /*
         * ignore clicks to the detail-view toggle
         */
        if ($(e.target).closest('table.slidetoggle').length ||
            $(e.target).hasClass('toggle')
        ) {
            return true;
        }

        // e.preventDefault();

        /*
         * initiate variables
         */
        var $wrp = $(this).closest('.media-item'),
            $cbx = $wrp.find('.tm-fileselect_checkbox'),
            id;

        /*
         * dig for the actual id
         */
        if ($(this).parent().attr('class') === 'savesend') {
            id = $(this).siblings('.del-attachment').attr('id');
        } else {
            id = $(this).closest('.media-item').find('td.savesend .del-link').attr('onclick');
        }
        id = parseInt(id.match(/del_attachment_([0-9]+)/)[1], 10);


        /*
         * Get current state.
         */
        var checked = $cbx.is(':checked');

        if (e.target !== $cbx[0]) {
            /*
             * Box was clicked - simulate checkbox click.
             */
            if (checked) {
                $cbx.removeAttr('checked');
            } else {
                $cbx.attr('checked', 'checked');
            }
            checked = !checked;
        } else if(selectAllClicked) {
            /*
             * Select all button was clicked - state is negative.
             */
            checked = !checked;
        }
        
        if (checked) {
            /*
             * Add id to selection
             */
            if (parentFS.checkedAttachments.indexOf(id) < 0) {
                parentFS.checkedAttachments.push(id);
            }
        } else {
            /*
             * Remove id from selection
             */
            if (parentFS.checkedAttachments.indexOf(id) >= 0) {
                parentFS.checkedAttachments.splice(
                    parentFS.checkedAttachments.indexOf(id),
                    1
                );
            }
        }
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

            self.setScope(this);

            /*
             * Start the fileSelect interface
             */
            $('html').addClass('tm-fileSelect_active');
            tb_show('', xiphe.themaster.fileselectbaseurl+'&type=file&TB_iframe=true');
            return false;
        });
    
        /*
         * Remove an entry if the remove button on the preview is clicked
         */
        $('.tm-fileselect_remove').live('click', function(e){
            e.preventDefault();
            /*
             * initiate variables
             */
            var $wrp = $(this).closest('.tm-fileselect_wrap'),
                $btn = $(this).closest('.tm-fileselect_fullwrap').find('.tm-fileselect_button');
            self.removeAttachment.call($btn, $wrp.attr('fullid'));
        });
    },

    /**
     * Adds active state animations to FileSelect Buttons.
     *
     * @return {void}
     */
    _buttonAnimation = function() {
        $(document).mousedown(function(e) {
            $('.tm-fileselect_removewrap, .tm-fileselect_detailswrap').find('a, button').removeClass('active');
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
                var $val = $(this).find('input.tm-fileselect_value');
                $(this).siblings('.tm-fileselect_preview').sortable({
                    stop: function() {
                        $val.val($(this).sortable("serialize").replace(/&tm-fileselect_id\[\]=/g, ',').replace(/tm-fileselect_id\[\]=/g, ''));
                    }
                });
                $(this).siblings('.tm-fileselect_preview').disableSelection();
            }
        });
    },

    _mergeSelections = function(namespace, data) {
        if (typeof namespace === 'undefined' || namespace == null) {
            namespace = '--';
        }

        var vals = self.$selection.val().split(','),
            has = [],
            newVals = [];

        $.each(vals, function(k, val) {
            if ((val+'').indexOf(namespace) === 0) {
                var id = parseInt(''+val.replace(namespace + '_', ''), 10);

                if (data.indexOf(id) > -1) {
                    newVals.push(val);
                    has.push(id);
                }
            } else {
                newVals.push(val);
            }
        });

        $.each(data, function(k, id) {
            if (has.indexOf(id) === -1) {
                newVals.push(namespace + '_' + id);
            }
        });

        self.$selection.val(newVals.join(','));
    }

    /* PUBLIC METHODS */;

    this.setScope = function(elm) {
        if (!$(elm).hasClass('tm-fileselect_button')) {
            return false;
        }

        if (self.$current && self.$current.length && self.$current[0] === elm) {
            return true;
        }

        self.$current = $(elm);

        /*
         * Point to the element that will receive the selected ids
         */
        self.$selection = $(elm).siblings('input.tm-fileselect_value');

        /*
         * Initiate/Reset options.
         */
        self.previewsize = $(elm).siblings('input.tm-fileselect_previewsize').val();
        self.validation = $(elm).siblings('input.tm-fileselect_validation').val();
        self.validation_nonce = $(elm).siblings('input.tm-fileselect_validation_nonce').val();
        self.parent_id = $(elm).siblings('input.tm-fileselect_parent_id').val();
        self.multiple = $(elm).siblings('input.tm-fileselect_multiple').val();
        self.foreign = $(elm).siblings('input.tm-fileselect_foreign').val();

        /*
         * Reset the working list of attachment ids
         */
        self.checkedAttachments = [];
        if (self.$selection.val().length) {
            $(self.$selection.val().split(',')).each(function() {
                var attachment = self.getAttachementData(this);

                if (attachment.namespace === 'fileselect') {
                    self.checkedAttachments.push(attachment.id);
                } else {
                    if (typeof self.foreignAttachments[attachment.namespace] === 'undefined') {
                        self.foreignAttachments[attachment.namespace] = [];
                    }

                    self.foreignAttachments[attachment.namespace].push(attachment.id);
                }
            });
        }
        return true;
    };

    this.getAttachementData = function(fullid) {
        var r = {},
            namespacePrt = fullid.match(/[a-z_]+/)[0];
        r.namespace = namespacePrt.substring(0, namespacePrt.length-1);
        r.id = parseInt(fullid.replace(namespacePrt, ''), 10);

        return r;
    };

    this.removeAttachment = function(fullid) {
        /* Make sure we can set right scope */
        if (!$(this).hasClass('tm-fileselect_button')) {
            return false;
        } else {
            /* and set it */
            self.setScope(this);
        }

        /* Ensure presence of fullid */
        if (typeof fullid === 'undefined') {
            return;
        }

        /* Initiate vars */
        var attachment = self.getAttachementData(fullid),
            index = -1;

        /* Remove native attachments from internal selection */
        if (attachment.namespace === 'fileselect') {
            index = self.checkedAttachments.indexOf(attachment.id);
            if (index > -1) {
                self.checkedAttachments.splice(index, 1);
            }

        /* Remove foreign attachments from internal selection */
        } else if (typeof self.foreignAttachments[attachment.namespace] === 'undefined') {
            index = self.foreignAttachments[attachment.namespace].indexOf(attachment.id);
            self.foreignAttachments[attachment.namespace].splice(index, 1);
        }

        /* Try to find an element with the id and remove it */
        $('#tmfs_'+attachment.namespace+'_id_'+attachment.id).fadeOut(function() {
            $(this).remove();
        }, 500);

        /* Remove the id from input */
        var vals = self.$selection.val().split(',');
        /* If the id exists - remove it from selection. */
        if(vals.indexOf(fullid) >= 0) {
            vals.splice(vals.indexOf(fullid), 1);
        }
        /* Put the new value into the $selection */
        self.$selection.val(vals.join(','));
    };

    /**
     * Register additional attachments handled by plug-ins
     *
     * @param {string} namespace the plugin namespace
     * @param {array}  data      id's of attachments
     */
    this.addForeignAttachment = function(namespace, data) {
        if (!self.setScope(this)) {
            return false;
        }

        self.foreignAttachments[namespace] = data;

        self.updateForeignAttachements.call(this);
    };

    /**
     * Get additional attachments handled by plug-ins
     *
     * @param {string}  namespace the plugin namespace
     * @param {integer} i         optional index of specific attachment
     */
    this.getForeignAttachment = function(namespace, i) {
        if (!self.setScope(this)) {
            return false;
        }

        if (typeof self.foreignAttachments[namespace] === 'undefined') {
            self.foreignAttachments[namespace] = [];
        }

        if (typeof i === 'undefined' || i === null) {
            return self.foreignAttachments[namespace];
        } else if (typeof self.foreignAttachments[namespace][i] !== 'undefined') {
            return self.foreignAttachments[namespace][i];
        } else {
            return [];
        }
    };

    /**
     * merge the foreign attachments and the native ones.
     *
     * @return {void}
     */
    this.updateForeignAttachements = function() {
        if (!self.setScope(this)) {
            return false;
        }

        $.each(self.foreignAttachments, function(namespace, data) {
            _mergeSelections(namespace, data);
        });
    };

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
     * @return {void}
     */
    this.selectItems = function() {

        /*
         * initiate variables.
         */
        var $preview = self.$selection.parent().next('.tm-fileselect_preview');

        /*
         * Set the selection
         */
        _mergeSelections('fileselect', self.checkedAttachments);

        /*
         * Update the preview
         */
        $preview
            .html('')
            .addClass('tm-fileselect_loading')
            .load(ajaxurl, {
                id:     self.$selection.val(),
                size:   self.previewsize,
                action: 'tm_fileselect_getfile',
                parent_id: self.parent_id,
                nonce:  self.$selection.siblings('.tm-fileselect_nonce').val()
            }, function() {
                $preview
                    .removeClass('tm-fileselect_loading')
                    .trigger('received_items');
            });

        /*
         * Close FileSelect
         */
        tb_remove();
        $('html').removeClass('tm-fileSelect_active');
    }

/* initiation */
;(function(){_init();$(document).ready(_ready);})();return this;})(jQuery)}});
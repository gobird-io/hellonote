/**
 * HelloNote Admin JavaScript
 */

(function($) {
    'use strict';

    const HelloNote = {
        /**
         * Initialize the plugin
         */
        init: function() {
            this.bindEvents();
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            // Add note form submission
            $('#hellonote-add-form').on('submit', this.handleAddNote.bind(this));

            // Delete note
            $(document).on('click', '.hellonote-delete', this.handleDeleteNote.bind(this));

            // Edit note
            $(document).on('click', '.hellonote-edit', this.handleEditNote.bind(this));

            // Update note (dynamically created form)
            $(document).on('submit', '.hellonote-update-form', this.handleUpdateNote.bind(this));

            // Cancel edit
            $(document).on('click', '.hellonote-cancel-edit', this.handleCancelEdit.bind(this));
        },

        /**
         * Show message to user
         */
        showMessage: function(message, type) {
            const messageHtml = '<div class="hellonote-message ' + type + '">' + message + '</div>';
            $('.hellonote-container').prepend(messageHtml);

            // Auto-remove after 5 seconds
            setTimeout(function() {
                $('.hellonote-message').fadeOut(300, function() {
                    $(this).remove();
                });
            }, 5000);
        },

        /**
         * Handle add note form submission
         */
        handleAddNote: function(e) {
            e.preventDefault();

            const $form = $(e.target);
            const content = $('#note-content').val();

            // Show loading state
            $form.addClass('hellonote-loading');

            // Send AJAX request
            $.ajax({
                url: hellonoteAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'hellonote_add',
                    nonce: hellonoteAjax.nonce,
                    content: content
                },
                success: function(response) {
                    if (response.success) {
                        HelloNote.showMessage(response.data.message, 'success');
                        // Clear form
                        $form[0].reset();
                        // Reload page to show new note
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        HelloNote.showMessage(response.data.message, 'error');
                    }
                },
                error: function() {
                    HelloNote.showMessage(hellonoteAjax.errorOccurred, 'error');
                },
                complete: function() {
                    $form.removeClass('hellonote-loading');
                }
            });
        },

        /**
         * Handle delete note
         */
        handleDeleteNote: function(e) {
            e.preventDefault();

            const noteId = $(e.target).data('id');
            const $noteItem = $('.hellonote-item[data-id="' + noteId + '"]');

            // Confirm deletion
            if (!confirm(hellonoteAjax.deleteConfirm)) {
                return;
            }

            // Show loading state
            $noteItem.addClass('hellonote-loading');

            // Send AJAX request
            $.ajax({
                url: hellonoteAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'hellonote_delete',
                    nonce: hellonoteAjax.nonce,
                    id: noteId
                },
                success: function(response) {
                    if (response.success) {
                        HelloNote.showMessage(response.data.message, 'success');
                        // Remove note from DOM
                        $noteItem.fadeOut(300, function() {
                            $(this).remove();
                            // Check if no notes left
                            if ($('.hellonote-item').length === 0) {
                                $('#hellonote-notes').html('<p class="hellonote-empty">' + hellonoteAjax.noNotes + '</p>');
                            }
                        });
                    } else {
                        HelloNote.showMessage(response.data.message, 'error');
                        $noteItem.removeClass('hellonote-loading');
                    }
                },
                error: function() {
                    HelloNote.showMessage(hellonoteAjax.errorOccurred, 'error');
                    $noteItem.removeClass('hellonote-loading');
                }
            });
        },

        /**
         * Handle edit note
         */
        handleEditNote: function(e) {
            e.preventDefault();

            const noteId = $(e.target).data('id');
            const $noteItem = $('.hellonote-item[data-id="' + noteId + '"]');

            // Show loading state
            $noteItem.addClass('hellonote-loading');

            // Get note data
            $.ajax({
                url: hellonoteAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'hellonote_get',
                    nonce: hellonoteAjax.nonce,
                    id: noteId
                },
                success: function(response) {
                    if (response.success) {
                        HelloNote.showEditForm($noteItem, response.data.note);
                    } else {
                        HelloNote.showMessage(response.data.message, 'error');
                    }
                },
                error: function() {
                    HelloNote.showMessage(hellonoteAjax.errorOccurred, 'error');
                },
                complete: function() {
                    $noteItem.removeClass('hellonote-loading');
                }
            });
        },

        /**
         * Show edit form
         */
        showEditForm: function($noteItem, note) {
            const editFormHtml = '<div class="hellonote-edit-form">' +
                '<form class="hellonote-update-form" data-id="' + note.id + '">' +
                '<textarea class="large-text" name="content" rows="5" required>' + this.escapeHtml(note.content) + '</textarea>' +
                '<div class="button-group">' +
                '<button type="submit" class="button button-primary">' + hellonoteAjax.updateButton + '</button>' +
                '<button type="button" class="button hellonote-cancel-edit">' + hellonoteAjax.cancelButton + '</button>' +
                '</div>' +
                '</form>' +
                '</div>';

            // Hide existing content and actions
            $noteItem.find('.hellonote-content, .hellonote-actions').hide();

            // Remove any existing edit forms
            $noteItem.find('.hellonote-edit-form').remove();

            // Add edit form
            $noteItem.append(editFormHtml);
        },

        /**
         * Handle update note
         */
        handleUpdateNote: function(e) {
            e.preventDefault();

            const $form = $(e.target);
            const noteId = $form.data('id');
            const content = $form.find('textarea[name="content"]').val();
            const $noteItem = $('.hellonote-item[data-id="' + noteId + '"]');

            // Show loading state
            $noteItem.addClass('hellonote-loading');

            // Send AJAX request
            $.ajax({
                url: hellonoteAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'hellonote_update',
                    nonce: hellonoteAjax.nonce,
                    id: noteId,
                    content: content
                },
                success: function(response) {
                    if (response.success) {
                        HelloNote.showMessage(response.data.message, 'success');
                        // Reload page to show updated note
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        HelloNote.showMessage(response.data.message, 'error');
                        $noteItem.removeClass('hellonote-loading');
                    }
                },
                error: function() {
                    HelloNote.showMessage(hellonoteAjax.errorOccurred, 'error');
                    $noteItem.removeClass('hellonote-loading');
                }
            });
        },

        /**
         * Handle cancel edit
         */
        handleCancelEdit: function(e) {
            e.preventDefault();

            const $noteItem = $(e.target).closest('.hellonote-item');

            // Remove edit form
            $noteItem.find('.hellonote-edit-form').remove();

            // Show content and actions again
            $noteItem.find('.hellonote-content, .hellonote-actions').show();
        },

        /**
         * Escape HTML to prevent XSS
         */
        escapeHtml: function(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        HelloNote.init();
    });

})(jQuery);

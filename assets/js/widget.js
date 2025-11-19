/**
 * HelloNote Dashboard Widget JavaScript
 */

jQuery(document).ready(function($) {
    'use strict';

    var form = $('#hellonote-widget-add-form');
    var contentField = $('#hellonote-widget-content');
    var submitButton = form.find('button[type="submit"]');
    var loadingSpan = $('.hellonote-widget-loading');
    var messageDiv = $('.hellonote-widget-message');
    var notesList = $('#hellonote-widget-notes');

    // Handle form submission
    form.on('submit', function(e) {
        e.preventDefault();

        var content = contentField.val().trim();

        if (content === '') {
            showMessage(hellonoteWidget.emptyNote, 'error');
            return;
        }

        // Disable form during submission
        submitButton.prop('disabled', true);
        loadingSpan.show();
        messageDiv.hide();

        // Send AJAX request
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'hellonote_add',
                nonce: form.find('input[name="hellonote_nonce"]').val(),
                content: content
            },
            success: function(response) {
                if (response.success) {
                    showMessage(response.data.message, 'success');
                    contentField.val('');

                    // Refresh notes list
                    refreshNotes();
                } else {
                    showMessage(response.data.message, 'error');
                }
            },
            error: function() {
                showMessage(hellonoteWidget.errorOccurred, 'error');
            },
            complete: function() {
                submitButton.prop('disabled', false);
                loadingSpan.hide();
            }
        });
    });

    // Handle delete button
    $(document).on('click', '.hellonote-widget-delete', function(e) {
        e.preventDefault();

        if (!confirm(hellonoteWidget.deleteConfirm)) {
            return;
        }

        var button = $(this);
        var noteId = button.data('id');
        var noteItem = button.closest('.hellonote-widget-item');

        button.prop('disabled', true);

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'hellonote_delete',
                nonce: hellonoteWidget.nonce,
                id: noteId
            },
            success: function(response) {
                if (response.success) {
                    noteItem.fadeOut(300, function() {
                        $(this).remove();

                        // Check if list is empty
                        if ($('.hellonote-widget-item').length === 0) {
                            refreshNotes();
                        }
                    });
                } else {
                    showMessage(response.data.message, 'error');
                    button.prop('disabled', false);
                }
            },
            error: function() {
                showMessage(hellonoteWidget.deleteFailed, 'error');
                button.prop('disabled', false);
            }
        });
    });

    // Refresh notes list
    function refreshNotes() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'hellonote_get_widget_notes',
                nonce: hellonoteWidget.nonce
            },
            success: function(response) {
                if (response.success) {
                    notesList.html(response.data.html);
                }
            }
        });
    }

    // Show message function
    function showMessage(text, type) {
        messageDiv
            .removeClass('success error')
            .addClass(type)
            .text(text)
            .show();

        setTimeout(function() {
            messageDiv.fadeOut();
        }, 5000);
    }
});

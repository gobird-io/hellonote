<?php

namespace HelloNote;

/**
 * Admin page renderer
 * Displays the HelloNote admin interface
 */
class Page {

    /**
     * Database handler instance
     * @var Database
     */
    private $database;

    /**
     * Constructor
     */
    public function __construct() {
        $this->database = new Database();
    }

    /**
     * Render the admin page
     *
     * @return void
     */
    public function render() {
        // Security check - verify user capability
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'hellonote'));
        }

        // Get all notes
        $notes = $this->database->get_all_notes();

        // Render the page HTML
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Admin Notes', 'hellonote'); ?></h1>

            <div class="hellonote-container">
                <!-- Add Note Form -->
                <div class="hellonote-form">
                    <h2><?php echo esc_html__('Add New Note', 'hellonote'); ?></h2>
                    <form id="hellonote-add-form">
                        <?php wp_nonce_field('hellonote_add_note', 'hellonote_nonce'); ?>
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="note-content"><?php echo esc_html__('Note', 'hellonote'); ?></label>
                                </th>
                                <td>
                                    <textarea id="note-content" name="content" rows="5" class="large-text" required></textarea>
                                </td>
                            </tr>
                        </table>
                        <p class="submit">
                            <button type="submit" class="button button-primary"><?php echo esc_html__('Add Note', 'hellonote'); ?></button>
                        </p>
                    </form>
                </div>

                <!-- Notes List -->
                <div class="hellonote-list">
                    <h2><?php echo esc_html__('All Notes', 'hellonote'); ?></h2>
                    <div id="hellonote-notes">
                        <?php if (empty($notes)): ?>
                            <p class="hellonote-empty"><?php echo esc_html__('No notes yet. Add your first note above!', 'hellonote'); ?></p>
                        <?php else: ?>
                            <?php foreach ($notes as $note): ?>
                                <div class="hellonote-item" data-id="<?php echo esc_attr($note['id']); ?>">
                                    <div class="hellonote-content">
                                        <?php echo wp_kses_post(nl2br($note['content'])); ?>
                                    </div>
                                    <div class="hellonote-meta">
                                        <span class="hellonote-author">
                                            <?php
                                            echo esc_html__('By', 'hellonote') . ' ';
                                            echo esc_html($note['display_name'] ? $note['display_name'] : __('Unknown', 'hellonote'));
                                            ?>
                                        </span>
                                        <span class="hellonote-date">
                                            <?php
                                            $timestamp = strtotime(get_date_from_gmt($note['created_at']));
                                            echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $timestamp));
                                            ?>
                                        </span>
                                        <div class="hellonote-actions">
                                            <button class="button button-small hellonote-edit" data-id="<?php echo esc_attr($note['id']); ?>">
                                                <?php echo esc_html__('Edit', 'hellonote'); ?>
                                            </button>
                                            <button class="button button-small hellonote-delete" data-id="<?php echo esc_attr($note['id']); ?>">
                                                <?php echo esc_html__('Delete', 'hellonote'); ?>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}

<?php

namespace HelloNote;

/**
 * Dashboard widget handler
 * Displays recent notes and add form on the WordPress admin dashboard
 */
class Widget {

    /**
     * Database instance
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
     * Register the dashboard widget
     *
     * @return void
     */
    public function register_widget() {
        // Only show widget to administrators
        if (!current_user_can('manage_options')) {
            return;
        }

        wp_add_dashboard_widget(
            'hellonote_dashboard_widget',           // Widget ID
            __('Admin Notes', 'hellonote'),         // Widget title
            array($this, 'render_widget')           // Callback
        );
    }

    /**
     * Render the dashboard widget
     *
     * @return void
     */
    public function render_widget() {
        $notes = $this->database->get_recent_notes(5);

        ?>
        <!-- Add Note Form -->
        <div class="hellonote-widget-form">
            <form id="hellonote-widget-add-form">
                <?php wp_nonce_field('hellonote_nonce', 'hellonote_nonce'); ?>
                <textarea
                    id="hellonote-widget-content"
                    name="content"
                    rows="3"
                    placeholder="<?php echo esc_attr__('Add a note...', 'hellonote'); ?>"
                    required
                ></textarea>
                <button type="submit" class="button button-primary button-small">
                    <?php echo esc_html__('Post Note', 'hellonote'); ?>
                </button>
                <span class="hellonote-widget-loading" style="display:none;">
                    <?php echo esc_html__('Posting...', 'hellonote'); ?>
                </span>
            </form>
            <div class="hellonote-widget-message" style="display:none;"></div>
        </div>

        <!-- Notes List -->
        <div class="hellonote-widget-notes" id="hellonote-widget-notes">
        <?php
        if (empty($notes)) {
            echo '<p class="hellonote-widget-empty">' . esc_html__('No notes yet.', 'hellonote') . '</p>';
        } else {
            echo '<ul class="hellonote-widget-list">';
            foreach ($notes as $note) {
                $this->render_note_item($note);
            }
            echo '</ul>';
        }
        ?>
        </div>

        <p class="hellonote-widget-footer">
            <a href="<?php echo esc_url(admin_url('tools.php?page=hellonote')); ?>">
                <?php echo esc_html__('View All Notes', 'hellonote'); ?> &rarr;
            </a>
        </p>

        <?php
        // Enqueue scripts and styles for the widget
        $this->enqueue_widget_assets();
    }

    /**
     * Render a single note item
     *
     * @param array $note Note data
     * @return void
     */
    private function render_note_item($note) {
        $local_time = strtotime(get_date_from_gmt($note['created_at']));
        $created_time = human_time_diff($local_time, current_time('timestamp'));
        $note_id = absint($note['id']);
        ?>
        <li class="hellonote-widget-item" data-id="<?php echo esc_attr($note_id); ?>">
            <div class="hellonote-widget-content">
                <?php echo wp_kses_post(nl2br($note['content'])); ?>
            </div>
            <div class="hellonote-widget-meta">
                <span class="hellonote-widget-author">
                    <?php echo esc_html($note['display_name'] ? $note['display_name'] : __('Unknown', 'hellonote')); ?>
                </span>
                <span class="hellonote-widget-date">
                    <?php echo esc_html($created_time) . ' ' . esc_html__('ago', 'hellonote'); ?>
                </span>
                <button class="hellonote-widget-delete" data-id="<?php echo esc_attr($note_id); ?>" title="<?php echo esc_attr__('Delete', 'hellonote'); ?>">
                    <span class="dashicons dashicons-trash"></span>
                </button>
            </div>
        </li>
        <?php
    }

    /**
     * Enqueue widget-specific assets
     *
     * @return void
     */
    private function enqueue_widget_assets() {
        // Enqueue widget CSS
        wp_enqueue_style(
            'hellonote-widget',
            plugin_dir_url(dirname(__FILE__)) . 'assets/css/widget.css',
            array(),
            '1.0.0'
        );

        // Enqueue widget JavaScript
        wp_enqueue_script(
            'hellonote-widget',
            plugin_dir_url(dirname(__FILE__)) . 'assets/js/widget.js',
            array('jquery'),
            '1.0.0',
            true
        );

        // Localize script with translated strings and nonce
        wp_localize_script('hellonote-widget', 'hellonoteWidget', array(
            'nonce' => wp_create_nonce('hellonote_nonce'),
            'emptyNote' => __('Note cannot be empty.', 'hellonote'),
            'errorOccurred' => __('An error occurred. Please try again.', 'hellonote'),
            'deleteConfirm' => __('Are you sure you want to delete this note?', 'hellonote'),
            'deleteFailed' => __('Failed to delete note.', 'hellonote')
        ));
    }
}

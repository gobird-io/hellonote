<?php

namespace HelloNote;

/**
 * AJAX handler for HelloNote plugin
 * Handles all AJAX requests for note operations
 */
class Ajax {

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
     * Register AJAX handlers
     *
     * @return void
     */
    public function register_handlers() {
        add_action('wp_ajax_hellonote_add', array($this, 'add_note'));
        add_action('wp_ajax_hellonote_update', array($this, 'update_note'));
        add_action('wp_ajax_hellonote_delete', array($this, 'delete_note'));
        add_action('wp_ajax_hellonote_get', array($this, 'get_note'));
        add_action('wp_ajax_hellonote_get_widget_notes', array($this, 'get_widget_notes'));
    }

    /**
     * Verify nonce and capability
     *
     * @return bool True if valid, dies with error if not
     */
    private function verify_request() {
        // Check nonce
        if (!check_ajax_referer('hellonote_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => __('Security check failed', 'hellonote')));
            wp_die();
        }

        // Check capability
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions', 'hellonote')));
            wp_die();
        }

        return true;
    }

    /**
     * Add a new note
     *
     * @return void
     */
    public function add_note() {
        $this->verify_request();

        // Get and sanitize input
        $content = isset($_POST['content']) ? wp_kses_post($_POST['content']) : '';

        // Validate input
        if (empty($content)) {
            wp_send_json_error(array('message' => __('Content is required', 'hellonote')));
        }

        // Insert note
        $note_id = $this->database->insert_note($content);

        if ($note_id) {
            wp_send_json_success(array(
                'message' => __('Note added successfully', 'hellonote'),
                'note_id' => $note_id
            ));
        } else {
            wp_send_json_error(array('message' => __('Failed to add note', 'hellonote')));
        }
    }

    /**
     * Update an existing note
     *
     * @return void
     */
    public function update_note() {
        $this->verify_request();

        // Get and sanitize input
        $id = isset($_POST['id']) ? absint($_POST['id']) : 0;
        $content = isset($_POST['content']) ? wp_kses_post($_POST['content']) : '';

        // Validate input
        if (!$id || empty($content)) {
            wp_send_json_error(array('message' => __('Invalid input', 'hellonote')));
        }

        // Update note
        $result = $this->database->update_note($id, $content);

        if ($result) {
            wp_send_json_success(array('message' => __('Note updated successfully', 'hellonote')));
        } else {
            wp_send_json_error(array('message' => __('Failed to update note', 'hellonote')));
        }
    }

    /**
     * Delete a note
     *
     * @return void
     */
    public function delete_note() {
        $this->verify_request();

        // Get and validate ID
        $id = isset($_POST['id']) ? absint($_POST['id']) : 0;

        if (!$id) {
            wp_send_json_error(array('message' => __('Invalid note ID', 'hellonote')));
        }

        // Delete note
        $result = $this->database->delete_note($id);

        if ($result) {
            wp_send_json_success(array('message' => __('Note deleted successfully', 'hellonote')));
        } else {
            wp_send_json_error(array('message' => __('Failed to delete note', 'hellonote')));
        }
    }

    /**
     * Get a single note
     *
     * @return void
     */
    public function get_note() {
        $this->verify_request();

        // Get and validate ID
        $id = isset($_POST['id']) ? absint($_POST['id']) : 0;

        if (!$id) {
            wp_send_json_error(array('message' => __('Invalid note ID', 'hellonote')));
        }

        // Get note
        $note = $this->database->get_note($id);

        if ($note) {
            wp_send_json_success(array('note' => $note));
        } else {
            wp_send_json_error(array('message' => __('Note not found', 'hellonote')));
        }
    }

    /**
     * Get widget notes list HTML
     *
     * @return void
     */
    public function get_widget_notes() {
        $this->verify_request();

        $notes = $this->database->get_recent_notes(5);

        ob_start();
        if (empty($notes)) {
            echo '<p class="hellonote-widget-empty">' . esc_html__('No notes yet.', 'hellonote') . '</p>';
        } else {
            echo '<ul class="hellonote-widget-list">';
            foreach ($notes as $note) {
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
            echo '</ul>';
        }
        $html = ob_get_clean();

        wp_send_json_success(array('html' => $html));
    }
}

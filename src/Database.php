<?php

namespace HelloNote;

/**
 * Database handler for HelloNote plugin
 * Manages the custom notes table creation and schema
 */
class Database {

    /**
     * Table name (without prefix)
     * @var string
     */
    private $table_name;

    /**
     * Full table name (with prefix)
     * @var string
     */
    private $table_name_full;

    /**
     * WordPress database object
     * @var \wpdb
     */
    private $wpdb;

    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = 'hellonote_notes';
        $this->table_name_full = $this->wpdb->prefix . $this->table_name;
    }

    /**
     * Create the notes table
     * Called on plugin activation
     *
     * @return void
     */
    public function create_table() {
        $charset_collate = $this->wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$this->table_name_full} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NOT NULL,
            content text NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Get all notes with user information
     *
     * @return array Array of note objects
     */
    public function get_all_notes() {
        $results = $this->wpdb->get_results(
            "SELECT n.*, u.display_name, u.user_email
             FROM {$this->table_name_full} n
             LEFT JOIN {$this->wpdb->users} u ON n.user_id = u.ID
             ORDER BY n.created_at DESC",
            ARRAY_A
        );

        return $results ? $results : array();
    }

    /**
     * Get a single note by ID
     *
     * @param int $id Note ID
     * @return array|null Note data or null if not found
     */
    public function get_note($id) {
        $note = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->table_name_full} WHERE id = %d",
                $id
            ),
            ARRAY_A
        );

        return $note;
    }

    /**
     * Insert a new note
     *
     * @param string $content Note content
     * @param int $user_id User ID (defaults to current user)
     * @return int|false Insert ID on success, false on failure
     */
    public function insert_note($content, $user_id = null) {
        if ($user_id === null) {
            $user_id = get_current_user_id();
        }

        $result = $this->wpdb->insert(
            $this->table_name_full,
            array(
                'user_id' => absint($user_id),
                'content' => wp_kses_post($content)
            ),
            array('%d', '%s')
        );

        return $result ? $this->wpdb->insert_id : false;
    }

    /**
     * Update an existing note
     *
     * @param int $id Note ID
     * @param string $content Note content
     * @return bool True on success, false on failure
     */
    public function update_note($id, $content) {
        $result = $this->wpdb->update(
            $this->table_name_full,
            array(
                'content' => wp_kses_post($content)
            ),
            array('id' => $id),
            array('%s'),
            array('%d')
        );

        return $result !== false;
    }

    /**
     * Delete a note
     *
     * @param int $id Note ID
     * @return bool True on success, false on failure
     */
    public function delete_note($id) {
        $result = $this->wpdb->delete(
            $this->table_name_full,
            array('id' => $id),
            array('%d')
        );

        return $result !== false;
    }

    /**
     * Get recent notes for dashboard widget
     *
     * @param int $limit Number of notes to retrieve
     * @return array Array of note objects
     */
    public function get_recent_notes($limit = 5) {
        $results = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT n.*, u.display_name
                 FROM {$this->table_name_full} n
                 LEFT JOIN {$this->wpdb->users} u ON n.user_id = u.ID
                 ORDER BY n.created_at DESC
                 LIMIT %d",
                $limit
            ),
            ARRAY_A
        );

        return $results ? $results : array();
    }
}

<?php

namespace HelloNote;

/**
 * Main plugin class
 * Coordinates all plugin components
 */
class Plugin {

    /**
     * Menu handler instance
     * @var Menu
     */
    private $menu;

    /**
     * Ajax handler instance
     * @var Ajax
     */
    private $ajax;

    /**
     * Widget handler instance
     * @var Widget
     */
    private $widget;

    /**
     * Initialize the plugin
     *
     * @return void
     */
    public function init() {
        $this->menu = new Menu();
        $this->ajax = new Ajax();
        $this->widget = new Widget();

        // Register hooks
        add_action('admin_menu', array($this->menu, 'register_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('wp_dashboard_setup', array($this->widget, 'register_widget'));

        // Register AJAX handlers
        $this->ajax->register_handlers();
    }

    /**
     * Enqueue CSS and JavaScript assets
     * Only loads on the HelloNote admin page
     *
     * @param string $hook Current admin page hook
     * @return void
     */
    public function enqueue_assets($hook) {
        // Only load on our plugin's page (now under Tools menu)
        if ($hook !== 'tools_page_hellonote') {
            return;
        }

        // Enqueue CSS
        wp_enqueue_style(
            'hellonote-admin',
            HELLONOTE_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            HELLONOTE_VERSION
        );

        // Enqueue JavaScript
        wp_enqueue_script(
            'hellonote-admin',
            HELLONOTE_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            HELLONOTE_VERSION,
            true
        );

        // Localize script with AJAX URL, nonce and translatable strings
        wp_localize_script(
            'hellonote-admin',
            'hellonoteAjax',
            array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('hellonote_nonce'),
                'errorOccurred' => __('An error occurred. Please try again.', 'hellonote'),
                'deleteConfirm' => __('Are you sure you want to delete this note?', 'hellonote'),
                'noNotes' => __('No notes yet. Add your first note above!', 'hellonote'),
                'updateButton' => __('Update', 'hellonote'),
                'cancelButton' => __('Cancel', 'hellonote')
            )
        );
    }
}

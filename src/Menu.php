<?php

namespace HelloNote;

/**
 * Admin menu handler
 * Registers the HelloNote menu page in WordPress admin
 */
class Menu {

    /**
     * Page renderer instance
     * @var Page
     */
    private $page;

    /**
     * Constructor
     */
    public function __construct() {
        $this->page = new Page();
    }

    /**
     * Register the admin menu as a Tools submenu
     *
     * @return void
     */
    public function register_menu() {
        add_submenu_page(
            'tools.php',                            // Parent slug (Tools menu)
            __('HelloNote', 'hellonote'),           // Page title
            __('HelloNote', 'hellonote'),           // Menu title
            'manage_options',                       // Capability
            'hellonote',                            // Menu slug
            array($this->page, 'render')            // Callback
        );
    }
}

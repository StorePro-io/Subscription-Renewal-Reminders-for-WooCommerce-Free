<?php
/**
 * @package  RenewalReminders
 */

/**
 * Class SPRRSettingsApi
 * Handles the settings and menu pages for the RenewalReminders plugin.
 */
class SPRRSettingsApi
{
    public $admin_pages = array();
    public $settings = array();
    public $sections = array();
    public $fields = array();

    /**
     * Registers the plugin settings and menu pages.
     */
    public function sprr_register()
    {
        if (!empty($this->admin_pages)) {
            add_action('admin_menu', array($this, 'sprr_addAdminMenu'));
            add_action('admin_head', array($this, 'admin_menu_styles'), 11);
        }

        if (!empty($this->settings)) {
            add_action('admin_init', array($this, 'sprr_registerCustomFields'));
        }
    }

    /**
     * Adds admin pages to the plugin.
     *
     * @param array $pages An array of admin pages to add.
     * @return $this
     */
    public function sprr_addPages(array $pages)
    {
        $this->admin_pages = $pages;
        return $this;
    }

    /**
     * Adds the main admin menu and submenu pages.
     */
    public function sprr_addAdminMenu()
    {
        foreach ($this->admin_pages as $page) {
            add_menu_page(
                $page['page_title'],
                $page['menu_title'],
                $page['capability'],
                $page['menu_slug'],
                $page['callback'],
                $page['icon_url'],
                $page['position']
                
            );

            // Add a submenu for current settings
            add_submenu_page(
                $page['menu_slug'],
                esc_html__('Settings', 'subscriptions-renewal-reminders'),
                esc_html__('Settings', 'subscriptions-renewal-reminders'),
                $page['capability'],
                $page['menu_slug'],
                null // No callback function is provided
            );

            // Add a submenu for Marketing
            add_submenu_page(
                $page['menu_slug'],
                esc_html__('Marketing', 'subscriptions-renewal-reminders'),
                esc_html__('Marketing', 'subscriptions-renewal-reminders'),
                $page['capability'],
                'sp-renewal-reminders-marketing',
                array($this, 'sprr_marketing_page_callback')
            );

            // Add a submenu for Templates
            add_submenu_page(
                $page['menu_slug'],
                esc_html__('Templates', 'subscriptions-renewal-reminders'),
                esc_html__('Templates', 'subscriptions-renewal-reminders'),
                $page['capability'],
                'sp-renewal-reminders-templates',
                array($this, 'sprr_templates_page_callback')
            );

            // Add a submenu for Help
            add_submenu_page(
                $page['menu_slug'],
                esc_html__('Help', 'subscriptions-renewal-reminders'),
                esc_html__('Help', 'subscriptions-renewal-reminders'),
                $page['capability'],
                'sp-renewal-reminders-help',
                array($this, 'sprr_help_page_callback')
            );

            // Add a submenu for the "Upgrade" section
            add_submenu_page($page['menu_slug'],esc_html__('Upgrade', 'subscriptions-renewal-reminders'),  esc_html__('Upgrade to Pro', 'subscriptions-renewal-reminders'), $page['capability'], 'upgrade', array($this, 'sprr_upgrade_page_callback'));
        }
    }

    /**
     * Callback function for Marketing page
     */
    public function sprr_marketing_page_callback()
    {
        require SPRR_PLUGIN_DIR . 'templates/renewal-reminders-marketing.php';
    }

    /**
     * Callback function for Templates page
     */
    public function sprr_templates_page_callback()
    {
        require SPRR_PLUGIN_DIR . 'templates/renewal-reminders-templates.php';
    }

    /**
     * Callback function for Help page
     */
    public function sprr_help_page_callback()
    {
        require SPRR_PLUGIN_DIR . 'templates/renewal-reminders-help.php';
    }

    /**
     * Callback function to redirect to an external link for the "Upgrade" section.
     */
    public function sprr_upgrade_page_callback()
    {
        // Redirect to the external link
        wp_redirect('https://storepro.io/subscription-renewal-premium/');
        exit();
    }

    /**
     * Adds custom CSS styles to the admin menu.
     */
    public function admin_menu_styles()
    {
        // Style the "Upgrade to Pro" submenu item by slug, independent of position
        $styles = '#toplevel_page_sp-renewal-reminders ul li a[href*="page=upgrade"]{background-color:#fbb03b !important;color:#0c3c74 !important;font-weight:600 !important;}
        #toplevel_page_sp-renewal-reminders ul li a[href*="page=upgrade"]:hover{background-color:#ffc657 !important;color:#0c3c74 !important;}';

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        printf('<style>%s</style>', $styles);
    }

    /**
     * Sets the plugin settings.
     *
     * @param array $settings An array of plugin settings.
     * @return $this
     */
    public function sprr_setSettings(array $settings)
    {
        $this->settings = array_merge($this->settings, $settings);
        return $this;
    }

    /**
     * Sets the sections for the plugin settings.
     *
     * @param array $sections An array of sections for the settings.
     * @return $this
     */
    public function sprr_setSections(array $sections)
    {
        $this->sections = array_merge($this->sections, $sections);
        return $this;
    }

    /**
     * Sets the fields for the plugin settings.
     *
     * @param array $fields An array of fields for the settings.
     * @return $this
     */
    public function sprr_setFields(array $fields)
    {
        $this->fields = array_merge($this->fields, $fields);
        return $this;
    }

    /**
     * Registers custom fields and settings for the plugin.
     */
    public function sprr_registerCustomFields()
    {
        // Register settings
        foreach ($this->settings as $setting) {
            register_setting($setting["option_group"], $setting["option_name"], (isset($setting["callback"]) ? $setting["callback"] : ''));
        }

        // Add settings sections
        foreach ($this->sections as $section) {
            add_settings_section($section["id"], $section["title"], (isset($section["callback"]) ? $section["callback"] : ''), $section["page"]);
        }

        // Add settings fields
        foreach ($this->fields as $field) {
            add_settings_field($field["id"], $field["title"], (isset($field["callback"]) ? $field["callback"] : ''), $field["page"], $field["section"], (isset($field["args"]) ? $field["args"] : ''));
        }
    }
}

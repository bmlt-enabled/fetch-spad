<?php

namespace Spad;

class Dashboard
{
    public function __construct()
    {
        add_action('admin_init', [$this, 'registerSettings']);
    }

    public function registerSettings(): void
    {
        register_setting('spad-plugin-settings-group', 'spad_layout');
    }

    public function createMenu(string $baseFile): void
    {
        add_options_page(
            esc_html__('Fetch SPAD Plugin Settings'), // Page Title
            esc_html__('Fetch SPAD'),                 // Menu Title
            'manage_options',             // Capability
            'spad-plugin',                // Menu Slug
            [$this, 'drawSettings']  // Callback function to display the page content
        );
        add_filter('plugin_action_links_' . $baseFile, [$this, 'settingsLink']);
    }

    public function settingsLink($links)
    {
        $settings_url = admin_url('options-general.php?page=spad-plugin');
        $links[] = "<a href='{$settings_url}'>Settings</a>";
        return $links;
    }

    public function drawSettings(): void
    {
        ?>
        <div class="wrap">
            <h1>Fetch SPAD Plugin Settings</h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('spad-plugin-settings-group');
                do_settings_sections('spad-plugin-settings-group');
                ?>
                <table class="form-table">
                    <tr>
                        <td colspan="2">
                            <div style="max-width: 600px;">
                            This is a plugin that fetches A Spiritual Principle A Day and puts it on your site. Simply add [spad] shortcode to your page. Fetch SPAD Widget can be added to your sidebar or
                            footer as well.
                            </div>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Layout</th>
                        <td>
                            <select id="spad_layout" name="spad_layout">
                                <option value="table" <?php selected(get_option('spad_layout'), 'table'); ?>>Table (Raw HTML)</option>
                                <option value="block" <?php selected(get_option('spad_layout'), 'block'); ?>>Block</option>
                            </select>
                            <p class="description">Change between raw HTML Table and CSS block elements.</p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}

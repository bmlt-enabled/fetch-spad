<?php
 
/* Admin dashboard of the SPAD plugin */

function spad_plugin_settings()
{
    //register our settings
    register_setting('spad-plugin-settings-group', 'spad_layout');
}

add_action('admin_init', 'spad_plugin_settings');

function fetch_spad_plugin_page()
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
                <tr valign="top">
                    <th scope="row">Layout</th>
                    <td>
                        <select id="spad_layout" name="spad_layout">
                            <option value="table" <?php if (esc_attr(get_option('spad_layout'))=='table') {
                                echo 'selected="selected"';
                                                  } ?>>Table (Raw HTML)</option>
                            <option value="block" <?php if (esc_attr(get_option('spad_layout'))=='block') {
                                echo 'selected="selected"';
                                                  } ?>>Block</option>
                        </select>
                        <p class="description">Change between raw HTML Table and CSS block elements.</p>
                    </td>
                </tr>
            </table>
            <?php  submit_button(); ?>
        </form>
   </div>
<?php }

// End SPAD Settings Page Function
?>

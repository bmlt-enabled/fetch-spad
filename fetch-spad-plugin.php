<?php
/*
Plugin Name: Fetch SPAD
Plugin URI: https://wordpress.org/plugins/fetch-spad/
Description: This is a plugin that fetches A Spiritual Principle A Day and puts it on your site Simply add [spad] shortcode to your page. Fetch SPAD Widget can be added to your sidebar or footer as well.
Version: 1.0.0
Install: Drop this directory into the "wp-content/plugins/" directory and activate it.
*/
/* Disallow direct access to the plugin file */
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die('Sorry, but you cannot access this page directly.');
}

require_once('admin/spad-dashboard.php');

// create admin menu settings page
add_action('admin_menu', 'spad_options_menu');
function spad_options_menu()
{
    add_options_page('Fetch SPAD Plugin Settings', 'Fetch SPAD', 'manage_options', 'spad-plugin', 'fetch_spad_plugin_page');
}

// add settings link to plugins page
function spad_add_settings_link($links)
{
    $settings_link = '<a href="options-general.php?page=spad-plugin">' . __('Settings') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}
$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'spad_add_settings_link');
add_action('wp_enqueue_scripts', 'spad_assets');

function spad_assets()
{
    wp_enqueue_style("spadcss", plugin_dir_url(__FILE__) . "css/spad.css", false, filemtime(plugin_dir_path(__FILE__) . "css/spad.css"), false);
}

function spad_func($atts = [])
{
    $args = shortcode_atts(
        array(
            'layout'    =>  ''
        ),
        $atts
    );
    $HTTP_RETRIEVE_ARGS = array(
        'headers' => array(
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:105.0) Gecko/20100101 Firefox/105.0'
        ),
        'timeout' => 60
    );

    $spad_layout = (!empty($args['layout']) ? sanitize_text_field(strtolower($args['layout'])) : get_option('spad_layout'));
    # Get Today's Meditation from NAWS
    $spad_url = 'https://spadna.org';
    $spad_dom_element = 'table';
    $char_encoding = "UTF-8";
    $spad_get = wp_remote_get($spad_url, $HTTP_RETRIEVE_ARGS);
    $spad_body = wp_remote_retrieve_body($spad_get);
    // Get the contents of SPAD
    if ($spad_layout == 'block') {
        libxml_use_internal_errors(true);
        $spad_data = mb_convert_encoding($spad_body, 'HTML-ENTITIES', $char_encoding);
        $spad_data = str_replace('--', '&mdash;', $spad_data);
        $d = new DOMDocument();
        $d->validateOnParse = true;
        $d->loadHTML($spad_data);
        libxml_clear_errors();
        libxml_use_internal_errors(false);
   
        $spad_ids = array('spad-date','spad-title','spad-page','spad-quote','spad-quote-source','spad-content','spad-divider','spad-thought','spad-copyright');
        $spad_class = 'spad-rendered-element';
        $i = 0;
        $k = 1;
        $content = '<div id="spad-container" class="'.$spad_class.'">';

        foreach ($d->getElementsByTagName('tr') as $element) {
            if ($i != 5) {
                $formated_element = trim($element->nodeValue);
                $content .= '<div id="'.$spad_ids[$i].'" class="'.$spad_class.'">'.$formated_element.'</div>';
            } else {
                $xpath = new DOMXPath($d);
                foreach ($xpath->query('//tr') as $row) {
                    $row_values = array();
                    foreach ($xpath->query('td', $row) as $cell) {
                        $innerHTML= '';
                        $children = $cell->childNodes;
                        foreach ($children as $child) {
                            $innerHTML .= $child->ownerDocument->saveXML($child);
                        }
                        $row_values[] = $innerHTML;
                    }
                    $values[] = $row_values;
                }
                $break_array = preg_split('/<br[^>]*>/i', (join('', $values[5])));
                $content .= '<div id="'.$spad_ids[$i].'" class="'.$spad_class.'">';
                foreach ($break_array as $p) {
                    if (!empty($p)) {
                        $formated_element = '<p id="'.$spad_ids[$i].'-'.$k.'" class="'.$spad_class.'">'.trim($p).'</p>';
                        $content .= preg_replace("/<p[^>]*>([\s]|&nbsp;)*<\/p>/", '', $formated_element);
                        $k++;
                    }
                }
                $content .= '</div>';
            }
            $i++;
        }
        $content .= '</div>';
    } else {
        $spad_data = str_replace('--', '&mdash;', $spad_body);
        $content = '';
        $d1 = new DOMDocument;
        $spad = new DOMDocument;
        libxml_use_internal_errors(true);
        $d1->loadHTML(mb_convert_encoding($spad_data, 'HTML-ENTITIES', $char_encoding));
        libxml_clear_errors();
        libxml_use_internal_errors(false);
        $xpath = new DOMXpath($d1);
        $body = $xpath->query("//$spad_dom_element");
        foreach ($body as $child) {
            $spad->appendChild($spad->importNode($child, true));
        }
        $content .= $spad->saveHTML();
    }
    return $content;
}

// create [spad] shortcode
add_shortcode('spad', 'spad_func');

/** START Fetch SPAD Widget **/
// register SPAD_Widget
add_action('widgets_init', function () {
    register_widget('SPAD_Widget');
});
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
// phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps
class SPAD_Widget extends WP_Widget
{
// phpcs:enable PSR1.Classes.ClassDeclaration.MissingNamespace
// phpcs:enable Squiz.Classes.ValidClassName.NotCamelCaps
    /**
     * Sets up a new Fetch SPAD widget instance.
     *
     */
    public function __construct()
    {
        $widget_ops = array(
            'classname' => 'SPAD_widget',
            'description' => 'Displays the Spiritual Principle A Day',
        );
        parent::__construct('SPAD_widget', 'Fetch SPAD', $widget_ops);
    }

    /**
    * Outputs the content for the current Fetch SPAD widget instance.
    *
    *
    * @spad_func gets and parses the spad
    *
    * @param array $args     Display arguments including 'before_title', 'after_title',
    *                        'before_widget', and 'after_widget'.
    * @param array $instance Settings for the current Area Meetings Dropdown widget instance.
    */

    public function widget($args, $instance)
    {
        echo $args['before_widget'];
        if (! empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }
        echo spad_func($atts);
        echo $args['after_widget'];
    }

    /**
     * Outputs the settings form for the Fetch SPAD widget.
     * @param $instance
     */
    public function form($instance)
    {
        $title = ! empty($instance['title']) ? $instance['title'] : esc_html__('Title', 'text_domain');
        ?>
        <p>
        <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
        <?php esc_attr_e('Title:', 'text_domain'); ?>
        </label>

        <input
            class="widefat"
            id="<?php echo esc_attr($this->get_field_id('title')); ?>"
            name="<?php echo esc_attr($this->get_field_name('title')); ?>"
            type="text"
            value="<?php echo esc_attr($title); ?>">
        </p>
        <?php
    }

    /**
    * Handles updating settings for the current Fetch SPAD widget instance.
    *
    * @param array $new_instance New settings for this instance as input by the user via
    *                            WP_Widget::form().
    * @param array $old_instance Old settings for this instance.
    * @return array Updated settings to save.
    */
    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['title'] = ( ! empty($new_instance['title']) ) ? strip_tags($new_instance['title']) : '';
        return $instance;
    }
}
/** END Fetch SPAD Widget **/
?>

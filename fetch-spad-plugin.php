<?php
/**
 * Plugin Name:       Fetch SPAD
 * Plugin URI:        https://wordpress.org/plugins/fetch-spad/
 * Description:       This is a plugin that fetches A Spiritual Principle A Day and puts it on your site Simply add [spad] shortcode to your page. Fetch SPAD Widget can be added to your sidebar or footer as well.
 * Install:           Drop this directory into the "wp-content/plugins/" directory and activate it.
 * Contributors:      pjaudiomv, bmltenabled
 * Author:            bmltenabled
 * Version:           1.2.3
 * Requires PHP:      8.1
 * Requires at least: 6.2
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace SpadPlugin;

require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

if ( basename( $_SERVER['PHP_SELF'] ) == basename( __FILE__ ) ) {
	die( 'Sorry, but you cannot access this page directly.' );
}


use FetchMeditation\SPADLanguage;
use FetchMeditation\SPADSettings;
use FetchMeditation\SPAD;

/**
 * Class FETCHSPAD
 * @package SpadPlugin
 */
class FETCHSPAD {

	private const SETTINGS_GROUP   = 'fetch-spad-group';
	private const DEFAULT_LAYOUT = 'block';
	private const PLUG_SLUG = 'fetch-spad';

	/**
	 * Singleton instance of the class.
	 *
	 * @var null|self
	 */
	private static ?self $instance = null;

	/**
	 * Constructor method for initializing the plugin.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'plugin_setup' ] );
	}

	/**
	 * Setup method for initializing the plugin.
	 *
	 * This method checks if the current context is in the admin dashboard or not.
	 * If in the admin dashboard, it registers admin-related actions and settings.
	 * If not in the admin dashboard, it sets up a shortcode and associated actions.
	 *
	 * @return void
	 */
	public function plugin_setup(): void {
		if ( is_admin() ) {
			add_action( 'admin_menu', [ static::class, 'create_menu' ] );
			add_action( 'admin_init', [ static::class, 'register_settings' ] );
		} else {
			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_files' ] );
			add_shortcode( 'spad', [ static::class, 'render_shortcode' ] );
		}
	}

	/**
	 * Determines the option value based on the provided attributes or fallbacks to a default value.
	 *
	 * @param string|array $attrs An string or associative array of attributes where the key is the option name.
	 * @param string $option The specific option to fetch (e.g., 'language', 'book', 'layout').
	 * @return string Sanitized and lowercased value of the determined option.
	 */
	private static function determine_option( string|array $attrs, string $option ): string {
		if ( isset( $_POST['fetch_spad_nonce'] ) && wp_verify_nonce( $_POST['fetch_spad_nonce'], 'fetch_spad_action' ) ) {
			if ( isset( $_POST[ $option ] ) ) {
				// Form data option
				return sanitize_text_field( strtolower( $_POST[ $option ] ) );
			}
		}
		if ( isset( $_GET[ $option ] ) ) {
			// Query String Option
			return sanitize_text_field( strtolower( $_GET[ $option ] ) );
		} elseif ( ! empty( $attrs[ $option ] ) ) {
			// Shortcode Option
			return sanitize_text_field( strtolower( $attrs[ $option ] ) );
		} else {
			// Settings Option or Default
			return sanitize_text_field( strtolower( get_option( 'fetch_spad_' . $option, self::DEFAULT_LAYOUT ) ) );
		}
	}

	public static function render_shortcode( string|array $attrs = [] ): string {
		$layout   = self::determine_option( $attrs, 'layout' );
		$settings = new SPADSettings( SPADLanguage::English );
		$instance = SPAD::getInstance( $settings );
		$entry    = $instance->fetch();
		if ( is_string( $entry ) ) {
			return "Error: {$entry}";
		} else {
			return static::build_layout( $entry, 'block' === $layout );
		}
	}

	private static function build_layout( object $entry, bool $in_block ): string {
		// Render Content As HTML Table or CSS Block Elements
		$css_identifier = $in_block ? 'spad' : 'spad-table';

		$paragraph_content = '';
		$count            = 1;

		foreach ( $entry->content as $c ) {
			if ( $in_block ) {
				$paragraph_content .= "\n    <p id=\"$css_identifier-content-$count\" class=\"$css_identifier-rendered-element\">$c</p>";
			} else {
				$paragraph_content .= "$c<br><br>";
			}
			++$count;
		}
		$paragraph_content .= "\n";

		$content = "\n<div id=\"$css_identifier-container\" class=\"spad-rendered-element\">\n";
		if ( ! $in_block ) {
			$content .= '<table align="center">' . "\n";
		}

		$data = [
			'date'       => $entry->date,
			'title'      => $entry->title,
			'page'       => $entry->page,
			'quote'      => $entry->quote,
			'source'     => $entry->source,
			'paragraphs' => $paragraph_content,
			'thought'    => $entry->thought,
			'copyright'  => $entry->copyright,
		];

		foreach ( $data as $key => $value ) {
			if ( empty( $value ) ) {
				continue;
			}

			if ( 'quote' === $key && ! $in_block ) {
				$element = '<i>' . $value . '</i>';
			} elseif ( 'title' === $key && ! $in_block ) {
				$element = '<h1>' . $value . '</h1>';
			} elseif ( 'date' === $key && ! $in_block ) {
				$element = '<h2>' . $value . '</h2>';
			} else {
				$element = $value;
			}

			if ( $in_block ) {
				$content .= "  <div id=\"$css_identifier-$key\" class=\"$css_identifier-rendered-element\">$element</div>\n";
			} else {
				$alignment = in_array( $key, [ 'title', 'page', 'source' ] ) ? 'center' : 'left';
				$line_break = in_array( $key, [ 'quote-source', 'quote', 'thought', 'page' ] ) ? '<br><br>' : '';
				$content  .= "<tr><td align=\"$alignment\">$element$line_break</td></tr>\n";
			}
		}

		$content .= $in_block ? "</div>\n" : "</table>\n</div>\n";
		return $content;
	}

	public function enqueue_frontend_files(): void {
		wp_enqueue_style( self::PLUG_SLUG, plugin_dir_url( __FILE__ ) . 'css/fetch-spad.css', false, '1.0.0', 'all' );
	}

	public static function register_settings(): void {
		// Register plugin settings with WordPress
		register_setting(
			self::SETTINGS_GROUP,
			'fetch_spad_layout',
			[
				'type'              => 'string',
				'default'           => self::DEFAULT_LAYOUT,
				'sanitize_callback' => 'sanitize_text_field',
			]
		);
	}

	public static function create_menu(): void {
		// Create the plugin's settings page in the WordPress admin menu
		add_options_page(
			esc_html__( 'Fetch Spad Settings', 'fetch-spad' ), // Page Title
			esc_html__( 'Fetch Spad', 'fetch-spad' ),          // Menu Title
			'manage_options',                        // Capability
			self::PLUG_SLUG,                         // Menu Slug
			[ static::class, 'draw_settings' ]         // Callback function to display the page content
		);
		// Add a settings link in the plugins list
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), [ static::class, 'settings_link' ] );
	}

	public static function settings_link( array $links ): array {
		// Add a "Settings" link for the plugin in the WordPress admin
		$settings_url = admin_url( 'options-general.php?page=' . self::PLUG_SLUG );
		$links[]      = "<a href='{$settings_url}'>Settings</a>";
		return $links;
	}

	public static function draw_settings(): void {
		// Display the plugin's settings page
		$spad_layout   = esc_attr( get_option( 'fetch_spad_layout' ) );
		$allowed_html = [
			'select' => [
				'id'   => [],
				'name' => [],
			],
			'option' => [
				'value'   => [],
				'selected'   => [],
			],
		];
		?>
		<div class="wrap">
			<h2>Fetch Spad Settings</h2>
			<form method="post" action="options.php">
				<?php wp_nonce_field( 'fetch_spad_action', 'fetch_spad_nonce' ); ?>
				<?php settings_fields( self::SETTINGS_GROUP ); ?>
				<?php do_settings_sections( self::SETTINGS_GROUP ); ?>
				<table class="form-table">
					<tr valign="top">
						<th scope="row">Layout</th>
						<td>
							<?php
							echo wp_kses(
								static::render_select_option(
									'fetch_spad_layout',
									$spad_layout,
									[
										'table' => 'Table',
										'block' => 'Block (CSS)',
									]
								),
								$allowed_html
							);
							?>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	private static function render_select_option( string $name, string $selected_value, array $options ): string {
		// Render a dropdown select input for settings
		$select_html = "<select id='$name' name='$name'>";
		foreach ( $options as $value => $label ) {
			$selected    = selected( $selected_value, $value, false );
			$select_html .= "<option value='$value' $selected>$label</option>";
		}
		$select_html .= '</select>';

		return $select_html;
	}

	public static function get_instance(): self {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}

FETCHSPAD::get_instance();

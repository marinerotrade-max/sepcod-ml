<?php
/**
 * Enhanced Language Switcher with Flags and Modal Popup.
 *
 * @package AutoMultilingualSEO
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AMST_Language_Switcher class.
 */
class AMST_Language_Switcher {
	
	/**
	 * Flag icons mapping for EU languages.
	 *
	 * @var array
	 */
	private $flag_icons = array(
		'bg' => '🇧🇬', // Bulgarian
		'hr' => '🇭🇷', // Croatian
		'cs' => '🇨🇿', // Czech
		'da' => '🇩🇰', // Danish
		'nl' => '🇳🇱', // Dutch
		'en' => '🇬🇧', // English
		'et' => '🇪🇪', // Estonian
		'fi' => '🇫🇮', // Finnish
		'fr' => '🇫🇷', // French
		'de' => '🇩🇪', // German
		'el' => '🇬🇷', // Greek
		'hu' => '🇭🇺', // Hungarian
		'ga' => '🇮🇪', // Irish
		'it' => '🇮🇹', // Italian
		'lv' => '🇱🇻', // Latvian
		'lt' => '🇱🇹', // Lithuanian
		'mt' => '🇲🇹', // Maltese
		'pl' => '🇵🇱', // Polish
		'pt' => '🇵🇹', // Portuguese
		'ro' => '🇷🇴', // Romanian
		'sk' => '🇸🇰', // Slovak
		'sl' => '🇸🇮', // Slovenian
		'es' => '🇪🇸', // Spanish
		'sv' => '🇸🇪', // Swedish
	);
	
	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_shortcode( 'amst_language_switcher', array( $this, 'render_shortcode' ) );
		
		// Add filter for menu links to maintain language persistence
		add_filter( 'nav_menu_link_attributes', array( $this, 'add_language_to_menu_links' ), 10, 3 );
	}
	
	/**
	 * Enqueue CSS and JS assets.
	 */
	public function enqueue_assets() {
		wp_enqueue_style(
			'amst-language-switcher',
			plugins_url( 'assets/css/language-switcher.css', dirname( __FILE__ ) ),
			array(),
			'1.0.0'
		);
		
		wp_enqueue_script(
			'amst-language-switcher',
			plugins_url( 'assets/js/language-switcher.js', dirname( __FILE__ ) ),
			array( 'jquery' ),
			'1.0.0',
			true
		);
		
		// Pass settings to JS
		$settings = array(
			'show_names'     => get_option( 'amst_switcher_show_names', 'no' ),
			'modal_title'    => get_option( 'amst_switcher_modal_title', __( 'Select Language', 'auto-multilingual-seo' ) ),
			'flag_size'      => get_option( 'amst_switcher_flag_size', 'medium' ),
			'position'       => get_option( 'amst_switcher_position', 'bottom-right' ),
		);
		
		wp_localize_script( 'amst-language-switcher', 'amstSwitcher', $settings );
	}
	
	/**
	 * Render language switcher shortcode - SIMPLIFIED DROPDOWN ONLY.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function render_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'show_names' => get_option( 'amst_switcher_show_names', 'yes' ),
				'position'   => get_option( 'amst_switcher_position', 'inline' ),
			),
			$atts,
			'amst_language_switcher'
		);
		
		$language_detector = amst()->language_detector;
		$translator        = amst()->translator;
		
		$current_lang = $language_detector->get_current_language();
		$enabled_languages = $language_detector->get_enabled_languages();
		$supported_languages = $translator->get_supported_languages();
		
		$show_names = 'yes' === $atts['show_names'];
		$position = $atts['position'];
		
		// Position class
		$position_class = '';
		if ( in_array( $position, array( 'bottom-right', 'bottom-left', 'top-right', 'top-left' ), true ) ) {
			$position_class = ' amst-fixed amst-position-' . esc_attr( $position );
		} elseif ( 'inline' !== $position ) {
			// If not inline and not a recognized fixed position, default to inline
			$position_class = '';
		}
		
		// Render simple dropdown switcher
		ob_start();
		?>
		<div class="amst-language-switcher-simple<?php echo esc_attr( $position_class ); ?>">
			<select class="amst-language-dropdown" onchange="if(this.value) window.location.href=this.value;">
				<?php foreach ( $enabled_languages as $lang_code ) : ?>
					<?php
					$flag = isset( $this->flag_icons[ $lang_code ] ) ? $this->flag_icons[ $lang_code ] : '🌐';
					$name = isset( $supported_languages[ $lang_code ] ) ? $supported_languages[ $lang_code ] : $lang_code;
					$url = $this->get_language_url( $lang_code );
					$is_current = ( $lang_code === $current_lang );
					?>
					<option value="<?php echo esc_url( $url ); ?>" <?php selected( $is_current ); ?>>
						<?php echo esc_html( $flag ); ?> 
						<?php if ( $show_names ) : ?>
							<?php echo esc_html( $name ); ?>
						<?php endif; ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>
		<?php
		return ob_get_clean();
	}

	
	/**
	 * Get URL for switching to a specific language.
	 *
	 * FIXED: Properly replaces language codes instead of stacking them.
	 * Handles homepage correctly and generates absolute URLs from root.
	 *
	 * @param string $lang Language code.
	 * @return string Language-specific URL (absolute path from root).
	 */
	private function get_language_url( $lang ) {
		$language_detector = amst()->language_detector;
		$default_lang = $language_detector->get_default_language();
		$current_lang = $language_detector->get_current_language();
		
		// Get home URL (always absolute)
		$home_url = untrailingslashit( home_url( '/' ) );
		
		// Check if we're on homepage using multiple detection methods
		$is_homepage = $this->is_homepage();
		
		// Special handling for homepage/front page
		if ( $is_homepage ) {
			// Check if manual homepage URL is configured
			$homepage_urls = get_option( 'amst_homepage_urls', array() );
			if ( ! empty( $homepage_urls[ $lang ] ) ) {
				// Use manually configured homepage URL
				$url_template = $homepage_urls[ $lang ];
				$url = str_replace( 
					array( '{home}', '{lang}' ), 
					array( $home_url, $lang ), 
					$url_template 
				);
				return trailingslashit( $url );
			}
			
			// Fallback to automatic generation
			if ( $lang === $default_lang ) {
				// Default language - just home URL
				return trailingslashit( $home_url );
			} else {
				// Other language - home URL with language prefix
				// e.g., domain.com/de/
				return trailingslashit( $home_url . '/' . $lang );
			}
		}
		
		// For all other pages, get current URL and replace/add language code
		// Use REQUEST_URI for the actual URL path
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		
		if ( empty( $request_uri ) ) {
			// Fallback to WordPress request
			global $wp;
			$request_uri = '/' . ( isset( $wp->request ) ? $wp->request : '' );
		}
		
		// Parse the URI to separate path from query string
		$uri_parts = explode( '?', $request_uri, 2 );
		$path = $uri_parts[0];
		$query_string = isset( $uri_parts[1] ) ? $uri_parts[1] : '';
		
		// Remove leading and trailing slashes for processing
		$path = trim( $path, '/' );
		
		// Split path into segments
		$path_segments = empty( $path ) ? array() : explode( '/', $path );
		
		// Get all enabled language codes
		$enabled_languages = $language_detector->get_enabled_languages();
		
		// Check if the first segment is a language code
		$has_lang_prefix = false;
		if ( ! empty( $path_segments[0] ) && in_array( $path_segments[0], $enabled_languages, true ) ) {
			// Remove the existing language code
			array_shift( $path_segments );
			$has_lang_prefix = true;
		}
		
		// Now $path_segments contains the path WITHOUT any language prefix
		$clean_path = implode( '/', $path_segments );
		
		// Build the new URL with absolute path from root
		if ( $lang === $default_lang ) {
			// For default language, no prefix needed
			if ( empty( $clean_path ) ) {
				// Homepage in default language
				$new_url = $home_url . '/';
			} else {
				// Other page in default language
				$new_url = $home_url . '/' . $clean_path . '/';
			}
		} else {
			// For non-default languages, add the language prefix
			if ( empty( $clean_path ) ) {
				// Homepage in target language: domain.com/de/
				$new_url = $home_url . '/' . $lang . '/';
			} else {
				// Other page in target language: domain.com/de/about/
				$new_url = $home_url . '/' . $lang . '/' . $clean_path . '/';
			}
		}
		
		// Add query string if it exists
		if ( ! empty( $query_string ) ) {
			$new_url .= '?' . $query_string;
		}
		
		// Normalize slashes (avoid double slashes)
		$new_url = preg_replace( '#(?<!:)//+#', '/', $new_url );
		
		return $new_url;
	}
	
	/**
	 * Detect if current page is homepage using multiple methods.
	 *
	 * @return bool True if homepage.
	 */
	private function is_homepage() {
		// Method 1: Check our custom query var (set by rewrite rules for /de/, /fr/, etc.)
		$is_homepage_var = get_query_var( 'is_homepage' );
		if ( ! empty( $is_homepage_var ) ) {
			return true;
		}
		
		// Method 2: Check WordPress query vars
		global $wp_query;
		if ( isset( $wp_query ) ) {
			// Check if this is the main query without any specific page vars
			if ( empty( $wp_query->query_vars['pagename'] ) && 
			     empty( $wp_query->query_vars['page_id'] ) && 
			     empty( $wp_query->query_vars['name'] ) &&
			     empty( $wp_query->query_vars['category_name'] ) &&
			     empty( $wp_query->query_vars['tag'] ) &&
			     empty( $wp_query->query_vars['post_type'] ) ) {
				return true;
			}
		}
		
		// Method 3: Check URL path
		global $wp;
		if ( isset( $wp->request ) && ( empty( $wp->request ) || $wp->request === '' ) ) {
			return true;
		}
		
		// Method 4: Check request URI
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$request_uri = trim( $request_uri, '/' );
		
		// Remove language prefix if present
		$language_detector = amst()->language_detector;
		$enabled_languages = $language_detector->get_enabled_languages();
		$path_parts = explode( '/', $request_uri );
		if ( ! empty( $path_parts[0] ) && in_array( $path_parts[0], $enabled_languages, true ) ) {
			array_shift( $path_parts );
			$request_uri = implode( '/', $path_parts );
		}
		
		// If path is empty or just has query string, it's homepage
		if ( empty( $request_uri ) || strpos( $request_uri, '?' ) === 0 ) {
			return true;
		}
		
		// Method 5: WordPress conditionals (least reliable, use as last resort)
		if ( is_front_page() || is_home() ) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Get current URL.
	 *
	 * @return string Current URL.
	 */
	private function get_current_url() {
		$protocol = is_ssl() ? 'https://' : 'http://';
		$host = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		
		return $protocol . $host . $request_uri;
	}
	
	/**
	 * Get flag icon for language.
	 *
	 * @param string $lang Language code.
	 * @return string Flag emoji or default icon.
	 */
	public function get_flag_icon( $lang ) {
		return isset( $this->flag_icons[ $lang ] ) ? $this->flag_icons[ $lang ] : '🌐';
	}
	
	/**
	 * Add language prefix to menu links to maintain language persistence.
	 * 
	 * Fixes the issue where clicking menu items in /de/ goes back to English root.
	 * This filter intercepts every menu link and adds the current language prefix.
	 *
	 * @param array    $atts  The HTML attributes applied to the menu item's <a> element.
	 * @param WP_Post  $item  The current menu item.
	 * @param stdClass $args  An object of wp_nav_menu() arguments.
	 * @return array Modified attributes with language prefix in href.
	 */
	public function add_language_to_menu_links( $atts, $item, $args ) {
		// Get current language
		$language_detector = amst()->language_detector;
		$current_lang = $language_detector->get_current_language();
		$default_lang = $language_detector->get_default_language();
		
		// Only modify if we're not in default language
		if ( $current_lang === $default_lang ) {
			return $atts;
		}
		
		// Get the menu item URL
		$url = isset( $atts['href'] ) ? $atts['href'] : '';
		
		if ( empty( $url ) ) {
			return $atts;
		}
		
		// Get home URL for comparison
		$home_url = untrailingslashit( home_url( '/' ) );
		
		// Check if this URL belongs to our site (not external)
		if ( strpos( $url, $home_url ) !== 0 ) {
			// External link or different domain - don't modify
			return $atts;
		}
		
		// Extract the path after home URL
		$path = str_replace( $home_url, '', $url );
		$path = trim( $path, '/' );
		
		// Split path into segments
		$path_segments = empty( $path ) ? array() : explode( '/', $path );
		
		// Get all enabled language codes
		$enabled_languages = $language_detector->get_enabled_languages();
		
		// Check if path already has a language prefix
		if ( ! empty( $path_segments[0] ) && in_array( $path_segments[0], $enabled_languages, true ) ) {
			// Already has a language prefix - don't add another one (prevent stacking)
			return $atts;
		}
		
		// Now add the current language prefix
		if ( empty( $path ) ) {
			// This is the home link - point to language root
			// e.g., janadory.com/de/
			$atts['href'] = trailingslashit( $home_url . '/' . $current_lang );
		} else {
			// This is a regular menu item - add language prefix
			// e.g., janadory.com/contact → janadory.com/de/contact
			$atts['href'] = trailingslashit( $home_url . '/' . $current_lang . '/' . $path );
		}
		
		return $atts;
	}
}

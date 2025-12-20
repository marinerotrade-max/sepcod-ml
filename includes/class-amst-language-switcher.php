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
		// v1.5.1: Removed JavaScript enqueuing - pure PHP links only
		add_shortcode( 'amst_language_switcher', array( $this, 'render_shortcode' ) );
		add_shortcode( 'language_switcher', array( $this, 'render_shortcode' ) ); // v1.5.0: Add alias shortcode
		add_filter( 'home_url', array( $this, 'filter_home_url' ), 10, 2 );
	}
	
	/**
	 * Render language switcher shortcode - v1.6.0 Uses Language_Context.
	 * Theme-independent solution with direct href links.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function render_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'show_names' => 'yes',
				'position'   => 'inline',
				'style'      => 'list', // list or dropdown
			),
			$atts,
			'language_switcher'
		);
		
		// v1.6.0: Use centralized Language_Context
		$language_context = AMST_Language_Context::instance();
		$translator       = amst()->translator;
		
		$current_lang = $language_context->get_current_language();
		$enabled_languages = $language_context->get_enabled_languages();
		$supported_languages = $translator->get_supported_languages();
		
		$show_names = 'yes' === $atts['show_names'];
		$position = $atts['position'];
		$style = $atts['style'];
		
		// Position class
		$position_class = '';
		if ( in_array( $position, array( 'bottom-right', 'bottom-left', 'top-right', 'top-left' ), true ) ) {
			$position_class = ' amst-fixed amst-position-' . esc_attr( $position );
		}
		
		// v1.5.0: Render with direct href links (NO JavaScript events)
		ob_start();
		?>
		<div class="amst-language-switcher-v5<?php echo esc_attr( $position_class ); ?>">
			<?php if ( 'dropdown' === $style ) : ?>
				<!-- Dropdown style -->
				<select class="amst-language-select" onchange="if(this.value) window.location.href=this.value;">
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
			<?php else : ?>
				<!-- List style (default) - Direct href links -->
				<ul class="amst-language-list">
					<?php foreach ( $enabled_languages as $lang_code ) : ?>
						<?php
						$flag = isset( $this->flag_icons[ $lang_code ] ) ? $this->flag_icons[ $lang_code ] : '🌐';
						$name = isset( $supported_languages[ $lang_code ] ) ? $supported_languages[ $lang_code ] : $lang_code;
						$url = $this->get_language_url( $lang_code );
						$is_current = ( $lang_code === $current_lang );
						$active_class = $is_current ? ' active' : '';
						?>
						<li class="amst-lang-item<?php echo esc_attr( $active_class ); ?>">
							<a href="<?php echo esc_url( $url ); ?>" class="amst-lang-link" data-lang="<?php echo esc_attr( $lang_code ); ?>">
								<span class="amst-flag"><?php echo esc_html( $flag ); ?></span>
								<?php if ( $show_names ) : ?>
									<span class="amst-lang-name"><?php echo esc_html( $name ); ?></span>
								<?php endif; ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	
	/**
	 * Get URL for switching to a specific language.
	 * v1.6.0: Now uses centralized Language_Context for URL generation.
	 * 
	 * This method ONLY generates URLs, it does NOT detect or set any language state.
	 *
	 * @param string $lang Language code.
	 * @return string Language-specific URL (absolute path from root).
	 */
	private function get_language_url( $lang ) {
		$language_context = AMST_Language_Context::instance();
		$default_lang = $language_context->get_default_language();
		
		// Get home URL (always absolute)
		$home_url = untrailingslashit( home_url( '/' ) );
		
		// Check if we're on homepage
		$is_homepage = $this->is_homepage();
		
		// Special handling for homepage
		if ( $is_homepage ) {
			if ( $lang === $default_lang ) {
				return trailingslashit( $home_url );
			} else {
				return trailingslashit( $home_url . '/' . $lang );
			}
		}
		
		// For all other pages, get current URL and use Language_Context to generate language URL
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		
		if ( empty( $request_uri ) ) {
			global $wp;
			$request_uri = '/' . ( isset( $wp->request ) ? $wp->request : '' );
		}
		
		// Build full current URL
		$current_url = $home_url . $request_uri;
		
		// Use Language_Context to generate the language-specific URL
		return $language_context->get_language_url( $current_url, $lang );
	}
	
	/**
	 * Detect if current page is homepage using multiple methods.
	 *
	 * @return bool True if homepage.
	 */
	private function is_homepage() {
		// Method 1: Check REQUEST_URI first (most reliable)
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		
		// Parse to remove query string
		$uri_parts = explode( '?', $request_uri, 2 );
		$path = trim( $uri_parts[0], '/' );
		
		// If we have a non-empty path, check if it's just a language code or has actual content
		if ( ! empty( $path ) ) {
			// Remove language prefix if present
			$language_detector = amst()->language_detector;
			$enabled_languages = $language_detector->get_enabled_languages();
			$path_parts = explode( '/', $path );
			
			if ( ! empty( $path_parts[0] ) && in_array( $path_parts[0], $enabled_languages, true ) ) {
				array_shift( $path_parts );
				$path = implode( '/', $path_parts );
			}
			
			// If path still has content after removing language prefix, it's NOT homepage
			if ( ! empty( $path ) ) {
				return false;
			}
			
			// If path is now empty, it was just a language prefix - this IS homepage
			return true;
		}
		
		// Path was empty from the start - this is homepage
		return true;
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
	 * Filter home_url to add language prefix for menu links.
	 * This ensures Home button and logo link to the correct language homepage.
	 *
	 * @param string $url The home URL.
	 * @param string $path Path relative to home URL.
	 * @return string Modified home URL with language prefix.
	 */
	public function filter_home_url( $url, $path ) {
		// Don't modify in admin
		if ( is_admin() ) {
			return $url;
		}
		
		$language_detector = amst()->language_detector;
		$current_lang = $language_detector->get_current_language();
		$default_lang = $language_detector->get_default_language();
		
		// If current language is default, no modification needed
		if ( $current_lang === $default_lang ) {
			return $url;
		}
		
		// Check if URL already has language prefix to avoid duplication
		$parsed_url = wp_parse_url( $url );
		$url_path = isset( $parsed_url['path'] ) ? trim( $parsed_url['path'], '/' ) : '';
		$path_parts = explode( '/', $url_path );
		
		$enabled_languages = $language_detector->get_enabled_languages();
		
		// If first part is already a language code, don't add another
		if ( ! empty( $path_parts[0] ) && in_array( $path_parts[0], $enabled_languages, true ) ) {
			return $url;
		}
		
		// Add language prefix to home URL
		$home_url = untrailingslashit( get_option( 'home' ) );
		
		if ( empty( $path ) || $path === '/' ) {
			// Homepage link - add language prefix
			return trailingslashit( $home_url . '/' . $current_lang );
		} else {
			// Other paths - add language prefix before the path
			return $home_url . '/' . $current_lang . '/' . ltrim( $path, '/' );
		}
	}
}

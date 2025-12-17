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
		
		// Render simple dropdown switcher
		ob_start();
		?>
		<div class="amst-language-switcher-simple">
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
	 * @param string $lang Language code.
	 * @return string Language-specific URL.
	 */
	private function get_language_url( $lang ) {
		$language_detector = amst()->language_detector;
		$default_lang = $language_detector->get_default_language();
		$current_url = $this->get_current_url();
		
		if ( $lang === $default_lang ) {
			// Remove language prefix for default language
			return $language_detector->remove_language_prefix( $current_url );
		} else {
			// Add/change language prefix
			return $language_detector->get_language_url( $current_url, $lang );
		}
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
}

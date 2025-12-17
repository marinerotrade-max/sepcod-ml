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
	 * Render language switcher shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function render_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'style'      => 'modal', // 'modal', 'dropdown', or 'inline'
				'show_names' => get_option( 'amst_switcher_show_names', 'no' ),
				'flag_size'  => get_option( 'amst_switcher_flag_size', 'medium' ),
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
		$flag_size_class = 'amst-flag-' . sanitize_html_class( $atts['flag_size'] );
		
		ob_start();
		
		if ( 'modal' === $atts['style'] ) {
			$this->render_modal_switcher( $current_lang, $enabled_languages, $supported_languages, $show_names, $flag_size_class );
		} elseif ( 'dropdown' === $atts['style'] ) {
			$this->render_dropdown_switcher( $current_lang, $enabled_languages, $supported_languages, $show_names, $flag_size_class );
		} else {
			$this->render_inline_switcher( $current_lang, $enabled_languages, $supported_languages, $show_names, $flag_size_class );
		}
		
		return ob_get_clean();
	}
	
	/**
	 * Render modal-style language switcher.
	 *
	 * @param string $current_lang Current language code.
	 * @param array  $enabled_languages Enabled language codes.
	 * @param array  $supported_languages Language code to name mapping.
	 * @param bool   $show_names Whether to show language names.
	 * @param string $flag_size_class CSS class for flag size.
	 */
	private function render_modal_switcher( $current_lang, $enabled_languages, $supported_languages, $show_names, $flag_size_class ) {
		$current_flag = isset( $this->flag_icons[ $current_lang ] ) ? $this->flag_icons[ $current_lang ] : '🌐';
		$current_name = isset( $supported_languages[ $current_lang ] ) ? $supported_languages[ $current_lang ] : $current_lang;
		
		// Check if user has already interacted with the language switcher
		$has_selected = isset( $_COOKIE['amst_language_selected'] ) && $_COOKIE['amst_language_selected'] === 'yes';
		
		?>
		<div class="amst-language-switcher amst-modal-style <?php echo esc_attr( $flag_size_class ); ?>">
			<button class="amst-current-language" aria-label="<?php esc_attr_e( 'Select Language', 'auto-multilingual-seo' ); ?>">
				<span class="amst-flag"><?php echo esc_html( $current_flag ); ?></span>
				<?php if ( $show_names ) : ?>
					<span class="amst-lang-name"><?php echo esc_html( $current_name ); ?></span>
				<?php endif; ?>
				<span class="amst-dropdown-arrow">▼</span>
			</button>
			
			<!-- Always render modal, JS will handle auto-show behavior -->
			<div class="amst-modal-overlay" style="display: none;" data-auto-show="<?php echo $has_selected ? 'no' : 'yes'; ?>">
				<div class="amst-modal-content">
					<div class="amst-modal-header">
						<h3><?php echo esc_html( get_option( 'amst_switcher_modal_title', __( 'Select Language', 'auto-multilingual-seo' ) ) ); ?></h3>
						<button class="amst-modal-close" aria-label="<?php esc_attr_e( 'Close', 'auto-multilingual-seo' ); ?>">&times;</button>
					</div>
					<div class="amst-modal-body">
						<div class="amst-languages-grid">
							<?php foreach ( $enabled_languages as $lang_code ) : ?>
								<?php
								$flag = isset( $this->flag_icons[ $lang_code ] ) ? $this->flag_icons[ $lang_code ] : '🌐';
								$name = isset( $supported_languages[ $lang_code ] ) ? $supported_languages[ $lang_code ] : $lang_code;
								$url = $this->get_language_url( $lang_code );
								$is_current = ( $lang_code === $current_lang );
								?>
								<a href="<?php echo esc_url( $url ); ?>" 
								   class="amst-language-option <?php echo $is_current ? 'amst-current' : ''; ?>"
								   data-lang="<?php echo esc_attr( $lang_code ); ?>">
									<span class="amst-flag"><?php echo esc_html( $flag ); ?></span>
									<span class="amst-lang-name"><?php echo esc_html( $name ); ?></span>
								</a>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
	
	/**
	 * Render dropdown-style language switcher.
	 *
	 * @param string $current_lang Current language code.
	 * @param array  $enabled_languages Enabled language codes.
	 * @param array  $supported_languages Language code to name mapping.
	 * @param bool   $show_names Whether to show language names.
	 * @param string $flag_size_class CSS class for flag size.
	 */
	private function render_dropdown_switcher( $current_lang, $enabled_languages, $supported_languages, $show_names, $flag_size_class ) {
		$current_flag = isset( $this->flag_icons[ $current_lang ] ) ? $this->flag_icons[ $current_lang ] : '🌐';
		$current_name = isset( $supported_languages[ $current_lang ] ) ? $supported_languages[ $current_lang ] : $current_lang;
		
		?>
		<div class="amst-language-switcher amst-dropdown-style <?php echo esc_attr( $flag_size_class ); ?>">
			<button class="amst-current-language" aria-label="<?php esc_attr_e( 'Select Language', 'auto-multilingual-seo' ); ?>">
				<span class="amst-flag"><?php echo esc_html( $current_flag ); ?></span>
				<?php if ( $show_names ) : ?>
					<span class="amst-lang-name"><?php echo esc_html( $current_name ); ?></span>
				<?php endif; ?>
				<span class="amst-dropdown-arrow">▼</span>
			</button>
			
			<div class="amst-dropdown-menu" style="display: none;">
				<?php foreach ( $enabled_languages as $lang_code ) : ?>
					<?php
					if ( $lang_code === $current_lang ) {
						continue;
					}
					$flag = isset( $this->flag_icons[ $lang_code ] ) ? $this->flag_icons[ $lang_code ] : '🌐';
					$name = isset( $supported_languages[ $lang_code ] ) ? $supported_languages[ $lang_code ] : $lang_code;
					$url = $this->get_language_url( $lang_code );
					?>
					<a href="<?php echo esc_url( $url ); ?>" 
					   class="amst-language-option"
					   data-lang="<?php echo esc_attr( $lang_code ); ?>">
						<span class="amst-flag"><?php echo esc_html( $flag ); ?></span>
						<span class="amst-lang-name"><?php echo esc_html( $name ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}
	
	/**
	 * Render inline-style language switcher.
	 *
	 * @param string $current_lang Current language code.
	 * @param array  $enabled_languages Enabled language codes.
	 * @param array  $supported_languages Language code to name mapping.
	 * @param bool   $show_names Whether to show language names.
	 * @param string $flag_size_class CSS class for flag size.
	 */
	private function render_inline_switcher( $current_lang, $enabled_languages, $supported_languages, $show_names, $flag_size_class ) {
		?>
		<div class="amst-language-switcher amst-inline-style <?php echo esc_attr( $flag_size_class ); ?>">
			<?php foreach ( $enabled_languages as $lang_code ) : ?>
				<?php
				$flag = isset( $this->flag_icons[ $lang_code ] ) ? $this->flag_icons[ $lang_code ] : '🌐';
				$name = isset( $supported_languages[ $lang_code ] ) ? $supported_languages[ $lang_code ] : $lang_code;
				$url = $this->get_language_url( $lang_code );
				$is_current = ( $lang_code === $current_lang );
				?>
				<a href="<?php echo esc_url( $url ); ?>" 
				   class="amst-language-option <?php echo $is_current ? 'amst-current' : ''; ?>"
				   data-lang="<?php echo esc_attr( $lang_code ); ?>"
				   title="<?php echo esc_attr( $name ); ?>">
					<span class="amst-flag"><?php echo esc_html( $flag ); ?></span>
					<?php if ( $show_names ) : ?>
						<span class="amst-lang-name"><?php echo esc_html( $name ); ?></span>
					<?php endif; ?>
				</a>
			<?php endforeach; ?>
		</div>
		<?php
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

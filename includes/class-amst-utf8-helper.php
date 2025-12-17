<?php
/**
 * UTF-8 Encoding Helper for special characters support.
 *
 * Handles Croatian (č, š, ž, đ, ć) and all other special characters
 * across all EU languages.
 *
 * @package AutoMultilingualSEO
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AMST_UTF8_Helper class.
 */
class AMST_UTF8_Helper {
	
	/**
	 * Sanitize and normalize UTF-8 text for proper display.
	 *
	 * This function ensures translations with special characters
	 * (Croatian: č, š, ž, đ, ć; French: é, è, ê, à, etc.) display correctly.
	 *
	 * @param string $text Text to sanitize and normalize.
	 * @return string Properly encoded UTF-8 text.
	 */
	public static function sanitize_translation_output( $text ) {
		if ( empty( $text ) ) {
			return $text;
		}
		
		// Step 1: Check if string is valid UTF-8, convert if not
		if ( ! mb_check_encoding( $text, 'UTF-8' ) ) {
			// Try to detect original encoding
			$detected_encoding = mb_detect_encoding( $text, mb_detect_order(), true );
			if ( $detected_encoding !== false ) {
				$text = mb_convert_encoding( $text, 'UTF-8', $detected_encoding );
			} else {
				// Fallback: assume ISO-8859-1 (Latin-1)
				$text = mb_convert_encoding( $text, 'UTF-8', 'ISO-8859-1' );
			}
		}
		
		// Step 2: Decode HTML entities (handles &#263; → ć, &eacute; → é, etc.)
		// ENT_QUOTES handles both single and double quotes
		$text = html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		
		// Step 3: Normalize UTF-8 (NFC - Canonical Decomposition followed by Canonical Composition)
		// This ensures consistent representation of accented characters
		if ( class_exists( 'Normalizer' ) && Normalizer::isNormalized( $text, Normalizer::FORM_C ) === false ) {
			$text = Normalizer::normalize( $text, Normalizer::FORM_C );
		}
		
		// Step 4: Remove any invalid UTF-8 sequences
		$text = mb_convert_encoding( $text, 'UTF-8', 'UTF-8' );
		
		return $text;
	}
	
	/**
	 * Prepare text for database storage with UTF-8 encoding.
	 *
	 * @param string $text Text to prepare for storage.
	 * @return string Text ready for database insertion.
	 */
	public static function prepare_for_database( $text ) {
		if ( empty( $text ) ) {
			return $text;
		}
		
		// Ensure UTF-8
		if ( ! mb_check_encoding( $text, 'UTF-8' ) ) {
			$detected_encoding = mb_detect_encoding( $text, mb_detect_order(), true );
			if ( $detected_encoding !== false ) {
				$text = mb_convert_encoding( $text, 'UTF-8', $detected_encoding );
			}
		}
		
		// Normalize
		if ( class_exists( 'Normalizer' ) ) {
			$text = Normalizer::normalize( $text, Normalizer::FORM_C );
		}
		
		return $text;
	}
	
	/**
	 * Ensure database connection uses utf8mb4 charset.
	 *
	 * Checks and reports on database charset configuration.
	 *
	 * @return array Status information about database charset.
	 */
	public static function check_database_charset() {
		global $wpdb;
		
		$status = array(
			'wpdb_charset' => $wpdb->charset,
			'wpdb_collate' => $wpdb->collate,
			'recommended' => 'utf8mb4',
			'is_correct' => false,
		);
		
		// Check if using utf8mb4 (WordPress standard for full Unicode support)
		if ( 'utf8mb4' === $wpdb->charset ) {
			$status['is_correct'] = true;
			$status['message'] = 'Database is correctly configured for UTF-8 support.';
		} else {
			$status['message'] = 'Database charset is not utf8mb4. Some special characters may not display correctly.';
		}
		
		return $status;
	}
	
	/**
	 * Force database query to use utf8mb4 charset.
	 *
	 * Call this before database queries that involve translation data.
	 *
	 * @param wpdb $wpdb WordPress database object.
	 */
	public static function set_charset_utf8mb4( $wpdb = null ) {
		if ( null === $wpdb ) {
			global $wpdb;
		}
		
		// Set connection charset to utf8mb4
		if ( method_exists( $wpdb, 'set_charset' ) ) {
			$wpdb->set_charset( $wpdb->dbh, 'utf8mb4', 'utf8mb4_unicode_ci' );
		}
		
		// Also set SQL mode for proper charset handling
		$wpdb->query( "SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'" );
	}
	
	/**
	 * Validate and fix a translation string for output.
	 *
	 * This is the main function to use before outputting any translated text.
	 * Handles all encoding issues comprehensively.
	 *
	 * @param string $translated_text The translated text to fix.
	 * @return string Fixed and validated UTF-8 text.
	 */
	public static function fix_translation_encoding( $translated_text ) {
		if ( empty( $translated_text ) ) {
			return $translated_text;
		}
		
		// Step 1: Sanitize and normalize
		$fixed_text = self::sanitize_translation_output( $translated_text );
		
		// Step 2: Additional decoding for double-encoded content
		// Sometimes translations come back with entities that are then encoded again
		if ( false !== strpos( $fixed_text, '&amp;' ) || false !== strpos( $fixed_text, '&#' ) ) {
			$fixed_text = html_entity_decode( $fixed_text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		}
		
		// Step 3: Fix common encoding issues for Croatian and other special chars
		// Google Translate sometimes returns these incorrectly
		$replacements = array(
			// Croatian
			'&#269;' => 'č',
			'&#268;' => 'Č',
			'&#263;' => 'ć',
			'&#262;' => 'Ć',
			'&#273;' => 'đ',
			'&#272;' => 'Đ',
			'&#353;' => 'š',
			'&#352;' => 'Š',
			'&#382;' => 'ž',
			'&#381;' => 'Ž',
			// Common HTML entities
			'&eacute;' => 'é',
			'&egrave;' => 'è',
			'&ecirc;' => 'ê',
			'&agrave;' => 'à',
			'&acirc;' => 'â',
			'&uuml;' => 'ü',
			'&ouml;' => 'ö',
			'&auml;' => 'ä',
			'&szlig;' => 'ß',
			'&ntilde;' => 'ñ',
		);
		
		$fixed_text = str_replace( array_keys( $replacements ), array_values( $replacements ), $fixed_text );
		
		// Step 4: Final UTF-8 validation
		$fixed_text = mb_convert_encoding( $fixed_text, 'UTF-8', 'UTF-8' );
		
		return $fixed_text;
	}
	
	/**
	 * Escape translation output for HTML display.
	 *
	 * Use this when outputting to HTML to prevent XSS while preserving special characters.
	 *
	 * @param string $text Text to escape.
	 * @param bool   $double_encode Whether to double encode (default false for translations).
	 * @return string Escaped text safe for HTML output.
	 */
	public static function esc_translation_html( $text, $double_encode = false ) {
		// First fix encoding issues
		$text = self::fix_translation_encoding( $text );
		
		// Then escape for HTML
		// Using ENT_NOQUOTES to preserve quotes in translations
		return htmlspecialchars( $text, ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8', $double_encode );
	}
}

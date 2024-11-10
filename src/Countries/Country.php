<?php
/**
 * Country Utility Class for Easy Digital Downloads (EDD)
 *
 * @package       ArrayPress/EDD-Utils
 * @copyright     Copyright 2024, ArrayPress Limited
 * @license       GPL-2.0-or-later
 * @version       1.0.0
 * @author        David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\EDD\Countries;

/**
 * Class Country
 *
 * Static utility methods for working with countries.
 */
class Country {

	/**
	 * Get the country name for a given country code.
	 *
	 * @param string $code The two-letter ISO country code.
	 *
	 * @return string The country name or empty string if not found.
	 */
	public static function get_name( string $code ): string {
		if ( ! self::is_valid( $code ) ) {
			return '';
		}

		$countries = edd_get_country_list();

		return isset( $countries[ strtoupper( $code ) ] )
			? esc_html( html_entity_decode( $countries[ strtoupper( $code ) ] ) )
			: '';
	}

	/**
	 * Get the flag emoji for a country code.
	 *
	 * @param string $code The two-letter ISO country code.
	 *
	 * @return string The flag emoji for the country code or an empty string if invalid.
	 */
	public static function get_flag( string $code ): string {
		if ( ! self::is_valid( $code ) ) {
			return '';
		}

		$code = strtoupper( $code );

		// Calculate Unicode code points for regional indicator symbols
		$first_letter  = ord( $code[0] ) - ord( 'A' ) + 0x1F1E6;
		$second_letter = ord( $code[1] ) - ord( 'A' ) + 0x1F1E6;

		// Convert code points to UTF-8 characters and return the flag emoji
		return mb_convert_encoding(
			pack( 'N*', $first_letter, $second_letter ),
			'UTF-8',
			'UTF-32BE'
		);
	}

	/**
	 * Check if a country code is valid.
	 *
	 * @param string $code The two-letter ISO country code to validate.
	 *
	 * @return bool True if the country code is valid, false otherwise.
	 */
	public static function is_valid( string $code ): bool {
		$code = strtoupper( trim( $code ) );

		if ( empty( $code ) || strlen( $code ) !== 2 ) {
			return false;
		}

		$countries = edd_get_country_list();

		return isset( $countries[ $code ] );
	}

	/**
	 * Format the country code and name together.
	 *
	 * @param string $code         The two-letter ISO country code.
	 * @param bool   $include_flag Optional. Whether to include the flag emoji. Default true.
	 *
	 * @return string Formatted country string or empty string if invalid.
	 */
	public static function format( string $code, bool $include_flag = true ): string {
		if ( ! self::is_valid( $code ) ) {
			return '';
		}

		$code = strtoupper( $code );
		$name = self::get_name( $code );
		$flag = $include_flag ? self::get_flag( $code ) . ' ' : '';

		return sprintf( '%s%s (%s)', $flag, $name, $code );
	}

	/**
	 * Get all details for a country code.
	 *
	 * @param string $code The two-letter ISO country code.
	 *
	 * @return array{name: string, code: string, flag: string}|array<empty> Country details or empty array if invalid.
	 */
	public static function get_details( string $code ): array {
		if ( ! self::is_valid( $code ) ) {
			return [];
		}

		$code = strtoupper( $code );

		return [
			'name' => self::get_name( $code ),
			'code' => $code,
			'flag' => self::get_flag( $code ),
		];
	}

	/**
	 * Normalize a country code.
	 *
	 * @param string $code The country code to normalize.
	 *
	 * @return string The normalized country code or empty string if invalid.
	 */
	public static function normalize( string $code ): string {
		$code = strtoupper( trim( $code ) );

		return self::is_valid( $code ) ? $code : '';
	}

	/**
	 * Search for countries by name.
	 *
	 * @param string $search         The search term.
	 * @param bool   $case_sensitive Optional. Whether the search should be case-sensitive. Default false.
	 *
	 * @return array<string, string> Array of matching country codes and names.
	 */
	public static function search( string $search, bool $case_sensitive = false ): array {
		if ( empty( $search ) ) {
			return [];
		}

		$countries = edd_get_country_list();
		$results   = [];

		foreach ( $countries as $code => $name ) {
			$haystack = $case_sensitive ? $name : strtolower( $name );
			$needle   = $case_sensitive ? $search : strtolower( $search );

			if ( strpos( $haystack, $needle ) !== false ) {
				$results[ $code ] = $name;
			}
		}

		return $results;
	}


	/**
	 * Get all states/regions for a country code.
	 *
	 * @param string $code The two-letter ISO country code.
	 *
	 * @return array<string, string> Array of state codes and names.
	 */
	public static function get_states( string $code ): array {
		if ( ! self::is_valid( $code ) ) {
			return [];
		}

		$countries = new \EDD\Utils\Countries();

		return $countries->get_states( strtoupper( $code ) );
	}

	/**
	 * Get a specific state/region name.
	 *
	 * @param string $code       The two-letter ISO country code.
	 * @param string $state_code The state/region code.
	 *
	 * @return string The state name or empty string if not found.
	 */
	public static function get_state_name( string $code, string $state_code ): string {
		if ( ! self::is_valid( $code ) ) {
			return '';
		}

		$countries = new \EDD\Utils\Countries();

		return $countries->get_state_name( strtoupper( $code ), $state_code );
	}

	/**
	 * Check if a country has states/regions.
	 *
	 * @param string $code The two-letter ISO country code.
	 *
	 * @return bool True if the country has states, false otherwise.
	 */
	public static function has_states( string $code ): bool {
		return ! empty( self::get_states( $code ) );
	}

	/**
	 * Check if a state/region exists for a country.
	 *
	 * @param string $code       The two-letter ISO country code.
	 * @param string $state_code The state/region code to validate.
	 *
	 * @return bool True if the state exists, false otherwise.
	 */
	public static function has_state( string $code, string $state_code ): bool {
		$states = self::get_states( $code );

		return isset( $states[ $state_code ] );
	}

	/**
	 * Get formatted state/region information.
	 *
	 * @param string $code       The two-letter ISO country code.
	 * @param string $state_code The state/region code.
	 *
	 * @return array{name: string, code: string, country_code: string}|array<empty> State details or empty array if
	 *                     invalid.
	 */
	public static function get_state_details( string $code, string $state_code ): array {
		if ( ! self::has_state( $code, $state_code ) ) {
			return [];
		}

		return [
			'name'         => self::get_state_name( $code, $state_code ),
			'code'         => $state_code,
			'country_code' => strtoupper( $code ),
		];
	}

}
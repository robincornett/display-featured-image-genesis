<?php

/**
 * SixTenPressShortcodes loader
 *
 * Handles checking for and smartly loading the newest version of this library.
 *
 * @category  WordPressLibrary
 * @package   SixTenPressShortcodes
 * @author    Robin Cornett <hello@robincornett.com>
 * @copyright 2016 Robin Cornett <hello@robincornett.com>
 * @license   GPL-2.0+
 * @version   0.3.2
 * @link      https://gitlab.com/robincornett/sixtenpress-shortcodes
 * @since     0.1.0
 */

/**
 * Copyright (c) 2017 Robin Cornett (email : hello@robincornett.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/**
 * Loader versioning: http://jtsternberg.github.io/wp-lib-loader/
 */

if ( ! class_exists( 'SixTenPressShortcodes_032', false ) ) {

	/**
	 * Versioned loader class-name
	 *
	 * This ensures each version is loaded/checked.
	 *
	 * @category WordPressLibrary
	 * @package  SixTenPressShortcodes
	 * @author   Robin Cornett <hello@robincornett.com>
	 * @license  GPL-2.0+
	 * @version  0.3.2
	 * @link     https://gitlab.com/robincornett/sixtenpress-shortcodes
	 * @since    0.1.0
	 */
	class SixTenPressShortcodes_032 {

		/**
		 * SixTenPressShortcodes version number
		 * @var   string
		 * @since 0.1.0
		 */
		const VERSION = '0.3.2';

		/**
		 * Current version hook priority.
		 * Will decrement with each release
		 *
		 * @var   int
		 * @since 0.1.0
		 */
		const PRIORITY = 9992;

		/**
		 * Starts the version checking process.
		 * Creates SIXTENPRESSSHORTCODES_LOADED definition for early detection by
		 * other scripts.
		 *
		 * Hooks SixTenPressShortcodes inclusion to the sixtenpressshortcodes_load hook
		 * on a high priority which decrements (increasing the priority) with
		 * each version release.
		 *
		 * @since 0.1.0
		 */
		public function __construct() {
			if ( ! defined( 'SIXTENPRESSSHORTCODES_LOADED' ) ) {
				/**
				 * A constant you can use to check if SixTenPressShortcodes is loaded
				 * for your plugins/themes with SixTenPressShortcodes dependency.
				 *
				 * Can also be used to determine the priority of the hook
				 * in use for the currently loaded version.
				 */
				define( 'SIXTENPRESSSHORTCODES_LOADED', self::PRIORITY );
			}

			// Use the hook system to ensure only the newest version is loaded.
			add_action( 'sixtenpressshortcodes_load', array( $this, 'include_lib' ), self::PRIORITY );

			/*
			 * Hook in to the first hook we have available and
			 * fire our `sixtenpressshortcodes_load' hook.
			 */
			add_action( 'muplugins_loaded', array( __CLASS__, 'fire_hook' ), 9 );
			add_action( 'plugins_loaded', array( __CLASS__, 'fire_hook' ), 9 );
			add_action( 'after_setup_theme', array( __CLASS__, 'fire_hook' ), 9 );
		}

		/**
		 * Fires the sixtenpressshortcodes_load action hook.
		 *
		 * @since 0.1.0
		 */
		public static function fire_hook() {
			if ( ! did_action( 'sixtenpressshortcodes_load' ) ) {
				// Then fire our hook.
				do_action( 'sixtenpressshortcodes_load' );
			}
		}

		/**
		 * A final check if SixTenPressShortcodes exists before kicking off
		 * our SixTenPressShortcodes loading.
		 *
		 * SIXTENPRESSSHORTCODES_VERSION and SIXTENPRESSSHORTCODES_DIR constants are
		 * set at this point.
		 *
		 * @since  0.1.0
		 */
		public function include_lib() {
			if ( class_exists( 'SixTenPressShortcodes', false ) ) {
				return;
			}

			if ( ! defined( 'SIXTENPRESSSHORTCODES_VERSION' ) ) {
				/**
				 * Defines the currently loaded version of SixTenPressShortcodes.
				 */
				define( 'SIXTENPRESSSHORTCODES_VERSION', self::VERSION );
			}

			if ( ! defined( 'SIXTENPRESSSHORTCODES_DIR' ) ) {
				/**
				 * Defines the directory of the currently loaded version of SixTenPressShortcodes.
				 */
				define( 'SIXTENPRESSSHORTCODES_DIR', dirname( __FILE__ ) . '/' );
			}

			// Include and initiate SixTenPressShortcodes.
			require_once SIXTENPRESSSHORTCODES_DIR . 'includes/class-sixtenpress-shortcodes.php';
		}

	}

	// Kick it off.
	new SixTenPressShortcodes_032();
}

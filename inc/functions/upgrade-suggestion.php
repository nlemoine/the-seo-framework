<?php
/**
 * @package The_SEO_Framework\Suggestion
 * @subpackage The_SEO_Framework\Bootstrap\Install
 */

namespace The_SEO_Framework\Suggestion;

/**
 * The SEO Framework plugin
 * Copyright (C) 2018 - 2020 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 3 as published
 * by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.

\defined( 'THE_SEO_FRAMEWORK_PRESENT' ) or die;

/**
 * This file holds functions for installing TSFEM.
 * This file will only be called ONCE on plugin install, or upgrade from pre-v3.0.6.
 *
 * @since 3.0.6
 * @since 3.2.4 Applied namspacing to this file. All method names have changed.
 * @access private
 */

// phpcs:ignore, TSF.Performance.Opcodes.ShouldHaveNamespaceEscape
_prepare( $previous_version, $current_version );
/**
 * Prepares a suggestion notification to ALL applicable plugin users on upgrade;
 * For TSFEM, it's shown when:
 *    0. The upgrade actually happened.
 *    1. The constant 'TSF_DISABLE_SUGGESTIONS' is not defined or false.
 *    2. The current dashboard is the main site's.
 *    3. TSFEM isn't already installed.
 *    4. PHP and WP requirements of TSFEM are met.
 *
 * The notice is automatically dismissed after X views, and it can be ignored without reappearing.
 *
 * @since 3.0.6
 * @since 4.1.0 1. Now tests TSFEM 2.4.0 requirements.
 *              2. Removed the user capability requirement, and forwarded that to `_suggest_extension_manager()`.
 *              3. Can now run on the front-end without crashing.
 *              4. Added the first two parameters, $previous_version and $current_version.
 *              5. Now tests if the upgrade actually happened, before invoking the suggestion.
 * @access private
 *
 * @param string $previous_version The previous version the site upgraded from, if any.
 * @param string $current_version  The current version of the site.
 */
function _prepare( $previous_version, $current_version ) {

	//? 0
	// phpcs:ignore, WordPress.PHP.StrictComparisons.LooseComparison -- might be mixed types.
	if ( $previous_version == $current_version ) return;
	//? 1
	if ( \defined( 'TSF_DISABLE_SUGGESTIONS' ) && TSF_DISABLE_SUGGESTIONS ) return;
	//? 2
	if ( ! \is_main_site() ) return;
	//? 3a
	if ( \defined( 'TSF_EXTENSION_MANAGER_VERSION' ) ) return;

	if ( ! \function_exists( '\\get_plugins' ) )
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

	//? 3b
	if ( ! empty( \get_plugins()['the-seo-framework-extension-manager/the-seo-framework-extension-manager.php'] ) ) return;

	/** @source https://github.com/sybrew/The-SEO-Framework-Extension-Manager/blob/08db1ab7410874c47d8f05b15479ce923857c35e/bootstrap/envtest.php#L68-L77 */
	// We can forgo this test, since TSF has a higher requirement. We'll probably keep the plugins in line henceforth...
	$requirements = [
		'php' => 50605,
		'wp'  => '4.9-dev',
	];

	// phpcs:disable, Generic.Formatting.MultipleStatementAlignment, WordPress.WhiteSpace.PrecisionAlignment
	//? PHP_VERSION_ID is definitely defined, but let's keep it homonymous with the envtest of TSFEM.
	   ! \defined( 'PHP_VERSION_ID' ) || PHP_VERSION_ID < $requirements['php'] and $test = 1
	or version_compare( $GLOBALS['wp_version'], $requirements['wp'], '<' ) and $test = 2
	or $test = true;
	// phpcs:enable, Generic.Formatting.MultipleStatementAlignment, WordPress.WhiteSpace.PrecisionAlignment

	//? 4
	if ( true !== $test ) return;

	// phpcs:ignore, TSF.Performance.Opcodes.ShouldHaveNamespaceEscape
	_suggest_extension_manager( $previous_version, $current_version );
}

/**
 * Registers "look at TSFEM" notification to applicable plugin users on upgrade.
 *
 * @since 3.0.6
 * @since 4.1.0 Is now a persistent notice, that outputs at most 3 times, on some admin pages, only for users that can install plugins.
 * @access private
 *
 * @param string $previous_version The previous version the site upgraded from, if any.
 * @param string $current_version  The current version of the site.
 */
function _suggest_extension_manager( $previous_version, $current_version ) {

	$tsf = \the_seo_framework();

	$suggest_key = 'suggest-extension-manager';

	if ( $previous_version < '4103' )
		$tsf->register_dismissible_persistent_notice(
			$tsf->convert_markdown(
				vsprintf(
					'<p>The SEO Framework was updated to v4.1! It brings 9 new features and [over 350 QOL improvements for performance and accessibility](%s).</p>
					<p>Did you know we have [10 premium extensions](%s), adding features beyond SEO? Our anti-spam extension runs locally, has a 99.98%% catch rate, and adds only 0.13KB to your website.</p>
					<p>We want to make TSF even better for you &mdash; please consider [filling out our survey](%s), it has 5 questions and should take you about 2 minutes. Thank you.</p>',
					[
						'https://theseoframework.com/?p=3598',
						'https://theseoframework.com/?p=3599',
						'https://theseoframework.com/?p=3591',
					]
				),
				[ 'a', 'em', 'strong' ],
				[ 'a_internal' => false ]
			),
			$suggest_key,
			[
				'type'   => 'info',
				'icon'   => false,
				'escape' => false,
			],
			[
				'screens'      => [],
				'excl_screens' => [ 'update-core', 'post', 'term', 'upload', 'media', 'plugin-editor', 'plugin-install', 'themes', 'widgets', 'user', 'nav-menus', 'theme-editor', 'profile', 'export', 'site-health', 'export-personal-data', 'erase-personal-data' ],
				'capability'   => 'install_plugins',
				'user'         => 0,
				'count'        => 3,
				'timeout'      => DAY_IN_SECONDS * 7,
			]
		);
}

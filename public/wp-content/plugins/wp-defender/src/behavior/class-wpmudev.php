<?php
/**
 * This file contains everything relate to WPMUDEV.
 *
 * @package WP_Defender\Behavior
 */

namespace WP_Defender\Behavior;

use WPMUDEV_Dashboard;
use WP_Defender\Traits\IO;
use WP_Defender\Traits\Formats;
use Calotes\Component\Behavior;
use WP_Defender\Traits\Defender_Hub_Client;
use WP_Defender\Traits\Defender_Dashboard_Client;
use WP_Defender\Component\Config\Config_Hub_Helper;

/**
 * This class contains everything relate to WPMUDEV.
 *
 * @since 2.2
 */
class WPMUDEV extends Behavior implements WPMUDEV_Const_Interface {

	use IO;
	use Formats;
	use Defender_Dashboard_Client;
	use Defender_Hub_Client;

	/**
	 * Get membership status.
	 *
	 * @return bool
	 */
	public function is_pro() {
		return $this->get_apikey() !== false;
	}

	/**
	 * Get WPMUDEV API KEY.
	 *
	 * @return bool|string
	 */
	public function get_apikey() {
		if ( ! class_exists( '\WPMUDEV_Dashboard' ) ) {
			return false;
		}

		WPMUDEV_Dashboard::instance();
		if (
			method_exists( WPMUDEV_Dashboard::$upgrader, 'user_can_install' )
			&& WPMUDEV_Dashboard::$upgrader->user_can_install(
				Config_Hub_Helper::WDP_ID,
				true
			)
		) {
			return WPMUDEV_Dashboard::$api->get_key();
		} else {
			return false;
		}
	}

	/**
	 * Check if whitelabel is enabled.
	 *
	 * @return bool Returns true if whitelabel is enabled, false otherwise.
	 * @since 2.5.5 Use Whitelabel filters instead of calling the whitelabel functions directly.
	 */
	public function is_whitelabel_enabled() {
		if ( $this->get_apikey() ) {
			// Use backward compatibility.
			if ( WPMUDEV_Dashboard::$version > '4.11.1' ) {
				$settings = apply_filters( 'wpmudev_branding', array() );

				return array() !== $settings;
			} else {
				$site     = WPMUDEV_Dashboard::$site;
				$settings = $site->get_whitelabel_settings();

				return $settings['enabled'];
			}
		}

		return false;
	}

	/**
	 * Hide WPMU DEV urls for the current user if:
	 * 1) Whitelabel option is enabled,
	 * 2) the user is not listed in WPMU DEV > Settings > Permissions.
	 *
	 * @return bool
	 * @since 4.1.0
	 */
	public function hide_wpmu_dev_urls(): bool {
		return $this->is_whitelabel_enabled() && ! $this->is_wpmu_dev_admin();
	}

	/**
	 * Show support links if:
	 * plugin version isn't Free,
	 * Whitelabel is disabled.
	 *
	 * @return bool
	 * @since 2.5.5
	 */
	public function show_support_links() {
		if ( $this->get_apikey() ) {
			// Use backward compatibility.
			if ( WPMUDEV_Dashboard::$version > '4.11.1' ) {
				$settings = apply_filters( 'wpmudev_branding', array() );

				return array() === $settings;
			} else {
				$site     = WPMUDEV_Dashboard::$site;
				$settings = $site->get_whitelabel_settings();

				return ! $settings['enabled'];
			}
		}

		return false;
	}

	/**
	 * Builds an array of audit data to be sent to the hub.
	 *
	 * @return array An array containing the number of audit log entries, the timestamp of the
	 *               last audit log entry, and a boolean indicating if audit logging is enabled.
	 */
	public function build_audit_hub_data(): array {
		$date_from   = ( new \DateTime( wp_date( 'Y-m-d', strtotime( '-30 days' ) ) ) )->setTime(
			0,
			0,
			0
		)->getTimestamp();
		$date_to     = ( new \DateTime( wp_date( 'Y-m-d' ) ) )->setTime( 23, 59, 59 )->getTimestamp();
		$month_count = \WP_Defender\Model\Audit_Log::count( $date_from, $date_to );
		$last        = \WP_Defender\Model\Audit_Log::get_last();
		if ( is_object( $last ) ) {
			$last = wp_date( 'Y-m-d g:i a', $last->timestamp );
		} else {
			$last = 'n/a';
		}

		$settings = new \WP_Defender\Model\Setting\Audit_Logging();

		return array(
			'month'      => $month_count,
			'last_event' => $last,
			'enabled'    => $settings->is_active(),
		);
	}
}
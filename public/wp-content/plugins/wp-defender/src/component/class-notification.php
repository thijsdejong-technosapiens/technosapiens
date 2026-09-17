<?php
/**
 * Handles notifications for various WP Defender components such as Firewall, Malware, and Audit Logging.
 *
 * @package WP_Defender\Component
 */

namespace WP_Defender\Component;

use WP_Defender\Behavior\WPMUDEV;
use WP_Defender\Model\Setting\Audit_Logging;
use WP_Defender\Model\Setting\Scan as Scan_Settings;
use WP_Defender\Model\Notification\Audit_Report;
use WP_Defender\Model\Notification\Malware_Report;
use WP_Defender\Model\Notification\Tweak_Reminder;
use WP_Defender\Model\Notification\Firewall_Report;
use WP_Defender\Model\Notification\Malware_Notification;
use WP_Defender\Model\Notification\Firewall_Notification;

/**
 * Handles notifications for various WP Defender components such as Firewall, Malware, and Audit Logging.
 * Pro implementation of the Notification component.
 */
class Notification extends Notification_Base {

	/**
	 * Indicates if the current installation is a Pro version.
	 *
	 * @var bool
	 */
	private $is_pro;

	/**
	 * Constructs the Notification component and initializes the WPMUDEV behavior.
	 */
	public function __construct() {
		$this->attach_behavior( WPMUDEV::class, WPMUDEV::class );
		$this->is_pro        = ( new WPMUDEV() )->is_pro();
		$this->scan_settings = wd_di()->get( Scan_Settings::class );
	}

	/**
	 * Get all modules as array of arrays.
	 *
	 * @return array
	 */
	public function get_modules(): array {
		$modules = array(
			wd_di()->get( Tweak_Reminder::class )->export(),
			wd_di()->get( Malware_Notification::class )->export(),
			wd_di()->get( Firewall_Notification::class )->export(),
		);
		if ( true === $this->is_pro ) {
			return array_merge( $modules, $this->get_active_pro_reports() );
		}

		return $modules;
	}

	/**
	 * Get all modules as array of objects.
	 *
	 * @return array
	 */
	public function get_modules_as_objects(): array {
		$modules = array(
			wd_di()->get( Tweak_Reminder::class ),
			wd_di()->get( Malware_Notification::class ),
			wd_di()->get( Firewall_Notification::class ),
		);
		if ( true === $this->is_pro ) {
			$modules = array_merge(
				$modules,
				array(
					wd_di()->get( Malware_Report::class ),
					wd_di()->get( Firewall_Report::class ),
					wd_di()->get( Audit_Report::class ),
				)
			);
		}

		return $modules;
	}

	/**
	 * Return the time that next report will be triggered.
	 *
	 * @return string|null
	 */
	public function get_next_run() {
		if ( false === $this->is_pro ) {
			return esc_html__( 'Never', 'wpdef' );
		}
		$modules  = $this->get_active_pro_reports_as_objects();
		$next_run = null;
		foreach ( $modules as $module ) {
			if ( \WP_Defender\Model\Notification::STATUS_ACTIVE !== $module->status ) {
				continue;
			}
			if ( is_null( $next_run ) ) {
				$next_run = $module;
			} elseif ( $module->est_timestamp < $next_run->est_timestamp ) {
				$next_run = $module;
			}
		}
		if ( is_null( $next_run ) ) {
			return esc_html__( 'Never', 'wpdef' );
		}

		return $next_run->get_next_run_as_string();
	}

	/**
	 * Get inactive modules.
	 *
	 * @return array
	 * @since 2.7.0 Malware Scanning - Reporting may be inactive.
	 */
	public function get_inactive_modules(): array {
		if ( false === $this->is_pro ) {
			return array();
		}
		$modules = array();
		if ( ! $this->scan_settings->is_enabled_scheduled_scanning() ) {
			$module         = wd_di()->get( Malware_Report::class )->export();
			$module['link'] = network_admin_url( 'admin.php?page=wdf-scan&view=settings&enable=scheduled_scanning#setting_scheduled_scanning' );
			$modules[]      = $module;
		}
		if ( false === wd_di()->get( Audit_Logging::class )->is_active() ) {
			$module         = wd_di()->get( Audit_Report::class )->export();
			$module['link'] = network_admin_url( 'admin.php?page=wdf-logging&view=logs' );
			$modules[]      = $module;
		}

		return $modules;
	}

	/**
	 * Get active modules of Pro reports as array of arrays.
	 *
	 * @return array
	 */
	public function get_active_pro_reports(): array {
		$modules = array();
		// Malware_Report.
		if ( $this->scan_settings->is_enabled_scheduled_scanning() ) {
			$modules[] = wd_di()->get( Malware_Report::class )->export();
		}
		// Firewall_Report.
		$modules[] = wd_di()->get( Firewall_Report::class )->export();
		// Audit_Report.
		if ( true === wd_di()->get( Audit_Logging::class )->is_active() ) {
			$modules[] = wd_di()->get( Audit_Report::class )->export();
		}

		return $modules;
	}

	/**
	 * Get active modules of Pro reports as array of objects.
	 *
	 * @return array
	 */
	public function get_active_pro_reports_as_objects(): array {
		$modules = array();
		// Malware_Report.
		if ( $this->scan_settings->is_enabled_scheduled_scanning() ) {
			$modules[] = wd_di()->get( Malware_Report::class );
		}
		// Firewall_Report.
		$modules[] = wd_di()->get( Firewall_Report::class );
		// Audit_Report.
		if ( true === wd_di()->get( Audit_Logging::class )->is_active() ) {
			$modules[] = wd_di()->get( Audit_Report::class );
		}

		return $modules;
	}

	/**
	 * Dispatches reports if conditions are met.
	 */
	public function maybe_dispatch_report() {
		$modules = array( wd_di()->get( Tweak_Reminder::class ) );
		if ( true === $this->is_pro ) {
			$modules = array_merge( $modules, $this->get_active_pro_reports_as_objects() );
		}

		foreach ( $modules as $module ) {
			if ( $module->maybe_send() ) {
				$module->send();
			}
		}
	}
}
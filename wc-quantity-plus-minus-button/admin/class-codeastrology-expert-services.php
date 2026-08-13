<?php
/**
 * Shared CodeAstrology expert-services promotion component.
 *
 * Other CodeAstrology plugins can bundle this file and register their own
 * context through Expert_Services::register(). The class and dashboard widget
 * IDs are shared deliberately so the UI is only registered once.
 *
 * @package CodeAstrology\ExpertServices
 */

namespace CodeAstrology\Shared;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( __NAMESPACE__ . '\\Expert_Services' ) ) {
	final class Expert_Services {

		const VERSION = '1.0.2';

		/** @var array<string,array<string,mixed>> */
		private static $contexts = array();

		/** @var bool */
		private static $booted = false;

		/**
		 * Register one plugin's service context.
		 *
		 * @param array<string,mixed> $context Context configuration.
		 * @return void
		 */
		public static function register( $context ) {
			$defaults = array(
				'key'            => '',
				'plugin_name'    => '',
				'plugin_version' => '',
				'parent_slug'    => '',
				'menu_slug'      => '',
				'admin_page_path' => '',
				'capability'     => 'manage_options',
				'asset_url'      => '',
				'headline'       => '',
				'description'    => '',
				'settings_description' => 'Our experts build custom WordPress and WooCommerce solutions tailored to your business.',
				'services'       => array(),
				'contact_email'  => 'contact@codeastrology.com',
				'gmail_email'    => 'codersaiful@gmail.com',
				'contact_url'    => 'https://codeastrology.com/contact-us/',
				'company_url'    => 'https://codeastrology.com/',
			);

			$context = wp_parse_args( $context, $defaults );
			$key     = sanitize_key( $context['key'] );

			if ( ! $key || ! $context['parent_slug'] || ! $context['menu_slug'] ) {
				return;
			}

			$context['key'] = $key;
			self::$contexts[ $key ] = $context;

			if ( self::$booted ) {
				return;
			}

			self::$booted = true;
			add_action( 'admin_menu', array( __CLASS__, 'register_submenus' ), 90 );
			add_action( 'wp_dashboard_setup', array( __CLASS__, 'register_dashboard_widget' ) );
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		}

		/**
		 * Register a contextual submenu for every participating plugin.
		 *
		 * @return void
		 */
		public static function register_submenus() {
			foreach ( self::$contexts as $context ) {
				add_submenu_page(
					$context['parent_slug'],
					__( 'Hire an Expert', 'woo-min-max-quantity-step-control-single' ),
					__( 'Hire an Expert', 'woo-min-max-quantity-step-control-single' ),
					$context['capability'],
					$context['menu_slug'],
					array( __CLASS__, 'render_hire_page' )
				);
			}
		}

		/**
		 * Add a single shared WordPress dashboard widget.
		 *
		 * @return void
		 */
		public static function register_dashboard_widget() {
			$context = self::get_visible_context();

			if ( ! $context ) {
				return;
			}

			wp_add_dashboard_widget(
				'codeastrology_expert_services_widget',
				__( 'WordPress & WooCommerce Expert Help', 'woo-min-max-quantity-step-control-single' ),
				array( __CLASS__, 'render_dashboard_widget' )
			);
		}

		/**
		 * Load the shared styles only on relevant admin screens.
		 *
		 * @param string $hook_suffix Current admin hook suffix.
		 * @return void
		 */
		public static function enqueue_assets( $hook_suffix ) {
			$context = self::get_visible_context();

			if ( ! $context || empty( $context['asset_url'] ) ) {
				return;
			}

			$relevant = 'index.php' === $hook_suffix;
			foreach ( self::$contexts as $registered_context ) {
				if ( false !== strpos( $hook_suffix, $registered_context['parent_slug'] ) || false !== strpos( $hook_suffix, $registered_context['menu_slug'] ) ) {
					$relevant = true;
					break;
				}
			}

			if ( ! $relevant ) {
				return;
			}

			wp_enqueue_style(
				'codeastrology-expert-services',
				$context['asset_url'],
				array(),
				self::VERSION
			);
		}

		/**
		 * Render the contextual Hire an Expert page.
		 *
		 * @return void
		 */
		public static function render_hire_page() {
			$context = self::get_context_from_request();

			if ( ! $context || ! current_user_can( $context['capability'] ) ) {
				wp_die( esc_html__( 'You do not have permission to access this page.', 'woo-min-max-quantity-step-control-single' ) );
			}

			$links = self::get_contact_links( $context, 'hire-page' );
			?>
			<div class="wrap caes-page">
				<section class="caes-hero">
					<div class="caes-eyebrow"><?php esc_html_e( 'CODEASTROLOGY EXPERT SERVICES', 'woo-min-max-quantity-step-control-single' ); ?></div>
					<h1><?php echo esc_html( $context['headline'] ); ?></h1>
					<p class="caes-lead"><?php echo esc_html( $context['description'] ); ?></p>
					<div class="caes-actions">
						<a class="button button-primary button-hero" href="<?php echo esc_url( $links['contact'] ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Discuss Your Project', 'woo-min-max-quantity-step-control-single' ); ?></a>
						<a class="button button-hero" href="<?php echo esc_url( $links['gmail'] ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Gmail Us', 'woo-min-max-quantity-step-control-single' ); ?></a>
						<a class="button button-hero" href="<?php echo esc_url( $links['email'] ); ?>"><?php esc_html_e( 'Email Us', 'woo-min-max-quantity-step-control-single' ); ?></a>
					</div>
				</section>

				<section class="caes-section">
					<div class="caes-section-heading">
						<span><?php esc_html_e( 'WHAT WE CAN BUILD', 'woo-min-max-quantity-step-control-single' ); ?></span>
						<h2><?php esc_html_e( 'Practical help for your entire WooCommerce store', 'woo-min-max-quantity-step-control-single' ); ?></h2>
					</div>
					<div class="caes-service-grid">
						<?php foreach ( $context['services'] as $service ) : ?>
							<div class="caes-service-item">
								<span class="dashicons dashicons-yes-alt" aria-hidden="true"></span>
								<span><?php echo esc_html( $service ); ?></span>
							</div>
						<?php endforeach; ?>
					</div>
				</section>

				<section class="caes-section caes-process-section">
					<div class="caes-section-heading">
						<span><?php esc_html_e( 'A SIMPLE PROCESS', 'woo-min-max-quantity-step-control-single' ); ?></span>
						<h2><?php esc_html_e( 'From requirement to reliable delivery', 'woo-min-max-quantity-step-control-single' ); ?></h2>
					</div>
					<div class="caes-process-grid">
						<div><strong>01</strong><h3><?php esc_html_e( 'Share your requirement', 'woo-min-max-quantity-step-control-single' ); ?></h3><p><?php esc_html_e( 'Tell us what you need, what is not working, or what you want to improve.', 'woo-min-max-quantity-step-control-single' ); ?></p></div>
						<div><strong>02</strong><h3><?php esc_html_e( 'Review and estimate', 'woo-min-max-quantity-step-control-single' ); ?></h3><p><?php esc_html_e( 'We review the scope and provide a clear plan, timeline, and estimate.', 'woo-min-max-quantity-step-control-single' ); ?></p></div>
						<div><strong>03</strong><h3><?php esc_html_e( 'Build and deliver', 'woo-min-max-quantity-step-control-single' ); ?></h3><p><?php esc_html_e( 'We develop, test, and deliver a solution that fits your store.', 'woo-min-max-quantity-step-control-single' ); ?></p></div>
					</div>
				</section>

				<section class="caes-final-cta">
					<div><span><?php esc_html_e( 'HAVE A PROJECT IN MIND?', 'woo-min-max-quantity-step-control-single' ); ?></span><h2><?php esc_html_e( 'Tell us what you need - we will propose a quality solution that fits your budget.', 'woo-min-max-quantity-step-control-single' ); ?></h2></div>
					<a class="button button-primary button-hero" href="<?php echo esc_url( $links['contact'] ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Get an Affordable Quote', 'woo-min-max-quantity-step-control-single' ); ?></a>
				</section>

				<section class="caes-payment-section" aria-labelledby="caes-payment-title">
					<div class="caes-payment-icon" aria-hidden="true"><span class="dashicons dashicons-lock"></span></div>
					<div class="caes-payment-copy">
						<span><?php esc_html_e( 'FOR APPROVED PROJECTS', 'woo-min-max-quantity-step-control-single' ); ?></span>
						<h2 id="caes-payment-title"><?php esc_html_e( 'Make a Secure Payment - USD', 'woo-min-max-quantity-step-control-single' ); ?></h2>
						<p><?php esc_html_e( 'Use this payment option only after your project scope and quotation have been confirmed with our team. Enter the agreed amount and include your project title or reference on the payment form.', 'woo-min-max-quantity-step-control-single' ); ?></p>
						<small><?php esc_html_e( 'Secure payment powered by Stripe.', 'woo-min-max-quantity-step-control-single' ); ?></small>
					</div>
					<a class="button button-primary button-hero caes-payment-button" href="https://buy.stripe.com/28E5kCdPBaEx1js6Dm48004" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Make a Secure Payment', 'woo-min-max-quantity-step-control-single' ); ?></a>
				</section>
			</div>
			<?php
		}

		/**
		 * Render the compact card used at the end of a plugin settings page.
		 *
		 * @param string $key Registered context key.
		 * @return void
		 */
		public static function render_settings_card( $key ) {
			$context = isset( self::$contexts[ $key ] ) ? self::$contexts[ $key ] : null;

			if ( ! $context || ! current_user_can( $context['capability'] ) ) {
				return;
			}

			$links = self::get_contact_links( $context, 'settings-card' );
			?>
			<section class="caes-settings-card" aria-labelledby="caes-settings-title">
				<div class="caes-settings-copy">
					<span class="caes-eyebrow"><?php esc_html_e( 'NEED SOMETHING MORE CUSTOM?', 'woo-min-max-quantity-step-control-single' ); ?></span>
					<h2 id="caes-settings-title"><?php echo esc_html( $context['headline'] ); ?></h2>
					<p><?php echo esc_html( $context['settings_description'] ); ?></p>
				</div>
				<div class="caes-actions">
					<a class="button button-primary" href="<?php echo esc_url( admin_url( $context['admin_page_path'] ? $context['admin_page_path'] : 'admin.php?page=' . $context['menu_slug'] ) ); ?>"><?php esc_html_e( 'Explore Expert Help', 'woo-min-max-quantity-step-control-single' ); ?></a>
					<a class="button" href="<?php echo esc_url( $links['email'] ); ?>"><?php esc_html_e( 'Email Us', 'woo-min-max-quantity-step-control-single' ); ?></a>
				</div>
			</section>
			<?php
		}

		/**
		 * Render the single shared dashboard box.
		 *
		 * @return void
		 */
		public static function render_dashboard_widget() {
			$context = self::get_visible_context();

			if ( ! $context ) {
				return;
			}

			$links = self::get_contact_links( $context, 'dashboard-widget' );
			?>
			<div class="caes-dashboard-widget">
				<div class="caes-widget-badge"><?php esc_html_e( 'CodeAstrology Team', 'woo-min-max-quantity-step-control-single' ); ?></div>
				<h3><?php esc_html_e( 'Need help with WordPress or WooCommerce?', 'woo-min-max-quantity-step-control-single' ); ?></h3>
				<p><?php esc_html_e( 'We can help with plugin customization, bug fixing, store setup, custom workflows, integrations, and complete WooCommerce development.', 'woo-min-max-quantity-step-control-single' ); ?></p>
				<div class="caes-actions">
					<a class="button button-primary" href="<?php echo esc_url( $links['contact'] ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Hire an Expert', 'woo-min-max-quantity-step-control-single' ); ?></a>
					<a class="button" href="<?php echo esc_url( $links['gmail'] ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Gmail Us', 'woo-min-max-quantity-step-control-single' ); ?></a>
					<a class="button" href="<?php echo esc_url( $links['email'] ); ?>"><?php esc_html_e( 'Email Us', 'woo-min-max-quantity-step-control-single' ); ?></a>
				</div>
			</div>
			<?php
		}

		/**
		 * Find the context matching the current submenu page.
		 *
		 * @return array<string,mixed>|null
		 */
		private static function get_context_from_request() {
			$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';

			foreach ( self::$contexts as $context ) {
				if ( $page === $context['menu_slug'] ) {
					return $context;
				}
			}

			return null;
		}

		/**
		 * Return the first context the current user is allowed to see.
		 *
		 * @return array<string,mixed>|null
		 */
		private static function get_visible_context() {
			foreach ( self::$contexts as $context ) {
				if ( current_user_can( $context['capability'] ) ) {
					return $context;
				}
			}

			return null;
		}

		/**
		 * Build contextual contact, Gmail, and mailto links.
		 *
		 * @param array<string,mixed> $context   Registered context.
		 * @param string              $placement CTA placement within wp-admin.
		 * @return array<string,string>
		 */
		private static function get_contact_links( $context, $placement = 'plugin-admin' ) {
			$subject = sprintf( 'Custom WooCommerce Development Request - %s', $context['plugin_name'] );
			$body    = sprintf(
				"Hello CodeAstrology Team,\n\nI am using %1\$s (version %2\$s) and would like help with custom WordPress or WooCommerce development.\n\nWebsite URL:\nProject requirement:\nExpected timeline:\nAdditional details:\n",
				$context['plugin_name'],
				$context['plugin_version']
			);

			$contact = add_query_arg(
				array(
					'service_context' => $context['key'],
					'utm_source'      => $context['key'],
					'utm_medium'      => 'plugin-admin',
					'utm_campaign'    => 'hire-an-expert',
					'utm_content'     => sanitize_key( $placement ),
				),
				$context['contact_url']
			);

			$gmail = add_query_arg(
				array(
					'view' => 'cm',
					'fs'   => '1',
					'to'   => sanitize_email( $context['gmail_email'] ),
					'su'   => $subject,
					'body' => $body,
				),
				'https://mail.google.com/mail/'
			);

			$email = 'mailto:' . sanitize_email( $context['contact_email'] ) . '?' . http_build_query(
				array(
					'subject' => $subject,
					'body'    => $body,
				),
				'',
				'&',
				PHP_QUERY_RFC3986
			);

			return array(
				'contact' => $contact,
				'gmail'   => $gmail,
				'email'   => $email,
			);
		}
	}
}

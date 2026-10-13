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

		const VERSION = '1.1.1';

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
				'menu_title'     => __( 'Hire an Expert', 'wc-quantity-plus-minus-button' ),
				'admin_page_path' => '',
				'capability'     => 'manage_options',
				'asset_url'      => '',
				'asset_pages'    => array(),
				'headline'       => '',
				'description'    => '',
				'settings_description' => __( 'Our experts build custom WordPress and WooCommerce solutions tailored to your business.', 'wc-quantity-plus-minus-button' ),
				'services'       => array(),
				'page_mode'      => 'hire',
				'review_items'   => array(),
				'audit_focus_icon' => 'dashicons-admin-generic',
				'audit_focus_title' => '',
				'audit_focus_description' => '',
				'settings_eyebrow' => __( 'NEED SOMETHING MORE CUSTOM?', 'wc-quantity-plus-minus-button' ),
				'settings_button_text' => __( 'Explore Expert Help', 'wc-quantity-plus-minus-button' ),
				'contact_email'  => 'contact@codeastrology.com',
				'gmail_email'    => 'codersaiful@gmail.com',
				'contact_url'    => 'https://forms.gle/EQXNvD6LBh8HqQjy9',
				'company_url'    => 'https://codeastrology.com/',
			);

			$context = wp_parse_args( $context, $defaults );
			$context['asset_pages'] = array_filter( array_map( 'sanitize_key', (array) $context['asset_pages'] ) );
			if ( in_array( $context['page_mode'], array( 'audit', 'review' ), true ) && empty( $context['review_items'] ) ) {
				$context['review_items'] = self::get_default_audit_items( $context );
			}
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
					$context['menu_title'],
					$context['menu_title'],
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
				__( 'Free WordPress & WooCommerce Store Audit', 'wc-quantity-plus-minus-button' ),
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
			$context = null;
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only screen check, nothing is written.
			$page    = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';

			if ( 'index.php' === $hook_suffix ) {
				$context = self::get_visible_context();
			} else {
				foreach ( self::$contexts as $registered_context ) {
					if ( ! current_user_can( $registered_context['capability'] ) || empty( $registered_context['asset_url'] ) ) {
						continue;
					}

					$asset_pages = array_merge( array( $registered_context['menu_slug'] ), $registered_context['asset_pages'] );
					if (
						( $page && in_array( $page, $asset_pages, true ) ) ||
						false !== strpos( $hook_suffix, $registered_context['menu_slug'] )
					) {
						$context = $registered_context;
						break;
					}
				}
			}

			if ( ! $context || empty( $context['asset_url'] ) ) {
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
				wp_die( esc_html__( 'You do not have permission to access this page.', 'wc-quantity-plus-minus-button' ) );
			}

			if ( in_array( $context['page_mode'], array( 'audit', 'review' ), true ) ) {
				self::render_review_page( $context );
				return;
			}

			$links = self::get_contact_links( $context, 'hire-page' );
			?>
			<div class="wrap caes-page">
				<section class="caes-hero">
					<div class="caes-eyebrow"><?php esc_html_e( 'CODEASTROLOGY EXPERT SERVICES', 'wc-quantity-plus-minus-button' ); ?></div>
					<h1><?php echo esc_html( $context['headline'] ); ?></h1>
					<p class="caes-lead"><?php echo esc_html( $context['description'] ); ?></p>
					<div class="caes-actions">
						<a class="button button-primary button-hero" href="<?php echo esc_url( $links['contact'] ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Discuss Your Project', 'wc-quantity-plus-minus-button' ); ?></a>
						<a class="button button-hero" href="<?php echo esc_url( $links['gmail'] ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Gmail Us', 'wc-quantity-plus-minus-button' ); ?></a>
						<a class="button button-hero" href="<?php echo esc_url( $links['email'] ); ?>"><?php esc_html_e( 'Email Us', 'wc-quantity-plus-minus-button' ); ?></a>
					</div>
				</section>

				<section class="caes-section">
					<div class="caes-section-heading">
						<span><?php esc_html_e( 'WHAT WE CAN BUILD', 'wc-quantity-plus-minus-button' ); ?></span>
						<h2><?php esc_html_e( 'Practical help for your entire WooCommerce store', 'wc-quantity-plus-minus-button' ); ?></h2>
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
						<span><?php esc_html_e( 'A SIMPLE PROCESS', 'wc-quantity-plus-minus-button' ); ?></span>
						<h2><?php esc_html_e( 'From requirement to reliable delivery', 'wc-quantity-plus-minus-button' ); ?></h2>
					</div>
					<div class="caes-process-grid">
						<div><strong>01</strong><h3><?php esc_html_e( 'Share your requirement', 'wc-quantity-plus-minus-button' ); ?></h3><p><?php esc_html_e( 'Tell us what you need, what is not working, or what you want to improve.', 'wc-quantity-plus-minus-button' ); ?></p></div>
						<div><strong>02</strong><h3><?php esc_html_e( 'Review and estimate', 'wc-quantity-plus-minus-button' ); ?></h3><p><?php esc_html_e( 'We review the scope and provide a clear plan, timeline, and estimate.', 'wc-quantity-plus-minus-button' ); ?></p></div>
						<div><strong>03</strong><h3><?php esc_html_e( 'Build and deliver', 'wc-quantity-plus-minus-button' ); ?></h3><p><?php esc_html_e( 'We develop, test, and deliver a solution that fits your store.', 'wc-quantity-plus-minus-button' ); ?></p></div>
					</div>
				</section>

				<section class="caes-final-cta">
					<div><span><?php esc_html_e( 'HAVE A PROJECT IN MIND?', 'wc-quantity-plus-minus-button' ); ?></span><h2><?php esc_html_e( 'Tell us what you need - we will propose a quality solution that fits your budget.', 'wc-quantity-plus-minus-button' ); ?></h2></div>
					<a class="button button-primary button-hero" href="<?php echo esc_url( $links['contact'] ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Get an Affordable Quote', 'wc-quantity-plus-minus-button' ); ?></a>
				</section>

				<section class="caes-payment-section" aria-labelledby="caes-payment-title">
					<div class="caes-payment-icon" aria-hidden="true"><span class="dashicons dashicons-lock"></span></div>
					<div class="caes-payment-copy">
						<span><?php esc_html_e( 'FOR APPROVED PROJECTS', 'wc-quantity-plus-minus-button' ); ?></span>
						<h2 id="caes-payment-title"><?php esc_html_e( 'Make a Secure Payment - USD', 'wc-quantity-plus-minus-button' ); ?></h2>
						<p><?php esc_html_e( 'Use this payment option only after your project scope and quotation have been confirmed with our team. Enter the agreed amount and include your project title or reference on the payment form.', 'wc-quantity-plus-minus-button' ); ?></p>
						<small><?php esc_html_e( 'Secure payment powered by Stripe.', 'wc-quantity-plus-minus-button' ); ?></small>
					</div>
					<a class="button button-primary button-hero caes-payment-button" href="https://buy.stripe.com/28E5kCdPBaEx1js6Dm48004" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Make a Secure Payment', 'wc-quantity-plus-minus-button' ); ?></a>
				</section>
			</div>
			<?php
		}

		/**
		 * Render the free store-review experience.
		 *
		 * @param array<string,mixed> $context Current plugin context.
		 * @return void
		 */
		private static function render_review_page( $context ) {
			$links = self::get_contact_links( $context, 'free-store-review-page' );
			?>
			<div class="wrap caes-page caes-review-page">
				<h1 class="caes-notice-anchor" aria-hidden="true"></h1>

				<section class="caes-hero caes-review-hero">
					<div class="caes-eyebrow"><?php esc_html_e( 'FREE WORDPRESS & WOOCOMMERCE AUDIT', 'wc-quantity-plus-minus-button' ); ?></div>
					<h1><?php echo esc_html( $context['headline'] ); ?></h1>
					<p class="caes-lead"><?php echo esc_html( $context['description'] ); ?></p>
					<div class="caes-review-trust" aria-label="<?php esc_attr_e( 'Review highlights', 'wc-quantity-plus-minus-button' ); ?>">
						<span><span class="dashicons dashicons-yes" aria-hidden="true"></span><?php esc_html_e( 'Free initial audit', 'wc-quantity-plus-minus-button' ); ?></span>
						<span><span class="dashicons dashicons-yes" aria-hidden="true"></span><?php esc_html_e( 'No obligation to hire', 'wc-quantity-plus-minus-button' ); ?></span>
						<span><span class="dashicons dashicons-yes" aria-hidden="true"></span><?php esc_html_e( 'Clear, practical next steps', 'wc-quantity-plus-minus-button' ); ?></span>
					</div>
					<div class="caes-actions">
						<a class="button button-primary button-hero" href="<?php echo esc_url( $links['contact'] ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Request My Free Audit', 'wc-quantity-plus-minus-button' ); ?></a>
						<a class="button button-hero" href="<?php echo esc_url( $links['gmail'] ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Send Details with Gmail', 'wc-quantity-plus-minus-button' ); ?></a>
						<a class="caes-text-link" href="<?php echo esc_url( $links['email'] ); ?>"><?php esc_html_e( 'or use your email app', 'wc-quantity-plus-minus-button' ); ?></a>
					</div>
				</section>

				<section class="caes-section">
					<div class="caes-section-heading caes-review-heading">
						<span><?php esc_html_e( 'WHAT WE REVIEW', 'wc-quantity-plus-minus-button' ); ?></span>
						<h2><?php esc_html_e( 'A practical look at the parts of your store that matter', 'wc-quantity-plus-minus-button' ); ?></h2>
						<p><?php esc_html_e( 'This is not a generic automated score. We look for visible problems, friction, and realistic opportunities across your WordPress and WooCommerce site.', 'wc-quantity-plus-minus-button' ); ?></p>
					</div>
					<div class="caes-review-grid">
						<?php foreach ( $context['review_items'] as $item ) : ?>
							<article class="caes-review-item">
								<span class="dashicons <?php echo esc_attr( $item['icon'] ); ?>" aria-hidden="true"></span>
								<div><h3><?php echo esc_html( $item['title'] ); ?></h3><p><?php echo esc_html( $item['description'] ); ?></p></div>
							</article>
						<?php endforeach; ?>
					</div>
				</section>

				<section class="caes-section caes-process-section">
					<div class="caes-section-heading">
						<span><?php esc_html_e( 'HOW IT WORKS', 'wc-quantity-plus-minus-button' ); ?></span>
						<h2><?php esc_html_e( 'Useful findings without a complicated process', 'wc-quantity-plus-minus-button' ); ?></h2>
					</div>
					<div class="caes-process-grid">
						<div><strong>01</strong><h3><?php esc_html_e( 'Share your website', 'wc-quantity-plus-minus-button' ); ?></h3><p><?php esc_html_e( 'Send your store URL, the issue you care about most, and any helpful screenshots. Do not send passwords.', 'wc-quantity-plus-minus-button' ); ?></p></div>
						<div><strong>02</strong><h3><?php esc_html_e( 'We audit the essentials', 'wc-quantity-plus-minus-button' ); ?></h3><p><?php esc_html_e( 'We perform a focused, non-invasive audit of the selected store areas and the experience related to this plugin.', 'wc-quantity-plus-minus-button' ); ?></p></div>
						<div><strong>03</strong><h3><?php esc_html_e( 'Get prioritized next steps', 'wc-quantity-plus-minus-button' ); ?></h3><p><?php esc_html_e( 'You receive clear findings and practical recommendations, with an optional quotation only if you want our help.', 'wc-quantity-plus-minus-button' ); ?></p></div>
					</div>
					<div class="caes-scope-note"><span class="dashicons dashicons-info-outline" aria-hidden="true"></span><p><?php esc_html_e( 'The free audit is an initial, non-invasive assessment—not a penetration test, formal security certification, or guarantee of SEO rankings.', 'wc-quantity-plus-minus-button' ); ?></p></div>
				</section>

				<section class="caes-final-cta caes-review-cta">
					<div><span><?php esc_html_e( 'READY WHEN YOU ARE', 'wc-quantity-plus-minus-button' ); ?></span><h2><?php esc_html_e( 'Send your store URL and tell us what concerns you most.', 'wc-quantity-plus-minus-button' ); ?></h2><p><?php esc_html_e( 'Start with the public website—no WordPress password is required for the initial review.', 'wc-quantity-plus-minus-button' ); ?></p></div>
					<a class="button button-primary button-hero" href="<?php echo esc_url( $links['contact'] ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Request My Free Audit', 'wc-quantity-plus-minus-button' ); ?></a>
				</section>

				<section class="caes-payment-section" aria-labelledby="caes-payment-title">
					<div class="caes-payment-icon" aria-hidden="true"><span class="dashicons dashicons-lock"></span></div>
					<div class="caes-payment-copy">
						<span><?php esc_html_e( 'FOR EXISTING CLIENTS ONLY', 'wc-quantity-plus-minus-button' ); ?></span>
						<h2 id="caes-payment-title"><?php esc_html_e( 'Make a Secure Payment - USD', 'wc-quantity-plus-minus-button' ); ?></h2>
						<p><?php esc_html_e( 'Use this option only after your project scope and quotation have been confirmed with our team.', 'wc-quantity-plus-minus-button' ); ?></p>
						<small><?php esc_html_e( 'Secure payment powered by Stripe.', 'wc-quantity-plus-minus-button' ); ?></small>
					</div>
					<a class="button button-primary button-hero caes-payment-button" href="https://buy.stripe.com/28E5kCdPBaEx1js6Dm48004" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Make a Secure Payment', 'wc-quantity-plus-minus-button' ); ?></a>
				</section>
			</div>
			<?php
		}

		/**
		 * Build the common store-audit areas plus one plugin-specific area.
		 *
		 * @param array<string,mixed> $context Current plugin context.
		 * @return array<int,array<string,string>>
		 */
		private static function get_default_audit_items( $context ) {
			$focus_title = $context['audit_focus_title']
				? $context['audit_focus_title']
				: sprintf( '%s & Store Experience', $context['plugin_name'] );
			$focus_description = $context['audit_focus_description']
				? $context['audit_focus_description']
				: 'How the plugin experience fits into the customer journey and where usability can be improved.';

			return array(
				array(
					'icon'        => 'dashicons-performance',
					'title'       => 'Speed',
					'description' => 'What is actually slowing your pages down—not just another generic performance score.',
				),
				array(
					'icon'        => 'dashicons-admin-plugins',
					'title'       => 'Plugins',
					'description' => 'Outdated or abandoned plugins, unnecessary overlap, and visible signs of known vulnerability risks.',
				),
				array(
					'icon'        => 'dashicons-search',
					'title'       => 'SEO & Indexability',
					'description' => 'Whether search engines can index your important pages and whether basic technical signals are working correctly.',
				),
				array(
					'icon'        => 'dashicons-cart',
					'title'       => 'Checkout',
					'description' => 'Friction, confusing steps, or visible problems that may cause customers to leave before completing payment.',
				),
				array(
					'icon'        => 'dashicons-shield-alt',
					'title'       => 'Basic Security Signals',
					'description' => 'Publicly exposed files, basic permission concerns, login protection, and other visible security warning signs.',
				),
				array(
					'icon'        => $context['audit_focus_icon'],
					'title'       => $focus_title,
					'description' => $focus_description,
				),
			);
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
					<span class="caes-eyebrow"><?php echo esc_html( $context['settings_eyebrow'] ); ?></span>
					<h2 id="caes-settings-title"><?php echo esc_html( $context['headline'] ); ?></h2>
					<p><?php echo esc_html( $context['settings_description'] ); ?></p>
				</div>
				<div class="caes-actions">
					<a class="button button-primary" href="<?php echo esc_url( admin_url( $context['admin_page_path'] ? $context['admin_page_path'] : 'admin.php?page=' . $context['menu_slug'] ) ); ?>"><?php echo esc_html( $context['settings_button_text'] ); ?></a>
					<a class="button" href="<?php echo esc_url( $links['email'] ); ?>"><?php esc_html_e( 'Email Us', 'wc-quantity-plus-minus-button' ); ?></a>
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
			$audit_page_url = admin_url( $context['admin_page_path'] ? $context['admin_page_path'] : 'admin.php?page=' . $context['menu_slug'] );
			?>
			<div class="caes-dashboard-widget">
				<div class="caes-widget-badge"><?php esc_html_e( 'CodeAstrology Team', 'wc-quantity-plus-minus-button' ); ?></div>
				<h3><?php esc_html_e( 'Want to know what may be holding your website back?', 'wc-quantity-plus-minus-button' ); ?></h3>
				<p><?php esc_html_e( 'Get a free initial audit covering speed, plugins, SEO visibility, checkout, basic security signals, and the experience related to your CodeAstrology plugin.', 'wc-quantity-plus-minus-button' ); ?></p>
				<div class="caes-actions">
					<a class="button button-primary" href="<?php echo esc_url( $audit_page_url ); ?>"><?php esc_html_e( 'Explore Free Store Audit', 'wc-quantity-plus-minus-button' ); ?></a>
					<a class="button" href="<?php echo esc_url( $links['gmail'] ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Gmail Us', 'wc-quantity-plus-minus-button' ); ?></a>
					<a class="button" href="<?php echo esc_url( $links['email'] ); ?>"><?php esc_html_e( 'Email Us', 'wc-quantity-plus-minus-button' ); ?></a>
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
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only screen check, nothing is written.
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
			if ( in_array( $context['page_mode'], array( 'audit', 'review' ), true ) ) {
				$subject = sprintf( 'Free WooCommerce Store Audit Request - %s', $context['plugin_name'] );
				$body    = sprintf(
					"Hello CodeAstrology Team,\n\nI am using %1\$s (version %2\$s) and would like a free audit of my WordPress/WooCommerce site.\n\nWebsite URL:\nMain concern:\nAreas to audit (Speed / Plugins / SEO / Checkout / Security / Plugin Experience):\nRelevant screenshots or details:\n\nI understand that I should not send passwords for the initial audit.\n",
					$context['plugin_name'],
					$context['plugin_version']
				);
				$campaign = 'free-store-audit';
			} else {
				$subject = sprintf( 'Custom WooCommerce Development Request - %s', $context['plugin_name'] );
				$body    = sprintf(
					"Hello CodeAstrology Team,\n\nI am using %1\$s (version %2\$s) and would like help with custom WordPress or WooCommerce development.\n\nWebsite URL:\nProject requirement:\nExpected timeline:\nAdditional details:\n",
					$context['plugin_name'],
					$context['plugin_version']
				);
				$campaign = 'hire-an-expert';
			}

			$contact = add_query_arg(
				array(
					'service_context' => $context['key'],
					'utm_source'      => $context['key'],
					'utm_medium'      => 'plugin-admin',
					'utm_campaign'    => $campaign,
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

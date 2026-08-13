<?php
/**
 * Shared, lightweight CodeAstrology sale notice.
 *
 * Each participating plugin can bundle this file and register its own context.
 * The class, hook, and user-meta IDs are shared so only one unobtrusive notice
 * appears and one dismissal hides the campaign everywhere.
 *
 * @package CodeAstrology\SaleNotice
 */

namespace CodeAstrology\Shared;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( __NAMESPACE__ . '\\Sale_Notice' ) ) {
	final class Sale_Notice {

		const DISMISS_ACTION = 'codeastrology_dismiss_super_sale_notice';
		const DISMISS_META   = 'codeastrology_dismissed_super_sale_80_2026';
		const NONCE_ACTION   = 'codeastrology_super_sale_notice';

		/** @var array<string,array<string,mixed>> */
		private static $contexts = array();

		/** @var bool */
		private static $booted = false;

		/**
		 * Register one plugin's sale context.
		 *
		 * @param array<string,mixed> $context Context configuration.
		 * @return void
		 */
		public static function register( $context ) {
			$context = wp_parse_args(
				$context,
				array(
					'key'          => '',
					'plugin_name'  => '',
					'capability'   => 'manage_options',
					'screen_tokens' => array(),
					'purchase_url'  => 'https://codeastrology.com/downloads/category/premium/',
					'all_plugins_url' => 'https://codeastrology.com/downloads/category/premium/',
					'has_premium'  => false,
					'is_premium'   => false,
					'headline'     => __( 'CodeAstrology Super Sale', 'woo-product-table' ),
					'description'  => __( 'Save up to 80% on selected premium WordPress and WooCommerce plugins for a limited time.', 'woo-product-table' ),
					'button_text'  => __( 'View Offer', 'woo-product-table' ),
				)
			);

			$key = sanitize_key( $context['key'] );
			if ( ! $key || empty( $context['screen_tokens'] ) || empty( $context['purchase_url'] ) ) {
				return;
			}

			$context['key'] = $key;
			$context['screen_tokens'] = array_filter( array_map( 'sanitize_key', (array) $context['screen_tokens'] ) );
			self::$contexts[ $key ] = $context;

			if ( self::$booted ) {
				return;
			}

			self::$booted = true;
			add_action( 'admin_notices', array( __CLASS__, 'render' ), 20 );
			add_action( 'wp_ajax_' . self::DISMISS_ACTION, array( __CLASS__, 'dismiss' ) );
		}

		/**
		 * Render one contextual notice.
		 *
		 * @return void
		 */
		public static function render() {
			$context = self::get_visible_context();
			if ( ! $context ) {
				return;
			}

			$purchase_url = add_query_arg(
				array(
					'utm_source'   => $context['plugin_name'] . ' Plugin Dashboard',
					'utm_medium'   => 'Free Version',
					'utm_campaign' => 'Super Sale 80 Notice',
					'utm_content'  => $context['button_text'],
				),
				$context['purchase_url']
			);
			$all_plugins_url = add_query_arg(
				array(
					'utm_source'   => $context['plugin_name'] . ' Plugin Dashboard',
					'utm_medium'   => 'Free Version',
					'utm_campaign' => 'Super Sale 80 Notice',
					'utm_content'  => 'All Plugins',
				),
				$context['all_plugins_url']
			);
			?>
			<div class="notice is-dismissible codeastrology-sale-notice" role="region" aria-label="<?php esc_attr_e( 'CodeAstrology special offer', 'woo-product-table' ); ?>">
				<div class="codeastrology-sale-notice__content">
					<span class="codeastrology-sale-notice__badge"><?php esc_html_e( 'UP TO 80% OFF', 'woo-product-table' ); ?></span>
					<div class="codeastrology-sale-notice__copy">
						<strong><?php echo esc_html( $context['headline'] ); ?></strong>
						<span><?php echo esc_html( $context['description'] ); ?></span>
					</div>
					<div class="codeastrology-sale-notice__actions">
						<a class="button button-primary codeastrology-sale-notice__button" href="<?php echo esc_url( $purchase_url ); ?>" target="_blank" rel="noopener noreferrer">
							<?php echo esc_html( $context['button_text'] ); ?>
						</a>
						<?php if ( ! empty( $context['has_premium'] ) ) : ?>
							<a class="button codeastrology-sale-notice__button" href="<?php echo esc_url( $all_plugins_url ); ?>" target="_blank" rel="noopener noreferrer">
								<?php esc_html_e( 'All Plugins', 'woo-product-table' ); ?>
							</a>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<style id="codeastrology-sale-notice-style">
				.codeastrology-sale-notice {
					border-left-color: #6d28d9;
					padding: 0 38px 0 0;
					box-shadow: 0 2px 8px rgba(30, 41, 59, .06);
				}
				.codeastrology-sale-notice__content {
					display: flex;
					gap: 14px;
					align-items: center;
					min-height: 58px;
					padding: 10px 4px 10px 14px;
				}
				.codeastrology-sale-notice__badge {
					flex: 0 0 auto;
					padding: 5px 9px;
					border-radius: 999px;
					background: #f3e8ff;
					color: #6d28d9;
					font-size: 11px;
					font-weight: 700;
					letter-spacing: .04em;
				}
				.codeastrology-sale-notice__copy {
					display: flex;
					flex: 1 1 auto;
					gap: 4px 10px;
					align-items: baseline;
					min-width: 0;
				}
				.codeastrology-sale-notice__copy strong {
					font-size: 14px;
				}
				.codeastrology-sale-notice__copy span {
					color: #59636e;
				}
				.codeastrology-sale-notice__actions {
					flex: 0 0 auto;
					display: flex;
					gap: 8px;
				}
				@media (max-width: 782px) {
					.codeastrology-sale-notice__content,
					.codeastrology-sale-notice__copy {
						align-items: flex-start;
						flex-direction: column;
					}
					.codeastrology-sale-notice__content {
						padding-top: 14px;
						padding-bottom: 14px;
					}
					.codeastrology-sale-notice__actions {
						flex-wrap: wrap;
					}
				}
			</style>
			<script id="codeastrology-sale-notice-script">
				document.addEventListener('click', function (event) {
					var dismissButton = event.target.closest('.codeastrology-sale-notice .notice-dismiss');
					if (!dismissButton) {
						return;
					}

					var request = new URLSearchParams();
					request.append('action', <?php echo wp_json_encode( self::DISMISS_ACTION ); ?>);
					request.append('nonce', <?php echo wp_json_encode( wp_create_nonce( self::NONCE_ACTION ) ); ?>);

					fetch(<?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>, {
						method: 'POST',
						credentials: 'same-origin',
						headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
						body: request.toString()
					}).catch(function () {});
				});
			</script>
			<?php
		}

		/**
		 * Permanently dismiss this campaign for the current user.
		 *
		 * @return void
		 */
		public static function dismiss() {
			check_ajax_referer( self::NONCE_ACTION, 'nonce' );

			if ( ! current_user_can( 'read' ) ) {
				wp_send_json_error( array( 'message' => __( 'Permission denied.', 'woo-product-table' ) ), 403 );
			}

			update_user_meta( get_current_user_id(), self::DISMISS_META, time() );
			wp_send_json_success();
		}

		/**
		 * Find the best context for the current screen.
		 *
		 * @return array<string,mixed>|null
		 */
		private static function get_visible_context() {
			if ( get_user_meta( get_current_user_id(), self::DISMISS_META, true ) ) {
				return null;
			}

			$screen = get_current_screen();
			if ( ! $screen ) {
				return null;
			}

			if ( 'plugins' === $screen->id ) {
				foreach ( self::$contexts as $context ) {
					if ( self::is_context_available( $context ) ) {
						return $context;
					}
				}
				return null;
			}

			$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
			foreach ( self::$contexts as $context ) {
				if ( ! self::is_context_available( $context ) ) {
					continue;
				}

				foreach ( $context['screen_tokens'] as $token ) {
					if ( false !== strpos( (string) $screen->id, $token ) || ( $page && false !== strpos( $page, $token ) ) ) {
						return $context;
					}
				}
			}

			return null;
		}

		/**
		 * Check capability and premium state for one context.
		 *
		 * @param array<string,mixed> $context Context configuration.
		 * @return bool
		 */
		private static function is_context_available( $context ) {
			if ( ! current_user_can( $context['capability'] ) ) {
				return false;
			}

			$is_premium = is_callable( $context['is_premium'] )
				? (bool) call_user_func( $context['is_premium'] )
				: (bool) $context['is_premium'];

			return ! $is_premium;
		}
	}
}

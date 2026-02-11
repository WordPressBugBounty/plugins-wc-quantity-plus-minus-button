<?php
namespace WQPMB\Admin;
/**
 * Freemius Pricing Table Customizer
 * 
 * Handles custom Freemius pricing UI using hooks.
 * Usage:
 *      include_once dirname(__FILE__) . '/class-wqpmb-freemius-customizer.php';
 *      new WQPMB_Freemius_Customizer();
 */

if ( ! class_exists( 'Freemius_Customizer' ) ) {

    class Freemius_Customizer {

        /**
         * Plugin slug
         * @var string
         */
        private $slug = 'wc-quantity-plus-minus-button';

        public function __construct() {
            
            // Hooks
            add_action( 'fs_before_pricing', array( $this, 'before_pricing' ) );
            add_action( 'fs_after_pricing', array( $this, 'after_pricing' ) );
            add_action( 'fs_custom_pricing_css', array( $this, 'custom_pricing_css' ) );

            // Disable default Freemius pricing box UI
            add_filter( 'fs_show_pricing', array( $this, 'maybe_hide_default_pricing' ), 10, 1 );
        }

        /**
         * Hide default Freemius pricing UI for this plugin only.
         */
        public function maybe_hide_default_pricing( $show ) {
            $fs = wqpmb_fs();
            if ( $fs->get_slug() === $this->slug ) {
                return false;
            }
            return $show;
        }

        /**
         * Display before pricing section (header, intro, etc)
         */
        public function before_pricing( $fs ) {
            if ( $fs->get_slug() !== $this->slug ) return;

            echo '<div class="wqpmb-pricing-wrapper">';
            echo '<h2 class="wqpmb-pricing-title">✨ Choose Your Plan</h2>';
            echo '<p class="wqpmb-pricing-subtitle">Upgrade to unlock all premium features and support!</p>';
        }

        /**
         * Custom pricing table after Freemius default UI (we’re replacing it)
         */
        public function after_pricing( $fs ) {
            if ( $fs->get_slug() !== $this->slug ) return;

            $plans = $fs->get_plans();
            if ( empty( $plans ) ) {
                echo '<p>No plans found.</p></div>';
                return;
            }

            echo '<div class="wqpmb-pricing-table">';

            foreach ( $plans as $plan ) {
                // Fetch plan price (first billing cycle)
                $price = $plan->pricing ? $plan->pricing : 'N/A';
                $title = esc_html( $plan->title );
                $desc  = esc_html( $plan->description ?? '' );
                $id    = intval( $plan->id );

                echo '<div class="wqpmb-plan-card">';
                    echo '<h3 class="wqpmb-plan-title">' . $title . '</h3>';
                    if ( $desc ) {
                        echo '<p class="wqpmb-plan-desc">' . $desc . '</p>';
                    }
                    echo '<div class="wqpmb-plan-price">$' . esc_html( $price ) . '/year</div>';
                    echo '<ul class="wqpmb-plan-features">';
                        echo '<li>✔ Premium Support</li>';
                        echo '<li>✔ Automatic Updates</li>';
                        echo '<li>✔ All Pro Features</li>';
                    echo '</ul>';
                    echo '<a href="' . esc_url( $fs->get_upgrade_url( $id ) ) . '" class="wqpmb-plan-btn">Upgrade Now</a>';
                echo '</div>';
            }

            echo '</div>'; // .wqpmb-pricing-table
            echo '</div>'; // .wqpmb-pricing-wrapper
        }

        /**
         * Add custom CSS for the pricing layout
         */
        public function custom_pricing_css() {
            ?>
            <style>
                .wqpmb-pricing-wrapper {
                    max-width: 1000px;
                    margin: 40px auto;
                    text-align: center;
                    font-family: "Inter", sans-serif;
                }
                .wqpmb-pricing-title {
                    font-size: 2rem;
                    margin-bottom: 0.3em;
                }
                .wqpmb-pricing-subtitle {
                    color: #666;
                    margin-bottom: 2em;
                }
                .wqpmb-pricing-table {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                    gap: 20px;
                }
                .wqpmb-plan-card {
                    background: #fff;
                    border-radius: 16px;
                    padding: 25px;
                    border: 2px solid #e5e7eb;
                    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
                    transition: all 0.3s ease;
                }
                .wqpmb-plan-card:hover {
                    border-color: #0073aa;
                    transform: translateY(-4px);
                }
                .wqpmb-plan-title {
                    font-size: 1.3rem;
                    margin-bottom: 10px;
                    color: #222;
                }
                .wqpmb-plan-desc {
                    color: #555;
                    font-size: 0.9rem;
                    margin-bottom: 1.2em;
                }
                .wqpmb-plan-price {
                    font-size: 1.5rem;
                    font-weight: bold;
                    margin-bottom: 1em;
                    color: #0073aa;
                }
                .wqpmb-plan-features {
                    list-style: none;
                    padding: 0;
                    margin-bottom: 1.5em;
                    color: #333;
                }
                .wqpmb-plan-features li {
                    margin: 5px 0;
                }
                .wqpmb-plan-btn {
                    display: inline-block;
                    background: #0073aa;
                    color: #fff;
                    text-decoration: none;
                    padding: 10px 25px;
                    border-radius: 6px;
                    font-weight: 600;
                    transition: background 0.3s ease;
                }
                .wqpmb-plan-btn:hover {
                    background: #005c8a;
                }
            </style>
            <?php
        }
    }
}

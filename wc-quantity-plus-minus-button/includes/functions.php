<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}


if( ! function_exists( 'wqpmb_fs' ) ){
    function wqpmb_fs(){
        return new class {
                public function __call($name, $arguments)
                {
                    return null;
                }

            };
    }
}

/**
 * Check is premium version active or not
 * 
 * even old pro version(WC_Min_Max_Quantity) is active then also return true
 *
 * @return boolean
 */
function wqpmb_is_premium(){
    return defined('WQPMB_PRO_ADDON_VERSION');
}
/**
 * Check is premium version installed or not
 *
 * @return boolean
 */
function wqpmb_is_premium_installed(){
    return wqpmb_is_premium();
}


if( !function_exists( 'wqpmb_locate_template' ) ){
    /**
     * Template selection for Quantity Button
     * 
     * @global type $woocommerce
     * @return type Template
     */
    function wqpmb_locate_template( $template, $template_name, $template_path ){
        $option_key = WQPMB_Button::$option['option'];
        $datas = get_option( $option_key, false );
		
        
        $validation = isset( $datas['on_off'] ) && $datas['on_off'] == 'on' ? true : false;
        
        /**
         * @Hook Filter: wqpmb_show_validation
         * To set any validation, Based on your saved data
         * use following Filter Hook.
         * @return bool Need True false Validation
         */
        $validation = apply_filters( 'wqpmb_show_validation', $validation, $datas );
        
        if ( false === $validation ) {
                return $template;
        }
        
        $show_on_product_page       = apply_filters( 'wqpmb_on_product_page', true );
        $show_on_cart_page          = apply_filters( 'wqpmb_on_cart_page', true );
        $show_on_mini_cart_page     = apply_filters( 'wqpmb_on_mini_cart_page', true );
        $emplate_on_off             = apply_filters( 'wqpmb_template_on_off', true, $template, $template_name, $template_path );

        //Assaign Template or changing template based on $template, $template_name, $template_path, If needed
        $template                   = apply_filters( 'wqpmb_template', $template, $template_name, $template_path );

        //To set condition based on current template name, temp path etc,
        if ( false === $emplate_on_off ) {
        return $template;
        }

        if ( false === $show_on_product_page && is_product() ) {
                return $template;
        }

        if ( false === $show_on_cart_page && is_cart() ) {
                return $template;
        }
        
        if ( false === $show_on_mini_cart_page && 'cart/mini-cart.php' == $template_name ) {
                return $template;
        }

        global $woocommerce;

        $_template     = $template;
        
        $template_base_dir = untrailingslashit( WQPMB_Button::getPath('BASE_DIR') ) . '/template/';
        
        /**
         * @Hook Filter: wqpmb_template_base_dir
         * To Change Template Base Directory, Use following Hook
         * In that directory, template files folder will be locate
         * @return string Need a String of your templates folder directory. default is: this_plugin_dir/templates/
         */
        $template_base_dir = apply_filters( 'wqpmb_template_base_dir', $template_base_dir, $datas, $template, $template_name, $template_path );
        $plugin_path   = $template_base_dir;
        $template_path = ( ! $template_path ) ? $woocommerce->template_url : null;
        
        /**
         * @Hook Filter: wqpmb_template_name
         * To change Default Template Name, use following Hook
         * Currently default template name is: global/quantity-input.php
         * By this Filter, you able to change that template name. default is: global/quantity-input.php
         */
        $template_name = apply_filters( 'wqpmb_template_name', $template_name, $datas, $template, $template_path );
        
        $template      = locate_template( array( $template_path . $template_name, $template_name ) );

        if ( ! $template && file_exists( $plugin_path . $template_name ) ) {
                $template = $plugin_path . $template_name;
        }

        if ( ! $template ) {
                $template = $_template;
        }
		
        return $template;
    }
    add_filter( 'woocommerce_locate_template', 'wqpmb_locate_template',1,3 );
}

if( !function_exists( 'wqpmb_admin_body_class' ) ){
    
    /**
     * set class for Admin Body tag
     * 
     * @param type $classes
     * @return String
     * 
     * Fully fixed
     * @author Saiful Islam <codersaiful@gmail.com>
     * Fixed by Saiful at V1.1.0.0
     */
    function wqpmb_admin_body_class( $class ){
        global $current_screen;
        if( isset( $current_screen->id ) && $current_screen->id == 'ultraaddons_page_' . WQPMB_MENU_SLUG ){
            if( is_array( $class ) ){
                $class[] = 'ultraaddons';
                $class[] = WQPMB_MENU_SLUG;
                return $class;
            }
            return  ' ultraaddons ' . WQPMB_MENU_SLUG . ' ';
        }
        return $class;
    }
    add_filter( 'admin_body_class', 'wqpmb_admin_body_class' );
}


if( !function_exists( 'wqpmb_css_property_rules' ) ){
    /**
     * Validation rule for every CSS property the settings form may set.
     *
     * `color` -> hex / rgb() / rgba() / a plain colour keyword.
     * `size`  -> up to four numeric tokens, each with an optional CSS unit.
     *
     * @since 2.1.1
     *
     * @return array<string, string> Property name => rule name.
     */
    function wqpmb_css_property_rules(){
        $rules = array(
            'background-color' => 'color',
            'border-color'     => 'color',
            'color'            => 'color',
            'border-width'     => 'size',
            'border-radius'    => 'size',
            'width'            => 'size',
            'height'           => 'size',
        );

        /**
         * @Hook Filter: wqpmb_css_property_rules
         * Register validation rules for CSS properties added by add-ons.
         *
         * @param array $rules Property name => rule name ( color|size ).
         */
        return apply_filters( 'wqpmb_css_property_rules', $rules );
    }
}

if( !function_exists( 'wqpmb_sanitize_css_size' ) ){
    /**
     * Validate a CSS length such as "3px", "0", "50%", or "4px 4px 0 0".
     *
     * @since 2.1.1
     *
     * @param string $value Raw value.
     * @return string Valid CSS length, or an empty string.
     */
    function wqpmb_sanitize_css_size( $value ){
        $value = trim( preg_replace( '/\s+/', ' ', (string) $value ) );

        if( '' === $value ){
            return '';
        }

        if( in_array( strtolower( $value ), array( 'auto', 'inherit', 'initial', 'unset' ), true ) ){
            return strtolower( $value );
        }

        // Shorthand properties (border-radius) accept up to four tokens.
        $tokens = explode( ' ', $value );
        if( count( $tokens ) > 4 ){
            return '';
        }

        foreach( $tokens as $token ){
            if( ! preg_match( '/^-?\d+(\.\d+)?(px|em|rem|%|vh|vw|pt|ex|ch)?$/i', $token ) ){
                return '';
            }
        }

        return implode( ' ', $tokens );
    }
}

if( !function_exists( 'wqpmb_sanitize_css_color' ) ){
    /**
     * Validate a CSS colour: hex, rgb(), rgba() or a bare keyword.
     *
     * @since 2.1.1
     *
     * @param string $value Raw value.
     * @return string Valid CSS colour, or an empty string.
     */
    function wqpmb_sanitize_css_color( $value ){
        $value = trim( (string) $value );

        if( '' === $value ){
            return '';
        }

        // #fff / #ffffff - handled by core.
        $hex = sanitize_hex_color( $value );
        if( ! empty( $hex ) ){
            return $hex;
        }

        // rgb( 0, 0, 0 ) / rgba( 0, 0, 0, 0.5 )
        $rgb = '/^rgba?\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}\s*(?:,\s*(?:0|1|0?\.\d+)\s*)?\)$/i';
        if( preg_match( $rgb, $value ) ){
            return preg_replace( '/\s+/', ' ', $value );
        }

        // Keywords such as "transparent", "red", "currentColor".
        if( preg_match( '/^[a-z]{3,20}$/i', $value ) ){
            return $value;
        }

        return '';
    }
}

if( !function_exists( 'wqpmb_sanitize_css_value' ) ){
    /**
     * Sanitize one CSS declaration value according to its property rule.
     *
     * Unknown properties are rejected outright, so nothing that has not been
     * explicitly whitelisted can ever reach the stylesheet.
     *
     * @since 2.1.1
     *
     * @param string $property CSS property name (already whitelisted).
     * @param string $value    Raw value from the request.
     * @return string Safe value, or an empty string.
     */
    function wqpmb_sanitize_css_value( $property, $value ){
        $rules = wqpmb_css_property_rules();
        $rule  = isset( $rules[ $property ] ) ? $rules[ $property ] : '';

        switch( $rule ){
            case 'color':
                $value = wqpmb_sanitize_css_color( $value );
                break;

            case 'size':
                $value = wqpmb_sanitize_css_size( $value );
                break;

            default:
                $value = '';
                break;
        }

        /**
         * @Hook Filter: wqpmb_sanitized_css_value
         *
         * @param string $value    Sanitized value.
         * @param string $property Property name.
         */
        return apply_filters( 'wqpmb_sanitized_css_value', $value, $property );
    }
}

if( !function_exists( 'wqpmb_sanitize_css_block' ) ){
    /**
     * Last line of defence before CSS is printed.
     *
     * Removes anything that could terminate the surrounding <style> element.
     * This also repairs installs that stored a malicious value with an older
     * version of the plugin, because the option is cleaned on output.
     *
     * @since 2.1.1
     *
     * @param string $css Raw CSS.
     * @return string CSS that cannot break out of a <style> block.
     */
    function wqpmb_sanitize_css_block( $css ){
        if( ! is_string( $css ) || '' === $css ){
            return '';
        }

        $css = wp_strip_all_tags( $css );

        // wp_strip_all_tags() leaves stray angle brackets behind; drop them.
        $css = str_replace( array( '<', '>' ), '', $css );

        return $css;
    }
}

if( !function_exists( 'wqpmb_allowed_setting_fields' ) ){
    /**
     * Map of every field our settings form is allowed to submit.
     *
     * Anything that is not described here is dropped before it ever reaches
     * the save handler, so an attacker cannot smuggle extra keys (or extra
     * CSS properties) into the options table.
     *
     * Add-ons that render their own fields inside our form must register
     * them through the `wqpmb_allowed_setting_fields` filter, otherwise
     * their values will be discarded.
     *
     * @since 2.1.1
     *
     * @return array{checkbox: string[], textarea: string[], css_groups: array<string, string[]>}
     */
    function wqpmb_allowed_setting_fields(){
        $fields = array(

            // Checkboxes: present in POST means "on", absent means "off".
            'checkbox'   => array(
                'on_off',
                'quantiy_box_archive',
            ),

            // Free text areas.
            'textarea'   => array(
                'custom_css',
            ),

            /**
             * CSS groups.
             *
             * Key   = the POST array name.
             * Value = the CSS property names that group may contain.
             */
            'css_groups' => array(
                'css'       => array(
                    'background-color',
                    'border-color',
                    'color',
                    'border-width',
                    'border-radius',
                ),
                'css_hover' => array(
                    'background-color',
                    'border-color',
                    'color',
                ),
                'css_input' => array(
                    'width',
                    'height',
                    'background-color',
                    'border-color',
                    'color',
                ),
            ),
        );

        /**
         * @Hook Filter: wqpmb_allowed_setting_fields
         * Register additional settings fields (e.g. from the premium add-on).
         *
         * @param array $fields Allowed field map.
         */
        return apply_filters( 'wqpmb_allowed_setting_fields', $fields );
    }
}

if( !function_exists( 'wqpmb_get_posted_settings' ) ){
    /**
     * Read the settings form submission.
     *
     * Replaces the previous `filter_input_array( INPUT_POST )` call, which
     * handed the entire unfiltered request body to the save handler.
     *
     * Only fields declared in wqpmb_allowed_setting_fields() are returned,
     * every value is unslashed and sanitized, and control fields such as
     * `nonce` / `configure_submit` are deliberately left out so they never
     * end up inside the stored option.
     *
     * @since 2.1.1
     *
     * @return array Sanitized settings, or an empty array when nothing was posted.
     */
    function wqpmb_get_posted_settings(){

        // Nonce is verified in wqpmb_form_submit() before anything is saved.
        // phpcs:disable WordPress.Security.NonceVerification.Missing
        if( empty( $_POST ) ){
            return array();
        }

        $fields = wqpmb_allowed_setting_fields();
        $datas  = array();

        // Checkboxes - keep the original "isset() means enabled" behaviour.
        $checkboxes = isset( $fields['checkbox'] ) && is_array( $fields['checkbox'] ) ? $fields['checkbox'] : array();
        foreach( $checkboxes as $field ){
            if( isset( $_POST[ $field ] ) ){
                $datas[ $field ] = sanitize_text_field( wp_unslash( $_POST[ $field ] ) );
            }
        }

        // Textareas.
        $textareas = isset( $fields['textarea'] ) && is_array( $fields['textarea'] ) ? $fields['textarea'] : array();
        foreach( $textareas as $field ){
            if( isset( $_POST[ $field ] ) && ! is_array( $_POST[ $field ] ) ){
                $datas[ $field ] = wqpmb_sanitize_css_block(
                    sanitize_textarea_field( wp_unslash( $_POST[ $field ] ) )
                );
            }
        }

        // CSS groups - only known groups, and inside them only known properties.
        $css_groups = isset( $fields['css_groups'] ) && is_array( $fields['css_groups'] ) ? $fields['css_groups'] : array();
        foreach( $css_groups as $group => $properties ){

            if( ! isset( $_POST[ $group ] ) || ! is_array( $_POST[ $group ] ) || ! is_array( $properties ) ){
                continue;
            }

            // Each member is sanitized individually a few lines below.
            $posted_group = wp_unslash( $_POST[ $group ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            $clean_group  = array();

            foreach( $properties as $property ){

                if( ! isset( $posted_group[ $property ] ) || is_array( $posted_group[ $property ] ) ){
                    continue;
                }

                $clean_group[ $property ] = wqpmb_sanitize_css_value(
                    $property,
                    sanitize_text_field( $posted_group[ $property ] )
                );
            }

            $datas[ $group ] = $clean_group;
        }
        // phpcs:enable WordPress.Security.NonceVerification.Missing

        return $datas;
    }
}


if( !function_exists( 'wqpmb_submit_form' ) ){
    /**
     * Form Submit based on Action Hook
     *
     * @param type $classes
     * @return Void
     */
    function wqpmb_form_submit( $datas ){

        /*
        * We need to verify this came from our screen and with proper authorization,
        * because the save_post action can be triggered at other times.
        */

        if ( ! isset( $_POST['nonce'] ) ) { // Check if our nonce is set.
            return;
        }

        // verify this came from the our screen and with proper authorization,
        // because save_post can be triggered at other times
        $nonce = sanitize_text_field( wp_unslash( $_POST['nonce'] ) );
        if( ! wp_verify_nonce( $nonce, plugin_basename( WQPMB_BASE_DIR ) ) ) {
            return;
        }

        /**
         * A nonce only proves the request came from our form - it says nothing
         * about who sent it. Re-check the capability here so the handler is
         * safe even if it is ever reached from outside the settings screen.
         *
         * @since 2.1.1
         */
        $capability = apply_filters( 'wqpmb_menu_capability', 'manage_woocommerce' );
        if( ! current_user_can( $capability ) ){
            return;
        }

        $option_key = WQPMB_Button::$option['option'];
        $css_key = WQPMB_Button::$option['css'];
        if( NULL !== filter_input( INPUT_POST, 'configure_submit' ) && !empty( $datas ) ){
            /**
             * @Hook Filter: wqpmb_data_on_save
             * Populate data where data will save and pass data condition
             * 
             * @return Array When submit form, user able to modify by this filter
             */
            $datas = apply_filters( 'wqpmb_data_on_save', $datas );
            update_option( $option_key, $datas );
            
            $selector = WQPMB_Button::$css_selector;
            $input_css_selector = WQPMB_Button::$input_css_selector;
            /**
             * @Hook Filter: wqpmb_default_css_selector
             * to change selector of css selector, Currently set for qty button and input box
             * @return String 
             */
            $selector = apply_filters( 'wqpmb_default_css_selector', $selector, $datas  );
            if( isset( $datas['css'] ) && is_array( $datas['css'] ) ){
                $style = "\n";
                $style_hover = "\n";
                foreach( $datas['css'] as $property => $value ){
                    
                    $style .= !empty( $value ) && !is_array( $value ) ? $property . ': ' . $value . " !important;\n" : '';
                }
                $css_hover = ! empty( $datas['css_hover'] ) && is_array( $datas['css_hover'] ) ? $datas['css_hover'] : [];
                foreach( $css_hover as $property => $value ){
                    
                    $style_hover .= !empty( $value ) && !is_array( $value ) ? $property . ': ' . $value . " !important;\n" : '';
                }
                $style_input = "\n";
                $css_input = ! empty( $datas['css_input'] ) && is_array( $datas['css_input'] ) ? $datas['css_input'] : [];

                foreach( $css_input as $property => $value ){
                    
                    $style_input .= !empty( $value ) && !is_array( $value ) ? $property . ': ' . $value . " !important;\n" : '';
                }
                $css_base = $selector . "{" . $style . "}\n";
                $css_hover = $selector . ":hover{" . $style_hover . "}\n";
                $css_input = $input_css_selector . "{" . $style_input . "}\n";
                $css = $css_base . $css_hover . $css_input;

                $css = apply_filters( 'wqpmb_css_on_save', $css, $datas );
                update_option( $css_key, $css);
            }
        }
        if( NULL !== filter_input( INPUT_POST, 'reset_button' ) ){
            $default_data = WQPMB_Button::defaultDatas();
            
            $r_data = apply_filters( 'wqpmb_data_on_reset', $default_data, $datas );
            update_option( $option_key , $r_data);
            
            $css = apply_filters( 'wqpmb_css_on_reset', false, $datas );
            update_option( $css_key, $css);
        }
    }
    add_filter( 'wqpmb_save_data', 'wqpmb_form_submit' );
}

if( !function_exists( 'wqpmb_button_off_in_minicart' ) ){
    
    /**
     * Disable our qty button from other place where global $product is not available
     *
     * @param bool $bool
     * @return bool
     */
    function wqpmb_button_off_in_minicart( $bool ){
        global $product;
        if(is_null($product)) return false;
        return $bool;
    }
    //add_filter( 'wqpmb_template_on_off', 'wqpmb_button_off_in_minicart' ); //currently disable
}

if( !function_exists( 'wqpmb_header_css' ) ){
    /**
     * Output CSS in the header
     * 
     * @return void
     */
    function wqpmb_header_css(){

        $style = '';

        $css_key = WQPMB_Button::$option['css'];
        $css     = get_option( $css_key );
        if( !empty( $css ) && is_string( $css ) ){
            // Sanitized on output as well, so options saved by an older
            // version of the plugin can never break out of the stylesheet.
            $style .= wqpmb_sanitize_css_block( $css );
        }

        // Add custom CSS
        $option_key = WQPMB_Button::$option['option'];
        $options    = get_option( $option_key );
        $custom_css = isset( $options['custom_css'] ) ? $options['custom_css'] : '';

        if( !empty( $custom_css ) && is_string( $custom_css ) ){
            $style .= "\n" . wqpmb_sanitize_css_block( $custom_css );
        }

        $style = trim( $style );

        if( '' === $style ){
            return;
        }

        /**
         * Printed through the stylesheet we already enqueue, instead of a
         * hand built <style> tag, so WordPress handles the escaping.
         */
        if( wp_style_is( 'wqpmb-style', 'enqueued' ) || wp_style_is( 'wqpmb-style', 'registered' ) ){
            wp_add_inline_style( 'wqpmb-style', $style );
            return;
        }

        // Fallback when the stylesheet was dequeued by the theme.
        wp_register_style( 'wqpmb-inline-style', false, array(), WQPMB_VERSION );
        wp_enqueue_style( 'wqpmb-inline-style' );
        wp_add_inline_style( 'wqpmb-inline-style', $style );
    }
    add_action( 'wp_enqueue_scripts', 'wqpmb_header_css', 100 );
}


if( ! function_exists('wqpmb_doc_link') ){
    
    function wqpmb_doc_link( $url, $title='Helper doc' ){
        ?>
            <a href="<?php echo esc_url($url)?>" target="_blank" class="wpt-doc-lick"><i class="wcmmq_icon-help-circled-alt"></i><?php esc_html( $title ); ?></a>
        <?php
    }
}
/**
* start the customisation
*/
// add_action('woocommerce_before_shop_loop', function() {
//     add_filter('woocommerce_loop_add_to_cart_link', 'wqpmb_loop_add_to_cart', 10, 3);
// });

/**
* customise Add to Cart link/button for product loop
* @param string $button
* @param object $product
* @param array $link
* @return string
*/
function wqpmb_loop_add_to_cart($button, $product, $link) {
    $product_type = $product->get_type();
    // return $button;
    // not for variable, grouped or external products
    if (!in_array($product_type, array('variable', 'grouped', 'external'))) {
        // only if can be purchased
        if ($product->is_purchasable()) {
            // show qty +/- with button
            ob_start();
            woocommerce_simple_add_to_cart();
            $button = ob_get_clean();
        }
    }elseif( $product_type == 'variable' ){
        if ($product->is_purchasable()) {
            //woocommerce_template_single_add_to_cart
            //woocommerce_template_loop_add_to_cart
            // show qty +/- with button
            ob_start();
            woocommerce_template_single_add_to_cart();
            $button = ob_get_clean();
        }
    }

    return $button;
}
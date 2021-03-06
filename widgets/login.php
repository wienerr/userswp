<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * UsersWP forgot password widget.
 *
 * @since 1.0.22
 */

class UWP_Login_Widget extends WP_Super_Duper {

    public function __construct() {

        $options = array(
            'textdomain'    => 'userswp',
            'block-icon'    => 'admin-site',
            'block-category'=> 'widgets',
            'block-keywords'=> "['userswp','login']",
            'class_name'     => __CLASS__,
            'base_id'       => 'uwp_login',
            'name'          => __('UWP > Login','userswp'),
            'widget_ops'    => array(
                'classname'   => 'uwp-login-class bsui',
                'description' => esc_html__('Displays login form or current logged in user.','userswp'),
            ),
            'arguments'     => array(
                'title'  => array(
                    'title'       => __( 'Widget title', 'userswp' ),
                    'desc'        => __( 'Enter widget title', 'userswp' ),
                    'type'        => 'text',
                    'desc_tip'    => true,
                    'default'     => '',
                    'advanced'    => false
                ),
                'form_title'  => array(
                    'title'       => __( 'Form title', 'userswp' ),
                    'desc'        => __( 'Enter the form title (or "0" for no title)', 'userswp' ),
                    'type'        => 'text',
                    'desc_tip'    => true,
                    'default'     => '',
                    'placeholder' => __('Login','userswp'),
                    'advanced'    => true
                ),
                'form_padding'  => array(
                    'title'       => __( 'Form padding', 'userswp' ),
                    'desc'        => __( 'Enter the px value for the form padding, default is 40, 10 looks better in sidbars.', 'userswp' ),
                    'type'        => 'number',
                    'desc_tip'    => true,
                    'default'     => '',
                    'placeholder' => __('default 40, 10 is better in sidebars','userswp'),
                    'advanced'    => true
                ),
                'logged_in_show'  => array(
                    'title' => __('Logged in show', 'userswp'),
                    'desc' => __('What to show when logged in.', 'userswp'),
                    'type' => 'select',
                    'options'   =>  array(
                        ""        =>  __('User Dashboard (default)', 'userswp'),
                        "simple"        =>  __('Simple username and logout link', 'userswp'),
                        "empty"        =>  __('Nothing', 'userswp'),
                    ),
                    'default'  => '',
                    'desc_tip' => true,
                    'advanced' => true
                ),
                'design_style'  => array(
                    'title' => __('Design Style', 'userswp'),
                    'desc' => __('The design style to use.', 'userswp'),
                    'type' => 'select',
                    'options'   =>  array(
                        ""        =>  __('default', 'userswp'),
                        "bs1"        =>  __('Style 1', 'userswp'),
                    ),
                    'default'  => '',
                    'desc_tip' => true,
                    'advanced' => true
                ),
                'css_class'  => array(
                    'type' => 'text',
                    'title' => __('Extra class:', 'userswp'),
                    'desc' => __('Give the wrapper an extra class so you can style things as you want.', 'userswp'),
                    'placeholder' => '',
                    'default' => '',
                    'desc_tip' => true,
                    'advanced' => true,
                ),
            )

        );
        
        // Design style
        

        // add integrations by default and add option to remove

        // GD
        if(class_exists( 'GeoDirectory' )){
            $options['arguments']['disable_gd'] = array(
                'title' => __("Disable GeoDirectory links from the user dashboard.", 'userswp'),
                'type' => 'checkbox',
                'desc_tip' => true,
                'value'  => '1',
                'default'  => '',
                'advanced' => true,
                'element_require' => '[%logged_in_show%]==""',
            );
        }

        // WPI
        if(class_exists( 'WPInv_Plugin' )){
            $options['arguments']['disable_wpi'] = array(
                'title' => __("Disable WP Invoicing links from the user dashboard.", 'userswp'),
                'type' => 'checkbox',
                'desc_tip' => true,
                'value'  => '1',
                'default'  => '',
                'advanced' => true,
                'element_require' => '[%logged_in_show%]==""',
            );
        }



        parent::__construct( $options );
    }

    public function output( $args = array(), $widget_args = array(), $content = '' ) {

        $defaults = array(
            'form_title'      => __('Login','userswp'),
            'form_padding'     => '',
            'logged_in_show'     => '',
            'css_class'     => 'border-0'
        );

        /**
         * Parse incoming $args into an array and merge it with $defaults
         */
        $args = wp_parse_args( $args, $defaults );

        // if logged in and set to show nothing then bail.
        if(is_user_logged_in() && $args['logged_in_show']=='empty'){
            return '';
        }

        ob_start();

        echo '<div class="uwp_widgets uwp_widget_login">';

        if(is_user_logged_in() && !is_admin() && !$this->is_preview()) {

            if($args['logged_in_show']=='simple'){
                self::simple_output($args);
            }else{
                self::advanced_output($args);
            }

        } else {
            
            global $uwp_widget_args;
            $uwp_widget_args = $args;

            $design_style = !empty($args['design_style']) ? esc_attr($args['design_style']) : uwp_get_option("design_style",'bootstrap');
            $template = $design_style ? $design_style."/login" : "login";

            echo '<div class="uwp_page">';

            uwp_locate_template($template);

            echo '</div>';

        }

        echo '</div>';

        $output = ob_get_clean();


        return trim($output);

    }

    public static function advanced_output($args){
        global $uwp_login_widget_args;
        $uwp_login_widget_args = $args;

        echo '<div class="uwp_page">';

        uwp_locate_template('dashboard');

        echo '</div>';
    }

    public static function simple_output($args){
        global $current_user;

        $template = new UsersWP_Templates();

        $logout_url = $template->uwp_logout_url();

        echo '<div class="uwp-login-widget user-loggedin">';

        echo '<p>'.__( 'Logged in as ', 'userswp' );

        echo '<a href="'. apply_filters('uwp_profile_link', get_author_posts_url($current_user->ID), $current_user->ID).'">' . get_avatar( $current_user->ID, 35 ). '<strong>'. apply_filters('uwp_profile_display_name', $current_user->display_name).'</strong></a>';

        echo '<span>';

        printf(__( '<a href="%1$s">Log out</a>', 'userswp'), esc_url( $logout_url ));

        echo '</span>';

        echo '</p>';

        echo '</div>';
    }
}
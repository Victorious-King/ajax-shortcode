<?php
/**
 * Plugin Name: Ajax Shortcode
 * Description: Demonstration of WordPress Ajax working as a shortcode.
 * Plugin URI:  /
 * Version:     2021.08.01
 * Author:      Yuri
 * Author URI:  /
 * License:     GPLv3
 */

add_action(
    'plugins_loaded',
    array ( B5F_SO_13498959::get_instance(), 'plugin_setup' )
);

class B5F_SO_13498959
{
    private $cpt = 'post'; # Adjust the CPT
    protected static $instance = NULL;
    public $plugin_url = '';
    public function __construct() {
        $this->plugin_url = plugins_url( '/', __FILE__ );
        // add_action( 'admin_menu', array( $this,'my_admin_menu') );

        add_shortcode( 'active-switch', array( $this, 'shortcode') );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'ajax_enqueue_styles' ) );
        add_action( 'wp_ajax_query_acive_plugin', array( $this, 'query_acive_plugin' ) );
        add_action( 'wp_ajax_nopriv_query_acive_plugin', array( $this, 'query_acive_plugin' ) );
    }

    public static function get_instance()
    {
        NULL === self::$instance and self::$instance = new self;
        return self::$instance;
    }


    /**
     * Regular plugin work
     */
    public function plugin_setup()
    {
       // $this->plugin_url = plugins_url( '/', __FILE__ );
       //  add_action( 'admin_menu', array( $this,'my_admin_menu') );

       //  add_shortcode( 'active-switch', array( $this, 'shortcode') );
       //  add_action( 'admin_wp_enqueue_scripts', array( $this, 'enqueue' ) );
       //  add_action( 'admin_wp_enqueue_scripts', array( $this, 'ajax_enqueue_styles' ) );
       //  add_action( 'wp_ajax_query_acive_plugin', array( $this, 'query_acive_plugin' ) );
       //  add_action( 'wp_ajax_nopriv_query_acive_plugin', array( $this, 'query_acive_plugin' ) );
    }


    public function ajax_enqueue_styles() {
    
    
         wp_enqueue_style( 'ajax-active-stylesheet', "{$this->plugin_url}ajax-shortcode.css" ,array(),rand(111,9999),'all');

  
    }
    
    /**
     * SHORTCODE output
     */
    public function shortcode( $atts, $content = null ) 
    {

        $output = shortcode_atts( array(
                    'plugin_name' => ''
                   ), $atts );

        $checked =  is_plugin_active( $output['plugin_name']) ? 'checked' :' ';
        $output = '<p style="text-align:center;" id="">
                    
                    <label class="toggle-plugin">
                      <input class="input-plugin" id="toggleswitch" type="checkbox" '. $checked .'  onchange="switchFunction(event)" plugin-name="'.$output['plugin_name'].'">
                      <span class="roundbutton-plugin" id="toggle-span"></span>
                    </label>
                    </p>';
        return $output;
    }

    /**
     * ACTION Enqueue scripts
     */
    public function enqueue() 
    {
        # jQuery will be loaded as a dependency
        ## DO NOT use other version than the one bundled with WP
        ### Things will BREAK if you do so

        wp_enqueue_script( 
             'ajax-active-script',
             "{$this->plugin_url}ajax-shortcode.js",
             array( 'jquery' ),rand(111,9999),'all'
        );
        # Here we send PHP values to JS
        wp_localize_script( 
             'ajax-active-script',
             'wp_ajax',
             array( 
                 'ajaxurl'      => admin_url( 'admin-ajax.php' ),
                 'ajaxnonce'   => wp_create_nonce( 'ajax_post_validation' ),
                 'loading'    => 'http://i.stack.imgur.com/drgpu.gif'
            ) 
        );
    }

    /**
     * AJAX query random post
     * 
     * Check for security and send proper responses back
     */
    public function query_acive_plugin()
    {
        check_ajax_referer( 'ajax_post_validation', 'security' );

        $plugin_name = $_POST['plugin_name'];
        if( !isset( $plugin_name ))  {
            wp_send_json_error( array( 'error' => __( 'Could not opearate a plugin.' ) ) );
            wp_die();
        }

        if($_POST['initial']=='true'){
            wp_send_json_success( array('msg'=>'The operation is done successfully','checked'=>is_plugin_active( $plugin_name )) );
            wp_die();
        }
        else{
            $active_result = $this-> operatePlugin($plugin_name);

            wp_send_json_success( array('msg'=>'The operation is done successfully','checked'=>$active_result['checked'] ));
        }
    }

    /**
     * AUX FUNCTION 
     * Search a random Post Type and return the post_content
     */
    public function operatePlugin($plugin_name)
    {
        $checked = 0;
        
       if (! is_plugin_active( $plugin_name ) ) {
             $result = activate_plugin( $plugin_name );
             if ( is_wp_error( $result ) ) {
                    wp_die(__('This plugin is corrupted for some issues'),'Error');
                }


        }  else {

            $result = true;
            deactivate_plugins( $plugin_name );
            $checked = 1;
        }

        return array('result'=>$result,'checked'=>$checked);
    }
}

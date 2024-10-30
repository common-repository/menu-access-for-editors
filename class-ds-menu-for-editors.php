<?php
/**
 @ copyright (C) 2019 Delphi Solutions Ltd (trading as WP Plugin Pros)
 * 
 * Class: Menu Access for Editors
 * 
 * Summary: Allow user role "Editor" to Edit Menus
 * 
 * After calling Check ->errors for any errors to show to admin
 * 
 * 
 * Final Testing:-
 * 1. Check menu and access via admin, editor (only menu should appear) and author roles
 * 2. Direct access via {website url}/wp-admin/customize.php must:-
 *   - redirect to dashboard with editor role 
 *   - allowed with admin role
 *   
 * @since 1.0.0 20-Sep-2019 Complete
 * @rev 1.0.2 03-Feb-21 Tested with WordPress 5.6
 */

if (!defined( 'ABSPATH' ) ) {
    die( 'Direct access to this file is not permitted' );
}


class DS_Menu_For_Editors {

    /**
     * Constructor
     *
     * @since 1.0.0
     *
     */
    
    const PLUGIN_VERSION = "1.0.2"; // This must match the version in the main plugin file 
    
    public $role = 'editor'; // user role to update
    public $error = ''; // HTML admin user error message
    private $role_obj = null; // Role to update
    
    public function __construct(){
        
        register_activation_hook  ( DS_PLUGIN_FILE, array($this,'activate') );
        register_deactivation_hook( DS_PLUGIN_FILE, array($this,'deactivate') );
        add_action( 'admin_menu', array($this,'remove_menus'),9999 ); // after admin menu has been created, 9999 to go last.
        add_action( 'admin_notices', array( $this,'show_admin_notices' ) );
        // Get the role object for the current user
        $this->role_obj = get_role( $this->role );
        
        if ( false == $this->role_obj ) { // failed to load
            $this->error .= 'plugin error (get_role)<br>';
        }
        
    }
    
    
    /**
     * Activate the plugin
     *
     * @since 1.0.0
     *
     */
    
    public function activate() {
        global $wp_version;
    
        // Note: Using "die" during activation is the correct procedure to prevent installation / show a message 

        // Get Plugin details
        $plugin_data    = get_plugin_data( DS_PLUGIN_FILE, false );
        $plugin_title   = $plugin_data['Title'];
        $plugin_dir     = $plugin_data['TextDomain'];
        $plugin_version = $plugin_data[ 'Version' ];
        
        if ( $plugin_version != $this::PLUGIN_VERSION ) {
            die( "<strong>Upgrade Plugin</strong> <strong>". $plugin_title ."</strong> ($plugin_dir).<br>
                This Plugin has an invalid version number (Plugin version does not match class version ) <br>
                To do this your WordPress Administrator will need to login to WordPress."
                ); // Prevent plugin from being installed
                $this->error .= 'Plugin version does not match class version '; // Insure we update the version number correctly.
        }
        
        // Check the mimumum WP and PHP version numbers
        if ($wp_version < 4.7) {
            die( "<strong>Upgrade WordPress</strong> before installing plugin <strong>". $plugin_title ."</strong> ($plugin_dir).<br>
                This Plugin has been tested for WordPress version 4.7 and upwards. (Current version $wp_version)<br>
                To do this your WordPress Administrator will need to login to WordPress."
            ); // Prevent plugin from being installed
        } 
        
        if (phpversion() < 5.6) {
            die( "<strong>Upgrade PHP</strong> before installing plugin <strong>". $plugin_title ."</strong> ($plugin_dir).<br>
                This Plugin has been tested with PHP version 5.6 and upwards. (Current version ".phpversion().")<br>
                You may need to login to (or contact) your hosting company to upgrade PHP. Old versions aren't supported or secure! "
                ); // Prevent plugin from being installed
        }
        
        
        // add a new capability
        $this->role_obj->add_cap( 'edit_theme_options', true );
        
        add_option('ds_menu_for_editors', $plugin_version); // Store our current version number
        
    }
    
    
    /**
     * Deactivate the plugin
     *
     * @since 1.0.0
     *
     */
    
    function deactivate() {
    
        // Remove capability
        $this->role_obj->remove_cap( 'edit_theme_options' );
        
        // Clean up Database
        delete_option('ds_menu_for_editors'); // remove version number
    }
    
    
    /**
     * 1. Remove unwanted submenus from user role.
     * 2. Prevent direct URL access to these unwanted submenus (by directing user to the main admin panel) 
     *  
     * @since 1.0.0
     *
     */
    
    function remove_menus() {
        global $submenu;
        
        // Only update admin menu for the specified user role (as function called for all admin users)
        $user_obj = wp_get_current_user();
        if ( !in_array($this->role, $user_obj->roles) ) return;
        $user_obj = null;
             
        // Check the submenu exists before working with it
        if (!isset($submenu['themes.php'])) return; 
        
        $self = basename($_SERVER['PHP_SELF']); // Basename removes path to the php file
        
        // Remove all this submenu's items, except for the menu item
        foreach ( $submenu['themes.php'] as $menu_index => $menu_item ) {
            $slug = $menu_item[2];
            // jm debug echo '<br>'.$menu_index.' '.print_r($menu_item,true).' slug:'.$slug.'<br>self: '.$self.'<br>';
            if ('nav-menus.php' != $slug ) { 
                if ( remove_submenu_page('themes.php',$slug) ) {
                    $self_len = strlen($self); 
                    $slug_len = strlen($slug);
                    // Prevent direct access to restricted page (user may have entered a manual url)
                    if ( $slug_len >= $self_len and substr($slug,  0, $self_len ) == $self ) {
                        wp_redirect( admin_url() ); 
                    } 
                } else {
                    $this->error .= "plugin error (remove_submenu_page '".print_r($menu_item,true)."')<br>";
                }
            }
        }
    }

    
    
    /**
     * Show admin notices (includes plugin errors)
     *
     * @since 1.0.0
     *
     */
    
    function show_admin_notices() {
        if ( '' == $this->error ) return;
        
        // Get Plugin details
        $plugin_data    = get_plugin_data( DS_PLUGIN_FILE, false );
        $plugin_title   = $plugin_data['Title'];
        $plugin_dir     = $plugin_data['TextDomain'];
        $author_uri     = $plugin_data['AuthorURI'];
                
        echo '<div class="error notice"> <h1>'. $plugin_title .' - Plugin Error, Sorry!</h1>
            <p>Leave a support message at <a href="'.$author_uri.'" target="_blank">'.$author_uri.'</a> with the below error messsage.
                Please give us a few days to get back to you before leaving negative feedback.</p><p>Error: '.$this->error.'</p>
                <p>Plugin directory: '. $plugin_dir .', version: '.$this::PLUGIN_VERSION.'</p>
               </div>';
        
    }
    
}
?>
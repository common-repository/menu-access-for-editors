<?php
/*
 
 @ copyright (C) 2019-2021 Delphi Solutions Ltd (trading as WP Plugin Pros), excludes freemius SDK 
 
 Plugin Name: Menu Access for Editors
 Description: Allow user role "Editor" to Edit Menus
 Plugin URI: https://WPpluginPROs.com/
 Author: Jeremy Mitchell, wpPluginPROs.com
 Version: 1.0.2
 Requires at least: 4.7
 Requires PHP: 5.6
 License: GPL3
 
 
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <https://www.gnu.org/licenses/gpl-3.0.html>.
 
 
 @rev 1.0.2 - 03-Feb-21 Tested with WordPress 5.6
 */


if (!defined( 'ABSPATH' ) ) {
    die( 'Direct access to this file is not permitted' );
}

// Only attempt to create our plugin object once - this php file gets included more than once by WP when activating/deactivating!
if ( defined('DS_PLUGIN_FILE')) {
    exit;
}

// Constants
define ( 'DS_PLUGIN_FILE', __FILE__ ); // Path to main plugin file

// Freemius SDK
// NOT currently in use - require_once (dirname(DS_PLUGIN_FILE).'/freemius-sdk.php');


// Main class
require_once (dirname(DS_PLUGIN_FILE).'/class-ds-menu-for-editors.php');

// Plugin is accessable to all users from the admin panel
if ( is_admin()) { // Admin Panel
    $ds_menu_for_editors_obj = new DS_Menu_For_Editors;
}


?>
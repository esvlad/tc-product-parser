<?
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @wordpress-plugin
 * Plugin Name:       TCP Parser
 * Plugin URI:        https://github.com/esvlad/tc-product-parser
 * Description:       Парсер для каталога товарос работающий на плагине TC Product.
 * Version:           0.0.1
 * Author:            Старцев Владислав
 * Author URI:        https://es.vlad
 * License:           MIT
 */
 
define ('TCPP_PLUGIN_DIR', plugin_dir_path( __FILE__ ));
define ('DIR_TEMPLATE_ADMIN', plugin_dir_path( __FILE__ ) . 'admin/view/');
define ('TCPP_PLUGIN_URL', plugins_url('', __FILE__));

$tpl = plugin_dir_path( __FILE__ );
$tpl .= 'admin/view/';
define ('TCPP_ADMIN_TPL', $tpl);

require_once TCPP_PLUGIN_DIR . 'config.php';

if ( ! defined( 'WPINC' ) ) {
	die;
}

register_activation_hook( __FILE__, array('TCPP_Config','activate') );
register_deactivation_hook( __FILE__, array('TCPP_Config','deactivate') );
register_uninstall_hook(__FILE__, array('TCPP_Config','uninstall'));


require_once TCPP_PLUGIN_DIR . 'tcpp.php';

function run_tcpp_app() {
	try{
		$plugin = new TCPP();
		$plugin->run();
	} catch(Exception $e){
		$e->getMessage();
	}
}

run_tcpp_app();
?>
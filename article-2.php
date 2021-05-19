<?php
/**
 * Plugin Name: CAH Article Custom Post Type
 * Description: Custom Post Type for Articles. Based on Code by Austin Tindle and Alessandro Vecchi. Requires the Multiple Featured Images plugin by Marcus Kober (<a href="https://wordpress.org/plugins/multiple-featured-images/">https://wordpress.org/plugins/multiple-featured-images/</a>).
 * Author: Mike W. Leavitt
 * Version: 2.0.0
 */

defined( 'ABSPATH' ) || exit( "No direct access plzthx." );

define( 'CAH_ARTICLE__PLUGIN_NAME', 'cah_article' );
define( 'CAH_ARTICLE__PLUGIN_VERSION', '2.0.0' );
define( 'CAH_ARTICLE__PLUGIN_URL', plugins_url( basename( dirname( __FILE__ ) ) ) );
define( 'CAH_ARTICLE__PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );
define( 'CAH_ARTICLE__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CAH_ARTICLE__PLUGIN_FILE', __FILE__ );

require_once 'includes/cah-article-setup.php';
require_once 'includes/cah-article-editor.php';

if( !function_exists( 'cah_article_2_plugin_activate' ) ) {
    function cah_article_2_plugin_activate() {
        CAH_ArticleSetup::register_article();
        flush_rewrite_rules();
    }
}
register_activation_hook( CAH_ARTICLE__PLUGIN_FILE, 'cah_article_2_plugin_activate' );

if( !function_exists( 'cah_article_2_plugin_deactivate' ) ) {
    function cah_article_2_plugin_deactivate() {
        flush_rewrite_rules();
    }
}
register_deactivation_hook( CAH_ARTICLE__PLUGIN_FILE, 'cah_article_2_plugin_deactivate' );

add_action( 'plugins_loaded', array( 'CAH_ArticleSetup', 'setup' ), 10, 0);
?>
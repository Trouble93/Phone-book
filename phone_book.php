<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://google.com
 * @since             1.0.0
 * @package           Phone_book
 *
 * @wordpress-plugin
 * Plugin Name:       Phone book
 * Plugin URI:        https://google.com
 * Description:       Записная книга
 * Version:           1.0.0
 * Author:            Roman Khavanskyi
 * Author URI:        https://google.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       phone_book
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('PHONE_BOOK_VERSION', '1.0.0');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-phone_book-activator.php
 */
function activate_phone_book()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-phone_book-activator.php';
    Phone_book_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-phone_book-deactivator.php
 */
function deactivate_phone_book()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-phone_book-deactivator.php';
    Phone_book_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_phone_book');
register_deactivation_hook(__FILE__, 'deactivate_phone_book');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-phone_book.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_phone_book()
{

    $plugin = new Phone_book();
    $plugin->run();

}

run_phone_book();


add_shortcode('phone_book_plug', 'phone_book');
function phone_book()
{
    $book = get_option('my_phone_book');

    ob_start(); ?>

    <div id="myDIV" class="phone-book">
        <h2 style="margin:5px">Phone Book</h2>
        <form id="send" enctype="multipart/form-data">
            <div class="inputs">
                <input type="text" name="name" id="name" placeholder="Name">
                <span class="error-message-name">Input correct name</span>
                <input type="text" name="phone" id="phone" placeholder="Phone number">
                <span class="error-message-phone">Input correct phone number</span>
                <button class="addBtn">Add</button>
            </div>
        </form>
    </div>

    <ul id="myUL">

        <?php
        if (isset($book) && !empty($book)) :
            $close = '\u00D7';
           $close = json_decode('"'.$close.'"');
            foreach ($book as $value) :
                echo sprintf('<li><span class="user-name" >%s</span> <span class="phone-number">%s</span><span class="close">%s</span></li>', $value['name'] , $value['phone'], $close );
            endforeach;
        endif; ?>

    </ul>

    <?php
    $html = ob_get_contents();
    ob_end_clean();
    return $html;
}
// Connect scripts and styles
add_action('wp_enqueue_scripts', 'load_plugin_files');

function load_plugin_files()
{
    $plugin_url = plugin_dir_url(__FILE__);

    wp_enqueue_style('style', $plugin_url . 'assets/css/style.css');
    wp_enqueue_script('jq', $plugin_url . 'assets/js/jquery-3.6.0.min.js');
    wp_enqueue_script('script', $plugin_url . 'assets/js/script.js');

    wp_localize_script('script', 'MyAjax', [
        'ajaxurl' => admin_url("admin-ajax.php")
    ]);
}

// Add and remove ajax callback
add_action('wp_ajax_update_data', 'update_data');
add_action('wp_ajax_nopriv_update_data', 'update_data');

function update_data() {

    $type = sanitize_text_field($_POST['type']);
    $name = sanitize_text_field($_POST['name']);
    $phone = sanitize_text_field($_POST['phone']);

    $book =  get_option('my_phone_book') !== false ? get_option('my_phone_book') : array();
    // add new item
    if($type == 'save') {
        $data = array('phone' => $phone, 'name' => $name);
        foreach ($book as $value) {
            if($value['phone'] == $phone){
                wp_die('error');
            }
        }
            array_push($book, $data);
            update_option('my_phone_book', $book);

            wp_send_json($data);

    }
    // remove item
    elseif ($type == 'delete') {
       if(!empty($book)){
           $temp = array();
           foreach ($book as $value) {
               if($value['phone'] != $phone && $value['name'] != $name){
                    array_push($temp, $value);
               }
           }
           update_option('my_phone_book',$temp);
           wp_die('ok');
       }
    }
}




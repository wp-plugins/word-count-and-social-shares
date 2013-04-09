<?php
/*
Plugin Name: Word Count and Social Shares
Plugin URI: dejanseo.com.au
Description: This Wordpress plugin will report on correlation between word count and social shares. The objective is to determine optimal post length to ensure most social shares.
Version: 0.4
Author: Dejanseo.com.au
*/

/*
 * admin page //////////////////////////////////////////////////////////////////
 */
set_time_limit(0);
@ini_set('display_errors', 0);
// include class
require_once 'includes/social.class.inc';
require_once 'includes/posts.class.inc';
require_once 'includes/cache.class.inc';
require_once 'includes/database.class.inc';

////////////////////////////////////////////////////////////////////////////////


// create menu in wp admin /////////////////////////////////////////////////////
add_action( 'admin_menu', 'social_shares_plugin_menu' );
function social_shares_plugin_menu() {
    add_menu_page("Word Count and Social Shares", "Word Count and Social Shares", 0, "social_shares", "social_shares_main_function",plugins_url('templates/images/sharea.png', __FILE__));
    add_submenu_page("social_shares", "Full Time report", "Full Time report", 0, "social_shares_full_time", "social_shares_full_time");
    add_submenu_page("social_shares", "Download .CSV", "Download .CSV", 0, "social_shares_csv", "social_shares_csv");
    add_submenu_page("social_shares", "Options", "Options", 0, "social_shares_config", "social_shares_config");
    
    
    wp_enqueue_script( 'jquery-ui-core' );
    wp_enqueue_script( 'jquery-ui-accordion' );
    wp_enqueue_script( 'jquery-ui-datepicker');
    wp_enqueue_script('jquery-ui-tabs');
    
    wp_register_script( 'DataTablePlugin', plugins_url('js/jquery.dataTables.min.js', __FILE__) );
    wp_enqueue_script( 'DataTablePlugin' );
    
    wp_register_script( 'GoogleChartPlugin', 'https://www.google.com/jsapi');
    wp_enqueue_script( 'GoogleChartPlugin' );
    
    wp_register_style( 'myPluginStylesheet', plugins_url('css/css.css', __FILE__) );
    wp_enqueue_style( 'myPluginStylesheet' );
}
////////////////////////////////////////////////////////////////////////////////


// main function ///////////////////////////////////////////////////////////////
function social_shares_main_function(){
    
    // include header template
    include("templates/header_tpl.php");
    

    // date range
    if($_POST['date_from']!="" AND $_POST['date_to']!=""){
        $from = $_POST['date_from'].' 00:00:00';
        $to = $_POST['date_to'].' 00:00:00';
    }
    else if($_GET['date_from']!="" AND $_GET['date_to']!=""){
        $from = $_GET['date_from'];
        $to = $_GET['date_to'];
    }
    else {
        // - 1 month
        $today_date = strtotime(date("d-m-Y"));
        $days_ago = strtotime("- 1 month",$today_date);
        $from = date("Y-m-d",$days_ago).' 00:00:00';
        // today
        $to = date("Y-m-d").' 00:00:00';
    }
    
    // date for template
    $from_show = str_replace(" 00:00:00","",$from);
    $to_show = str_replace(" 00:00:00","",$to);
    
    
    
    $date_range_cache_apc_key = md5("$from $to"); // key for cache
    $site_url = get_site_url(); // site full url
    $plus_api = get_option('google_plus_api'); // get google plus api key
    
    // set default api key
    if($plus_api == ""){
        $plus_api = "AIzaSyCKSbrvQasunBoV16zDH9R33D88CeLr9gQ";
    }
    
    // cache - new instance
    $c = new Cache();
    // delete expired cache
    $c->eraseExpired(); 
    
    // new myPosts Object
    $myPosts = new myPosts(10);
    
    
    // if cache exist show it
    if ($c->isCached($date_range_cache_apc_key)) {
        
        $cache_data = $c->retrieve($date_range_cache_apc_key); // get from cache
    
    }
    else {
        // call api class
        
        $show_posts = $myPosts->get_all_posts($from, $to,$site_url,$plus_api);
        $c->store($date_range_cache_apc_key, $show_posts,10800);
        
        $cache_data = $c->retrieve($date_range_cache_apc_key); // get from cache
        
    }
    
    // get chart data
    $total_chart_data = $myPosts->total_chart_data($cache_data);
    // get overview table data
    $overview_table_data = $myPosts->overview($cache_data);
    
    
    // generate HTML for PDF
    $pdf_title = "<h1>Word Count and Social Shares</h1>";
    $pdf_overview_table = $myPosts->PDFtable_overview($overview_table_data);
    $pdf_full_table = $myPosts->PDFtable_full($cache_data);
    
    $form_data = $pdf_title.$pdf_overview_table.$pdf_full_table;
    
    // pdfdownload page url
    //$download_file_url = plugins_url('downloadpdf.php', __FILE__);
    
    

    include("templates/home_tpl.php"); // include template
    
    // download csv
    if($_GET['act']=="csv"){

       $path_csvs = plugin_dir_path(__FILE__).'csvs/';
       $csv_create = $myPosts->SaveCSV($cache_data,$path_csvs,"$from_show-$to_show");
       if($csv_create){
            ?><meta http-equiv="REFRESH" content="0;url=?page=social_shares_csv"><?php
       }
    }


    
}
////////////////////////////////////////////////////////////////////////////////


// show full time report ///////////////////////////////////////////////////////
function social_shares_full_time(){
    
    // include header template
    include("templates/header_tpl.php");
    
    $site_url = get_site_url(); // site full url
    
    $myPosts = new myPosts(10);
    $cache_data = $myPosts->get_all_posts_from_db();
    // get chart data
    $total_chart_data = $myPosts->total_chart_data($cache_data);
    // get overview table data
    $overview_table_data = $myPosts->overview($cache_data);

    // generate HTML for PDF
    $pdf_title = "<h1>Word Count and Social Shares</h1>";
    $pdf_overview_table = $myPosts->PDFtable_overview($overview_table_data);
    $pdf_full_table = $myPosts->PDFtable_full($cache_data);
    
    $form_data = $pdf_title.$pdf_overview_table.$pdf_full_table;
    
    // pdfdownload page url
    //$download_file_url = plugins_url('downloadpdf.php', __FILE__);
    
    // download csv
    if($_GET['act']=="csv"){

       $path_csvs = plugin_dir_path(__FILE__).'csvs/';
       $csv_create = $myPosts->SaveCSV($cache_data,$path_csvs,"fulltime");
       if($csv_create){
            ?><meta http-equiv="REFRESH" content="0;url=?page=social_shares_csv"><?php
       }
    }

    include("templates/home_fulltime_tpl.php"); // include template

}
////////////////////////////////////////////////////////////////////////////////


// full file report cron ///////////////////////////////////////////////////////
add_filter('cron_schedules', 'add_scheduled_interval_5min');
add_filter('cron_schedules', 'add_scheduled_interval_20min');
add_filter('cron_schedules', 'add_scheduled_interval_1hour');
add_filter('cron_schedules', 'add_scheduled_interval_1day');
add_filter('cron_schedules', 'add_scheduled_interval_1week');
 
// add new cron intervals
function add_scheduled_interval_5min($schedules) {
    $schedules['minutes_5'] = array('interval' => 300, 'display' => 'every 5 minutes');
    return $schedules;
}
function add_scheduled_interval_20min($schedules) {
    $schedules['minutes_20'] = array('interval' => 1200, 'display' => 'every 20 minutes');
    return $schedules;
}
function add_scheduled_interval_1hour($schedules) {
    $schedules['hour_1'] = array('interval' => 3600, 'display' => 'every hour');
    return $schedules;
}
function add_scheduled_interval_1day($schedules) {
    $schedules['day_1'] = array('interval' => 86400, 'display' => 'every day');
    return $schedules;
}
function add_scheduled_interval_1week($schedules) {
    $schedules['week_1'] = array('interval' => 604800, 'display' => 'every week'); 
    return $schedules;
}


// 20 min cron
if (!wp_next_scheduled('social_shares_cron_hook_5min')) {
    wp_schedule_event(time(), 'minutes_20', 'social_shares_cron_hook_5min');
}
add_action('social_shares_cron_hook_5min', 'social_shares_cron_5min');


function social_shares_cron_5min() {
    
    // update article database
    $myCron = new myDatabase(0);
    
    // update details
    $plus_api = get_option('google_plus_api'); // get google plus api key
    // set default api key
    if ($plus_api == "") {
        $plus_api = "AIzaSyCKSbrvQasunBoV16zDH9R33D88CeLr9gQ";
    }
    
    
    $myCron->update_social_data($plus_api,null,null);
    
}


// 20 min cron
if (!wp_next_scheduled('social_shares_cron_hook_20min')) {
    wp_schedule_event(time(), 'minutes_20', 'social_shares_cron_hook_20min');
}
add_action('social_shares_cron_hook_20min', 'social_shares_cron_20min');


function social_shares_cron_20min() {
    
    // update article database
    $myCron = new myDatabase(0);
    
    // update details
    $plus_api = get_option('google_plus_api'); // get google plus api key
    // set default api key
    if ($plus_api == "") {
        $plus_api = "AIzaSyCKSbrvQasunBoV16zDH9R33D88CeLr9gQ";
    }
    
    // - 2days
    $today_date = strtotime(date("d-m-Y"));
    $days_ago = strtotime("- 2 days", $today_date);
    
    $from = date("Y-m-d", $days_ago) . ' 00:00:00';
    $to = date("Y-m-d") . ' 00:00:00';
    
    $myCron->update_social_data($plus_api,$from,$to);
    
}

// 1hour cron
if (!wp_next_scheduled('social_shares_cron_hook_1hour')) {
    wp_schedule_event(time(), 'hour_1', 'social_shares_cron_hook_1hour');
}
add_action('social_shares_cron_hook_1hour', 'social_shares_cron_1hour');


function social_shares_cron_1hour() {
    // update article database
    $myCron = new myDatabase(0);
    $myCron->update_article_list();
    
    // update details
    $plus_api = get_option('google_plus_api'); // get google plus api key
    // set default api key
    if ($plus_api == "") {
        $plus_api = "AIzaSyCKSbrvQasunBoV16zDH9R33D88CeLr9gQ";
    }
    
    // - 2days
    $today_date = strtotime(date("d-m-Y"));
    $days_ago_from = strtotime("- 15 days", $today_date);
    $days_ago_to = strtotime("- 2 days", $today_date);
    
    $from = date("Y-m-d", $days_ago_from) . ' 00:00:00';
    $to = date("Y-m-d", $days_ago_to) . ' 00:00:00';
    
    $myCron->update_social_data($plus_api,$from,$to);
    
}

// 1hour cron
if (!wp_next_scheduled('social_shares_cron_hook_1week')) {
    wp_schedule_event(time(), 'week_1', 'social_shares_cron_hook_1week');
}
add_action('social_shares_cron_hook_1week', 'social_shares_cron_1week');


function social_shares_cron_1week() {
    // update article database
    $myCron = new myDatabase(0);
    
    // update details
    $plus_api = get_option('google_plus_api'); // get google plus api key
    // set default api key
    if ($plus_api == "") {
        $plus_api = "AIzaSyCKSbrvQasunBoV16zDH9R33D88CeLr9gQ";
    }
    
    // - 2days
    $today_date = strtotime(date("d-m-Y"));
    $days_ago_from = strtotime("-20 year", $today_date);
    $days_ago_to = strtotime("- 15 days", $today_date);
    
    $from = date("Y-m-d", $days_ago_from) . ' 00:00:00';
    $to = date("Y-m-d", $days_ago_to) . ' 00:00:00';
    
    $myCron->update_social_data($plus_api,$from,$to);
    
}
////////////////////////////////////////////////////////////////////////////////


// show csv files //////////////////////////////////////////////////////////////
function social_shares_csv() {

    // paths
    $path_csvs = plugin_dir_path(__FILE__) . 'csvs/';
    $plugin_url = plugins_url('csvs/', __FILE__);
    
    $FILES = glob($path_csvs . '*.csv');

    foreach ($FILES as $key => $file) {

        if (filemtime($file) > $sec) {
            
            $name_only = substr($file, ( strrpos($file, "\\") + 1));
            $file_name = explode("/csvs/",$name_only);

            $FILE_LIST[$key]['path'] = substr($file, 0, ( strrpos($file, "\\") + 1));
            $FILE_LIST[$key]['name'] = substr($file, ( strrpos($file, "\\") + 1));
            $FILE_LIST[$key]['filename'] = $file_name[1];
            $FILE_LIST[$key]['size'] = filesize($file);
            $FILE_LIST[$key]['date'] = date('Y-m-d G:i:s', filemtime($file));
        }
    }
    if($FILE_LIST){
        include("templates/csv_table_tpl.php");
    }
    else echo "<h2>CSV lists</h2> No data";
    
    if($_GET['act']=="delete"){
        unlink($path_csvs.$_GET['filename']);
        ?><meta http-equiv="REFRESH" content="0;url=?page=social_shares_csv"><?php
    }
}
////////////////////////////////////////////////////////////////////////////////


//config page function /////////////////////////////////////////////////////////
function social_shares_config(){
    // include header template
    include("templates/header_tpl.php");
    $myApi = get_option('google_plus_api');
    ?>
           <h2>Enter Google Plus API:</h2>
            <form method="post" action="?page=social_shares_config&act=save">
                <table class="form-table">
                    <tr>
                        <td>API key: </td>
                        <td><input type="text" class="regular-text" name="api" value="<?=$myApi;?>"></td>
                    </tr>
                </table>
                <input type="submit" value="Save" class="button-primary"> 
                <br>
                <b>If you leave this field empty plugin will use predefined key, but this one have limits.</b>
            </form>  
    <?
    if($_GET['act']==save){
        update_option('google_plus_api', $_POST['api']);
        ?><meta http-equiv="REFRESH" content="0;url=?page=social_shares_config"><?php
    }
}
////////////////////////////////////////////////////////////////////////////////


// create database on plugin activation ////////////////////////////////////////

function social_shares_plugin_activation() {
    
    $my_plugin_version = "0.4";
    global $wpdb;

    // Check if installed
    if (get_option('social_shares_plugin_version') < $my_plugin_version) {
        $my_fb_comments_image_data_table = $wpdb->get_col("SHOW COLUMNS FROM " . $wpdb->prefix."social_shares_report");
        
        if (!$my_fb_comments_image_data_table) {
            
             $table_name = $wpdb->prefix.'social_shares_report';
            
             $sql = "CREATE TABLE $table_name (
                id int(9) NOT NULL AUTO_INCREMENT,
                post_url varchar(255) NOT NULL,
                post_id int(20) NOT NULL,
                post_title varchar(255) NOT NULL,
                post_date varchar(255) NOT NULL,
                post_word_count int(20) NOT NULL,
                fb_comments int(20) NOT NULL,
                fb_likes int(20) NOT NULL,
                fb_shares int(20) NOT NULL,
                tweets int(20) NOT NULL,
                linkedin_shares int(20) NOT NULL,
                google_pluses int(20) NOT NULL,
                total_shares int(20) NOT NULL,
                check_date varchar(255) NOT NULL,
                UNIQUE KEY id (id)
              );";

             require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
             dbDelta($sql);
            
            
        } else {
            //
        }

        update_option('social_shares_plugin_version', $my_plugin_version);
    }
}

register_activation_hook(__FILE__, 'social_shares_plugin_activation');
////////////////////////////////////////////////////////////////////////////////
?>

<?php
/*
Plugin Name: Plugins version info
Plugin URI: http://renefernandez.com/plugins-version-info
Description: This plugin retrieves data from all activated plugins and provides feedback to the admin about them.
Version: 0.9
Textdomain: kyr_plu
Author: Rene Fernandez
Author URI: http://renefernandez.com
License: GPL2 (view readme.txt)
*/

//Plugin initialization
add_action('admin_init', 'kyr_plu_init');
add_action( 'admin_menu', 'kyr_plu_menu' );

define('PLUGIN_PATH',plugin_dir_path(__FILE__));
define('TEXTDOMAIN','kyr_plu');

define('PLUGIN_NAME', 'Plugins version info');

define('VERSION_CAUTION_MESSAGE',__('Current version can not be determined',TEXTDOMAIN));
define('VERSION_DANGER_MESSAGE',__('You are not using the latest version',TEXTDOMAIN));
define('VERSION_SUCCESS_MESSAGE',__('You are using the latest version',TEXTDOMAIN));
define('UNEXPECTED_ERROR',__('An unexpected error has occurred',TEXTDOMAIN));

define('UPDATED_SIX_MONTHS',__('This plugin was updated in the past 6 months.',TEXTDOMAIN));
define('NOT_UPDATED_ONE_YEAR',__('This plugin was not updated in the past 12 months.',TEXTDOMAIN));
define('NOT_UPDATED_TWO_YEARS',__('This plugin was not updated in the past 2 years.',TEXTDOMAIN));
define('NOT_UPDATED_MORE_SIX_MONTHS',__('This plugin was not updated in almost a year.',TEXTDOMAIN));

define('COMPATIBLE_VERSION',__('Installed version is compatible with this version of Wordpress.',TEXTDOMAIN));
define('INCOMPATIBLE_VERSION',__('Installed version is not compatible with this version of Wordpress.',TEXTDOMAIN));

define('EMPTY_STRING_DATE',__('Can not determine last update date.',TEXTDOMAIN));
define('EMPTY_STRING_LAST_VERSION',__('Can not determine last version.',TEXTDOMAIN));
define('EMPTY_STRING_COMPATIBLE_VERSIONS',__('Can not determine compatible versions.',TEXTDOMAIN));

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

if(!class_exists('PluginInfoTable')){
	require_once(PLUGIN_PATH . 'PluginInfoTable.class.php');
}

/*
	Initializes the plugin.
*/
function kyr_plu_init() {
	add_action( 'admin_menu', 'kyr_plu_menu' );
	wp_register_style( 'kyr_plu_css', plugins_url('style.css', __FILE__) );
}

function ky_plu_admin_styles() {
       /*
        * It will be called only on your plugin admin page, enqueue our stylesheet here
        */
       wp_enqueue_style( 'kyr_plu_css' );
   }

/*
	Adds a subpage to the plugins section.
*/
function kyr_plu_menu() {
	$page = add_plugins_page( __(PLUGIN_NAME,TEXTDOMAIN),
					__(PLUGIN_NAME,TEXTDOMAIN),
					'manage_options',
					'kyr_plu',
					'kyr_plu_screen' );
	add_action( 'admin_print_styles-' . $page, 'ky_plu_admin_styles' );
}

/*
	Prints all the data for the plugin subpage.
*/
function kyr_plu_screen() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.',TEXTDOMAIN ) );
	}
	
	//Create an instance of our package class...
    $testListTable = new PluginInfoTable();
    //Fetch, prepare, sort, and filter our data...
    $testListTable->prepare_items();
	
	?>

	<div class="wrap">
		<div id="icon-plugins" class="icon32"><br></div>
		<h2><?php echo __(PLUGIN_NAME,TEXTDOMAIN); ?></h2>
		<p><?php echo __('This plugin displays some information about the currently activated plugins to compliment the info in the plugin screen such as:', TEXTDOMAIN); ?></p>
			<ul class="list">
				<li><?php echo __('Current intalled version of the plugin.', TEXTDOMAIN); ?></li>
				<li><?php echo __('Last version of the plugin available in the Wordpress.org repository.', TEXTDOMAIN); ?></li>
				<li><?php echo __('When was the plugin last updated.', TEXTDOMAIN); ?></li>
				<li><?php echo __('If the plugin is compatible with the Wordpress version you are using.', TEXTDOMAIN); ?></li>
			</ul>
        
        <form id="movies-filter" method="get">
        
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            
            <?php $testListTable->display() ?>
        </form>
        
    </div>
		
<?php }


/*
Gets the data of the plugin from the Wordpress API using a POST request.
More info can be found: http://dd32.id.au/projects/wordpressorg-plugin-information-api-docs/

Thanks to http://wp.tutsplus.com/tutorials/creative-coding/interacting-with-wordpress-plug-in-theme-api/ for the info.
*/
function getPluginData($args){
	
	//$args data example
	/*$args = array(  
    	'slug' => 'AddQuicktag',  
    	'fields' => array( 'last_updated' => true )  
    );*/
	
	$request = array(  
        'body' => array(
            'action' => 'plugin_information', 
            'request' => serialize((object)$args) 
        )
    ); 
    
    // Generate a cache key that would hold the response for this request: 
    $key='kyr_plu_'.md5(serialize($request)); 
 
    // Check transient. If it's there - use that, if not re fetch the plugin data  
    if ( false === ($plugin = get_transient($key)) ) {  
  
        // Theme not found - we need to re-fetch it  
        $response = wp_remote_post('http://api.wordpress.org/plugins/info/1.0/',$request);  
  
        if ( is_wp_error($response) )  
            return $response;  
  
        $plugin = unserialize(wp_remote_retrieve_body($response));  
  
        if ( !is_object($plugin) && !is_array($plugin) )  
            return new WP_Error('plugin_api_error', __('An unexpected error has occurred', TEXTDOMAIN));  
        
        // Set transient for next time... keep it for 24 hours should be good  
        set_transient($key, $plugin, 60*60*24); 
	}
	
	return $plugin;
}

/*
Gets the current active plugins.
*/
function kyr_plu_active_site_plugins() {
    $activePugins = get_option('active_plugins'); 
    
    return $activePugins;
}

/*
	Prints a message if the last version is being used.
*/
function usingLatestVersion($currentVersion,$latestVersion){
	
	if(is_wp_error($latestVersion))
		return '<div class="caution-message">'. $latestVersion->get_error_message() .'</div>';
	
	if(strcmp($latestVersion,"")==0 || strcmp($currentVersion,"")==0)
		return '<div class="caution-message">'. EMPTY_STRING_LAST_VERSION .'</div>';
		
	
	if(strcmp($currentVersion, $latestVersion)==0)		
		return '<div class="success-message">'. VERSION_SUCCESS_MESSAGE .'</div>';

	
	return '<div class="danger-message">'. VERSION_DANGER_MESSAGE .'</div>';
}

/*
	Prints a different message depending of the last time the plugin was updated.
*/
function whenWasLastUpdated($lastUpdated){
	
	if(is_wp_error($lastUpdated))
		return '<div class="caution-message">'. $lastUpdated->get_error_message() .'</div>';
	
	if(strcmp($lastUpdated,"")==0)
		return '<div class="caution-message">'. EMPTY_STRING_DATE .'</div>';

	//Was updated in the past 6 months?
	if((time()-(60*60*24*30*6)) < strtotime($lastUpdated))
		return '<div class="success-message">'.UPDATED_SIX_MONTHS.'</div>';
	
	//Was updated in the past 12 months?
	if((time()-(60*60*24*30*6)) > strtotime($lastUpdated) && ((time()-(60*60*24*30*12)) < strtotime($lastUpdated)))
		return '<div class="caution-message">'. NOT_UPDATED_MORE_SIX_MONTHS .'</div>';
	
	//Was updated in the past 12 months?
	if((time()-(60*60*24*30*12)) > strtotime($lastUpdated))
		return '<div class="caution-message">'. NOT_UPDATED_ONE_YEAR .'</div>';
	
	//Was updated in the past 2 years?
	if((time()-(60*60*24*30*12*2)) > strtotime($lastUpdated))
		return '<div class="danger-message">'.NOT_UPDATED_TWO_YEARS.'</div>';
		
}

/*
	Prints a message for the compatible versions column.
*/
function isCurrentVersionCompatible($currentVersion, $compatible_versions){
	
	if(is_wp_error($compatible_versions))
		return '<div class="caution-message">'. $compatible_versions->get_error_message() .'</div>';
	
	if(strcmp($compatible_versions,"")==0 || empty($compatible_versions))
		return '<div class="caution-message">'. EMPTY_STRING_COMPATIBLE_VERSIONS .'</div>';
	
	if(is_array($compatible_versions))
		if(in_array($currentVersion, $compatible_versions))
			return '<div class="success-message">'.COMPATIBLE_VERSION.'</div>';
			
		return '<div class="danger-message">'.INCOMPATIBLE_VERSION.'</div>';
			
}

/*
	Prints a message if the Latest Version is not set.
*/
function undefinedLatestVersionMessage($latestVersion){
	
	if(is_wp_error($latestVersion))
		return '<div class="caution-message">'. $latestVersion->get_error_message() .'</div>';
	
	if(strcmp($latestVersion,"")==0)
		return '<div class="caution-message">'. EMPTY_STRING_LAST_VERSION .'</div>';
			
}


?>
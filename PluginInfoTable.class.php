<?php
/*
	Thanks to: http://wordpress.org/extend/plugins/custom-list-table-example/
*/
class PluginInfoTable extends WP_List_Table {
    
       
    /** ************************************************************************
     * REQUIRED. Set up a constructor that references the parent constructor. We 
     * use the parent reference to set some default configs.
     ***************************************************************************/
    function __construct(){
        global $status, $page;
                
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'plugin',     //singular name of the listed records
            'plural'    => 'plugins',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
        
    }
    
    
    /** ************************************************************************
     * Recommended. This method is called when the parent class can't find a method
     * specifically build for a given column. Generally, it's recommended to include
     * one method for each column you want to render, keeping your package class
     * neat and organized. For example, if the class needs to process a column
     * named 'title', it would first see if a method named $this->column_title() 
     * exists - if it does, that method will be used. If it doesn't, this one will
     * be used. Generally, you should try to use custom column methods as much as 
     * possible. 
     * 
     * Since we have defined a column_title() method later on, this method doesn't
     * need to concern itself with any column with a name of 'title'. Instead, it
     * needs to handle everything else.
     * 
     * For more detailed insight into how columns are handled, take a look at 
     * WP_List_Table::single_row_columns()
     * 
     * @param array $item A singular item (one full row's worth of data)
     * @param array $column_name The name/slug of the column to be processed
     * @return string Text or HTML to be placed inside the column <td>
     **************************************************************************/
    function column_default($item, $column_name){
        switch($column_name){
            case 'name':
            case 'installed_version':
            case 'latest_version':
            case 'latest_version_date':
                return $item[$column_name];
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }
    
        
    /** ************************************************************************
     * Recommended. This is a custom column method and is responsible for what
     * is rendered in any column with a name/slug of 'title'. Every time the class
     * needs to render a column, it first looks for a method named 
     * column_{$column_title} - if it exists, that method is run. If it doesn't
     * exist, column_default() is called instead.
     * 
     * This example also illustrates how to implement rollover actions. Actions
     * should be an associative array formatted as 'slug'=>'link html' - and you
     * will need to generate the URLs yourself. You could even ensure the links
     * 
     * 
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
    function column_name($item){
        
        //Build row actions
        /*$actions = array(
            'edit'      => sprintf('<a href="?page=%s&action=%s&movie=%s">Edit</a>',$_REQUEST['page'],'edit',$item['ID']),
            'delete'    => sprintf('<a href="?page=%s&action=%s&movie=%s">Delete</a>',$_REQUEST['page'],'delete',$item['ID']),
        );*/
        $authorProfileText = __('Author profile', TEXTDOMAIN);
        
        if($item['author_profile']){
	        $authorProfileLink = sprintf('<a href="%1$s" title="%2$s">%3$s</a>',
	        		$item['author_profile'],
            		$authorProfileText,
            		$item['author']);
        }else{
	        $authorProfileLink = sprintf('%1$s',
	        		$item['author']);
        }
        
        //Return the title contents
        return sprintf('%1$s <br/><span style="color:silver">%2$s</span>',
            /*$1%s*/ $item['name'],
            		$authorProfileLink
        );
    }
    
    function column_installed_version($item){
        
        //Build row actions
        $message = usingLatestVersion($item['installed_version'], $item['latest_version']);
        //Return the title contents
        return sprintf('%1$s %2$s',
            /*$1%s*/ $item['installed_version'],
            		$message
        );
    }
    
     function column_latest_version($item){
        
        //Build row actions
        if(is_wp_error($item['latest_version']) || strcmp($item['latest_version'], "")==0){
        	$version = undefinedLatestVersionMessage($item['latest_version']);
        }else{
	        $version = $item['latest_version'];
        }
        
        //Return the title contents
        return sprintf('%1$s',
            /*$1%s*/ $version
        );
    }
    
    function column_latest_version_date($item){
        
        //Build row actions
        $message = whenWasLastUpdated($item['latest_version_date']);
        
        if(is_wp_error($item['latest_version_date'])){
        	$date = "";
        }else{
	        $date = $item['latest_version_date'];
        }
        
        //Return the title contents
        return sprintf('%1$s %2$s',
            /*$1%s*/ $date,
            		$message
        );
    }
    
    function column_compatible_versions($item){
        
        $versionsText="";
        //Build row actions
       	$message = isCurrentVersionCompatible($item['installed_version'],$item['compatible_versions']);
        
        if(isset($item['compatible_versions'])){
        	
        	if(is_wp_error($item['compatible_versions'])){
	       		$versionsText = "";
	        }else{
        	
	        	$count=0;
	        	if(is_array($item['compatible_versions'])){
			        foreach ($item['compatible_versions'] as $version){
				        $versionsText.= $version;
				        
				        if($count!=(count($item['compatible_versions'])-1)){
					        $versionsText.= ' - ';			        
				        }
				        
				        $count++;
			        }
			    }else{
			    	$versionsText= $item['compatible_versions'];
			    }
			}
	    }
        
        return sprintf('%1$s %2$s',
            /*$1%s*/ $versionsText,
            		$message
        );
        
        //Return the title contents
        //return sprintf('%1$s',
            /*$1%s*/ //$item['compatible_versions']
        //);
    }

    
    /** ************************************************************************
     * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
     * is given special treatment when columns are processed. It ALWAYS needs to
     * have it's own method.
     * 
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/ $item['ID']                //The value of the checkbox should be the record's id
        );
    }
    
    
    /** ************************************************************************
     * REQUIRED! This method dictates the table's columns and titles. This should
     * return an array where the key is the column slug (and class) and the value 
     * is the column's title text. If you need a checkbox for bulk actions, refer
     * to the $columns array below.
     * 
     * The 'cb' column is treated differently than the rest. If including a checkbox
     * column in your table you must create a column_cb() method. If you don't need
     * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
     * 
     * @see WP_List_Table::::single_row_columns()
     * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_columns(){
        $columns = array(
            //'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
            'name'     => __('Name',TEXTDOMAIN),
            'installed_version'    => __('Installed version',TEXTDOMAIN),
            'latest_version'  => __('Latest version available',TEXTDOMAIN),
            'latest_version_date'  => __('Latest version date',TEXTDOMAIN),
            'compatible_versions'  => __('Compatible versions',TEXTDOMAIN)
        );
        return $columns;
    }
    
    /** ************************************************************************
     * Optional. If you want one or more columns to be sortable (ASC/DESC toggle), 
     * you will need to register it here. This should return an array where the 
     * key is the column that needs to be sortable, and the value is db column to 
     * sort by. Often, the key and value will be the same, but this is not always
     * the case (as the value is a column name from the database, not the list table).
     * 
     * This method merely defines which columns should be sortable and makes them
     * clickable - it does not handle the actual sorting. You still need to detect
     * the ORDERBY and ORDER querystring variables within prepare_items() and sort
     * your data accordingly (usually by modifying your query).
     * 
     * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
     **************************************************************************/
    function get_sortable_columns() {
        $sortable_columns = array(
            'name'     => array('name',true),     //true means it's already sorted
            'installed_version'    => array('installed_version',false)
        );
        return $sortable_columns;
    }
    
    
    /** ************************************************************************
     * Optional. If you need to include bulk actions in your list table, this is
     * the place to define them. Bulk actions are an associative array in the format
     * 'slug'=>'Visible Title'
     * 
     * If this method returns an empty value, no bulk action will be rendered. If
     * you specify any bulk actions, the bulk actions box will be rendered with
     * the table automatically on display().
     * 
     * Also note that list tables are not automatically wrapped in <form> elements,
     * so you will need to create those manually in order for bulk actions to function.
     * 
     * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
     **************************************************************************/
    /*function get_bulk_actions() {
        $actions = array(
            'delete'    => 'Delete'
        );
        return $actions;
    }*/
    
    
    /** ************************************************************************
     * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
     * For this example package, we will handle it in the class to keep things
     * clean and organized.
     * 
     * @see $this->prepare_items()
     **************************************************************************/
    /*function process_bulk_action() {
        
        //Detect when a bulk action is being triggered...
        if( 'delete'===$this->current_action() ) {
            wp_die('Items deleted (or they would be if we had items to delete)!');
        }
        
    }*/
    
    
    /** ************************************************************************
     * REQUIRED! This is where you prepare your data for display. This method will
     * usually be used to query the database, sort and filter the data, and generally
     * get it ready to be displayed. At a minimum, we should set $this->items and
     * $this->set_pagination_args(), although the following properties and methods
     * are frequently interacted with here...
     * 
     * @global WPDB $wpdb
     * @uses $this->_column_headers
     * @uses $this->items
     * @uses $this->get_columns()
     * @uses $this->get_sortable_columns()
     * @uses $this->get_pagenum()
     * @uses $this->set_pagination_args()
     **************************************************************************/
    function prepare_items() {
        global $wpdb; //This is used only if making any database queries

        /**
         * First, lets decide how many records per page to show
         */
        $per_page = 10;
        
        
        /**
         * REQUIRED. Now we need to define our column headers. This includes a complete
         * array of columns to be displayed (slugs & titles), a list of columns
         * to keep hidden, and a list of columns that are sortable. Each of these
         * can be defined in another method (as we've done here) before being
         * used to build the value for our _column_headers property.
         */
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        
        /**
         * REQUIRED. Finally, we build an array to be used by the class for column 
         * headers. The $this->_column_headers property takes an array which contains
         * 3 other arrays. One for all columns, one for hidden columns, and one
         * for sortable columns.
         */
        $this->_column_headers = array($columns, $hidden, $sortable);
        
        
        /**
         * Optional. You can handle your bulk actions however you see fit. In this
         * case, we'll handle them within our package just to keep things clean.
         */
        //$this->process_bulk_action();
        
        
        /**
         * Instead of querying a database, we're going to fetch the example data
         * property we created for use in this plugin. This makes this example 
         * package slightly different than one you might build on your own. In 
         * this example, we'll be using array manipulation to sort and paginate 
         * our data. In a real-world implementation, you will probably want to 
         * use sort and pagination data to build a custom query instead, as you'll
         * be able to use your precisely-queried data immediately.
         */
        //$data = $this->example_data;
        
        $plugins = get_plugins();
		$activePlugins = kyr_plu_active_site_plugins();
		$totalPlugins= count($activePlugins);
		
		//print_r($activePlugins);
		
		$items = array();
			
		$idCount = 1;
		while($plugin = current($plugins)){ 
			
			$currentPlugin= array();
			
			//print_r($plugin);
			
			$currentPlugin['ID'] = $idCount;
			$currentPlugin['name']=$plugin["Name"];
			$currentPlugin['author']=$plugin["Author"];
			$currentPlugin['installed_version']=$plugin["Version"];
			
			
			if (in_array(key($plugins), $activePlugins)) {
				
				$arr = explode("/", key($plugins), 2);
				$first = $arr[0];
				
				if(!empty($first)){
					
					$args = array(  
				    	'slug' => $first,  
				    	'fields' => array( 'last_updated' => true)  
				    );
					
					// Get the plugin from the repo  
					$pluginInfo = getPluginData($args);  
					
					// Display theme information (or error message) plugin could not be found.  
					if ( is_wp_error($pluginInfo) ) {
						
						//$error_string = $pluginInfo->get_error_message();
						$currentPlugin['latest_version'] = $pluginInfo;
						$currentPlugin['latest_version_date'] = $pluginInfo;
						$currentPlugin['compatible_versions'] = $pluginInfo;
						//ERROR
						//$currentPlugin['latest_version']="Can't know";
						//$currentPlugin['latest_version_date']="Can't know";
					
					}else{
						
						$currentPlugin['author_profile']=esc_url($pluginInfo->author_profile);
						$currentPlugin['latest_version'] = esc_attr($pluginInfo->version);
						$currentPlugin['latest_version_date'] = esc_attr($pluginInfo->last_updated);
						
						
						$compatibility = $pluginInfo->compatibility;
	
						$count=0;
						$totalVersions= count($compatibility[get_bloginfo('version')]);
						$compatible_versions = array();
						
						if($totalVersions>0){
							while($count<$totalVersions){
							
							$wp_ver = current($compatibility[get_bloginfo('version')]);
							
							$compatible_versions[] = key($compatibility[get_bloginfo('version')]);
							
							
							
								$count++;
								next($compatibility[get_bloginfo('version')]);
							}
							
							$currentPlugin['compatible_versions']=$compatible_versions;
							
						}else{
							
							$compatible_versions[]="Not enough data.";
						}
						
					}
				}
				
				$items[] = $currentPlugin;
				$idCount++;
			}
			
			/*$currentPlugin['securityLevel']=0;	//current plugin security level
			$currentPlugin['compatible_versions'] = array(); //versions of the plugin compatibles with the Wordpress installed version
			
			$currentPlugin['installedV']="";		// Installed version points label
			$currentPlugin['latestV']="";		//Latest version points label
			$currentPlugin['compatibleV']="";	//Compatible version points lavel
			*/
			
			
			
			next($plugins);
		}

        $data = $items;
        
        /**
         * This checks for sorting input and sorts the data in our array accordingly.
         * 
         * In a real-world situation involving a database, you would probably want 
         * to handle sorting by passing the 'orderby' and 'order' values directly 
         * to a custom query. The returned data will be pre-sorted, and this array
         * sorting technique would be unnecessary.
         */
        function usort_reorder($a,$b){
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'title'; //If no sort, default to title
            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
            $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
            return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
        }
        usort($data, 'usort_reorder');
        
        
        /***********************************************************************
         * ---------------------------------------------------------------------
         * vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv
         * 
         * In a real-world situation, this is where you would place your query.
         * 
         * ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
         * ---------------------------------------------------------------------
         **********************************************************************/
        
                
        /**
         * REQUIRED for pagination. Let's figure out what page the user is currently 
         * looking at. We'll need this later, so you should always include it in 
         * your own package classes.
         */
        $current_page = $this->get_pagenum();
        
        /**
         * REQUIRED for pagination. Let's check how many items are in our data array. 
         * In real-world use, this would be the total number of items in your database, 
         * without filtering. We'll need this later, so you should always include it 
         * in your own package classes.
         */
        $total_items = count($data);
        
        
        /**
         * The WP_List_Table class does not handle pagination for us, so we need
         * to ensure that the data is trimmed to only the current page. We can use
         * array_slice() to 
         */
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);
        
        
        
        /**
         * REQUIRED. Now we can add our *sorted* data to the items property, where 
         * it can be used by the rest of the class.
         */
        $this->items = $data;
        
        
        /**
         * REQUIRED. We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }
    
}
?>
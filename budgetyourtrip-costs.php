<?php
/*
Plugin Name: Average Travel Costs
Description: Displays the average daily travel costs of a specific city or country with data from real travelers.
Version: 1.1
Author: Budget Your Trip
Author URI: https://www.budgetyourtrip.com
License: GPL2
*/


// The sidebar widget for the city widget
class BYT_Travel_Cost_Widget_City extends WP_Widget {

	// Main constructor
	public function __construct()
	{
		parent::__construct(
			'byt_travel_cost_widget_city',
			__( 'Average Travel Costs (City)', 'text_domain' ),
			array(
				'customize_selective_refresh' => true,
				'description' => __('Displays average daily travel costs for a selected country.', 'text_domain'),
			)
		);
	}

	// The widget form (for the backend )
	public function form( $instance )
	{	
		// Set widget defaults
		$defaults = array(
			'cityname'		=> '',
			'widgeturl' 	=> '',
			'locationurl' 		=> '',
			'hidecategories'	=> '',
			'defaultcurrency'	=> ''
		);
		
		// Parse current settings with defaults
		extract( wp_parse_args( ( array ) $instance, $defaults ) ); ?>

		<p>
			Type the first few letters of the city, and then select the city name from the dropdown.
		</p>
		
		<?php // City Name ?>
		<p id="byt-widget-field-container-city">
			<label for="<?php echo esc_attr( $this->get_field_id( 'cityname' ) ); ?>"><?php _e( 'City Name', 'text_domain' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'cityname' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'cityname' ) ); ?>" type="text" value="<?php echo esc_attr( $cityname ); ?>" />
			
		</p>
		<p>
			<input id="<?php echo esc_attr( $this->get_field_id( 'hidecategories' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'hidecategories' ) ); ?>" type="checkbox" value="1" <?php checked( '1', $hidecategories ); ?> />
			<label for="<?php echo esc_attr( $this->get_field_id( 'hidecategories' ) ); ?>"><?php _e( 'Hide Categories', 'text_domain' ); ?></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'defaultcurrency' ); ?>"><?php _e( 'Default Currency', 'text_domain' ); ?></label>
			<select name="<?php echo $this->get_field_name( 'defaultcurrency' ); ?>" id="<?php echo $this->get_field_id( 'defaultcurrency' ); ?>" class="widefat">
			<?php
			// Your options array
			$currency_options = array(
				''        => __( 'Local Currency', 'text_domain' ),
				'USD' => __( 'Dollar (United States)', 'text_domain' ),
				'EUR' => __( 'Euro', 'text_domain' ),
				'GBP' => __( 'Pound Sterling (UK)', 'text_domain' ),
				'AUD' => __( 'Dollar (Australia)', 'text_domain' ),
			);

			// Loop through options and add each one to the select dropdown
			foreach ( $currency_options as $key => $name ) {
				echo '<option value="' . esc_attr( $key ) . '" id="' . esc_attr( $key ) . '" '. selected( $defaultcurrency, $key, false ) . '>'. $name . '</option>';

			} ?>
			</select>
		</p>

		<?php // widgeturl ?>
		<input class="byt-widget-widgeturl" id="<?php echo esc_attr( $this->get_field_id( 'widgeturl' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'widgeturl' ) ); ?>" type="hidden" value="<?php echo esc_attr( $widgeturl ); ?>" />
		<input class="byt-widget-locationurl" id="<?php echo esc_attr( $this->get_field_id( 'locationurl' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'locationurl' ) ); ?>" type="hidden" value="<?php echo esc_attr( $locationurl ); ?>" />
		<?
	}

	// Update widget settings
	public function update( $new_instance, $old_instance )
	{
		$instance = $old_instance;
		
		$instance['cityname']    = isset( $new_instance['cityname'] ) ? wp_strip_all_tags( $new_instance['cityname'] ) : '';
		$instance['widgeturl']    = isset( $new_instance['widgeturl'] ) ? wp_strip_all_tags( $new_instance['widgeturl'] ) : '';
		$instance['locationurl']    = isset( $new_instance['locationurl'] ) ? wp_strip_all_tags( $new_instance['locationurl'] ) : '';
		$instance['hidecategories'] = isset( $new_instance['hidecategories'] ) ? 1 : false;
		$instance['defaultcurrency']   = isset( $new_instance['defaultcurrency'] ) ? wp_strip_all_tags( $new_instance['defaultcurrency'] ) : '';
		return $instance;
	}

	// Display the widget
	public function widget( $args, $instance )
	{
		extract( $args );

		// Check the widget options
		$cityname    = isset( $instance['cityname'] ) ? $instance['cityname'] : '';
		$widgeturl    = isset( $instance['widgeturl'] ) ? $instance['widgeturl'] : '';
		$locationurl    = isset( $instance['locationurl'] ) ? $instance['locationurl'] : '';
		$defaultcurrency   = isset( $instance['defaultcurrency'] ) ? $instance['defaultcurrency'] : '';
		$hidecategories = ! empty( $instance['hidecategories'] ) ? $instance['hidecategories'] : false;


		// WordPress core before_widget hook (always include )
		echo $before_widget;

		// Display the widget
		echo '<div class="widget-text wp_widget_plugin_box">';


			// Display iframe widget from budgetyourtrip.com 
			if ( $widgeturl && $widgeturl != "" )
			{
				$hidecatparam = "";
				$defaultcurparam = "";
				if($hidecategories)
				{
					$hidecatparam = "&hidecategories=1";
				}
				if($defaultcurrency)
				{
					$defaultcurparam = "&defaultcurrency=".$defaultcurrency;
				}
				?><script async src="<?php print $widgeturl . $hidecatparam . $defaultcurparam; ?>" type="text/javascript"></script><?php
				
				if($locationurl && $locationurl != "")
				{
					?><a href="<?php echo $locationurl; ?>" target="_blank" rel="noopener" class="budgetyourtrip-logo-pushdown">
					<?php
					if($cityname && $cityname != "")
					{
						echo $cityname;
					}
					?> Travel Prices</a><?php
				}
			}


		echo '</div>';

		// WordPress core after_widget hook (always include )
		echo $after_widget;
	}

}

// Register the sidebar widget
function byt_register_travel_cost_sidebar_widget_city() {
	register_widget( 'BYT_Travel_Cost_Widget_City' );
}
add_action( 'widgets_init', 'byt_register_travel_cost_sidebar_widget_city' );




// The sidebar widget for the city widget
class BYT_Travel_Cost_Widget_Country extends WP_Widget {

	// Main constructor
	public function __construct()
	{
		parent::__construct(
			'byt_travel_cost_widget_country',
			__( 'Average Travel Costs (Country)', 'text_domain' ),
			array(
				'customize_selective_refresh' => true,
				'description' => __('Displays average daily travel costs for a selected country.', 'text_domain'),
			)
		);
	}

	// The widget form (for the backend )
	public function form( $instance )
	{	
		// Set widget defaults
		$defaults = array(
			'countryname'		=> '',
			'widgeturl' 	=> '',
			'locationurl' 		=> '',
			'hidecategories'	=> '',
			'defaultcurrency'	=> ''
		);
		
		// Parse current settings with defaults
		extract( wp_parse_args( ( array ) $instance, $defaults ) ); ?>

		<p>
			Type the first few letters of the country, and then select the country name from the dropdown.
		</p>
		
		<?php // Country Name ?>
		<p id="byt-widget-field-container-country">
			<label for="<?php echo esc_attr( $this->get_field_id( 'countryname' ) ); ?>"><?php _e( 'Country Name', 'text_domain' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'countryname' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'countryname' ) ); ?>" type="text" value="<?php echo esc_attr( $countryname ); ?>" />
		</p>
		<p>
			<input id="<?php echo esc_attr( $this->get_field_id( 'hidecategories' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'hidecategories' ) ); ?>" type="checkbox" value="1" <?php checked( '1', $hidecategories ); ?> />
			<label for="<?php echo esc_attr( $this->get_field_id( 'hidecategories' ) ); ?>"><?php _e( 'Hide Categories', 'text_domain' ); ?></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'defaultcurrency' ); ?>"><?php _e( 'Default Currency', 'text_domain' ); ?></label>
			<select name="<?php echo $this->get_field_name( 'defaultcurrency' ); ?>" id="<?php echo $this->get_field_id( 'defaultcurrency' ); ?>" class="widefat">
			<?php
			// Your options array
			$currency_options = array(
				''        => __( 'Local Currency', 'text_domain' ),
				'USD' => __( 'Dollar (United States)', 'text_domain' ),
				'EUR' => __( 'Euro', 'text_domain' ),
				'GBP' => __( 'Pound Sterling (UK)', 'text_domain' ),
				'AUD' => __( 'Dollar (Australia)', 'text_domain' ),
			);

			// Loop through options and add each one to the select dropdown
			foreach ( $currency_options as $key => $name ) {
				echo '<option value="' . esc_attr( $key ) . '" id="' . esc_attr( $key ) . '" '. selected( $defaultcurrency, $key, false ) . '>'. $name . '</option>';

			} ?>
			</select>
		</p>

		<?php // widgeturl ?>
		<input class="byt-widget-widgeturl" id="<?php echo esc_attr( $this->get_field_id( 'widgeturl' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'widgeturl' ) ); ?>" type="hidden" value="<?php echo esc_attr( $widgeturl ); ?>" />
		<input class="byt-widget-locationurl" id="<?php echo esc_attr( $this->get_field_id( 'locationurl' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'locationurl' ) ); ?>" type="hidden" value="<?php echo esc_attr( $locationurl ); ?>" />
		<?
	}

	// Update widget settings
	public function update( $new_instance, $old_instance )
	{
		$instance = $old_instance;
		
		$instance['countryname']    = isset( $new_instance['countryname'] ) ? wp_strip_all_tags( $new_instance['countryname'] ) : '';
		$instance['widgeturl']    = isset( $new_instance['widgeturl'] ) ? wp_strip_all_tags( $new_instance['widgeturl'] ) : '';
		$instance['locationurl']    = isset( $new_instance['locationurl'] ) ? wp_strip_all_tags( $new_instance['locationurl'] ) : '';
		$instance['hidecategories'] = isset( $new_instance['hidecategories'] ) ? 1 : false;
		$instance['defaultcurrency']   = isset( $new_instance['defaultcurrency'] ) ? wp_strip_all_tags( $new_instance['defaultcurrency'] ) : '';
		return $instance;
	}

	// Display the widget
	public function widget( $args, $instance )
	{
		extract( $args );

		// Check the widget options
		$countryname    = isset( $instance['countryname'] ) ? $instance['countryname'] : '';
		$widgeturl    = isset( $instance['widgeturl'] ) ? $instance['widgeturl'] : '';
		$locationurl    = isset( $instance['locationurl'] ) ? $instance['locationurl'] : '';
		$defaultcurrency   = isset( $instance['defaultcurrency'] ) ? $instance['defaultcurrency'] : '';
		$hidecategories = ! empty( $instance['hidecategories'] ) ? $instance['hidecategories'] : false;


		// WordPress core before_widget hook (always include )
		echo $before_widget;

		// Display the widget
		echo '<div class="widget-text wp_widget_plugin_box">';


			// Display iframe widget from budgetyourtrip.com 
			if ( $widgeturl && $widgeturl != "" )
			{
				$hidecatparam = "";
				$defaultcurparam = "";
				if($hidecategories)
				{
					$hidecatparam = "&hidecategories=1";
				}
				if($defaultcurrency)
				{
					$defaultcurparam = "&defaultcurrency=".$defaultcurrency;
				}
				?><script async src="<?php print $widgeturl . $hidecatparam . $defaultcurparam; ?>" type="text/javascript"></script><?php
				
				if($locationurl && $locationurl != "")
				{
					?><a href="<?php echo $locationurl; ?>" target="_blank" rel="noopener" class="budgetyourtrip-logo-pushdown">
					<?php
					if($countryname && $countryname != "")
					{
						echo $countryname;
					}
					?> Travel Prices</a><?php
				}
			}


		echo '</div>';

		// WordPress core after_widget hook (always include )
		echo $after_widget;
	}

}

// Register the sidebar widget
function byt_register_travel_cost_sidebar_widget_countryname() {
	register_widget( 'BYT_Travel_Cost_Widget_Country' );
}
add_action( 'widgets_init', 'byt_register_travel_cost_sidebar_widget_countryname' );


// load javascript files
function byt_load_js()
{
	wp_enqueue_style('budgetyourtrip-costs-autocomplete.css', plugins_url( 'js/jquery.auto-complete.css', __FILE__ ) );
	wp_enqueue_script( 'budgetyourtrip-costs-autocomplete', plugins_url( 'js/jquery.auto-complete.min.js', __FILE__ ) );
	wp_enqueue_script( 'budgetyourtrip-costs-custom-js', plugins_url( 'js/bytplugin.js', __FILE__ ) );
}
add_action( 'admin_enqueue_scripts', 'byt_load_js' );

// create ajax city search function
add_action( 'wp_ajax_byt_ajax_city_search', function()
{
	// $s is the search term
	$s = wp_unslash( $_GET['q'] );

	
	$path = plugins_url( 'data/cities.csv', __FILE__ );

	$datafile = fopen($path, 'r');
	$count = 0;
	while (($line = fgetcsv($datafile)) !== FALSE)
	{
		//$line is an array of the csv elements
		if(stripos($line[1], $s) !== FALSE)
		{
			$results[] = $line;
			$count++;
		}
		if($count >= 10)
		{
			break;
		}
	}
	fclose($datafile);

	echo json_encode( $results );

	wp_die();
});

// create ajax list of countries function
add_action( 'wp_ajax_byt_ajax_country_list', function()
{

	$path = plugins_url( 'data/countries.csv', __FILE__ );

	$datafile = fopen($path, 'r');
	while (($line = fgetcsv($datafile)) !== FALSE)
	{
		$results[] = $line;
	}
	fclose($datafile);

	echo json_encode( $results );

	wp_die();
});

// create ajax country search function
add_action( 'wp_ajax_byt_ajax_country_search', function()
{
	// $s is the search term
	$s = wp_unslash( $_GET['q'] );

	
	$path = plugins_url( 'data/countries.csv', __FILE__ );

	$datafile = fopen($path, 'r');
	while (($line = fgetcsv($datafile)) !== FALSE)
	{
		//$line is an array of the csv elements
		if(stripos($line[1], $s) !== FALSE)
		{
			$results[] = $line;
		}
	}
	fclose($datafile);

	echo json_encode( $results );

	wp_die();
});




function byt_enqueue_costs_block_editor_assets() {
	// Scripts.
	wp_enqueue_script(
		'budgetyourtrip-costs-block-script', // Handle.
		plugin_dir_url( __FILE__ ) . 'block/block.js', // File.
		array( 'wp-blocks', 'wp-i18n', 'wp-element' ), // Dependencies.
		filemtime( plugin_dir_path( __FILE__ ) . 'block/block.js' ) // filemtime Gets file modification time.
	);
	
	wp_enqueue_style(
		'budgetyourtrip-costs-block-css', // Handle.
		plugin_dir_url( __FILE__ ) . 'block/editor.css', // Block editor CSS.
		array( 'wp-edit-blocks' ), // Dependency to include the CSS after it.
		filemtime( plugin_dir_path( __FILE__ ) . 'block/editor.css' )
	);

}
add_action( 'enqueue_block_editor_assets', 'byt_enqueue_costs_block_editor_assets' );

function byt_enqueue_front_end_block_scripts()
{
	wp_enqueue_style(
		'budgetyourtrip-costs-frontend-css', // Handle.
		plugin_dir_url( __FILE__ ) . 'block/front.css'
	);
}
add_action( 'wp_enqueue_scripts', 'byt_enqueue_front_end_block_scripts' );

function byt_block_categories( $categories, $post ) {
    if ( $post->post_type !== 'post' ) {
        return $categories;
    }
    return array_merge(
        $categories,
        array(
            array(
                'slug' => 'budget-travel',
                'title' => __( 'Budget Travel', 'budgetyourtrip-costs' )
                
            ),
        )
    );
}
add_filter( 'block_categories', 'byt_block_categories', 10, 2 );

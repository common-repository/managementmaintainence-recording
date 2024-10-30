<?php
/**

Plugin Name: Management & Maintenance Recording
Plugin URI: 
Description: This plugin add a dashboard widget to record admin activity on website. Simple to remember it
Author: Luca De Cristofano
Version: 0.5
Author URI: http://www.mesolutions.it/

*/


// visualizzazione del widget
function mmr_dashboard_widget()
{
	$icons= array(
		'online' => 'dashicons-admin-site-alt3 green',
		'offline' => 'dashicons-admin-site-alt3 red',
		'update' => 'dashicons-admin-appearance blue',
		'maintenance' => 'dashicons-admin-tools blue',
		'develop' => 'dashicons-hammer blue',
		'starting' => 'dashicons-location gray',
		'billing' => 'dashicons-tickets-alt navy',
		
	);
	
	$data = get_option('mmr_data');
	echo __('List of activity:');
	echo '<ul>';
	if ($data) foreach($data as $record) {
		echo '<li class="'.esc_attr($record['t']).'"><span class="date">'.$record['d'].'</span> <span class="dashicons '.$icons[$record['t']].'"></span> <span class="description">'.esc_html($record['s']).'</span></li>';
	} else {
		echo '<li>'.__('No activity added.').'</li>';
	}
	echo '</ul>';
	
}


// form di modifica e salvataggio
function mmr_dashboard_options() {
 	$icons= array(
		'online' => 'dashicons-admin-site-alt3 green',
		'offline' => 'dashicons-admin-site-alt3 red',
		'update' => 'dashicons-admin-appearance blue',
		'maintenance' => 'dashicons-admin-tools blue',
		'develop' => 'dashicons-hammer blue',
		'starting' => 'dashicons-location gray',
		'billing' => 'dashicons-tickets-alt purple',
		
	);
	
	
	$data = get_option('mmr_data'); 	
	$delete_id = $_POST['mmr_delete'];
	
	if( 'POST' == $_SERVER['REQUEST_METHOD'] 
	 && isset( $_POST['type']) 
	 && ($_POST['type']!='' ) 				) {
	 	$date_raw = sanitize_text_field($_POST['date']);
	 	$date = DateTime::createFromFormat("Y-m-d", $date_raw);
	 	if ($date!='') {
		 	$data[] = array(
		 		's' => sanitize_text_field($_POST['description']),
		 		'd' => $date->format("Y-m-d"),
		 		't' => sanitize_text_field($_POST['type'])
		 	);
			update_option( 'mmr_data', $data );
	 	} 
	}
	if( 'POST' == $_SERVER['REQUEST_METHOD'] 
	 && isset( $_POST['mmr_delete'] ) ) {
	 	$delete_id = $_POST['mmr_delete'];
	 	foreach($delete_id as $id => $value) {
	 		unset($data[intval($id)]);
	 	}
		update_option( 'mmr_data', $data );
	}
 
 	$today= date('Y-m-d');
	echo '<h3>'.__('List of activity').'</h3><ul>';
	$data = get_option('mmr_data');
	if ($data) foreach($data as $id => $record) 
		echo '<li class="'.esc_attr($record['t']).'"><input type="checkbox" name="mmr_delete['.$id.']"> <span class="date">'.$record['d'].'</span> <span class="dashicons '.$icons[$record['t']].'"></span> <span class="description">'.esc_html($record['s']).'</span> </li>';
	
	
	echo '</ul><p class="note">'.__('Check activity to <span style="color:red;">delete</span> it').'</p><h3>'.__('Add activity').'</h3>';
	echo '
		<input type="text" name="date"  pattern="\d{4}-\d{1,2}-\d{1,2}" class="datepicker" value="'.$today.'"  size="10" placeholder="yyyy-mm-dd"> 
		<select name="type">
			<option value="">--</option>
  			<option value="starting">Starting</option>
  			<option value="online">Go Online</option>
  			<option value="offline">Go Offline</option>
  			<option value="update">Update</option>
  			<option value="maintenance">Maintenance</option>
  			<option value="develop">Develop</option>
			<option value="billing">Billing</option>
			<option value="other">Other activity</option>
		</select>
		<input type="text" name="description" size="35" placeholder="activity description"> 
		
		';
		
}

// restituisce le attività per un endpoint
function mmr_activity_list() {
	$data = get_option('mmr_data');
	//$data = json_encode(unserialize(get_option('mmr_data')));
	return $data;		
}


// registra il widget
add_action("wp_dashboard_setup", function() {
	if (current_user_can('manage_options'))
    		wp_add_dashboard_widget("mmr", __("Management and Maintenance on website"), "mmr_dashboard_widget","mmr_dashboard_options" );
} );


// inserisce un pò di formattazione
add_action('admin_head',function() {
	echo '
	<style>
		#mmr .red{color:red;}#mmr .green{color:green;}#mmr .blue{color:blue;}#mmr .red{color:red;}#mmr .purple{color:purple;}#mmr .grey{color:grey;}
		#mmr .date{color:grey;font-style:italic;}
		#mmr .note{color:darkgray;font-size:80%}
		
	</style>
	';	
});



// registra un endpoint
add_action( 'rest_api_init', function () {
  register_rest_route( 'mmr/v1', '/activities', array(
    'methods' => 'GET',
    'callback' => 'mmr_activity_list',
  ) );
} );



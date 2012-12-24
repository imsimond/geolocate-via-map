<?php
/*
Plugin Name: Geolocate Via Map
Plugin URI: http://codeforthepeople.com/?plugin=geolocate-via-map
Description: 
Author: Simon Dickson
Version: 
Author URI: http://codeforthepeople.com/
Text Domain: cftpgeo
Domain Path: /languages/
*/

/**
* 
* Creates the metabox.
* Note the ability to filter the post type:
* if you want the functionality on something
* other than posts, try something like:
* 
* function some_other_post_type( $post_type ) {
*   return 'myposttype';
* }
* 
**/
add_action('admin_init','cftp_geo_addmetaboxes');
function cftp_geo_addmetaboxes() {
	$post_type = apply_filters( 'cftp_geo_post_type', 'post' );
	add_meta_box(
		'geolocate',
		'Geolocate',
		'cftp_geo_metabox',
		$post_type,
		'normal',
		'high'
		);
}

/**
* 
* The code to construct the metabox on the admin page.
* 
**/

function cftp_geo_metabox() {
	global $post;
	$location = get_post_meta($post->ID,'_location',true);
	?>
<table class="form-table">
	<tr valign="top">
		<th scope="row">
			<label><?php _e('Location name/address:','cftpgeo'); ?></label>
			<div class="description">
				<?php _e('Search using an address/postcode; then edit it for on-screen display.','cftpgeo'); ?>
			</div>
		</th>
		<td>
			<textarea id="location" style="width:100%;height:60px;line-height:1.31em;" name="locationname"><?php if($location['name']) { echo $location['name']; } ?></textarea>
			<div><button class="button" type="button" id="refreshlocmap"><?php _e('Find on map','cftpgeo'); ?></button></div>
		</td>
	</tr>
	<tr valign="top">
		<th>
			<label><?php _e('Location map:','cftpgeo'); ?></label>
			<div class="description">
				<?php _e('You can drag this manually to pinpoint the exact location.','cftpgeo'); ?>
			</div>
		</th>
		<td>
			
		<script type="text/javascript">
			var map;
			function cftp_geo_mapinit() {
					var coords = jQuery('#coords').val().split(',');
					var myOptions = {
						zoom: 9,
						center: new google.maps.LatLng( coords[0],coords[1] ),
						mapTypeId: google.maps.MapTypeId.ROADMAP
					};
					map = new google.maps.Map(document.getElementById('mapcanvas'),myOptions);
					
					var crosshairShape = {coords:[0,0,0,0],type:'rect'};
					var marker = new google.maps.Marker({
						map: map,
						icon: '<?php echo plugins_url(); ?>/geolocate-via-map/crosshair.gif',
						shape: crosshairShape
					});
					marker.bindTo('position', map, 'center'); 
					
					google.maps.event.addListener( map, 'dragend', function( event ) {
						var center = map.getCenter();
						jQuery('#coords').val( center.toUrlValue() );
					});
		
		
			}
		
			jQuery.getScript('http://maps.googleapis.com/maps/api/js?v=3&sensor=false&callback=cftp_geo_mapinit', function(data, textStatus){
				jQuery('#map').html('<div id="mapcanvas" style="width:100%;height:250px;"></div>');
			});
			
			jQuery('#refreshlocmap').click(function(){
				var geocoder = new google.maps.Geocoder();
				geocoder.geocode( {'address': jQuery('#location').val() }, function(data, status){
					if (status == google.maps.GeocoderStatus.OK) {
						var lat = data[0].geometry.location.Ya;
						var lon = data[0].geometry.location.Za;
						map.setCenter( new google.maps.LatLng(lat,lon) );
						jQuery('#coords').val( lat + ',' + lon );
					} else {
						alert("<?php _e('Geocode was not successful for the following reason:','cftpgeo'); ?> " + status);
					}
				});
			});
		</script>
		<div id="map" style="height:250px;background:#ededed url(<?php echo bloginfo('wpurl'); ?>/wp-admin/images/loading.gif) 50% 50% no-repeat;border:1px solid #dfdfdf;margin-bottom:1em;"></div>

		</td>
	</tr>
	<tr valign="top">
		<th><?php _e('Longitude, latitude','cftpgeo'); ?></th>
		<td>
		<input type="text" size="30" name="coords" readonly="readonly" id="coords" value="<?php
			$location = get_post_meta($post->ID,'_location',true);
			if( !empty($location['coords']) )
			{ echo $location['coords']; }
			else
			{ echo '51.5,0'; }
		?>" />
				</td>
			</tr>
		</table>
	<?php
}


/**
* 
* Saves the metabox data when you save the post
* 
**/

add_action('save_post', 'cftp_geo_save_metaboxes');
function cftp_geo_save_metaboxes() {
	global $post;
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return $post_id;
	}
	if (defined('DOING_AJAX') ) {
		return $post_id;
	}
	if ( empty($_POST['locationname']) ) {
		delete_post_meta($post->ID,'_location');	
	} else {
		$location = array(
			'name' => $_POST['locationname'],
			'coords' => $_POST['coords']
		);
		update_post_meta($post->ID,'_location', $location);
	}
}


/**
* 
* Adds a [map] shortcode, which lets you embed a map
* at your preferred point in the post, if you so desire.
* Recognises a few attributes for inline customisation.
* 
**/

add_shortcode('map', 'cftp_geo_postmap');
function cftp_geo_postmap($atts) {
     extract(shortcode_atts(array(
	      'width' => '100%',
	      'height' => '250px',
	      'zoom' => 9
     ), $atts));
     
     global $post;
	$location = get_post_meta($post->ID,'_location',true);
     
     $html = '
	<p id="cftp-geo-map" style="width:' . $width . ';height:' . $height . '"></p>
	<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?v=3&amp;sensor=false"></script>
	<script type="text/javascript">
	var mapOptions = {
		center: new google.maps.LatLng(' . $location['coords'] . '),
		zoom: ' . $zoom . ',
		mapTypeId: google.maps.MapTypeId.ROADMAP
	};
	var map = new google.maps.Map(document.getElementById("cftp-geo-map"),mapOptions);
	var pos = new google.maps.LatLng(' . $location['coords'] . ');
	var title = ' . json_encode( $location['name'] ) . ';
	var marker = new google.maps.Marker({
		position: pos,
		map: map,
		title: title
	});
	</script>
	';
     
     return $html;
}

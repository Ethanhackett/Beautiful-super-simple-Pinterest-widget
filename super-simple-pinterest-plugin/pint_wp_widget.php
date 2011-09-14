<?php
/*
Plugin Name: Super-simple Pinterest Widget
Plugin URI: http://pinterest.com/
Description: This is a lightweight Wordpress plugin to display your recent Pinterest pins.
Version: 0.1

Installation:
1. Copy pinterest_widget.php to your plugins folder, /wp-content/plugins/
2. Activate it through the plugin management screen.
3. Go to Themes->Sidebar Widgets and drag and drop the widget to wherever you want to show it.

This plugin is heavily-inspired by the Flickr Widget by Donncha O Caoimh
http://donncha.wordpress.com/flickr-widget/

*/

function widget_pinterest($args) {
	if( file_exists( ABSPATH . WPINC . '/rss.php') ) {
		require_once(ABSPATH . WPINC . '/rss.php');
	} else {
		require_once(ABSPATH . WPINC . '/rss-functions.php');
	}
	extract($args);

	$options = get_option('widget_pinterest');
	if( $options == false ) {
		$options[ 'title' ] = 'Pinterest Pins';
		$options[ 'items' ] = 3;
	}
	$title = empty($options['title']) ? __('My Pinterest Pins') : $options['title'];
	$items = $options[ 'items' ];
	$pinterest_rss_url = empty($options['pinterest_rss_url']) ? __('http://pinterest.com/paulsciarra/feed.rss') : $options['pinterest_rss_url'];
	if ( empty($items) || $items < 1 || $items > 15 ) $items = 3;
	
	$rss = fetch_rss( $pinterest_rss_url );
	if( is_array( $rss->items ) ) {
		$out = '';
		$items = array_slice( $rss->items, 0, $items );
		while( list( $key, $pin ) = each( $items ) ) {
			preg_match_all("/<IMG.+?SRC=[\"']([^\"']+)/si",$pin[ 'description' ],$sub,PREG_SET_ORDER);
			$pin_url = str_replace( "_m.jpg", "_t.jpg", $sub[0][1] );
			$out .= "<li><a href='{$pin['link']}'><img alt='".wp_specialchars( $pin[ 'title' ], true )."' title='".wp_specialchars( $pin[ 'title' ], true )."' src='$pin_url' border='0'><h4>".wp_specialchars( $pin[ 'title' ], true )."</h4></a></li>";
		}
		$pinterest_home = $rss->channel[ 'link' ];
		$pinterest_more_title = $rss->channel[ 'title' ];
	}
	?>
	<?php echo $before_widget; ?>
	

<!-- Start of Pinterest Badge -->

<style media="screen" type="text/css">
		#pint_badge_uber_wrapper {
			background-color: #f6f6f6;
			-moz-box-shadow: inset 0px 0px 10px rgba(0,0,0,0.1);
			-webkit-box-shadow: inset 0px 0px 10px rgba(0,0,0,0.1);
			box-shadow: inset 0px 0px 10px rgba(0,0,0,0.1);
			margin-right: -20px;
			margin-left: -24px;
			border-right: 4px solid #d89994;
			overflow: auto;
		}
		
		#pint_badge_uber_wrapper ul, #pint_badge_uber_wrapper ul li{
			list-style: none;
			overflow: auto;
			margin: 0;
			padding: 0;
		}
		
		#pint_badge_uber_wrapper ul  {
			clear: both;
			padding: 7px 5px 7px 20px;
			position: relative;
			top: -5px;
		}
		
		#pint_badge_uber_wrapper ul li a h4 {
			font-size: 11px;
			margin: 9px 4px 4px;
			color: #bdbdbd;
			font-weight: normal;
			font-style: normal;
		}
		
		#pint_badge_uber_wrapper ul li {
			float: left;
			background-color: #ffffff;
			-moz-box-shadow: 0px 0px 10px rgba(0,0,0,0.1);
			-webkit-box-shadow: 0px 0px 10px rgba(0,0,0,0.1);
			box-shadow: 0px 0px 10px rgba(0,0,0,0.1);
			text-align: center;
			padding: 15px 10px 10px;
			margin-right: 18px;
			margin-bottom: 15px;
			width: 20%;
			height: 210px;
			overflow: hidden;
		}
		
		#pint_badge_uber_wrapper ul li a img {
			max-height: 150px;
			max-width: 150px;
			border: 1px solid #f2f2f2;
		}
		
		#more-pins {
			display: block;
			float: left;
			padding: 10px 7px 10px 19px;
			color: #c2c2c2;
			font-size: 15px;
		}
		a#more-pins:hover {
			color: #77afb5;
		}
		#more-pins img {
			margin-bottom: -3px;
		}			
</style>

<div id="pint_badge_uber_wrapper" cellpadding="0" cellspacing="10" border="0">
<a href="<?php echo strip_tags( $pinterest_home ) ?>" id="more-pins"><img src="<?php bloginfo( 'url' ); ?>/wp-content/plugins/super-simple-pinterest-plugin/pinterest.png" width="78px" height="20px"> View Pins</a>
		<ul>
			<?php echo $out ?>
		</ul>
</div>

<!-- End of Pinterest Badge -->

		<?php echo $after_widget; ?>
<?php
}

function widget_pinterest_control() {
	$options = $newoptions = get_option('widget_pinterest');
	if( $options == false ) {
		$newoptions[ 'title' ] = 'Pinterest Pins';
	}
	if ( $_POST["pinterest-submit"] ) {
		$newoptions['title'] = strip_tags(stripslashes($_POST["pinterest-title"]));
		$newoptions['items'] = strip_tags(stripslashes($_POST["rss-items"]));
		$newoptions['pinterest_rss_url'] = strip_tags(stripslashes($_POST["pinterest-rss-url"]));
	}
	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_option('widget_pinterest', $options);
	}
	$title = wp_specialchars($options['title']);
	$items = wp_specialchars($options['items']);
	if ( empty($items) || $items < 1 ) $items = 3;
	$pinterest_rss_url = wp_specialchars($options['pinterest_rss_url']);

	?>
	<p><label for="pinterest-title"><?php _e('Title:'); ?> <input style="width: 250px;" id="pinterest-title" name="pinterest-title" type="text" value="<?php echo $title; ?>" /></label></p>
	<p><label for="pinterest-rss-url"><?php _e('Pinterest RSS URL:'); ?> <input style="width: 250px;" id="pinterest-title" name="pinterest-rss-url" type="text" value="<?php echo $pinterest_rss_url; ?>" /></label></p>
	<p style="text-align:center; line-height: 30px;"><?php _e('How many pins would you like to display?'); ?> <select id="rss-items" name="rss-items">
	<?php for ( $i = 1; $i <= 15; ++$i ) echo "<option value='$i' ".($items==$i ? "selected='selected'" : '').">$i</option>"; ?>
	</select></p>
	<p align='left'>* Your RSS feed can be found on your Pinterest profile.<br/><br clear='all'></p>
	<p>Leave the Pinterest RSS URL blank to display <a href="http://pinterest.com/paulsciarra/">Paul Sciarra's</a> Pinterest pins.</p>
	<input type="hidden" id="pinterest-submit" name="pinterest-submit" value="1" />
	<?php
}


function pinterest_widgets_init() {
	register_widget_control('Pinterest', 'widget_pinterest_control', 500, 250);
	register_sidebar_widget('Pinterest', 'widget_pinterest');
}
add_action( "init", "pinterest_widgets_init" );

?>
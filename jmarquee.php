<?php
/*
Plugin Name: jMarquee
Plugin URI: http://www.nathanbriggs.com/jmarquee
Version: 2.8
Description: 
Author: Nathan Briggs
Author URI: http://www.nathanbriggs.com/
*/

class jMarquee {
	var $folder = '/wp-content/plugins/jmarquee';
	var $fullfolderurl;

	// Don't start this plugin until all other plugins have started up
	function jMarquee() {
		add_action('plugins_loaded', array(&$this, 'Initalization'));
	}

	function Initalization() {
		$this->fullfolderurl = get_bloginfo('wpurl') . $this->folder . '/';

		add_action('init', array(&$this, 'addbuttons'));
		
		wp_enqueue_script('jmarquee', $this->folder . '/jquery.jmarquee.js', array('jquery'), $this->version);

		add_action('edit_form_advanced', array(&$this, 'edit_form'));
		//add_action('edit_page_form', array(&$this, 'edit_form'));
		add_action('admin_print_scripts', array(&$this, 'admin_js'));
		add_action('admin_head', array(&$this, 'admin_head'));
		add_shortcode('jmarquee', array(&$this, 'shortcode_handler'));
	}
	
	
	function shortcode_handler($atts, $content = null) {
		extract(shortcode_atts(array(
			'behavior' => 'scroll',
			'loop' => null,
			'scrollamount' => null,
			'direction' => 'right',
			'height' => null,
			'width' => null
		), $atts));
		$html = '<jmarquee behavior="'.$behavior.'" direction="'.$direction.'" ';
		if(!is_null($loop)) $html .= 'loop="'.$loop.'" ';
		if(!is_null($scrollamount)) $html .= 'scrollamount="'.$scrollamount.'" ';
		if(!is_null($height)) $html .= 'height="'.$height.'" ';
		if(!is_null($width)) $html .= 'width="'.$width.'" ';
		$html .= ">$content</jmarquee>";
		return $html;
	}

	// Make our buttons on the write screens
	function addbuttons() {
		// Don't bother doing this stuff if the current user lacks permissions as they'll never see the pages
		if ( !current_user_can('edit_posts') && !current_user_can('edit_pages') ) return;

		if ( 'true' == get_user_option('rich_editing')) {
			add_filter( 'mce_external_plugins', array(&$this, 'mce_external_plugins') );
			add_filter( 'mce_buttons_3', array(&$this, 'mce_buttons') );
		}
	}

	function admin_head() {
		echo '<link type="text/css" rel="stylesheet" href="' . get_bloginfo('wpurl') . '/wp-content/plugins/jmarquee/jmarquee.css" />' . "\n";
	}
	
	// TinyMCE integration hooks
	function mce_external_plugins( $plugins ) {
		$plugins['jmarquee'] = get_bloginfo('wpurl') . $this->folder . '/resources/tinymce3/editor_plugin.js';
		return $plugins;
	}
	function mce_buttons($buttons) {
		array_push($buttons, 'buttonjMarquee' );
		return $buttons;
	}
	
	function admin_js() {
		wp_enqueue_script('jquery-ui-dialog');
	}
	
	// Outputs the needed Javascript (not in a .js file as it's dynamic and just easier this way)
	function edit_form() { ?>

<script type="text/javascript">
	function jMarqueeDialog() {
		/*
			so we have a shortcode that needs attributes:-
			behavior: scroll, alternate, slide
			loop: int
			scrollamount: int (speed?)
			direction: up, down, left, right
			height: int in px
			width: int in px
			text line for the content
		*/	
		jQuery('#jmarquee_dialog').dialog('open');
	}


	jQuery(function($) {
		$('body').append('<div id="jmarquee_dialog" style="display:none; background: #fff; padding: 10px"></div>');
		var m_d = $('#jmarquee_dialog');
		m_d.append('<select name="md_behavior"><option>scroll</option><option>slide</option><option>alternate</option></select>');
		m_d.append('<p><input name="md_loop" type="text" size="4" />Use this to limit the number of times the jmarquee scrolls or whatever</p>');
		m_d.append('<p><input name="md_scrollamount" type="text"  size="4" value="1" />Use higher numbers to increase the speed</p>');
		m_d.append('<select name="md_direction"><option>right</option><option>left</option><option>up</option><option>down</option></select>');
		m_d.append('<p><input name="md_height" type="text"  size="4" value="20" />You can enter a number here to specify the height of the jmarquee. Don\'t add px or anything on the end!</p>');
		m_d.append('<p><input name="md_width" type="text"  size="4" value="400" />You can enter a number here to specify the width of the jmarquee. Don\'t add px or anything on the end!</p>');
		m_d.append('<textarea name="md_text" rows="6" cols="40">You can put any HTML you want in here!</textarea>');
		//$('#md_button').bind('click', );
		$('#jmarquee_dialog').dialog({
			autoOpen: false,
			height: 450,
			width: 450,
			modal: true,
			title: "jMarquee madness!",
			buttons: {
				'Close': function() {
					jQuery(this).dialog('close');
				},
				'Insert jmarquee': function() {
					var behavior = $('[name|=md_behavior]').val() ? 'behavior="'+$('[name|=md_behavior]').val()+'" ' : '';
					var loop = $('[name|=md_loop]').val() ? 'loop="'+$('[name|=md_loop]').val()+'" ' : '';
					var scrollamount = $('[name|=md_scrollamount]').val() ? 'scrollamount="'+$('[name|=md_scrollamount]').val()+'" ' : '';
					var direction = $('[name|=md_direction]').val() ? ' direction="'+$('[name|=md_direction]').val()+'" ' : '';
					var height = $('[name|=md_height]').val() ? ' height="'+$('[name|=md_height]').val()+'" ' : '';
					var width = $('[name|=md_width]').val() ? ' width="'+$('[name|=md_width]').val()+'" ' : '';
					var text = $('[name|=md_text]').val() ? $('[name|=md_text]').val() : '';
					buttonsnap_settext('[jmarquee '+behavior+loop+scrollamount+direction+height+width+']'+text+'[/jmarquee]');
					jQuery('#jmarquee_dialog').dialog('close');
					return false;//prevent default action
				},
			},
			close: function() {
			}
		});
	});

</script>

<?php
	} // edit_form()
   
        

} // end class jMarquee

global $jMarquee;

$jMarquee = new jMarquee();

// ButtonSnap needs to be loaded outside the class in order to work right
if ( !class_exists('buttonsnap') ) @include_once( ABSPATH . '/wp-content/plugins/jmarquee/resources/buttonsnap.php' );


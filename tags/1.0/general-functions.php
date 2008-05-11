<?php
/*
Copyright 2008 Oliver Schlöbe (email : webmaster@schloebe.de)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/* ************************************************ */
/* Capabilities	- propably for future release		*/
/* ************************************************ */

function ame_get_role( $capability ) {
	$check_order = array("subscriber", "contributor", "author", "editor", "administrator");

	$args = array_slice(func_get_args(), 1);
	$args = array_merge(array($capability), $args);

	foreach ($check_order as $role) {
		$check_role = get_role($role);
		
		if ( empty($check_role) )
			return false;
			
		if (call_user_func_array(array(&$check_role, 'has_cap'), $args))
			return $role;
	}
	return false;
}

function ame_set_capability( $lowest_role, $capability ) {
	$check_order = array("subscriber", "contributor", "author", "editor", "administrator");

	$add_capability = false;
	
	foreach ($check_order as $role) {
		if ($lowest_role == $role)
			$add_capability = true;
			
		$the_role = get_role($role);
		
		if ( empty($the_role) )
			continue;
			
		$add_capability ? $the_role->add_cap($capability) : $the_role->remove_cap($capability) ;
	}
}



/* ************************************************ */
/* Localization										*/
/* ************************************************ */

function ame_locale_exists() {
	$cur_locale = get_locale();
	$ame_date_locale_path = get_bloginfo('wpurl') . "/" . PLUGINDIR . AME_PLUGINPATH . 'js/jquery-addons/date_' . $cur_locale . '.js';
	if( file_exists( $ame_date_locale_path ) || !empty( $cur_locale ) ) {
		return true;
	} else {
		return false;
	}
}



/* ************************************************ */
/* Define the Ajax response functions				*/
/* ************************************************ */

function return_function( $output ) {
	return $output;
}

function ame_get_pageorder() {
	global $wpdb, $post;
	$pageorder2 = $_POST['pageordertable2'];
	parse_str( $pageorder2 );
	$orderval = ""; $i = 0;
	foreach( $pageordertable as $value ) {
		$value = intval( substr( $value, 5 ) );
		$has_parent = get_post( $value );
		if( $value != '0' && empty( $has_parent->post_parent ) ) {
			//$orderval .= $value . "=" . $i . " ; ";
			$wpdb->query("UPDATE $wpdb->posts SET menu_order = " . $i . " WHERE ID = " . $value . " AND post_type = 'page'");
			$i++;
		}
	}
	
	die( "jQuery(\"#ame_ordersave_loader\").html('');" );
	//die( "jQuery(\"table.widefat\").animate( { borderColor: '#328AB2' }, 300).animate( { borderColor: '#cccccc' }, 300).animate( { borderColor: '#328AB2' }, 300).animate( { borderColor: '#cccccc' }, 300);" );
}

function ame_toggle_showinvisposts() {
	global $wpdb;
	$status = intval($_POST['status']);
	
	update_option("ame_toggle_showinvisposts", $status);
	die( "location.reload();" );
}

function ame_toggle_orderoptions() {
	global $wpdb;
	$status = intval($_POST['status']);
	
	update_option("ame_show_orderoptions", $status);
	die( "location.reload();" );
}

function ame_slug_edit() {
	global $wpdb;
	$catid = intval($_POST['category_id']);
	$newslug = $_POST['newslug'];
	if( is_string($_POST['posttype']) ) $posttype = $_POST['posttype'];
	if( $posttype == 'post' ) { $postnumber = '1'; } elseif( $posttype == 'page' ) { $postnumber = '2'; }
	$curpostslug = $wpdb->get_var("SELECT post_name FROM $wpdb->posts WHERE ID = " . $catid);
	
	$addHTML = "<tr id='alter" . $posttype . "-" . $catid . "' class='author-other status-publish' valign='middle'><th scope='row' class='check-column'></th><td>" . __('Post') . " #" . $catid . "</td><td colspan='8' align='right'> <input type='text' value='" . $curpostslug . "' size='50' style='font-size:1em;' id='ame_slug" . $catid . "' /> <input value='" . __('Save') . "' class='button-secondary' type='button' style='font-size:1em;' onclick='ame_ajax_slug_save(" . $catid . ", " . $postnumber . ");' /> <input value='" . __('Cancel') . "' class='button' type='button' style='font-size:1em;' onclick='ame_edit_cancel(" . $catid . ");' /></td></tr>";
	die( "jQuery('#" . $posttype . "-" . $catid . "').after( \"" . $addHTML . "\" ); jQuery('#" . $posttype . "-" . $catid . "').hide();" );
}

function ame_save_order() {
	global $wpdb;
	$catid = intval( $_POST['category_id'] );
	$neworderid = intval( $_POST['new_orderid'] );
	if( is_string($_POST['posttype']) ) $posttype = $_POST['posttype'];
	
	$wpdb->query("UPDATE $wpdb->posts SET menu_order = '" . $neworderid . "' WHERE ID = '" . $catid . "'");
	die( "jQuery('span#ame_order_loader" . $catid . "').hide(); jQuery('#" . $posttype . "-" . $catid . " td, #" . $posttype . "-" . $catid . " th').animate( { backgroundColor: '#EAF3FA' }, 300).animate( { backgroundColor: '#F9F9F9' }, 300).animate( { backgroundColor: '#EAF3FA' }, 300).animate( { backgroundColor: '#F9F9F9' }, 300);" );
}

function ame_save_slug() {
	global $wpdb;
	$catid = intval($_POST['category_id']);
	$new_slug = sanitize_title($_POST['new_slug']);
	if( is_string($_POST['typenumber']) ) $posttype = $_POST['typenumber'];
	$current_page = basename($_SERVER['PHP_SELF'], ".php");
	if( $posttype == '1' ) { $posttype = 'post'; } elseif( $posttype == '2' ) { $posttype = 'page'; }
	
	$wpdb->query("UPDATE $wpdb->posts SET post_name = '" . $new_slug . "' WHERE ID = '" . $catid . "'");
	die( "jQuery('#" . $posttype . "-" . $catid . "').show(); jQuery('#alter" . $posttype . "-" . $catid . "').hide(); jQuery('#" . $posttype . "-" . $catid . " td, #" . $posttype . "-" . $catid . " th').animate( { backgroundColor: '#EAF3FA' }, 300).animate( { backgroundColor: '#F9F9F9' }, 300).animate( { backgroundColor: '#EAF3FA' }, 300).animate( { backgroundColor: '#F9F9F9' }, 300);" );
}

function ame_save_title() {
	global $wpdb;
	$catid = intval($_POST['category_id']);
	$new_title = $_POST['new_title'];
	if( is_string($_POST['posttype']) ) $posttype = $_POST['posttype'];
	
	$wpdb->query("UPDATE $wpdb->posts SET post_title = '" . $new_title . "' WHERE ID = '" . $catid . "'");
	die( "jQuery('a[@href$=post=" . $catid . "]').html('" . $new_title . "'); jQuery('#" . $posttype . "-" . $catid . "').show(); jQuery('#alter" . $posttype . "-" . $catid . "').hide(); jQuery('#" . $posttype . "-" . $catid . " td, #" . $posttype . "-" . $catid . " th').animate( { backgroundColor: '#EAF3FA' }, 300).animate( { backgroundColor: '#F9F9F9' }, 300).animate( { backgroundColor: '#EAF3FA' }, 300).animate( { backgroundColor: '#F9F9F9' }, 300);" );
}

function ame_set_date() {
	global $wpdb;
	$catid = intval(substr($_POST['category_id'], 10, 5));
	$newpostdate = get_date_from_gmt( date("Y-m-d H:i:s", strtotime( $_POST['pickedDate'] )) );
	$newpostdate_gtm = get_gmt_from_date( $newpostdate );
	if( is_string($_POST['posttype']) ) $posttype = $_POST['posttype'];
	
	//die( "alert('" . $newpostdate . "');alert('" . $newpostdate_gtm . "');" );
	$wpdb->query("UPDATE $wpdb->posts SET post_date = '" . $newpostdate . "' WHERE ID = '" . $catid . "'");
	$wpdb->query("UPDATE $wpdb->posts SET post_date_gmt = '" . $newpostdate_gtm . "' WHERE ID = '" . $catid . "'");
	if( strtotime( current_time(mysql) ) < strtotime( $newpostdate ) ) {
		$wpdb->query("UPDATE $wpdb->posts SET post_status = 'future' WHERE ID = '" . $catid . "'");
		die( "jQuery('#" . $posttype . "-" . $catid . " abbr').html('" . date(__('Y/m/d'), strtotime( $newpostdate ) ) . "'); jQuery('#" . $posttype . "-" . $catid . "').removeClass('status-publish').addClass('status-future'); jQuery('#" . $posttype . "-" . $catid . " td, #" . $posttype . "-" . $catid . " th').animate( { backgroundColor: '#EAF3FA' }, 300).animate( { backgroundColor: '#F9F9F9' }, 300).animate( { backgroundColor: '#EAF3FA' }, 300).animate( { backgroundColor: '#F9F9F9' }, 300);" );
	} elseif ( strtotime( current_time(mysql) ) > strtotime( $newpostdate ) ) {
		$wpdb->query("UPDATE $wpdb->posts SET post_status = 'publish' WHERE ID = '" . $catid . "'");
		die( "jQuery('#" . $posttype . "-" . $catid . " abbr').html('" . date(__('Y/m/d'), strtotime( $newpostdate ) ) . "'); jQuery('#" . $posttype . "-" . $catid . "').removeClass('status-future').addClass('status-publish'); jQuery('#" . $posttype . "-" . $catid . " td, #" . $posttype . "-" . $catid . " th').animate( { backgroundColor: '#EAF3FA' }, 300).animate( { backgroundColor: '#F9F9F9' }, 300).animate( { backgroundColor: '#EAF3FA' }, 300).animate( { backgroundColor: '#F9F9F9' }, 300);" );
	}
}

function ame_toggle_visibility() {
	global $wpdb;
	$catid = intval($_POST['category_id']);
	if( is_string($_POST['vis_status']) ) $status = $_POST['vis_status'];
	if( is_string($_POST['posttype']) ) $posttype = $_POST['posttype'];
	
	if ( $status == 'publish' ) {
		$wpdb->query("UPDATE $wpdb->posts SET post_status = '" . $status . "' WHERE ID = '" . $catid . "'");
		die( "document.getElementById('visicon$catid').innerHTML = '<a href=\"javascript:void(0);\" onclick=\"ame_ajax_set_visibility(" . $catid . ", \'draft\', \'" . $posttype . "\');return false;\"><img src=\"" . get_bloginfo('wpurl') . "/" . PLUGINDIR . AME_PLUGINPATH . "img/visible.png\" border=\"0\" alt=\"" . __('Toggle visibility', 'admin-management-xtended') . "\" title=\"" . __('Toggle visibility', 'admin-management-xtended') . "\" /></a>';jQuery('#" . $posttype . "-" . $catid . " td, #" . $posttype . "-" . $catid . " th').animate( { backgroundColor: '#EAF3FA' }, 300).animate( { backgroundColor: '#F9F9F9' }, 300).animate( { backgroundColor: '#EAF3FA' }, 300).animate( { backgroundColor: '#F9F9F9' }, 300);jQuery('#" . $posttype . "-" . $catid . "').removeClass('status-draft').addClass('status-publish');" );
	} else {
		$wpdb->query("UPDATE $wpdb->posts SET post_status = '" . $status . "' WHERE ID = '" . $catid . "'");
		die( "document.getElementById('visicon$catid').innerHTML = '<a href=\"javascript:void(0);\" onclick=\"ame_ajax_set_visibility(" . $catid . ", \'publish\', \'" . $posttype . "\');return false;\"><img src=\"" . get_bloginfo('wpurl') . "/" . PLUGINDIR . AME_PLUGINPATH . "img/hidden.png\" border=\"0\" alt=\"" . __('Toggle visibility', 'admin-management-xtended') . "\" title=\"" . __('Toggle visibility', 'admin-management-xtended') . "\" /></a>';jQuery('#" . $posttype . "-" . $catid . " td, #" . $posttype . "-" . $catid . " th').animate( { backgroundColor: '#EAF3FA' }, 300).animate( { backgroundColor: '#F9F9F9' }, 300).animate( { backgroundColor: '#EAF3FA' }, 300).animate( { backgroundColor: '#F9F9F9' }, 300);jQuery('#" . $posttype . "-" . $catid . "').removeClass('status-publish').addClass('status-draft');" );
	}
}

add_action('wp_ajax_ame_toggle_visibility', 'ame_toggle_visibility' );
add_action('wp_ajax_ame_set_date', 'ame_set_date' );
add_action('wp_ajax_ame_save_title', 'ame_save_title' );
add_action('wp_ajax_ame_save_slug', 'ame_save_slug' );
add_action('wp_ajax_ame_slug_edit', 'ame_slug_edit' );
add_action('wp_ajax_ame_save_order', 'ame_save_order' );
add_action('wp_ajax_ame_toggle_orderoptions', 'ame_toggle_orderoptions' );
add_action('wp_ajax_ame_toggle_showinvisposts', 'ame_toggle_showinvisposts' );
add_action('wp_ajax_ame_get_pageorder', 'ame_get_pageorder' );



/* ************************************************ */
/* Write JS into our admin header					*/
/* ************************************************ */

function ame_js_jquery_datepicker_header() {
	$cur_locale = get_locale();
	$current_page = basename($_SERVER['PHP_SELF'], ".php");
	$posttype = "lala";
	if( $current_page == 'edit' ) {
		$posttype = "post";
	} elseif ( $current_page == 'edit-pages' ) {
		$posttype = "page";
	}
	echo "<script type='text/javascript' src='" . get_bloginfo('wpurl') . "/" . PLUGINDIR . AME_PLUGINPATH . "js/jquery-addons/date.js'></script>\n";
	if( ame_locale_exists() === true ) {
		echo "<script type='text/javascript' src='" . get_bloginfo('wpurl') . "/" . PLUGINDIR . AME_PLUGINPATH . "js/jquery-addons/date_" . $cur_locale . ".js'></script>\n";
	}
	echo "<script type='text/javascript' src='" . get_bloginfo('wpurl') . "/" . PLUGINDIR . AME_PLUGINPATH . "js/jquery-addons/jquery.datePicker.js'></script>\n";
	if( $current_page == 'edit-pages' && get_option('ame_show_orderoptions') == '2' ) {
		echo "<script type='text/javascript' src='" . get_bloginfo('wpurl') . "/" . PLUGINDIR . AME_PLUGINPATH . "js/jquery-addons/jquery.tablednd.js'></script>\n";
		echo "<script type=\"text/javascript\">
//<![CDATA[
jQuery(document).ready(function() {
	jQuery(\".widefat\").attr(\"id\", \"pageordertable\");
	jQuery(\"#pageordertable > thead > tr\").attr(\"id\", \"page-0\");
	jQuery(\"tr:has('a:contains('—')')\").addClass('nodrop').addClass('nodrag');
    jQuery(\"#pageordertable\").tableDnD({
    	scrollAmount: \"30\",
    	onDragClass: \"ondragrow\",
    	onDragStart: function(table, row) {
    		//jQuery(\"tr[class*=\'nodrop\']\").addClass('cannotdrop');
    		jQuery(\"tr[class*=\'nodrop\'] a\").css( { opacity: 0.3 }, 600);
    	},
    	onDrop: function(table, row) {
    		//jQuery(\"tr[class*=\'cannotdrop\']\").show();
    		jQuery(\"tr[class*=\'nodrop\'] a\").css( { opacity: 1.0 }, 600);
    		jQuery(\"tr[class*=\'cannotdrop\']\").removeClass('cannotdrop');
    		jQuery(\"#ame_ordersave_loader\").html(\"<img src='" . get_bloginfo('wpurl') . "/" . PLUGINDIR . AME_PLUGINPATH . "img/loader2.gif' border='0' alt='' align='absmiddle' /> | \");
    		ame_ajax_get_pageorder( jQuery.tableDnD.serialize() );
    	}
    });
});
//]]>
</script>
\n";
	}
	echo "<link rel='stylesheet' href='" . get_bloginfo('wpurl') . "/" . PLUGINDIR . AME_PLUGINPATH . "css/datePicker.css' type='text/css' />\n";
	echo "<script type=\"text/javascript\" charset=\"utf-8\">
//<![CDATA[
Date.firstDayOfWeek = 1;
Date.format = 'yyyy-mm-dd';\n";
if ( get_locale() == 'de_DE' ) {
	echo "jQuery.dpText = {
	TEXT_PREV_YEAR		:	'Voriges Jahr',
	TEXT_PREV_MONTH		:	'Voriger Monat',
	TEXT_NEXT_YEAR		:	'N&auml;chstes Jahr',
	TEXT_NEXT_MONTH		:	'N&auml;chster Monat',
	TEXT_CLOSE			:	'Schlie&szlig;en',
	TEXT_CHOOSE_DATE	:	'Datum w&auml;hlen'
}\n";
}
	echo "jQuery(function() {
	jQuery('.date-pick')
		.datePicker({startDate:'2000-01-01', createButton:false, displayClose:true})
		.dpSetPosition(jQuery.dpConst.POS_TOP, jQuery.dpConst.POS_RIGHT)
		.bind(
			'click',
			function() {
				jQuery(this).dpDisplay();
				this.blur();
				return false;
			}
		)
		.bind(
			'dateSelected',
			function(e, selectedDate) {
				var cat_id = this.id;
				var selDate = selectedDate.getFullYear() + '-' + (Number(selectedDate.getMonth())+1) + '-' + selectedDate.getDate() + ' ' + selectedDate.getHours() + ':' + selectedDate.getMinutes() + ':' + selectedDate.getMilliseconds();
				ame_ajax_set_postdate( cat_id, selDate, posttype='" . $posttype . "' );
			}
		);
});
//]]>
</script>\n";
if( $current_page == 'edit-pages' ) {
	if ( get_option('ame_show_orderoptions') == '0' ) {
		echo "<script type=\"text/javascript\" charset=\"utf-8\">
jQuery(document).ready(function() {
   jQuery(\"div[class='tablenav'] div[class='alignleft']\").after(\"<div class='tablenav-pages'><span id='ame_order2_loader'>" . __('Edit Page Order:', 'admin-management-xtended') . "</span> <span class='page-numbers current'>" . __('Off', 'admin-management-xtended') . "</span> <a class='page-numbers' href='javascript:void(0);' onclick='ame_ajax_toggle_orderoptions(1)'>" . __('Direct input', 'admin-management-xtended') . "</a> <a class='page-numbers' href='javascript:void(0);' onclick='ame_ajax_toggle_orderoptions(2)'>" . __('Drag & Drop', 'admin-management-xtended') . "</a></div>\");
});
</script>\n";
	} elseif ( get_option('ame_show_orderoptions') == '1' ) {
		echo "<script type=\"text/javascript\" charset=\"utf-8\">
jQuery(document).ready(function() {
   jQuery(\"div[class='tablenav'] div[class='alignleft']\").after(\"<div class='tablenav-pages'><span id='ame_order2_loader'>" . __('Edit Page Order:', 'admin-management-xtended') . "</span> <a class='page-numbers' href='javascript:void(0);' onclick='ame_ajax_toggle_orderoptions(0)'>" . __('Off', 'admin-management-xtended') . "</a> <span class='page-numbers current'>" . __('Direct input', 'admin-management-xtended') . "</span> <a class='page-numbers' href='javascript:void(0);' onclick='ame_ajax_toggle_orderoptions(2)'>" . __('Drag & Drop', 'admin-management-xtended') . "</a></div>\");
});
</script>\n";
	} elseif ( get_option('ame_show_orderoptions') == '2' ) {
		echo "<script type=\"text/javascript\" charset=\"utf-8\">
jQuery(document).ready(function() {
   jQuery(\"div[class='tablenav'] div[class='alignleft']\").after(\"<div class='tablenav-pages'><span id='ame_ordersave_loader'></span> <span id='ame_order2_loader'>" . __('Edit Page Order:', 'admin-management-xtended') . "</span> <a class='page-numbers' href='javascript:void(0);' onclick='ame_ajax_toggle_orderoptions(0)'>" . __('Off', 'admin-management-xtended') . "</a> <a class='page-numbers' href='javascript:void(0);' onclick='ame_ajax_toggle_orderoptions(1)'>" . __('Direct input', 'admin-management-xtended') . "</a> <span class='page-numbers current'>" . __("Drag & Drop <a href='http://www.schloebe.de/wordpress/admin-management-xtended-plugin/#pageorder' target='_blank' style='color:#fff;text-decoration:underline;'>[?]</a>", 'admin-management-xtended') . "</span></div>\");
});
</script>\n";
	}
}
echo '<style type="text/css">
.status-draft, .status-future, .cannotdrop {
	-moz-opacity: 0.4;
	filter:Alpha(opacity=40, finishopacity=40, style=1);
}
.ondragrow {
	background: #328AB2;
	color: #fff;
}

.ondragrow a {
	color: #fff;
}
</style>';
if( $current_page == 'edit' ) {
	if ( get_option('ame_toggle_showinvisposts') == '1' ) {
		echo "<script type=\"text/javascript\" charset=\"utf-8\">
jQuery(document).ready(function() {
   jQuery(\"div#ajax-response + div[class='tablenav'] div[class='tablenav-pages']\").after(\"<div class='alignleft' style='margin-right:5px;'><input type='button' value='" . __('Hide invisible Posts', 'admin-management-xtended') . "' class='button-secondary' onclick='ame_ajax_toggle_showinvisposts(0)' id='ame_toggle_showinvisposts' /></div>\");
});
</script>\n";
	} elseif ( get_option('ame_toggle_showinvisposts') == '0' ) {
		echo "<script type=\"text/javascript\" charset=\"utf-8\">
jQuery(document).ready(function() {
   jQuery(\"div#ajax-response + div[class='tablenav'] div[class='tablenav-pages']\").after(\"<div class='alignleft' style='margin-right:5px;'><input type='button' value='" . __('Show invisible Posts', 'admin-management-xtended') . "' class='button-secondary' onclick='ame_ajax_toggle_showinvisposts(1)' id='ame_toggle_showinvisposts' /></div>\");
});
</script>\n";
	}
}
if ( get_option('ame_toggle_showinvisposts') == '0' ) {
	if( !isset( $_GET['post_status'] ) ) {
		echo '<script type="text/javascript" charset="utf-8">
jQuery(document).ready(function() {
   jQuery("tr[class*=\'status-draft\']").hide();
   jQuery("tr[class*=\'status-future\']").hide();
});
</script>' . "\n";
	}
}
}

function ame_js_admin_header() {
	wp_print_scripts( array( 'sack' ));

	$current_page = basename($_SERVER['PHP_SELF'], ".php");
	if( $current_page == 'edit' ) { $posttype = 'post'; } elseif( $current_page == 'edit-pages' ) { $posttype = 'page'; }
?>
<script type="text/javascript">
//<![CDATA[
function ame_ajax_get_pageorder( pageordertable ) {
	var ame_sack = new sack(
	"<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php");
	ame_sack.execute = 1;
	ame_sack.method = 'POST';
	ame_sack.setVar( "action", "ame_get_pageorder" );
	ame_sack.setVar( "pageordertable2", pageordertable );
	ame_sack.onError = function() { alert('Ajax error on getting page order') };
	ame_sack.runAJAX();
}

function ame_ajax_toggle_showinvisposts( status ) {
	jQuery("#ame_toggle_showinvisposts").attr("value", "<?php _e('Please wait...'); ?>");
	var ame_sack = new sack(
	"<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php");
	ame_sack.execute = 1;
	ame_sack.method = 'POST';
	ame_sack.setVar( "action", "ame_toggle_showinvisposts" );
	ame_sack.setVar( "status", status );
	ame_sack.onError = function() { alert('Ajax error on toggling post visibility') };
	ame_sack.runAJAX();
}

function ame_ajax_toggle_orderoptions( status ) {
	jQuery("#ame_order2_loader").html("<?php _e('Please wait...'); ?>");
	var ame_sack = new sack(
	"<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php");
	ame_sack.execute = 1;
	ame_sack.method = 'POST';
	ame_sack.setVar( "action", "ame_toggle_orderoptions" );
	ame_sack.setVar( "status", status );
	ame_sack.onError = function() { alert('Ajax error on toggling page order column') };
	ame_sack.runAJAX();
}

function ame_ajax_order_save( cat_id, posttype ) {
	var neworderid = jQuery("input#ame_pageorder" + cat_id).attr('value');
	jQuery("span#ame_order_loader" + cat_id).show();
	var ame_sack = new sack(
	"<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php");
	ame_sack.execute = 1;
	ame_sack.method = 'POST';
	ame_sack.setVar( "action", "ame_save_order" );
	ame_sack.setVar( "category_id", cat_id );
	ame_sack.setVar( "new_orderid", neworderid );
	ame_sack.setVar( "posttype", posttype );
	ame_sack.onError = function() { alert('Ajax error on saving page prder') };
	ame_sack.runAJAX();
}

function ame_slug_edit( cat_id, posttype ) {
	var newslug = jQuery("input#ame_slug" + cat_id).attr('value');
	var ame_sack = new sack(
	"<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php");
	ame_sack.execute = 1;
	ame_sack.method = 'POST';
	ame_sack.setVar( "action", "ame_slug_edit" );
	ame_sack.setVar( "category_id", cat_id );
	ame_sack.setVar( "posttype", posttype );
	ame_sack.onError = function() { alert('Ajax error on editing post slug') };
	ame_sack.runAJAX();
}

function ame_ajax_set_visibility( cat_id, status, posttype ) {
	var ame_sack = new sack(
	"<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php");
	ame_sack.execute = 1;
	ame_sack.method = 'POST';
	ame_sack.setVar( "action", "ame_toggle_visibility" );
	ame_sack.setVar( "category_id", cat_id );
	ame_sack.setVar( "vis_status", status );
	ame_sack.setVar( "posttype", posttype );
	ame_sack.onError = function() { alert('Ajax error on toggling visibility') };
	ame_sack.runAJAX();
}

function ame_ajax_set_postdate( cat_id, pickedDate, posttype ) {
	var ame_sack = new sack(
	"<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php");
	ame_sack.execute = 1;
	ame_sack.method = 'POST';
	ame_sack.setVar( "action", "ame_set_date" );
	ame_sack.setVar( "category_id", cat_id );
	ame_sack.setVar( "pickedDate", pickedDate );
	ame_sack.setVar( "posttype", posttype );
	ame_sack.onError = function() { alert('Ajax error on chosing new date') };
	ame_sack.runAJAX();
}

function ame_title_edit( cat_id, title_text, posttype ) {
	var addHTML = '<tr id="alter' + posttype + '-' + cat_id + '" class="author-other status-publish" valign="middle"><th scope="row" class="check-column"></th><td><?php _e("Post"); ?> #' + cat_id + '</td><td colspan="8" align="right"><input type="text" value="' + unescape(title_text) + '" size="50" style="font-size:1em;" id="ame_title' + cat_id + '" /> <input value="<?php _e("Save"); ?>" class="button-secondary" type="button" style="font-size:1em;" onclick="ame_ajax_title_save(\'' + cat_id + '\', \'' + posttype + '\');" /> <input value="<?php _e("Cancel"); ?>" class="button" type="button" style="font-size:1em;" onclick="ame_edit_cancel(\'' + cat_id + '\');" /></td></tr>';
	jQuery("#" + posttype + "-" + cat_id).after( addHTML );
	jQuery("#" + posttype + "-" + cat_id).hide();
}

function ame_ajax_title_save( cat_id, posttype ) {
	var newtitle = jQuery("input#ame_title" + cat_id).attr('value');
	var ame_sack = new sack(
	"<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php");
	ame_sack.execute = 1;
	ame_sack.method = 'POST';
	ame_sack.setVar( "action", "ame_save_title" );
	ame_sack.setVar( "category_id", cat_id );
	ame_sack.setVar( "new_title", newtitle );
	ame_sack.setVar( "posttype", posttype );
	ame_sack.onError = function() { alert('Ajax error on saving post title') };
	ame_sack.runAJAX();
}

function ame_ajax_slug_save( cat_id, typenumber ) {
	var newslug = jQuery("input#ame_slug" + cat_id).attr('value');
	var ame_sack = new sack(
	"<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php");
	ame_sack.execute = 1;
	ame_sack.method = 'POST';
	ame_sack.setVar( "action", "ame_save_slug" );
	ame_sack.setVar( "category_id", cat_id );
	ame_sack.setVar( "new_slug", newslug );
	ame_sack.setVar( "typenumber", typenumber );
	ame_sack.onError = function() { alert('Ajax error on saving post slug') };
	ame_sack.runAJAX();
}

function ame_edit_cancel( cat_id ) {
	jQuery("#alter<?php echo $posttype; ?>-" + cat_id).hide();
	jQuery("#<?php echo $posttype; ?>-" + cat_id).show();
}
//]]>
</script>
<?php
}

$current_page = basename($_SERVER['PHP_SELF'], ".php");
if( $current_page == 'edit' || $current_page == 'edit-pages' ) {
	add_action('admin_print_scripts', 'ame_js_admin_header' );
	add_action('admin_head', 'ame_js_jquery_datepicker_header' );
}
?>
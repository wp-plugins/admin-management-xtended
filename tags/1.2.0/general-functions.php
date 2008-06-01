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

function ame_ajax_set_commentstatus() {
	global $wpdb;
	$postid = intval($_POST['postid']);
	$q_status = intval($_POST['comment_status']);
	( $q_status == '1' ) ? $status = 'open' : $status = 'closed';
	if( is_string($_POST['posttype']) ) $posttype = $_POST['posttype'];
	
	if ( $status == 'open' ) {
		$wpdb->query("UPDATE $wpdb->posts SET comment_status = '" . $status . "' WHERE ID = '" . $postid . "'");
		die( "jQuery('#commentstatus" . $postid . "').html('<a href=\"javascript:void(0);\" onclick=\"ame_ajax_set_commentstatus(" . $postid . ", 0, \'" . $posttype . "\');return false;\"><img src=\"" . get_bloginfo('wpurl') . "/" . PLUGINDIR . AME_PLUGINPATH . "img/comments_open.png\" border=\"0\" alt=\"" . __('Toggle comment status open/closed', 'admin-management-xtended') . "\" title=\"" . __('Toggle comment status open/closed', 'admin-management-xtended') . "\" /></a>');jQuery('#" . $posttype . "-" . $postid . " td, #" . $posttype . "-" . $postid . " th').animate( { backgroundColor: '#EAF3FA' }, 300).animate( { backgroundColor: '#F9F9F9' }, 300).animate( { backgroundColor: '#EAF3FA' }, 300).animate( { backgroundColor: '#F9F9F9' }, 300);" );
	} else {
		$wpdb->query("UPDATE $wpdb->posts SET comment_status = '" . $status . "' WHERE ID = '" . $postid . "'");
		die( "jQuery('#commentstatus" . $postid . "').html('<a href=\"javascript:void(0);\" onclick=\"ame_ajax_set_commentstatus(" . $postid . ", 1, \'" . $posttype . "\');return false;\"><img src=\"" . get_bloginfo('wpurl') . "/" . PLUGINDIR . AME_PLUGINPATH . "img/comments_closed.png\" border=\"0\" alt=\"" . __('Toggle comment status open/closed', 'admin-management-xtended') . "\" title=\"" . __('Toggle comment status open/closed', 'admin-management-xtended') . "\" /></a>');jQuery('#" . $posttype . "-" . $postid . " td, #" . $posttype . "-" . $postid . " th').animate( { backgroundColor: '#EAF3FA' }, 300).animate( { backgroundColor: '#F9F9F9' }, 300).animate( { backgroundColor: '#EAF3FA' }, 300).animate( { backgroundColor: '#F9F9F9' }, 300);" );
	}
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
			$wpdb->query("UPDATE $wpdb->posts SET menu_order = " . $i . " WHERE ID = " . $value . " AND post_type = 'page'");
			$i++;
		}
	}
	
	die( "jQuery(\"#ame_ordersave_loader\").html('');" );
	die( "jQuery(\".tablenav\").animate( { backgroundColor: '#E5E5E5' }, 300).animate( { backgroundColor: '#EAF3FA' }, 300).animate( { backgroundColor: '#E5E5E5' }, 300).animate( { backgroundColor: '#EAF3FA' }, 300);" );
}

function ame_ajax_save_categories() {
	global $wpdb;
	$postid = intval( $_POST['postid'] );
	$ame_cats = $_POST['ame_cats'];
	
	$ame_categories = substr( $ame_cats, 0, -1 );
	$catarray = explode(",", $ame_categories);
	wp_set_post_categories($postid, $catarray);
	unset($GLOBALS['category_cache']);
	
	$categories = get_the_category( $postid );
    $post_cats = "";
	if ( !empty( $categories ) ) {
		$out = array();
		foreach ( $categories as $c ) {
			$out[] = '<a href="edit.php?category_name=' . $c->slug . '"> ' . wp_specialchars(sanitize_term_field('name', $c->name, $c->term_id, 'category', 'display')) . '</a>';
		}
		$ame_post_cats = join( ', ', $out );
	} else {
		$ame_post_cats = __('Uncategorized');
	}
	die( "re_init();jQuery('span#ame_category" . $postid . "').fadeOut('fast', function() {
		jQuery('a#thickboxlink" . $postid . "').show();
		jQuery('span#ame_category" . $postid . "').html('" . addslashes_gpc( $ame_post_cats ) . "').fadeIn('fast');
	});" );
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
		die( "jQuery('#visicon" . $catid . "').html('<a href=\"javascript:void(0);\" onclick=\"ame_ajax_set_visibility(" . $catid . ", \'draft\', \'" . $posttype . "\');return false;\"><img src=\"" . get_bloginfo('wpurl') . "/" . PLUGINDIR . AME_PLUGINPATH . "img/visible.png\" border=\"0\" alt=\"" . __('Toggle visibility', 'admin-management-xtended') . "\" title=\"" . __('Toggle visibility', 'admin-management-xtended') . "\" /></a>');jQuery('#" . $posttype . "-" . $catid . " td, #" . $posttype . "-" . $catid . " th').animate( { backgroundColor: '#EAF3FA' }, 300).animate( { backgroundColor: '#F9F9F9' }, 300).animate( { backgroundColor: '#EAF3FA' }, 300).animate( { backgroundColor: '#F9F9F9' }, 300);jQuery('#" . $posttype . "-" . $catid . "').removeClass('status-draft').addClass('status-publish');" );
	} else {
		$wpdb->query("UPDATE $wpdb->posts SET post_status = '" . $status . "' WHERE ID = '" . $catid . "'");
		die( "jQuery('#visicon$catid').html('<a href=\"javascript:void(0);\" onclick=\"ame_ajax_set_visibility(" . $catid . ", \'publish\', \'" . $posttype . "\');return false;\"><img src=\"" . get_bloginfo('wpurl') . "/" . PLUGINDIR . AME_PLUGINPATH . "img/hidden.png\" border=\"0\" alt=\"" . __('Toggle visibility', 'admin-management-xtended') . "\" title=\"" . __('Toggle visibility', 'admin-management-xtended') . "\" /></a>');jQuery('#" . $posttype . "-" . $catid . " td, #" . $posttype . "-" . $catid . " th').animate( { backgroundColor: '#EAF3FA' }, 300).animate( { backgroundColor: '#F9F9F9' }, 300).animate( { backgroundColor: '#EAF3FA' }, 300).animate( { backgroundColor: '#F9F9F9' }, 300);jQuery('#" . $posttype . "-" . $catid . "').removeClass('status-publish').addClass('status-draft');" );
	}
}

if( function_exists('add_action') ) {
	add_action('wp_ajax_ame_toggle_visibility', 'ame_toggle_visibility' );
	add_action('wp_ajax_ame_set_date', 'ame_set_date' );
	add_action('wp_ajax_ame_save_title', 'ame_save_title' );
	add_action('wp_ajax_ame_save_slug', 'ame_save_slug' );
	add_action('wp_ajax_ame_slug_edit', 'ame_slug_edit' );
	add_action('wp_ajax_ame_save_order', 'ame_save_order' );
	add_action('wp_ajax_ame_toggle_orderoptions', 'ame_toggle_orderoptions' );
	add_action('wp_ajax_ame_toggle_showinvisposts', 'ame_toggle_showinvisposts' );
	add_action('wp_ajax_ame_get_pageorder', 'ame_get_pageorder' );
	add_action('wp_ajax_ame_ajax_save_categories', 'ame_ajax_save_categories' );
	add_action('wp_ajax_ame_ajax_set_commentstatus', 'ame_ajax_set_commentstatus' );
}



/* ************************************************ */
/* Write JS into our admin header					*/
/* ************************************************ */

function ame_js_jquery_datepicker_header() {
	$current_page = basename($_SERVER['PHP_SELF'], ".php");
	$posttype = "lala";
	if( $current_page == 'edit' ) {
		$posttype = "post";
	} elseif ( $current_page == 'edit-pages' ) {
		$posttype = "page";
	}
	if( $current_page == 'edit-pages' && get_option('ame_show_orderoptions') == '2' ) {
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
ameAjaxL10n = {
	blogUrl: "<?php bloginfo( 'wpurl' ); ?>", pluginPath: "<?php echo "/" . PLUGINDIR; ?>", pluginUrl: "<?php echo AME_PLUGINPATH; ?>", requestUrl: "<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php", Post: "<?php _e("Post"); ?>", Save: "<?php _e("Save"); ?>", Cancel: "<?php _e("Cancel"); ?>", postType: "<?php echo $posttype; ?>", pleaseWait: "<?php _e("Please wait..."); ?>"
}
//]]>
</script>
<?php
}

function ame_css_admin_header() {
	echo '<link rel="stylesheet" type="text/css" href="' . get_settings('siteurl') . '/' . PLUGINDIR . AME_PLUGINPATH . 'css/styles.css?ver=' . AME_VERSION . '" />' . "\n";
}

$current_page = basename($_SERVER['PHP_SELF'], ".php");
if( function_exists('add_action') ) {
	if( $current_page == 'edit' || $current_page == 'edit-pages' ) {
		$cur_locale = get_locale();
		add_action('admin_head', 'ame_css_admin_header' );
		add_action('admin_head', wp_enqueue_script( array('thickbox') ) );
		add_action('admin_print_scripts', 'ame_js_admin_header' );
		add_action('admin_head', 'ame_js_jquery_datepicker_header' );
		add_action('admin_head', wp_enqueue_script( 'date', get_bloginfo('wpurl') . "/" . PLUGINDIR . AME_PLUGINPATH . "js/jquery-addons/date.js", array('jquery'), AME_VERSION ) );
		add_action('admin_head', wp_enqueue_script( 'datePicker', get_bloginfo('wpurl') . "/" . PLUGINDIR . AME_PLUGINPATH . "js/jquery-addons/jquery.datePicker.js", array('jquery'), AME_VERSION ) );
		add_action('admin_head', wp_enqueue_script( 'ame_miscsrcipts', get_bloginfo('wpurl') . "/" . PLUGINDIR . AME_PLUGINPATH . "js/functions.js", array('sack'), AME_VERSION ) );
		if( ame_locale_exists() === true ) {
			add_action('admin_head', wp_enqueue_script( 'localdate', get_bloginfo('wpurl') . "/" . PLUGINDIR . AME_PLUGINPATH . "js/jquery-addons/date_" . $cur_locale . ".js", array('jquery'), AME_VERSION ) );
		}
		if( $current_page == 'edit-pages' && get_option('ame_show_orderoptions') == '2' ) {
			add_action('admin_head', wp_enqueue_script( 'tablednd', get_bloginfo('wpurl') . "/" . PLUGINDIR . AME_PLUGINPATH . "js/jquery-addons/jquery.tablednd.js", array('jquery'), AME_VERSION ) );
		}
	}
}
?>
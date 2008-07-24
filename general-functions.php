<?php
/**
 * General functions used globally
 *
 * @package WordPress_Plugins
 * @subpackage AdminManagementXtended
 */

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

/**
 * Get all the WordPress user roles using for capability stuff
 *
 * @since 0.7
 * @author scripts@schloebe.de
 *
 * @param string $capability
 * @return string
 */
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

/**
 * Set the user capabilities using for permission stuff
 *
 * @since 0.7
 * @author scripts@schloebe.de
 *
 * @param string $lowest_role
 * @param string $capability
 * @return mixed
 */
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

/**
 * Checks if a current locale file used for popout calendar exists
 *
 * @since 0.7
 * @author scripts@schloebe.de
 *
 * @return bool
 */
function ame_locale_exists() {
	$cur_locale = get_locale();
	$ame_date_locale_path = AME_PLUGINFULLURL . 'js/jquery-addons/date_' . $cur_locale . '.js';
	if( file_exists( $ame_date_locale_path ) || !empty( $cur_locale ) ) {
		return true;
	} else {
		return false;
	}
}



/* ************************************************ */
/* Define the Ajax response functions				*/
/* ************************************************ */

/**
 * Returns the given parameter instead of echoing it
 *
 * @since 0.7
 * @author scripts@schloebe.de
 *
 * @return string|int|mixed
 */
function return_function( $output ) {
	return $output;
}

/**
 * SACK response function for saving media description
 *
 * @since 1.5.0
 * @author scripts@schloebe.de
 */
function ame_ajax_save_mediadesc() {
	global $wpdb;
	$postid = intval( $_POST['postid'] );
	$new_mediadesc = $_POST['new_mediadesc'];
	
	$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET post_excerpt = %s WHERE ID = %d", stripslashes( $new_mediadesc ), $postid ) );
	$ame_media_desc = '<span id="ame_mediadesc_text' . $postid . '">' . $new_mediadesc . '</span>';
	$ame_media_desc .= '&nbsp;<a id="mediadesceditlink' . $postid . '" href="javascript:void(0);" onclick="ame_ajax_form_mediadesc(' . $postid . ');return false;" title="' . __('Edit') . '"><img src="' . AME_PLUGINFULLURL . 'img/' . AME_IMGSET . 'edit_small.gif" border="0" alt="' . __('Edit') . '" title="' . __('Edit') . '" /></a>';
	die( "jQuery('span#ame_mediadesc" . $postid . "').fadeOut('fast', function() {
		jQuery('span#ame_mediadesc" . $postid . "').html('" . addslashes_gpc( $ame_media_desc ) . "').fadeIn('fast');
	});" );
}

/**
 * SACK response function for saving comment status for a post
 *
 * @since 1.2.0
 * @author scripts@schloebe.de
 */
function ame_ajax_set_commentstatus() {
	global $wpdb;
	$postid = intval($_POST['postid']);
	$q_status = intval($_POST['comment_status']);
	( $q_status == '1' ) ? $status = 'open' : $status = 'closed';
	if( is_string($_POST['posttype']) ) $posttype = $_POST['posttype'];
	
	if ( $status == 'open' ) {
		$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET comment_status = %s WHERE ID = %d", $status, $postid ) );
		die( "jQuery('#commentstatus" . $postid . "').html('<a href=\"javascript:void(0);\" onclick=\"ame_ajax_set_commentstatus(" . $postid . ", 0, \'" . $posttype . "\');return false;\"><img src=\"" . AME_PLUGINFULLURL . "img/" . AME_IMGSET . "comments_open.png\" border=\"0\" alt=\"" . __('Toggle comment status open/closed', 'admin-management-xtended') . "\" title=\"" . __('Toggle comment status open/closed', 'admin-management-xtended') . "\" /></a>');jQuery('#" . $posttype . "-" . $postid . " td, #" . $posttype . "-" . $postid . " th').animate( { backgroundColor: '#EAF3FA' }, 300).animate( { backgroundColor: '#F9F9F9' }, 300).animate( { backgroundColor: '#EAF3FA' }, 300).animate( { backgroundColor: '#F9F9F9' }, 300);" );
	} else {
		$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET comment_status = %s WHERE ID = %d", $status, $postid ) );
		die( "jQuery('#commentstatus" . $postid . "').html('<a href=\"javascript:void(0);\" onclick=\"ame_ajax_set_commentstatus(" . $postid . ", 1, \'" . $posttype . "\');return false;\"><img src=\"" . AME_PLUGINFULLURL . "img/" . AME_IMGSET . "comments_closed.png\" border=\"0\" alt=\"" . __('Toggle comment status open/closed', 'admin-management-xtended') . "\" title=\"" . __('Toggle comment status open/closed', 'admin-management-xtended') . "\" /></a>');jQuery('#" . $posttype . "-" . $postid . " td, #" . $posttype . "-" . $postid . " th').animate( { backgroundColor: '#EAF3FA' }, 300).animate( { backgroundColor: '#F9F9F9' }, 300).animate( { backgroundColor: '#EAF3FA' }, 300).animate( { backgroundColor: '#F9F9F9' }, 300);" );
	}
}

/**
 * SACK response function for saving page order
 *
 * @since 1.1.0
 * @author scripts@schloebe.de
 */
function ame_get_pageorder() {
	global $wpdb, $post;
	$pageorder2 = $_POST['pageordertable2'];
	parse_str( $pageorder2 );
	$orderval = ""; $i = 0;
	foreach( $pageordertable as $value ) {
		$value = intval( substr( $value, 5 ) );
		$has_parent = get_post( $value );
		if( $value != '0' && empty( $has_parent->post_parent ) ) {
			$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET menu_order = %d WHERE ID = %d AND post_type = 'page'", $i, $value ) );
			$i++;
		}
	}
	
	die( "jQuery(\"#ame_ordersave_loader\").html('');" );
	die( "jQuery(\".tablenav\").animate( { backgroundColor: '#E5E5E5' }, 300).animate( { backgroundColor: '#EAF3FA' }, 300).animate( { backgroundColor: '#E5E5E5' }, 300).animate( { backgroundColor: '#EAF3FA' }, 300);" );
}

/**
 * SACK response function for saving post tags
 *
 * @since 1.3.0
 * @author scripts@schloebe.de
 */
function ame_ajax_save_tags() {
	global $wpdb;
	$postid = intval( $_POST['postid'] );
	$ame_tags = $_POST['new_tags'];
	
	$tagarray = explode(",", trim( $ame_tags ));
	wp_set_post_tags($postid, $tagarray);
	unset($GLOBALS['tag_cache']);
	
	$tags = get_the_tags( $postid );
	$ame_post_tags = '';
	if ( !empty( $tags ) ) {
		$out = array();
		foreach ( $tags as $c ) {
			$out[] = '<a href="edit.php?tag=' . $c->slug . '"> ' . wp_specialchars(sanitize_term_field('name', $c->name, $c->term_id, 'post_tag', 'display')) . '</a>';
			$out2[] = wp_specialchars(sanitize_term_field('name', $c->name, $c->term_id, 'post_tag', 'display'));
		}
		$ame_post_tags .= join( ', ', $out );
		$ame_post_tags_plain .= join( ', ', $out2 );
	} else {
		$ame_post_tags .= __('No Tags');
		$ame_post_tags_plain .= '';
	}
	$ame_post_tags .= '&nbsp;<a id="tageditlink' . $postid . '" href="javascript:void(0);" onclick="ame_ajax_form_tags(' . $postid . ', \'' . $ame_post_tags_plain . '\');return false;" title="' . __('Edit') . '"><img src="' . AME_PLUGINFULLURL . 'img/' . AME_IMGSET . 'edit_small.gif" border="0" alt="' . __('Edit') . '" title="' . __('Edit') . '" /></a>';
	die( "jQuery('span#ame_tags" . $postid . "').fadeOut('fast', function() {
		jQuery('span#ame_tags" . $postid . "').html('" . addslashes_gpc( $ame_post_tags ) . "').fadeIn('fast');
	});" );
}

/**
 * SACK response function for saving post categories
 *
 * @since 1.2.0
 * @author scripts@schloebe.de
 */
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

/**
 * SACK response function for saving draft post visibility option
 *
 * @since 0.9
 * @author scripts@schloebe.de
 */
function ame_toggle_showinvisposts() {
	global $wpdb;
	$status = intval($_POST['status']);
	
	update_option("ame_toggle_showinvisposts", $status);
	die( "location.reload();" );
}

/**
 * SACK response function for toggling button image sets option
 *
 * @since 1.3.0
 * @author scripts@schloebe.de
 */
function ame_ajax_toggle_imageset() {
	global $wpdb;
	$setid = intval($_POST['setid']);
	
	update_option("ame_imgset", "set" . $setid);
	die( "location.reload();" );
}

/**
 * SACK response function for saving order input option
 *
 * @since 1.0
 * @author scripts@schloebe.de
 */
function ame_toggle_orderoptions() {
	global $wpdb;
	$status = intval($_POST['status']);
	
	update_option("ame_show_orderoptions", $status);
	die( "location.reload();" );
}

/**
 * SACK response function for displaying the slug edit form inline
 *
 * @since 1.0
 * @author scripts@schloebe.de
 */
function ame_slug_edit() {
	global $wpdb;
	$catid = intval($_POST['category_id']);
	$newslug = $_POST['newslug'];
	if( is_string($_POST['posttype']) ) $posttype = $_POST['posttype'];
	if( $posttype == 'post' ) { $postnumber = '1'; } elseif( $posttype == 'page' ) { $postnumber = '2'; }
	$curpostslug = $wpdb->get_var( $wpdb->prepare( "SELECT post_name FROM $wpdb->posts WHERE ID = %d", $catid ) );
	
	$addHTML = "<tr id='alter" . $posttype . "-" . $catid . "' class='author-other status-publish' valign='middle'><th scope='row' class='check-column'></th><td>" . __('Post') . " #" . $catid . "</td><td colspan='8' align='right'> <input type='text' value='" . $curpostslug . "' size='50' style='font-size:1em;' id='ame_slug" . $catid . "' /> <input value='" . __('Save') . "' class='button-secondary' type='button' style='font-size:1em;' onclick='ame_ajax_slug_save(" . $catid . ", " . $postnumber . ");' /> <input value='" . __('Cancel') . "' class='button' type='button' style='font-size:1em;' onclick='ame_edit_cancel(" . $catid . ");' /></td></tr>";
	die( "jQuery('#" . $posttype . "-" . $catid . "').after( \"" . $addHTML . "\" ); jQuery('#" . $posttype . "-" . $catid . "').hide();" );
}

/**
 * SACK response function for saving page order from direct input
 *
 * @since 1.0
 * @author scripts@schloebe.de
 */
function ame_save_order() {
	global $wpdb;
	$catid = intval( $_POST['category_id'] );
	$neworderid = intval( $_POST['new_orderid'] );
	if( is_string($_POST['posttype']) ) $posttype = $_POST['posttype'];
	
	$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET menu_order = %d WHERE ID = %d", $neworderid, $catid ) );
	die( "jQuery('span#ame_order_loader" . $catid . "').hide(); jQuery('#" . $posttype . "-" . $catid . " td, #" . $posttype . "-" . $catid . " th').animate( { backgroundColor: '#EAF3FA' }, 300).animate( { backgroundColor: '#F9F9F9' }, 300).animate( { backgroundColor: '#EAF3FA' }, 300).animate( { backgroundColor: '#F9F9F9' }, 300);" );
}

/**
 * SACK response function for saving page slug
 *
 * @since 1.0
 * @author scripts@schloebe.de
 */
function ame_save_slug() {
	global $wpdb;
	$catid = intval($_POST['category_id']);
	$new_slug = sanitize_title($_POST['new_slug']);
	if( is_string($_POST['typenumber']) ) $posttype = $_POST['typenumber'];
	$current_page = basename($_SERVER['PHP_SELF'], ".php");
	if( $posttype == '1' ) { $posttype = 'post'; } elseif( $posttype == '2' ) { $posttype = 'page'; }
	
	$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET post_name = %s WHERE ID = %d", $new_slug, $catid ) );
	die( "jQuery('#" . $posttype . "-" . $catid . "').show(); jQuery('#alter" . $posttype . "-" . $catid . "').hide(); jQuery('#" . $posttype . "-" . $catid . " td, #" . $posttype . "-" . $catid . " th').animate( { backgroundColor: '#EAF3FA' }, 300).animate( { backgroundColor: '#F9F9F9' }, 300).animate( { backgroundColor: '#EAF3FA' }, 300).animate( { backgroundColor: '#F9F9F9' }, 300);" );
}

/**
 * SACK response function for saving post//page title
 *
 * @since 0.7
 * @author scripts@schloebe.de
 */
function ame_save_title() {
	global $wpdb;
	$catid = intval($_POST['category_id']);
	$new_title = $_POST['new_title'];
	if( is_string($_POST['posttype']) ) $posttype = $_POST['posttype'];
	
	$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET post_title = %s WHERE ID = %d", $new_title, $catid ) );
	die( "jQuery('a[@href$=post=" . $catid . "]').html('" . $new_title . "'); jQuery('#" . $posttype . "-" . $catid . "').show(); jQuery('#alter" . $posttype . "-" . $catid . "').hide(); jQuery('#" . $posttype . "-" . $catid . " td, #" . $posttype . "-" . $catid . " th').animate( { backgroundColor: '#EAF3FA' }, 300).animate( { backgroundColor: '#F9F9F9' }, 300).animate( { backgroundColor: '#EAF3FA' }, 300).animate( { backgroundColor: '#F9F9F9' }, 300);" );
}

/**
 * SACK response function for saving post/page publication date
 *
 * @since 0.7
 * @author scripts@schloebe.de
 */
function ame_set_date() {
	global $wpdb;
	$catid = intval(substr($_POST['category_id'], 10, 5));
	$newpostdate = get_date_from_gmt( date("Y-m-d H:i:s", strtotime( $_POST['pickedDate'] )) );
	$newpostdate_gmt = get_gmt_from_date( $newpostdate );
	if( is_string($_POST['posttype']) ) $posttype = $_POST['posttype'];
	
	$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET post_date = %s WHERE ID = %d", $newpostdate, $catid ) );
	$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET post_date_gmt = %s WHERE ID = %d", $newpostdate_gmt, $catid ) );
	if( strtotime( current_time(mysql) ) < strtotime( $newpostdate ) ) {
		$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET post_status = 'future' WHERE ID = %d", $catid ) );
		die( "jQuery('#" . $posttype . "-" . $catid . " abbr').html('" . date(__('Y/m/d'), strtotime( $newpostdate ) ) . "'); jQuery('#" . $posttype . "-" . $catid . "').removeClass('status-publish').addClass('status-future'); jQuery('#" . $posttype . "-" . $catid . " td, #" . $posttype . "-" . $catid . " th').animate( { backgroundColor: '#EAF3FA' }, 300).animate( { backgroundColor: '#F9F9F9' }, 300).animate( { backgroundColor: '#EAF3FA' }, 300).animate( { backgroundColor: '#F9F9F9' }, 300);" );
	} elseif ( strtotime( current_time(mysql) ) > strtotime( $newpostdate ) ) {
		$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET post_status = 'publish' WHERE ID = %d", $catid ) );
		die( "jQuery('#" . $posttype . "-" . $catid . " abbr').html('" . date(__('Y/m/d'), strtotime( $newpostdate ) ) . "'); jQuery('#" . $posttype . "-" . $catid . "').removeClass('status-future').addClass('status-publish'); jQuery('#" . $posttype . "-" . $catid . " td, #" . $posttype . "-" . $catid . " th').animate( { backgroundColor: '#EAF3FA' }, 300).animate( { backgroundColor: '#F9F9F9' }, 300).animate( { backgroundColor: '#EAF3FA' }, 300).animate( { backgroundColor: '#F9F9F9' }, 300);" );
	}
}

/**
 * SACK response function for toggling post/page visibility
 *
 * @since 0.7
 * @author scripts@schloebe.de
 */
function ame_toggle_visibility() {
	global $wpdb;
	$catid = intval($_POST['category_id']);
	if( is_string($_POST['vis_status']) ) $status = $_POST['vis_status'];
	if( is_string($_POST['posttype']) ) $posttype = $_POST['posttype'];
	
	if ( $status == 'publish' ) {
		$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET post_status = %s WHERE ID = %d", $status, $catid ) );
		die( "jQuery('#visicon" . $catid . "').html('<a href=\"javascript:void(0);\" onclick=\"ame_ajax_set_visibility(" . $catid . ", \'draft\', \'" . $posttype . "\');return false;\"><img src=\"" . AME_PLUGINFULLURL . "img/" . AME_IMGSET . "visible.png\" border=\"0\" alt=\"" . __('Toggle visibility', 'admin-management-xtended') . "\" title=\"" . __('Toggle visibility', 'admin-management-xtended') . "\" /></a>');jQuery('#" . $posttype . "-" . $catid . " td, #" . $posttype . "-" . $catid . " th').animate( { backgroundColor: '#EAF3FA' }, 300).animate( { backgroundColor: '#F9F9F9' }, 300).animate( { backgroundColor: '#EAF3FA' }, 300).animate( { backgroundColor: '#F9F9F9' }, 300);jQuery('#" . $posttype . "-" . $catid . "').removeClass('status-draft').addClass('status-publish');" );
	} else {
		$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET post_status = %s WHERE ID = %d", $status, $catid ) );
		die( "jQuery('#visicon$catid').html('<a href=\"javascript:void(0);\" onclick=\"ame_ajax_set_visibility(" . $catid . ", \'publish\', \'" . $posttype . "\');return false;\"><img src=\"" . AME_PLUGINFULLURL . "img/" . AME_IMGSET . "hidden.png\" border=\"0\" alt=\"" . __('Toggle visibility', 'admin-management-xtended') . "\" title=\"" . __('Toggle visibility', 'admin-management-xtended') . "\" /></a>');jQuery('#" . $posttype . "-" . $catid . " td, #" . $posttype . "-" . $catid . " th').animate( { backgroundColor: '#EAF3FA' }, 300).animate( { backgroundColor: '#F9F9F9' }, 300).animate( { backgroundColor: '#EAF3FA' }, 300).animate( { backgroundColor: '#F9F9F9' }, 300);jQuery('#" . $posttype . "-" . $catid . "').removeClass('status-publish').addClass('status-draft');" );
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
	add_action('wp_ajax_ame_ajax_save_tags', 'ame_ajax_save_tags' );
	add_action('wp_ajax_ame_ajax_toggle_imageset', 'ame_ajax_toggle_imageset' );
	add_action('wp_ajax_ame_ajax_save_mediadesc', 'ame_ajax_save_mediadesc' );
}



/* ************************************************ */
/* Write JS into our admin header					*/
/* ************************************************ */

/**
 * Writes the javascript stuff into page header needed for the JS popout calendar
 *
 * @since 0.7
 * @author scripts@schloebe.de
 */
function ame_js_jquery_datepicker_header() {
	$current_page = basename($_SERVER['PHP_SELF'], ".php");
	$posttype = "";
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
    		jQuery(\"#ame_ordersave_loader\").html(\"<img src='" . AME_PLUGINFULLURL . "img/" . AME_IMGSET . "loader2.gif' border='0' alt='' align='absmiddle' /> | \");
    		ame_ajax_get_pageorder( jQuery.tableDnD.serialize() );
    	}
    });
});
//]]>
</script>
\n";
	}
	if( $current_page == 'edit' ) {
	echo "<script type=\"text/javascript\" charset=\"utf-8\">
//<![CDATA[
function ame_setupSuggest( ame_suggestid ) {
	jQuery('#ame-new-tags' + ame_suggestid).suggest( '" . get_bloginfo('wpurl') . "/wp-admin/admin-ajax.php?action=ajax-tag-search', { delay: 500, minchars: 2, multiple: true, multipleSep: \", \" } );
}
//]]>
</script>\n";
	}
	echo "<link rel='stylesheet' href='" . AME_PLUGINFULLURL . "css/datePicker.css' type='text/css' />\n";
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

/**
 * Writes javascript stuff into page header needed for the plugin and calls for the SACK library
 *
 * @since 0.7
 * @author scripts@schloebe.de
 */
function ame_js_admin_header() {
	wp_print_scripts( array( 'sack' ));

	$current_page = basename($_SERVER['PHP_SELF'], ".php");
	if( $current_page == 'edit' ) { $posttype = 'post'; } elseif( $current_page == 'edit-pages' ) { $posttype = 'page'; }
?>
<script type="text/javascript">
//<![CDATA[
ameAjaxL10n = {
	blogUrl: "<?php bloginfo( 'wpurl' ); ?>", pluginPath: "<?php echo AME_PLUGINFULLDIR; ?>", pluginUrl: "<?php echo AME_PLUGINFULLURL; ?>", requestUrl: "<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php", imgUrl: "<?php echo AME_PLUGINFULLURL; ?>img/<?php echo AME_IMGSET ?>", Post: "<?php _e("Post"); ?>", Save: "<?php _e("Save"); ?>", Cancel: "<?php _e("Cancel"); ?>", postType: "<?php echo $posttype; ?>", pleaseWait: "<?php _e("Please wait..."); ?>"
}
//]]>
</script>
<?php
}

/**
 * Writes the css stuff into page header needed for the plugin
 *
 * @since 1.2.0
 * @author scripts@schloebe.de
 */
function ame_css_admin_header() {
	echo '<link rel="stylesheet" type="text/css" href="' . AME_PLUGINFULLURL . 'css/styles.css?ver=' . AME_VERSION . '" />' . "\n";
	echo '
<style type="text/css">
#TB_window #TB_title {
	font-weight: 700;
	color: #D7D7D7;
	background-color: #222;
}
</style>' . "\n";
}

/**
 * Returns the output for the 'change image set' link
 *
 * @since 1.3.0
 * @author scripts@schloebe.de
 *
 * @return string
 */
function ame_changeImgSet() {
	if( get_option("ame_imgset") == 'set1' ) { $imgset = '2'; } elseif( get_option("ame_imgset") == 'set2' ) { $imgset = '1'; }
	return ' <a tip="' . __('Change image set', 'admin-management-xtended') . '" href="javascript:void(0);" onclick="ame_ajax_toggle_imageset(' . $imgset . ');return false;"><img src="' . AME_PLUGINFULLURL . 'img/' . AME_IMGSET . 'changeimgset.gif" border="0" alt="' . __('Change image set', 'admin-management-xtended') . '" title="' . __('Change image set', 'admin-management-xtended') . '" /></a>';
}

/**
 * Writes a version metatag to the fe page for support info
 *
 * @since 1.3.0
 * @author scripts@schloebe.de
 */
function ame_feheader_insert()
{
	echo "<meta name='AMEWP' content='" . AME_VERSION . "' />\n";
}

$current_page = basename($_SERVER['PHP_SELF'], ".php");
if( function_exists('add_action') ) {
	$wp_version = (!isset($wp_version)) ? get_bloginfo('version') : $wp_version;
	add_action('wp_head', 'ame_feheader_insert', 1);
	if( $current_page == 'edit' || $current_page == 'edit-pages' ) {
		$cur_locale = get_locale();
		add_action('admin_head', 'ame_css_admin_header' );
		add_action('admin_print_scripts', 'ame_js_admin_header' );
		add_action('admin_head', 'ame_js_jquery_datepicker_header' );
		add_action('admin_head', wp_enqueue_script( 'date', AME_PLUGINFULLURL . "js/jquery-addons/date.js", array('jquery'), AME_VERSION ) );
		add_action('admin_head', wp_enqueue_script( 'datePicker', AME_PLUGINFULLURL . "js/jquery-addons/jquery.datePicker.js", array('jquery'), AME_VERSION ) );
		add_action('admin_head', wp_enqueue_script( 'ame_miscsrcipts', AME_PLUGINFULLURL . "js/functions.js", array('sack'), AME_VERSION ) );
		if( ame_locale_exists() === true ) {
			add_action('admin_head', wp_enqueue_script( 'localdate', AME_PLUGINFULLURL . "js/jquery-addons/date_" . $cur_locale . ".js", array('jquery'), AME_VERSION ) );
		}
		if( $current_page == 'edit-pages' && get_option('ame_show_orderoptions') == '2' ) {
			add_action('admin_head', wp_enqueue_script( 'tablednd', AME_PLUGINFULLURL . "js/jquery-addons/jquery.tablednd.js", array('jquery'), AME_VERSION ) );
		}
		add_action('admin_head', wp_enqueue_script( array('thickbox') ) );
		if ( version_compare( $wp_version, '2.5.9', '>=' ) ) {
			add_action('admin_head', wp_enqueue_style( array('thickbox') ) );
		}
	}
	if( $current_page == 'upload' ) {
		add_action('admin_print_scripts', 'ame_js_admin_header' );
		add_action('admin_head', wp_enqueue_script( 'ame_miscsrcipts', AME_PLUGINFULLURL . "js/functions.js", array('sack'), AME_VERSION ) );
	}
	if( $current_page == 'edit' ) {
		/**
 		* @since 1.6.0
 		*/
		add_action('admin_head', wp_enqueue_script( array('suggest') ) );
	}
}
?>
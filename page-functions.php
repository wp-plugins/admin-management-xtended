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

load_plugin_textdomain('admin-management-xtended', PLUGINDIR . '/admin-management-xtended');

/* ************************************************ */
/* Adding the columns and data						*/
/* ************************************************ */

function ame_column_page_actions( $defaults ) {
	$wp_version = (!isset($wp_version)) ? get_bloginfo('version') : $wp_version;
	
    $defaults['ame_page_actions'] = '<abbr style="cursor:help;" title="' . __('Enhanced by Admin Management Xtended Plugin', 'admin-management-xtended') . '">' . __('Actions') . '</abbr>';
    return $defaults;
}

function ame_column_page_order( $defaults ) {
	$wp_version = (!isset($wp_version)) ? get_bloginfo('version') : $wp_version;
	
    $defaults['ame_page_order'] = '<abbr style="cursor:help;" title="' . __('Enhanced by Admin Management Xtended Plugin', 'admin-management-xtended') . '">' . __('Page Order') . '</abbr>';
    return $defaults;
}

function ame_custom_column_page_actions( $ame_column_name, $ame_id ) {
	global $wpdb, $locale;
    if( $ame_column_name == 'ame_page_actions' ) {
    	$post_status = get_post_status($ame_id);
    	#$q_post_status_datum = get_post($ame_id);
    	#$post_status_datum = strtotime( $q_post_status_datum->post_date );
    	echo '<div style="width:91px;" class="ame_options">';
    	if ( $post_status == 'publish' ) {
    		// Visibility icon
    		echo '<div id="visicon' . $ame_id . '" style="padding:1px;float:left;"><a href="javascript:void(0);" onclick="ame_ajax_set_visibility(' . $ame_id . ', \'draft\', \'page\');return false;"><img src="' . get_bloginfo('wpurl') . '/' . PLUGINDIR . AME_PLUGINPATH . 'img/visible.png" border="0" alt="' . __('Toggle visibility', 'admin-management-xtended') . '" title="' . __('Toggle visibility', 'admin-management-xtended') . '" /></a></div> ';
    	} else {
    		// Visibility icon
    		echo '<div id="visicon' . $ame_id . '" style="padding:1px;float:left;"><a href="javascript:void(0);" onclick="ame_ajax_set_visibility(' . $ame_id . ', \'publish\', \'page\');return false;"><img src="' . get_bloginfo('wpurl') . '/' . PLUGINDIR . AME_PLUGINPATH . 'img/hidden.png" border="0" alt="' . __('Toggle visibility', 'admin-management-xtended') . '" title="' . __('Toggle visibility', 'admin-management-xtended') . '" /></a></div>';
    	}
    	echo '</div>';
    	// Date icon
    	echo '<div id="date' . $ame_id . '" style="padding:1px;float:left;"><a href="javascript:void(0);" class="date-pick" id="datepicker' . $ame_id . '"><img src="' . get_bloginfo('wpurl') . '/' . PLUGINDIR . AME_PLUGINPATH . 'img/date.png" border="0" alt="' . __('Change Publication Date', 'admin-management-xtended') . '" title="' . __('Change Publication Date', 'admin-management-xtended') . '" /></a></div> ';
    	// Title edit icon
    	$q_post_title = get_post($ame_id);
    	$post_title = attribute_escape( $q_post_title->post_title );
    	echo '<div id="title' . $ame_id . '" style="padding:1px;float:left;"><a href="javascript:void(0);" id="titledit' . $ame_id . '" onclick="ame_title_edit(' . $ame_id . ', \'' . wp_specialchars( $post_title ) . '\', \'page\');"><img src="' . get_bloginfo('wpurl') . '/' . PLUGINDIR . AME_PLUGINPATH . 'img/page_white_edit.png" border="0" alt="' . __('Change Page Title', 'admin-management-xtended') . '" title="' . __('Change Page Title', 'admin-management-xtended') . '" /></a></div> ';
		// Slug edit icon
    	echo '<div id="slug' . $ame_id . '" style="padding:1px;float:left;"><a href="javascript:void(0);" id="slugedit' . $ame_id . '" onclick="ame_slug_edit(' . $ame_id . ', \'page\');"><img src="' . get_bloginfo('wpurl') . '/' . PLUGINDIR . AME_PLUGINPATH . 'img/slug_edit.png" border="0" alt="' . __('Edit Page Slug', 'admin-management-xtended') . '" title="' . __('Edit Page Slug', 'admin-management-xtended') . '" /></a></div>';
    	// Comment open/closed status icon
    	$q_commentstatus = get_post($ame_id);
    	$comment_status = $q_commentstatus->comment_status;
    	if( $comment_status == 'open' ) { $c_status = 0; $c_img = '_open'; } else { $c_status = 1; $c_img = '_closed'; }
    	echo '<div id="commentstatus' . $ame_id . '" style="padding:1px;float:left;"><a tip="' . __('Toggle comment status open/closed', 'admin-management-xtended') . '" href="javascript:void(0);" onclick="ame_ajax_set_commentstatus(' . $ame_id . ', ' . $c_status . ', \'page\');return false;"><img src="' . get_bloginfo('wpurl') . '/' . PLUGINDIR . AME_PLUGINPATH . 'img/comments' . $c_img . '.png" border="0" alt="' . __('Toggle comment status open/closed', 'admin-management-xtended') . '" title="' . __('Toggle comment status open/closed', 'admin-management-xtended') . '" /></a></div> ';
    }
}

function ame_custom_column_page_order( $ame_column_name, $ame_id ) {
	global $wpdb;
    if( $ame_column_name == 'ame_page_order' ) {
    	$q_post_order = get_post( $ame_id );
    	echo '<div style="width:75px;" class="ame_options">';
    	echo '<input type="text" value="' . $q_post_order->menu_order . '" size="3" maxlength="3" style="font-size:1em;" id="ame_pageorder' . $ame_id . '" onchange="ame_ajax_order_save(' . $ame_id . ', \'page\');" /> <span id="ame_order_loader' . $ame_id . '" style="display:none;"><img src="' . get_bloginfo('wpurl') . '/' . PLUGINDIR . AME_PLUGINPATH . 'img/loader.gif" border="0" alt="" /></span>';
    	echo '</div>';
    }
}

add_action('manage_pages_custom_column', 'ame_custom_column_page_actions', 500, 2);
add_filter('manage_pages_columns', 'ame_column_page_actions', 500, 2);
if ( get_option('ame_show_orderoptions') == '1' ) {
	add_action('manage_pages_custom_column', 'ame_custom_column_page_order', 500, 2);
	add_filter('manage_pages_columns', 'ame_column_page_order', 500, 2);
}
?>
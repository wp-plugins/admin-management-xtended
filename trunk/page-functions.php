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
/* Adding the columns and data						*/
/* ************************************************ */

function ame_column_page_actions( $defaults ) {
	$wp_version = (!isset($wp_version)) ? get_bloginfo('version') : $wp_version;
	
    $defaults['ame_page_actions'] = __('Actions');
    return $defaults;
}

function ame_custom_column_page_actions( $ame_column_name, $ame_id ) {
	global $wpdb, $locale;
    if( $ame_column_name == 'ame_page_actions' ) {
    	$post_status = get_post_status($ame_id);
    	#$q_post_status_datum = get_post($ame_id);
    	#$post_status_datum = strtotime( $q_post_status_datum->post_date );
    	echo '<div style="width:60px;">';
    	if ( $post_status == 'publish' ) {
    		// Visibility icon
    		echo '<div id="visicon' . $ame_id . '" style="padding:1px;float:left;"><a href="javascript:void(0);" onclick="ame_ajax_set_visibility(' . $ame_id . ', \'draft\', \'page\');return false;"><img src="' . get_bloginfo('wpurl') . '/' . PLUGINDIR . AME_PLUGINPATH . 'img/visible.png" border="0" alt="' . __('Toggle visibility', 'admin-management-xtended') . '" title="' . __('Toggle visibility', 'admin-management-xtended') . '" /></a></div> ';
    		// Date icon
    		echo '<div id="date' . $ame_id . '" style="padding:1px;float:left;"><a href="javascript:void(0);" class="date-pick" id="datepicker' . $ame_id . '"><img src="' . get_bloginfo('wpurl') . '/' . PLUGINDIR . AME_PLUGINPATH . 'img/date.png" border="0" alt="' . __('Change Publication Date', 'admin-management-xtended') . '" title="' . __('Change Publication Date', 'admin-management-xtended') . '" /></a></div> ';
    		// Title edit icon
    		$q_post_title = get_post($ame_id);
    		$post_title = attribute_escape( $q_post_title->post_title );
    		echo '<div id="title' . $ame_id . '" style="padding:1px;float:left;"><a href="javascript:void(0);" id="titledit' . $ame_id . '" onclick="ame_title_edit(' . $ame_id . ', \'' . htmlentities( $post_title ) . '\', \'page\');"><img src="' . get_bloginfo('wpurl') . '/' . PLUGINDIR . AME_PLUGINPATH . 'img/page_white_edit.png" border="0" alt="' . __('Change Page Title', 'admin-management-xtended') . '" title="' . __('Change Page Title', 'admin-management-xtended') . '" /></a></div> ';
    		// Author edit icon
    		//echo '<div id="author' . $ame_id . '" style="padding:1px;float:left;"><a href="javascript:void(0);" id="authoredit' . $ame_id . '" onclick="ame_author_edit(' . $ame_id . ');"><img src="' . get_bloginfo('wpurl') . '/' . PLUGINDIR . AME_PLUGINPATH . 'img/user_edit.png" border="0" alt="" /></a></div>';
    	} else {
    		// Visibility icon
    		echo '<div id="visicon' . $ame_id . '" style="padding:1px;float:left;"><a href="javascript:void(0);" onclick="ame_ajax_set_visibility(' . $ame_id . ', \'publish\', \'page\');return false;"><img src="' . get_bloginfo('wpurl') . '/' . PLUGINDIR . AME_PLUGINPATH . 'img/hidden.png" border="0" alt="' . __('Toggle visibility', 'admin-management-xtended') . '" title="' . __('Toggle visibility', 'admin-management-xtended') . '" /></a></div>';
    		// Date icon
    		echo '<div id="date' . $ame_id . '" style="padding:1px;float:left;"><a href="javascript:void(0);" class="date-pick" id="datepicker' . $ame_id . '"><img src="' . get_bloginfo('wpurl') . '/' . PLUGINDIR . AME_PLUGINPATH . 'img/date.png" border="0" alt="' . __('Change Publication Date', 'admin-management-xtended') . '" title="' . __('Change Publication Date', 'admin-management-xtended') . '" /></a></div>';
    		// Title edit icon
    		$q_post_title = get_post($ame_id);
    		$post_title = attribute_escape( $q_post_title->post_title );
    		echo '<div id="title' . $ame_id . '" style="padding:1px;float:left;"><a href="javascript:void(0);" id="titledit' . $ame_id . '" onclick="ame_title_edit(' . $ame_id . ', \'' . htmlentities( $post_title ) . '\', \'page\');"><img src="' . get_bloginfo('wpurl') . '/' . PLUGINDIR . AME_PLUGINPATH . 'img/page_white_edit.png" border="0" alt="' . __('Change Page Title', 'admin-management-xtended') . '" title="' . __('Change Page Title', 'admin-management-xtended') . '" /></a></div>';
    	}
    	echo '</div>';
    }
}

add_action('manage_pages_custom_column', 'ame_custom_column_page_actions', 500, 2);
add_filter('manage_pages_columns', 'ame_column_page_actions', 500, 2);
?>
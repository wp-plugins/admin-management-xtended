<?php
/**
 * Media-related functions
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
/* Adding the columns and data						*/
/* ************************************************ */

/**
 * Adds a new 'Media Order' column to the media management view
 *
 * @since 1.4
 * @author scripts@schloebe.de
 *
 * @param array
 * @return array
 */
function ame_column_media_order( $defaults ) {
	$wp_version = (!isset($wp_version)) ? get_bloginfo('version') : $wp_version;
	
    $defaults['ame_media_order'] = '<abbr style="cursor:help;" title="' . __('Enhanced by Admin Management Xtended Plugin', 'admin-management-xtended') . ' ' . get_option("ame_version") . '">' . __('Media Order', 'admin-management-xtended') . '</abbr>';
    return $defaults;
}

/**
 * Adds content to the new 'Media Order' column on the media management view
 *
 * @since 1.4
 * @author scripts@schloebe.de
 *
 * @param string
 * @param int
 */
function ame_custom_column_media_order( $ame_column_name, $ame_id ) {
	global $wpdb;
    if( $ame_column_name == 'ame_media_order' ) {
    	$q_media_order = get_post( $ame_id );
    	echo '<div style="width:75px;" class="ame_options">';
    	echo '<input type="text" value="' . $q_media_order->menu_order . '" size="3" maxlength="3" style="font-size:1em;" id="ame_postorder' . $ame_id . '" onchange="ame_ajax_order_save(' . $ame_id . ', \'post\');" /> <span id="ame_order_loader' . $ame_id . '" style="display:none;"><img src="' . get_bloginfo('wpurl') . '/' . PLUGINDIR . AME_PLUGINPATH . 'img/' . AME_IMGSET . 'loader.gif" border="0" alt="" /></span>';
    	echo '</div>';
    }
}

add_action('manage_media_custom_column', 'ame_custom_column_media_order', 500, 2);
add_filter('manage_media_columns', 'ame_column_media_order', 500, 2);
?>
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
/* Some stuff for editing categories inline			*/
/* ************************************************ */

function ame_column_category_actions( $defaults ) {
	$wp_version = (!isset($wp_version)) ? get_bloginfo('version') : $wp_version;
	
	unset($defaults['categories']);
	if( $defaults['tags'] ) {
		$defaults['ame_cat_actions'] = '<abbr style="cursor:help;" title="' . __('Enhanced by Admin Management Xtended Plugin', 'admin-management-xtended') . '">' . __('Categories') . '</abbr>';
	}
    return $defaults;
}

function ame_custom_column_category_actions( $ame_column_name, $ame_id ) {
	global $wpdb, $locale;
    if( $ame_column_name == 'ame_cat_actions' && current_user_can( 'edit_post', $ame_id ) ) {
    	$categories = get_the_category( $ame_id );
    	$post_cats = "";
		if ( !empty( $categories ) ) {
			$out = array();
			foreach ( $categories as $c ) {
				$out[] = "<a href='edit.php?category_name=$c->slug'> " . wp_specialchars(sanitize_term_field('name', $c->name, $c->term_id, 'category', 'display')) . "</a>";
			}
			$ame_post_cats .= join( ', ', $out );
		} else {
			_e('Uncategorized');
		}
			?>
<div id="categorychoosewrap<?php echo $ame_id; ?>" style="width:300px;height:165px;overflow:auto;display:none;">
<div id="categorychoose<?php echo $ame_id; ?>" class="categorydiv">
	<ul id="categorychecklist" class="list:category categorychecklist form-no-clear" style="height:165px;overflow:auto;">
		<?php wp_category_checklist($ame_id); ?>
	</ul>
	<div style="text-align:center;"><input type="button" value="<?php _e('Save') ?>" class="button-secondary" onclick="ame_ajax_save_categories(<?php echo $ame_id; ?>);return false;" />&nbsp;<input type="button" value="<?php _e('Cancel') ?>" class="button" onclick="tb_remove();" /></div>
</div>
</div>
			<?php
		echo '<span id="ame_category' . $ame_id . '">' . $ame_post_cats . '</span>&nbsp;<a class="thickbox" id="thickboxlink' . $ame_id . '" href="#TB_inline?height=200&width=300&inlineId=categorychoosewrap' . $ame_id . '&modal=true" title="' . __('Edit Categories for Post', 'admin-management-xtended') . ' #' . $ame_id . '"><img src="' . get_bloginfo('wpurl') . '/' . PLUGINDIR . AME_PLUGINPATH . 'img/edit_small.gif" border="0" alt="' . __('Edit Categories', 'admin-management-xtended') . ' ' . $ame_id . '" title="' . __('Edit Categories for Post', 'admin-management-xtended') . ' ' . $ame_id . '" /></a>';
    }
}

add_action('manage_posts_custom_column', 'ame_custom_column_category_actions', 1, 2);
add_filter('manage_posts_columns', 'ame_column_category_actions', 1, 1);



/* ************************************************ */
/* Adding the columns and data						*/
/* ************************************************ */

function ame_column_post_actions( $defaults ) {
	$wp_version = (!isset($wp_version)) ? get_bloginfo('version') : $wp_version;
	
	$defaults['ame_post_actions'] = '<abbr style="cursor:help;" title="' . __('Enhanced by Admin Management Xtended Plugin', 'admin-management-xtended') . '">' . __('Actions') . '</abbr>';
    return $defaults;
}

function ame_custom_column_post_actions( $ame_column_name, $ame_id ) {
	global $wpdb, $locale;
    if( $ame_column_name == 'ame_post_actions' && current_user_can( 'edit_post', $ame_id ) ) {
    	$post_status = get_post_status($ame_id); $q_post = get_post($ame_id);
    	echo '<div style="width:91px;" class="ame_options">';
    	if ( $post_status == 'publish' ) {
    		// Visibility icon
    		echo '<div id="visicon' . $ame_id . '" style="padding:1px;float:left;"><a tip="' . __('Toggle visibility', 'admin-management-xtended') . '" href="javascript:void(0);" onclick="ame_ajax_set_visibility(' . $ame_id . ', \'draft\', \'post\');return false;"><img src="' . get_bloginfo('wpurl') . '/' . PLUGINDIR . AME_PLUGINPATH . 'img/visible.png" border="0" alt="' . __('Toggle visibility', 'admin-management-xtended') . '" title="' . __('Toggle visibility', 'admin-management-xtended') . '" /></a></div> ';
    	} else {
    		// Visibility icon
    		echo '<div id="visicon' . $ame_id . '" style="padding:1px;float:left;"><a href="javascript:void(0);" onclick="ame_ajax_set_visibility(' . $ame_id . ', \'publish\', \'post\');return false;"><img src="' . get_bloginfo('wpurl') . '/' . PLUGINDIR . AME_PLUGINPATH . 'img/hidden.png" border="0" alt="' . __('Toggle visibility', 'admin-management-xtended') . '" title="' . __('Toggle visibility', 'admin-management-xtended') . '" /></a></div>';
    	}
    	echo '</div>';
    	// Date icon
    	echo '<div id="date' . $ame_id . '" style="padding:1px;float:left;"><a href="javascript:void(0);" class="date-pick" id="datepicker' . $ame_id . '"><img src="' . get_bloginfo('wpurl') . '/' . PLUGINDIR . AME_PLUGINPATH . 'img/date.png" border="0" alt="' . __('Change Publication Date', 'admin-management-xtended') . '" title="' . __('Change Publication Date', 'admin-management-xtended') . '" /></a></div>';
		// Title edit icon
    	$q_post_title = get_post($ame_id);
    	$post_title = attribute_escape( $q_post_title->post_title );
    	echo '<div id="title' . $ame_id . '" style="padding:1px;float:left;"><a href="javascript:void(0);" id="titledit' . $ame_id . '" onclick="ame_title_edit(' . $ame_id . ', \'' . wp_specialchars( $post_title ) . '\', \'post\');"><img src="' . get_bloginfo('wpurl') . '/' . PLUGINDIR . AME_PLUGINPATH . 'img/page_white_edit.png" border="0" alt="' . __('Change Post Title', 'admin-management-xtended') . '" title="' . __('Change Post Title', 'admin-management-xtended') . '" /></a></div>';
    	// Slug edit icon
    	echo '<div id="slug' . $ame_id . '" style="padding:1px;float:left;"><a href="javascript:void(0);" id="slugedit' . $ame_id . '" onclick="ame_slug_edit(' . $ame_id . ', \'post\');"><img src="' . get_bloginfo('wpurl') . '/' . PLUGINDIR . AME_PLUGINPATH . 'img/slug_edit.png" border="0" alt="' . __('Edit Post Slug', 'admin-management-xtended') . '" title="' . __('Edit Post Slug', 'admin-management-xtended') . '" /></a></div>';
    	// Comment open/closed status icon
    	$q_commentstatus = get_post($ame_id);
    	$comment_status = $q_commentstatus->comment_status;
    	if( $comment_status == 'open' ) { $c_status = 0; $c_img = '_open'; } else { $c_status = 1; $c_img = '_closed'; }
    	echo '<div id="commentstatus' . $ame_id . '" style="padding:1px;float:left;"><a tip="' . __('Toggle comment status open/closed', 'admin-management-xtended') . '" href="javascript:void(0);" onclick="ame_ajax_set_commentstatus(' . $ame_id . ', ' . $c_status . ', \'post\');return false;"><img src="' . get_bloginfo('wpurl') . '/' . PLUGINDIR . AME_PLUGINPATH . 'img/comments' . $c_img . '.png" border="0" alt="' . __('Toggle comment status open/closed', 'admin-management-xtended') . '" title="' . __('Toggle comment status open/closed', 'admin-management-xtended') . '" /></a></div> ';
    }
}

add_action('manage_posts_custom_column', 'ame_custom_column_post_actions', 500, 2);
add_filter('manage_posts_columns', 'ame_column_post_actions', 500, 2);
?>
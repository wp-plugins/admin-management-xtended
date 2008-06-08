<?php
/**
 * The main plugin file
 *
 * @package AdminManagamentXtended
 */
 
/*
Plugin Name: Admin Management Xtended
Version: 1.3.0
Plugin URI: http://www.schloebe.de/wordpress/admin-management-xtended-plugin/
Description: Extends admin functionalities by introducing: <abbr title="">toggling post/page visibility inline</abbr>, <abbr title="">changing page order with drag'n'drop</abbr>, <abbr title="">inline category management</abbr>, <abbr title="">inline tag management</abbr>, <abbr title="">changing publication date inline</abbr>, <abbr title="">changing post slug inline</abbr>, <abbr title="">toggling comment status open/closed</abbr>, <abbr title="">hide draft posts</abbr>
Author: Oliver Schl&ouml;be
Author URI: http://www.schloebe.de/
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
/* General stuff									*/
/* ************************************************ */

/**
 * Define the plugin version
 */
define("AME_VERSION", "1.3.0");

/**
 * Define the plugin path slug
 */
define("AME_PLUGINPATH", "/admin-management-xtended/");

/**
 * Define the plugin image set
 */
define("AME_IMGSET", get_option("ame_imgset") . "/" );

/**
 * Load all the l18n data from languages path
 */
load_plugin_textdomain('admin-management-xtended', PLUGINDIR . AME_PLUGINPATH);



/* ************************************************ */
/* Includes											*/
/* ************************************************ */

set_include_path( dirname(__FILE__) . PATH_SEPARATOR . get_include_path() );

/** 
 * This file holds all of the general information and functions
 */
require_once('general-functions.php');

/** 
 * This file holds all of the post functions
 */
require_once('post-functions.php');

/** 
 * This file holds all of the page functions
 */
require_once('page-functions.php');
restore_include_path();



/* ************************************************ */
/* User Option stuff								*/
/* ************************************************ */

if( !get_option("ame_show_orderoptions") ) {
	add_option("ame_show_orderoptions", "1");
}
if( !get_option("ame_toggle_showinvisposts") ) {
	add_option("ame_toggle_showinvisposts", "1");
}
if( !get_option("ame_version") ) {
	add_option("ame_version", AME_VERSION);
}
if( !get_option("ame_imgset") ) {
	add_option("ame_imgset", 'set1');
}
update_option("ame_version", AME_VERSION);
?>
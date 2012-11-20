<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Winans Creative 2009, Intelligent Spark 2010, iserv.ch GmbH 2010
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_iso_products']['useFeed'] 				= array('Use in Feed', 'Check this box if you wish to set and use this product data in feeds.');
$GLOBALS['TL_LANG']['tl_iso_products']['gid_condition'] 		= array('Condition', 'Please enter the condition of the product.');
$GLOBALS['TL_LANG']['tl_iso_products']['gid_availability'] 		= array('Availability', 'Please enter the availability of the product.');
$GLOBALS['TL_LANG']['tl_iso_products']['gid_brand'] 			= array('Brand', 'Please enter the brand/manufacturer of the product. Not required for custom products, books, or media, or if you are providing a GTIN and MPN.');
$GLOBALS['TL_LANG']['tl_iso_products']['gid_gtin'] 				= array('Global Trade Item Number', 'Please enter the GTIN for the product, an 8-, 12-, or 13-digit number (UPC, EAN, JAN, or ISBN). Not required for custom products, apparel, or media, or if you are providing Brand and MPN.');
$GLOBALS['TL_LANG']['tl_iso_products']['gid_mpn'] 				= array('Manufacturer Part Number', 'Please enter the MPN for the product. Not required for custom products, apparel, or media, or if you are providing Brand and GTIN');
$GLOBALS['TL_LANG']['tl_iso_products']['gid_google_product_category'] = array('Google Product Taxonomy', 'Please select from the predefined values from Google\'s product taxonomy.  Required for all items that belong to the \'Apparel and Accessories\', \'Media\', and \'Software\' categories. This attribute should be included in addition to, not as a replacement for, the \'Your Product Type\' attribute.');
$GLOBALS['TL_LANG']['tl_iso_products']['gid_product_type'] = array('Your Product Type(s)', 'This attribute contains the category of the product according to your taxonomy. As with the \'Google Product Category\' attribute, include the category with full “breadcrumb” information. For example, \'Books > Non-Fiction > Sports > Baseball\' is better than just \'Baseball\'. Any separator such as > or / may be used.');

/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_iso_products']['new'] 			= 'New';
$GLOBALS['TL_LANG']['tl_iso_products']['used'] 			= 'Used';
$GLOBALS['TL_LANG']['tl_iso_products']['refurbished'] 	= 'Refurbished';
$GLOBALS['TL_LANG']['tl_iso_products']['in stock'] 		= 'In Stock';
$GLOBALS['TL_LANG']['tl_iso_products']['available for order'] = 'Available For Order';
$GLOBALS['TL_LANG']['tl_iso_products']['out of stock'] 	= 'Out of Stock';
$GLOBALS['TL_LANG']['tl_iso_products']['preorder'] 		= 'Preorder';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_products']['cache_feeds'] = 'Refresh feed cache';
$GLOBALS['TL_LANG']['tl_iso_products']['generate_feeds'] = 'Regenerate feed';

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_products']['feed_legend:hide']	= "Feed Settings";
$GLOBALS['TL_LANG']['tl_iso_products']['feed_legend']		= "Feed Settings";

?>
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
 * Table tl_iso_config 
 */
$GLOBALS['TL_DCA']['tl_iso_config']['config']['onsubmit_callback'][] = array('IsotopeFeeds', 'generateFeeds');
 
$GLOBALS['TL_DCA']['tl_iso_config']['palettes']['__selector__'][] = 'addFeed';
$GLOBALS['TL_DCA']['tl_iso_config']['palettes']['default'] = str_replace('{images_legend}', '{feed_legend},addFeed;{images_legend}', $GLOBALS['TL_DCA']['tl_iso_config']['palettes']['default']);
$GLOBALS['TL_DCA']['tl_iso_config']['subpalettes']['addFeed'] = 'feedTypes,feedName,feedBase,feedTitle,feedDesc,feedJumpTo';
					

// Fields
$GLOBALS['TL_DCA']['tl_iso_config']['fields']['addFeed'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['addFeed'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'eval'					  => array('submitOnChange'=>true)
);
$GLOBALS['TL_DCA']['tl_iso_config']['fields']['feedTypes'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['feedTypes'],
	'exclude'                 => true,
	'inputType'               => 'checkboxWizard',
	'options_callback'        => array('tl_iso_config_feeds', 'getFeedTypes'),
	'eval'                    => array('multiple'=>true)
);
$GLOBALS['TL_DCA']['tl_iso_config']['fields']['feedName'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['feedName'],
	'exclude'                 => true,
	'inputType'               => 'text',
	'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
);
$GLOBALS['TL_DCA']['tl_iso_config']['fields']['feedBase'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['feedBase'],
	'exclude'                 => true,
	'inputType'               => 'text',
	'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
);
$GLOBALS['TL_DCA']['tl_iso_config']['fields']['feedTitle'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['feedTitle'],
	'exclude'                 => true,
	'inputType'               => 'text',
	'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
);
$GLOBALS['TL_DCA']['tl_iso_config']['fields']['feedDesc'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_iso_config']['feedDesc'],
	'exclude'                 => true,
	'inputType'               => 'text',
	'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
);
$GLOBALS['TL_DCA']['tl_iso_config']['fields']['feedJumpTo'] = array
(
	'label'					=> &$GLOBALS['TL_LANG']['tl_iso_config']['feedJumpTo'],
	'inputType'				=> 'pageTree',
	'eval'					=> array('fieldType'=>'radio', 'tl_class'=>'clr'),
);

class tl_iso_config_feeds extends Backend
{
	/**
	 * Import the back end user object
	 */
	public function __construct()
	{
		parent::__construct();
		$this->import('BackendUser', 'User');
	}
	
	/**
	 * Return all editable fields of table tl_member
	 * @return array
	 */
	public function getFeedTypes()
	{
		$return = array();

		foreach ($GLOBALS['ISO_FEEDS'] as $k=>$v)
		{
			$return[$k] = $GLOBALS['TL_LANG']['ISO_FEEDS'][$k];
		}
		return $return;
	}
	
}
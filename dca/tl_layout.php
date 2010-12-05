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
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_layout']['palettes']['default'] = str_replace('calendarfeeds','calendarfeeds,productfeeds',$GLOBALS['TL_DCA']['tl_layout']['palettes']['default']);

/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_layout']['fields']['productfeeds'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_layout']['productfeeds'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'options_callback'        => array('tl_layout_isotopefeeds', 'getProductfeeds'),
	'eval'                    => array('multiple'=>true)
);


class tl_layout_isotopefeeds extends Backend
{
	/**
	 * Return all XML product feeds
	 * @return array
	 */
	public function getProductfeeds()
	{
		$objFeed = $this->Database->execute("SELECT * FROM tl_iso_config WHERE addFeed=1");

		if ($objFeed->numRows < 1)
		{
			return array();
		}

		$return = array();

		while ($objFeed->next())
		{
			$arrFeeds = deserialize($objFeed->feedTypes);
			if(is_array($arrFeeds) && count($arrFeeds) > 0)
			{
				foreach($arrFeeds as $feed)
				{
					$return[$objFeed->id . '|'. $feed] = $objFeed->id . ': '. $GLOBALS['TL_LANG']['ISO_FEEDS'][$feed];
				}
			}
		}

		return $return;
	}
}
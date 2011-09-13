<?php //if (!defined('TL_ROOT')) die('You can not access this file directly!');

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


class IsotopeFeedsRunonce extends Controller
{

	/**
	 * Initialize the object
	 */

	public function __construct()
	{
		parent::__construct();

		// Fix potential Exception on line 0 because of __destruct method (see http://dev.contao.org/issues/2236)
		$this->import((TL_MODE=='BE' ? 'BackendUser' : 'FrontendUser'), 'User');
		$this->import('Database');
		$this->import('Files');
	}

	/**
	 * Run the controller
	 */
	public function run()
	{
		if (!$this->Database->tableExists('tl_google_taxonomy') || $this->Database->query("SELECT COUNT(*) AS total FROM tl_google_taxonomy")->total == 0)
		{
			$objFile = new File('system/modules/isotope_feeds/config/taxonomy.sql');
			$strContents = trim($objFile->getContent());
			$arrChunks = explode(';', $strContents);
			foreach($arrChunks as $query)
			{
				if(strlen($query))
				{
					$this->Database->query($query);
				}
			}
		}
	}


}

/**
 * Instantiate controller
 */
$objIsotopeFeeds = new IsotopeFeedsRunonce();
$objIsotopeFeeds->run();


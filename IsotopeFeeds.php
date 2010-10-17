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

class IsotopeFeeds extends Frontend
{

	/**
	 * Update a particular store config's RSS feed
	 * @param integer
	 */
	public function generateFeed($intId)
	{
		$objConfig = $this->Database->prepare("SELECT * FROM tl_iso_config WHERE id=? AND addFeed=?")
									 ->limit(1)
									 ->execute($intId, 1);

		if ($objConfig->numRows < 1)
		{
			return;
		}

		$objConfig->feedName = strlen($objConfig->feedName) ? $objConfig->feedName : 'products' . $objConfig->id;
		$arrTypes = deserialize($objConfig->feedTypes);
		
		// Delete XML file
		if ($this->Input->get('act') == 'delete')
		{
			$this->import('Files');
			foreach($arrTypes as $feedType)
			{
				$this->Files->delete($objConfig->feedName . '-' . $feedType . '.xml');
			}
		}

		// Update XML file
		else
		{
			foreach($arrTypes as $feedType)
			{
				$this->generateFiles($objConfig->row(), $feedType);
				$this->log('Generated products feed ' . $objConfig->feedName . '-'. $feedType . '.xml"', 'IsotopeFeeds generateFeed()', TL_CRON);
			}
		}
	}


	/**
	 * Delete old files and generate all product feeds
	 */
	public function generateFeeds()
	{
		$this->removeOldFeeds();
		$objConfig = $this->Database->execute("SELECT * FROM tl_iso_config WHERE addFeed=1");

		while ($objConfig->next())
		{
			$objConfig->feedName = strlen($objConfig->feedName) ? $objConfig->feedName : 'products' . $objConfig->id;
			$arrFeedTypes = deserialize($objConfig->feedTypes);
			
			foreach( $arrFeedTypes as $feedType )
			{
				$this->generateFiles($objConfig->row(), $feedType);
				$this->log('Generated product feed ' . $objConfig->feedName . '-'. $feedType . '.xml"', 'IsotopeFeeds generateFeeds()', TL_CRON);
			}
		}
	}


	/**
	 * Generate an XML files and save them to the root directory
	 * @param array
	 * @param string
	 */
	protected function generateFiles($arrConfig, $strType)
	{
		$arrType = $GLOBALS['ISO_FEEDS'][$strType];
		$time = time();
		$strLink = strlen($arrConfig['feedBase']) ? $arrConfig['feedBase'] : $this->Environment->base;
		$strFile = $arrConfig['feedName'] . '-' . $strType;
		
		try
		{
			$objFeed = new $arrType[0]($strFile);
		}
		catch (Exception $e)
		{
			$objFeed = new GoogleFeed($strFile);
		}

		$objFeed->link = $strLink;
		$objFeed->title = $arrConfig['name'];
		$objFeed->description = $arrConfig['description'];
		$objFeed->language = $arrConfig['language'];
		$objFeed->published = time();

		// Get root pages that belong to this store config.
		$objRoot = $this->Database->prepare("SELECT * FROM tl_page p WHERE type='root' AND iso_config=?")->execute($arrConfig['id']);
		if($objRoot->numRows)
		{
			//Get an array of all pages under the root so that we can compare to product categories
			$objPages = $this->Database->execute("SELECT id FROM tl_page");
			while($objPages->next())
			{
				$objDetails = $this->getPageDetails($objPages->id);
				if($objDetails->rootId == $objRoot->id)
				{
					$arrPages[] = $objPages->id;
				}
			}
		}
		$objProducts = $this->Database->execute("SELECT * FROM tl_iso_products WHERE pid=0 AND (start='' OR start<$time) AND (stop='' OR stop>$time) AND published=1");
		while($objProducts->next())
		{
			if(count($arrPages))
			{
				$arrProductPages = deserialize($objProducts->pages);
				if( count(array_intersect($arrPages, $arrProductPages)) )
				{
					$arrProducts[] = $objProducts->row();
				}
			}
			else
			{
				$arrProducts[] = $objProducts->row();
			}
		}
		
		// Get default URL
		$objParent = $this->Database->prepare("SELECT id, alias FROM tl_page WHERE id=?")
									->limit(1)
									->execute($arrConfig['feedJumpTo']);
		if(!$objParent->numRows)
		{
			//Get the first reader page we can find 
			//@todo: restrict it to store config root
			$objModules = $this->Database->prepare("SELECT iso_reader_jumpTo FROM tl_module WHERE iso_reader_jumpTo!=''")->limit(1)->execute();

			$objParent = $this->Database->prepare("SELECT id, alias FROM tl_page WHERE id=?")
									->limit(1)
									->execute($objModules->iso_reader_jumpTo);
		}

		$strUrl = $this->generateFrontendUrl($objParent->fetchAssoc(), '/product/%s');

		// Parse items
		foreach($arrProducts as $objProduct)
		{
			
			$objItem = new FeedItem();

			$objItem->title = $objProduct['name'];
			$objItem->link = $this->getLink($objProduct, $strUrl);
			$objItem->published = time();

			// Prepare the description
			$strDescription = $objProduct['description'];
			$strDescription = $this->replaceInsertTags($strDescription);
			$objItem->description = $this->convertRelativeUrls($strDescription, $strLink);

			$objFeed->addItem($objItem);
			
		}
		
		// Create file
		$objRss = new File($strFile . '.xml');
		$objRss->write($this->replaceInsertTags($objFeed->$arrType[1]()));
		$objRss->close();
	}


	/**
	 * Return the link of a product
	 * @param object
	 * @param string
	 * @return string
	 */
	protected function getLink(Array $arrProduct, $strUrl)
	{
		// Link to the default page
		return sprintf($strUrl, ((strlen($arrProduct['alias']) && !$GLOBALS['TL_CONFIG']['disableAlias']) ? $arrProduct['alias'] : $arrProduct['id']));
	}
}

?>
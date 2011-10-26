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

class IsotopeFeeds extends Controller
{

	/**
	 * Import some default libraries
	 */
	protected function __construct()
	{
		parent::__construct();
		$this->import('Isotope');
		$this->import('Database');
	}

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
	 * remove feeds hook to preserve files
	 */
	public function preserveFeeds()
	{
		$objConfig = $this->Database->execute("SELECT * FROM tl_iso_config WHERE addFeed=1");
		$arrFeeds = array();
		$objConfig->feedName = strlen($objConfig->feedName) ? $objConfig->feedName : 'products' . $objConfig->id;
		$arrFeedTypes = deserialize($objConfig->feedTypes);
		if(is_array($arrFeedTypes) && count($arrFeedTypes)>0)
		{
			foreach( $arrFeedTypes as $feedType )
			{
				$arrFeeds[] = $objConfig->feedName . '-'. $feedType;
			}
		}
		return $arrFeeds;

	}

	/**
	 * hook to add feed to head
	 */
	public function addFeedToLayout(Database_Result $objPage, Database_Result $objLayout, PageRegular $objPageRegular)
	{
		$arrFeeds = deserialize($objLayout->productfeeds);
		if(is_array($arrFeeds) && count($arrFeeds) > 0)
		{
			foreach($arrFeeds as $feed)
			{
				$arrConfig = explode('|', $feed);
				if(is_array($arrConfig) && count($arrConfig) > 0)
				{
					$objConfig = $this->Database->prepare("SELECT * FROM tl_iso_config WHERE addFeed=1 AND id=?")->limit(1)->execute($arrConfig[0], $arrConfig[1]);

					$arrTypes = deserialize($objConfig->feedTypes);
					foreach($arrTypes as $type)
					{
						if($arrConfig[1]==$type)
						{
							$strFeedname = strlen($objConfig->feedName) ? $objConfig->feedName : 'products' . $objConfig->id;
							$strName = $strFeedname . '-'. $type;
							$base = strlen($objConfig->feedBase) ? $objConfig->feedBase : $this->Environment->base;
							$GLOBALS['TL_HEAD'][] = '<link rel="alternate" href="' . $base . $strName . '.xml" type="application/rss+xml" title="' . $objConfig->feedTitle . '" />' . "\n";
						}

					}
				}
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
		$objFeed->title = $arrConfig['feedTitle'];
		$objFeed->description = $arrConfig['feedDesc'];
		$objFeed->language = $arrConfig['language'];
		$objFeed->published = time();

		// Get root pages that belong to this store config.
		$arrPages = array();
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

		// Get default URL
		$intJumpTo = $arrConfig['feedJumpTo'];

		if(!strlen($intJumpTo))
		{
			//Get the first reader page we can find
			$objModules = $this->Database->prepare("SELECT iso_reader_jumpTo FROM tl_module WHERE ".(count($arrPages)>0 ? "iso_reader_jumpTo IN (" . implode(',',$arrPages) . ") AND " : ''). "iso_reader_jumpTo !=''")->limit(1)->execute();

			if($objModules->numRows)
			{
				$intJumpTo = $objModules->iso_reader_jumpTo;
			}
		}

		$objProductData = $this->Database->execute("SELECT p.*, (SELECT class FROM tl_iso_producttypes t WHERE p.type=t.id) AS product_class FROM tl_iso_products p LEFT JOIN tl_iso_product_categories c ON c.pid=p.id WHERE ".(count($arrPages)>0 ? "c.pageid IN (" . implode(',',$arrPages) . ") AND " : ''). "p.pid=0 AND (p.start='' OR p.start<$time) AND (p.stop='' OR p.stop>$time) AND p.published=1 ORDER BY p.tstamp DESC");

		while($objProductData->next())
		{
			$strClass = $GLOBALS['ISO_PRODUCT'][$objProductData->product_class]['class'];

			if ($strClass == '' || !$this->classFileExists($strClass))
			{
				continue;
			}

			$objProduct = new $strClass($objProductData->row());

			if($objProduct->available)
			{
				$objItem = new FeedItem();

				$strUrlKey = $objProduct->alias ? $objProduct->alias  : ($objProduct->pid ? $objProduct->pid : $objProduct->id);

				$objItem->title = $objProduct->name;
				$objItem->link = $strLink . '/' . $this->generateFrontendUrl($this->Database->prepare("SELECT * FROM tl_page WHERE id=?")->execute($intJumpTo)->fetchAssoc(), '/product/' . $strUrlKey);
				$objItem->published = time();

				// Prepare the description
				$strDescription = $objProduct->description;
				$strDescription = $this->replaceInsertTags($strDescription);
				$objItem->description = $this->convertRelativeUrls($strDescription, $strLink);

				//Sku, price, etc
				$objItem->sku = strlen($objProduct->sku) ? $objProduct->sku : $objProduct->alias;
				$objItem->price = $this->Isotope->formatPrice($objProduct->original_price) .' '. $arrConfig['currency'];

				//Google specific settings
				$objItem->condition = $objProduct->gid_condition;
				$objItem->availability = $objProduct->gid_availability;
				$objItem->brand = $objProduct->gid_brand;
				$objItem->gtin = $objProduct->gid_gtin;
				$objItem->mpn = $objProduct->gid_mpn;
				$objItem->google_product_category = $this->Database->prepare("SELECT * FROM tl_google_taxonomy WHERE id=?")->execute($objProduct->gid_google_product_category)->fullname;

				//Custom product category taxomony
				$objItem->product_type = deserialize($objProduct->gid_product_type);


				//Prepare the images
				$arrImages = $this->getProductImages($objProduct);
				if(is_array($arrImages) && count($arrImages)>0)
				{
					$objItem->image_link = $this->Environment->base . $arrImages[0]['medium'];
					$objItem->addEnclosure($arrImages[0]['medium']);
					unset($arrImages[0]);
					if(count($arrImages)>0)
					{
						//Additional images
						$arrAdditional = array();
						foreach($arrImages as $additional)
						{
							$arrAdditional[] = $this->Environment->base . $additional['medium'];
						}
						$objItem->additional_image_link = $arrAdditional;
					}
				}

				$objFeed->addItem($objItem);
			}
		}

		// Create file
		$objRss = new File($strFile . '.xml');
		$objRss->write($this->replaceInsertTags($objFeed->$arrType[1]()));
		$objRss->close();
	}


	/**
	 * Return an array of the product's original and/or watermarked images
	 * @param array
	 * @return array
	 */
	protected function getProductImages($objProduct)
	{
		$arrReturn = array();
		$varValue = deserialize($this->Database->execute("SELECT images FROM tl_iso_products WHERE id={$objProduct->id}")->images);

		if(is_array($varValue) && count($varValue))
		{
			foreach( $varValue as $k => $file )
			{
				$strFile = 'isotope/' . substr($file['src'], 0, 1) . '/' . $file['src'];

				if (is_file(TL_ROOT . '/' . $strFile))
				{
					$objFile = new File($strFile);

					if ($objFile->isGdImage)
					{
						foreach( (array)$this->Isotope->Config->imageSizes as $size )
						{
							$strImage = $this->getImage($strFile, $size['width'], $size['height'], $size['mode']);

							if ($size['watermark'] != '')
							{
								$strImage = IsotopeFrontend::watermarkImage($strImage, $size['watermark'], $size['position']);
							}

							$arrSize = @getimagesize(TL_ROOT . '/' . $strImage);
							if (is_array($arrSize) && strlen($arrSize[3]))
							{
								$file[$size['name'] . '_size'] = $arrSize[3];
							}

							$file['alt'] = specialchars($file['alt']);
							$file['desc'] = specialchars($file['desc']);

							$file[$size['name']] = $strImage;
						}

						$arrReturn[] = $file;
					}
				}
			}
		}

		// No image available, add default image
		if (!count($arrReturn) && is_file(TL_ROOT . '/' . $this->Isotope->Config->missing_image_placeholder))
		{
			$strImage = $this->getImage($this->Isotope->Config->missing_image_placeholder, 250, 250, 'proportional');

			$arrSize = @getimagesize(TL_ROOT . '/' . $strImage);
			if (is_array($arrSize) && strlen($arrSize[3]))
			{
				$file['medium_size'] = $arrSize[3];
			}

			$file['medium'] = $strImage;

			$arrReturn[] = $file;
		}

		return $arrReturn;
	}

}

?>
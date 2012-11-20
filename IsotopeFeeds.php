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
		$this->import('Files');
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
				$this->log('Generated product feed ' . $objConfig->feedName . '-'. $feedType . '.xml"', __METHOD__, TL_CRON);
			}
		}
	}
	
	
	/**
	 * Cache the product XML for each store config
	 */
	public function cacheProduct($dc)
	{
		$objConfig = $this->Database->execute("SELECT * FROM tl_iso_config WHERE addFeed=1");

		while ($objConfig->next())
		{
			$arrFeedTypes = deserialize($objConfig->feedTypes, true);
			foreach( $arrFeedTypes as $feedType )
			{
				$this->generateProductXML($feedType, $dc->activeRecord, $objConfig->row());
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
		$arrFeedTypes = deserialize($objConfig->feedTypes, true);
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

					$arrTypes = deserialize($objConfig->feedTypes, true);
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
		$arrType = $GLOBALS['ISO_FEEDS'][$strType]['feed'];
		$time = time();
		$strLink = strlen($arrConfig['feedBase']) ? $arrConfig['feedBase'] : $this->Environment->base;
		$strFile = $arrConfig['feedName'] . '-' . $strType;

		try
		{
			$objFeed = new $arrType[0]($strFile);
		}
		catch (Exception $e)
		{
			$objFeed = new FeedIsotope($strFile);
		}

		$objFeed->link = $strLink;
		$objFeed->title = $arrConfig['feedTitle'];
		$objFeed->description = $arrConfig['feedDesc'];
		$objFeed->language = $arrConfig['language'];
		$objFeed->published = time();
		
		$strDir = 'isotope/cache/' . $arrConfig['id'] . '/' . $strType;
		
		if(is_dir(TL_ROOT . '/' .  $strDir))
		{
			$arrFiles = scan(TL_ROOT . '/' .  $strDir);
			
			foreach($arrFiles as $file)
			{
				if(is_file(TL_ROOT  . '/' . $strDir . '/' . $file))
				{
					$objFile = new File($strDir . '/' . $file);
					$objFeed->addFile($objFile);
				}
			}
			
			// Create file
			$objRss = new File($strFile . '.xml');
			$objRss->write($this->replaceInsertTags($objFeed->$arrType[1]()));
			$objRss->close();
		}
	}
	
	
	
	/**
	 * Generate an XML file for a product and save it to the cache
	 * @param string - feed type
	 * @param object - DataContainer ActiveRecord or objProduct
	 * @param array - store config info row
	 */
	public function generateProductXML($strType, $objRecord, $arrConfig)
	{
		$time = time();
		$arrFeedClass = $GLOBALS['ISO_FEEDS'][$strType];
		$this->import($arrFeedClass['feed'][0]);
		$strLink = strlen($arrConfig['feedBase']) ? $arrConfig['feedBase'] : $this->Environment->base;
		
		// Get root pages that belong to this store config.
		$arrPages = array();
		$objRoot = $this->Database->prepare("SELECT * FROM tl_page p WHERE type='root' AND iso_config=?")->execute($arrConfig['id']);
		
		if($objRoot->numRows)
		{
				$arrRoot = $objRoot->fetchEach('id');
				//Get an array of all pages under the root so that we can compare to product categories
				$objPages = $this->Database->execute("SELECT id FROM tl_page");
				while($objPages->next())
				{
					$objDetails = $this->getPageDetails($objPages->id);
					if(in_array($objDetails->rootId,$arrRoot))
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
		
		$strIDField = $objRecord->pid > 0 ? 'p.pid' : 'p.id';
		
		$objProductData = $this->Database->execute("SELECT p.*, (SELECT class FROM tl_iso_producttypes t WHERE p.type=t.id) AS product_class FROM tl_iso_products p LEFT JOIN tl_iso_product_categories c ON c.pid=$strIDField WHERE ".(count($arrPages)>0 ? "c.page_id IN (" . implode(',',$arrPages) . ") AND " : ''). "(p.start='' OR p.start<$time) AND (p.stop='' OR p.stop>$time) AND p.published=1 AND p.id=$objRecord->id ORDER BY p.tstamp DESC");
		
		if(!$objProductData->numRows)
		{
			$blnDelete = true;
		}

		while($objProductData->next())
		{
			$strClass = $GLOBALS['ISO_PRODUCT'][$objProductData->product_class]['class'];

			if ($strClass == '' || !$this->classFileExists($strClass))
			{
				continue;
			}

			$objProduct = new $strClass($objProductData->row());

			if($objProduct->published && $objProduct->available && $objProduct->useFeed)
			{
				//Check for variants and run them instead if they exist
				if($objProduct->pid==0 && count($objProduct->variant_ids))
				{
					foreach($objProduct->variant_ids as $variantID)
					{
						$objRecord = $this->Database->prepare("SELECT * FROM tl_iso_products WHERE id=?")->execute($variantID);
						$this->generateProductXML($strType, $objRecord, $arrConfig);
						
					}
					
					//Do not run the parent and delete the cache file if it exists
					$strLocation = 'isotope/cache/' . $arrConfig['id'] . '/' .  $strType . '/' . $objProduct->alias . ($objProduct->pid > 0 ? '-' . $objProduct->id : '' ) .  '.xml';
					$this->Files->delete($strLocation);
					continue;
				}
				
				try
				{
					$objItem = new $GLOBALS['ISO_FEEDS'][$strType]['item']();
				}
				catch (Exception $e)
				{
					$objItem = new FeedItemIsotope();
				}

				$strUrlKey = $objProduct->alias ? $objProduct->alias  : ($objProduct->pid ? $objProduct->pid : $objProduct->id);

				$objItem->title = $objProduct->name;
				$objItem->link = $strLink . $this->generateFrontendUrl($this->Database->prepare("SELECT * FROM tl_page WHERE id=?")->execute($intJumpTo)->fetchAssoc(), '/product/' . $strUrlKey);
				$objItem->published = time();

				// Prepare the description
				$strDescription = $objProduct->description;
				$strDescription = $this->replaceInsertTags($strDescription);
				$objItem->description = $this->convertRelativeUrls($strDescription, $strLink);

				//Sku, price, etc
				$objItem->id = $objProduct->id;
				$objItem->sku = strlen($objProduct->sku) ? $objProduct->sku : $objProduct->alias;
				$objItem->price = $this->Isotope->formatPrice($objProduct->original_price) .' '. $arrConfig['currency'];

				//Google basic settings
				$objItem->condition = $objProduct->gid_condition;
				$objItem->availability = $objProduct->gid_availability;
				$objItem->brand = $objProduct->gid_brand;
				$objItem->gtin = $objProduct->gid_gtin;
				$objItem->mpn = $objProduct->gid_mpn;
				$objItem->google_product_category = $this->Database->prepare("SELECT * FROM tl_google_taxonomy WHERE id=?")->execute($objProduct->gid_google_product_category)->fullname;
				
				//Google variants only
				if($objProduct->pid>0)
				{
					$objItem->item_group_id = strlen($objProduct->sku) ? $objProduct->sku : $objProduct->alias;
				}
				
				//Custom product category taxomony
				$objItem->product_type = deserialize($objProduct->gid_product_type);
				
				//HOOK for other data that needs to be added
				if (isset($GLOBALS['ISO_HOOKS']['feedItem']) && is_array($GLOBALS['ISO_HOOKS']['feedItem']))
				{
					foreach ($GLOBALS['ISO_HOOKS']['feedItem'] as $callback)
					{
						$this->import($callback[0]);
						$objItem = $this->$callback[0]->$callback[1]($strType, $objItem, $objProduct);
					}
				}

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
				
				//Cache the file
				$strLocation = 'isotope/cache/' . $arrConfig['id'] . '/' .  $strType . '/' . $objProduct->alias . ($objProduct->pid > 0 ? '-' . $objProduct->id : '' ) .  '.xml';
				$objItem->cache($strLocation);
			}
			else
			{
				$blnDelete = true;
			}

		}
		
		if($blnDelete)
		{
			//Delete the cache file if it exists
			if($objRecord->pid>0)
			{
				$objParent = $this->Database->execute("SELECT * FROM tl_iso_products WHERE id={$objRecord->pid}");
			}
			$strAlias = strlen($objProduct->alias) ? $objProduct->alias : (strlen($objRecord->alias) ? $objRecord->alias : $objParent->alias);
			$strLocation = 'isotope/cache/' . $arrConfig['id'] . '/' .  $strType . '/' . $strAlias . ($objRecord->pid > 0 ? '-' . $objRecord->id : '' ) .  '.xml';
			if(is_file(TL_ROOT . '/' . $strLocation))
			{
				$this->Files->delete($strLocation);
			}
		}

	}


	/**
	 * Return an array of the product's original and/or watermarked images
	 * @param array
	 * @return array
	 */
	protected function getProductImages($objProduct)
	{
		$arrReturn = array();
		$intID = $objProduct->pid ? $objProduct->pid : $objProduct->id;
		$varValue = deserialize($this->Database->execute("SELECT images FROM tl_iso_products WHERE id={$intID}")->images);

		if(is_array($varValue) && count($varValue))
		{
			foreach( $varValue as $k => $file )
			{
				$strFile = 'isotope/' . strtolower(substr($file['src'], 0, 1)) . '/' . $file['src'];

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
	
	
	/**
	 * Handle the AJAX request that will rebuild the cache, etc
	 * @param string
	 * @return string
	 */
	 public function ajaxHandler($strAction)
	 {
		 if($strAction=='startCache')
		 {
		
			$intLimit = 5;
			$intOffset = (int) $this->Input->post('offset');
			
			$time = time();
			$objProduct = $this->Database->prepare("SELECT * FROM tl_iso_products WHERE pid=0 AND (start='' OR start<$time) AND (stop='' OR stop>$time) AND published=1 ORDER BY tstamp DESC")
										 ->limit($intLimit, $intOffset)
										 ->execute();
										 
			$arrConfigs = $this->Database->prepare("SELECT * FROM tl_iso_config WHERE addFeed=1")->execute()->fetchAllAssoc();
			
			$varOffset = $objProduct->numRows ? ($intOffset+5) :  'finished';
			$strMessage = $objProduct->numRows ? ($intOffset+5) . ' products cached...' : 'Cache refresh complete';
			
			if($objProduct->numRows)
			{
				while($objProduct->next())
				{
					foreach($arrConfigs as $arrConfig)
					{
						$arrFeedTypes = deserialize($arrConfig['feedTypes'], true);
						
						foreach( $arrFeedTypes as $feedType )
						{
							//Empty the cache folder if this is first run
							if((int)$this->Input->post('offset')==0)
							{
								$strLocation = 'isotope/cache/' . $arrConfig['id'] . '/' .  $strType;
								if(is_dir(TL_ROOT . '/' . $strLocation))
								{
									$objFolder = new Folder($strLocation);
									$objFolder->clear();
								}
							}
						
							$this->generateProductXML($feedType, $objProduct, $arrConfig);
						}
					}
				}
			}
			
			echo json_encode(array
			(
				'content' => array(
					
					'offset'	=> $varOffset,
					'message'	=> $strMessage,
				),
				'token'   => REQUEST_TOKEN
			));
			exit;
			 
		 }
		 
	 }
	

}

?>
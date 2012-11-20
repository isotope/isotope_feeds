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

class FeedGoogleMerchant extends FeedIsotope
{


	/**
	 * Generate an Google RSS 2.0 feed and return it as XML string
	 * @return string
	 */
	public function generateGoogle()
	{

		$xml  = '<?xml version="1.0" encoding="' . $GLOBALS['TL_CONFIG']['characterSet'] . '"?>' . "\n";
		$xml .= '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">' . "\n";
		$xml .= '  <channel>' . "\n";
		$xml .= '    <title>' . specialchars($this->title) . '</title>' . "\n";
		$xml .= '    <description>' . specialchars($this->description) . '</description>' . "\n";
		$xml .= '    <link>' . specialchars($this->link) . '</link>' . "\n";
		$xml .= '    <language>' . $this->language . '</language>' . "\n";
		$xml .= '    <pubDate>' . date('r', $this->published) . '</pubDate>' . "\n";
		$xml .= '    <generator>Contao Open Source CMS</generator>' . "\n";

		foreach ($this->arrFiles as $objFile)
		{
			$xml .= $objFile->getContent();
		}

		$xml .= '  </channel>' . "\n";
		$xml .= '</rss>';

		return $xml;
	}

}


class FeedItemGoogleMerchant extends FeedItemIsotope
{
	/**
	 * Cache the item's XML node to a file
	 * @param string
	 */
	public function cache($strLocation)
	{
		//double-check: if we do not have two out of 3 unique identifiers, delete cache
		if((!$this->condition || !$this->availability || !$this->brand) || (!strlen($this->gtin) && !strlen($this->mpn)))
		{
			if(is_file(TL_ROOT . '/' . $strLocation))
			{
				$this->Files->delete($strLocation);
			}
			return;
		}
		
		$arrGoogleFields = array
		(
			'id',
			'price',
			'availability',
			'condition',
			'image_link',
			'product_type',
			'google_product_category',
			'brand',
			'gtin',
			'mpn',
			'additional_image_link',
			'sale_price',
			'sale_price_effective_date',
			'item_group_id',
			'color',
			'material',
			'pattern',
			'size',
			'gender',
			'age_group',
		);
		
		
		$xml .= '	<item>' . "\n";
		$xml .= '      <title>' . specialchars($this->title) . '</title>' . "\n";
		$xml .= '      <description><![CDATA[' . preg_replace('/[\n\r]+/', ' ', $this->description) . ']]></description>' . "\n";
		$xml .= '      <link><![CDATA[' . specialchars($this->link) . ']]></link>' . "\n";
		foreach($arrGoogleFields as $strKey)
		{
			if($this->__isset($strKey) && count($this->$strKey) )
			{
				if(is_array($this->$strKey) && count($this->$strKey))
				{
					foreach($this->$strKey as $value)
					{
						$xml .= '      <g:'.$strKey.'><![CDATA[' . specialchars($value) . ']]></g:'.$strKey.'>' . "\n";
					}
				}
				elseif(!is_array($this->$strKey) && strlen($this->$strKey))
				{
					$xml .= '      <g:'.$strKey.'><![CDATA[' . specialchars($this->$strKey) . ']]></g:'.$strKey.'>' . "\n";
				}
			}
		}
		if($this->shipping)
		{
			$xml .= $this->shipping;
		}
		$xml .= '	</item>' . "\n";
		
		$this->write($xml, $strLocation);
		
	}

}
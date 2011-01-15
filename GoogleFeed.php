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

class GoogleFeed extends Feed
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
		$xml .= '    <atom:link href="' . specialchars($this->Environment->base . $this->strName) . '.xml" rel="self" type="application/rss+xml" />' . "\n";

		foreach ($this->arrItems as $objItem)
		{
			$xml .= '    <item>' . "\n";
			$xml .= '      <title>' . specialchars($objItem->title) . '</title>' . "\n";
			$xml .= '      <description><![CDATA[' . preg_replace('/[\n\r]+/', ' ', $objItem->description) . ']]></description>' . "\n";
			$xml .= '      <link>' . specialchars($objItem->link) . '</link>' . "\n";
			$xml .= '      <g:id>' . specialchars($objItem->sku) . '</g:id>' . "\n";
			$xml .= '      <g:price>' . specialchars($objItem->price) . '<g:price>' . "\n";
			$xml .= '      <g:condition>new<g:condition>' . "\n";
			$xml .= '      <g:image_link>' . specialchars($objItem->image) . '</g:image_link>' . "\n";
			$xml .= '      <guid>' . ($objItem->guid ? $objItem->guid : specialchars($objItem->link)) . '</guid>' . "\n";
			$xml .= '    </item>' . "\n";
		}

		$xml .= '  </channel>' . "\n";
		$xml .= '</rss>';

		return $xml;
	}

}

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

class FeedIsotope extends Feed
{

	/**
	 * Individual XML files
	 * @var array
	 */
	protected $arrFiles = array();
	
	
	/**
	 * Initialize the object and import default classes
	 * @param string
	 */
	public function __construct($strName='')
	{
		parent::__construct($strName);
		$this->import('Files');
	}
	
	
	/**
	 * Add a XML file
	 * @param object
	 */
	public function addFile(File $objFile)
	{
		$this->arrFiles[] = $objFile;
	}


	/**
	 * Generate an RSS 2.0 feed and return it as XML string
	 * @return string
	 */
	public function generateRss()
	{
		$this->adjustPublicationDate();

		$xml  = '<?xml version="1.0" encoding="' . $GLOBALS['TL_CONFIG']['characterSet'] . '"?>';
		$xml .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">';
		$xml .= '<channel>';
		$xml .= '<title>' . specialchars($this->title) . '</title>';
		$xml .= '<description>' . specialchars($this->description) . '</description>';
		$xml .= '<link>' . specialchars($this->link) . '</link>';
		$xml .= '<language>' . $this->language . '</language>';
		$xml .= '<pubDate>' . date('r', $this->published) . '</pubDate>';
		$xml .= '<generator>Contao Open Source CMS</generator>';
		$xml .= '<atom:link href="' . specialchars($this->Environment->base . $this->strName) . '.xml" rel="self" type="application/rss+xml" />';

		foreach ($this->arrFiles as $objFile)
		{
			$xml .= $objFile->getContent();
		}

		$xml .= '</channel>';
		$xml .= '</rss>';

		return $xml;
	}

}


class FeedItemIsotope extends FeedItem
{
	/**
	 * Cache the item's XML node to a file
	 * @param string
	 */
	public function cache($strLocation)
	{
		$xml .= '<item>';
		$xml .= '<title>' . specialchars($this->title) . '</title>';
		$xml .= '<description><![CDATA[' . preg_replace('/[\n\r]+/', ' ', $this->description) . ']]></description>';
		$xml .= '<link>' . specialchars($this->link) . '</link>';
		$xml .= '<pubDate>' . date('r', $this->published) . '</pubDate>';
		$xml .= '<guid>' . ($this->guid ? $this->guid : specialchars($this->link)) . '</guid>';

		// Enclosures
		if (is_array($this->enclosure))
		{
			foreach ($this->enclosure as $arrEnclosure)
			{
				$xml .= '<enclosure url="' . $arrEnclosure['url'] . '" length="' . $arrEnclosure['length'] . '" type="' . $arrEnclosure['type'] . '" />';
			}
		}

		$xml .= '</item>';
		
		$this->write($xml, $strLocation);
	}
	
	
	/**
	 * Write the XML to a file
	 * @param string
	 */
	protected function write($strXml, $strLocation)
	{
		$objFile = new File($strLocation);
		$objFile->truncate();
		$objFile->write($strXml);
		$objFile->close();
	}
	
}

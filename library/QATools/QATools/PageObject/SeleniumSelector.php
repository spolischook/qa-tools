<?php
/**
 * This file is part of the QA-Tools library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/qa-tools/qa-tools
 */

namespace QATools\QATools\PageObject;


use Behat\Mink\Selector\CssSelector;
use Behat\Mink\Selector\SelectorInterface;
use Behat\Mink\Selector\Xpath\Escaper;
use QATools\QATools\PageObject\Exception\ElementException;

/**
 * Class for handling Selenium-style element selectors.
 *
 * @method \Mockery\Expectation shouldReceive(string $name)
 *
 * @link http://bit.ly/qa-tools-findby-selector
 */
class SeleniumSelector implements SelectorInterface
{

	/**
	 * Reference to CSS selector.
	 *
	 * @var CssSelector
	 */
	private $_cssSelector;

	/**
	 * The XPath escaper.
	 *
	 * @var Escaper
	 */
	private $_xpathEscaper;

	/**
	 * Creates instance of SeleniumSelector class.
	 */
	public function __construct()
	{
		$this->_cssSelector = new CssSelector();
		$this->_xpathEscaper = new Escaper();
	}

	/**
	 * Translates provided locator into XPath.
	 *
	 * @param mixed $locator Current selector locator.
	 *
	 * @return string
	 * @throws ElementException When used selector is broken or not implemented.
	 */
	public function translateToXPath($locator)
	{
		if ( !$locator || !is_array($locator) ) {
			throw new ElementException(
				'Incorrect Selenium selector format',
				ElementException::TYPE_INCORRECT_SELECTOR
			);
		}

		list ($selector, $locator) = each($locator);
		$locator = trim($locator);

		if ( $selector == How::CLASS_NAME ) {
			$locator = $this->_xpathEscaper->escapeLiteral(' ' . $locator . ' ');

			return "descendant-or-self::*[@class and contains(concat(' ', normalize-space(@class), ' '), " . $locator . ')]';
		}
		elseif ( $selector == How::CSS ) {
			return $this->_cssSelector->translateToXPath($locator);
		}
		elseif ( $selector == How::ID ) {
			return 'descendant-or-self::*[@id = ' . $this->_xpathEscaper->escapeLiteral($locator) . ']';
		}
		elseif ( $selector == How::NAME ) {
			return 'descendant-or-self::*[@name = ' . $this->_xpathEscaper->escapeLiteral($locator) . ']';
		}
		elseif ( $selector == How::ID_OR_NAME ) {
			$locator = $this->_xpathEscaper->escapeLiteral($locator);

			return 'descendant-or-self::*[@id = ' . $locator . ' or @name = ' . $locator . ']';
		}
		elseif ( $selector == How::TAG_NAME ) {
			return 'descendant-or-self::' . $locator;
		}
		elseif ( $selector == How::LINK_TEXT ) {
			$locator = $this->_xpathEscaper->escapeLiteral($locator);

			return 'descendant-or-self::a[./@href][normalize-space(string(.)) = ' . $locator . ']';
		}
		elseif ( $selector == How::LABEL ) {
			$locator = $this->_xpathEscaper->escapeLiteral($locator);
			$xpath_pieces = array();
			$xpath_pieces[] = 'descendant-or-self::*[@id = (//label[normalize-space(string(.)) = ' . $locator . ']/@for)]';
			$xpath_pieces[] = 'descendant-or-self::label[normalize-space(string(.)) = ' . $locator . ']//input';

			return implode('|', $xpath_pieces);
		}
		elseif ( $selector == How::PARTIAL_LINK_TEXT ) {
			$locator = $this->_xpathEscaper->escapeLiteral($locator);

			return 'descendant-or-self::a[./@href][contains(normalize-space(string(.)), ' . $locator . ')]';
		}
		elseif ( $selector == How::XPATH ) {
			return $locator;
		}

		/*case How::LINK_TEXT:
		case How::PARTIAL_LINK_TEXT:*/

		throw new ElementException(
			sprintf('Selector type "%s" not yet implemented', $selector),
			ElementException::TYPE_UNKNOWN_SELECTOR
		);
	}

}

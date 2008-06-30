<?php
/**
 * 
 * Span plugin to escape HTML remaining the span.
 * 
 * @category Solar
 * 
 * @package Solar_Markdown_Wiki
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Escape.php 2933 2007-11-09 20:37:35Z moraes $
 * 
 */
class Solar_Markdown_Wiki_Escape extends Solar_Markdown_Plugin {
    
    /**
     * 
     * This is a span-level plugin.
     * 
     * @var bool
     * 
     */
    protected $_is_span = true;
    
    /**
     * 
     * Escapes HTML remaining in the text.
     * 
     * @param string $text The source text to be parsed.
     * 
     * @return string The transformed XHTML.
     * 
     */
    public function parse($text)
    {
        return $this->_escape($text);
    }
}

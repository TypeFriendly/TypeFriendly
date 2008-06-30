<?php
/**
 * 
 * Span plugin to add <br /> tags to each line ending with two or more
 * spaces.
 * 
 * @category Solar
 * 
 * @package Solar_Markdown
 * 
 * @author John Gruber <http://daringfireball.net/projects/markdown/>
 * 
 * @author Michel Fortin <http://www.michelf.com/projects/php-markdown/>
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Break.php 2933 2007-11-09 20:37:35Z moraes $
 * 
 */
class Solar_Markdown_Plugin_Break extends Solar_Markdown_Plugin {
    
    /**
     * 
     * This is a span plugin.
     * 
     * @var bool
     * 
     */
    protected $_is_span = true;
    
    /**
     * 
     * Adds <br /> tags to each line ending with two or more spaces.
     * 
     * @param string $text The source text.
     * 
     * @return string The transformed XHTML.
     * 
     */
    public function parse($text)
    {
        return preg_replace('/ {2,}\n/', $this->_toHtmlToken("<br />") . "\n", $text);
    }
}

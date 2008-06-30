<?php
/**
 * 
 * Replaces wiki and interwiki links in source text with XHTML anchors.
 * 
 * Wiki links are in this format ...
 * 
 *     [[wiki page]]
 *     [[wiki page #anchor]]
 *     [[wiki page]]s
 *     [[wiki page | display this instead]]
 *     [[wiki page #anchor | ]]
 * 
 * The "wiki page" name is normalized to "Wiki_page".  The last 
 * example, the one with the blank display text, will not display
 * the anchor fragment.
 * 
 * Page links are replaced with encoded placeholders.  At cleanup()
 * time, the placeholders are transformed into XHTML anchors.
 * 
 * This plugin also supports Interwiki links, in this format ...
 * 
 *     [[site::page]]
 *     [[site::page #anchor]]
 *     [[site::page]]s
 *     [[site::page | display this instead]]
 *     [[site::page #anchor | ]]
 * 
 * Site prefixes and page names are **not** normalize.  The last
 * example, the one with the blank display text, will not display 
 * the site prefix or the anchor fragment.
 * 
 * Interwiki links are replaced with HTML immediately and are not
 * checked for existence.
 * 
 * @category Solar
 * 
 * @package Solar_Markdown_Wiki
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Link.php 2933 2007-11-09 20:37:35Z moraes $
 * 
 */
class Solar_Markdown_Wiki_Link extends Solar_Markdown_Plugin {
    
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
     * Runs during the cleanup() phase.
     * 
     * @var bool
     * 
     */
    protected $_is_cleanup = true;
    
    /**
     * 
     * Array of which pages exist and which don't.
     * 
     * Format is page name => true/false.
     * 
     * @var array
     * 
     */
    protected $_pages;
    
    /**
     * 
     * Array of information for each link found in the source text.
     * 
     * Each element is an array with these keys ...
     * 
     * `norm`
     * : The normalized form of the page name.
     * 
     * `page`
     * : The page name as entered in the source text.
     * 
     * `frag`
     * : A fragment anchor for the target page (for example, "#example").
     * 
     * `text`
     * : The text to display in place of the page name.
     * 
     * `atch`
     * : Attached suffix text to go on the end of the displayed text.
     * 
     * @var array
     * 
     */
    protected $_links;
    
    /**
     * 
     * Running count of $this->_links, so we don't have to call count()
     * on it all the time.
     * 
     * @var int
     * 
     */
    protected $_count = 0;
    
    /**
     * 
     * The name of this class, for identifying encoded keys in the
     * source text.
     * 
     * @var string
     * 
     */
    protected $_class;
    
    /**
     * 
     * Attribs for 'read' and 'add' style links.
     * 
     * Note that 'href' is special, in that it is an sprintf() format 
     * string.
     * 
     * @var array
     * 
     */
    protected $_attribs = array(
        'read' => array(
            'href' => '/wiki/read/%s'
        ),
        'add' => array(
            'href' => '/wiki/add/%s'
        ),
    );
    
    /**
     * 
     * Array of interwiki site names to base hrefs.
     * 
     * Interwiki href values are actually sprintf() strings, where %s
     * will be replaced with the page requested at the interwiki site.
     * For example, this key-value pair ...
     * 
     *     'php' => 'http://php.net/%s'
     * 
     * ... means that ``[[php::print()]]`` will become a link to
     * ``http://php.net/print()``.
     * 
     * @var array
     * 
     */
    protected $_interwiki = array(
        'amazon' => 'http://amazon.com/s?keywords=%s',
        'ask'    => 'http://www.ask.com/web?q=%s',
        'google' => 'http://www.google.com/search?q=%s',
        'imdb'   => 'http://imdb.com/find?s=all&q=%s',
        'php'    => 'http://php.net/%s',
    );
    
    /**
     * 
     * Callback to check if pages linked from the source text exist or 
     * not.
     * 
     * @var callback
     * 
     */
    protected $_check_pages = false;
    
    /**
     * 
     * Constructor.
     * 
     * @param array $config Array of user-defined configuariont values.
     * 
     */
    public function __construct($config = null)
    {
        parent::__construct($config);
        $this->_class = get_class($this);
    }
    
    /**
     * 
     * Sets the callback to check if pages exist.
     * 
     * The callback has to take exactly one parameter, an array keyed
     * on page names, with the value being true or false.  It should
     * return a similar array, saying whether or not each page in the
     * array exists.
     * 
     * If left empty, the plugin will assume all links exist.
     * 
     * @param callback $callback The callback to check if pages exist.
     * 
     * @return array An array of which pages exist and which don't.
     * 
     */
    public function setCheckPagesCallback($callback)
    {
        $this->_check_pages = $callback;
    }
    
    /**
     * 
     * Sets one anchor attribute.
     * 
     * @param string $type The anchor type, generally 'read' or 'add'.
     * 
     * @param string $key The attribute key, for example 'href' or 'class'.
     * 
     * @param string $val The attribute value.
     * 
     * @return void
     * 
     */
    public function setAttrib($type, $key, $val)
    {
        $this->_attribs[$type][$key] = $val;
    }
    
    /**
     * 
     * Sets one or more interwiki name and href mapping.
     * 
     * Interwiki href values are actually sprintf() strings, where %s
     * will be replaced with the page requested at the interwiki site.
     * 
     * @param string|array $spec If a string, the interwiki site name;
     * if an array, an array of name => href mappings to merge with
     * current interwiki list.
     * 
     * @param string $val If $spec is a string, this is the sprintf()
     * format string for the href to the interwiki.
     * 
     * @return void
     * 
     */
    public function setInterwiki($spec, $val = null)
    {
        if (is_array($spec)) {
            $this->_interwiki = array_merge($spec, $this->_interwiki);
        } else {
            $this->_interwiki[$spec] = $val;
        }
    }
    
    /**
     * 
     * Gets the list of interwiki mappings.
     * 
     * @return array
     * 
     */
    public function getInterwiki()
    {
        return $this->_interwiki;
    }
    
    /**
     * 
     * Sets all attributes for one anchor type.
     * 
     * @param string $type The anchor type, generally 'read' or 'add'.
     * 
     * @param array $list The attributes to set in key => value format.
     * 
     * @return void
     * 
     */
    public function setAttribs($type, $list)
    {
        $this->_attribs[$type] = $list;
    }
    
    /**
     * 
     * Gets the list of pages found in the source text.
     * 
     * @return array
     * 
     */
    public function getPages()
    {
        return array_keys($this->_pages);
    }
    
    /**
     * 
     * Resets this plugin for a new transformation.
     * 
     * @return void
     * 
     */
    public function reset()
    {
        parent::reset();
        $this->_links = array();
        $this->_pages = array();
        $this->_count = 0;
    }
    
    /**
     * 
     * Parses the source text for wiki page and interwiki links.
     * 
     * @param string $text The source text.
     * 
     * @return string The parsed text.
     * 
     */
    public function parse($text)
    {
        $regex = '/\[\[(.*?)(\|.*?)?\]\](\w*)?/';
        return preg_replace_callback(
            $regex,
            array($this, '_parse'),
            $text
        );
    }
    
    /**
     * 
     * Support callback for parsing wiki links.
     * 
     * @param array $matches Matches from preg_replace_callback().
     * 
     * @return string The replacement text.
     * 
     */
    protected function _parse($matches)
    {
        $addr = $matches[1];
        $text = empty($matches[3]) ? 'short' : trim($matches[3], "| \t");
        
        if(strpos($addr, 'http://') === 0)
        {
        	// We have an URL
        	$this -> _links[$this -> _count] = array(
        		'type' => 'url',
        		'addr' => $addr,
        		'text' => $text
        	);
        }
        else
        {
        	$project = tfProject::get();
        	
        	$page = $project -> getMetaInfo($addr, false);
        	if(!is_null($page))
        	{
        		$this -> _links[$this -> _count] = array(
        			'type' => 'inter',
        			'addr' => $project -> getOutput() -> toAddress($addr),
        			'text' => ($text == 'short' && isset($page['ShortTitle']) ? $page['ShortTitle'] : $page['Title'])
        		);
        	}
        	else
        	{
        		$prog = tfProgram::get();
        		$prog -> console -> stderr -> writeln('Cannot create a link for '.$addr.': page does not exist.');
        		
	        	$this -> _links[$this -> _count] = array(
	        		'type' => 'url',
	        		'addr' => '#',
	        		'text' => '!'.$addr.'!'
	        	);        		
        	}
        }

        $key = $this->_class . ':' . $this->_count ++;
        return "\x1B$key\x1B";
    }
    
    /**
     * 
     * Cleans up text to replace encoded placeholders with anchors.
     * 
     * @param string $text The source text with placeholders.
     * 
     * @return string The text with anchors instead of placeholders.
     * 
     */
    public function cleanup($text)
    {
        // first, update $this->_pages against the data store to see
        // which pages exist and which do not.
        if ($this->_check_pages) {
            $this->_pages = call_user_func($this->_check_pages, $this->_pages);
        }
        
        // now go through and replace tokens
        $regex = "/\x1B{$this->_class}:(.*?)\x1B/";
        return preg_replace_callback(
            $regex,
            array($this, '_cleanup'),
            $text
        );
    }
    
    /**
     * 
     * Support callback for replacing placeholder with anchors.
     * 
     * @param array $matches Matches from preg_replace_callback().
     * 
     * @return string The replacement text.
     * 
     */
    protected function _cleanup($matches)
    {
        $link = $this -> _links[$matches[1]];
        return '<a href="'.$link['addr'].'">'.$link['text'].'</a>';
    }
}

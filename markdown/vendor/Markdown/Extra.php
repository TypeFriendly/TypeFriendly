<?php
/**
 * 
 * Solar port of Markdown-Extra by Michel Fortin.
 * 
 * This class implements a plugin set for the Markdown-Extra syntax;
 * be sure to visit the [Markdown-Extra][] site for syntax examples.
 * 
 * [Markdown-Extra]: http://www.michelf.com/projects/php-markdown/extra/
 * 
 * @category Solar
 * 
 * @package Solar_Markdown_Extra
 * 
 * @author Michel Fortin <http://www.michelf.com/projects/php-markdown/>
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Extra.php 2933 2007-11-09 20:37:35Z moraes $
 * 
 * @todo Implement the markdown-in-html portion of Markdown-Extra.
 * 
 */
 
require_once 'Markdown.php'; 

require_once 'Markdown/Plugin.php'; 
 
require_once 'Markdown/Plugin/Prefilter.php';
require_once 'Markdown/Plugin/StripLinkDefs.php';
require_once 'Markdown/Extra/Header.php';
require_once 'Markdown/Extra/Table.php';
require_once 'Markdown/Plugin/HorizRule.php';
require_once 'Markdown/Plugin/List.php';
require_once 'Markdown/Extra/DefList.php';
require_once 'Markdown/Plugin/CodeBlock.php';
require_once 'Markdown/Plugin/BlockQuote.php';
//require_once 'Markdown/Plugin/Html.php'; 
require_once 'Markdown/Extra/Html.php';
require_once 'Markdown/Plugin/Paragraph.php';
require_once 'Markdown/Plugin/CodeSpan.php';
require_once 'Markdown/Plugin/Image.php';
require_once 'Markdown/Plugin/Link.php';
require_once 'Markdown/Plugin/Uri.php';
require_once 'Markdown/Plugin/Encode.php';
require_once 'Markdown/Plugin/AmpsAngles.php';
require_once 'Markdown/Extra/EmStrong.php';
require_once 'Markdown/Plugin/Break.php';
 
class Solar_Markdown_Extra extends Solar_Markdown {
    
    /**
     * 
     * User-defined configuration values.
     * 
     * This sets the plugins and their processing order for the engine.
     * 
     * @var array
     * 
     */
    protected $_config = array(
		'tab_width' => 4,
        
        'tidy' => false,
        'plugins' => array(
            
            // pre-processing on the source as a whole
            'Solar_Markdown_Plugin_Prefilter',
            'Solar_Markdown_Plugin_StripLinkDefs',
            
            // blocks
            'Solar_Markdown_Extra_Header',
            'Solar_Markdown_Extra_Table',
            'Solar_Markdown_Plugin_HorizRule',
            'Solar_Markdown_Plugin_List',
            'Solar_Markdown_Extra_DefList',
            'Solar_Markdown_Plugin_CodeBlock',
            'Solar_Markdown_Plugin_BlockQuote',
            //'Solar_Markdown_Plugin_Html', 
			'Solar_Markdown_Extra_Html',
            'Solar_Markdown_Plugin_Paragraph',
            
            // spans
            'Solar_Markdown_Plugin_CodeSpan',
            'Solar_Markdown_Plugin_Image',
            'Solar_Markdown_Plugin_Link',
            'Solar_Markdown_Plugin_Uri',
            'Solar_Markdown_Plugin_Encode',
            'Solar_Markdown_Plugin_AmpsAngles',
            'Solar_Markdown_Extra_EmStrong',
            'Solar_Markdown_Plugin_Break',
        ),
    );
}

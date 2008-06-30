<?php
/**
 * 
 * Markdown engine rules for wiki markup.
 * 
 * This class implements a plugin set for the Markdown-Extra syntax;
 * be sure to visit the [Markdown-Extra][] site for syntax examples.
 * 
 * [Markdown-Extra]: http://www.michelf.com/projects/php-markdown/extra/
 * 
 * @category Solar
 * 
 * @package Solar_Markdown_Wiki
 * 
 * @author Paul M. Jones <pmjones@solarphp.com>
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 * @version $Id: Wiki.php 2933 2007-11-09 20:37:35Z moraes $
 * 
 * @todo Implement the markdown-in-html portion of Markdown-Extra.
 * 
 */
require_once 'Markdown.php'; 
require_once 'Markdown/Plugin.php';
require_once 'Markdown/Wiki/Filter.php';
require_once 'Markdown/Plugin/StripLinkDefs.php';
require_once 'Markdown/Wiki/MethodSynopsis.php';
require_once 'Markdown/Wiki/Header.php';
require_once 'Markdown/Extra/Table.php';
require_once 'Markdown/Plugin/HorizRule.php';
require_once 'Markdown/Plugin/List.php';
require_once 'Markdown/Extra/DefList.php';
require_once 'Markdown/Wiki/ColorCodeBlock.php';
require_once 'Markdown/Plugin/CodeBlock.php';
require_once 'Markdown/Plugin/BlockQuote.php';
require_once 'Markdown/Extra/Html.php';
require_once 'Markdown/Plugin/Paragraph.php';
require_once 'Markdown/Wiki/CodeSpan.php';
require_once 'Markdown/Wiki/Link.php';
require_once 'Markdown/Plugin/Image.php';
require_once 'Markdown/Plugin/Link.php';
require_once 'Markdown/Plugin/Uri.php';
require_once 'Markdown/Plugin/Encode.php';
require_once 'Markdown/Extra/EmStrong.php';
require_once 'Markdown/Plugin/Break.php';
require_once 'Markdown/Wiki/BlockBorder.php';
//require_once 'Markdown/Wiki/Escape.php'; 
 
class Solar_Markdown_Wiki extends Solar_Markdown {
    
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
            
            // highest-priority prepare and cleanup
            'Solar_Markdown_Wiki_Filter',
            
            // for Markdown images and links
            'Solar_Markdown_Plugin_StripLinkDefs',
            
            // blocks
			'Solar_Markdown_Wiki_BlockBorder',
            'Solar_Markdown_Wiki_MethodSynopsis',
            'Solar_Markdown_Wiki_Header',
			
            'Solar_Markdown_Extra_Table',
			'Solar_Markdown_Wiki_ColorCodeBlock',
			
			'Solar_Markdown_Extra_Html',
            'Solar_Markdown_Plugin_HorizRule',
            'Solar_Markdown_Plugin_List',
            'Solar_Markdown_Extra_DefList',
            'Solar_Markdown_Plugin_CodeBlock',
            'Solar_Markdown_Plugin_BlockQuote',
			
            'Solar_Markdown_Plugin_Paragraph',
            
            // spans
            'Solar_Markdown_Wiki_CodeSpan',
            'Solar_Markdown_Wiki_Link',
            'Solar_Markdown_Plugin_Image',
            'Solar_Markdown_Plugin_Link',
            'Solar_Markdown_Plugin_Uri',
            'Solar_Markdown_Plugin_Encode',
            'Solar_Markdown_Extra_EmStrong',
            'Solar_Markdown_Plugin_Break',
            //'Solar_Markdown_Wiki_Escape',
        ),
    );
}

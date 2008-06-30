<?php
/**
 * 
 * Block plugin to save literal blocks of HTML.
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
 * @version $Id: Html.php 2933 2007-11-09 20:37:35Z moraes $
 * 
 */
class Solar_Markdown_Extra_Html extends Solar_Markdown_Plugin {
    
    /**
     * 
     * Run this plugin during the "prepare" phase.
     * 
     * @var bool
     * 
     */
    //protected $_is_prepare = true;
    
    /**
     * 
     * This is a block plugin.
     * 
     * @var bool
     * 
     */
    protected $_is_block = true;
    
    /**
     * 
     * Run this plugin during the "cleanup" phase.
     * 
     * @var bool
     * 
     */
    protected $_is_cleanup = true;
    
    /**
     * 
     * When preparing text for parsing, remove pre-existing HTML blocks.
     * 
     * @param string $text The source text.
     * 
     * @return string The transformed XHTML.
     * 
     */
    public function prepare($text)
    {
        return $this->parse($text);
    }
    
    /**
     * 
     * When cleaning up after parsing, replace all HTML tokens with
     * their saved blocks.
     * 
     * @param string $text The source text.
     * 
     * @return string The transformed XHTML.
     * 
     */
    public function cleanup($text)
    {
        return $this->_unHtmlToken($text);
    }
	
		### HTML Block Parser ###
	
	# Tags that are always treated as block tags:
	var $block_tags = 'p|div|h[1-6]|blockquote|pre|table|dl|ol|ul|address|form|fieldset|iframe|hr|legend';
	
	# Tags treated as block tags only if the opening tag is alone on it's line:
	var $context_block_tags = 'script|noscript|math|ins|del';
	
	# Tags where markdown="1" default to span mode:
	var $contain_span_tags = 'p|h[1-6]|li|dd|dt|td|th|legend|address';
	
	# Tags which must not have their contents modified, no matter where 
	# they appear:
	var $clean_tags = 'script|math';
	
	# Tags that do not need to be closed.
	var $auto_close_tags = 'hr|img';
    

    public function parse($text)
    {
		list($text, ) = $this->_hashHTMLBlocks_inMarkdown($text);
		
		return $text;
    }
    
	function _hashHTMLBlocks_inMarkdown($text, $indent = 0, 
										$enclosing_tag = '', $span = false)
	{
	#
	# Parse markdown text, calling _HashHTMLBlocks_InHTML for block tags.
	#
	# *   $indent is the number of space to be ignored when checking for code 
	#     blocks. This is important because if we don't take the indent into 
	#     account, something like this (which looks right) won't work as expected:
	#
	#     <div>
	#         <div markdown="1">
	#         Hello World.  <-- Is this a Markdown code block or text?
	#         </div>  <-- Is this a Markdown code block or a real tag?
	#     <div>
	#
	#     If you don't like this, just don't indent the tag on which
	#     you apply the markdown="1" attribute.
	#
	# *   If $enclosing_tag is not empty, stops at the first unmatched closing 
	#     tag with that name. Nested tags supported.
	#
	# *   If $span is true, text inside must treated as span. So any double 
	#     newline will be replaced by a single newline so that it does not create 
	#     paragraphs.
	#
	# Returns an array of that form: ( processed text , remaining text )
	#
		if ($text === '') return array('', '');

		# Regex to check for the presense of newlines around a block tag.
		$newline_match_before = '/(?:^\n?|\n\n)*$/';
		$newline_match_after = 
			'{
				^						# Start of text following the tag.
				(?:[ ]*<!--.*?-->)?		# Optional comment.
				[ ]*\n					# Must be followed by newline.
			}xs';
		
		# Regex to match any tag.
		$block_tag_match =
			'{
				(					# $2: Capture hole tag.
					</?					# Any opening or closing tag.
						(?:				# Tag name.
							'.$this->block_tags.'			|
							'.$this->context_block_tags.'	|
							'.$this->clean_tags.'        	|
							(?!\s)'.$enclosing_tag.'
						)
						\s*				# Whitespace.
						(?'.'>
							".*?"		|	# Double quotes (can contain `>`)
							\'.*?\'   	|	# Single quotes (can contain `>`)
							.+?				# Anything but quotes and `>`.
						)*?
					>					# End of tag.
				|
					<!--    .*?     -->	# HTML Comment
				|
					<\?.*?\?'.'> | <%.*?%>	# Processing instruction
				|
					<!\[CDATA\[.*?\]\]>	# CData Block
				)
			}xs';

		
		$depth = 0;		# Current depth inside the tag tree.
		$parsed = "";	# Parsed text that will be returned.

		#
		# Loop through every tag until we find the closing tag of the parent
		# or loop until reaching the end of text if no parent tag specified.
		#
		do {
			#
			# Split the text using the first $tag_match pattern found.
			# Text before  pattern will be first in the array, text after
			# pattern will be at the end, and between will be any catches made 
			# by the pattern.
			#
			$parts = preg_split($block_tag_match, $text, 2, 
								PREG_SPLIT_DELIM_CAPTURE);
			
			# If in Markdown span mode, add a empty-string span-level hash 
			# after each newline to prevent triggering any block element.
			if ($span) {
				$void = $this->hashPart("", ':');
				$newline = "$void\n";
				$parts[0] = $void . str_replace("\n", $newline, $parts[0]) . $void;
			}
			
			$parsed .= $parts[0]; # Text before current tag.
			
			# If end of $text has been reached. Stop loop.
			if (count($parts) < 3) {
				$text = "";
				break;
			}
			
			$tag  = $parts[1]; # Tag to handle.
			$text = $parts[2]; # Remaining text after current tag.
			
			#
			# Check for: Tag inside code block or span
			#
			if (# Find current paragraph
				preg_match('/(?'.'>^\n?|\n\n)((?'.'>.+\n?)*?)$/', $parsed, $matches) &&
				(
				# Then match in it either a code block...
				preg_match('/^ {'.($indent+4).'}.*(?'.'>\n {'.($indent+4).'}.*)*'.
							'(?!\n)$/', $matches[1], $x) ||
				# ...or unbalenced code span markers. (the regex matches balenced)
				!preg_match('/^(?'.'>[^`]+|(`+)(?'.'>[^`]+|(?!\1[^`])`)*?\1(?!`))*$/s',
							 $matches[1])
				))
			{
				# Tag is in code block or span and may not be a tag at all. So we
				# simply skip the first char (should be a `<`).
				$parsed .= $tag{0};
				$text = substr($tag, 1) . $text; # Put back $tag minus first char.
			}
			#
			# Check for: Opening Block level tag or
			#            Opening Content Block tag (like ins and del) 
			#               used as a block tag (tag is alone on it's line).
			#
			else if (preg_match("{^<(?:$this->block_tags)\b}", $tag) ||
				(	preg_match("{^<(?:$this->context_block_tags)\b}", $tag) &&
					preg_match($newline_match_before, $parsed) &&
					preg_match($newline_match_after, $text)	)
				)
			{
				# Need to parse tag and following text using the HTML parser.
				list($block_text, $text) = 
					$this->_hashHTMLBlocks_inHTML($tag . $text, "hashBlock", true);
				
				# Make sure it stays outside of any paragraph by adding newlines.
				$parsed .= "\n\n$block_text\n\n";
			}
			#
			# Check for: Clean tag (like script, math)
			#            HTML Comments, processing instructions.
			#
			else if (preg_match("{^<(?:$this->clean_tags)\b}", $tag) ||
				$tag{1} == '!' || $tag{1} == '?')
			{
				# Need to parse tag and following text using the HTML parser.
				# (don't check for markdown attribute)
				list($block_text, $text) = 
					$this->_hashHTMLBlocks_inHTML($tag . $text, "hashClean", false);
				
				$parsed .= $block_text;
			}
			#
			# Check for: Tag with same name as enclosing tag.
			#
			else if ($enclosing_tag !== '' &&
				# Same name as enclosing tag.
				preg_match("{^</?(?:$enclosing_tag)\b}", $tag))
			{
				#
				# Increase/decrease nested tag count.
				#
				if ($tag{1} == '/')						$depth--;
				else if ($tag{strlen($tag)-2} != '/')	$depth++;

				if ($depth < 0) {
					#
					# Going out of parent element. Clean up and break so we
					# return to the calling function.
					#
					$text = $tag . $text;
					break;
				}
				
				$parsed .= $tag;
			}
			else {
				$parsed .= $tag;
			}
		} while ($depth >= 0);
		
		return array($parsed, $text);
	}
	function _hashHTMLBlocks_inHTML($text, $hash_method, $md_attr) {
	#
	# Parse HTML, calling _HashHTMLBlocks_InMarkdown for block tags.
	#
	# *   Calls $hash_method to convert any blocks.
	# *   Stops when the first opening tag closes.
	# *   $md_attr indicate if the use of the `markdown="1"` attribute is allowed.
	#     (it is not inside clean tags)
	#
	# Returns an array of that form: ( processed text , remaining text )
	#
		if ($text === '') return array('', '');
		
		# Regex to match `markdown` attribute inside of a tag.
		$markdown_attr_match = '
			{
				\s*			# Eat whitespace before the `markdown` attribute
				markdown
				\s*=\s*
				(?:
					(["\'])		# $1: quote delimiter		
					(.*?)		# $2: attribute value
					\1			# matching delimiter	
				|
					([^\s>]*)	# $3: unquoted attribute value
				)
				()				# $4: make $3 always defined (avoid warnings)
			}xs';
		
		# Regex to match any tag.
		$tag_match = '{
				(					# $2: Capture hole tag.
					</?					# Any opening or closing tag.
						[\w:$]+			# Tag name.
						\s*				# Whitespace.
						(?'.'>
							".*?"		|	# Double quotes (can contain `>`)
							\'.*?\'   	|	# Single quotes (can contain `>`)
							.+?				# Anything but quotes and `>`.
						)*?
					>					# End of tag.
				|
					<!--    .*?     -->	# HTML Comment
				|
					<\?.*?\?'.'> | <%.*?%>	# Processing instruction
				|
					<!\[CDATA\[.*?\]\]>	# CData Block
				)
			}xs';
		
		$original_text = $text;		# Save original text in case of faliure.
		
		$depth		= 0;	# Current depth inside the tag tree.
		$block_text	= "";	# Temporary text holder for current text.
		$parsed		= "";	# Parsed text that will be returned.

		#
		# Get the name of the starting tag.
		#
		if (preg_match("/^<([\w:$]*)\b/", $text, $matches))
			$base_tag_name = $matches[1];

		#
		# Loop through every tag until we find the corresponding closing tag.
		#
		do {
			#
			# Split the text using the first $tag_match pattern found.
			# Text before  pattern will be first in the array, text after
			# pattern will be at the end, and between will be any catches made 
			# by the pattern.
			#
			$parts = preg_split($tag_match, $text, 2, PREG_SPLIT_DELIM_CAPTURE);
			
			if (count($parts) < 3) {
				#
				# End of $text reached with unbalenced tag(s).
				# In that case, we return original text unchanged and pass the
				# first character as filtered to prevent an infinite loop in the 
				# parent function.
				#
				return array($original_text{0}, substr($original_text, 1));
			}
			
			$block_text .= $parts[0]; # Text before current tag.
			$tag         = $parts[1]; # Tag to handle.
			$text        = $parts[2]; # Remaining text after current tag.
			
			#
			# Check for: Auto-close tag (like <hr/>)
			#			 Comments and Processing Instructions.
			#
			if (preg_match("{^</?(?:$this->auto_close_tags)\b}", $tag) ||
				$tag{1} == '!' || $tag{1} == '?')
			{
				# Just add the tag to the block as if it was text.
				$block_text .= $tag;
			}
			else {
				#
				# Increase/decrease nested tag count. Only do so if
				# the tag's name match base tag's.
				#
				if (preg_match("{^</?$base_tag_name\b}", $tag)) {
					if ($tag{1} == '/')						$depth--;
					else if ($tag{strlen($tag)-2} != '/')	$depth++;
				}
				
				#
				# Check for `markdown="1"` attribute and handle it.
				#
				if ($md_attr && 
					preg_match($markdown_attr_match, $tag, $attr_m) &&
					preg_match('/^1|block|span$/', $attr_m[2] . $attr_m[3]))
				{
					# Remove `markdown` attribute from opening tag.
					$tag = preg_replace($markdown_attr_match, '', $tag);
					
					# Check if text inside this tag must be parsed in span mode.
					$this->mode = $attr_m[2] . $attr_m[3];
					$span_mode = $this->mode == 'span' || $this->mode != 'block' &&
						preg_match("{^<(?:$this->contain_span_tags)\b}", $tag);
					
					# Calculate indent before tag.
					preg_match('/(?:^|\n)( *?)(?! ).*?$/', $block_text, $matches);
					$indent = strlen($matches[1]);
					
					# End preceding block with this tag.
					$block_text .= $tag;
					$parsed .= $this->$hash_method($block_text);
					
					# Get enclosing tag name for the ParseMarkdown function.
					preg_match('/^<([\w:$]*)\b/', $tag, $matches);
					$tag_name = $matches[1];
					
					# Parse the content using the HTML-in-Markdown parser.
					list ($block_text, $text)
						= $this->_hashHTMLBlocks_inMarkdown($text, $indent, 
														$tag_name, $span_mode);
					
					# Outdent markdown text.
					if ($indent > 0) {
						$block_text = preg_replace("/^[ ]{1,$indent}/m", "", 
													$block_text);
					}
					
					# Append tag content to parsed text.
					if (!$span_mode)	$parsed .= "\n\n$block_text\n\n";
					else				$parsed .= "$block_text";
					
					# Start over a new block.
					$block_text = "";
				}
				else $block_text .= $tag;
			}
			
		} while ($depth > 0);
		
		#
		# Hash last block text that wasn't processed inside the loop.
		#
		$parsed .= $this->$hash_method($block_text);
		
		return array($parsed, $text);
	}
	
	function hashClean($text) {
	#
	# Called whenever a tag must be hashed when a function insert a "clean" tag
	# in $text, it pass through this function and is automaticaly escaped, 
	# blocking invalid nested overlap.
	#
		return $this->_toHtmlToken($text);
	}
	
	function hashBlock($text) {
	#
	# Shortcut function for hashPart with block-level boundaries.
	#
		return $this->_toHtmlToken($text);
	}
} 
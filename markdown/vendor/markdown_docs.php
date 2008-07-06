<?php
/*
  --------------------------------------------------------------------
                           TypeFriendly
                 Copyright (c) 2008 Invenzzia Team
                    http://www.invenzzia.org/
                See README for more author details
  --------------------------------------------------------------------
  This file is part of TypeFriendly.
                                                                   
  TypeFriendly is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  TypeFriendly is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with TypeFriendly. If not, see <http://www.gnu.org/licenses/>.
*/
// $Id$

	@define('MARKDOWN_PARSER_CLASS', 'MarkdownDocs_Parser');
	
	require_once 'markdown.php';
	require_once TF_GESHI.'geshi.php';
	
	/*
		Original Markdown parser is written in PHP4, so I don't think it is
		necessary to use 'private' and 'public' in functions here
		- eXtreme
	*/
	
	class MarkdownDocs_Parser extends MarkdownExtra_Parser
	{
		function _doCodeBlocks_callback($matches)
		{
			$codeblock = $matches[1];
			
			$codeblock = $this->outdent($codeblock);
	
			# trim leading newlines and trailing newlines
			$codeblock = preg_replace('/\A\n+|\n+\z/', '', $codeblock);
			
			$clear = true;
			$codeblock = $this->_codeBlockHighlighter($codeblock, $clear);
	
			if($clear)
			{
				$codeblock = htmlspecialchars($codeblock, ENT_NOQUOTES);
				$codeblock = "<pre><code>$codeblock\n</code></pre>";
			}
			return "\n\n".$this->hashBlock($codeblock)."\n\n";
		} // end _doCodeBlocks_callback();
		
		function _doFencedCodeBlocks_callback($matches)
		{
			$codeblock = $matches[2];
			
			$clear = true;
			$codeblock = $this->_codeBlockHighlighter($codeblock, $clear);
			
			if($clear)
			{
				$codeblock = htmlspecialchars($codeblock, ENT_NOQUOTES);
				$codeblock = preg_replace_callback('/^\n+/', array($this, '_doFencedCodeBlocks_newlines'), $codeblock);
				$codeblock = "<pre><code>$codeblock</code></pre>";
			}
			
			return "\n\n".$this->hashBlock($codeblock)."\n\n";
		} // end _doFencedCodeBlocks_callback();
		
		function _codeBlockHighlighter($codeblock, &$clear)
		{
			/*if($codeblock{0} == '>')
			{
				$codeblock = htmlspecialchars($codeblock, ENT_NOQUOTES);
				$codeblock = preg_replace_callback('/^\n+/', array($this, '_doFencedCodeBlocks_newlines'), $codeblock);
				$codeblock = "<pre class=\"console\"><code>$codeblock</code></pre>";
				
				$clear = false;			
				return $codeblock;	
			}
			elseif($codeblock{0}.$codeblock{1} == '\>')
			{
				$codeblock = substr($codeblock, 1);
				return $codeblock;
			}
			else*/if(preg_match('/^((\\\){0,2}\[([a-zA-Z0-9\-_]+)\]\s*\n)/', $codeblock, $matches))
			{
				
				if($matches[2] == '\\')
				{
					$codeblock = substr($codeblock, 1);
					return $codeblock;
				}
				
				$strlen = strlen($matches[1]);
				$parser = strtolower($matches[3]);
				
				if($strlen > 0)
				{
					$codeblock = substr($codeblock, $strlen);
					if($parser == 'console')
					{
						$codeblock = htmlspecialchars($codeblock, ENT_NOQUOTES);
						$codeblock = preg_replace_callback('/^\n+/', array($this, '_doFencedCodeBlocks_newlines'), $codeblock);
						$codeblock = "<pre class=\"console\"><code>$codeblock</code></pre>";
					}
					else
					{
						$codeblock = preg_replace('/\n+$/', '', $codeblock);
						$geshi = new GeSHi($codeblock, $parser);
					
						$codeblock = $geshi->parse_code();
					}
					
					$clear = false;
				}
			}
			return $codeblock;						
		} // end _codeBlockHighlighter();
		
		function _doBlockQuotes_callback($matches)
		{
			$bq = $matches[1];
			# trim one level of quoting - trim whitespace-only lines
			$bq = preg_replace('/^[ ]*>[ ]?|^[ ]+$/m', '', $bq);
			                                                          
			$addClass = '';
			if(preg_match('/^((\\\){0,2}\[([a-zA-Z0-9\-_]+)\]\s*\n)/', $bq, $matches))
			{
				if($matches[2] == '\\')
				{
					$bq = substr($bq, 1);
				}
				else
				{	
					$strlen = strlen($matches[1]);
					$parser = strtolower($matches[3]);
					
					if($strlen > 0)
					{
						$bq = substr($bq, $strlen);
						$addClass = ' class="'.$parser.'"';
					}
				}
			}
			
			$bq = $this->runBlockGamut($bq);		# recurse
			
			$bq = preg_replace('/^/m', "  ", $bq);
			# These leading spaces cause problem with <pre> content, 
			# so we need to fix that:
			$bq = preg_replace_callback('{(\s*<pre>.+?</pre>)}sx', array($this, '_DoBlockQuotes_callback2'), $bq);
			
			return "\n".$this->hashBlock("<blockquote$addClass>\n$bq\n</blockquote>")."\n\n";
		} // end _doBlockQuotes_callback();
		
		function _doBlockQuotes_callback2($matches)
		{
			$pre = $matches[1];
			$pre = preg_replace('/^  /m', '', $pre);
			return $pre;
		} // end _doBlockQuotes_callback2();
		
		function _doHeaders_attr($attr)
		{
			if(empty($attr)) return "";
			return " id=\"$attr\"";
		} // end _doHeaders_attr();
		
		function _doHeaders_callback_setext($matches)
		{
			if($matches[3] == '-' && preg_match('{^- }', $matches[1]))
				return $matches[0];
			
			$level = $matches[3]{0} == '=' ? 1 : 2;
			$level += 1;
			$attr  = $this->_doHeaders_attr($id =& $matches[2]);
			$block = "<h$level$attr>".$this->runSpanGamut($matches[1])."</h$level>";
			return "\n".$this->hashBlock($block)."\n\n";
		} // end _doHeaders_callback_setext();
		
		function _doHeaders_callback_atx($matches)
		{
			$level = strlen($matches[1]);
			$level += 1;
			if($level > 6)
				$level = 6;
				
			$attr  = $this->_doHeaders_attr($id =& $matches[3]);
			$block = "<h$level$attr>".$this->runSpanGamut($matches[2])."</h$level>";
			return "\n".$this->hashBlock($block)."\n\n";
		} // end _doHeaders_callback_atx();
	} // end MarkdownDocs_Parser;

<?php
	@define('MARKDOWN_PARSER_CLASS', 'MarkdownDocs_Parser');
	
	require_once 'markdown.php';
	require_once TF_GESHI.'geshi.php';
	
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
		}
		
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
		}
		
		function _codeBlockHighlighter($codeblock, &$clear)
		{
			if($codeblock{0} == '>')
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
			elseif(preg_match('/^((\\\)?\[([a-zA-Z0-9\-_]+)\]\s*\n)/', $codeblock, $matches))
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
						$geshi = new GeSHi($codeblock, $parser);
					
						$codeblock = $geshi->parse_code();
					}
					
					$clear = false;
				}
			}
			return $codeblock;						
		}
		
		function _doBlockQuotes_callback($matches)
		{
			$bq = $matches[1];
			# trim one level of quoting - trim whitespace-only lines
			$bq = preg_replace('/^[ ]*>[ ]?|^[ ]+$/m', '', $bq);
			$bq = $this->runBlockGamut($bq);		# recurse
	
			$bq = preg_replace('/^/m', "  ", $bq);
			# These leading spaces cause problem with <pre> content, 
			# so we need to fix that:
			$bq = preg_replace_callback('{(\s*<pre>.+?</pre>)}sx', array($this, '_DoBlockQuotes_callback2'), $bq);
	
			return "\n".$this->hashBlock("<blockquote>\n$bq\n</blockquote>")."\n\n";
		}
		
		function _doBlockQuotes_callback2($matches)
		{
			$pre = $matches[1];
			$pre = preg_replace('/^  /m', '', $pre);
			return $pre;
		}
		
		function _doHeaders_attr($attr)
		{
			if(empty($attr)) return "";
			return " id=\"$attr\"";
		}
		
		function _doHeaders_callback_setext($matches)
		{
			if($matches[3] == '-' && preg_match('{^- }', $matches[1]))
				return $matches[0];
			
			$level = $matches[3]{0} == '=' ? 1 : 2;
			$level += 1;
			$attr  = $this->_doHeaders_attr($id =& $matches[2]);
			$block = "<h$level$attr>".$this->runSpanGamut($matches[1])."</h$level>";
			return "\n".$this->hashBlock($block)."\n\n";
		}
		
		function _doHeaders_callback_atx($matches)
		{
			$level = strlen($matches[1]);
			$level += 1;
			if($level > 6)
				$level = 6;
				
			$attr  = $this->_doHeaders_attr($id =& $matches[3]);
			$block = "<h$level$attr>".$this->runSpanGamut($matches[2])."</h$level>";
			return "\n".$this->hashBlock($block)."\n\n";
		}
	} // end class;

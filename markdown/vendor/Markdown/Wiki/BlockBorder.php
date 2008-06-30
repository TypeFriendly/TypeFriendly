<?php

class Solar_Markdown_Wiki_BlockBorder extends Solar_Markdown_Plugin {
    

    protected $_is_block = true;
    

    public function parse($text)
    {
        $tab_width = $this->_getTabWidth();
        
        $regex = '{
            <block\s+color="([^"]+)"\s*>\s*\n                          # $1 = the colorization type, if any
                (                                       # $2 = the code block -- one or more lines, starting with a space/tab
                  (?:(?!\s*<\/block>)                                   
                    .*?\n+                               
                  )+                              
                )                                       
            \s*<\/block>\s*                                  # end of the block
        }mx';                                           
        
        $text = preg_replace_callback(
            $regex,
            array($this, '_parse'),
            $text
        );
        
        return $text;
    }

    protected function _parse($matches)
    {
		//var_dump($matches);
            $code = "\n\n<div class=\"block-border border-".$matches[1]."\" markdown=\"1\">" . $matches[2] . "</div>\n\n";
        
        // done
        return "\n"
             . $code
             . "\n";
    }
} 
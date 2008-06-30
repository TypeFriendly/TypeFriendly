<?php
	set_include_path(TF_MARKDOWN.'vendor'.PATH_SEPARATOR.get_include_path());
	require(TF_MARKDOWN.'vendor/Markdown.php');
	require(TF_MARKDOWN.'vendor/Markdown/Extra.php');
	require(TF_MARKDOWN.'vendor/Markdown/Wiki.php');

	function Markdown($text)
	{
		static $parser;
		if(!isset($parser))
		{
			$parser = new Solar_Markdown_Wiki;
		}

		// Transform text using parser.
		return $parser->transform($text);
	} // end Markdown();

?>
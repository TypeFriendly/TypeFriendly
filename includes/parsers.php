<?php

	class tfParsers
	{
		static private $instance;
		
		private function __construct()
		{
			
		} // end __construct();
		
		static public function get()
		{
			if(is_null(tfParsers::$instance))
			{
				tfParsers::$instance = new tfParsers;
			}
			return tfParsers::$instance;
		} // end get();
		
		public function tfdoc($filename)
		{
			if(!file_exists($filename))
			{
				throw new SystemException('tfdoc: '.$filename.' - file not found');
			}

			$f = fopen($filename, 'r');
			
			$data = array();
			
			// Part 1 - parsing titles
			$ok = true;
			do
			{
				if($ok)
				{
					$line = trim(fgets($f));
				}
				if(preg_match('/^([a-zA-Z\-]+)\:\\s*$/', $line, $found))
				{
					$hash = $found[1];
					$data[$hash] = array();
					$line = trim(fgets($f));
					while(preg_match('/^[ ]?\- (.+)$/', $line, $found))
					{
						$data[$hash][] = $found[1];
						$line = trim(fgets($f));
					}
					$ok = false;
					continue;
				}
				if(preg_match('/^([a-zA-Z\-]+)\:( )?(.+)$/', $line, $found))
				{
					$data[$found[1]] = trim($found[3]);
					$ok = true;
				}
				$ok = true;
			}
			while(!$this -> separator($line));
			$data['Content'] = '';
			while(!feof($f))
			{
				$data['Content'] .= fread($f, 8192);
			}
			fclose($f);
			return $data;
		} // end tfdoc();

		public function parse($text)
		{
			return Markdown($text);
		} // end parse();
		
		public function config($filename)
		{
			$items = parse_ini_file($filename, true);
			
			if(!is_array($items))
			{
				throw new SystemException('The specified file: '.$filename.' is not a valid configuration file.');
			}
			return $items;
		} // end config();

		private function separator($text)
		{
			return preg_match('/^[\-\=\*]{3,}$/', trim($text));
		} // end separator();

	} // end tfParsers;

?>

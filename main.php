<?php

	class tfMain extends tfApplication
	{
		private $args;

		public function parseArgs(tfProgram $prg)
		{		
			$this -> args = array(
				'-l' => array(0 => OPT_OPTIONAL, TYPE_STRING),
				'-l' => array(0 => OPT_OPTIONAL, TYPE_STRING),
				'-o' => array(0 => OPT_OPTIONAL, TYPE_STRING),
				'#path' => array(0 => OPT_REQUIRED, TYPE_PATH),					
			);
			try
			{
				$prg -> console -> testArgs($this -> args);
			
				if($prg -> console -> testArgNum(1, 5))
				{
					$this -> action = 'work';
				}
	
				if(isset($this -> args['-c']))
				{
					$this -> action = 'compare';
				}
			}
			catch(Exception $e)
			{
				$this -> action = 'main';
			}
		} // end parseArgs();

		public function main(tfProgram $prg)
		{
			$out = $prg -> console -> stdout;
			
			$out -> writeHr('=', 80);
			$out -> space();
			$out -> center('TypeFriendly', 80);
			$out -> center('Documentation building tool', 80);
			$out -> center('(c) Invenzzia Group 2008', 80);
			$out -> center('www.invenzzia.org', 80);
			$out -> space();
			$out -> center('This program is free software. You can use, redistribute and/or modify it', 80);
			$out -> center('under the terms of GNU General Public License 3 or later. The license', 80);
			$out -> center('should be provided within the sources. The program comes with', 80);
			$out -> center('ABSOLUTELY NO WARRANTY!', 80);
			$out -> space();
			$out -> writeHr('=', 80);
			$out -> writeln('Usage:');
			$out -> writeln('   typefriendly.php [OPTIONS] DOCUMENTATION_PATH');
			$out -> space();
			$out -> writeln('Options:');
			$out -> writeln('   -c language - compare the specified language with the main language used in');
			$out -> writeln('                 the manual.');
			$out -> writeln('   -l language - render the specified language. If not set, the base');
			$out -> writeln('                 language settings are used.');
			$out -> writeln('   -o output   - render only the specified output. The output');
			$out -> writeln('                 must be declared within the project.');
		} // end main();
		
		public function work(tfProgram $prg)
		{
			$prg -> loadLibrary('parsers');
			$prg -> loadLibrary('output');
			$prg -> loadLibrary('project');
			
			$project = new tfProject($this -> args['#path']);
			tfProject::set($project);
			
			// Choose the language
			if(isset($this -> args['-l']))
			{
				$project -> setLanguage($this -> args['-l']);
			}
			else
			{
				$project -> setLanguage($project -> config['baseLanguage']);
			}

			if(isset($this -> args['-o']))
			{
				$prg -> console -> stdout -> writeln('Processing the files.');
				$project -> loadItems();
				$prg -> console -> stdout -> writeln('Starting '.$this -> args['-o'].'.');
				$project -> setOutput($this -> args['-o']);
				$project -> copyMedia();
				$project -> generate();	
			}
			else
			{
				$prg -> console -> stdout -> writeln('Processing the files.');
				$project -> loadItems();

				foreach($project -> config['outputs'] as $out)
				{
					$prg -> console -> stdout -> writeln('Starting '.$out.'.');
					$project -> setOutput($out);
					$project -> copyMedia();
					$project -> generate();	
				}
			}
		} // end work();
		
		public function compare(tfProgram $prg)
		{
			$prg -> loadLibrary('project');

			$project = new tfProject($this -> args['#path']);
			tfProject::set($project);
			
			$project -> versionCompare($this -> args['-c']);
		} // end compare();
	} // end tfMain;

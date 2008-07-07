<?php

	class web extends standardOutput
	{
		public function init($project, $path)
		{
			$this->project = $project;
			$this->path = $path;
			// Generating TOC.

			$prog = tfProgram::get();
			$prog->console->stderr->writeln('The "web" output is not yet implemented.');
		} // end init();

		public function generate($page)
		{		

		} // end generate();

		public function close()
		{
		
		} // end close();
	
		public function toAddress($page)
		{
			return $page.'.html';
		} // end toAddress();
	} // end xhtml;

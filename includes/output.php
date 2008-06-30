<?php

	abstract class standardOutput
	{
		protected $project;
		protected $path;
	
		public function init($project, $path)
		{
			$this -> project = $project;
			$this -> path = $path;
		} // end init();
	
		abstract public function generate($page);

		public function close()
		{
			/*
			 * null
			 */
		} // end close();
		
		abstract public function toAddress($page);
	} // end standardOutput();

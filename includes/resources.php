<?php

	class tfResources
	{
		public $outputs;
		
		private $pr;
		
		static private $instance;
		
		static public function get()
		{
			if(is_null(tfResources::$instance))
			{
				tfResources::$instance = new tfResources;
			}
			return tfResources::$instance;
		} // end get();
		
		private function __construct()
		{
			$app = tfProgram::get();
			
			// Load outputs
			$list = $app -> fs -> listDirectory('outputs/', true, false);
			foreach($list as &$item)
			{
				if(strpos($item, '.php') !== false)
				{
					$item = substr($item, 0, strlen($item) - 4);
				}
			}
			$this -> outputs = $list;
		} // end __construct();
	
	
	} // end tfResources;

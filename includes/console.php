<?php
	define('OPT_REQUIRED', 0);
	define('OPT_OPTIONAL', 1);

	define('TYPE_STRING', 0);
	define('TYPE_INTEGER', 1);
	define('TYPE_PATH', 2);

	class tfStream
	{
		private $stream;
		private $nl;
		
		public function __construct($stream)
		{
			switch(USED_OS)
			{
				case 'Windows':
					$this -> nl = "\r\n";
					break;
				case 'Linux':
				case 'FreeBSD':
				case 'NetBSD':
				case 'OpenBSD':
					$this -> nl = "\n";
					break;
				case 'MacOS':
					$this -> nl = "\r";
					break;
				default:
					$this -> nl = "\r\n";
			}

			$this -> stream = $stream;
			if(!is_resource($this -> stream))
			{
				throw new Exception('Stream exception: an attempt to initialize an empty stream.');
			}
		} // end __construct();
		
		public function __destruct()
		{
			fclose($this -> stream);
		} // end __destruct();
		
		public function write($text)
		{
			fwrite($this->stream, $text);
		} // end write();
		
		public function writeln($text)
		{			
			fwrite($this -> stream, $text.$this -> nl);
		} // end writeln();
		
		public function center($text, $length)
		{
			$tl = strlen($text);
			if($tl > $length)
			{
				fwrite($this -> stream, $text.$this -> nl);
			}
			else
			{
				fwrite($this -> stream, str_repeat(' ', floor($length/2) - floor($tl/2)).$text.$this->nl);
			}
		} // end center();
		
		public function space()
		{
			fwrite($this -> stream, $this -> nl);
		} // end space();
		
		public function writeHr($type = '-', $repeat=30)
		{
			fwrite($this -> stream, str_repeat($type, $repeat).$this->nl);
		} // end writeHr();
		
		public function read($length = 80)
		{
			return fread($this -> stream, $length);
		} // end read();		
	} // end tfStream();

	class tfConsole
	{
		private $args;
		public $stdin;
		public $stdout;
		public $stderr;
		public $os;

		public function __construct()
		{
			$this -> detectOS();
			
			$this -> stdin = new tfStream(STDIN);
			$this -> stdout = new tfStream(STDOUT);
			$this -> stderr = new tfStream(STDERR);
			
			$this -> args = $_SERVER['argv'];
		} // end __construct();
		
		public function testArgNum($from, $to = null)
		{
			$size = sizeof($this -> args) - 1;
			if(is_null($to))
			{
				return $size == $from;				
			}
			else
			{
				return ($size >= $from) && ($size <= $to);
			}
		} // end testArgNum();
		
		public function testArgs(&$list)
		{
			$i = 1;
			foreach($list as $name => &$item)
			{
				if($name[0] == '#')
				{
					if(isset($this -> args[$i]) && $this -> testValue($this->args[$i], $item[1]))
					{
						$item = $this -> args[$i];
						$i++;
					}
					else
					{
						if($item[0] == OPT_OPTIONAL)
						{
							continue;
						}
						throw new Exception('Invalid argument #'.$i.': '.$name.'.');
					}
				}
				else
				{
					if(isset($this -> args[$i]) && $this -> args[$i] == $name)
					{
						$i++;
						if($this -> testValue($this->args[$i], $item[1]))
						{
							$item = $this -> args[$i];
							$i++;
						}
						else
						{
							if($item[0] == OPT_OPTIONAL)
							{
								unset($list[$name]);
								continue;
							}
							throw new Exception('Invalid argument #'.$i.': '.$name.'.');
						}
					}
					elseif($item[0] == OPT_OPTIONAL)
					{
						unset($list[$name]);
						continue;
					}
					else
					{
						throw new Exception('Invalid argument #'.$i.': '.$name.'.');
					}
				}
			}
		} // end testArgs();
		
		private function testValue($value, $type)
		{
			switch($type)
			{
				case TYPE_STRING:
					return true;
				case TYPE_INTEGER:
					return ctype_digit($value);	
				case TYPE_PATH:
					return is_dir($value);
			}
		} // end testValue();
		
		public function detectOS()
		{
			$this -> os = php_uname('s');
			define('USED_OS', $this -> os);
		} // end detectOS();		
	} // end tfConsole;
	
	class tfProgram
	{
		public $console;
		public $outputs;
		public $fs;
		protected $app;
		
		static private $instance;
		
		private function __construct()
		{
			$this -> console = new tfConsole;
			
			// This is the master filesystem
			$this -> fs = new tfFilesystem;
			$this -> fs -> setMasterDirectory(TF_DIR, TF_READ | TF_EXEC);
		} // end __construct();
		
		static public function get()
		{
			if(is_null(tfProgram::$instance))
			{
				tfProgram::$instance = new tfProgram;
			}
			return tfProgram::$instance;
		} // end get();
		
		final public function loadModule($module)
		{
			if(!file_exists(TF_DIR.$module.'.php'))
			{
				$this -> console -> stderr -> writeln('Specified module has not been found: '.$module);
				die();
			}
			require_once(TF_DIR.$module.'.php');
			$className = 'tf'.ucfirst($module);
			
			if(!class_exists($className))
			{
				$this -> console -> stderr -> writeln('Error while loading a module: '.$module);
				die();
			}
			$this -> app = new $className;
		} // end load();
		
		final public function loadLibrary($name)
		{
			require_once(TF_DIR.'includes/'.$name.'.php');
		} // end loadLibrary();
		
		final public function run()
		{
			try
			{
				$this -> app -> parseArgs($this);
				$a = $this -> app -> action;
			
				if(!method_exists($this->app, $a))
				{
					$this -> app -> main($this);
				}
				else
				{
					$this -> app -> $a($this);
				}
			}
			catch(Exception $e)
			{
				fwrite(STDERR, "\nAn exception occured during the execution: \n".$e -> getMessage()."\n");
				die();			
			}
		} // end run();
	} // end tfProgram;
	
	abstract class tfApplication
	{
		public $action;
		
		abstract public function parseArgs(tfProgram $prg);
		abstract public function main(tfProgram $prg);
	} // end tfApplication();

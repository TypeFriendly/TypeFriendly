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

	function walkTrim(&$val)
	{
		$val = trim($val);
	} // end walkTrim();

	class SystemException extends Exception{}

	class tfItem
	{
		private $name;
		
		public $iParent = null;
		public $iNext = null;
		public $iPrev = null;
		public $firstChild = null;
		public $lastChild = null;
		
		private $level;
		
		public function __construct($name)
		{
			$this -> name = $name;
			$this -> level = substr_count($name, '.');
		} // end __construct();
		
		public function getName()
		{
			return $this -> name;
		} // end getName();
		
		public function getLevel()
		{
			return $this -> level;
		} // end getLevel();
	} // end tfItem;

	class tfProject
	{
		public $fs;
		
		public $config = array();
		public $sortHints = array();
		public $autoLinks = array();
		public $tree;
		
		private $output;
		private $outputObj;
		private $language;
	
		private $langs;
		private $prog;
		private $media = array();
		private $pages = array();
		private $parsed = false;
		
		static private $object;
		
		public function __construct($directory)
		{
			$p = tfParsers::get();
			$this -> prog = tfProgram::get();
			
			$this -> fs = new tfFilesystem;
			if(!$this -> fs -> setMasterDirectory($directory, TF_READ | TF_EXEC))
			{
				throw new SystemException('The project directory: '.$directory.' is not accessible');
			}
			
			$this -> config = $p -> config($this->fs->get('settings.ini'));
			// Some workaround
			if(isset($this -> config['outputs']))
			{
				$this -> config['outputs'] = explode(',', $this->config['outputs']);
				array_walk($this -> config['outputs'], 'walkTrim');
			}
			if(!isset($this -> config['navigation']))
			{
				$this -> config['navigation'] = 'tree';
			}
			
			
			// Now we have to check the directory accessibility
			if(!$this -> fs -> checkDirectories(array(
				'input/' => TF_READ | TF_EXEC,
				'output/' => TF_READ | TF_WRITE,
				'media/' => TF_READ | TF_EXEC
			)))
			{
				throw new Exception('The project does not have the necessary directories.');
			}
			
			// Retrieve the language versions
			$this -> langs = $this -> fs -> listDirectory('input/', false, true);
			// Produce the outputs		
		} // end __construct();
		
		static public function set(tfProject $project)
		{
			self::$object = $project;
		} // end set();
		
		static public function get()
		{
			return self::$object;
		} // end get();
		
		public function getOutput()
		{
			return $this -> outputObj;
		} // end getOutput();

		public function setLanguage($language)
		{
			if(!in_array($language, $this -> langs))
			{
				throw new SystemException('The used language '.$language.' is not supported in this project.');
			}

			$this -> language = $language;
		} // end setLanguage();
		
		public function setOutput($output)
		{
			$res = tfResources::get();
			if(!in_array($output, $res -> outputs))
			{
				throw new SystemException('The used output '.$output.' is not supported by TypeFriendly.');
			}
			$this -> output = $output;
		} // end setOutput();
		
		public function loadItems()
		{
			$items = $this -> fs -> listDirectory('input/'.$this->language.'/', true, true);
			
			// See, what are the documentation pages, and what - other files.
			$doc = array();
			foreach($items as $item)
			{
				if(($s = strpos($item, '.txt')) !== false)
				{
					if($s == strlen($item) - 4)
					{
						$doc[] = substr($item, 0, $s);
					}
				}
				else
				{
					$this -> media[] = $item;
				}
			}
			sort($doc);
			
			// Build the standard connections
			$list = array();
			foreach($doc as &$item)
			{
				$extract = explode('.', $item);
				array_pop($extract);
				$parentId = implode('.', $extract);
				if(!isset($list[$parentId]))
				{
					$list[$parentId] = array(0 => array('id' => $item, 'order' => 0));
				}
				else
				{
					$list[$parentId][] = array('id' => $item, 'order' => sizeof($list[$parentId]));
				}
				if(!isset($list[$item]))
				{
					$list[$item] = array();
				}
			}

			try
			{
			//	echo 'krajator';
				$this -> sortHint = $this -> fs -> readAsArray('sort_hints.txt');
			//	var_dump($this->sortHint);
			//	echo 'ddd';
				$hintedList = array();
				foreach($this -> sortHint as &$item)
				{
					$extract = explode('.', $item);
					array_pop($extract);
					$parentId = implode('.', $extract);
	
					if(!isset($hintedList[$parentId]))
					{
						$hintedList[$parentId] = array($item => array('id' => $item, 'order' => 0));
					}
					else
					{
						$hintedList[$parentId][$item] = array('id' => $item, 'order' => sizeof($hintedList[$parentId]));
					}
				}
				
				// Now connect those two lists:
				foreach($list as $id => &$item)
				{
					if(isset($hintedList[$id]))
					{
						foreach($item as &$val)
						{
							if(isset($hintedList[$id][$val['id']]))
							{
								$val['order'] = $hintedList[$id][$val['id']]['order'];
							}
							else
							{
								throw new Exception('Not all children of '.$id.' are defined in the sorting hint list: '.$val['id'].'. I don\'t know, what to do.');
							}
						}
						usort($item, array($this, 'orderSort'));
					}
				}
			}
			catch(SystemException $e)
			{
				// Nothing to worry, if the file is not accessible. At least the data won't be sorted.
				// TODO: However, if the debug is active, there must be some kind of message.
			}
			/*
			 * Part 2 - create the meta-data for each page.
			 */

			
			$this -> pages = array();
			$parser = tfParsers::get();
			foreach($list as $id => &$sublist)
			{		
				foreach($sublist as $subId => &$item)
				{
					$metaData = $parser -> tfdoc($this->fs->get('input/'.$this->language.'/'.$item['id'].'.txt'));
					// unset($metaData['Content']);
					// Validate the user-defined meta.
					if(!isset($metaData['ShortTitle']))
					{
						$metaData['ShortTitle'] = $metaData['Title'];
					}
					
					// Create the additional meta.
					$metaData['Id'] = $item['id'];
					
					// Create the navigation according to the chapter layout					
					$metaData['_Parent'] = $id;
					$metaData['_Previous'] = null;
					$metaData['_Next'] = null;
					if(isset($sublist[$subId-1]))
					{
						$metaData['_Previous'] = $sublist[$subId-1]['Id'];
					}
					if(isset($sublist[$subId+1]))
					{
						$metaData['_Next'] = $sublist[$subId+1]['id'];
					}
					
					if($this -> config['navigation'] == 'flat')
					{
						// Create a flat navigation, where "Next" can point to the first child, if accessible
						$metaData['_XNext'] = $metaData['_Next'];
						if(sizeof($list[$item['id']]) > 0)
						{
							$metaData['_Next'] = $list[$item['id']][0]['id'];
						}
						elseif(is_null($metaData['_Next']) && $id != '')
						{
							$metaData['_Next'] = $this -> pages[$id]['_XNext'];
						}

						if(!is_null($metaData['_Previous']))
						{
							$xid = $metaData['_Previous'];
							while(($size = sizeof($list[$xid])) > 0)
							{
								$xid = $list[$xid][$size-1]['id'];
							}
							$metaData['_Previous'] = $xid;
						}
						elseif(is_null($metaData['_Previous']) && $id != '')
						{
							$metaData['_Previous'] = $id;
						}
						
					}
					$item = $metaData;
					$this -> pages[$item['Id']] = &$item;
				}
			}
			$this -> tree = $list;
		} // end loadItems();

		public function copyMedia()
		{
			try
			{
				$this -> fs -> copyFromVFS($this->prog->fs, 'media/'.$this->output.'/', 'output/'.$this->output.'/');
			}
			catch(SystemException $e)
			{
				// Nothing to do - here this is not a crtitical error.
			}
		} // end copyMedia();

		public function generate()
		{
			$this -> fs -> safeMkDir('output/'.$this->output, TF_READ | TF_WRITE | TF_EXEC);
			
			$this -> outputObj = $out = $this -> prog -> fs -> loadObject('outputs/'.$this->output.'.php', $this->output);
			$out -> init($this, 'output/'.$this->output.'/');
			$parsers = tfParsers::get();
			
			foreach($this -> pages as &$page)
			{
				if(!$this -> parsed)
				{	
					$page['Content'] = $parsers -> parse($page['Content']);
				}
				$out -> generate($page);
			}
			$this -> parsed = true;
			$out -> close();
		} // end generate();

		public function versionCompare($secondLanguage)
		{
			if(!in_array($this -> config['baseLanguage'], $this -> langs) || !in_array($this -> langs))
			{
				throw new SystemException('The used language '.$this -> config['baseLanguage'].' is not supported in this project.');
			}
			if(!in_array($secondLanguage, $this -> langs))
			{
				throw new SystemException('The used language '.$secondLanguage.' is not supported in this project.');
			}
			
			$statBase = $this -> fs -> getModificationTime('input/'.$this->config['baseLanguage'].'/');
			$statSec = $this -> fs -> getModificationTime('input/'.$secondLangage.'/');
			$thirdList = array();
			
			$out = $this -> prog -> console -> stdout;
			
			$out -> writeln('Comparing "'.$secondLanguage.'" to the base language: "'.$this->config['baseLanguage'].'".');
			$out -> space();
			$out -> writeln('Files that are not up-to-date in "'.$secondLanguage.'":');
			$out -> space();
			
			foreach($statBase as $name => $time)
			{
				if(isset($statSec[$name]))
				{
					if($time > $statSec[$name])
					{
						$out -> writeln('  '.$name);
					}
					unset($statSec[$name]);
				}
				else
				{
					$thirdList[] = $name;
				}
			}
			
			$out -> space();
			$out -> writeln('Files that do not exist in "'.$secondLanguage.'":');
			$out -> space();
			
			foreach($thirdList as $name)
			{
				$out -> writeln('  '.$name);
			}

			$out -> space();
			$out -> writeln('Files that do not exist in "'.$this->config['baseLanguage'].'" (but should, if they are used in "'.$secondLanguage.'"):');
			$out -> space();
			
			foreach($statSec as $name => $time)
			{
				$out -> writeln('  '.$name);
			}
		} // end versionCompare();

		public function orderSort($a, $b)
		{
			return $a['order'] - $b['order'];
		} // end orderSort();

		public function getMetaInfo($pageId, $exception = true)
		{
			if(isset($this->pages[$pageId]))
			{
				return $this->pages[$pageId];
			}
			if($exception)
			{
				throw new SystemException('An attemt to access the meta-data of unexisting page: '.$pageId);
			}
			return NULL;
		} // end getMetaInfo();
	} // end tfProject;

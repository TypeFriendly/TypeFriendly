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
// $Id$

/**
 * The class allows to perform complex manipulations on the filesystem.
 */
class TypeFriendly_Filesystem
{
	/**
	 * Permission to read.
	 */
	const READ = 4;
	/**
	 * Permission to write.
	 */
	const WRITE = 2;
	/**
	 * Permission to execute
	 */
	const EXEC = 1;

	/**
	 * The directory we perform operations on.
	 * @var String
	 */
	private $_directory = null;

	/**
	 * Constructs a new filesystem manipulator.
	 *
	 * @param String $directory The directory we perform manipulations on.
	 */
	public function __construct($directory, $permissions = self::READ)
	{
		$this->setMasterDirectory($directory, $permissions);
	} // end __construct();

	/**
	 * Sets the directory we perform operations on. If there are not enough
	 * permissions, an exception is thrown.
	 * 
	 * @param String $directory The directory.
	 */
	public function setMasterDirectory($directory, $permissions)
	{
		if($directory[strlen($directory)-1] != '/')
		{
			$directory .= '/';
		}

		if(!$this->_checkPermissions($dir, $permissions))
		{
			throw new Typefriendly_Filesystem_Exception('Invalid permissions for: '.$directory.', '.$permissions.' expected.');
		}
		$this->_directory = $directory;
	} // end setMasterDirectory();

	/**
	 * Returns the absolute name of the specified item within the filesystem.
	 * If the item does not exist, an exception is thrown.
	 *
	 * @param String $name The path to the item within the filesystem
	 * @return String
	 */
	public function getAbsoluteName($name)
	{
		$name = str_replace('../', '', $name);
		if(!file_exists($this->_directory.$name))
		{
			throw new TypeFriendly_Filesystem_Exception('The file '.$this->_directory.$name.' is not accessible.');
		}

		return $this->_directory.$name;
	} // end getAbsoluteName();

	/**
	 * Reads the contents of the specified file. If the requested file
	 * is not accessible, an exception is thrown.
	 *
	 * @param String $name The file name.
	 * @return String
	 */
	public function readFile($name)
	{
		$name = str_replace('../', '', $name);
		if(!file_exists($this->_directory.$name))
		{
			throw new TypeFriendly_Filesystem_Exception('The file '.$this->_directory.$name.' is not accessible.');
		}

		return file_get_contents($this->_directory.$name);
	} // end readFile();

	/**
	 * Reads the contents of the specified file and returns the
	 * array of file lines. If the requested file is not accessible,
	 * an exception is thrown.
	 *
	 * @param String $name The file name.
	 * @return Array
	 */
	public function readFileAsArray($name)
	{
		$name = str_replace('../', '', $name);
		if(!file_exists($this->_directory.$name))
		{
			throw new TypeFriendly_Filesystem_Exception('The file '.$this->_directory.$name.' is not accessible.');
		}

		$data = file($this->_directory.$name);
		foreach($data as &$item)
		{
			$item = trim($item);
		}
		return $data;
	} // end readFileAsArray();

	/**
	 * Writes the content to the specified file. If the file already
	 * exists, it is overwritten. Returns the number of bytes that
	 * have been written or FALSE.
	 *
	 * @param String $name The file name.
	 * @param String $content The new file content.
	 * @param Integer|Boolean
	 */
	public function writeFile($name, $content)
	{
		return file_put_contents($this->_directory.str_replace('../', '', $name), $content);
	} // end writeFile();

	/**
	 * Checks the permissions of the specified directories. The $list
	 * is an assotiative pair of 'directory name' => 'permissions'.
	 * The method returns an array of directories that cause problems
	 * in the format: 'directory name' => 'error message'. If there
	 * are no problems, an empty array is returned.
	 *
	 * @param Array $list The requested directory settings.
	 * @return Array
	 */
	public function checkDirectories($list)
	{
		$err = false;
		$errors = array();
		foreach($list as $name => $param)
		{
		    if(!is_dir($this->_directory.$name))
		    {
				$errors[$name] = 'Not a directory.';
				$err = true;
				continue;
		    }
			if($param & self::READ)
			{
			    if(!is_readable($this->_directory.$name))
			    {
					$errors[$name] = 'Not readable';
					$err = true;
			    }
			}
			if($param & self::WRITE)
			{
			    if(!is_writeable($this->_directory.$name))
			    {
			    	$errors[$name] = 'Not writeable';
					$err = true;
			    }
		    }
			if($param & self::EXEC)
			{
			    if(!is_executable($this->_directory.$name))
			    {
					$errors[$name] = 'Not executable';
					$err = true;
			    }
			}
		}
		if($err)
		{
			return $errors;
		}
		return true;
	} // end checkDirectories();

	/**
	 * Lists the contents of the specified directory.
	 *
	 * @param String $directory The directory to list.
	 * @param Boolean $files Include files?
	 * @param Boolean $directories Include directories?
	 * @param Array
	 */
	public function listDirectory($directory, $files = true, $directories = false)
	{
		$dir = @opendir($this->_directory.$directory);
		if(!is_resource($dir))
		{
			throw new TypeFriendly_Filesystem_Exception('Cannot open directory: '.$directory);
		}
		$list = array();
		while($f = readdir($dir))
		{
			if($f != '.' && $f != '..')
			{
				if($this->ignoreHidden && $f[0] == '.')
				{
					continue;
				}
				if($files && is_file($this->_directory.$directory.$f))
				{
					$list[] = $f;
				}
				elseif($directories && is_dir($this->_directory.$directory.$f))
				{
					$list[] = $f;
				}
			}
		}
		closedir($dir);
		return $list;
	} // end listDirectory();

	/**
	 * Creates a new directory and initializes it with the requested
	 * permissions.
	 *
	 * @param String $directory The directory name
	 * @param Integer $access The requested permissions.
	 */
	public function createDirectory($directory, $access)
	{
		if(!is_dir($this->master.$directory))
		{
			mkdir($this->master.$directory);
		}
		$what = '';
		if($access & TF_READ)
		{
			if(!is_readable($this->master.$directory))
			{
				$what .= 'r';
			}
		}
		if($access & TF_WRITE)
		{
			if(!is_writeable($this->master.$directory))
			{
				$what .= 'w';
			}
		}
		if($access & TF_EXEC)
		{
			if(!is_executable($this->master.$directory))
			{
				$what .= 'x';
			}
		}
		if(php_uname('s') != 'Windows' && strlen($what) > 0)
		{
			system('chmod u+'.$what.' "'.$this->_directory.$directory.'"');
		}
	} // end createDirectory();

	/**
	 * Copies an item (file or directory) from one place to another within
	 * the filesystem. In case of problems, an exception is thrown.
	 *
	 * @param String $from Source location.
	 * @param String $to Destination location.
	 */
	public function copyItem($from, $to)
	{
		if(is_file($this->_directory.$from))
		{
			copy($this->_directory.$from, $this->_directory.$to);
		}
		elseif(is_dir($this->_directory.$from))
		{
			$this->createDirectory($to, self::WRITE);
			$this->_recursiveCopy($this->_directory.$from, $this->_directory.$to);
		}
		else
		{
			throw new TypeFriendly_Filesystem_Exception('The directory '.$this->_directory.$from.' does not exist.');
		}
	} // end copyItem();

	/**
	 * Copies an item (file or directory) between different TypeFriendly_Filesystem
	 * instances. In case of problems, an exception is thrown.
	 *
	 * @param TypeFriendly_Filesystem $sys The source filesystem.
	 * @param String $from The source directory within the external filesystem.
	 * @param String $to The destination directory in the current filesystem.
	 */
	public function copyFromFilesystem(TypeFriendly_Filesystem $sys, $from, $to)
	{
		if(is_file($sys->_directory.$from))
		{
			copy($sys->_directory.$from, $this->_directory.$to);
		}
		else
		{
			$this->createDirectory($to, self::WRITE);

			if(!is_dir($sys->_directory.$from))
			{
				throw new TypeFriendly_Filesystem_Exception('The directory '.$sys->_directory.$from.' does not exist.');
			}

			$this->_recursiveCopy($sys->_directory.$from, $this->_directory.$to);
		}
	} // end copyFromFilesystem();

	/**
	 * Returns the modification times of the files in the directory.
	 *
	 * @param String $directory The directory to be scanned.
	 * @return Array
	 */
	public function getModificationTimes($directory)
	{
		$dir = @opendir($this->_directory.$directory);
		if(!is_resource($dir))
		{
			throw new TypeFriendly_Filesystem_Exception('Cannot open directory: '.$directory);
		}

		$list = array();
		while($f = readdir($dir))
		{
			if($f != '.' && $f != '..')
			{
				if(is_file($this->_directory.$directory.$f))
				{
					$list[$f] = filemtime($this->_directory.$directory.$f);
				}
			}
		}
		closedir($dir);
		return $list;
	} // end getModificationTime();

	/**
	 * Recursively copies a directory.
	 *
	 * @internal
	 * @param String $from
	 * @param String $to
	 */
	private function _recursiveCopy($from, $to)
	{
		$dir = opendir($source);
		while($f = readdir($dir))
		{
			if($f != '.' && $f != '..')
			{
				if($this->ignoreHidden && $f[0] == '.')
				{
					continue;
				}
				if(is_file($source.$f))
				{
					copy($source.$f, $dest.$f);
				}
				else
				{

					if(!is_dir($dest.$f))
					{
						mkdir($dest.$f);
					}
					$this->recursiveCopy($source.$f.'/', $dest.$f.'/');
				}
			}
		}
		closedir($dir);
	} // end _recursiveCopy();

	/**
	 * Checks the permissions of the specified filesystem item.
	 *
	 * @internal
	 * @param String $directory Item name.
	 * @param Integer $access Requested access.
	 * @return Boolean
	 */
	private function _checkPermissions($directory, $access)
	{
		if($access & TF_READ)
		{
			if(!is_readable($directory))
			{
				return false;
			}
		}
		if($access & TF_WRITE)
		{
			if(!is_writeable($directory))
			{
				return false;
			}
		}
		if($access & TF_EXEC && USED_OS != 'Windows')
		{
			if(!is_executable($directory))
			{
				return false;
			}
		}
		return true;
	} // end _checkPermissions();
} // end TypeFriendly_Filesystem;
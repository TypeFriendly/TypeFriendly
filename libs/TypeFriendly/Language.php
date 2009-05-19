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
 * The class represents the language
 */
class TypeFriendly_Language
{
	/**
	 * The language file directory.
	 * @static
	 * @var TypeFriendly_Filesystem
	 */
	static private $_filesystem;

	/**
	 * The language identifier.
	 * @var String
	 */
	private $_id;

	/**
	 * The base language.
	 * @var TypeFriendly_Language;
	 */
	private $_base;

	/**
	 * The language messages
	 * @var Array
	 */
	private $_data;

	/**
	 * Sets the language filesystem.
	 */
	static public function setDirectory(TypeFriendly_Filesystem $fs)
	{
		$this->_filesystem = $fs;
	} // end setDirectory();

	/**
	 * Constructs a new language object.
	 */
	public function __construct($languageId)
	{
		$this->_id = $languageId;
	} // end __construct();

	/**
	 * Sets the base language. If the current language does not have
	 * a message in the database, the system will attempt to load it
	 * from the base language.
	 *
	 * @param TypeFriendly_Language|Null $base The base language.
	 */
	public function setBaseLanguage(TypeFriendly_Language $base = null)
	{
		$this->_base = $base;
	} // end setBaseLanguage();

	/**
	 * Reads the text from the language file.
	 * @param String $group The message group.
	 * @param String $id The message identifier.
	 */
	public function _($group, $id)
	{
		if(!isset($this->_data[$group]))
		{
			if(!$this->_loadGroup($group) && $this->_base !== null)
			{
				$this->_data[$group] = array();
				$this->_base->_loadGroup($group);
			}
			else
			{
				throw new TypeFriendly_Language_Exception('The group '.$group.' cannot be loaded.');
			}
		}
		if(!isset($this->_data[$group][$id]))
		{
			if($this->_base !== null)
			{
				$text = $this->_base->_($group, $id);
			}
			else
			{
				throw new TypeFriendly_Language_Exception('Message '.$group.'@'.$id.' is not defined.');
			}
		}
		else
		{
			$text = $this->_data[$group][$id];
		}
		if(func_num_args() > 2)
		{
			$args = func_get_args();
			unset($args[0]);
			unset($args[1]);
			return vsprintf($text, $args);
		}
		return $text;
	} // end _();

	/**
	 * Loads the message group. If the group file cannot be found, it
	 * throws an exception.
	 *
	 * @internal
	 * @param String $group The group name.
	 */
	private function _loadGroup($group)
	{
		try
		{
			$this->_data[$group] = parse_ini_file($this->_filesystem->readFile($this->_id.'/'.$group.'.txt'));
			return true;
		}
		catch(TypeFriendly_Filesystem_Exception $exception)
		{
			return false;
		}
	} // end _loadGroup();
} // end TypeFriendly_Language;
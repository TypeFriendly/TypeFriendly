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
 * The class represents the TypeFriendly documentation project and
 * provides the necessary information about it.
 */
class TypeFriendly_Project
{
	const MODE_TREE = 0;
	const MODE_PLAIN = 1;

	/**
	 * The project path.
	 * @var String
	 */
	private $_path;

	/**
	 * Opens an existing project from the specified path.
	 * @param String $path The path.
	 */
	public function __construct($path)
	{

	} // end __construct();

	/**
	 * Returns the specified configuration option. There
	 * are two versions that allow to read the basic
	 * configuration and an option from a particular group.
	 *
	 * @param String $group The group or the option name.
	 * @param String|Null $name The option name.
	 * @return Mixed
	 */
	public function getOption($group, $name = null)
	{

	} // end getOption();

	public function setOption($value, $group, $name = null)
	{

	} // end setOption();

	public function setLanguage($languageId)
	{

	} // end setLanguage();

	public function getLanguages()
	{

	} // end getLanguages();

	public function getPages($pageMode = TypeFriendly_Project::MODE_TREE)
	{

	} // end getPages();

	/**
	 * Returns the page with the specified identifier or
	 * NULL if the page does not exist.
	 *
	 * @param String $identifier The page identifier.
	 * @return TypeFriendly_Page|Null
	 */
	public function getPage($identifier)
	{
		return NULL;
	} // end getPage();

	public function processDocuments(Typefriendly_Options $options)
	{

	} // end processDocuments();
} // end TypeFriendly_Project;
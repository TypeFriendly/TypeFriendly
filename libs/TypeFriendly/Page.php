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
 * The class represents a single documentation page.
 */
class TypeFriendly_Page
{
	/**
	 * The project that the page belongs to.
	 * @var TypeFriendly_Project
	 */
	private $_project = null;

	/**
	 * The page identifier.
	 * @var String
	 */
	private $_identifier = null;

	/**
	 * Constructs a new page object. The constructor should be called
	 * by the project manager only.
	 *
	 * @internal
	 * @param TypeFriendly_Project $project The project manager.
	 * @param String $identifier The page identifier.
	 */
	public function __construct(TypeFriendly_Project $project, $identifier)
	{
		$this->_project = $project;
		$this->_identifier = $identifier;
	} // end __construct();

	/**
	 * Returns the page identifier.
	 * @return String
	 */
	public function getIdentifier()
	{
		return $this->_identifier;
	} // end getIdentifier();

	/**
	 * Returns the page project manager.
	 * @return TypeFriendly_Project
	 */
	public function getProject()
	{
		return $this->_project;
	} // end getProject();
} // end TypeFriendly_Page;
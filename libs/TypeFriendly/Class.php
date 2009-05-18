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
 * The main TFLib class.
 */
class TypeFriendly_Class
{
	const AUTOLOADER_STANDARD = 0;
	const AUTOLOADER_OPL = 1;

	/**
	 * The autoloading services for TypeFriendly library. Note that
	 * you do not have to use them, if you already have Open Power Libs.
	 *
	 * @param String $class The class name.
	 */
	static public function autoload($class)
	{

	} // end autoload();

	/**
	 * Registers the autoloader.
	 */
	static public function registerAutoloader($type = TypeFriendly_Class::AUTOLOADER_STANDARD)
	{

	} // end registerAutoloader();
} // end TypeFriendly_Class;
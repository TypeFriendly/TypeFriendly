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
 * The class is a tag-manager. It checks whether the page is using
 * correct tags and forces the output system to parse them.
 */
class tfTags
{
	const STRING = 0;
	const PAGE_IDENTIFIER = 1;
	const NUMBER = 2;
	const BOOLEAN = 3;
	const IDENTIFIER = 4;
	const WORD = 5;
	const ARRAY_OF = 6;

	/**
	 * The list of available tags
	 * @static
	 * @var Array
	 */
	static private $_availableTags = array(
		'General' =>	array(
			'Title' => self::STRING,
			'ShortTitle' => self::STRING,
			'Author' => self::STRING,
			'Content' => self::STRING
		),
		'Status' => array(
			'Status' => self::STRING,
			'FeatureInformation' => self::IDENTIFIER
		),
		'Programming' => array(
			'Construct' => self::STRING,
			'Type' => self::STRING,
			'Visibility' => self::STRING,
			'Extends' => self::PAGE_IDENTIFIER,
			'EExtends' => self::STRING,
			'MultiExtends' => array(self::ARRAY_OF, self::PAGE_IDENTIFIER),
			'EMultiExtends' => array(self::ARRAY_OF, self::STRING),
			'Implements' => self::PAGE_IDENTIFIER,
			'EImplements' => self::STRING,
			'ExtendedBy' => array(self::ARRAY_OF, self::PAGE_IDENTIFIER),
			'EExtendedBy' => array(self::ARRAY_OF, self::STRING),
			'Reference' => self::STRING,
			'Arguments' => array(self::ARRAY_OF, array('Name' => self::WORD, 'Type' => self::PAGE_IDENTIFIER, 'EType' => self::STRING, 'Desc' => self::STRING)),
			'Returns' => self::STRING,
			'Throws' => array(self::ARRAY_OF, self::PAGE_IDENTIFIER),
			'EThrows' => array(self::ARRAY_OF, self::STRING),
		),
		'VersionControl' => array(
			'VCSKeywords' => self::STRING,
			'VersionSince' => self::STRING,
			'VersionTo' => self::STRING
		),
		'Navigation' => array(
			'SeeAlso' => array(self::ARRAY_OF, self::PAGE_IDENTIFIER),
			'SeeAlsoExternal' => array(self::ARRAY_OF, self::STRING)
		),
		'DocumentType' => array(
			'Appendix' => self::BOOLEAN
		)
	);

	/**
	 * Some tags need to be processed together.
	 * @static
	 * @var Array
	 */
	static private $_connectedTags = array(
		'Extends' => 'EExtends',
		'Implements' => 'EImplements',
		'ExtendedBy' => 'EExtendedBy',
		'Throws' => 'EThrows',
		'MultiExtends' => 'EMultiExtends',
		'SeeAlso' => 'SeeAlsoExternal'
	);

	/**
	 * Available constructs
	 * @static
	 * @var Array
	 */
	static private $_constructs = array(
		'class', 'interface', 'abstract class', 'function', 'method', 'static method',
		'abstract method', 'variable', 'static variable', 'module', 'package',
		'constructor', 'destructor', 'magic method', 'namespace', 'accessor method',
		'datatype', 'structure', 'macro'
	);

	/**
	 * A copy of the tag list, but without the categorization. Created on-the-fly.
	 * @static
	 * @var Array
	 */
	static private $_tags = array();

	/**
	 * Do we allow to use unknown tags?
	 * @static
	 * @var Boolean
	 */
	static private $_allowUnknown = false;

	/**
	 * The configuration of the project.
	 * @static
	 * @var Array
	 */
	static private $_config = NULL;

	/**
	 * The error message buffer.
	 * @static
	 * @var String
	 */
	static private $_error = NULL;

	/**
	 * Sets the allow-unknown flag
	 * @static
	 * @param Boolean $newState The new flag value.
	 */
	static public function setAllowUnknown($newState)
	{
		self::$_allowUnknown = $newState;
	} // end setAllowUnknown();

	/**
	 * Registers the configuration.
	 * @static
	 * @param Array $config The configuration
	 */
	static public function setConfiguration(Array $configuration)
	{
		self::$_config = $configuration;
	} // end setConfiguration();

	/**
	 * Returns the last error message.
	 * @static
	 * @return String
	 */
	static public function getError()
	{
		return self::$_error;
	} // end getError();

	/**
	 * Validates the tag list and returns the result.
	 *
	 * @param Array &$tags The list of tags
	 * @return Boolean
	 */
	static public function validateTags(Array &$tags)
	{
		self::_buildTagList();

		if(!isset($tags['Title']))
		{
			self::$_error = 'The required tag "Title" is not defined.';
			return false;
		}
		if(!isset($tags['ShortTitle']))
		{
			$tags['ShortTitle'] = $tags['Title'];
		}

		// Validate the tags.
		foreach($tags as $tag => &$value)
		{
			if(!isset(self::$_tags[$tag]))
			{
				if(!self::$_allowUnknown)
				{
					self::$_error = 'The tag "'.$tag.'" cannot be recognized as a valid TypeFriendly tag.';
					return false;
				}
			}
			if(!self::_validate($value, self::$_tags[$tag]))
			{
				self::$_error = '"'.$tag.'": invalid value.';
				return false;
			}
		}
		if(isset($tags['Extends']) && isset($tags['EExtends']))
		{
			self::$_error = 'Tags "Extends" and "EExtends" cannot be used together.';
			return false;
		}
		if((isset($tags['Extends']) || isset($tags['EExtends'])) && (isset($tags['MultiExtends']) || isset($tags['EMultiExtends'])))
		{
			self::$_error = 'Tags "Extends" and "MultiExtends" cannot be used together.';
			return false;
		}
		// Process the "FeatureInformation" tag.
		if(isset($tags['FeatureInformation']))
		{
			if(!isset(self::$_config['featureInformation']) || !isset(self::$_config['featureInformation'][$tags['FeatureInformation']]))
			{
				self::$_error = 'The feature information identifier: "'.$tags['FeatureInformation'].'" is not defined in the configuration file.';
				return false;
			}
			$parser = tfParsers::get();
			$tags['FeatureInformation'] = $parser->parse(str_replace('\n', PHP_EOL, self::$_config['featureInformation'][$tags['FeatureInformation']]));
		}
		// Process the "Construct" tag
		if(isset($tags['Construct']))
		{
			$translate = tfTranslate::get();
			$construct = strtolower(trim($tags['Construct']));
			if(!in_array($construct, self::$_constructs))
			{
				$tags['ConstructType'] = 'unknown';
			}
			else
			{
				$tags['ConstructType'] = str_replace(' ', '_', $construct);
				$tags['Construct'] = $translate->_('constructs', $tags['ConstructType']);
			}
			// Using the information from "Construct", we can perform some extra checks.
			$extends = false;
			$reference = false;
			$throws = false;
			switch($tags['ConstructType'])
			{
				case 'function':
				case 'method':
				case 'static_method':
				case 'abstract_method':
				case 'accessor_method':
				case 'magic_method':
				case 'constructor':
				case 'destructor':
				case 'macro':
					$reference = true;
					$throws = true;
					break;
				case 'class':
				case 'interface':
				case 'abstract_class':
				case 'structure':
					$extends = true;
					break;
			}

			if(!$reference && isset($tags['Reference']))
			{
				throw new Exception('Tag "Reference" is not allowed with the specified construct.');
			}
			if(!$throws && (isset($tags['Throws']) || isset($tags['EThrows'])))
			{
				throw new Exception('Tags "Throws" and "EThrows" are not allowed with the specified construct.');
			}
			if(!$extends && (
				isset($tags['Extends']) || isset($tags['EExtends']) ||
				isset($tags['Implements']) || isset($tags['Implements']) ||
				isset($tags['ExtendedBy']) || isset($tags['EExtendedBy']) ||
				isset($tags['MultiExtends']) || isset($tags['EMultiExtends'])))
			{
				throw new Exception('The tags that describe the OOP inheritance are not allowed with the specified construct.');
			}
		}
		return true;
	} // end validateTags();

	/**
	 * Orders processing the tag group and returns the output
	 * code generated by the output system. For each tag, it
	 * calls the methods in convention _tagTagName() in the
	 * specified object. If the method cannot be found, it
	 * throws an exception.
	 *
	 * @param Array &$meta The page meta-data
	 * @param String $group The tag group name
	 * @param Object $object The output system object that processes the tags.
	 * @return String
	 */
	static public function orderProcessTags(&$meta, $group, $object)
	{
		$source = '';
		foreach(self::$_availableTags[$group] as $tag => $type)
		{
			if(!in_array($tag, self::$_connectedTags))
			{
				$source .= self::orderProcessTag($meta, $group, $tag, $object);
			}
		}
		return $source;
	} // end orderProcessTags();

	/**
	 * Orders processing the tag and returns the output
	 * code generated by the output system. The tag processing
	 * method is constructed in convention _tagTagName(). If
	 * such method is not found in the specified object or
	 * the processing tag is unknown, an exception is thrown.
	 *
	 * @param String $group The group name
	 * @param String $taq The tag name
	 * @param Object $object The output system object that processes the tags.
	 * @return String
	 */
	static public function orderProcessTag(&$meta, $group, $tag, $object)
	{
		if(!isset(self::$_tags[$tag]))
		{
			throw new Exception('Output system error: requesting unknown tag "'.$group.'/'.$tag.'".');
		}
		if(isset(self::$_connectedTags[$tag]))
		{
			$another = self::$_connectedTags[$tag];
		}

		$name = '_tag'.$tag;
		if(method_exists($object, $name))
		{
			// The connected version
			if(isset($another))
			{
				if(!isset($meta[$tag]) && !isset($meta[$another]))
				{
					return '';
				}
				if(!isset($meta[$tag]))
				{
					$meta[$tag] = null;
				}
				if(!isset($meta[$another]))
				{
					$meta[$another] = null;
				}
				return $object->$name($meta[$tag], $meta[$another]);
			}
			else
			{
				if(!isset($meta[$tag]))
				{
					return '';
				}
				return $object->$name($meta[$tag]);
			}
		}
		else
		{
			throw new Exception('Output system error: cannot process "'.$tag.'"!');
		}
	} // end orderProcessTag();


	/**
	 * Builds a working copy of tag list.
	 * @static
	 */
	static private function _buildTagList()
	{
		if(sizeof(self::$_tags) > 0)
		{
			return;
		}
		foreach(self::$_availableTags as &$group)
		{
			foreach($group as $item => $type)
			{
				self::$_tags[$item] = $type;
			}
		}
	} // end _buildTagList();

	/**
	 * Performs a data validation.
	 *
	 * @param Mixed &$value The validated value.
	 * @param Mixed $type The validation type.
	 */
	static private function _validate(&$value, $type)
	{
		if(is_array($type))
		{
			// Processing ARRAY_OF
			if(isset($type[0]) && $type[0] == self::ARRAY_OF)
			{
				if(!is_array($value))
				{
					return false;
				}
				foreach($value as &$item)
				{
					if(!self::_validate($item, $type[1]))
					{
						return false;
					}
				}
			}
			else
			{
				// Split the string into single words and check them one by another.
				if(!is_string($value))
				{
					return false;
				}
				$value .= '|';
				preg_match_all('#([a-zA-Z0-9\_]+)\:\s(.*?[^\\\\])\|#ms', $value, $found);
				$cnt = sizeof($found[0]);
				$value = array();
				for($i = 0; $i < $cnt; $i++)
				{
					$value[$found[1][$i]] = trim($found[2][$i]);
					if(!self::_validate($value[$found[1][$i]], $type[$found[1][$i]]))
					{
						return false;
					}
				}
			}
		}
		else
		{
			// Validate the primitive types
			switch($type)
			{
				case self::BOOLEAN:
					$v = strtolower($value);
					if($v == 'yes' || $v == 'true')
					{
						$value = true;
						return true;
					}
					elseif($v == 'no' || $v == 'false')
					{
						$value = false;
						return true;
					}
					return false;
				case self::PAGE_IDENTIFIER:
					if(preg_match('/[a-zA-Z0-9\_\-\.]*/', $value))
					{
						return true;
					}
					return false;
				case self::IDENTIFIER:
					if(preg_match('/[a-zA-Z\_][a-zA-Z0-9\_]+/', $value))
					{
						return true;
					}
					return false;
				case self::NUMBER:
					if(ctype_digit($value))
					{
						return true;
					}
					return false;
				case self::WORD:
					if(strpos($value, ' ') === false)
					{
						return true;
					}
					return false;
			}
		}
		return true;
	} // end _validate();
} // end tfTags;

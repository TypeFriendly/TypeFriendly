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

	class xhtml extends standardOutput
	{
		private $date = '';

		/**
		 * Initializes the generation, creating the index.html file with the
		 * table of contents.
		 * @param tfProject $project The project
		 * @param String $path Output path
		 */
		public function init($project, $path)
		{		
			$translate = tfTranslate::get();
			$this->date = date('d.m.Y');
			
			$this->project = $project;
			$this->path = $path;
			// Generating TOC.
			$code = $this->createHeader($translate->_('general','table_of_contents'), array());
			
			$code .= '<h1>'.$this->project->config['title'].' '.$this->project->config['version'].'</h1>';
			
			$code .= '<p><strong>Copyright &copy; '.$this->project->config['copyright'].'</strong></p>';
			
			$code .= '<p>'.$translate->_('general','doc_license').': '.$this->project->config['license'].'</p>';
			
			$code .= '<p>'.$translate->_('general','generated_in',$this->date).'</p>';
			
			$code .= $this->menuGen('', true, true);
			$code .= $this->createFooter();
			
			$this->project->fs->write($this->path.'index.html', $code);			
		} // end init();

		/**
		 * Generates a single page and saves it on the disk.
		 *
		 * @param Array $page The page meta-info.
		 */
		public function generate($page)
		{		
			$nav = array();
			
			$nav[$page['Id']] = $page['ShortTitle'];
			
			$parent = $page['_Parent']; 			
			do
			{
				$parent = $this->project->getMetaInfo($parent, false);
				if(!is_null($parent))
				{
					$nav[$parent['Id']] = $parent['ShortTitle'];
					$parent = $parent['_Parent']; 
				}
			}
			while(!is_null($parent));
			
			$nav = array_reverse($nav, true);
			
			$code = $this->createHeader($page['Title'], $nav);
			$code .= $this->createTopNavigator($page);
			if($this->project->config['showNumbers'])
			{
				$code .= '<h1>'.$page['FullNumber'].'. '.$page['Title'].'</h1>';
			}
			else
			{
				$code .= '<h1>'.$page['Title'].'</h1>';
			}
			$code .= $this->menuGen($page['Id'], false, true);
			$code .= $this->createReference($page);

			if($this->project->config['versionControlInfo'])
			{
				$code .= $this->createVersionControlInfo($page);
			}
			
			$code .= $page['Content'];
			
			if(isset($page['SeeAlso']) || isset($page['SeeAlsoExternal']))
			{
				$code .= $this->createSeeAlso($page);
			}
			
			$code .= $this->createBottomNavigator($page);
			$code .= $this->createFooter();
			$this->project->fs->write($this->path.$page['Id'].'.html', $code);
		} // end generate();

		/**
		 * Closes the parsing - unused.
		 */
		public function close()
		{
		
		} // end close();

		/**
		 * Internal method that generates a common header for all the pages
		 * and returns the source code.
		 *
		 * @param String $title The page title.
		 * @param Array $nav The navigation list.
		 * @return String
		 */
		private function createHeader($title, Array $nav)
		{
			$translate = tfTranslate::get();
			$docTitle = $this->project->config['title'];
			$docVersion = $this->project->config['version'];
			
			$textDocumentation = $translate->_('general','documentation');
$code = <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="pl">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta name="robots" content="all" />

	<title>{$title} - {$docTitle}</title>
	

	<link rel="stylesheet" type="text/css" href="design/generic.css"  />
	<!--[if lte IE 6]><link rel="stylesheet" href="design/ie.css" type="text/css" /><![endif]-->	
</head>
<body>

<div id="wrap">
	<div id="header">
		<h1>{$docTitle} {$docVersion}</h1>
		<h2>{$title}</h2>
		<p class="generated">@ {$this->date}</p>
		<p class="location"><a href="index.html"><strong>{$textDocumentation}</strong></a>
EOF;
		foreach($nav as $id => $title)
		{
			$code .= ' &raquo; <a href="'.$id.'.html">'.$title.'</a>';
		}
$code .= <<<EOF
</p>
	</div>
	
	<div id="content">
EOF;
			return $code;
		} // end createHeader();

		/**
		 * Creates a common footer for each page.
		 *
		 * @return String
		 */
		public function createFooter()
		{
			$translate = tfTranslate::get();		
			$textLicense = $translate->_('general','doc_license');

			if(strlen($this->project->config['copyrightLink']) > 0)
			{
				$copyright = '<a href="'.$this->project->config['copyrightLink'].'">'.$this->project->config['copyright'].'</a>';
			}
			else
			{
				$copyright = $this->project->config['copyright'];
			}
			if(strlen($this->project->config['licenseLink']) > 0)
			{
				$license = '<a href="'.$this->project->config['licenseLink'].'">'.$this->project->config['license'].'</a>';
			}
			else
			{
				$license = $this->project->config['license'];
			}
$code = <<<EOF
	</div>
	
	<div id="footer">
		<p>Copyright &copy; {$copyright}</p>
		<p>{$textLicense}: {$license}</p>
		<p>Generated by <strong>TypeFriendly</strong> by <a href="http://www.invenzzia.org/">Invenzzia</a></p>
	</div>
</div>

</body>
</html>
EOF;
			return $code;
		} // end createFooter();

		/**
		 * Creates the navigation above the page contents.
		 *
		 * @param Array &$page The page meta-info.
		 * @return String
		 */
		public function createTopNavigator(&$page)
		{
			$n =& $this->project->config['showNumbers'];
			
			$translate = tfTranslate::get();
			$parent = $this->project->getMetaInfo($page['_Parent'], false);
			$prev = $this->project->getMetaInfo($page['_Previous'], false);
			$next = $this->project->getMetaInfo($page['_Next'], false);
			$code = '<dl class="location">';
			if(!is_null($parent))
			{
				$code .= '<dt><a href="'.$parent['Id'].'.html">'.($n ? $parent['FullNumber'].'. ' : '').$parent['Title'].'</a><br/>'.($n ? $page['FullNumber'].'. ' : '').$page['Title'].'<hr/></dt>';
			}
			else
			{
				$code .= '<dt><a href="index.html">'.$translate->_('general','table_of_contents').'</a><br/>'.($n ? $page['FullNumber'].'. ' : '').$page['Title'].'<hr/></dt>';
			}
			if(!is_null($prev))
			{
				$code .= '<dd class="prev">'.($n ? $prev['FullNumber'].'. ' : '').$prev['Title'].'<br/><a href="'.$prev['Id'].'.html">&laquo; '.$translate->_('navigation','prev').'</a></dd>';
			}
			if(!is_null($next))
			{
				$code .= '<dd class="next">'.($n ? $next['FullNumber'].'. ' : '').$next['Title'].'<br/><a href="'.$next['Id'].'.html">'.$translate->_('navigation','next').' &raquo;</a></dd>';
			}
			$code .= '</dl>	';
			return $code;
		} // end createTopNavigator();

		/**
		 * Creates the navigation below the page contents.
		 *
		 * @param Array &$page The page meta-info.
		 * @return String
		 */
		public function createBottomNavigator(&$page)
		{
			$n =& $this->project->config['showNumbers'];
			
			$translate = tfTranslate::get();
			$parent = $this->project->getMetaInfo($page['_Parent'], false);
			$prev = $this->project->getMetaInfo($page['_Previous'], false);
			$next = $this->project->getMetaInfo($page['_Next'], false);
			$code = '<dl class="location location-bottom">';
			if(!is_null($parent))
			{
				$code .= '<dt><hr/>'.($n ? $page['FullNumber'].'. ' : '').$page['Title'].'<br/><a href="'.$parent['Id'].'.html">'.($n ? $parent['FullNumber'].'. ' : '').$parent['Title'].'</a></dt>';
			}
			else
			{
				$code .= '<dt><hr/>'.($n ? $page['FullNumber'].'. ' : '').$page['Title'].'<br/><a href="index.html">'.$translate->_('general','table_of_contents').'</a></dt>';
			}
			if(!is_null($prev))
			{
				$code .= '<dd class="prev"><a href="'.$prev['Id'].'.html">&laquo; '.$translate->_('navigation','prev').'</a><br/>'.($n ? $prev['FullNumber'].'. ' : '').$prev['Title'].'</dd>';
			}
			if(!is_null($next))
			{
				$code .= '<dd class="next"><a href="'.$next['Id'].'.html">'.$translate->_('navigation','next').' &raquo;</a><br/>'.($n ? $next['FullNumber'].'. ' : '').$next['Title'].'</dd>';
			}
			$code .= '</dl>	';
			return $code;
		} // end createBottomNavigator();

		/**
		 * Creates "See also" links below the page content.
		 *
		 * @param Array &$page The page meta-info.
		 * @return String
		 */
		public function createSeeAlso(&$page)
		{
			$n =& $this->project->config['showNumbers'];
			
			$translate = tfTranslate::get();
			$prog = tfProgram::get();
			$i = 0;
			$code = '<h4>'.$translate->_('navigation','see_also').':</h4><ul>';
			if(isset($page['SeeAlso']))
			{
				foreach($page['SeeAlso'] as $value)
				{
					$meta = $this->project->getMetaInfo($value, false);
					if(is_null($meta))
					{
						$prog->console->stderr->writeln('The page "'.$value.'" linked in See Also of "'.$page['Id'].'" does not exist.');
					}
					else
					{
						$code .= '<li><a href="'.$meta['Id'].'.html">'.($n ? $meta['FullNumber'].'. ' : '').$meta['Title'].'</a></li>';
						$i++;
					}			
				}
			}
			if(isset($page['SeeAlsoExternal']))
			{
				foreach($page['SeeAlsoExternal'] as $value)
				{
					if(($sep = strpos($value, ' ')) !== false)
					{
						$code .= '<li><a href="'.substr($value, 0, $sep).'">'.substr($value, $sep).'</a></li>';
						$i++;
					}
					else
					{
						$code .= '<li><a href="'.$value.'">'.$value.'</a></li>';
						$i++;
					}
				}
			}
			$code .= '</ul>';
			
			if($i == 0)
			{
				return '';
			}
			
			return $code;
		} // end createSeeAlso();

		/**
		 * Creates the programming references about the described structure.
		 * This includes the support for various programming-related tags
		 * in the page header.
		 *
		 * @param Array &$page The page meta-info.
		 * @return String
		 */
		public function createReference(&$page)
		{
			$translate = tfTranslate::get();
			$code = '';
			// Function reference tag
			if(isset($page['Reference']))
			{
				$code .= '<p><strong>'.$translate->_('tags','reference').': </strong><code>'.$page['Reference'].'</code></p>';
			}
			// Status tag
			if(isset($page['Status']))
			{
				$code .= '<p><strong>'.$translate->_('tags','status').': </strong>'.$page['Status'].'</p>';
			}
			// Visibility tag
			if(isset($page['Visibility']))
			{
				$code .= '<p><strong>'.$translate->_('tags','visibility').': </strong>'.$page['Visibility'].'</p>';
			}
			// What class is extended.
			if(isset($page['Extends']))
			{
				$pp = $this->project->getMetaInfo($page['Extends'], false);
				if(!is_null($pp))
				{
					$code .= '<p><strong>'.$translate->_('tags','obj_extends').': </strong><a href="'.$pp['Id'].'.html">'.$pp['ShortTitle'].'</a></p>';
				}
			}
			elseif(isset($page['EExtends']))
			{
				$code .= '<p><strong>'.$translate->_('tags','obj_extends').':</strong> <code>'.$page['EExtends'].'</code></p>';
			}
			// What interfaces are implemented
			if(isset($page['Implements']) || isset($page['EImplements']))
			{
				$code .= '<p><strong>'.$translate->_('tags','obj_implements').':</strong></p><ul>';
				if(isset($page['Implements']))
				{
					foreach($page['Implements'] as $item)
					{
						$pp = $this->project->getMetaInfo($item, false);
						if(!is_null($pp))
						{
							$code .= '<li><a href="'.$pp['Id'].'.html">'.$pp['ShortTitle'].'</a></li>';
						}
					}
				}
				if(isset($page['EImplements']))
				{
					foreach($page['EImplements'] as $item)
					{
						$code .= '<li><code>'.$item.'</code></li>';
					}
				}
				$code .= '</ul>';
			}
			// What classes extend the current class.
			if(isset($page['ExtendedBy']) || isset($page['EExtendedBy']))
			{
				$code .= '<p><strong>'.$translate->_('tags','obj_extended').':</strong></p><ul>';
				if(isset($page['ExtendedBy']))
				{
					foreach($page['ExtendedBy'] as $item)
					{
						$pp = $this->project->getMetaInfo($item, false);
						if(!is_null($pp))
						{
							$code .= '<li><a href="'.$pp['Id'].'.html">'.$pp['ShortTitle'].'</a></li>';
						}
					}
				}
				if(isset($page['EExtendedBy']))
				{
					foreach($page['EExtendedBy'] as $item)
					{
						$code .= '<li><code>'.$item.'</code></li>';
					}
				}
				$code .= '</ul>';
			}
			// What exceptions are thrown.
			if(isset($page['Throws']) || isset($page['EThrows']))
			{
				$code .= '<p><strong>'.$translate->_('tags','obj_throws').':</strong></p><ul>';
				if(isset($page['Throws']))
				{
					foreach($page['Throws'] as $item)
					{
						$pp = $this->project->getMetaInfo($item, false);
						if(!is_null($pp))
						{
							$code .= '<li><a href="'.$pp['Id'].'.html">'.$pp['ShortTitle'].'</a></li>';
						}
					}
				}
				if(isset($page['EThrows']))
				{
					foreach($page['EThrows'] as $item)
					{
						$code .= '<li><code>'.$item.'</code></li>';
					}
				}
				$code .= '</ul>';
			}
			// Version-Since tag
			if(isset($page['VersionSince']))
			{
				$code .= '<p><strong>'.$translate->_('tags','version_since').': </strong>'.$page['VersionSince'].'</p>';
			}
			// Version-To tag
			if(isset($page['VersionTo']))
			{
				$code .= '<p><strong>'.$translate->_('tags','version_to').': </strong>'.$page['VersionTo'].'</p>';
			}
			// Author tag
			if(isset($page['Author']))
			{
				$code .= '<p><strong>'.$translate->_('tags','author').': </strong>'.$page['Author'].'</p>';
			}
			if($code != '')
			{
				$code .= '<hr/>';
			}
			return $code;
		} // end createReference();

		/**
		 * Creates version control information for the page.
		 *
		 * @param Array &$page The page meta-info.
		 * @return String
		 */
		public function createVersionControlInfo($page)
		{
			$translate = tfTranslate::get();
			$code = '';
			if(isset($page['VCSKeywords']))
			{
				$code .= '<p><strong>'.$translate->_('tags','versionControlInfo').': </strong><code>'.$page['VCSKeywords'].'</code></p>';
			}

			return $code;
		} // end createVersionControlInfo();

		/**
		 * Generates a menu.
		 *
		 * @param String $what The root page.
		 * @param Boolean $recursive Do we need a recursive tree?
		 * @param Boolean $start Do we include the "Table of contents" text?
		 * @return String
		 */
		public function menuGen($what, $recursive = true, $start = false)
		{
			$n =& $this->project->config['showNumbers'];
			
			$translate = tfTranslate::get();
			$code = '';
			if($start)
			{
				$code .= '<h4>'.$translate->_('general','table_of_contents').'</h4>';
			}
			if(isset($this->project->tree[$what]) && count($this->project->tree[$what]) > 0)
			{
				$code .= '<ul class="toc">';
				foreach($this->project->tree[$what] as $item)
				{
					if($recursive)
					{
						$code .= '<li><a href="'.$item['Id'].'.html">'.($n ? $item['FullNumber'].'. ' : '').$item['Title'].'</a>'.$this->menuGen($item['Id'], true).'</li>';
					}
					else
					{
						$code .= '<li><a href="'.$item['Id'].'.html">'.($n ? $item['FullNumber'].'. ' : '').$item['Title'].'</a></li>';
					}
				}
				$code .= '</ul>';
				return $code;
			}
			return '';
		} // end menuGen();

		/**
		 * Converts the page identifier to the URL.
		 *
		 * @param String $page The page identifier.
		 * @return String
		 */
		public function toAddress($page)
		{
			return $page.'.html';
		} // end toAddress();
	} // end xhtml;

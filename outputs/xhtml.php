<?php

	class xhtml extends standardOutput
	{
		private $date = '';
		
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
			
			$code .= $page['Content'];
			
			if(isset($page['SeeAlso']) || isset($page['SeeAlsoExternal']))
			{
				$code .= $this->createSeeAlso($page);
			}
			
			$code .= $this->createBottomNavigator($page);
			$code .= $this->createFooter();
			$this->project->fs->write($this->path.$page['Id'].'.html', $code);
		} // end generate();

		public function close()
		{
		
		} // end close();
		
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
		
		public function createReference(&$page)
		{
			$translate = tfTranslate::get();
			$code = '';
			if(isset($page['Reference']))
			{
				$code .= '<p><strong>'.$translate->_('tags','reference').': </strong><code>'.$page['Reference'].'</code></p>';
			}
			if(isset($page['Status']))
			{
				$code .= '<p><strong>'.$translate->_('tags','status').': </strong>'.$page['Status'].'</p>';
			}
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
			if(isset($page['VersionSince']))
			{
				$code .= '<p><strong>'.$translate->_('tags','version_since').': </strong>'.$page['VersionSince'].'</p>';
			}
			if(isset($page['VersionTo']))
			{
				$code .= '<p><strong>'.$translate->_('tags','version_to').': </strong>'.$page['VersionTo'].'</p>';
			}
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

		public function toAddress($page)
		{
			return $page.'.html';
		} // end toAddress();
	} // end xhtml;

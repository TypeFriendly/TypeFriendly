<?php

	class xhtml_single extends standardOutput
	{
		private $code;
		private $pageOrder = array();
		private $pageContent = array();

		public function init($project, $path)
		{
			$this->date = date('d.m.Y');
			
			$this->project = $project;
			$this->path = $path;	
		} // end init();

		public function generate($page)
		{	
			$code = '';	
			
			$n =& $this->project->config['showNumbers'];
			
			$code .= $this->createTopNavigator($page);
			
			$id = str_replace('.', '_', $page['Id']);
			$code .= '<h1>'.($n ? $page['FullNumber'].'. ' : '').$page['Title'].'</h1>';
			
			$code .= $this->createReference($page);
			
			$code .= $page['Content'];
			
			if(isset($page['SeeAlso']) || isset($page['SeeAlsoExternal']))
			{
				$code .= $this->createSeeAlso($page);
			}
			
			$this->pageContent[$page['Id']] = $code;
		} // end generate();

		public function close()
		{
			$translate = tfTranslate::get();
			
			$code = $this->createHeader();
			
			$code .= '<h1>'.$this->project->config['title'].' '.$this->project->config['version'].'</h1>';
			
			$code .= '<p><strong>Copyright &copy; '.$this->project->config['copyright'].'</strong></p>';
			
			$code .= '<p>'.$translate->_('general','doc_license').': '.$this->project->config['license'].'</p>';
			
			$code .= '<p>'.$translate->_('general','generated_in',$this->date).'</p>';
			
			$code .= '<h4 id="toc">'.$translate->_('general','table_of_contents').'</h4>';
			$code .= $this->menuGen('', true);
			foreach($this->pageOrder as $id)
			{
				$code .= $this->pageContent[$id];
			}
			
			$code .= $this->createFooter();
		
			$this->project->fs->write($this->path.'index.html', $code);
		} // end close();
		
		private function createHeader()
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

	<title>{$docTitle}</title>
	

	<link rel="stylesheet" type="text/css" href="design/generic.css"  />
	<!--[if lte IE 6]><link rel="stylesheet" href="design/ie.css" type="text/css" /><![endif]-->	
</head>
<body>

<div id="wrap">
	<div id="header">
		<h1>{$docTitle} {$docVersion}</h1>
		<p class="generated">@ {$this->date}</p>
		<p class="location"><a href="index.html"><strong>{$textDocumentation}</strong></a></p>
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
            
            $id = str_replace('.', '_', $page['Id']);
			
			$translate = tfTranslate::get();
			$parent = $this->project->getMetaInfo($page['_Parent'], false);
			$prev = $this->project->getMetaInfo($page['_Previous'], false);
			$next = $this->project->getMetaInfo($page['_Next'], false);
			$code = '<dl id="'.$id.'" class="location location-middle">';
			if(!is_null($parent))
			{
				$code .= '<dt><a href="'.$this->toAddress($parent['Id']).'">'.($n ? $parent['FullNumber'].'. ' : '').$parent['Title'].'</a><br/>'.($n ? $page['FullNumber'].'. ' : '').$page['Title'].'<hr/></dt>';
			}
			else
			{
				$code .= '<dt><a href="#toc">'.$translate->_('general','table_of_contents').'</a><br/>'.($n ? $page['FullNumber'].'. ' : '').$page['Title'].'<hr/></dt>';
			}
			if(!is_null($prev))
			{
				$code .= '<dd class="prev">'.($n ? $prev['FullNumber'].'. ' : '').$prev['Title'].'<br/><a href="'.$this->toAddress($prev['Id']).'">&laquo; '.$translate->_('navigation','prev').'</a></dd>';
			}
			if(!is_null($next))
			{
				$code .= '<dd class="next">'.($n ? $next['FullNumber'].'. ' : '').$next['Title'].'<br/><a href="'.$this->toAddress($next['Id']).'">'.$translate->_('navigation','next').' &raquo;</a></dd>';
			}
			$code .= '</dl>	';
			return $code;
		} // end createTopNavigator();
		
		public function createSeeAlso(&$page)
		{
			$n =& $this->project->config['showNumbers'];
			
			$translate = tfTranslate::get();
			
			$i = 0;
			
			$prog = tfProgram::get();
			$code = '<h3>'.$translate->_('navigation','see_also').':</h3><ul>';
			if(isset($page['SeeAlso']))
			{
				foreach($page['SeeAlso'] as $value)
				{
					$meta = $this->project->getMetaInfo($value, false);
					if(is_null($meta))
					{
						$prog->console->stderr->writeln('The page "'.$value.'" linked in See Also does not exist.');
					}
					else
					{
						$code .= '<li><a href="'.$this->toAddress($meta['Id']).'">'.($n ? $meta['FullNumber'].'. ' : '').$meta['Title'].'</a></li>';
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
					$code .= '<p><strong>'.$translate->_('tags','obj_extends').': </strong><a href="'.$this->toAddress($pp['Id']).'">'.$pp['ShortTitle'].'</a></p>';
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
							$code .= '<li><a href="'.$this->toAddress($pp['Id']).'">'.$pp['ShortTitle'].'</a></li>';
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
							$code .= '<li><a href="'.$this->toAddress($pp['Id']).'">'.$pp['ShortTitle'].'</a></li>';
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
		
		public function menuGen($what, $recursive = true)
		{
			$n =& $this->project->config['showNumbers'];
			
			if(isset($this->project->tree[$what]) && count($this->project->tree[$what]) > 0)
			{			
				$code = '<ul class="toc">';
				foreach($this->project->tree[$what] as $item)
				{
					$this->pageOrder[] = $item['Id'];
					if($recursive)
					{
						$code .= '<li><a href="'.$this->toAddress($item['Id']).'">'.($n ? $item['FullNumber'].'. ' : '').$item['Title'].'</a>'.$this->menuGen($item['Id'], true).'</li>';
					}
					else
					{
						$code .= '<li><a href="'.$this->toAddress($item['Id']).'">'.($n ? $item['FullNumber'].'. ' : '').$item['Title'].'</a></li>';
					}
				}
				$code .= '</ul>';
				return $code;
			}
			return '';
		} // end menuGen();
		
		public function toAddress($page)
		{
			$page = str_replace('.', '_', $page);
			return '#'.$page;
		} // end toAddress();
	} // end xhtml_single;

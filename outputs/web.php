<?php

	class xhtml extends standardOutput
	{
		public function init($project, $path)
		{
			$this->project = $project;
			$this->path = $path;
			// Generating TOC.
			$code = $this->createHeader('Spis treści', array());
			$code .= '<h1>Spis treści</h3>';
			$code .= $this->menuGen('', true);
			$code .= $this->createFooter();
			
			$this->project->fs->write($this->path.'index.html', $code);			
		} // end init();

		public function generate($page)
		{		
			$code = $this->createHeader($page['Title'], array());
			$code .= $this->createTopNavigator($page);
			$code .= '<h1>'.$page['Title'].'</h1>';
			$code .= $this->menuGen($page['Id'], false);
			$code .= $this->createReference($page);
			
			$code .= $page['Content'];
			
			if(isset($page['SeeAlso']))
			{
				$code .= $this->createSeeAlso($page['SeeAlso']);
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
			$docTitle = $this->project->config['title'];
			$docVersion = $this->project->config['version'];
$code = <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="pl">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta name="author" content="" />
	<meta name="keywords" content="" />
	<meta name="description" content="" />
	<meta name="robots" content="all" />

	<title>{$title} - {$docTitle}</title>
	

	<link rel="stylesheet" type="text/css" href="design/generic.css"  />
	<!--[if lte IE 6]><link rel="stylesheet" href="design/ie.css" type="text/css" /><![endif]-->	
	<!--[if IE 7]><link rel="stylesheet" href="design/ie7.css" type="text/css" /><![endif]-->	
</head>
<body>

<div id="wrap">
	<div id="header">
		<h1>{$docTitle} {$docVersion}</h1>
		<h2>{$title}</h2>
		<p class="generated"></p>
		<p class="location"><strong>Dokumentacja</strong>
EOF;
		foreach($nav as $id => $title)
		{
			$code .= ' &raquo; <a href="'.$id.'">'.$title.'</a>';
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
			$copyrightLink = $this->project->config['copyrightLink'];
			$copyright = $this->project->config['copyright'];
$code = <<<EOF
	</div>
	
	<div id="footer">
		<p>Copyright &copy; <a href="{$copyrightLink}">{$copyright}</a></p>
		<p>Generated by <a href="#">TypeFriendly</a> by <a href="#">Invenzzia</a></p>
	</div>
</div>

</body>
</html>
EOF;
			return $code;
		} // end createFooter();
		
		public function createTopNavigator(&$page)
		{
			$parent = $this->project->getMetaInfo($page['_Parent'], false);
			$prev = $this->project->getMetaInfo($page['_Previous'], false);
			$next = $this->project->getMetaInfo($page['_Next'], false);
			$code = '<dl class="location">';
			if(!is_null($parent))
			{
				$code .= '<dt><a href="'.$parent['Id'].'.html">'.$parent['Title'].'</a><br/>'.$page['Title'].'<hr/></dt>';
			}
			else
			{
				$code .= '<dt><a href="index.html">Spis treści</a><br/>'.$page['Title'].'<hr/></dt>';
			}
			if(!is_null($prev))
			{
				$code .= '<dd class="prev">'.$prev['Title'].'<br/><a href="'.$prev['Id'].'.html">&laquo; Poprzedni</a></dd>';
			}
			if(!is_null($next))
			{
				$code .= '<dd class="next">'.$next['Title'].'<br/><a href="'.$next['Id'].'.html">Następny &raquo;</a></dd>';
			}
			$code .= '</dl>	';
			return $code;
		} // end createTopNavigator();

		public function createBottomNavigator(&$page)
		{
			$parent = $this->project->getMetaInfo($page['_Parent'], false);
			$prev = $this->project->getMetaInfo($page['_Previous'], false);
			$next = $this->project->getMetaInfo($page['_Next'], false);
			$code = '<dl class="location location-bottom">';
			if(!is_null($parent))
			{
				$code .= '<dt><a href="'.$parent['Id'].'.html">'.$parent['Title'].'</a><br/>'.$page['Title'].'<hr/></dt>';
			}
			else
			{
				$code .= '<dt><a href="index.html">Spis treści</a><br/>'.$page['Title'].'<hr/></dt>';
			}
			if(!is_null($prev))
			{
				$code .= '<dd class="prev">'.$prev['Title'].'<br/><a href="'.$prev['Id'].'.html">&laquo; Poprzedni</a></dd>';
			}
			if(!is_null($next))
			{
				$code .= '<dd class="next">'.$next['Title'].'<br/><a href="'.$next['Id'].'.html">Następny &raquo;</a></dd>';
			}
			$code .= '</dl>	';
			return $code;
		} // end createBottomNavigator();
		
		public function createSeeAlso($seealso)
		{
			$prog = tfProgram::get();
			$code = '<h3>Zobacz także:</h3><ul>';
			foreach($seealso as $value)
			{
				$page = $this->project->getMetaInfo($value, false);
				if(is_null($page))
				{
					$prog->console->stderr->writeln('The page "'.$value.'" linked in See Also does not exist.');
				}
				else
				{
					$code .= '<li><a href="'.$page['Id'].'.html">'.$page['Title'].'</a></li>';
				}			
			}
			$code .= '</ul>';
			return $code;
		} // end createSeeAlso();
		
		public function createReference(&$page)
		{
			$code = '';
			if(isset($page['Status']))
			{
				$code .= '<p><strong>Status: </strong>'.$page['Status'].'</p>';
			}
			if(isset($page['Extends']))
			{
				$pp = $this->project->getMetaInfo($page['Extends'], false);
				if(!is_null($pp))
				{
					$code .= '<p><strong>Klasa bazowa: </strong><a href="'.$pp['Id'].'.html">'.$pp['ShortTitle'].'</a></p>';
				}
			}
			elseif(isset($page['EExtends']))
			{
				$code .= '<p><strong>Klasa bazowa:</strong> <code>'.$page['EExtends'].'</code></p>';
			}
			if(isset($page['Implements']) || isset($page['EImplements']))
			{
				$code .= '<p><strong>Implementuje:</strong></p><ul>';
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
				$code .= '<p><strong>Klasy pochodne:</strong></p><ul>';
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
			if($code != '')
			{
				$code .= '<hr/>';
			}
			return $code;
		} // end createReference();
		
		public function menuGen($what, $recursive = true)
		{
			if(isset($this->project->tree[$what]))
			{
				$code = '<ul>';
				foreach($this->project->tree[$what] as $item)
				{
					if($recursive)
					{
						$code .= '<li><a href="'.$item['Id'].'.html">'.$item['Title'].'</a> '.$this->menuGen($item['Id'], true).'</li>';
					}
					else
					{
						$code .= '<li><a href="'.$item['Id'].'.html">'.$item['Title'].'</a></li>';
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

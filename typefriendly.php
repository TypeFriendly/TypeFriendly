#!/usr/bin/php -q
<?php
	define('TF_DIR', './');
	define('TF_INCLUDES', TF_DIR.'includes/');
	define('TF_MARKDOWN', TF_DIR.'markdown/');
	define('TF_GESHI', TF_DIR.'geshi/');
	define('TF_OUTPUTS', TF_DIR.'outputs/');
	define('TF_TPL', TF_DIR.'templates/');
	require_once(TF_INCLUDES.'console.php');
	require_once(TF_INCLUDES.'resources.php');
	require_once(TF_INCLUDES.'filesystem.php');
	require_once(TF_MARKDOWN.'markdown.php');

	$app = tfProgram::get();
	$app -> loadModule('main');
	$app -> run();

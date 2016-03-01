<?php
if(! defined('page_include_allowed'))
{
	//Force 404 error by seeking a non-existant dummy page.
	$host = $_SERVER['HTTP_HOST'];
	$uri = rtrim(dirname($_SERVER['PHP_SELF']));
	$dummypage = 'somepage.html';
	header("Location: http://$host$uri/$dummypage");
	exit();
	
}

function MattaInitialise() {
	$GLOBALS['sDataDir'] = joinPaths($GLOBALS['sTOPDIR'],'..', 'generic', 'data');
	$GLOBALS['sImagesDir'] = joinPaths($GLOBALS['sTOPDIR'],'..', 'generic', 'images');
	$GLOBALS['sOutputDir'] = joinPaths($GLOBALS['sTOPDIR'],'outbound');
	define('sENCODING', 'utf-8'); //Constant with default encoding
	//Find default Locale and set it to US, if none found.
	setlocale(LC_ALL,''); //NULL or '' set it from computer defaults. A 0 returns all values.
	$sTemp = setlocale(LC_TIME,0);
	if (($sTemp == 'C') || ($sTemp == ''))
	{
		setlocale(LC_ALL, "en_US.UTF-8");
		$sTemp = setlocale(LC_ALL,0);
	}
	$GLOBALS['sLC_TIME'] = $sTemp;
	$sTemp = preg_split('/\./',$sTemp);
	$GLOBALS['sDefaultLocale'] = $sTemp[0];
	$GLOBALS['sDefaultEncoding'] = $sTemp[1];
	
	//Create the HTML locales for later use
	switch($GLOBALS['sLang']) {
		case 'ar':
			$GLOBALS['sHTMLlocale'] = 'ar_LB';
			break;
		case 'en':
			$GLOBALS['sHTMLlocale'] = 'en_GB';
			break;
		case 'fr':
			$GLOBALS['sHTMLlocale'] = 'fr_FR';
			break;
		case 'id':
			$GLOBALS['sHTMLlocale'] = 'id_ID';
			break;
		case 'pt':
			$GLOBALS['sHTMLlocale'] = 'pt_BR';
			break;
		case 'es':
			$GLOBALS['sHTMLlocale'] = 'es_MX';
			break;
		default:
			$GLOBALS['sHTMLlocale'] = 'en_GB';
	}
	
	$GLOBALS['arrConvMap'] = array(0x80, 0x10ffff, 0, 0xffffff);
	mb_internal_encoding('UTF-8');

}

function joinPaths() {
	$args = func_get_args();
	$paths = array();
	foreach ($args as $arg)
		$paths = array_merge($paths, (array)$arg);

	$paths2 = array();
	foreach ($paths as $i=>$path)
	{   $path = trim($path, DIRECTORY_SEPARATOR);
	if (strlen($path))
		$paths2[]= $path;
	}
	$result = join(DIRECTORY_SEPARATOR, $paths2); // If first element of old path was absolute, make this one absolute also
	if (strlen($paths[0]) && substr($paths[0], 0, 1) == DIRECTORY_SEPARATOR)
		return '/'.$result;
	return $result;
}

?>
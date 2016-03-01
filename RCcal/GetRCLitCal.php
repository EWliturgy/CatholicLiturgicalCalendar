<?php

//Security check. Check that we have been called by the correct HTML page.
$sTempCallerPath = $_SERVER['HTTP_REFERER'];
$sTempCallerPage = basename($sTempCallerPath);
if (($sTempCallerPage != 'RCLitCal.html') && ($sTempCallerPage != 'GetRCLitCal.php'))
{
	//Force 404 error by seeking a non-existant dummy page.
	$host = $_SERVER['HTTP_HOST'];
	$uri = rtrim(dirname($_SERVER['PHP_SELF']));
	$dummypage = 'somepage.html';
	header("Location: http://$host$uri/$dummypage");
	exit();
}
/*
if (($sTempCallerPath != 'http://localhost/RCcal/RCLitCal.html') &&
        ($sTempCallerPath != 'http://www.liturgy.guide/RCcal/RCLitCal.html') &&
		($sTempCallerPath != 'http://liturgy.guide/RCcal/RCLitCal.html') &&
    ($sTempCallerPath != 'http://localhost/RCcal/GetRCLitCal.php') &&
        ($sTempCallerPath != 'http://www.liturgy.guide/RCcal/GetRCLitCal.php') &&
		($sTempCallerPath != 'http://liturgy.guide/RCcal/GetRCLitCal.php'))
{
	//Force 404 error by seeking a non-existant dummy page.
	$host = $_SERVER['HTTP_HOST'];
	$uri = rtrim(dirname($_SERVER['PHP_SELF']));
	$dummypage = 'somepage.html';
	header("Location: http://$host$uri/$dummypage");
	exit();
}
 */
define('page_include_allowed', TRUE);

//set the default timezone
date_default_timezone_set('UTC');

//SQLite query for years available in the database
//select distinct substr(Date_this_year, 1, 4) from Cal;

include '../generic/MattaUtils/MattaGlobals.php';
include '../generic/MattaUtils/MattaSQLite.php';
include '../generic/MattaUtils/MattaTranslationFuncs.php';
include '../generic/MattaUtils/MattaPrintToHTML.php';
include '../generic/MattaUtils/MattaCreateICAL.php';
include './PrintRCCal.php';

//Set up some basic global values
$GLOBALS['iYearSought'] = $_POST[ "dateMenu" ];
$GLOBALS['sLang'] = $_POST[ "langMenu" ];
$GLOBALS['iEDM'] = $_POST[ "edmMenu" ];
$GLOBALS['sCalendarChosen'] = $_POST[ "calMenu"];
$GLOBALS['sTOPDIR'] = dirname(__FILE__);
$GLOBALS['iMonth'] = $_POST[ "monthMenu" ];
$GLOBALS['bDoICAL'] = $_POST[ "iCalMenu" ];

//Initialise all that is needed.
//   Should initialise ONLY after base data in $GLOBALS .
MattaInitialise();

//Open up the databases required.
$GLOBALS['dbRomanCal'] = MattaOpenSQLiteDB($GLOBALS['sDataDir'], 'RomanKeys.db3');
$GLOBALS['dbMelkTexts'] = MattaOpenSQLiteDB($GLOBALS['sDataDir'], 'MelkiteTexts.db3');

//Some checking and set-up functions only required for the first month of the year
if($GLOBALS['iMonth'] == 1) {

	//   set up Book names for translation
	if($GLOBALS['sLang'] != 'en')
		pSetUpBibleBooks();
	
	//	Check that the year is valid
	$bYearError = TRUE;
	if (is_numeric($GLOBALS['iYearSought']))
	{
		#Check that the year is a valid one for the Gregorian Calendar
		$GLOBALS['iYearSought'] = intval($GLOBALS['iYearSought']);
		if (($GLOBALS['iYearSought'] >= 1583) && ($GLOBALS['iYearSought'] <= 4099))
			$bYearError = FALSE;
	}
	if ($bYearError)
	{
		$sError = "<h1>Error!</h1>\n<p>&#xA0;</p>\n";
		if ($bYearError)
		{
			$sError = $sError . "<p> Incorrect Year (" . $sTempYear . ") supplied. <br>";
			$sError = $sError . "The Year should be between 1583 and 4099.</p>\n";
		}
		echo($sError);
		exit;
	}
	
	//Check to see whether the Year and Local Calendar combination already exists
	$bGenerateCalData = False;
	$sMonthDate = $GLOBALS['iYearSought'] . '-' . sprintf('%02d', $GLOBALS['iMonth']);
	$sTempSQL = "select * from RCcalThisYear where (Date_this_year like '" . $sMonthDate . "%') and (ForWhichCal = '" . $GLOBALS['sCalendarChosen'] . "') order by Date_this_year asc";
	$stmt = $GLOBALS['dbRomanCal']->prepare($sTempSQL);
	$stmt->execute();
	$result = $stmt->fetch();
	if($result) {
		$bGenerateCalData = False;
	} else {
		#fetch() returns False, if there is no data found.
		$bGenerateCalData = True;
	}

	if ($bGenerateCalData) {
		//Initialise all that is needed.
		ini_set('max_execution_time', 0);
		$ctx = array(); // This is the context of the clips
		clips_init($ctx);

		ob_start(); #Turn on output buffering to capture CLIPS command outputs.
		//Initial data required.
		clips_exec('(clear)', false);
		clips_exec('(reset)', false);
		clips_exec('(defglobal ?*EDM* = ' . $GLOBALS['iEDM'] . ')', false);
		clips_exec('(defglobal ?*yearSought* = ' . $GLOBALS['iYearSought'] . ')', false);
		clips_exec('(defglobal ?*calendarInUse* = "' . $GLOBALS['sCalendarChosen'] . '")', false);

		chdir('./src');

		clips_exec('(eval "(batch* \"' . getcwd() . '/RomanCal00.clp\")")', false);
		ob_end_clean(); #Clear output buffer and cease buffering.
		#chdir('..');
		$arrFacts = array();
		clips_query_facts($arrFacts, 'RCcalThisYear');
		
		//wrap all inserts into a single transaction
		$sTempSQL = "begin transaction";
		$stmt = $GLOBALS ['dbRomanCal']->prepare ( $sTempSQL );
		$stmt->execute ();
		//insert the values into the table
		foreach ($arrFacts as $arrDayFacts) {
			//for each day of the year in summaryDayFact
			$sCols = '';
			$sValues = '';
				
			foreach ($arrDayFacts as $sKey => $sValue) {
				if (strlen($sValue) > 0) {
					if (($sKey != '__template__') && ($sKey != 'Date_this_year')) {
						if ($sKey == 'Date_ISO8601') {
							$sCols .= 'Date_this_year, ';
							$sValues .= "'$sValue', ";
						} else {
							$sCols .= "'$sKey', ";
							$sValues .= "'$sValue', ";
						}
					}
				}
			}
				
			$sNCols = mb_substr($sCols,0,-2,'UTF-8'); // get rid of the last comma and whitespace
			$sNValues = mb_substr($sValues,0,-2,'UTF-8'); // get rid of the last comma and whitespace
			$sTempSQL = "insert into RCcalThisYear (" . $sNCols . ") values (" . $sNValues . ");";
			//echo($sTempSQL); #For debug
			$stmt = $GLOBALS ['dbRomanCal']->prepare ( "$sTempSQL" );
			$stmt->execute ();
		}
		//finalise the transaction
		$sTempSQL = "commit transaction";
		$stmt = $GLOBALS ['dbRomanCal']->prepare ( $sTempSQL );
		$stmt->execute ();
		
	}
}

//Find details and print
if($GLOBALS['bDoICAL']) {
	pCreateICAL($GLOBALS['iYearSought'], $GLOBALS['sLang'], $GLOBALS['iEDM'], $GLOBALS['sCalendarChosen'], 'RC');
} else {
	pPrintRCCalendar($GLOBALS['iYearSought'], $GLOBALS['iMonth'], $GLOBALS['sLang'], $GLOBALS['iEDM'], $GLOBALS['sCalendarChosen']);
}

//Early clean-up
$GLOBALS['dbRomanCal'] = NULL;
$GLOBALS['dbMelkTexts'] = NULL;

?>
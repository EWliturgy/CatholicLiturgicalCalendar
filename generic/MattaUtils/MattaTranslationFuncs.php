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

define('sSEMICOLON', ';');
define('sFULLSTOP', '.');
define('sCOMMA', ',');
define('sCOLON', ':');
define('sHYPHEN', '-');
define('sSPACE', ' ');


function pFormatDate($dDate, $sLang)
{
	//Change locale to get translated dates
	//The following two PHP commands should work, but locales cannot
	//   be guaranteed to be present. Hence we do this ourselves.
	//   setlocale(LC_TIME, $GLOBALS['sHTMLlocale'] . '.utf8');
	//   $sTemp = strftime("%A, %d %B %Y");

	$stmtD = $GLOBALS['dbMelkTexts']->prepare("select " . $sLang . " from TransCalWeekdays where DayNum = ?");
	$stmtD->execute(array(date('N', $dDate)));
	$resultD = $stmtD->fetchAll();
	$sTempDate = $resultD[0][$sLang];
	if($sLang != 'ar')
		$sTempDate = $sTempDate . ',';
	if($sLang == 'ar')
	{
		$sTempDate = $sTempDate . ' ' . pX00_ConvertRefToRTL(date('d', $dDate));
	} else {
		$sTempDate = $sTempDate . ' ' . date('d', $dDate);
	}

	$stmtD = $GLOBALS['dbMelkTexts']->prepare("select " . $sLang . " from TransCalMonths where MonthNum = ?");
	$stmtD->execute(array(date('n', $dDate)));
	$resultD = $stmtD->fetchAll();
	$sTempDate = $sTempDate . ' ' . $resultD[0][$sLang];
	if($sLang == 'ar')
	{
		$sTempDate = $sTempDate . ' ' . pX00_ConvertRefToRTL(date('Y', $dDate));
	} else {
		$sTempDate = $sTempDate . ' ' . date('Y', $dDate);
	}

	return $sTempDate;
}

function pFormatMonthYearDate($dDate, $sLang)
{
	//Change locale to get translated dates
	//The following two PHP commands should work, but locales cannot
	//   be guaranteed to be present. Hence we do this ourselves.
	//   setlocale(LC_TIME, $GLOBALS['sHTMLlocale'] . '.utf8');
	//   $sTemp = strftime("%B %Y");

	$stmtD = $GLOBALS['dbMelkTexts']->prepare("select " . $sLang . " from TransCalMonths where MonthNum = ?");
	$stmtD->execute(array(date('n', $dDate)));
	$resultD = $stmtD->fetchAll();
	$sTempDate = $resultD[0][$sLang];
	if($sLang == 'ar')
	{
		//Add the modern Arabic month name as well, to avoid confusion.
		$stmtD2 = $GLOBALS['dbMelkTexts']->prepare("select eg from TransCalMonths where MonthNum = ?");
		$stmtD2->execute(array(date('n', $dDate)));
		$resultD2 = $stmtD2->fetchAll();
		$sTempDate2 = $resultD2[0]['eg'];
		$sTempDate = $sTempDate . " (" . $sTempDate2 . ") " . ' ' . pX00_ConvertRefToRTL(date('Y', $dDate));
	} else {
		$sTempDate = $sTempDate . ' ' . date('Y', $dDate);
	}

	return $sTempDate;
}

function pFormatMonthFromNum($iMonth, $sLang)
{
	//Change locale to get translated dates
	//The following two PHP commands should work, but locales cannot
	//   be guaranteed to be present. Hence we do this ourselves.
	//   setlocale(LC_TIME, $GLOBALS['sHTMLlocale'] . '.utf8');
	//   $sTemp = strftime("%B %Y");

	$stmtD = $GLOBALS['dbMelkTexts']->prepare("select " . $sLang . " from TransCalMonths where MonthNum = ?");
	$stmtD->execute(array($iMonth));
	$resultD = $stmtD->fetchAll();
	$sTempDate = $resultD[0][$sLang];
	if($sLang == 'ar')
	{
		//Add the modern Arabic month name as well, to avoid confusion.
		$stmtD2 = $GLOBALS['dbMelkTexts']->prepare("select eg from TransCalMonths where MonthNum = ?");
		$stmtD2->execute(array($iMonth));
		$resultD2 = $stmtD2->fetchAll();
		$sTempDate2 = $resultD2[0]['eg'];
		$sTempDate = $sTempDate . " (" . $sTempDate2 . ") ";
	} else {
		$sTempDate = $sTempDate;
	}

	return $sTempDate;
}

function pFormatWeekdayDate($dDate, $sLang)
{
	//Change locale to get translated dates
	//The following two PHP commands should work, but locales cannot
	//   be guaranteed to be present. Hence we do this ourselves.
	//   setlocale(LC_TIME, $GLOBALS['sHTMLlocale'] . '.utf8');
	//   $sTemp = strftime("%A");

	$stmtD = $GLOBALS['dbMelkTexts']->prepare("select " . $sLang . " from TransCalWeekdays where DayNum = ?");
	$stmtD->execute(array(date('N', $dDate)));
	$resultD = $stmtD->fetchAll();
	$sTempDate = $resultD[0][$sLang];

	return $sTempDate;
}

function pConvDigits($sText, $sLang)
{
	if($sLang != 'ar')
	{
		return $sText;
	} else {
		$sTempText = pX00_ConvertRefToRTL($sText);
		return $sTempText;
	}
}

function pConvBibleRefs($sText, $sLang)
{
	$sTempText = $sText;
	if($sLang != 'en')
		$sTempText = pX05_ConvertBookName($sTempText);
	
	if($sLang == 'ar')
		$sTempText = pTranslateRef($sTempText, $sLang);
	
	return $sTempText;
}

function pTranslateRef($sText, $sLang)
{
	$sTempText = $sText;
	if($sLang != 'en')
		$sTempText = pX05_ConvertBookName($sTempText);

	if($sLang == 'ar')
		$sTempText = pX00_ConvertRefToRTL($sTempText);

	return $sTempText;
}

function pX00_ConvertRefToRTL($sText)
{
	
	///Converts Biblical references from Western to Arabic style.
	$iCharCounter = 0;
	$sTempRef = '';
	$sPreviousPiece = '';
	while ($iCharCounter < mb_strlen($sText))
	{
		$sChar = '';
		$sChar = pX20_FindNextCharGroup($sText, $iCharCounter);
		if(mb_strlen($sChar) == 0)
			return '';
		$iCharCounter = $iCharCounter + mb_strlen($sChar);
		$sPreviousChars = '';
		$sCurrentChars = '';
		for ($iCount = 0; $iCount < mb_strlen($sChar); $iCount++)
		{
			$sGotIt = mb_substr($sChar, $iCount, 1);
			switch ($sGotIt) {
				case sCOLON:
					$sCurrentChars = ' ' . sCOLON . ' ';
					break;
				case sCOMMA:
					$sCurrentChars = ' ' . json_decode('"'.'\u0648'.'"');
					break;
				case sSPACE:
					$sCurrentChars = sSPACE;
					break;
				case sHYPHEN:
					$sCurrentChars = ' - ';
					break;
				case sSEMICOLON:
					$sCurrentChars = ' . ';
					break;
				case '0':
					$sCurrentChars = json_decode('"'.'\u0660'.'"');
					break;
				case '1':
					$sCurrentChars = json_decode('"'.'\u0661'.'"');
					break;
				case '2':
					$sCurrentChars = json_decode('"'.'\u0662'.'"');
					break;
				case '3':
					$sCurrentChars = json_decode('"'.'\u0663'.'"');
					break;
				case '4':
					$sCurrentChars = json_decode('"'.'\u0664'.'"');
					break;
				case '5':
					$sCurrentChars = json_decode('"'.'\u0665'.'"');
					break;
				case '6':
					$sCurrentChars = json_decode('"'.'\u0666'.'"');
					break;
				case '7':
					$sCurrentChars = json_decode('"'.'\u0667'.'"');
					break;
				case '8':
					$sCurrentChars = json_decode('"'.'\u0668'.'"');
					break;
				case '9':
					$sCurrentChars = json_decode('"'.'\u0669'.'"');
					break;
				default:
					$sCurrentChars = $sGotIt;
			}
			$sTempRef = $sTempRef . $sCurrentChars;
			$sPreviousChars = $sGotIt;
		}
		$sPreviousPiece = $sChar;
	}
	
	return $sTempRef;	
}

function pX05_ConvertBookName($sText)
{
	$sTempText = $sText;
	foreach($GLOBALS['lTransBooks'] as $BookEn => $BookForeign)
	{
		$sTempText = mb_ereg_replace($BookEn, $BookForeign, $sTempText);
	}
	return $sTempText;
}

function pX20_FindNextCharGroup($sText, $iCharCounter)
{
	// mb_ereg_match('\d', mb_substr($harry,2,1));
	//The function finds the next group of up to three characters 
	//   forming a number. (A verse my have no more than three digits.
	if(! mb_ereg_match('\d', mb_substr($sText,$iCharCounter,1)))
		return mb_substr($sText,$iCharCounter,1);
	
	//Now check to see whether there is more than one digit, and up to three.
	$sTempCharGroupFound = mb_substr($sText,$iCharCounter,1); #Found one digit
	if(mb_strlen($sText) > ($iCharCounter + 1))
	{
		if(mb_ereg_match('\d', mb_substr($sText,($iCharCounter + 1),1)))
		{
			//found a second digit
			$sTempCharGroupFound = $sTempCharGroupFound . mb_substr($sText,($iCharCounter + 1),1);
			if(mb_strlen($sText) > ($iCharCounter + 2))
			{
				if(mb_ereg_match('\d', mb_substr($sText,($iCharCounter + 2),1)))
				{
					//found a third digit
					$sTempCharGroupFound = $sTempCharGroupFound . mb_substr($sText,($iCharCounter + 2),1);
					return $sTempCharGroupFound;
				} else {
					//found two digits
					return $sTempCharGroupFound;
				}
			} else {
				//found two digits
				return $sTempCharGroupFound;
			}
		} else {
			//found one digit
			return $sTempCharGroupFound;
		}
	} else {
		//found one digit
		return $sTempCharGroupFound;
	}
}

function pSetUpBibleBooks()
{
	unset($lTransBooks);
	$sTemp = 'Book_' . $GLOBALS['sLang'];
	$stmtT = $GLOBALS['dbMelkTexts']->prepare("select Book_en, $sTemp from Bible_abbrev order by Book_en asc");
	$stmtT->execute();
	$resultT = $stmtT->fetchAll();
	if ($resultT == '' || $resultT == NULL)
		return;

	foreach($resultT as $row)
		$lTransBooks[$row['Book_en']] = $row[$sTemp];
	
	$GLOBALS['lTransBooks'] = $lTransBooks;	
}

function pGetKnownTranslation($sText)
{
	//Gets translations of prayers and key terms from database.
	// Returns English, if nothing found.
	if($GLOBALS['sLang'] == 'en')
		return $sText;
	
	$stmt = $GLOBALS['dbMelkTexts']->prepare("select " . $GLOBALS['sLang'] . " from ConvertToSlashU where en = ? ");
	$stmt->execute(array($sText));
	$result = $stmt->fetchAll();
	$sRowData = $result[0];
	
	$sTempText = $sText; //default answer is English
	if(($sRowData[$GLOBALS['sLang']] != NULL) && (mb_strlen($sRowData[$GLOBALS['sLang']]) > 0))
		$sTempText = $sRowData[$GLOBALS['sLang']];
	return $sTempText;
	
}

function pNormaliseUnicode($sText)
{
	//Normalizer may not be installed.
	//   However, the W3C HTML5 Validator requests normalized text.
	$sTempText = $sText;
	if (extension_loaded('intl')) {
		if (! normalizer_is_normalized($sTempText, Normalizer::FORM_C))
		{
			$sTempText = normalizer_normalize($sTempText, Normalizer::FORM_C);
		}
	} else {
		//If Normalizer not installed, merely return text.
		return $sText;
	}
	
	return $sTempText;
}

function pCleanUpHTML($sText)
{
	$sTempText = $sText;
	//Perhaps basic conversion of characters special to HTML.
	$sTempText = htmlspecialchars($sTempText, ENT_QUOTES | ENT_HTML5, 'UTF-8');
	
	//Catch those other characters that don't always print well.
	//  Has the side-effect of encoding all the Arabic.
	#$sTempText = mb_encode_numericentity($sTempText, $GLOBALS['arrConvMap'], 'UTF-8');
	
	//Now convert some difficult to print characters to something simpler.
	$sTempText = mb_ereg_replace(json_decode('"'.'\u00B7'.'"'),'&#32;', $sTempText); #middle-dot replaced with space
	$sTempText = mb_ereg_replace(json_decode('"'.'\u00A0'.'"'),'&#32;', $sTempText); #nbsp replaced with space
	$sTempText = mb_ereg_replace(json_decode('"'.'\u0671'.'"'),'&#1575;', $sTempText); #alif be hamzatu-l-waSl
	$sTempText = mb_ereg_replace(json_decode('"'.'\uFB50'.'"'),'&#1575;', $sTempText);
	$sTempText = mb_ereg_replace(json_decode('"'.'\uFB51'.'"'),'&#1575;', $sTempText);
	$sTempText = mb_ereg_replace(json_decode('"'.'\uFE91'.'"'),'&#1575;', $sTempText); # part beh
	
	//Finally place <br> ahead of newlines.
	if (PHP_VERSION_ID >= 50300) {
		//Second parameter only available from 5.3.0.
		$sTempText = nl2br($sTempText, FALSE);
	} else {
		//Former output was HTML format as desired.
		$sTempText = nl2br($sTempText);
	}
	
	return $sTempText;
}
?>
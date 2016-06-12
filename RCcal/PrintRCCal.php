<?php
if (! defined ( 'page_include_allowed' )) {
	// Force 404 error by seeking a non-existant dummy page.
	$host = $_SERVER ['HTTP_HOST'];
	$uri = rtrim ( dirname ( $_SERVER ['PHP_SELF'] ) );
	$dummypage = 'somepage.html';
	header ( "Location: http://$host$uri/$dummypage" );
	exit ();
}
function pPrintRCCalendar($iYear, $iMonth, $sLang, $iEDM, $sCalendarChosen) {
	// We already have the starting date
	$sMonthDate = $iYear . '-' . sprintf ( '%02d', $iMonth );
	$sMonthStart = $sMonthDate . '-01';
	$dMonthStart = strtotime ( $sMonthStart );
	// $sEndDate = $iTempYear . '-' . sprintf('%02d', $iTempMonth) . '-01';

	// The table to use for source data varies according to the Easter Dating Method
	if ($iEDM == 2) {
		$sCalType = "Orthodox calendar";
	} elseif ($iEDM == 1) {
		$sCalType = "Julian calendar";
	} else {
		// default value is Western Easter
		$sCalType = "Gregorian calendar";
	}

	// Write out the page header
	echo ('<!DOCTYPE html>' . "\n");
	echo ('<html lang="' . $sLang . '">' . "\n");
	echo ('<head>' . "\n");
	echo ('<meta charset="utf-8">' . "\n");
	echo ('<meta http-equiv="X-UA-Compatible" content="IE=edge">' . "\n");
	echo ('<meta name="viewport" content="width=device-width, initial-scale=1">' . "\n");
	echo ('<title>Roman Catholic Liturgical Calendar</title>' . "\n");
	echo ('<link rel="stylesheet" href="../generic/css/monthcal.css">' . "\n");

	echo ('<!-- Basic Twitter Bootstrap stylesheet -->' . "\n");
	echo ('<link rel="stylesheet" href="../generic/css/bootstrap.min.css">' . "\n\n");
	echo ('<!-- Theme stylesheet -->' . "\n");
	echo ('<link rel="stylesheet" href="../generic/css/bootstrap-theme.min.css">' . "\n\n");

	echo ('<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->' . "\n");
	echo ('<!--[if lt IE 9]>' . "\n");
	echo ('<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>' . "\n");
	echo ('<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>' . "\n");
    echo ('<![endif]-->' . "\n");

	echo ("<script>\n");
	echo ('function submitSpecificMonth(iMonthWanted) {' . "\n");
	echo (' document.getElementsByName("monthMenu")[0].value = iMonthWanted;' . "\n");
	echo (' document.forms["formSpecificMonth"].submit();' . "\n");
	echo ('}' . "\n");
	echo ("</script>\n");
	echo ("</head>\n");
	echo ('<body class="calendar-page">' . "\n");
	echo ('<div class="container"><div id="page">' . "\n");
	echo ('<div class="box">' . "\n");
	echo ('<div class="body">' . "\n");
	echo ('<div class="calendar current-month myDivToPrint">' . "\n");
	echo ('<div class="calendar-head">' . "\n");
	echo ('<ul class="calendar-menu">' . "\n");
	echo ('<li class="current"><a href="RCLitCal.html">' . pGetKnownTranslation ( "Menu" ) . "</a></li>\n");
	if ($iMonth != 1) {
		echo ('<li class="current">' . "\n");
		echo ('<a href="javascript: submitSpecificMonth(' . ($iMonth - 1) . ')">' . pGetKnownTranslation ( "Previous Month" ) . '</a>' . "\n");
	} else {
		echo ('<li>' . "\n");
		echo ('<a id="noPrev">' . pGetKnownTranslation ( "Previous Month" ) . '</a>' . "\n");
	}

	echo ('</li>' . "\n");
	if ($iMonth != 12) {
		echo ('<li class="current">' . "\n");
		echo ('<a href="javascript: submitSpecificMonth(' . ($iMonth + 1) . ')">' . pGetKnownTranslation ( "Next Month" ) . '</a>' . "\n");
	} else {
		echo ('<li>' . "\n");
		echo ('<a id="NoNext">' . pGetKnownTranslation ( "Next Month" ) . '</a>' . "\n");
	}

	echo ('</li>' . "\n");
	echo ('</ul>' . "\n");

	// Month header
	$sTemp = pFormatMonthYearDate ( $dMonthStart, $sLang );
	echo ('<h2>' . $sTemp . '&#xA0;&#xA0;&#xA0;' . $sCalendarChosen . ',&#xA0;&#xA0;&#xA0; Easter of the ' . $sCalType . '</h2>');
	echo ('<ul  class="calendar-menu">');
	echo ('<li><a href="javascript: submitSpecificMonth(\'1\')">' . pFormatMonthFromNum(1, $sLang) . '</a></li>');
	echo ('<li><a href="javascript: submitSpecificMonth(\'2\')">' . pFormatMonthFromNum(2, $sLang) . '</a></li>');
	echo ('<li><a href="javascript: submitSpecificMonth(\'3\')">' . pFormatMonthFromNum(3, $sLang) . '</a></li>');
	echo ('<li><a href="javascript: submitSpecificMonth(\'4\')">' . pFormatMonthFromNum(4, $sLang) . '</a></li>');
	echo ('<li><a href="javascript: submitSpecificMonth(\'5\')">' . pFormatMonthFromNum(5, $sLang) . '</a></li>');
	echo ('<li><a href="javascript: submitSpecificMonth(\'6\')">' . pFormatMonthFromNum(6, $sLang) . '</a></li>');
	echo ('<li><a href="javascript: submitSpecificMonth(\'7\')">' . pFormatMonthFromNum(7, $sLang) . '</a></li>');
	echo ('<li><a href="javascript: submitSpecificMonth(\'8\')">' . pFormatMonthFromNum(8, $sLang) . '</a></li>');
	echo ('<li><a href="javascript: submitSpecificMonth(\'9\')">' . pFormatMonthFromNum(9, $sLang) . '</a></li>');
	echo ('<li><a href="javascript: submitSpecificMonth(\'10\')">' . pFormatMonthFromNum(10, $sLang) . '</a></li>');
	echo ('<li><a href="javascript: submitSpecificMonth(\'11\')">' . pFormatMonthFromNum(11, $sLang) . '</a></li>');
	echo ('<li><a href="javascript: submitSpecificMonth(\'12\')">' . pFormatMonthFromNum(12, $sLang) . '</a></li>');
	echo ('</ul>');
	echo ( "\n</div>\n");
	echo ('<table>' . "\n<thead>\n<tr>\n");
	// Get the Day of Week header
	$iCol = 0;
	$stmtD = $GLOBALS ['dbMelkTexts']->prepare ( "select * from TransCalWeekdays order by DayNum asc" );
	$stmtD->execute ();
	$resultD = $stmtD->fetchAll ();
	// Cycle through the rows, which are in format of ISO-8601 numeric representation of the day of
	// the week (i.e., Mon = 1, Sun = 7).
	while ( $iCol < 7 ) {
		if ($iCol == 0) {
			$iTemp = 6;
		} else {
			$iTemp = $iCol - 1;
		}
		$sTemp = $resultD [$iTemp] [$sLang];
		echo ('<th>' . $sTemp . '</th>' . "\n");
		$iCol = $iCol + 1;
	}
	echo ("</tr>\n</thead>\n");
	// Write out the footer with the Legend of fasting icons
	echo ('<tfoot>' . "\n");
	echo ('<tr>' . "\n");
	echo ('<td colspan="7"><h4>' . pGetKnownTranslation ( "Details" ) . '</h4><p>' . "\n");
	echo ('<span class="class1feast">&#x2720;</span>&nbsp;=&nbsp;' . pGetKnownTranslation ( "Solemnity" ) . '&nbsp;&nbsp;&nbsp;' . "\n");
	echo ('<span class="class2feast">&#x2720;</span>&nbsp;=&nbsp;' . pGetKnownTranslation ( "Feast" ) . '&nbsp;&nbsp;&nbsp;' . "\n");
	echo ('<span class="class4feast">&#x2720;</span>&nbsp;=&nbsp;' . pGetKnownTranslation ( "Memoria" ) . '&nbsp;&nbsp;&nbsp;' . "\n");
	echo ('<span class="class5feast">&#x2720;</span>&nbsp;=&nbsp;' . pGetKnownTranslation ( "Optional Memoria" ) . '&nbsp;&nbsp;&nbsp;' . "\n");
	echo ("</p></td>\n");
	echo ('</tr>' . "\n");
	echo ('</tfoot>' . "\n");

	// Retrieve all records for the given month of the given year
	echo ("<tbody>\n");
	$sTempSQL = "select * from RCcalThisYear where (Date_this_year like '" . $sMonthDate . "%') and (EDM = " . $GLOBALS['iEDM'] . ") and (ForWhichCal = '" . $sCalendarChosen . "') order by Date_this_year asc";
	$stmt = $GLOBALS ['dbRomanCal']->prepare ( $sTempSQL );
	$stmt->execute ();
	$result = $stmt->fetchAll ();

	// cycle through each day of the month
	$iCol = 0;
	$bPrintedFirstOrdinaryTime = False;
	foreach ( $result as $entry ) {

		// The table to use for source data varies according to whether the feast is local or general
		if (substr ( $entry ['TypeIndex'], 0, 4 ) == 'CAL_') {
			$CalTable = "CalendarOTHER";
		} else {
			// default value is the General Roman Calendar
			$CalTable = "CalendarGEN";
		}
		// Now get the record from the appropriate Calendar
		$sTempSQL2 = "select * from " . $CalTable . " where TypeIndex = '" . $entry ['TypeIndex'] . "' order by Date_this_year asc";
		$stmt2 = $GLOBALS ['dbRomanCal']->prepare ( $sTempSQL2 );
		$stmt2->execute ();
		$result2 = $stmt2->fetch ();

		// If Sunday, start a new row
		if ($iCol == 0)
			echo ("<tr>\n");

		$arrDateFields = explode ( '-', $entry ['Date_this_year'] );
		$sDay = substr ( $entry ['Date_this_year'], - 2 );

		// Find the correct column and populate empty cells before the first day of the month.
		if ($sDay == "01")
			$iCol = pFindDayOfWeekColumn ( $entry ['Date_this_year'], $iCol );

			// Output the relevant cell data for each day of this month
		echo ("<td>\n");
		echo ('<p class="closishLinesPara"><span><a id="' . $entry ['Date_this_year'] . '">');
		if ($sLang == "ar") {
			echo (pConvDigits ( intval ( substr ( $entry ['Date_this_year'], - 2 ) ), "ar" ));
		} else {
			echo (intval ( substr ( $entry ['Date_this_year'], - 2 ) ));
		}
		// Class marker used to mark Solemnity, Feast, Memoria, Optional Memoria
		if ($result2 ['Rank'] == 'Solemnity') {
			$iRank = 1;
		} elseif ($result2 ['Rank'] == 'Feast') {
			$iRank = 2;
		} elseif ($result2 ['Rank'] == 'Memoria') {
			$iRank = 4;
		} else {
			$iRank = 5;
		}
		echo ('</a></span> <span class="class' . $iRank . 'feast">&#x2720;</span>');
		// Fasting information images
		if ($entry ['AbstinenceToday']) {
			echo ('<img src="../generic/images/fish.jpeg" class="feastNfastIndicator" alt="Abstinence today">&#xA0;' . "\n");
		}
		if ($entry ['FastingToday']) {
			echo ('<img src="../generic/images/fast.jpeg" class="feastNfastIndicator" alt="Fast today">&#xA0;' . "\n");
		}
		echo ("</p>\n");
		// Names of the feast in the chosen language
		$sShortFeastName = 'Short_name_' . $sLang;
		$arrShortNames = explode ( ';', $result2 [$sShortFeastName], 2 );
		// Do not display most of the Ordinary Time info.
		// For weekdays, it should only print for the first week of Ordinary Time,
		//    and for the first week of the resumption after Eastertide.
		if (($result2 ['PrintOnCal']) || ($entry ['PrintOnCal'])) {
			if (($iRank == 5) && ($result2 ['Lit_rank'] > 11)) {
				echo ('<p class="T2">' . $arrShortNames [0] . "</p>\n");
			} else {
				echo ('<p class="T1">' . $arrShortNames [0] . "</p>\n");
			}
		}
		if (count ( $arrShortNames ) > 1) {
			if ($result2 ['PrintOnCal']) {
				echo ('<p class="T2">' . $arrShortNames [1] . "</p>\n");
			}
		}
		// Check for other Optional Memoriae.
		$sOptionalMemNames = "";
		if (mb_strlen ( $entry ['Optional1'] ) > 1) {
			$sTemp = pFindOptionalMemoriae ( $entry ['Optional1'] );
			if (mb_strlen ( $sTemp ) > 1) {
				$sOptionalMemNames = $sTemp;
			}
			if (mb_strlen ( $entry ['Optional2'] ) > 1) {
				$sTemp = pFindOptionalMemoriae ( $entry ['Optional2'] );
				if (mb_strlen ( $sTemp ) > 1) {
					$sOptionalMemNames .= '; ' . $sTemp;
				}
				if (mb_strlen ( $entry ['Optional3'] ) > 1) {
					$sTemp = pFindOptionalMemoriae ( $entry ['Optional3'] );
					if (mb_strlen ( $sTemp ) > 1) {
						$sOptionalMemNames .= '; ' . $sTemp;
					}
				}
			}
		}
		if ($entry ['OptMemBVM']) {
			if (mb_strlen ( $sOptionalMemNames ) > 1) {
				$sOptionalMemNames = pGetKnownTranslation ( "Optional Memoria BVM" ) . '; ' . $sOptionalMemNames;
			} else {
				$sOptionalMemNames = pGetKnownTranslation ( "Optional Memoria BVM" );
			}
		}
		if (mb_strlen ( $sOptionalMemNames ) > 1) {
			// We now have at last one Optional Memoria
			$arrOptMemNames = explode ( ';', $sOptionalMemNames, 3 );
			echo ('<p class="T2">' . $arrOptMemNames [0] . "</p>\n");
			if (count ( $arrOptMemNames ) > 1) {
				echo ('<p class="T2">' . $arrOptMemNames [1] . "</p>\n");
				if (count ( $arrOptMemNames ) > 2) {
					echo ('<p class="T2">' . $arrOptMemNames [2] . "</p>\n");
				}
			}
		}
		// Readings may have Epistle, Gospel, and sometimes an Eothinon Gospel.
		// ## *** Placeholder for readings, if desired.

		// End the cell
		echo ("</td>\n");

		// If Saturday, end the row
		if ($iCol == 6)
			echo ("</tr>\n");
			// Increment column counter
		$iCol = $iCol + 1;
		if ($iCol == 7)
			$iCol = 0;
	}

	// Populate any empty cells after the end of the month
	$bNeedTR = False;
	if (($iCol < 7) && ($iCol != 0)) {
		$bNeedTR = True;
		while ( $iCol <= 6 ) {
			echo ('<td class="next-month-day"><span><a id="dummy' . $iCol . '">&#xA0;</a></span></td>' . "\n");
			$iCol = $iCol + 1;
		}
	}
	if ($bNeedTR)
		echo ("</tr>\n");

		// finalise the table and divisions
	echo ("</tbody>\n</table>\n</div>\n</div>\n</div>\n</div>\n");


	// Hidden form to get a specific month
	echo ('<form id="formSpecificMonth" method="post" action="GetRCLitCal.php">' . "\n");
	echo ('<input type="hidden" name="dateMenu" value="' . $iYear . '" />' . "\n");
	echo ('<input type="hidden" name="langMenu" value="' . $sLang . '" />' . "\n");
	echo ('<input type="hidden" name="edmMenu" value="' . $iEDM . '" />' . "\n");
	echo ('<input type="hidden" name="calMenu" value="' . $sCalendarChosen . '" />' . "\n");
	echo ('<input type="hidden" name="iCalMenu" value="' . $GLOBALS ['bDoICAL'] . '" />' . "\n");
	echo ('<input type="hidden" name="monthMenu" value="0" />' . "\n");
	echo ("</form>\n");

	// End of the HTML page
	echo ("</div>\n");

	echo ('<!-- Bootstrap core JavaScript' . "\n");
	echo ('================================================== -->' . "\n");
	echo ('<!-- Placed at the end of the document so the pages load faster -->' . "\n");
	echo ('<script src="../generic/js/jquery.min.js"></script>' . "\n");
	echo ('<script src="../generic/js/bootstrap.min.js"></script>' . "\n");
	echo ('		<!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->' . "\n");
	echo ('		<script src="../generic/js/ie10-viewport-bug-workaround.js"></script>' . "\n");


	echo("</body>\n</html>\n");
}
function pFindOptionalMemoriae($sOptMem) {

	// Check whether General or Local then return the Short Name
	// The table to use for source data varies according to whether the feast is local or general
	if (substr ( $sOptMem, 0, 4 ) == 'CAL_') {
		$CalTable = "CalendarOTHER";
	} else {
		// default value is the General Roman Calendar
		$CalTable = "CalendarGEN";
	}
	// Now get the record from the appropriate Calendar
	$sTempSQL3 = "select * from " . $CalTable . " where TypeIndex = '" . $sOptMem . "' order by Date_this_year asc";
	$stmt3 = $GLOBALS ['dbRomanCal']->prepare ( $sTempSQL3 );
	$stmt3->execute ();
	$result3 = $stmt3->fetch ();

	$sShortFeastName = 'Short_name_' . $GLOBALS ['sLang'];
	// echo("<h3>" . $sOptMem . $result3[$sShortFeastName] . " : " . substr($sOptMem, 0, 4) . $sTempSQL3 . "<h3>\n");

	// Do not return a name, if it should not be printed.
	if ($result3 ['PrintOnCal']) {
		return $result3 [$sShortFeastName];
	}
}
function pFindDayOfWeekColumn($sDate, $iCol) {
	$dTempDate = strtotime ( $sDate );
	$iDoW = date ( "w", $dTempDate ); // Sun = 0; Sat = 6.
	$iColCounter = $iCol;
	while ( $iColCounter != $iDoW ) {
		// If $iCol is the same as $iDow, we exit.
		echo ('<td class="previous-month-day"><span><a id="dummy' . $iColCounter . '">&#xA0;</a></span></td>' . "\n");
		$iColCounter = $iColCounter + 1;
	}
	return $iColCounter;
}
function pFindReadingsRef($sCode, $bEothinon) {
	$sRefCode = $sCode;
	// Special work for the Eothinon to construct the Reference
	if ($bEothinon) {
		if (($sCode != NULL) && (mb_strlen ( $sCode ) > 0)) {
			$iEothinonNumber = NULL;
			if (mb_strlen ( $sCode ) <= 2) {
				// assume we have found a number
				$iEothinonNumber = sprintf ( "%02d", $sCode );
				$sTempInterimEothinon = 'EOTHGOS' . $iEothinonNumber;
			} else {
				$sTempInterimEothinon = $sCode;
			}
			$stmtT = $GLOBALS ['dbMelkTexts']->prepare ( "select DB_Key from Eothina where Code = ?" );
			$stmtT->execute ( array (
					$sTempInterimEothinon
			) );
			$resultT = $stmtT->fetchAll ();
			if ($resultT == '' || $resultT == NULL)
				return;
			$sTempRef = $resultT [0] ['DB_Key'];
			$sRefCode = $sTempRef;
		}
	}

	// Get reference from all codes
	$stmtT = $GLOBALS ['dbBigTexts']->prepare ( "select Ref, " . $GLOBALS ['sLang'] . " from Texts where DB_Key = ?" );
	$stmtT->execute ( array (
			$sRefCode
	) );
	$resultT = $stmtT->fetchAll ();
	if ($resultT == '' || $resultT == NULL)
		return;
	$sTempRef = $resultT [0] ['Ref'];
	$sTempRef = pTranslateRef ( $sTempRef, $GLOBALS ['sLang'] );
	return $sTempRef;
}

?>

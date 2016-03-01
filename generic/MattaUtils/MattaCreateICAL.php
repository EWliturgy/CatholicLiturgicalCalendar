<?php
if (! defined ( 'page_include_allowed' )) {
	// Force 404 error by seeking a non-existant dummy page.
	$host = $_SERVER ['HTTP_HOST'];
	$uri = rtrim ( dirname ( $_SERVER ['PHP_SELF'] ) );
	$dummypage = 'somepage.html';
	header ( "Location: http://$host$uri/$dummypage" );
	exit ();
}

function pCreateICAL($iYear, $sLang, $iEDM, $sCalendarChosen, $sRCorByz) {
	if ($sRCorByz == 'RC') {
		$sFileName = 'RCcal' . $sCalendarChosen . $iYear . $sLang . '.ics';
	} else {
		$sFileName = 'Byzcal' . $iEDM . $iYear . $sLang . '.ics';
	}
	$sDir = joinPaths ( $GLOBALS['sDataDir'], '..', 'docs', 'ics' );
	$sPath = joinPaths ( $sDir, $sFileName );

	// Check to see if the file already exists. If so, send it and return
	if (file_exists ( $sPath )) {
		header ( 'Content-Description: File Transfer' );
		header ( 'Content-Type: application/octet-stream' );
		header ( 'Content-Disposition: attachment; filename=' . basename ( $sPath ) );
		header ( 'Content-Transfer-Encoding: binary' );
		header ( 'Expires: 0' );
		header ( 'Cache-Control: must-revalidate' );
		header ( 'Pragma: public' );
		header ( 'Content-Length: ' . filesize ( $sPath ) );
		ob_clean ();
		flush ();
		readfile ( $sPath );
		exit ();
	}
	
	// Create the file
	$sICSfile = "";
	$oFile = fopen ( $sPath, "w" );
	$sICSfile .= "BEGIN:VCALENDAR\r\n";
	$sICSfile .= "VERSION:2.0\r\n";
	// Include UTF-8 character early to ensure correct encoding upon read.
	if ($sRCorByz == 'RC') {
		$sICSfile .= "PRODID:-//Roman Catholic Church//Liturgical Calendar Ecclesiæ//" . strtoupper ( $sLang ) . "\r\n";
	} else {
		$sICSfile .= "PRODID:-//Eastern Orthodox or Byzantine Catholic//Liturgical Calendar Ecclesiæ//" . strtoupper ( $sLang ) . "\r\n";
	}
	if ($iEDM == 1) {
		$sICSfile .= "CALSCALE:JULIAN\r\n";
	} else {
		$sICSfile .= "CALSCALE:GREGORIAN\r\n";
	}
	$sICSfile .= "METHOD:PUBLISH\r\n";
	
	// Loop for each day of the year.
	// We only output records for those with Print-to-Calendar set to TRUE.
	if ($sRCorByz == 'RC') {
		$sMonthDate = $iYear . '-' . sprintf ( '%02d', "01" );
		$sTempSQL = "select * from RCcalThisYear where (Date_this_year like '" . $iYear . "%') and (ForWhichCal = '" . $sCalendarChosen . "') order by Date_this_year asc";
		$stmt = $GLOBALS ['dbRomanCal']->prepare ( $sTempSQL );
		$stmt->execute ();
		$result = $stmt->fetchAll ();
		
		// cycle through each day of the month
		foreach ( $result as $entry ) {
			
			// The table to use for source data varies according to whether the feast is local or general
			if (substr ( $entry ['TypeIndex'], 0, 4 ) == 'CAL_') {
				$CalTable = "CalendarOTHER";
			} else {
				// default value is the General Roman Calendar
				$CalTable = "CalendarGEN";
			}
			// Now get the record from the appropriate Calendar
			$sTempSQL2 = "select rowid, * from " . $CalTable . " where TypeIndex = '" . $entry ['TypeIndex'] . "' order by Date_this_year asc";
			$stmt2 = $GLOBALS ['dbRomanCal']->prepare ( $sTempSQL2 );
			$stmt2->execute ();
			$result2 = $stmt2->fetch ();
			
			// Begin the output for the individual event,
			//  if it should be printed and it has a name -- i.e. it is not a place-holder record.
			if (($result2 ['PrintOnCal']) && ($result2['Short_name_en'])) {
				// Create the body of the event description
				$sBody = "";
				// Full nameof feast and its liturgical rank
				$sLongFeastName = 'Feast_' . $sLang;
				$sBody .= $result2 [$sLongFeastName] . "\n";
				// Liturgical Rank
				if (strlen ( $result2 ['Rank'] ) > 0) {
					$sBody .= $result2 ['Rank'] . "\n";
				}
				// Optional Memorias
				$sOptionalMemNames = "";
				if (mb_strlen ( $entry ['Optional1'] ) > 1) {
					$sTemp = pFindOptionalMemoriae ( $entry ['Optional1'] );
					if(mb_strlen($sTemp) > 1) {
						$sOptionalMemNames = $sTemp;
					}
					if (mb_strlen ( $entry ['Optional2'] ) > 1) {
						$sTemp = pFindOptionalMemoriae ( $entry ['Optional2'] );
						if(mb_strlen($sTemp) > 1) {
							$sOptionalMemNames .= '; ' . $sTemp;
						}
						if (mb_strlen ( $entry ['Optional3'] ) > 1) {
						$sTemp = pFindOptionalMemoriae ( $entry ['Optional3'] );
						if(mb_strlen($sTemp) > 1) {
							$sOptionalMemNames .= '; ' . $sTemp;
						}
						}
					}
				}
				if ($entry ['OptMemBVM']) {
					if(mb_strlen($sOptionalMemNames) > 1) {
						$sOptionalMemNames = pGetKnownTranslation ( "Optional Memoria BVM" ) . '; ' . $sOptionalMemNames;
					} else {
						$sOptionalMemNames = pGetKnownTranslation ( "Optional Memoria BVM" );
					}
				}
				if(mb_strlen($sOptionalMemNames) > 1) {
					$sBody .= "Optional Memorias: " . $sOptionalMemNames . "\n";
				}
				// Fasting and abstinence
				if ($entry ['AbstinenceToday'])
					$sBody .= "Abstinence from meat today.\n";
				if ($entry ['FastingToday'])
					$sBody .= "Today is a day of Fast.\n";
				//Liturgical Cycle
				$sTemp = pFindCurrentCycle($entry['TypeIndex'], $entry['CurrentCycle'], $sLang);
				if(mb_strlen($sTemp) > 1) 
					$sBody .= "Liturgical Cycle: " . $sTemp . "\n";
				//Psalter Week for LotH
				if(mb_strlen($entry ['PsalterWeek']) > 0)
					$sBody .= "LotH: Psalter Week " . $entry ['PsalterWeek'] . "\n";
							
					// Create the event
				$sICSfile .= "BEGIN:VEVENT\r\n";
				$sICSfile .= "DTSTAMP:" . date ( "Ymd" ) . "T" . date ( "His" ) . "Z" . "\r\n";
				$sTemp = str_replace ( "-", "", $entry ['Date_this_year'] );
				$sICSfile .= "DTSTART;VALUE=DATE:" . $sTemp . "\r\n";
				$sICSfile .= "UID:" . $sTemp . "@" . sprintf ( '%04d', $result2 ['rowid'] ) . "\r\n";
				$dTemp = new DateTime($entry ['Date_this_year']);
				$dTemp->add(new DateInterval('P1D'));
				$sTemp = $dTemp->format('Ymd');
				$sICSfile .= "DTEND;VALUE=DATE:" . $sTemp . "\r\n";
				$sICSfile .= "LOCATION:RC\r\n";
				$sShortFeastName = 'Short_name_' . $sLang;
				$sICSfile .= "SUMMARY:" . pCleanTextForICAL ( $result2 [$sShortFeastName] ) . "\r\n";
				$sICSfile .= "DESCRIPTION:" . pCleanTextForICAL($sBody, 1) . "\r\n";
				// finalise the event
				$sICSfile .= "CLASS:PRIVATE\r\nTRANSP:TRANSPARENT\r\nEND:VEVENT\r\n";
			}
			
		}
	} else {
		//We process the Byzantine calendar details here.
		//   The table to use for source data varies according to the Easter Dating Method
		if($iEDM == 2)
		{
			$CalTable = "OrthodoxCal";
			$sWhichEasterMethod = "Calendar using Orthodox Easter";
		} elseif($iEDM == 1)
		{
			$CalTable = "JulianCal";
			$sWhichEasterMethod = "Calculating Easter according to the Julian Calendar";
		} else {
			//default value is Western Easter
			$CalTable = "Cal";
			$sWhichEasterMethod = "Calculating Easter according to the Gregorian Calendar";
		}
		$sTempSQL = "select rowid, * from " . $CalTable . " where Date_this_year like '" . $iYear . "%' order by Date_this_year asc";
		$stmt = $GLOBALS['dbMelkTexts']->prepare($sTempSQL);
		$stmt->execute();
		$result = $stmt->fetchAll();
		//  loop through the year
		foreach ($result as $entry) {
			if($entry['Print_on_Cal_with_Sun123']) {
				// Create the body of the event description
				$sBody = "";
				// Full name of feast
				$sLongFeastName = 'Feast_' . $sLang;
				$sBody .= $entry [$sLongFeastName] . "\n";
				if(mb_strlen($entry['Trad_Fasting']) > 0) 
					$sBody .= pGetKnownTranslation($entry['Trad_Fasting']) . "\n";
				if(mb_strlen($entry['Tone']) > 0)
					$sBody .= pGetKnownTranslation("Tone of Week:") . " " . $entry['Tone'] . "\n";
				
				// Create the event
				$sICSfile .= "BEGIN:VEVENT\r\n";
				$sICSfile .= "DTSTAMP:" . date ( "Ymd" ) . "T" . date ( "His" ) . "Z" . "\r\n";
				$sTemp = str_replace ( "-", "", $entry ['Date_this_year'] );
				$sICSfile .= "DTSTART;VALUE=DATE:" . $sTemp . "\r\n";
				$sICSfile .= "UID:" . $sTemp . "@" . sprintf ( '%04d', $entry ['rowid'] ) . "\r\n";
				$dTemp = new DateTime($entry ['Date_this_year']);
				$dTemp->add(new DateInterval('P1D'));
				$sTemp = $dTemp->format('Ymd');
				$sICSfile .= "DTEND;VALUE=DATE:" . $sTemp . "\r\n";
				$sICSfile .= "LOCATION:Byzantine\r\n";
				$sShortFeastName = 'Short_name_' . $sLang;
				$sICSfile .= "SUMMARY:" . pCleanTextForICAL ( $entry [$sShortFeastName] ) . "\r\n";
				$sICSfile .= "DESCRIPTION:" . pCleanTextForICAL($sBody, 1) . "\r\n";
				// finalise the event
				$sICSfile .= "CLASS:PRIVATE\r\nTRANSP:TRANSPARENT\r\nEND:VEVENT\r\n";
			}
		}
	}
	$sICSfile .= "END:VCALENDAR\r\n";
	
	// Close the file
	fwrite ( $oFile, $sICSfile );
	fclose ( $oFile );

	
	// Pass the file for download
	header ( 'Content-Description: File Transfer' );
	header ( 'Content-Type: application/octet-stream' );
	header ( 'Content-Disposition: attachment; filename=' . basename ( $sPath ) );
	header ( 'Content-Transfer-Encoding: binary' );
	header ( 'Expires: 0' );
	header ( 'Cache-Control: must-revalidate' );
	header ( 'Pragma: public' );
	header ( 'Content-Length: ' . filesize ( $sPath ) );
	ob_clean ();
	flush ();
	readfile ( $sPath );
	exit ();
}
function pCleanTextForICAL($sText, $bManageLength = 0) {
	$iMAX_LEN = 72; // maximum line length is 75 octets; longer lines should be folded (cf. RFC 2445).
	$sTemp = $sText;
	
	// Clean up text for iCal rules
	$sTemp = str_replace ( "\B", "\B\B", $sTemp );
	$sTemp = str_replace ( ",", "\\,", $sTemp );
	$sTemp = str_replace ( ";", "\\;", $sTemp );
	$sTemp = str_replace ( "\n", "\\n", $sTemp );
	
	// Shorten line, if required.
	if ($bManageLength) {
		$iCount = mb_strlen ( $sTemp );
		$sFinalBody = "";
		while ( $iCount > $iMAX_LEN ) {
			$sFinalBody .= mb_substr ( $sTemp, 0, $iMAX_LEN ) . "\r\n ";
			$iTemp = $iMAX_LEN ;
			$sTemp = mb_substr ( $sTemp, $iTemp );
			$iCount = mb_strlen ( $sTemp );
		}
		// Finalise the description to accord with the RFC
		$sFinalBody .= $sTemp . "\r\n  \\n\\n";
		$sTemp = $sFinalBody;
	}
	
	return $sTemp;
}

function pFindCurrentCycle($sTypeIndex, $sCurrentCycle, $sLang) {
	
	//Check first, if the CurrentCycle is set, and use it.
	if(strlen($sCurrentCycle) > 1) {
		// The table to use for source data varies according to whether the feast is local or general
		if (substr ( $sCurrentCycle, 0, 4 ) == 'CAL_') {
			$CalTable = "CalendarOTHER";
		} else {
			// default value is the General Roman Calendar
			$CalTable = "CalendarGEN";
		}
		// Now get the record from the appropriate Calendar
		$sTempSQL9 = "select rowid, * from " . $CalTable . " where TypeIndex = '" . $sCurrentCycle . "' order by Date_this_year asc";
		$stmt9 = $GLOBALS ['dbRomanCal']->prepare ( $sTempSQL9 );
		$stmt9->execute ();
		$result9 = $stmt9->fetch ();
		$sLongFeastName = 'Feast_' . $sLang;
		return $result9[$sLongFeastName];
	} elseif((substr($sTypeIndex, 0, 3) == "MOV") || (substr($sTypeIndex, 0, 3) == "ORW")) {
		// The table to use for source data varies according to whether the feast is local or general
		if (substr ( $sTypeIndex, 0, 4 ) == 'CAL_') {
			$CalTable = "CalendarOTHER";
		} else {
			// default value is the General Roman Calendar
			$CalTable = "CalendarGEN";
		}
		// Now get the record from the appropriate Calendar
		$sTempSQL9 = "select rowid, * from " . $CalTable . " where TypeIndex = '" . $sTypeIndex . "' order by Date_this_year asc";
		$stmt9 = $GLOBALS ['dbRomanCal']->prepare ( $sTempSQL9 );
		$stmt9->execute ();
		$result9 = $stmt9->fetch ();
		$sLongFeastName = 'Feast_' . $sLang;
		return $result9[$sLongFeastName];
	}
}

?>
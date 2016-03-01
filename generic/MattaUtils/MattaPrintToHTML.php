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

define('sCOPYRIGHT_en', '(NRSV)');
define('sCOPYRIGHT_es', '(Versión Biblia de Jerusalén, 1976)');
define('sCOPYRIGHT_fr', '(Copyright AELF - Paris - 1980 - Tous droits réservés)');
define('sCOPYRIGHT_pt', '(CNBB)');
define('sCOPYRIGHT_ar', '');
define('sCOPYRIGHT_id', '(Copyright (C) Lembaga Alkitab Indonesia 1994)');
						#'Teks Alkitab Terjemahan Baru Indonesia

function pPrintOutHTMLRefsNTexts($sDataKey, $sH1Text = NULL, $sH2Text = NULL, $sH3Text = NULL, $sH4Text = NULL, $iEothinonNumber = NULL, $sWhence = NULL, $iChantTone = NULL)
{
	$stmtT = $GLOBALS['dbBigTexts']->prepare("select Ref, " . $GLOBALS['sLang'] . " from Texts where DB_Key = ?");
	$stmtT->execute(array($sDataKey));
	$resultT = $stmtT->fetchAll();
	if ($resultT == '' || $resultT == NULL)
		return;
	$sTempRef = $resultT[0]['Ref'];
	$sTempRef = pTranslateRef($sTempRef, $GLOBALS['sLang']);
	if($iEothinonNumber != NULL)
		$sTempRef = pGetKnownTranslation('Eothinon Gospel') . ' ' . $iEothinonNumber . ' -- ' . $sTempRef;
	if($iChantTone != NULL) 
	{
		$sTemp = ' ( ' . pGetKnownTranslation('Tone') . ' ' . pConvDigits($iChantTone, $GLOBALS['sLang']) . ' ) ';
		$sTempRef = $sTempRef . $sTemp;
	}
	$sTempText = $resultT[0][$GLOBALS['sLang']];
	if(($sTempText == NULL) || (mb_strlen($sTempText) == 0))
		return;

	//Check for Epistle or Gospel and add Whence to the reference.
	if (($sWhence != NULL))
	{
		$sTempRef = $sTempRef . ' ( ' . $sWhence . ' ) ';
	}
	
	//We have a reading; write it all out.
	if($sH1Text != NULL)
		echo("<h1>" . $sH1Text . "</h1>\n");
	if($sH2Text != NULL)
	{
		if($GLOBALS['sLang'] == 'en')
		{
			echo("<h2>" . $sH2Text . "</h2>\n");
		} else {
			$sTempHeadingText = pGetKnownTranslation($sH2Text);
			if(($GLOBALS['sLang'] != 'en') && ($GLOBALS['sLang'] != 'id'))
				$sTempHeadingText = pNormaliseUnicode($sTempHeadingText);
			echo("<h2>" . $sTempHeadingText . "</h2>\n");
		}
	}
	if($sH3Text != NULL)
	{
		if($GLOBALS['sLang'] == 'en')
		{
			echo("<h3>" . $sH3Text . "</h3>\n");
		} else {
			$sTempHeadingText = pGetKnownTranslation($sH3Text);
			if(($GLOBALS['sLang'] != 'en') && ($GLOBALS['sLang'] != 'id'))
				$sTempHeadingText = pNormaliseUnicode($sTempHeadingText);
			echo("<h3>" . $sTempHeadingText . "</h3>\n");
		}
	}
	if($sH4Text != NULL)
	{
		if($GLOBALS['sLang'] == 'en')
		{
			echo("<h4>" . $sH4Text . "</h4>\n");
		} else {
			$sTempHeadingText = pGetKnownTranslation($sH4Text);
			if(($GLOBALS['sLang'] != 'en') && ($GLOBALS['sLang'] != 'id'))
				$sTempHeadingText = pNormaliseUnicode($sTempHeadingText);
			echo("<h4>" . $sTempHeadingText . "</h4>\n");
		}
	}

	//Now output the Reference and the verse(s).
	//The Arabic text may not be normalized according to Unicode.
	//Pass other non-English European texts through, in case.
	if(($GLOBALS['sLang'] != 'en') && ($GLOBALS['sLang'] != 'id'))
	{
		$sTempRef = pNormaliseUnicode($sTempRef);
		$sTempText = pNormaliseUnicode($sTempText);
	}
	
	//clean up strange characters that do not print well in HTML.
	$sTempRef = pCleanUpHTML($sTempRef);
	$sTempText = pCleanUpHTML($sTempText);
	
	//write out the cleaned text	
	echo('<p class="reading-ref">' . $sTempRef . "</p>\n");
	echo('<p class="reading-text">' . $sTempText);
	//Copyright text
	switch ($GLOBALS['sLang'])
	{
		case 'en':
			echo(' ' . sCOPYRIGHT_en);
			break;
		case 'ar':
			echo(' ' . sCOPYRIGHT_ar);
			break;
		case 'pt':
			echo(' ' . sCOPYRIGHT_pt);
			break;
		case 'fr':
			echo(' ' . sCOPYRIGHT_fr);
			break;
		case 'id':
			echo(' ' . sCOPYRIGHT_id);
			break;
		case 'es':
			echo(' ' . sCOPYRIGHT_es);
			break;
	}
	echo("</p>\n");
	return;
}

function pPrintOutHTMLTextsOnly($sText)
{
	$sTempText = $sText;
	if($GLOBALS['sLang'] == 'ar')
		$sTempText = pConvDigits($sTempText, $GLOBALS['sLang']);
	if(($GLOBALS['sLang'] != 'en') && ($GLOBALS['sLang'] != 'id'))
		$sTempText = pNormaliseUnicode($sTempText);

	$sTempText = pCleanUpHTML($sTempText);
	echo("<p>" . $sTempText . "</p>\n");

	return;
}

function pPrintOtherDLTexts($sDataKey, $sH1Text = NULL, $sH2Text = NULL, $sH3Text = NULL, $sH4Text = NULL, $iTimesToChant = NULL)
{
	//We have a reading; write it all out.
	$sTempText = $sDataKey;
	$sAccumulatedHeadings = '';
	if($sH1Text != NULL)
		$sAccumulatedHeadings = "<h1>" . $sH1Text . "</h1>\n";
	if($sH2Text != NULL)
	{
		if($GLOBALS['sLang'] == 'en')
		{
			$sAccumulatedHeadings = $sAccumulatedHeadings . "<h2>" . $sH2Text . "</h2>\n";
		} else {
			$sTempHeadingText = pGetKnownTranslation($sH2Text);
			if(($GLOBALS['sLang'] != 'en') && ($GLOBALS['sLang'] != 'id'))
				$sTempHeadingText = pNormaliseUnicode($sTempHeadingText);
			$sAccumulatedHeadings = $sAccumulatedHeadings . "<h2>" . $sTempHeadingText . "</h2>\n";
		}
	}
	if($sH3Text != NULL)
	{
		if($GLOBALS['sLang'] == 'en')
		{
			$sAccumulatedHeadings = $sAccumulatedHeadings . "<h3>" . $sH3Text . "</h3>\n";
		} else {
			$sTempHeadingText = pGetKnownTranslation($sH3Text);
			if(($GLOBALS['sLang'] != 'en') && ($GLOBALS['sLang'] != 'id'))
				$sTempHeadingText = pNormaliseUnicode($sTempHeadingText);
			$sAccumulatedHeadings = $sAccumulatedHeadings . "<h3>" . $sTempHeadingText . "</h3>\n";
		}
	}
	if($sH4Text != NULL)
	{
		if($GLOBALS['sLang'] == 'en')
		{
			$sAccumulatedHeadings = $sAccumulatedHeadings . "<h4>" . $sH4Text . "</h4>\n";
		} else {
			$sTempHeadingText = pGetKnownTranslation($sH4Text);
			if(($GLOBALS['sLang'] != 'en') && ($GLOBALS['sLang'] != 'id'))
				$sTempHeadingText = pNormaliseUnicode($sTempHeadingText);
			$sAccumulatedHeadings = $sAccumulatedHeadings . "<h4>" . $sTempHeadingText . "</h4>\n";
		}
	}
	
	$bHeadingsOutput = FALSE;
	$arrTemp = explode(' ', $sDataKey);
	$iTempTimes = $iTimesToChant;
	foreach ($arrTemp as $sTempDataKey)
	{
		//Find all lines of text from the AllTexts table
		if (strpos($sTempDataKey, "@")) 
		{
			$sSQLquery = "select * from AllTexts where IndexLine like '" . $sTempDataKey . "%' order by IndexLine asc";
		} else {
			$sSQLquery = 'select * from AllTexts where "Index" = ' . "'" . $sTempDataKey . "' order by IndexLine asc";
		}
		$stmt = $GLOBALS['dbBigTexts']->prepare($sSQLquery);
		$stmt->execute();
		$result = $stmt->fetchAll();
		$sList = $result;
		//var_dump($sList);
		$sTempParagraph = '';
		
		if ($sList != NULL)
		{
			foreach ($sList as $sTempRec)
			{
				$sTempCol = '';
				$sTextToTrans = '';
				$sTempCol = 'Sentence_' . $GLOBALS['sLang'];
				$sTextToTrans = $sTempRec[$sTempCol];
				if (($sTextToTrans != NULL) && (mb_strlen($sTextToTrans) > 0))
				{
					if ($GLOBALS['sLang'] == 'ar')
						$sTextToTrans = pConvDigits($sTextToTrans, 'ar');
					$sTempParagraph = $sTextToTrans ;
					$iInstruction = $sTempRec['Instruction'];
					$iSticheron = $sTempRec['Sticheron'];
					$sHeading = $sTempRec['Heading'];
					$sTempCol = 'LeadingNote_' . $GLOBALS['sLang'];
					$sLeadingNote = $sTempRec[sTempCol];
					$sTempCol = 'FollowingNote_' . $GLOBALS['sLang'];
					$sFollowingNote = $sTempRec[sTempCol];
		
					#Print out the group of lines
					#   The Arabic text may not be normalised according to Unicode
					if (mb_strlen($sTempParagraph) > 0)
					{
						if ($GLOBALS['sLang'] == 'ar')
							$sTempParagraph = pNormaliseUnicode($sTempParagraph);
						#   Clean up strange characters that do not display well in HTML
						$sTempParagraph = pCleanUpHTML($sTempParagraph);
						#   Write out the cleaned text to file, writing any headings first
						if (! $bHeadingsOutput)
						{
							echo($sAccumulatedHeadings);
							$bHeadingsOutput = TRUE;
						}
						if (($iInstruction != NULL) && ($iInstruction == 1))
						{
							echo('<p class="rubric">' . $sTempParagraph . '</p>' . "\n");
						} else {
							echo('<p class="dl-text">');
							if (($sLeadingNote != NULL) && (mb_strlen($sLeadingNote) > 0))
								echo('<span class="rubrical">' . $sLeadingNote . '</span> ');
							echo($sTempParagraph);
							if (mb_substr($sTempDataKey,0, 8) != "RES-TROP") {
								if (($iTempTimes != NULL) && is_numeric($iTempTimes) && ($iTempTimes > 1))
								{
									if ($sFollowingNote == NULL)
									{
										$sFollowingNote = pGetKnownTranslation(' (x' . $iTempTimes . ')');
									} else {
										$sFollowingNote = $sFollowingNote . pGetKnownTranslation(' (x' . $iTempTimes . ')');
									}
								}
							}
							if (($sFollowingNote != NULL) && (mb_strlen($sFollowingNote) > 0))
							{
								echo(' <span class="rubrical">' . $sFollowingNote . '</span>');
								echo('</p>' . "\n");
							} else {
								echo('</p>' . "\n");
							}
						}
					}
				}
			}
		}
		//Only first Troparion should be sung more than once.
		if (mb_substr($sTempDataKey,0, 8) != "RES-TROP") {
			if ($iTempTimes != NULL)
				$iTempTimes = 1;
		}
	}
}

?>
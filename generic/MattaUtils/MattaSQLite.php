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

function MattaOpenSQLiteDB($sDataDir, $sDBName)
{
	
	try
	{
		//open the database
		global $sPath ;
		$sPath = joinPaths($sDataDir, $sDBName);
		
		if (is_readable($sPath))
		{
			$db = new PDO('sqlite:'.$sPath);
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			return $db;
		} else {
			echo("Cannot find file: $sPath");
		}
		
	}
	catch(PDOException $e)
	{
		print 'Exception : '.$e->getMessage();
	}
}

function pCheckDateOK($dDate)
{
	//Compares string dates, as the ISO 8601 format short sort correctly.

	//Get first date within the database
	$stmt = $GLOBALS['dbMelkTexts']->prepare("select Date_this_year from Cal order by Date_this_year asc limit 1");
	$stmt->execute();
	$result = $stmt->fetchAll();
	if ($result == '' || $result == NULL)
		return;
	$GLOBALS['sFirstDate'] = $result[0]['Date_this_year'];
	
	//Get last date within the database
	$stmt = $GLOBALS['dbMelkTexts']->prepare("select Date_this_year from Cal order by Date_this_year desc limit 1");
	$stmt->execute();
	$result = $stmt->fetchAll();
	if ($result == '' || $result == NULL)
		return;
	$GLOBALS['sLastDate'] = $result[0]['Date_this_year'];
	
	//Check date is within range
	if(($dDate >= strtotime($GLOBALS['sFirstDate'])) && ($dDate <= strtotime($GLOBALS['sLastDate'])))
	{
		return TRUE;
	} else {
		return FALSE;
	}
	
}

function pUseRaya($sDataDir, $sDBName)
{
	//Get handle to normal BigTexts
	//$dbDisk = MattaOpenSQLiteDB($sDataDir, $sDBName);
	global $sPath ;
	global $sPath2;
	global $dbMem;
	$sPath = joinPaths($sDataDir, $sDBName);
	$sPath2 = joinPaths($sDataDir, 'MelkExtraTexts.db3');
	$sSQLattach = 'attach database "' . $sPath . '" as dbDisk';
	$sSQLattach2 = 'attach database "' . $sPath2 . '" as dbExtraDisk';
	$sSQLcreateTable1 = 'create table Texts as select DB_Key, Ref, en, ar  from dbDisk.Texts';
	$sSQLcreateTable2 = 'create table AllTexts as select * from dbDisk.AllTexts';
	$sSQLupdate = 'update Texts set en = (select E.Raya from dbExtraDisk.Texts as E where E.DB_Key = Texts.DB_Key)';
	$sSQLdetach = 'detach dbDisk';
	$sSQLdetach2 = 'detach dbExtraDisk';
	try 
	{
		$dbMem = new PDO('sqlite::memory:');
		$dbMem->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);		
	}
	catch(PDOException $e)
	{
		print 'Exception : '.$e->getMessage();
	}
	$dbMem->exec($sSQLattach);
	$dbMem->exec($sSQLattach2);
	$dbMem->exec($sSQLcreateTable1);
	$dbMem->exec($sSQLcreateTable2);
	$dbMem->exec($sSQLupdate);
	
	return $dbMem;
}

?>
<?php 
//* Stunters Title Pack > http://stunters.org > Cup Plugin for Stunters Control
//* Version		0.2
//* Coded by	Spaï

// 2014-03-10 : v0.2
// Added page selector on Maps Details table
// Added Cup Rank Window
// 2014-09-15 : v.03
// pastis, change button and show various 


# TODO
# Page selection on server ranking manialink

class plugin_stunters_cup extends FoxControlPlugin {

	public $config;
	public $MapUId;
	public $MapName;
	public $MapsByPage = 15;
	public $Planets;
	
	public function GetMapInfos()
	{
		$this->instance()->client->query('GetCurrentMapInfo');
		$mapInfo = $this->instance()->client->getResponse();
		$this->MapUId = $mapInfo['UId'];
		$this->MapName = $mapInfo['Name'];
		
		$this->instance()->client->query('GetServerPlanets');
		$this->Planets = $this->instance()->client->getResponse();
	}
	
	public function getPlayersList()
	{
		$this->instance()->client->query('GetPlayerList', 300, 0);
		$playerlist = $this->instance()->client->getResponse();
		return($playerlist);
	}
	
	public function sendInformations($login = null)
	{
		$this->GetMapInfos();
		$players = $this->getPlayersList();
		
		if ($login)
		{
			$score = 0;
			$sql = 'SELECT * FROM records WHERE challengeid="'.$this->MapUId.'" AND playerlogin="'.$login.'"';
			$reqScore = mysqli_query($this->db, $sql);
			
			if ($reqScore->num_rows)
			{
				$resScore = $reqScore->fetch_assoc();
				$score = $resScore["score"];

				$rank = $this->getPlayerRank($login, $this->MapUId);
				if($rank)
				{
					$this->chatToLogin($login, '$dd0Your Score $fff$o$w'.$score.' $z$dd0Rank $fff$o$w$s'.$rank.' $z$dd0on $z'.$this->MapName);
				}
			}
			else
			{
				$this->chatToLogin($login, '$dd0No Records $z$dd0on $z'.$this->MapName);
			}
		}
		else
		{		
			foreach($players as $id=>$player)
			{		
				$score = 0;
				$sql = 'SELECT * FROM records WHERE challengeid="'.$this->MapUId.'" AND playerlogin="'.$player["Login"].'"';
				$reqScore = mysqli_query($this->db, $sql);
				
				if ($reqScore->num_rows)
				{
					$resScore = $reqScore->fetch_assoc();
					$score = $resScore["score"];

					$rank = $this->getPlayerRank($player["Login"], $this->MapUId);
					if($rank)
					{
						$this->chatToLogin($player["Login"], '$dd0Your Score $fff$o$w'.$score.' $z$dd0Rank $fff$o$w$s'.$rank.' $z$dd0on $z'.$this->MapName);
					}
				}
				else
				{
					$this->chatToLogin($player["Login"], '$dd0No Records $z$dd0on $z'.$this->MapName);
				}
				
				
				$this->SendRankWindow($player["Login"]);
			}
		}
	}
	
	public function onStartUp()
	{	
		$this->name = 'Stunters Cup';
		$this->author = 'Spaï';
		$this->version = '0.3';
	
		$this->registerCommand('cup', 'Show cup maps details', false);
		$this->registerMLIds(51);
	
		$this->MapUId = "";
		$this->MapName = "";
		$this->Planets = "";
	
		// Load config file (plugin.stunters_cup.config.xml)
		// $this->config = $this->loadConfig();
	
		// Getting current Map Name & UId
		$this->GetMapInfos();
		
		$this->sendInformations();
	}
	
	public function onBeginMap($args)
	{
		//Getting current Map Name & UId
		$this->GetMapInfos();
		
		$this->sendInformations();
	}
	
	public function onCommand($args)
	{
		$login = $args[1];
		$command = $args[2];
		
		if ($command == "cup")
		{			
			$this->SendMapsTable($login);
			$this->sendInformations($login);
		}
	}
			
	public function onPlayerConnect($args)
	{
		// echo $args["Login"];
		$this->sendInformations($args["Login"]);
		$this->SendRankWindow($args["Login"]);
	}
	
	// Search rank of a player on a map in database
	public function getPlayerRank($player, $mapuid)
	{
		$sql = 'SELECT * FROM records WHERE challengeid="'.$mapuid.'" ORDER BY score DESC';
		$reqScore = mysqli_query($this->db, $sql);
		
		$rank = 1;
		while ($resScore = $reqScore->fetch_assoc())
		{
			if ($resScore['playerlogin'] == $player) return $rank;
			$rank++;
		}
		
		return -1;
	}
	
	public function SendMapsTable($login, $selectedPage = 1)
	{	
		global $settings;
		
		#### Calculate server ranking
		// Get map List
		$this->instance()->client->query("GetMapList", 500, 0);
		$mapList = $this->instance()->client->getResponse();
		
		$players = Array();
		$playersNickName = Array();
		
		$maps = Array();
		
		// Get database player list
		$sql = "SELECT playerlogin, nickname FROM players";
		$mysql = mysqli_query($this->db, $sql);
		
		// Initialize players arrays
		while ($player = $mysql->fetch_assoc())
		{
			$players[$player["playerlogin"]] = 0;
			$playersNickName[$player["playerlogin"]] = $player["nickname"];
		}
		
		$totalPoints = 0;
		$mapCount = 0;
		
		foreach ($mapList as $id=>$map)
		{
			$totalMapScore = 0;
			$sql = "SELECT playerlogin, score FROM records WHERE challengeid='".$map["UId"]."' ORDER BY score DESC";
			$mysql = mysqli_query($this->db, $sql);
			
			$maps[$mapCount] = Array();
			$maps[$mapCount]['Name']		= $map["Name"];
			$maps[$mapCount]['Top1Name']	= '';
			$maps[$mapCount]['Top1Score']	= 0;
			$maps[$mapCount]['YourRank']	= '';
			$maps[$mapCount]['YourScore']	= '';
			$maps[$mapCount]['RecordsNb']= 0;
			
			$rank = 1;
			
			while ($record = $mysql->fetch_assoc())
			{
				if ($rank == 1)
				{
					$maps[$mapCount]['Top1Name'] = $playersNickName[$record["playerlogin"]];
					$maps[$mapCount]['Top1Score'] = $record["score"];
				}
				if ($record["playerlogin"] == $login)
				{
					$maps[$mapCount]['YourRank'] = $rank;
					$maps[$mapCount]['YourScore'] = $record["score"];
				}				
				$totalPoints += $record["score"];
				$maps[$mapCount]['RecordsNb']++;
				$rank++;
			}
			$mapCount++;
			
		}

		arsort($players);
				
		#### UI
		// Window title
		$windowTitle = '$w$fffCup Ranking';
		
		$cellHeight = 5;
		$tableWidth = 190;
		
		$ml = '<?xml version="1.0" encoding="UTF-8" ?>';
		$ml .= '<manialink id="StuntersCupRankingWindow" version="1">';
		$ml .= '<timeout>0</timeout>'.$zr;
		
		$ml .= '<frame posn="-'.($tableWidth/2).' '.(($cellHeight*$this->MapsByPage+3*$cellHeight)/2).' 25">';
		
		$ml .= '<frame posn="0 0">';
			$ml .= '<quad posn="0 0 -2" sizen="'.$tableWidth.' '.($cellHeight).'" bgcolor="000d" />'.PHP_EOL;
			$ml .= '<label posn="4 '.(-$cellHeight/2).'" sizen="5" text="" halign="right" valign="center2" scale="0.8" />';
			$ml .= '<label posn="8 '.(-$cellHeight/2).'" sizen="40" text="$iMap" valign="center2" scale="0.8" />';
			$ml .= '<label posn="60 '.(-$cellHeight/2).'" sizen="40" text="$iTop 1" valign="center2" scale="0.8" />';
			$ml .= '<label posn="92 '.(-$cellHeight/2).'" sizen="40" text="$iScore" valign="center2" scale="0.8" />';
			$ml .= '<label posn="110 '.(-$cellHeight/2).'" sizen="40" text="$iYour Score/Diff." valign="center2" scale="0.8" />';
			$ml .= '<label posn="145 '.(-$cellHeight/2).'" sizen="40" text="$iYour Rank" valign="center2" scale="0.8" />';
			$ml .= '<label posn="170 '.(-$cellHeight/2).'" sizen="40" text="$iPlanets" valign="center2" scale="0.8" />';
		$ml .= '</frame>';
		
		
		$rank = 1;
		$mapNb = 1;
		
		for ($i=($selectedPage-1)*$this->MapsByPage; $i < $selectedPage*$this->MapsByPage; $i++)
		{
			$ml .= '<frame posn="0 '.(-$rank*$cellHeight).'">';
			
			if ($i%2==0) $ml .= '<quad posn="0 0 -2" sizen="'.$tableWidth.' '.($cellHeight).'" bgcolor="0005" />'.PHP_EOL;
			else  $ml .= '<quad posn="0 0 -2" sizen="'.$tableWidth.' '.($cellHeight).'" bgcolor="0001" />'.PHP_EOL;
			
			if (array_key_exists($i, $maps)) $ml .= '<label posn="5 0" sizen="6" scale="0.8" text="$bb0'.($i +1).'" halign="right" />';
			$ml .= '<label posn="6 0" sizen="49" text="'.htmlspecialchars($maps[$i]['Name']).'" scale="0.9" />';
			$ml .= '<label posn="55 0" sizen="39" text="'.htmlspecialchars($maps[$i]['Top1Name']).'" scale="0.9" />';
			$ml .= '<label posn="102 0" sizen="20" text="'.htmlspecialchars($maps[$i]['Top1Score']).'"  halign="right" />';

			if ($maps[$i]['YourScore']) $ml .= '<label posn="122 0" sizen="20" text="$0d0$i'.$maps[$i]['YourScore'].'"halign="right" />';
			if ($maps[$i]['YourScore']) $ml .= '<label posn="122 0" sizen="20" text="$z$fff$i - '.($maps[$i]['Top1Score']-$maps[$i]['YourScore']).'"halign="left" />';

			if ($maps[$i]['YourRank']) $ml .= '<label posn="152 0" sizen="20" text="$0d0$o'.$maps[$i]['YourRank'].'" halign="right" />';
            if ($maps[$i]['YourRank']) $ml .= '<label posn="152 0" sizen="20" text="$z$fff$o / '.$maps[$i]['RecordsNb'].'" halign="left" />';
			
			if (($this->Planets*$maps[$i]['YourScore'])/$totalPoints) $ml .= '<label posn="170 0" sizen="20" text="$i$cc3'.round(($this->Planets*$maps[$i]['YourScore'])/$totalPoints, 1).'" />';

			$ml .= '</frame>';

			$rank++;
		}
		
		
		$ml .= '<quad posn="0 0 -1" sizen="'.$tableWidth.' '.($rank*$cellHeight).'" style="BgsPlayerCard" substyle="BgCard" />'.PHP_EOL;
		
		// Pages
		$ml .= '<quad posn="0 '.(-$rank*$cellHeight).' -3" sizen="'.$tableWidth.' 5" bgcolor="000d" />'.PHP_EOL;
		
		for($i=1; $i < (count($maps)/$this->MapsByPage+1); $i++ )
		{
			if ($i == $selectedPage) 
			{
				$ml .= '<label posn="'.(5+$i*6).' '.(-$rank*$cellHeight).' 0" sizen="5" text="$o'.$i.'" halign="center" />';
				$ml .= '<quad posn="'.(5+$i*6).' '.(-$rank*$cellHeight).' -1" sizen="5 5" bgcolor="050c" halign="center" />';
			}
			else
			{
				$ml .= '<label posn="'.(5+$i*6).' '.(-$rank*$cellHeight).' 0" sizen="5" text="'.$i.'" halign="center" />';
				$ml .= '<quad posn="'.(5+$i*6).' '.(-$rank*$cellHeight).' -1" sizen="5 5" bgcolor="444c" bgcolorfocus="999c" halign="center" action="'.$this->mlids[$i].'" />';
			}
			
			
		}
		
		$ml .= '<label posn="'.($tableWidth/2).' '.(-$rank*$cellHeight).' 0" sizen="25" text="'.count($maps).' maps" halign="center" />';
		
		
		// Quit
		$ml .= '<quad posn="'.$tableWidth.' '.(-$rank*$cellHeight).' 2" sizen="'.($tableWidth/4).' 5" halign="right" bgcolor="600a" bgcolorfocus="800c" action="'.$this->mlids[0].'" />'.PHP_EOL;
		$ml .= '<label posn="'.($tableWidth-($tableWidth/8)).' '.(-$rank*$cellHeight).' 3" sizen="25" text="Close" halign="center" translate="1" />';
		
		$ml .= '</frame>';
		$ml .='</manialink>';
		
		$this->instance()->client->query('SendDisplayManialinkPageToLogin', $login, $ml, 0, False);
	}
	
	
	public function SendRankWindow($login)
	{	
		global $settings;
		
		#### Calculate server ranking
		// Get map List
		$this->instance()->client->query("GetMapList", 500, 0);
		$mapList = $this->instance()->client->getResponse();
		
		$players = Array();
		$playersNickName = Array();
		
		$maps = Array();
		
		// Get database player list
		$sql = "SELECT playerlogin, nickname FROM players";
		$mysql = mysqli_query($this->db, $sql);
		
		// Initialize players arrays
		while ($player = $mysql->fetch_assoc())
		{
			$players[$player["playerlogin"]] = 0;
			$playersNickName[$player["playerlogin"]] = $player["nickname"];
		}
		
		$totalPoints = 0;
		
		foreach ($mapList as $id=>$map)
		{
			$totalMapScore = 0;
			$sql = "SELECT playerlogin, score FROM records WHERE challengeid='".$map["UId"]."' ORDER BY score DESC";
			$mysql = mysqli_query($this->db, $sql);
			
			while ($record = $mysql->fetch_assoc())
			{			
				$players[$record["playerlogin"]] += $record["score"];
				$totalPoints += $record["score"];
			}			
		}

		arsort($players);
		
		$playerRank = 1;
		foreach($players as $loginid=>$score)
		{
			if ($loginid == $login) break;
			$playerRank++;
		}
		
		$ml = '<?xml version="1.0" encoding="UTF-8" ?>';
		$ml .= '<manialink id="StuntersRankWindow" version="1">';
		$ml .= '<timeout>0</timeout>'.$zr;
		$ml .= '<frame posn="118.25 -55.5 4">';

		// General window
		$ml .= '<quad posn="0 0 -1" sizen="40 12" bgcolor="222A" />'.PHP_EOL;
		$ml .= '<quad posn="0 0 0" sizen="40 4" bgcolor="222a" />';
		$ml .= '<label posn="20 -2 1" text="$o$iCup Rankings" valign="center2" halign="center" style="TextRaceStaticSmall" scale="1"  />';
		$ml .= '<quad posn="1.5 -1.5 2" sizen="4 4" style="Icons128x128_1" substyle="Rankings" halign="center" valign="center2"  />';
		
		
		// Rank
		$ml .= '<quad posn="3 -6 1" sizen="4 4" style="Icons64x64_1" substyle="OfficialRace" halign="center" valign="center2"  />';
		$ml .= '<label posn="19 -6 1" text="$o$0c0'.$playerRank.'$fff$o / '.count($players).'" valign="center2" style="TextRaceStaticSmall" scale="1.1" halign="right" />';
		
		// Points
		$ml .= '<quad posn="0 -6 -1" sizen="20 4" style="BgsPlayerCard" substyle="BgCard" valign="center2" />'.PHP_EOL;
		
		$ml .= '<quad posn="23 -6 1" sizen="4 4" style="BgRaceScore2" substyle="Points" halign="center" valign="center2"  />';
		$ml .= '<label posn="39 -6 1" text="'.number_format($players[$login], 0, '.', ' ').'" valign="center2" style="TextRaceStaticSmall" scale="1.1" halign="right" />';
		
		// Planets
		$ml .= '<quad posn="23 -9.5 1" sizen="3.5 3.5" style="ManiaPlanetLogos" substyle="IconPlanetsSmall" halign="center" valign="center2"  />';
		$ml .= '<label posn="39 -10 1" text="$cc3'.number_format(round(($this->Planets*$players[$login])/$totalPoints, 2), 2, '.', ' ').'" valign="center2" halign="right" style="TextRaceStaticSmall" scale="1.1" />';
		
		// Maps
		$ml .= '<quad posn="0 -10 1" sizen="20 4" valign="center2"  bgcolor="055a" bgcolorfocus="770a"  action="'.$this->mlids[50].'" />'.PHP_EOL;
		$ml .= '<label posn="10 -10 2" text="Maps list" valign="center2" halign="center" style="TextRaceStaticSmall" scale="1.1"  />';
		
		$ml .= '</frame>';
		
		$ml .='</manialink>';
		
		$this->instance()->client->query('SendDisplayManialinkPageToLogin', $login, $ml, 0, False);
		
	}
	
	public function onManialinkPageAnswer($args)
	{
		if($args[2] == $this->mlids[50]) // Open maps details
		{
			$this->SendMapsTable($args[1]);
		}
		else if($args[2] == $this->mlids[49]) // Open server ranking
		{
			// $this->SendMapsTable($args[1]);
		}
		else if($args[2] == $this->mlids[0]) // Close ML
		{
			$this->instance()->client->query('SendDisplayManialinkPageToLogin', $args[1], '<?xml version="1.0" encoding="UTF-8" ?>
			<manialink id="StuntersCupRankingWindow"></manialink>', 1, false);
		}
		
		else if ($args[2] >= $this->mlids[1] && $args[2] <= $this->mlids[48])
		{
			foreach($this->mlids as $id=>$value)
			{
				if ($value == $args[2])
				{
					$this->SendMapsTable($args[1], $id);
					break;
				}
			}
		}
	}

	// Convert string to bool
	function stringToBool($string)
	{
		if (strtolower($string)=="false" || $string=="0" || $string=="") return false;
		return true;
	}	
}
?>
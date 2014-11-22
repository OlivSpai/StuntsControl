<?php 
//* Stunters Title Pack > Local Records Plugin for FoxControl
//* Version		0.5
//* Coded by	Spaï
//* UI Designer	Pastis
//* Copyright	http://stunters.org
//
// v0.2 - 2012.11.13
// Added : {mapname} option in header text
// max_records_to_display work correctly
//
// v0.3 - 2012.12.14
// Added : Scale in Rank, NickName and Score xml settings
// Added : Rank in chat message
// Fixed : NickName in local records table is now corectly updated
//
// V0.4 - 2012.12.15
// Added : Server ranking based on cumulative points.
//
// V0.5 - 2013.01.19
// Fixed : Special characters on maps and nicknames

// V0.6 - 2013.09.21
// Easy config file
// Window is now 40. width like Stunters
// Window is movable

# TODO
# Average Ranking
# Page selection on server ranking manialink

class plugin_stunters_localrecords extends FoxControlPlugin {

	public $config;
	public $MapUId;
	public $MapName;
	
	public function GetMapInfos()
	{
		$this->instance()->client->query('GetCurrentMapInfo');
		$mapInfo = $this->instance()->client->getResponse();
		$this->MapUId = $mapInfo['UId'];
		$this->MapName = $mapInfo['Name'];	
	}
	
	public function onStartUp()
	{	
		$this->name = 'Stunters Server Records';
		$this->author = 'Spaï';
		$this->version = '0.6';
	
		$this->registerCommand('srank', 'Shows server average rank', false);
		$this->registerCommand('prank', 'Shows server points rank', false);
		$this->registerCommand('lt', 'Limited Time administration', true);
	
		//Register ML IDs
		$this->registerMLIds(10);

		$this->MapUId = "";
		$this->MapName = "";
	
		//Load config file (plugin.stunters_localrecords_momo.config.xml)
		$this->config = $this->loadConfig();
		
		// create needed columns in 'records' database
		mysqli_query($this->db, "ALTER TABLE `records` ADD score INT NOT NULL");
		mysqli_query($this->db, "ALTER TABLE `records` ADD figures VARCHAR(65536) NOT NULL");
		mysqli_query($this->db, "ALTER TABLE `records` ADD distance FLOAT NOT NULL");
		mysqli_query($this->db, "ALTER TABLE `records` ADD time INT UNSIGNED NOT NULL");		
		mysqli_query($this->db, "ALTER TABLE `records` ADD `id` INT UNSIGNED NOT NULL AUTO_INCREMENT FIRST , ADD PRIMARY KEY (`id`) , ADD UNIQUE (`id`)");
		mysqli_query($this->db, "ALTER TABLE `records` ADD INDEX `challengeid_id` (`challengeid`)");
		mysqli_query($this->db, "ALTER TABLE `records` ADD INDEX `playerlogin_id` (`playerlogin`)");
		
		//Getting current Map Name & UId
		$this->GetMapInfos();

		// Send local records table when FoxControl start
		$this->SendLocalRecordsTable();
		
		// create needed columns in 'limited_time' database
		$tbl_players = "
			CREATE TABLE IF NOT EXISTS `limited_time` (
			`playerlogin` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
			`timeplayed` INT NOT NULL DEFAULT 0,
			INDEX (`playerlogin`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ";
		mysqli_query($this->db, $tbl_players);

	}
	
	public function onModeScriptCallback($args)
	{
		// Callback name = $args[0]
		// Callback string value = $args[1]

		if ($args[0] == "Stunters.Player.Finish") $this->StuntersPlayerFinish(json_decode($args[1]));
	}
	
	public function onManialinkPageAnswer($args)
	{
	
		print_r($args);
	
	}
	public function onBeginMap($args)
	{
		//Getting current Map Name & UId
		$this->GetMapInfos();
		
		// Send local records table when map begin
		$this->SendLocalRecordsTable();
		
		// sends the remaining time to complete 
		if ($this->stringToBool($this->config->LT_actif))
		{
			//Get PlayerList
			$this->instance()->client->query('GetPlayerList', 200, 0);
			$playerlist = $this->instance()->client->getResponse();
			// $playerlist = getPlayersList();
			
			foreach($playerlist as $key => $value)
			{
				$login = $playerlist[$key]['Login'];
				$timeplayed = $this->time_played($login);
				if ($timeplayed < $this->config->LT_time)
				{
					$fromArray = array('{time_left}');
					$toArray = array($this->time_elapsed($this->config->LT_time - $timeplayed));
					$message = str_replace($fromArray, $toArray, $this->config->LT_entry);
				} // end if
				else
				{
					$fromArray = array('{time_left}');
					$toArray = array($this->config->LT_time);
					$message = str_replace($fromArray, $toArray, $this->config->LT_lateentry);
				}
				// Send message
				$this->instance()->client->query('ChatSendServerMessageToLogin', $message, $login);
			} // end foreach $playerlist	
			
		} // end if LT_actif
		
	} // end onBeginMap
	
	public function onPlayerConnect($args)
	{
		// limited_time
		if ($this->config->LT_actif == 'true')
		{
			$login = $args["Login"];
			//Insert Player into the database or update it's data
			$sql = mysqli_query($this->db, "SELECT playerlogin,timeplayed FROM `limited_time` WHERE playerlogin = '".$login."'");
			if(!$row = $sql->fetch_object()){
				$timeplayed = 0;
				$sql = mysqli_query($this->db, "INSERT INTO `limited_time` (playerlogin, timeplayed) VALUES ('".mysqli_real_escape_string($this->db, $login)."', $timeplayed)");
			}
			else
			{
				$timeplayed = $row->timeplayed;
			}
			// Format message
			if ($timeplayed >= $this->config->LT_time)
			{
				$fromArray = array('{time_left}');
				$toArray = array($this->config->LT_time);
				$message = str_replace($fromArray, $toArray, $this->config->LT_lateentry);
				//$message = $this->config->LT_lateentry;
				//$message = "trop tard";
			}
			else
			{
				$fromArray = array('{time_left}');
				$toArray = array($this->time_elapsed($this->config->LT_time - $timeplayed));
				$message = str_replace($fromArray, $toArray, $this->config->LT_entry);
			}
			
			// Send message
			$this->instance()->client->query('ChatSendServerMessageToLogin', $message, $login);
		} // endif LT_actif

		// Send local records table when a player connect
		$this->SendLocalRecordsTable();
	}
	
	public function onCommand($args)
	{
		$login 		= $args[1];
		$command 	= $args[2];
		
		if ($command == "srank")
		{			
			$this->SendServerRankTableTest($login);
		}
		elseif ($command == "prank")
		{			
			$this->SendPointsRankTable($login);
		}
		elseif ($command == "lt")
		{			
			$this->lt_admin($login,$command,$args[3]);
		}
	}
	
	
	// lt admin
	function lt_admin($login,$command,$args)
	{
		$rights = $this->getRights($login);
		$rights_level = $rights[0];

		switch ($args[0])
		{
			case 'help':
				$this->LT_help($login);
				break;

			case 'gettime':
				$this->display_time($login,$args);
				break;
			case 'reinit':
				// commands are only for SuperAdmin
				if ($rights_level == 3)
				{
					if (isset($args[1]))
					{
						switch ($args[1])
						{
							case 'recs':
								$this->reinit_recs($login);
								$this->SendLocalRecordsTable();
								break;
							case 'time':
								$this->reinit_time($login);
								$this->SendLocalRecordsTable();
								break;
							case 'all':
								$this->reinit_recs($login);
								$this->reinit_time($login);
								$this->SendLocalRecordsTable();
								break;
						} // endswitch $args[1]
					} // endif isset $args[1]
					else
					{
						// Format message
						$fromArray = array('{now }');
						$toArray = array('');
						$message = str_replace($fromArray, $toArray, $this->config->LT_help->reinit);
				
						// Send message
						$this->instance()->client->query('ChatSendServerMessageToLogin', $message, $login);
					}
				} // endif $rights_level
				else
				{
					$this->display_admins_only($login);					
				}
				break;
			case 'on':
				// commands are only for SuperAdmin
				if ($rights_level == 3)
				{
					$this->config->LT_actif = 'true';
					// Format message
					$fromArray = array('{nickname}');
					$toArray = array($this->loginToNickname($login));
					$message = str_replace($fromArray, $toArray, $this->config->LT_on);
					
					// Send message
					$this->instance()->client->query('ChatSendServerMessage', $message);		
				}
				else
				{
					$this->display_admins_only($login);					
				}
				break;
			case 'off':
				// commands are only for SuperAdmin
				if ($rights_level == 3)
				{
					$this->config->LT_actif = 'false';
					// Format message
					$fromArray = array('{nickname}');
					$toArray = array($this->loginToNickname($login));
					$message = str_replace($fromArray, $toArray, $this->config->LT_off);
					
					// Send message
					$this->instance()->client->query('ChatSendServerMessage', $message);
				}
				else
				{
					$this->display_admins_only($login);					
				}
				break;
			case 'get':
				// Format message
				$fromArray = array('{now }','{time1}','{time2}');
				$toArray = array('',$this->time_elapsed($this->config->LT_time),$this->config->LT_time);
				$message = str_replace($fromArray, $toArray, $this->config->LT_print_time);
				
				// Send message
				$this->instance()->client->query('ChatSendServerMessageToLogin', "$message", $login);
				
				$this->LT_display_active($login);
				break;
			case 'set':
				// commands are only for SuperAdmin
				if ($rights_level == 3)
				{
					if (isset($args[1]))
					{
						$this->config->LT_time = $args[1];
						// Format message
						$fromArray = array('{now }','{time1}','{time2}');
						$toArray = array('now ',$this->time_elapsed($this->config->LT_time),$this->config->LT_time);
						$message = str_replace($fromArray, $toArray, $this->config->LT_print_time);
						
						// Send message
						$this->instance()->client->query('ChatSendServerMessage', $message);
					}
				}
				else
				{
					$this->display_admins_only($login);					
				}
				break;
			case 'addtime':
				// commands are only for SuperAdmin
				if ($rights_level == 3)
				{
					if (isset($args[1]) && isset($args[2]))
					{
						$this->add_time_player($args[1],$args[2],$login);
					}
					else
					{
						$this->error_add($login);
					}
				}
				else
				{
					$this->display_admins_only($login);					
				}
				break;
			case 'save':
				if ($rights_level == 3)
				{
					$this->LT_save($login);
				}
				else
				{
					$this->display_admins_only($login);					
				}
				
				break;
			default:
				$this->display_time($login,$args);
				$message = $this->config->LT_alone;
				// Send message
				$this->instance()->client->query('ChatSendServerMessageToLogin', "$message", $login);
				
				$this->LT_display_active($login);

		} //endswitch $args[0]
	} // end lt_admin
	
	// display just for admins
	function display_admins_only($login)
	{
		// Format message
		//$fromArray = array('{nickname}');
		//$toArray = array($login);
		//$message = str_replace($fromArray, $toArray, $this->config->LT_admin_only);
		$message = $this->config->LT_admin_only;
		
		// Send message
		$this->instance()->client->query('ChatSendServerMessageToLogin', "$message", $login);
	} //end display_admins_only
	
	// display the time played by a player
	function display_time($login,$args)
	{
		$login2 = $login;
		if (isset($args[1]))
		{
			$login2 = $args[1];
		}
		$nick = $this->loginToNickname($login2);
		$timeplayed = $this->time_played($login2);
		if ($timeplayed == 0)
		{
			// Format message
			$fromArray = array('{nickname}');
			$toArray = array($nick);
			$message = str_replace($fromArray, $toArray, $this->config->LT_never_played);
			
			// Send message
			$this->instance()->client->query('ChatSendServerMessageToLogin', $message, $login);
		}
		else
		{
			// Format message
			$fromArray = array('{nickname}','{timeplayed}','{who}','{timeleft}');
			if ($login == $login2)
			{
				$who = 'You';
			}
			else
			{
				$who = 'He';
			}
			if ($timeplayed >= $this->config->LT_time)
			{
				$timeleft = 'no time';
			}
			else
			{
				$timeleft = $this->time_elapsed($this->config->LT_time - $timeplayed);
			}
			$toArray = array($nick,$this->time_elapsed($timeplayed),$who,$timeleft);
			$message = str_replace($fromArray, $toArray, $this->config->LT_time_played);
			
			// Send message
			$this->instance()->client->query('ChatSendServerMessageToLogin', $message, $login);
			}
	} // end display_time
	
	// reset the recs table
	function reinit_recs($login)
	{
		$sql = mysqli_query($this->db, "TRUNCATE TABLE records");			
		// Format message
		$fromArray = array('{nickname}');
		$toArray = array($this->loginToNickname($login));
		$message = str_replace($fromArray, $toArray, $this->config->LT_reset_recs);
		
		// Send message
		$this->instance()->client->query('ChatSendServerMessage', $message);
	} // end reinit_recs
	
	// reset the limited_time table
	function reinit_time($login)
	{
		//$sql = mysqli_query($this->db, "UPDATE `limited_time` SET timeplayed=0");			
		$sql = mysqli_query($this->db, "TRUNCATE TABLE limited_time");			
		
		// Format message
		$fromArray = array('{nickname}');
		$toArray = array($this->loginToNickname($login));
		$message = str_replace($fromArray, $toArray, $this->config->LT_reset_time);
		
		// Send message
		$this->instance()->client->query('ChatSendServerMessage', $message);
	} // end reinit_time
	
	// get time played by a player
	public function time_played($login)
	{
		$result = mysqli_query($this->db, "SELECT playerlogin, timeplayed FROM limited_time WHERE playerlogin = '".$login."'");
		if ($result->num_rows == 0)
		{
		// player not in the database
			mysqli_query($this->db, "INSERT INTO `limited_time` SET playerlogin = '".$login."', timeplayed = 0 ");		
			return 0;
		} 
		else
		{
			while ($obj = $result->fetch_object())
			{
				return $obj->timeplayed;
			}
		}
	} // end time_played

	// add time to a player
	public function add_time_player($login,$time,$admin_login)
	{
		if (is_numeric($time))
		{
			$result = mysqli_query($this->db, "SELECT playerlogin, timeplayed FROM limited_time WHERE playerlogin = '".$login."'");
			if ($result->num_rows == 0)
			{
				$this->error_add($admin_login);
			} 
			else
			{
				while ($obj = $result->fetch_object())
				{
					$newtime = $obj->timeplayed;
				}
				$newtime -= $time;
				if ($newtime < 0)
				{
					$newtime = 0;
				}
				mysqli_query($this->db, "UPDATE limited_time SET timeplayed=".$newtime." WHERE playerlogin = '".$login."'");
				// Format message
				$fromArray = array('{nickname}','{time}');
				$toArray = array($this->loginToNickname($login),$this->time_elapsed($this->config->LT_time - $newtime));
				$message = str_replace($fromArray, $toArray, $this->config->LT_newtime_player);
				
				// Send message
				$this->instance()->client->query('ChatSendServerMessageToLogin', $message, $admin_login);
					
			} // endif num_rows == 0
		}
		else
		{
			$this->error_add($admin_login);
		} // endif is_numeric($time)
	} // end time_played

	// erroor 
	function error_add($login)
	{
		// Format message
		//$fromArray = array('{nickname}');
		//$toArray = array($this->loginToNickname($login));
		//$message = str_replace($fromArray, $toArray, $this->config->LT_error_add);
		$message = $this->config->LT_error_add;
		
		// Send message
		$this->instance()->client->query('ChatSendServerMessageToLogin', "$message", $login);
	} // end reinit_time
	
	function time_elapsed($secs)
	{
		$bit = array(
			' year'        => $secs / 31556926 % 12,
			' week'        => $secs / 604800 % 52,
			' day'        => $secs / 86400 % 7,
			' hour'        => $secs / 3600 % 24,
			' minute'    => $secs / 60 % 60,
			' second'    => $secs % 60
			);
		   
		foreach($bit as $k => $v){
			if($v > 1) $ret[] = $v . $k . 's';
			if($v == 1) $ret[] = $v . $k;
			}
		if ($secs == 0)
		{
			$ret[] = ' 0 second';
		}
		
		//array_splice($ret, count($ret)-1, 0, 'and');
		//$ret[] = 'ago.';
		return join(' ', $ret);
	} // end time_elapsed

	// display if the plugin is active
	function LT_display_active($login)
	{
		if ($this->stringToBool($this->config->LT_actif))
		{
			//$is_active = 'active';
			$is_active = 'ON';
		}
		else
		{
			//$is_active = 'inactive';
			$is_active = 'OFF';
		}
		// Format message
		$fromArray = array('{active}');
		$toArray = array($is_active);
		$message = str_replace($fromArray, $toArray, $this->config->LT_is_active);
		
		// Send message
		$this->instance()->client->query('ChatSendServerMessageToLogin', "$message", $login);
	
	} // end LT_display_active
	
	// Display the limited time help
	function LT_help($login)
	{
		$help = get_object_vars($this->config->LT_help);

		#### UI
		// Create window
		$window = $this->window;
		$window->init();
		
		// Window title
		$windowTitle = '$w$fffLimited Time Help';
		$window->title($windowTitle);
		$window->close(true);
		
		// Window settings
		$window->displayAsTable(true);
		// $window->fontSize(1.5);
		$window->size(70, '');
		$window->posY('37');
		//$window->target('onPages', $this);
		$window->target('onButtonPressed', $this);
	
		// Window header
		$window->content('<td width="15">Command</td><td width="2"></td><td width="50">Description</td>');
		
		$rank = 1;
		foreach ($help as $key => $value)
		{
			$windowContent = "";
			$windowContent .= '<td width="15">'.$key.'</td>';
			$windowContent .= '<td width="50">'.$value.'</td>';
			$window->content($windowContent);
			
			$rank++;
			if ($rank > 25) break;			
		}
		
		// Close button
		//$window->addButton('', '15.5', false);
		$window->addButton('Close', 10, true);
		//$window->addButton('', '15.5', false);
		
		$window->show($login);
	} // end LT_help
	
	// save the configuration
	function LT_save($login)
	{
		$classname = str_replace('plugin_', 'plugin.', $this->classname);
		$filename = $classname.'.config.xml';
		
		if(file_exists('plugins/config/'.$filename)) 
		{
			$contenu = file_get_contents('plugins/config/'.$filename);
			
			$pattern = '#(.*)<LT_actif>(\D+)</LT_actif>(.*)#';
			$remplacement = '${1}<LT_actif>'.$this->boolToString($this->config->LT_actif).'</LT_actif>${3}';
			$contenu = preg_replace($pattern, $remplacement, $contenu);

			$pattern = '#(.*)<LT_time>(\d+)</LT_time>(.*)#';
			$remplacement = '${1}<LT_time>'.$this->config->LT_time.'</LT_time>${3}';
			$contenu = preg_replace($pattern, $remplacement, $contenu);

			file_put_contents('plugins/config/'.$filename,$contenu);
		
		}
		$message = $this->config->LT_config_saved;

		// Send message
		$this->instance()->client->query('ChatSendServerMessageToLogin', "$message", $login);
	
	} // end LT_save
	
	// update the connexion time of the players
	public function onEverySecond()
	{
		if ($this->stringToBool($this->config->LT_actif))
		{
			//Get PlayerList
			$this->instance()->client->query('GetPlayerList', 200, 0);
			$playerlist = $this->instance()->client->getResponse();
			// $playerlist = getPlayersList();
			
			foreach($playerlist as $key => $value)
			{
				$login = $playerlist[$key]['Login'];
				if ($playerlist[$key]['SpectatorStatus'] == 0)
				{
					if ($this->time_played($login) < $this->config->LT_time)
					{
						$sql = mysqli_query($this->db, "UPDATE limited_time SET timeplayed=timeplayed + 1 WHERE playerlogin = '".$login."'");		
					} // end if
				}
			} // end foreach $playerlist	
			
		} // endif LT_actif
	} // end onEverySecond
	
	// Search rank of a player on a map in database
	public function getPlayerRank($player, $mapuid)
	{
		$sqlMap = mysqli_query($this->db, 'SELECT * FROM maps WHERE UId="'.$mapuid.'" LIMIT 1');
		
		if (!$sqlMap->num_rows) return -1;
		
		$resMap = $sqlMap->fetch_object();
		$maptype = $resMap->MapType;
		
		if ($maptype == 'Stunters') $sql = 'SELECT * FROM records WHERE challengeid="'.$mapuid.'" ORDER BY score DESC';
		else  $sql = 'SELECT * FROM records WHERE challengeid="'.$mapuid.'" ORDER BY time ASC';
		
		$reqScore = mysqli_query($this->db, $sql);
		
		$rank = 1;
		while ($resScore = $reqScore->fetch_assoc())
		{
			if ($resScore['playerlogin'] == $player) return $rank;
			$rank++;
		}
		
		return -1;
	}
	
	public function StuntersPlayerFinish($data)
	{	
		$affiche = 0;
		
		print_r($data);
		
		// Search if the player have already a score
		$sql = 'SELECT * FROM records WHERE playerlogin="'.$data->Login.'" AND challengeid="'.$this->MapUId.'"';
		$reqScore = mysqli_query($this->db, $sql);
		$nbScoresFound = $reqScore->num_rows;
		
		// If no score found, create it
		if ($nbScoresFound == 0)
		{
			// Get the player nickname 
			$this->instance()->client->query('GetPlayerInfo', $data->Login);
			$response = $this->instance()->client->getResponse();
			$nickname = $response['NickName'];
			
			// limited_time
			if ((($this->stringToBool($this->config->LT_actif)) && ($this->time_played($data->Login) < $this->config->LT_time))
				|| !$this->stringToBool($this->config->LT_actif))
			{
				// Create record in database
				$sql = 'INSERT INTO records (challengeid, playerlogin, nickname, score, time, figures, date, distance) VALUES ("'.$this->MapUId.'" ,"'.$data->Login.'","'.mysqli_escape_string($this->db,$nickname).'", '.$data->Score.', '.$data->Time.',"'.mysqli_escape_string($this->db,json_encode($data->Figures)).'" ,NOW(), '.$data->Distance.')';
				$reqInsert = mysqli_query($this->db, $sql);
				if ($this->db->affected_rows != 1)
				{
					trigger_error('{STUNTERS][Error] Could not insert record ! (' . mysql_error() . ')' . CRLF . 'sql = ' . $reqInsert, E_USER_WARNING);
				}
			
				// Send chat message only if send_chat_messages=true in config file
				if ($this->stringToBool($this->config->send_chat_messages))
				{
					// Get player rank
					$rank = $this->getPlayerRank($data->Login, $this->MapUId);
					
					// Format message
					$fromArray = array('{score}', '{mapname}', '{rank}');
					$toArray = array($data->Score, $this->MapName, $rank);
					$message = str_replace($fromArray, $toArray, $this->config->first_record);
					
					// Send message
					$this->instance()->client->query('ChatSendServerMessageToLogin', $message, $data->Login);
				}
			} // endif time_played
			else
			{
				$message = $this->config->LT_toolate;
					$fromArray = array('{score}');
					$toArray = array($data->Score);
					$message = str_replace($fromArray, $toArray, $this->config->LT_toolate);
				// Send message
				$this->instance()->client->query('ChatSendServerMessageToLogin', $message, $data->Login);
			}

			// Send local records table
			$this->SendLocalRecordsTable();
		}
		else // Score found, update only if (player score > score found in database)
		{			
			$resScore = $reqScore->fetch_assoc();
			$oldScore = $resScore['score'];
			if ( ($data->MapType == 'Stunters' && ($data->Score > $resScore['score']) ) || ( ($data->MapType == 'StuntersRacers' || $data->MapType == 'StuntersReachScore') && ($data->Time < $resScore['time']) ))
			{
				if ((($this->stringToBool($this->config->LT_actif)) && ($this->time_played($data->Login) < $this->config->LT_time))
					|| !$this->stringToBool($this->config->LT_actif))
				{
					// Update record in database
					$sql = 'UPDATE records SET score='.$data->Score.', time='.$data->Time.', distance='.$data->Distance.',figures="'.mysqli_escape_string($this->db,json_encode($data->Figures)).'",date=NOW() WHERE playerlogin="'.$data->Login.'" AND challengeid="'.$this->MapUId.'"';
					$reqUpdate = mysqli_query($this->db, $sql);
					
					// Send chat message only if send_chat_messages=true in config file
					if ($this->stringToBool($this->config->send_chat_messages))
					{
						// Get player rank
						$rank = $this->getPlayerRank($data->Login, $this->MapUId);	
						
						if ($data->MapType == 'Stunters') $scoreEvolution = $data->Score - $resScore['score'];
						else $scoreEvolution = $resScore['time'] - $data->Time;
						
						// Format message
						$fromArray = array('{score}', '{score_evolution}', '{mapname}', '{rank}');
						$toArray = array($data->Score, $scoreEvolution, $this->MapName, $rank);
						$message = str_replace($fromArray, $toArray, $this->config->new_record);
						
						// Send message
						$this->instance()->client->query('ChatSendServerMessageToLogin', $message, $data->Login);
					}
				}// endif time_played
				else
				{
					$message = $this->config->LT_toolate;
					$fromArray = array('{score}');
					$toArray = array($data->Score);
					$message = str_replace($fromArray, $toArray, $this->config->LT_toolate);
					// Send message
					$this->instance()->client->query('ChatSendServerMessageToLogin', $message, $data->Login);
					
					$affiche = 1;
				}
		
			} 		
			
			// Send local records table
			$this->SendLocalRecordsTable();
		}  // end Score found
		
		if ($this->stringToBool($this->config->LT_actif))
		{
				$timeplayed = $this->time_played($data->Login);
				if ($timeplayed < $this->config->LT_time)
				{
					$fromArray = array('{time_left}');
					$toArray = array($this->time_elapsed($this->config->LT_time - $timeplayed));
					$message = str_replace($fromArray, $toArray, $this->config->LT_entry);
				} // end if
				else
				{
					$fromArray = array('{time_left}');
					$toArray = array($this->config->LT_time);
					$message = str_replace($fromArray, $toArray, $this->config->LT_lateentry);
				}
				// Send message
				$this->instance()->client->query('ChatSendServerMessageToLogin', $message, $data->Login);
			if ($this->time_played($data->Login) < $this->config->LT_time)
			{
			
			}
			elseif ($affiche)
			{
				/*
				$message = $this->config->LT_toolate;
				$fromArray = array('{score}');
				$toArray = array($data->Score);
				$message = str_replace($fromArray, $toArray, $this->config->LT_toolate);
				// Send message
				$this->instance()->client->query('ChatSendServerMessageToLogin', $message, $data->Login);
				*/
			}
		}

	} // StuntersPlayerFinish
	
	
	
	public function SendServerRankTableTest($login)
	{	
		global $settings;
		
		#### Calculate server ranking
		// Get map List
		$this->instance()->client->query("GetMapList", 500, 0);
		$mapList = $this->instance()->client->getResponse();
				
		$players = Array();
		$playersNickName = Array();
		
		// Get database player list
		$sql = "SELECT playerlogin, nickname FROM players";
		$mysql = mysqli_query($this->db, $sql);
		
		// Initialize players arrays
		while ($player = $mysql->fetch_assoc())
		{
			$players[$player["playerlogin"]]["points"] = 0;
			$players[$player["playerlogin"]]["nickname"] = $player["nickname"];
		}
		
		foreach ($mapList as $id=>$map)
		{		
			$maptype = explode('\\', $map["MapType"])[1];
			
			$totalMapScore = 0;
			
			if ($maptype == 'Stunters') $sql = "SELECT playerlogin, score FROM records WHERE challengeid='".$map["UId"]."' ORDER BY score DESC";
			else $sql = "SELECT playerlogin, score FROM records WHERE challengeid='".$map["UId"]."' ORDER BY time ASC";
			
			
			$mysql = mysqli_query($this->db, $sql);
			$score = $mysql->num_rows;
			while ($record = $mysql->fetch_assoc())
			{
				$players[$record["playerlogin"]]["points"] += $score;
				$score--;
			}
		}

		$playersRanking = array();
	
		foreach($players as $login2=>$data)
		{
			if ($data["points"]) $playersRanking[$login2] = $data["points"];	
		}
		
		arsort($playersRanking);
		
		/* Produce XML TODO: Cache */
		$windowWidth = 70;
		if ($this->stringToBool($this->config->LT_actif)) $windowWidth = 90;
		
		$rank = 1;
		$xml = '<datas>'.PHP_EOL;
		foreach($playersRanking as $login2=>$points)
		{
			$xml .= '<player rank="'.$rank.'" points="'.$players[$login2]["points"].'" nickname="'.htmlspecialchars($players[$login2]["nickname"]).'" login="'.htmlspecialchars($login2).'" />'.PHP_EOL;
			$rank++;
			
			if ($rank > 250) break;
		}
		$xml .= '</datas>'.PHP_EOL;
		
		
		// Window title
		$windowTitle = '$fffS$cccerver $fffR$cccanking';
		$frameModel = '<framemodel id="DataLine">';
		
		$windowHeader = '';
		
		// Window header
		$windowHeader .= '
			<label sizen="5" text="$iRank" />
			<label sizen="35" posn="6" text="$iNickName" />
			<label sizen="15" posn="42" text="$iLogin" />
			<label sizen="15" posn="58" text="$iPoints" />
			';			
		
		$frameModel .= '
			<label id="rank" sizen="5" text="$iRank" valign="center2" textfont="Stunts/XBall" />
			<label id="nickname" sizen="35" posn="6" text="$iNickName" valign="center2" />
			<label id="login" sizen="15" posn="42" text="$iLogin" valign="center2" />
			<label id="points" sizen="15" posn="58" text="$iPoints" valign="center2" textfont="Stunts/XBall" />
			';
		
		// Limited time
		if ($this->stringToBool($this->config->LT_actif))
		{
			$windowHeader .= '
				<label sizen="10" posn="69" text="$iTimePlayed" />
				<label sizen="10" posn="80" text="$iTimeLeft" />
				';
				
			/* $frameModel .= '
				<td width="10">'.$this->time_played($login2).'</td>
				<td width="10">'.($this->config->LT_time - $this->time_played($login2)).'</td>
				'; */
		}

		$frameModel .= '</framemodel>';
		
		$lineHeight = 6;
		$posY		= 0;
		
		$frameInstance = '<frame id="DataFrame">';
		
		for($i=0;$i<20;$i++)
		{
			$frameInstance .= '<frameinstance posn="0 '.$posY.'" modelid="DataLine" hidden="1" />';
			$posY -= $lineHeight;
		}
		
		$posY		= 0;
		for($i=0;$i<20;$i++)
		{
			$frameInstance .= '<frameinstance posn="90 '.$posY.'" modelid="DataLine" hidden="1" />';
			$posY -= $lineHeight;
		}
		
		$posY		= 0;
		for($i=0;$i<20;$i++)
		{
			$frameInstance .= '<frameinstance posn="180 '.$posY.'" modelid="DataLine" hidden="1" />';
			$posY -= $lineHeight;
		}
		
		$frameInstance .= '</frame>';
		
					
			$ml = '<?xml version="1.0" encoding="UTF-8" ?>';
			$ml .= '<manialink id="3626" name="StuntsControl/AvgRanking" version="2">
			<quad id="testRanking" action="mytest"  posn="-160 90 -1" sizen="320 180" bgcolor="111f" bgcolorfocus="333f" scriptevents="1" />
			';
			$ml .= '<frame id="AverageRankWindow" posn="-130 70 0">';
			
			$ml .= '
			
			<label posn="0 8 10" sizen="25" text="'.htmlspecialchars($windowTitle).'" />';
			
			$ml .= $frameModel;
			$ml .= $windowHeader;
			$ml .= $frameInstance;
		
			$ml .= '</frame>
			<script><!--
		#Include "TextLib"
		
		declare CMlFrame[] 		DataLines;
		declare Integer			CurrentPage;
		declare CXmlDocument	xml;
		
		Void UpdateLine(Integer _Line, Text[Text] _Data)
		{	
			if (!DataLines.existskey(_Line)) return;
			
			declare DataLine	<=> DataLines[_Line];	
			declare rank		<=> (DataLine.GetFirstChild("rank") 	as CMlLabel);
			declare nickname	<=> (DataLine.GetFirstChild("nickname") as CMlLabel);
			declare login		<=> (DataLine.GetFirstChild("login") 	as CMlLabel);
			declare points		<=> (DataLine.GetFirstChild("points") 	as CMlLabel);
			
			DataLine.Show();	
			rank.SetText(_Data["rank"]);
			login.SetText(_Data["login"]);
			nickname.SetText(_Data["nickname"]);
			points.SetText(_Data["points"]);
			
			if (_Data["login"] == LocalUser.Login) rank.SetText("$aaf$o$w"^_Data["rank"]);
		}
		
		Void ReadData()
		{
			declare persistent StuntsControl_AverageRankingPage for This = 1;
		
			declare LineNumber = 0;
			if (xml != Null)
			{				
				foreach(Node in xml.Nodes)
				{	
					if (Node.Name == "page")
					{
						if (Node.GetAttributeText("resultsTotal", "0") == "0") (Page.GetFirstChild("WorldRecordsHeaderText") as CMlLabel).SetText("$fffNo World Records");
						else (Page.GetFirstChild("WorldRecordsHeaderText") as CMlLabel).SetText("$fffWorld Records");
					}
					else if (Node.Name == "player")
					{	
						if (LineNumber>LineNumber*StuntsControl_AverageRankingPage || LineNumber<LineNumber*(StuntsControl_AverageRankingPage+1))
						{
							UpdateLine(LineNumber,
									[
									"rank"=>Node.GetAttributeText("rank", ""),
									"login"=>Node.GetAttributeText("login", ""),
									"nickname"=>Node.GetAttributeText("nickname", ""),
									"points"=>Node.GetAttributeText("points", "")
									]
								);
						}
						
						LineNumber += 1;
					}					
				}
			}	
		}
		
		main ()
		{
			foreach (Control in (Page.GetFirstChild("DataFrame") as CMlFrame).Controls) DataLines.add((Control as CMlFrame));
			
			xml <=> Xml.Create("""'.$xml.'""");
			
			declare persistent StuntsControl_AverageRankingPage for This = 1;
			StuntsControl_AverageRankingPage = 2;
			ReadData();
			
			while(True)
			{				
				yield;
				
				foreach(Event in PendingEvents)
				{
					if (Event.Type == CMlEvent::Type::MouseClick && Event.ControlId == "testRanking")
					{
						log("clik");
						// TriggerPageAction("testouille");
					}
				
				}
				// if (!Page.Visible) continue;
			}
		}
	--></script>
	</manialink>';
			
		$this->instance()->client->query('SendDisplayManialinkPage', $ml, 0, False);

	}
	
	
	
	
	
	
	public function SendServerRankTable($login)
	{	
		global $settings;
		
		#### Calculate server ranking
		// Get map List
		$this->instance()->client->query("GetMapList", 500, 0);
		$mapList = $this->instance()->client->getResponse();
		
		// print_r($mapList);
				
		$players = Array();
		$playersNickName = Array();
		
		// Get database player list
		$sql = "SELECT playerlogin, nickname FROM players";
		$mysql = mysqli_query($this->db, $sql);
		
		// Initialize players arrays
		while ($player = $mysql->fetch_assoc())
		{
			$players[$player["playerlogin"]]["points"] = 0;
			$players[$player["playerlogin"]]["nickname"] = $player["nickname"];
		}
		
		foreach ($mapList as $id=>$map)
		{		
			$maptype = explode('\\', $map["MapType"])[1];
			
			$totalMapScore = 0;
			
			if ($maptype == 'Stunters') $sql = "SELECT playerlogin, score FROM records WHERE challengeid='".$map["UId"]."' ORDER BY score DESC";
			else $sql = "SELECT playerlogin, score FROM records WHERE challengeid='".$map["UId"]."' ORDER BY time ASC";
			
			
			$mysql = mysqli_query($this->db, $sql);
			$score = $mysql->num_rows;
			while ($record = $mysql->fetch_assoc())
			{
				$players[$record["playerlogin"]]["points"] += $score;
				$score--;
			}
		}

		$playersPoints = array();
	
		foreach($players as $login2=>$data)
		{
			if ($data["points"]) $playersPoints[$login2] = $data["points"];	
		}
		
		arsort($playersPoints);
		
		/* Produce XML Cache */
		$rank = 1;
		$xml = '<datas>'.PHP_EOL;
		foreach($playersPoints as $login2=>$points)
		{
			$xml .= '<player rank="'.$rank.'" points="'.$players[$login2]["points"].'" nickname="'.htmlspecialchars($players[$login2]["nickname"]).'" />'.PHP_EOL;
			$rank++;
		}
		$xml .= '</datas>'.PHP_EOL;
		// arsort($players);
		
		echo $xml;
		#### UI
		// Create window
		$window = $this->window;
		$window->init();
		
		// Window title
		$windowTitle = '$fffS$cccerver $fffR$cccanking';
		$window->title($windowTitle);
		
		// Window settings
		$window->displayAsTable(true);
		// $window->fontSize(1.5);
		if ($this->stringToBool($this->config->LT_actif))
		{
			$window->size(90, '');
		}
		else
		{
			$window->size(70, '');
		}
		$window->posY('37');
		$window->target('onPages', $this);
		
		// Close button
		$window->addButton('', '15.5', false);
		$window->addButton('Close', '10', true);
		$window->addButton('', '15.5', false);
		
		// Window header
		//$window->content('<td width="5">$iRank</td><td width="35">$iNickName</td><td width="15">$iLogin</td><td width="15">$iPoints</td>');
		
		$windowContent = '<td width="5">$iRank</td><td width="35">$iNickName</td><td width="15">$iLogin</td><td width="15">$iPoints</td>';
		if ($this->stringToBool($this->config->LT_actif))
		{
			$windowContent .= '<td width="10">$iTimePlayed</td>';
			$windowContent .= '<td width="10">$iTimeLeft</td>';
		}
		$window->content($windowContent);
		
		$rank = 1;
		foreach($players as $playerLogin=>$points)
		{
			$windowContent = "";
			$windowContent .= '<td width="5">'.$rank.'</td>';
			$windowContent .= '<td width="35">'.htmlspecialchars($players[$playerLogin]["nickname"]).'</td>';
			$windowContent .= '<td width="15">'.$playerLogin.'</td>';
			$windowContent .= '<td width="15">'.$players[$playerLogin]["points"].'</td>';
			if ($this->stringToBool($this->config->LT_actif))
			{
				$windowContent .= '<td width="10">'.$this->time_played($playerLogin).'</td>';
				$windowContent .= '<td width="10">'.($this->config->LT_time - $this->time_played($playerLogin)).'</td>';
			}
			$window->content($windowContent);
			
			$rank++;
			if ($rank > 25) break;			
		}
		
		$window->show($login);
	}
	
	
	
	
	
		
	public function SendPointsRankTable($login)
	{	
		global $settings;
		
		#### Calculate server ranking
		// Get map List
		$this->instance()->client->query("GetMapList", 500, 0);
		$mapList = $this->instance()->client->getResponse();
		
		$players = Array();
		$playersNickName = Array();
		
		// Get database player list
		$sql = "SELECT playerlogin, nickname FROM players";
		$mysql = mysqli_query($this->db, $sql);
		
		// Initialize players arrays
		while ($player = $mysql->fetch_assoc())
		{
			$players[$player["playerlogin"]] = 0;
			$playersNickName[$player["playerlogin"]] = $player["nickname"];
		}
		
		foreach ($mapList as $id=>$map)
		{
			$totalMapScore = 0;
			$sql = "SELECT playerlogin, score FROM records WHERE challengeid='".$map["UId"]."'";
			$mysql = mysqli_query($this->db, $sql);
			
			while ($record = $mysql->fetch_assoc())
			{
				$players[$record["playerlogin"]] += $record["score"];
			}
		}

		arsort($players);
		
		
		#### UI
		// Create window
		$window = $this->window;
		$window->init();
		
		// Window title
		$windowTitle = '$070S$ffferver $070R$fffanking';
		$window->title($windowTitle);
		
		// Window settings
		$window->displayAsTable(true);
		// $window->fontSize(1.5);
		if ($this->stringToBool($this->config->LT_actif))
		{
			$window->size(90, '');
		}
		else
		{
			$window->size(70, '');
		}
		$window->posY('37');
		$window->target('onPages', $this);
		
		// Close button
		$window->addButton('', '15.5', false);
		$window->addButton('Close', '10', true);
		$window->addButton('', '15.5', false);
		
		// Window header
		//$window->content('<td width="5">$iRank</td><td width="35">$iNickName</td><td width="15">$iLogin</td><td width="15">$iPoints</td>');
		
		$windowContent = '<td width="5">$iRank</td><td width="35">$iNickName</td><td width="15">$iLogin</td><td width="15">$iPoints</td>';
		if ($this->stringToBool($this->config->LT_actif))
		{
			$windowContent .= '<td width="10">$iTimePlayed</td>';
			$windowContent .= '<td width="10">$iTimeLeft</td>';
		}
		$window->content($windowContent);
		
		$rank = 1;
		foreach($players as $playerLogin=>$points)
		{
			$windowContent = "";
			$windowContent .= '<td width="5">'.$rank.'</td>';
			$windowContent .= '<td width="35">'.htmlspecialchars($playersNickName[$playerLogin]).'</td>';
			$windowContent .= '<td width="15">'.$playerLogin.'</td>';
			$windowContent .= '<td width="15">'.$players[$playerLogin].'</td>';
			if ($this->stringToBool($this->config->LT_actif))
			{
				$windowContent .= '<td width="10">'.$this->time_played($playerLogin).'</td>';
				$windowContent .= '<td width="10">'.($this->config->LT_time - $this->time_played($playerLogin)).'</td>';
			}
			$window->content($windowContent);
			
			$rank++;
			if ($rank > 25) break;			
		}
		
		$window->show($login);
	}
	
	
	
	
	
	public function SendLocalRecordsTable()
	{
		$HeaderHeight = 5.0;
		$CellHeight = 4.5;
		$PosY = 0.0;
		$Width = 40.0;
	
		if ($this->stringToBool($this->config->show_widget))
		{		
			// Request records from database
			if ($this->CurrentMap["MapType"] == 'Stunters') $sql = 'SELECT records.score, records.time, players.nickname FROM records LEFT JOIN players ON records.playerlogin=players.playerlogin WHERE challengeid="'.$this->MapUId.'" ORDER BY score DESC LIMIT '.$this->config->max_records_to_display;
			else  $sql = 'SELECT records.score, records.time, players.nickname FROM records LEFT JOIN players ON records.playerlogin=players.playerlogin WHERE challengeid="'.$this->MapUId.'" ORDER BY time ASC LIMIT '.$this->config->max_records_to_display;
			$reqScore = mysqli_query($this->db, $sql);
			$numRows = $reqScore->num_rows;
			
			$title = "";
			
			$ml = '<?xml version="1.0" encoding="UTF-8" ?>';
			$ml .= '<manialink id="StuntersLocalRecordsTable" version="1">';
			$ml .= '<frame id="Window" posn="'. $this->config->posX .' '. $this->config->posY .' '. $this->config->posZ .'">';
			
			// If no records found: display no record value, else display formatted text
			if ($numRows == 0) $title = $this->config->header_text_when_no_records;
			else $title = $this->config->header_text;
			
			// Format message
			$fromArray = array('{mapname}');
			$toArray = array($this->MapName);
			$title = str_replace($fromArray, $toArray, $title);
			
			// Header background
			//$ml .= '<quad sizen="40 5" posn="0 0 4" bgcolor="'.$this->config->header_color.'" />';
			
			// Header text		
			$ml .= '<label posn="2 '.(-$HeaderHeight/2).'" sizen="'.($Width-6).'" textfont="Stunts/XBall" text="$fff$s'. htmlspecialchars($title) .'" valign="center2" scale="0.8" />';
			
			// Icon
			$ml .= '<quad posn="0.5 '.(-$CellHeight/2).'" sizen="4.5 4.5" style="'.$this->config->icon_style.'" substyle="'.$this->config->icon_substyle.'" valign="center2" />';
			
			
			$PosY -= $HeaderHeight;
			
			// Local records list
			if ($numRows != 0)
			{	
				$counter = 0;
				
				while ($resScore = $reqScore->fetch_assoc())
				{
					// Rank
					$ml .= '<label posn="4.5 '.($PosY-($CellHeight/2)).'" sizen="4" text="'.($counter+1).'" textfont="Stunts/XBall" scale="'.$this->config->rank_scale.'" textprefix="'.$this->config->rank_prefix.'" halign="right" valign="center2" />';
					
					// NickName
					$ml .= '<label posn="6 '.($PosY-($CellHeight/2)).'" sizen="'.($Width-5-7.5).'" text="'.htmlspecialchars($resScore['nickname']).'" style="'.$this->config->nickname_style.'" scale="'.$this->config->nickname_scale.'" valign="center2" />';
					
					// Score
					if ($this->CurrentMap["MapType"] == 'Stunters') $ml .= '<label posn="'.($Width-1).' '.($PosY-($CellHeight/2)).'" sizen="8" text="$fff$s'.$resScore['score'].'" textfont="Stunts/XBall" scale="'.$this->config->score_scale.'" halign="right" valign="center2" />';
					else  $ml .= '<label posn="'.($Width-1).' '.($PosY-($CellHeight/2)).'" sizen="8" text="$fff$s'.$resScore['time'].'" textfont="Stunts/XBall" scale="'.$this->config->score_scale.'" halign="right" valign="center2" />';
					$counter++;
					$PosY -= $CellHeight;
				}
				
				$ml .= '<quad id="DragQuad" sizen="40 '.-$PosY.'" posn="0 0 -2" bgcolor="'.$this->config->window_color.'"  scriptevents="1" />';
			}
		
			$ml .= '</frame>';
			
			// ML Script with movable window and position recorded on player profil
			$ml .= '<script><!--
		#Include "TextLib" as TextLib
		main ()
		{
			declare CMlFrame Window <=> (Page.GetFirstChild("Window") as CMlFrame);
			declare CMlQuad DragQuad <=> (Page.GetFirstChild("DragQuad") as CMlQuad);
			declare persistent Boolean MLS_ShowServerRecords for This = True;
			declare persistent Vec2 MLS_ServerRecordsPos for This = <'.($this->config->posX).','.($this->config->posY).'>;
			Window.RelativePosition.X = MLS_ServerRecordsPos.X;
			Window.RelativePosition.Y = MLS_ServerRecordsPos.Y;
			declare MoveWindow = False;
			declare Vec3 LastDelta = <Window.RelativePosition.X,Window.RelativePosition.Y,0.>;
			declare Vec3 DeltaPos = <0.,0.,0.>;
			declare Vec2 LastMouse  = <0.,0.>;			
			
			while(True)
			{				
				yield;				
				
				Window.Visible = MLS_ShowServerRecords;
				
				if (!Window.Visible) continue;				
				
				if (MoveWindow)
				{                                                                                                    
					DeltaPos.X = MouseX - LastMouse.X;
					DeltaPos.Y = MouseY - LastMouse.Y;
					LastDelta += DeltaPos;					
					Window.RelativePosition = LastDelta;                                    
					LastMouse = <MouseX,MouseY>;
					MLS_ServerRecordsPos.X = LastDelta.X;
					MLS_ServerRecordsPos.Y = LastDelta.Y;
				}
				else
				{				
					if (Window.RelativePosition.X != MLS_ServerRecordsPos.X || Window.RelativePosition.Y != MLS_ServerRecordsPos.Y)
					{
						Window.RelativePosition.X = MLS_ServerRecordsPos.X;
						Window.RelativePosition.Y = MLS_ServerRecordsPos.Y;
						LastDelta = <Window.RelativePosition.X, Window.RelativePosition.Y, 0.0>;
					}
				}
				
				if (MouseLeftButton == True || MouseMiddleButton == True)
				{
					foreach (Event in PendingEvents)
					{
						if (Event.Type == CMlEvent::Type::MouseClick && Event.ControlId == "DragQuad")
						{
							LastMouse = <MouseX,MouseY>;
							MoveWindow = True;
						}
					}
				}
				else
				{
					MoveWindow = False;
				} 				
			}
		}
	--></script>';
			
			
			$ml .='</manialink>';
			
			$this->instance()->client->query('SendDisplayManialinkPage', $ml, 0, False);
		} // if show_widget
	}
	
	// Convert string to bool
	function stringToBool($string)
	{
		if (strtolower($string)=="false" || $string=="0" || $string=="") return false;
		return true;
	}	
	
	// convert bool to string
	function boolToString($bool)
	{
		if ($this->stringToBool($bool) == true)
		{
			return 'true';
		}
		else
		{
			return 'false';
		}
	} // end boolToString
	
	// search the nickname of the login
	function loginToNickname($login)
	{
		$nick = $login;
		$result = mysqli_query($this->db, "SELECT playerlogin,nickname FROM players WHERE playerlogin = '".$login."' LIMIT 1");
		$obj = $result->fetch_object();
		if ($result->num_rows) $nick = $obj->nickname;

		return $nick;
	}
}
?>
<?php
// Based on FoxControl 2010->2012 by FoxRace, http://www.fox-control.de - Coded by:  matrix142, cyrilw, libero

// StuntsControl
// Source code on GitHub : https://github.com/Spaii/StuntsControl/
// Copyleft 2012->2015 by http://stunters.org - Momo, Pastis, Spaï

require_once('include/GbxRemote.inc.php');
require_once('include/defines.php');

error_reporting(E_ALL);

function console($console)
{
	if(trim($console) == '') return;
	
	$ct = explode("\n", $console);
	for($i = 0; isset($ct[$i]); $i++)
	{
		echo'['.date('d.m.y H:i:s').'] '.$ct[$i].nz;
	}
	
	//Write daily logs files
	/*if(file_exists('logs/'.date('d.m.Y').'.log')){
		$log = file_get_contents('logs/'.date('d.m.Y').'.log');
		$log = $log.nz.'['.date('d.m.y H:i:s').'] '.$console;
		
		file_put_contents('logs/'.date('d.m.Y').'.log', $log);
	}
	else{
		$newdate = date('d.m.Y');
		
		file_put_contents('logs/'.$newdate.'.log', '['.date('d.m.y H:i:s').'] '.$console);
		
		chmod ('./logs/'.$newdate.'.log', 0777);
	}*/
}

class control {
	
	public $playerList 		= array();
	public $CurrentMap 		= array();
	public $CurrentStatus 	= 0;

	public function run()
	{
		global $control, $settings;
		
		$control 	= $this;
		$settings 	= array();
				
		console(SC_Name.' '.SC_Version);
		
		$this->client = New IXR_Client_Gbx;
		
		// Read config file (TODO: php default values)
		if(file_exists('config.xml')) $xml = @simplexml_load_file('config.xml');
		else
		{
			console('ERROR: Can\'t read config.xml file.');
			exit;
		}
		
		$settings['Port'] 						= $xml->port;
		$settings['ServerIP'] 					= $xml->serverIP;
		$settings['ServerPW'] 					= $xml->SuperAdminPW;
		$settings['ServerLogin'] 				= $xml->serverlogin;
		$settings['ServerPassword'] 			= $xml->serverpassword;
		$settings['AdminTMLogin'] 				= $xml->YourTmLogin;
		$settings['ServerLocation'] 			= $xml->ServerLocation;
		$settings['Nation'] 					= $xml->nation;
		$settings['DB_Path'] 					= $xml->db_path;
		$settings['DB_User'] 					= $xml->db_user;
		$settings['DB_PW'] 						= $xml->db_passwd;
		$settings['DB_Name'] 					= $xml->db_name;
		$settings['Name_SuperAdmin'] 			= $xml->name_superadmin;
		$settings['Name_Admin'] 				= $xml->name_admin;
		$settings['Name_Operator'] 				= $xml->name_operator;
		$settings['ServerName'] 				= $xml->servername;
		$settings['Text_wrong_rights'] 			= $xml->text_false_rights;
		$settings['StartWindow'] 				= $xml->startwindow;
		$settings['Text_StartWindow'] 			= $xml->startwindowtext;
		$settings['Message_PlayerConnect'] 		= $xml->player_message_connect;
		$settings['Message_PlayerLeft'] 		= $xml->player_message_left;
		$settings['message_connect'] 			= $xml->message_connect;
		$settings['message_left'] 				= $xml->message_left;
		$settings['Color_Default'] 				= $xml->default_color;
		$settings['Color_Kick'] 				= $xml->color_kick;
		$settings['Color_Warn'] 				= $xml->color_warn;
		$settings['Color_Ban'] 					= $xml->color_ban;
		$settings['Color_UnBan'] 				= $xml->color_unban;
		$settings['Color_ForceSpec'] 			= $xml->color_forcespec;
		$settings['Color_Ignore'] 				= $xml->color_ignore;
		$settings['Color_SetPW'] 				= $xml->color_setpw;
		$settings['Color_NewServername'] 		= $xml->color_newservername;
		$settings['Color_NewAdmin'] 			= $xml->color_newadmin;
		$settings['Color_RemoveAdmin'] 			= $xml->color_removeadmin;
		$settings['Color_Join'] 				= $xml->color_join;
		$settings['Color_Left'] 				= $xml->color_left;
		$settings['Color_OpConnect'] 			= $xml->color_op_connect;
		$settings['Color_AdminConnect'] 		= $xml->color_admin_connect;
		$settings['Color_SuperAdminConnect'] 	= $xml->color_superadmin_connect;
		$settings['Color_NewChallenge'] 		= $xml->color_newchallenge;
		$settings['menu_name'] 					= $xml->menu_name;
		$settings['display_local_recs'] 		= $xml->display_local_recs;
		$settings['max_local_recs'] 			= $xml->max_local_recs;
		$settings['chat_locals_number'] 		= $xml->chat_locals_number;
		$settings['autosave_matchsettings'] 	= $xml->autosave_matchsettings;
		$settings['matchsettings_filename'] 	= $xml->matchsettings_filename;
		$settings['default_style1'] 			= $xml->default_style1;
		$settings['default_substyle1'] 			= $xml->default_substyle1;
		$settings['default_style2'] 			= $xml->default_style2;
		$settings['default_substyle2'] 			= $xml->default_substyle2;
		$settings['default_window_style'] 		= $xml->default_window_style;
		$settings['default_window_substyle'] 	= $xml->default_window_substyle;	
		
		console('Config file initialized!'.nz);	
	
		// Timezone
		date_default_timezone_set($settings['ServerLocation']);
		
		// If server connection is false
		if(!$this->connect($settings['ServerIP'], $settings['Port'],  'SuperAdmin', $settings['ServerPW']))
		{
			die('ERROR: Connection canceled! Wrong Port, IP or SuperAdmin Password!' . nz); 
		// Else initialize FoxControl
		}
		else
		{
			$defaultcolor = $settings['Color_Default'];
			
			$this->client->query('SetApiVersion', '2013-04-16');
			
			//Hide all Manialinks
			$this->client->query('SendHideManialinkPage');
		
			//Display FoxControl is starting window
			$this->client->query('SendDisplayManialinkPage', '<?xml version="1.0" encoding="UTF-8" ?>
			<manialink id="1">
				<quad posn="0 43 0" sizen="30 3" style="Bgs1" halign="center" substyle="NavButton" action="0"/>
				<label text="'.SC_ColoredName.'$fff is starting.." halign="center" posn="0 42.7 1" sizen="30 3" />
			</manialink>', 0, False);
			
			//Insert start message into console
			console(SC_Name.' is now running with PHP '.phpversion().' on '. PHP_OS);
			console(nz.'-->Connecting to the database..');
		
			//Connect to database
			global $db, $fc_db;
		
			$db = mysqli_connect($settings['DB_Path'], $settings['DB_User'], $settings['DB_PW']);
			
			if(!mysqli_select_db($db, $settings['DB_Name'])) die('[ERROR] Can\'t connect to the database!');
			
			console('-->Connected!'.nz);
			
			/* Admins */
			$tbl_admins = "
				CREATE TABLE IF NOT EXISTS `admins` (
				`id` smallint(6) NOT NULL AUTO_INCREMENT,
				`playerlogin` varchar(50) NOT NULL,
				`rights` smallint(1) NOT NULL DEFAULT '1',
				PRIMARY KEY (`id`)
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=280 ;";
			mysqli_query($db, $tbl_admins);
			
			/* Karma */
			$tbl_karma = "
				CREATE TABLE IF NOT EXISTS `karma` (
				`challengeid` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
				`challengename` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
				`playerlogin` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
				`vote` varchar(6) NOT NULL,
				`timestamp` int(11) NOT NULL
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
			mysqli_query($db, $tbl_karma);
			
			/* Players */
			$tbl_players = "
				CREATE TABLE IF NOT EXISTS `players` (
				`id` smallint(6) NOT NULL AUTO_INCREMENT,
				`playerlogin` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
				`nickname` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
				`lastconnect` int(30) NOT NULL,
				`timeplayed` int(30) NOT NULL,
				`donations` int(11) NOT NULL,
				PRIMARY KEY (`id`)
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=120 ;";
			mysqli_query($db, $tbl_players);
			mysqli_query($db, "ALTER TABLE `players` ADD country VARCHAR(50) NOT NULL");
			mysqli_query($db, "ALTER TABLE `players` ADD continent VARCHAR(50) NOT NULL");
			mysqli_query($db, "ALTER TABLE `players` ADD path VARCHAR(255) NOT NULL");
			mysqli_query($db, "ALTER TABLE `players` ADD connections INT(20) NOT NULL");
			
			/* Records */
			$tbl_records = "
				CREATE TABLE IF NOT EXISTS `records` (
				`challengeid` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
				`playerlogin` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
				`nickname` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
				`time` int(11) NOT NULL,
				`date` datetime NOT NULL
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
			mysqli_query($db, $tbl_records);
			
			/* Maps */
			$maps_table = 'CREATE TABLE IF NOT EXISTS `maps` (
		   `Id` smallint UNSIGNED NOT NULL auto_increment,
		   `UId` varchar(255) NOT NULL default "",
		   `Enviro` varchar(25) NOT NULL default "",
		   `Car` varchar(255) NOT NULL default "",
		   `MapCar` varchar(255) NOT NULL default "Classic",
		   `Name` varchar(255) COLLATE utf8_unicode_ci NOT NULL default "",			   
		   `Login` varchar(255) COLLATE utf8_unicode_ci NOT NULL default "",
		   `NickName` varchar(255) COLLATE utf8_unicode_ci NOT NULL default "",
		   `CollectionName` varchar(255) COLLATE utf8_unicode_ci NOT NULL default "",
		   `DecorationName` varchar(255) COLLATE utf8_unicode_ci NOT NULL default "",
		   `MapType` varchar(255) COLLATE utf8_unicode_ci NOT NULL default "",
		   `Style` varchar(255) COLLATE utf8_unicode_ci NOT NULL default "",
			`CopperPrice` smallint UNSIGNED NOT NULL default 0,
		   `TimeLimit` mediumint UNSIGNED NOT NULL default 0,
		   `ScoreToReach` mediumint UNSIGNED NOT NULL default 0,
		   `AuthorScore` mediumint UNSIGNED NOT NULL default 0,
		   `GoldScore` mediumint UNSIGNED NOT NULL default 0,			   
		   `SilverScore` mediumint UNSIGNED NOT NULL default 0,
		   `BronzeScore` mediumint UNSIGNED NOT NULL default 0,
		   `DateAdded` datetime NOT NULL default "0000-00-00 00:00:00",
		   `PlayCount` int UNSIGNED NOT NULL default 0,
		   PRIMARY KEY (`Id`),
		   UNIQUE maps_uid_unique (`Uid`),
		   INDEX maps_environement_index (`Enviro`),
		   INDEX maps_car_index (`Car`),
		   INDEX maps_maptype_index (`MapType`),
		   INDEX maps_login_index (`Login`)
		 ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';
			mysqli_query($db, $maps_table);
			
			// Remove widget_settings table if exist
			$db->query("DROP TABLE IF EXISTS `widget_settings`");
			
			// Remove vinfo table if exist
			$db->query("DROP TABLE IF EXISTS `vinfo`");			
			
			/* Fix bug MostActive time enorm TODO: Correct bug, not patch over bug */
			$sql = mysqli_query($db, "SELECT playerlogin, timeplayed FROM `players`");
			while($row = $sql->fetch_object())
			{
				if($row->timeplayed > 10000000)	mysqli_query($db, "UPDATE `players` SET timeplayed = '0' WHERE playerlogin = '".$row->playerlogin."'");
			}
			
		/* Creating DB end */
		
		
			$fc_db = $db;
			global $FoxControl_Reboot, $FoxControl_Shutdown;
			$FoxControl_Reboot = false;
			$FoxControl_Shutdown = false;
		
			/* Creating SuperAdmin account */
			if(trim($settings['AdminTMLogin']) != '' AND trim($settings['AdminTMLogin'] != 'YourLogin'))
			{
				$sql = mysqli_query($db, "SELECT * FROM admins WHERE playerlogin = '".$settings['AdminTMLogin']."'");
				if(!$row = $sql->fetch_object())
				{
					$sql2 = mysqli_query($db, "INSERT INTO `admins` (id, playerlogin, rights) VALUES ('', '".$settings['AdminTMLogin']."', '3')");
				}
	
				$atmlfile = file('config.xml');
				file_put_contents('config.xml', str_replace('<YourTmLogin>'.$settings['AdminTMLogin'].'</YourTmLogin>', '', $atmlfile));
			}
	
			/* Enable callbacks */
			console('-->Enable Callbacks');
			if (!$this->client->query('EnableCallbacks', true))
			{
				console('[Error ' . $this->client->getErrorCode() . '] ' . $this->client->getErrorMessage());
				die('[Error] Cant\'t enable callbacks!');
			}
			else console('-->Callbacks enabled'.nz);

		
			/* Load plugins */
			global $fc_active_plugins, $plugins_cb, $fc_mlids, $fc_commands, $events;
		
			$fc_active_plugins 	= array();
			$plugins_cb 		= array();
			$fc_mlids 			= 3;
			$fc_commands 		= array();
			$events 			= array();
		
			require_once('./include/class.foxcontrolplugin.php');
			require_once('./include/class.window.php');
			require_once('./include/class.manialink.php');
		
			$pluginclass = 'window';
			$plugins_cb[] = array(0 => new $pluginclass, 1 => $pluginclass, 2 => array(), 3 => array());
			$plugins_cb[0][0]->initFCPluginClass($pluginclass);
			$plugins_cb[0][0]->onInit();
			
			$pluginclass = 'manialink';
			$plugins_cb[] = array(0 => new $pluginclass, 1 => $pluginclass, 2 => array(), 3 => array());
			$plugins_cb[1][0]->initFCPluginClass($pluginclass);
			$plugins_cb[1][0]->onInit();
			
			global $window, $manialink;
			$window 	= $plugins_cb[0][0];
			$manialink 	= $plugins_cb[1][0];
			
			/* Write events array */
			$events['onStartUp'] = array();
			$events['onEverySecond'] = array();
			$events['onTick'] = array();
			$events['onPlayerConnect'] = array();
			$events['onPlayerDisconnect'] = array();
			$events['onManialinkPageAnswer'] = array();
			$events['onCommand'] = array();
			$events['onChat'] = array();
			$events['onBeginRace'] = array();
			$events['onEndRace'] = array();
			$events['onEcho'] = array();
			$events['onServerStart'] = array();
			$events['onServerStop'] = array();
			$events['onBeginMap'] = array();
			$events['onEndMap'] = array();
			$events['onBeginRound'] = array();
			$events['onEndRound'] = array();
			$events['onBeginMatch'] = array();
			$events['onEndMatch'] = array();
			$events['onStatusChanged'] = array();
			$events['onPlayerCheckpoint'] = array();
			$events['onPlayerFinish'] = array();
			$events['onPlayerIncoherence'] = array();
			$events['onBillUpdated'] = array();
			$events['onTunnelDataReceived'] = array();
			$events['onMapListModified'] = array();
			$events['onPlayerInfoChanged'] = array();
			$events['onManualFlowControlTransition'] = array();
			$events['onVoteUpdated'] = array();
			$events['onModeScriptCallback'] = array();
			
			/* Start up */
			
			// wait for non waiting state
			console('Waiting server...'.nz);
			$serverStatusTmp = 1;
			while($serverStatusTmp == 1)
			{
				if (!$this->client->query('GetStatus')) die('GetStatus - An error occurred - ' . $this->client->getErrorCode() . ':' . $this->client->getErrorMessage());
				$serverStatusTmp = $this->client->getResponse()["Code"];
				sleep(1);
			}
			console('Starting control'.nz);
			
			// Create Playerlist
			$this->client->query('GetPlayerList', 200, 0);
			$playerlist = $this->client->getResponse();			
			foreach($playerlist as $key => $value) $this->updatePlayerList($playerlist[$key]['Login']);
			
			// Update maps table
			$this->client->query('GetMapList', 500, 0);
			$mapList = $this->client->getResponse();
			foreach($mapList as $key => $value) $this->updateMap($value);
			
			/* Current Map */
			$this->client->query('GetCurrentMapInfo');
			$this->CurrentMap = $this->client->getResponse();
			$this->CurrentMap["MapType"] = explode('\\', $this->CurrentMap["MapType"])[1];

			/* GetMapProperties */
			$this->client->query('TriggerModeScriptEvent', "GetMapParameters", "");
			
			/* READ PLUGINS.XML */
			$xml = @simplexml_load_file('plugins.xml');
			$plugin_id = 0;
		
			console('-->Loading plugins..');
		
			while(isset($xml->plugin[$plugin_id])){
				console('-->Load plugin '.trim($xml->plugin[$plugin_id]).' ['.$plugin_id.']');
				
				if(file_exists('plugins/'.trim($xml->plugin[$plugin_id]).'')) {
					require('plugins/'.trim($xml->plugin[$plugin_id]).'');
				
					$fc_active_plugins[] = trim($xml->plugin[$plugin_id]);
					$pluginclass = str_replace('.', '_', trim($xml->plugin[$plugin_id]));
					$pluginclass = str_replace('_php', '', $pluginclass);
					$plugins_cb[] = array(0 => new $pluginclass, 1 => $pluginclass, 2 => array(), 3 => array());
					$plugins_cb[$plugin_id+2][0]->initFCPluginClass($pluginclass);
				} else {
					die('[ERROR] Can\'t load plugin \''.trim($xml->plugin[$plugin_id]).'\'. File does not exist!');
				}
				$plugin_id++;
			}
			
			for($i = 0; $i < count($plugins_cb); $i++) {
				foreach($events as $key => $value) {
					if(method_exists($plugins_cb[$i][0], $key)) {
						$events[$key][] = $i;
					}
				}
			}
			
			console('-->Plugins loaded!'.nz);
			$this->client->query('SendHideManialinkPage');		
			
			//Call StartUp Event in all Plugins
			$this->callEvent('StartUp');
		
			$this->client->query('GetServerName');
			$servername = $this->client->getResponse();
			
			if($settings['ServerName'] == '') {
				
			} else {			
				$this->client->query('SetServerName', (string) $settings['ServerName']);
			}
		
			console(SC_Name.' '.SC_Version);
			console('Authors: matrix142, cyrilw, libero, jens, spaii, pastis-51, momo');
			console('Running on '.$this->rgb_decode($settings['ServerName']));
			
			//StartUp Chat message			
			$this->client->query('ChatSendServerMessage', '$z$fff» '.SC_ColoredName.' $999'.SC_Version.' $fffstarted $fff» $af7'.($plugin_id+1).'$fff Plugins loaded.');
						
			$this->StuntsControl();
		}
	} // Run End
	
	/* Functions */	
	//GET ID OF SPECIFIED PLUGIN
	public function getPluginId($classname)
	{
		global $plugins_cb;
		
		for($i = 0; $i < count($plugins_cb); $i++)
		{
			if($plugins_cb[$i][1] == $classname) return $i;
		}
		
		return false;
	}
	
	//REGISTER MANIALINK IDS FOR PLUGIN
	public function registerMLIds($ids, $class)
	{
		global $fc_mlids, $plugins_cb;
		
		$pluginid = $this->getPluginId($class);
		
		if($pluginid === false) return false;
		
		$return = array();
		for($i = 0; $i < $ids; $i++)
		{
			$fc_mlids++;
			$return[] = $fc_mlids;
			$plugins_cb[$pluginid][2][] = $fc_mlids;
		}
		
		return $return;
	}
	
	//REIGSTER COMMAND FOR PLUGIN
	public function registerCommand($command, $description, $admin, $class)
	{
		global $fc_commands, $plugins_cb;
		
		$pluginid = $this->getPluginId($class);
		
		if($pluginid === false) return false;
		
		$fc_commands[] = array(0 => $command, 1 => $description, 2 => $admin);
		$plugins_cb[$pluginid][3][] = $command;
		return true;
	}
	
	/* Register page action prefix */
	public function registerPageAction($prefix, $class)
	{
		global $plugins_cb;
		
		$pluginid = $this->getPluginId($class);
		
		if($pluginid === false) return false;

		$plugins_cb[$pluginid][5][] = $prefix;
	}
	
	// GET LIST OF ALL CHAT COMMANDS
	public function getCommands($commands)
	{
		global $fc_commands;
		
		if($commands == 'all') return $fc_commands;
		else if($commands == 'player')
		{
			$array = array();
			for($i = 0; $i < count($fc_commands); $i++)
			{
				if($fc_commands[$i][2] === false && $fc_commands[$i][1] !== false) $array[] = $fc_commands[$i];
			}
			return $array;
		}
		else if($commands == 'admin')
		{
			$array = array();
			for($i = 0; $i < count($fc_commands); $i++)
			{
				if($fc_commands[$i][2] === true && $fc_commands[$i][1] !== false) $array[] = $fc_commands[$i];
			}
			return $array;
		}
	}
	
	// CHECK IF PLUGIN IS ACTIVE
	public function pluginIsActive($pluginName) {
		global $fc_active_plugins;
		
		for($i = 0; $i < count($fc_active_plugins); $i++) {
			if($fc_active_plugins[$i] == $pluginName) return true;
		}
		return false;
	}
	
	//UNBAN PLAYER
	public function unban($unban_player, $unbanmessage, $CommandAuthor, $ubplayer){
		global $settings, $db;
		
		$this->client->query('UnBan', $unban_player);
		$sql = "SELECT * FROM `admins` WHERE playerlogin = '".$CommandAuthor['Login']."'";
		$mysql = mysqli_query($db, $sql);
		
		if($admin_rights = $mysql->fetch_object()){
			if($admin_rights->rights==1){
				$Admin_Rank = $settings['Name_Operator'];
			}
			elseif($admin_rights->rights==2){
				$Admin_Rank = $settings['Name_Admin'];
			}
			elseif($admin_rights->rights==3){
				$Admin_Rank = $settings['Name_SuperAdmin'];
			}
			else $Admin_Rank = '';
		
		
		}
		else $Admin_Rank = '';
		
		if($unbanmessage === false) return;
		
		if(!isset($Unbanned_player['NickName'])) $Unbanned_player['NickName'] = $unban_player;
		
		$color_unban = $settings['Color_UnBan'];
		$this->client->query('ChatSendServerMessage', $color_unban.''.$Admin_Rank.' $fff'.$CommandAuthor['NickName'].'$z$s '.$color_unban.'unbanned $fff'.$ubplayer->nickname.'$z$s '.$color_unban.'!');
	}
	
	//GET RANK NAME OF PLAYER
	public function getPlayerRankName($login, $colors = true) {
		global $db, $settings;
	
		$sql = "SELECT * FROM `admins` WHERE playerlogin = '".$login."'";
		$mysql = mysqli_query($db, $sql);
		if(mysqli_errno($db)) {
			console(mysqli_error($db));
		}
		
		if($row = $mysql->fetch_object()) {
			if($row->rights == 1){
				if($colors == true) {
					$player_rank = $settings['Color_OpConnect'].$settings['Name_Operator'];
				} else {
					$player_rank = $settings['Name_Operator'];
				}
			}
			elseif($row->rights == 2){
				if($colors == true) {
					$player_rank = $settings['Color_AdminConnect'].$settings['Name_Admin'];
				} else {
					$player_rank = $settings['Name_Admin'];
				}
			}
			elseif($row->rights == 3){
				if($colors == true) {
					$player_rank = $settings['Color_SuperAdminConnect'].$settings['Name_SuperAdmin'];
				} else {
					$player_rank = $settings['Name_SuperAdmin'];
				}
			}
		}
		else{
			$player_rank = 'Player';
		}
		return $player_rank;
	}
	
	//PLAYER CONNECT
	public function playerconnect($connected_player)
	{
		global $db, $settings;
		
		$color_join = $settings['Color_Join'];
		$newline = "\n";
		
		//Get Servername and PlayerInfos
		$this->client->query('GetServerName');
		$servername = $this->client->getResponse();
		
		$this->updatePlayerList($connected_player[0]);
		
		$login = $connected_player[0];
		
		//Get Player Rank
		$player_rank = $this->getPlayerRankName($login);
		
		//If Join message is activated
		if($settings['Message_PlayerConnect'] == true) {
			$join_message = $settings['message_connect'];
				
			$replace = array('{rank}', '{nickname}', '{path}', '{ladder}');
			$replace2 = array($player_rank, $this->playerList[$login]['NickName'], $this->playerList[$login]['Path'], $this->playerList[$login]['LadderStats']);
				
			$join_message = str_replace($replace, $replace2, $join_message);
			
			//Send Join message
			$this->client->query('ChatSendServerMessage', $join_message);
		}
		
		//Send Welcome message to player
		$this->client->query('ChatSendServerMessageToLogin', '$06f» $fffWelcome '.$this->playerList[$login]['NickName'].'$z$s$fff on '.$servername.$newline.'$z$s$06f» $fffThis Server is running with $f51S$ffftunters$f51C$fffontrol$fff ('.SC_Version.' )'.$newline.'$06f» $fffHave fun!', $login);  
		console('New '.str_replace('$o', '', $player_rank).' ' . $login  . ' connected! IP: '.$this->playerList[$login]['IPAddress'].'');
		
		//Get Country
		$continent 	= explode('|', $this->playerList[$login]['Path'])[1];
		$country 	= explode('|', $this->playerList[$login]['Path'])[2];
				
		$sql = mysqli_query($db, "SELECT * FROM players WHERE playerlogin='".$login."' LIMIT 1");
		
		//Insert Player into the database or update it's data
		if(!$row = $sql->fetch_object())
		{
			$sql = mysqli_query($db, "INSERT INTO `players` (id, playerlogin, nickname, path, lastconnect, continent, country, connections) VALUES ('', '".mysqli_real_escape_string($db, $login)."', '".mysqli_real_escape_string($db, $this->playerList[$login]['NickName'])."', '".mysqli_real_escape_string($db, $this->playerList[$login]['Path'])."', '".time()."', '".$continent."', '".$country."', '1')");
		}
		else{
			//Get Connections			
			$connections = $row->connections;
			$connections += 1;
		
			//Update Data
			$sql = mysqli_query($db, "UPDATE `players` SET nickname = '".mysqli_real_escape_string($db, $this->playerList[$login]['NickName'])."', lastconnect = '".time()."', continent = '".mysqli_real_escape_string($db, $continent)."', country = '".mysqli_real_escape_string($db, $country)."', path = '".mysqli_real_escape_string($db, $this->playerList[$login]['Path'])."', connections = '".$connections."' WHERE playerlogin = '".mysqli_real_escape_string($db, $login)."'");
		}
		
		//Create welcome window
		if($settings['StartWindow'] == 'true') {
			global $window;
				
			$window->init();
			$window->title('$fffWelcome on $z$o$fff'.$servername.'$z$fff!');
			$window->size('60', '');
			$window->close(false);
				
			$content = $settings['Text_StartWindow'];
			$content = str_replace('{player}', $this->playerList[$login]['NickName'].'$z$fff', $content);
			$content = str_replace('{server}', $servername.'$z$fff', $content);
			$content = str_replace('FoxControl', '$o$f51S$ffftunts $f51C$fffontrol$o', $content);
			$content = explode('{newline}', $content);
				
			for($i = 0; isset($content[$i]); $i++) {
				$window->content($content[$i]);
			}
				
			$window->addButton('Ok', '20', true);
			$window->show($login);
		}
	}
	
	//PLAYER DISCONNECT
	public function playerdisconect($playerdata){
		global $db, $settings;
		
		//Get PlayerList
		$this->getPlayerList();
		
		$color_left = $settings['Color_Left'];
		$login = $playerdata[0];
		
		//Update Player data (timeplayed)
		$sql = mysqli_query($db, "SELECT * FROM players WHERE playerlogin = '".mysqli_real_escape_string($db, $login)."'");
		
		if($row = $sql->fetch_object()) {
			$nickname = $row->nickname;
			
			if(isset($this->playerList[$login]['timePlayed']) && $this->playerList[$login]['timePlayed'] > 0) {
				$timePlayedCurrent = time()-$this->playerList[$login]['timePlayed'];
				$timePlayed = $row->timeplayed + $timePlayedCurrent;
			} else {
				$timePlayed = $row->timeplayed;
			}
			
			$sql2 = mysqli_query($db, "UPDATE players SET timeplayed='".mysqli_real_escape_string($db, $timePlayed)."' WHERE playerlogin='".mysqli_real_escape_string($db, $login)."'");
		}
		
		$player_rank = $this->getPlayerRankName($login);

		//If disconnected player is FoxTeam Member
		if($login == 'jensoo7' OR $login == 'matrix142'){
			$player_rank .= ' '.$settings['Color_Default'].'and '.$color_left.'F$fffox'.$color_left.' T$fffeam '.$color_left.'M$fffember$o';
		}
		
		//If message left is true
		if($settings['Message_PlayerLeft'] == true) {
			if(!isset($nickname)) {
				if(isset($this->playerList[$login]['NickName'])) {
					$nickname = $this->playerList[$login]['NickName'];
				} else {
					$nickname = 'Undefined';
				}
			}
		
			$left_message = $settings['message_left'];
			$replace = array('{rank}', '{nickname}');
			$replace2 = array($player_rank, $nickname);
			
			$left_message = str_replace($replace, $replace2, $left_message);
			
			//Send left message
			$this->client->query('ChatSendServerMessage', $left_message);
		}
		
		$this->updatePlayerList($login, true);
		
		//Insert left message into log file
		console('Player '.$login.' left the game');
	}
	
	
	/* Update Current Map */
	
	/* UPDATE MAP */
	public function updateMap($map)
	{
		global $db;
		
		$maptype = explode('\\', $map["MapType"])[1];
									
		/* Insert map in database */
		$sql = mysqli_query($db, "SELECT * FROM `maps` WHERE UId = '".$map["UId"]."'");
		
		if (!$sql->num_rows)
			$sql = mysqli_query($db, "INSERT INTO `maps` (UId, Login, Name, MapType, Enviro) VALUES (
									'".mysqli_real_escape_string($db, $map["UId"])."',
									'".$map["Author"]."',
									'".mysqli_real_escape_string($db, $map["Name"])."',
									'".$maptype."',
									'".$map["Environnement"]."'
									)");
		else
			$sql = mysqli_query($db, "UPDATE `maps` SET 
									Login='".$map["Author"]."',
									Name='".mysqli_real_escape_string($db, $map["Name"])."',
									MapType='".$maptype."',
									Enviro='".$map["Environnement"]."'
									WHERE UId='".$map["UId"]."'"
									);
	}
	
	public function updateMapProperties($map)
	{
		global $db;
		
		$sql = mysqli_query($db, "SELECT * FROM `maps` WHERE UId='".$this->CurrentMap["UId"]."'");
		
		if ($sql->num_rows)
			$sql = mysqli_query($db, "UPDATE `maps` SET 
								Car='".$map->Car."',
								MapCar='".$map->MapCar."',
								TimeLimit='".$map->TimeLimit."',
								ScoreToReach='".$map->ScoreToReach."'
								WHERE UId='".$this->CurrentMap["UId"]."'"
								);
									
	}
	
	
	/* UPDATE PLAYER LIST */
	public function updatePlayerList($login, $unset = false) {
		global $settings;
	
		if($login != $settings['ServerLogin']) {
			if($unset == false) {
				$this->client->query('GetDetailedPlayerInfo', $login);
				$playerInfo = $this->client->getResponse();
		
				$this->playerList[$login] = array();
				$this->playerList[$login]['NickName'] = $playerInfo['NickName'];
				$this->playerList[$login]['Path'] = $playerInfo['Path'];
				$this->playerList[$login]['LadderStats'] = $playerInfo['LadderStats']['PlayerRankings'][0]['Ranking'];
				$this->playerList[$login]['IPAddress'] = $playerInfo['IPAddress'];
				$this->playerList[$login]['timePlayed'] = time();	
			} else {
				if(isset($this->playerList[$login])) {
					unset($this->playerList[$login]);
				}
			}
		}
	}
	
	/* GET PLAYER LIST */
	public function getPlayerList() {
		return $this->playerList;
	}
	
	/*
		GET GAMEMODE
	*/
	public function getGameMode() {
		$this->client->query('GetGameMode');
		$gameMode = $this->client->getResponse();
		
		if($gameMode == 0) $gameMode = 'script';
		if($gameMode == 1) $gameMode = 'rounds';
		if($gameMode == 2) $gameMode = 'timeattack';
		if($gameMode == 3) $gameMode = 'team';
		if($gameMode == 4) $gameMode = 'laps';
		if($gameMode == 5) $gameMode = 'cup';
		if($gameMode == 6) $gameMode = 'stunts';
	
		if($gameMode == 'script') {
			$this->client->query('GetModeScriptInfo');
			$scriptInfo = $this->client->getResponse();
			$gameMode = $scriptInfo['Name'];
		
			if($gameMode == '<in-development>') {
				include_once('include/gbxdatafetcher.inc.php');
	
				$this->client->query('GetMapsDirectory');
				$mapDir = $this->client->getResponse();
	
				//Getting current MapType
				$this->client->query('GetCurrentMapInfo');
				$mapInfo = $this->client->getResponse();
				$fileName = $mapInfo['FileName'];
		
				$path = $mapDir.$fileName;
			
				$gbx = new GBXChallengeFetcher($path, true);
		
				$gameMode = $gbx->parsedxml['DESC']['MAPTYPE'];
			}
		
			$gameMode = str_replace('TrackMania\\', '', $gameMode);
			$gameMode = str_replace('Trackmania\\', '', $gameMode);
			$gameMode = str_replace('Multi', '', $gameMode);
			$gameMode = str_replace('.Script.txt', '', $gameMode);
		
			if($gameMode == 'BattleWaves') {
				$gameMode = 'Battle';
			}
		}
		
		return $gameMode;
	}
	
	//FORMAT TIME
	public function formattime($time_to_format){

		//FORMAT TIME
		$formatedtime_minutes = floor($time_to_format/(1000*60));
		$formatedtime_seconds = floor(($time_to_format - $formatedtime_minutes*60*1000)/1000);
		$formatedtime_hseconds = substr($time_to_format, strlen($time_to_format)-3, 2);
		$formatedtime = sprintf('%02d:%02d.%02d', $formatedtime_minutes, $formatedtime_seconds, $formatedtime_hseconds);
	
		return $formatedtime;

	}
	
	//FORMAT TIME HOUR
	public function formattime_hour($time_to_format){

		//FORMAT TIME
		$formatedtime_houres = floor($time_to_format/3600);
	
		return $formatedtime_houres.'h';

	}
	
	//FORMAT TIME MINUTE
	public function formattime_minute($time_to_format) {
		//FORMAT TIME
		$formatedtime_minutes = floor($time_to_format/60);
	
		return $formatedtime_minutes.'min';	
	}
	
	//CHECK IF PLAYER IS ADMIN
	public function is_admin($player_to_check){
		global $db;
		
		$sql = "SELECT * FROM `admins` WHERE playerlogin = '".mysqli_real_escape_string($db, $player_to_check)."'";
		if($mysql = mysqli_query($db, $sql)){
			if($admin_rights = $mysql->fetch_object()){
				return true;	
			}
			else return false;
		}
		else return false;
	}
	
	//KICK PLAYER
	public function player_kick($player_to_kick, $kickmessage, $CommandAuthor){
		$control->client = $this->client;
		global $db, $settings;
		
		$sql = "SELECT * FROM `admins` WHERE playerlogin = '".mysqli_real_escape_string($db, $CommandAuthor['Login'])."'";
		$mysql = mysqli_query($db, $sql);
		
		if($admin_rights = $mysql->fetch_object()){
			if($admin_rights->rights==1){
				$Admin_Rank = $settings['Name_Operator'];;
			}
			elseif($admin_rights->rights==2){
				$Admin_Rank = $settings['Name_Admin'];;
			}
			elseif($admin_rights->rights==3){
				$Admin_Rank = $settings['Name_SuperAdmin'];;
			}
			else $Admin_Rank = '';
		}
			
		$control->client->query('GetDetailedPlayerInfo', $player_to_kick);
		$kickedplayer = $control->client->getResponse();
			
	    if($kickmessage==true){
			$color_kick = $settings['Color_Kick'];
			$control->client->query('ChatSendServerMessage', $color_kick.'-> '.$Admin_Rank.' $fff'.$CommandAuthor['NickName'].'$z$s '.$color_kick.'kicked $fff'.$kickedplayer['NickName'].'$z$s '.$color_kick.'!');
		}
		$control->client->query('Kick', $kickedplayer['Login']);
	}
		
	//IGNORE PLAYER
	public function player_ignore($player_to_ignore, $ignoremessage, $CommandAuthor){ //function to ignore a player. The first parameter is the login of the player. The others are optional. If the secound parameter = true, then write the script a message in the chat. The third parameter is the Nickname of the player who ignored the player (only when message = true)
		global $db, $settings;
		
		$sql = "SELECT * FROM `admins` WHERE playerlogin = '".mysqli_real_escape_string($db, $CommandAuthor['Login'])."'";
		$mysql = mysqli_query($db, $sql);
		
		if($admin_rights = $mysql->fetch_object()){
			if($admin_rights->rights==1){
				$Admin_Rank = $settings['Name_Operator'];
			}
			elseif($admin_rights->rights==2){
				$Admin_Rank = $settings['Name_Admin'];
			}
			elseif($admin_rights->rights==3){
				$Admin_Rank = $settings['Name_SuperAdmin'];
			}
			else $Admin_Rank = '';
		}
		
		$this->client->query('GetDetailedPlayerInfo', $player_to_ignore);
		$ignoredplayer = $this->client->getResponse();
		
		$this->client->query('GetIgnoreList', 1000, 0);
		$ignore_list = $this->client->getResponse();
		
		$curr_ignore_id = 0;
		$player_in_ignore_list = false;
		while(isset($ignore_list[$curr_ignore_id])){
			if($ignore_list[$curr_ignore_id]['Login'] == trim($ignoredplayer['Login'])){
				$player_in_ignore_list = true;
				break;
			}
			$curr_ignore_id++;
		}
		
		if($player_in_ignore_list==true){
			if($ignoremessage==true){
				$color_ignore = $settings['Color_Ignore'];
				$this->client->query('ChatSendServerMessage', $color_ignore.'-> '.$Admin_Rank.' $fff'.$CommandAuthor['NickName'].'$z$s '.$color_ignore.'unignored $fff'.$ignoredplayer['NickName'].'$z$s '.$color_ignore.'!');
			}
			$this->client->query('UnIgnore', $ignoredplayer['Login']);
		}
		else{
			if($ignoremessage==true){
				$color_ignore = $settings['Color_Ignore'];
				$this->client->query('ChatSendServerMessage', $color_ignore.'-> '.$Admin_Rank.' $fff'.$CommandAuthor['NickName'].'$z$s '.$color_ignore.'ignored $fff'.$ignoredplayer['NickName'].'$z$s '.$color_ignore.'!');
			}
			$this->client->query('Ignore', $ignoredplayer['Login']);
		}
	}
	
	//CHAT WITH NICK FROM OTHER PLAYER
	public function chat_with_nick($chat_to_write, $chat_nick){
		$this->client->query('GetDetailedPlayerInfo', $chat_nick);
		$chat_nick = $this->client->getResponse();
		
		$chat_nick = $chat_nick['NickName'];
		
		$this->client->query('ChatSendServerMessage', $chat_nick.'$z$s$06f» $fff'.$chat_to_write);
	}
	
	//RGB DECODE STRING
	public function rgb_decode($string){
		$string = str_replace('$o', '', $string);
		$string = str_replace('$s', '', $string);
		$string = str_replace('$n', '', $string);
		$string = str_replace('$i', '', $string);
		$string = str_replace('$w', '', $string);
		$string = str_replace('$t', '', $string);
		$string = str_replace('$z', '', $string);
		$string = str_replace('$g', '', $string);
		$string = str_replace('$l', '', $string);
		$string = str_replace('$h', '', $string);
		$string = preg_replace('/\$(0|1|2|3|4|5|6|7|8|9|a|b|c|d|e|f)(0|1|2|3|4|5|6|7|8|9|a|b|c|d|e|f)(0|1|2|3|4|5|6|7|8|9|a|b|c|d|e|f)/i', '', $string);
		
		return $string;
	}
	
	public function chat_message($chat_message){
		$this->client->query('ChatSendServerMessage', $chat_message);
	}
	
	public function chat_message_player($chat_message, $player){
		$this->client->query('ChatSendServerMessageToLogin', $chat_message, $player);
	}
	
	public function console($console_message){
		console($console_message);
	}
	
	/************************
	*********EVENTS**********
	************************/
	public function callEvent($EventName, $args = false) {
		global $plugins_cb, $events;
		
		$eventIsCommand = false;
		$commandFound = false;
		$callfunction = 'on'.$EventName;
		
		foreach($events[$callfunction] as $key => $value)
		{
			if($plugins_cb[$value][0]->enabled == true)
			{
				if($EventName == 'ManialinkPageAnswer')
				{
					/* ml ids */
					for($i = 0; $i < count($plugins_cb[$value][2]); $i++)
					{
						if($plugins_cb[$value][2][$i] == $args[2]) $plugins_cb[$value][0]->$callfunction($args);
					}
					
					/* page actions */
					for($i = 0; $i < count($plugins_cb[$value][5]); $i++)
					{						
						if($plugins_cb[$value][5][$i] == explode(':', $args[2])[0] ) $plugins_cb[$value][0]->$callfunction($args);
					}
				}
				else if($EventName == 'Command')
				{
					$eventIsCommand = true;
					for($i = 0; $i < count($plugins_cb[$value][3]); $i++)
					{
						if(trim($plugins_cb[$value][3][$i]) == trim($args[2]))
						{
							$plugins_cb[$value][0]->$callfunction($args);
							$commandFound = true;
						}
					}
				}
				else
				{
					if($args === false) $plugins_cb[$value][0]->$callfunction();
					else $plugins_cb[$value][0]->$callfunction($args);
				}
			}
		}
		
		if($commandFound === false && $eventIsCommand === true) {
			$this->client->query('ChatSendServerMessageToLogin', '$f00» Command not found! Type $fff/help$f00 to get a list of all available commands.', $args[1]);
		}
	}
	
	/* Reboot StuntsControl */
	public function Reboot()
	{
		global $FoxControl_Reboot;
		
		$this->client->query('SendHideManialinkPage');
		$FoxControl_Reboot = true;
	}
	
	//AUTOUPDATE FOXCONTROL
	public function FoxControl_shutdown() {
		global $FoxControl_Shutdown;
		
		$this->client->query('SendHideManialinkPage');
		$FoxControl_Shutdown = true;
	}
	
	//SKIP CHALLENGE
	public function challenge_skip(){
		$this->client->query('NextMap');
	}
	
	/* Main Loop */
	public function StuntsControl(){
		global $db, $FoxControl_Reboot, $FoxControl_Shutdown, $settings;
		
		$defaultcolor = '07b';
		$newline = "\n";
		$servername = $this->client->getResponse();
		$current_time = time();
	
		// Main loop
		while(true)
		{			
			// STOP FOXCONTROL
			if($FoxControl_Reboot == true || $FoxControl_Shutdown == true)
			{
				break;
			}
			
			//EVENT EVERYSECOND
			if($current_time !== time())
			{
				$this->callEvent('EverySecond');
			}
			
			//EVENT TICK
			$this->callEvent('Tick');
			
			//SET CUSTOM UI
			$current_time = time();
			if(!isset($curr_time_30sec)) {
				$curr_time_30sec = time();
			}
			
			if($curr_time_30sec <= time()-30) {
				$curr_time_30sec = time();
			}
			
			//ESTABLISH DATABASE CONNECTION
			if(!isset($database10min)) {
				$database10min = time();
				mysqli_query($db, "SELECT playerlogin FROM `admins` LIMIT 1");
			}
			
			if($database10min <= time()-600) {
				$database10min = time();
				mysqli_query($db, "SELECT playerlogin FROM `admins` LIMIT 1");
			}
			
			
			//GET SERVER CALLBACKS
			$this->client->readCB(1);
			$calls = $this->client->getCBResponses();
			
			if(!empty($calls)) {
				foreach($calls as $call) {
					$cbname = $call[0];
					$cbdata = $call[1];

					// echo $cbname.PHP_EOL;
					// print_r($cbdata);
					
					//$this->client->query('ChatSendServerMessage', $cbname);
					
					//Switch Callbacks
					switch($cbname) {
						//Player Connect
						case 'ManiaPlanet.PlayerConnect':
							global $manialink;
							
							$this->client->query('GetDetailedPlayerInfo', $cbdata[0]);
							$connectedplayer = $this->client->getResponse();
						
							$this->playerconnect($cbdata);
							$this->callEvent('PlayerConnect', $connectedplayer);
						break;
			
						//Player Disconnect
						case 'ManiaPlanet.PlayerDisconnect':
							$this->playerdisconect($cbdata);
							$this->callEvent('PlayerDisconnect', $cbdata);
						break;
			
						//Manialink Page Answer
						case 'ManiaPlanet.PlayerManialinkPageAnswer':
							// print_r($cbdata);
						
							if($cbdata[2] >= 10000 && $cbdata[2] <= 10010) {
								global $window;
								$window->mlAnswer($cbdata);
							}
							
							if(preg_match('/manialink:/', $cbdata[2])) {
								global $manialink;
								$manialink->onManialinkPageAnswer($cbdata);
							}
							
							$this->callEvent('ManialinkPageAnswer', $cbdata);
						break;
			
						//Player Chat
						case 'ManiaPlanet.PlayerChat':
							if($cbdata[0] !== 0)
							{
								if(substr(trim($cbdata[2]), 0, 1) == '/')
								{
									$args = explode(' ', trim($cbdata[2]));
								
									if(!isset($args[1])) $args[1] = '';
								
									$this->callEvent('Command', array(0 => $cbdata[0], 1 => $cbdata[1], 2 => str_replace('/', '', $args[0]), 3 => explode(' ', trim(str_replace($args[0], '', trim($cbdata[2])))), 4 => trim(str_replace(array($args[0], $args[1]), array('', ''), trim($cbdata[2])))));
								}
								else
								{
									$this->callEvent('Chat', $cbdata);
								}
							}
						break;
			
						//Begin Race
						case 'TrackMania.BeginRace':
							$this->callEvent('BeginRace', $cbdata);
						break;
						
						/* Begin Map */
						case 'ManiaPlanet.BeginMap':
							$map = $cbdata[0];
							$this->CurrentMap				= $map;
							$this->CurrentMap["MapType"]	= explode('\\', $map["MapType"])[1];
							$this->updateMap($map);

							$this->callEvent('BeginMap', $cbdata);
						break;
						
						// End Race
						case 'TrackMania.EndRace':
							$this->callEvent('EndRace', $cbdata);
						break;
						
						// Echo
						case 'ManiaPlanet.Echo':
							$this->callEvent('Echo', $cbdata);
						break;
						
						// Server Start
						case 'ManiaPlanet.ServerStart':
							$this->callEvent('ServerStart');
						break;
						
						// Server Stop
						case 'ManiaPlanet.ServerStop':
							$this->callEvent('ServerStop');
						break;
						
			
						// End Map
						case 'ManiaPlanet.EndMap':						
							/*global $chall_restarted_admin;
							
							if($chall_restarted_admin !== true) {
								$this->callEvent('EndMap', $cbdata);
								$this->callEvent('EndChallenge', $cbdata);
							} else {
								$chall_restarted_admin = false;
							}
							
							$this->saveMatchsettings();*/
							$this->callEvent('EndMap', $cbdata);
						break;
						
						//Begin Match
						case 'ManiaPlanet.BeginMatch':
							global $chall_restarted_admin;
						
							$this->callEvent('BeginMatch');
							
							if($chall_restarted_admin == true) {							
								$this->callEvent('BeginMap', $cbdata);
								$chall_restarted_admin = false;
							}
						break;
						
						//End Match
						case 'ManiaPlanet.EndMatch':
							global $chall_restarted_admin, $timeEndMatchTriggered;
							
							$trigger = false;
							
							if(!isset($timeEndMatchTriggered)) {
								$timeEndMatchTriggered = time();
								$trigger = true;
							}
							
							if($timeEndMatchTriggered < (time()-5)) {
								$timeEndMatchTriggered = time();
								$trigger = true;
							}
							
							if($trigger == true) {							
								if($chall_restarted_admin !== true) {								
									$this->client->query('GetCurrentMapInfo');
									$mapInfo = $this->client->getResponse();
								
									$this->client->query('GetCurrentRanking', 200, 0);
									$ranking = $this->client->getResponse();
								
									$this->callEvent('EndMap', array(0 => $ranking, 1 => $mapInfo));
									$this->callEvent('EndMatch', $cbdata);
								}
							
								$this->saveMatchsettings();
							}
						break;
						
						//Begin Round
						case 'ManiaPlanet.BeginRound':
							$this->callEvent('BeginRound');
						break;
						
						//End Round
						case 'ManiaPlanet.EndRound':
							$this->callEvent('EndRound');
						break;
						
						//Server Status Changed
						case 'ManiaPlanet.StatusChanged':
							$this->CurrentStatus = (int) $cbdata[0];						
							$this->callEvent('StatusChanged', $cbdata);
						break;
						
						//Player Checkpoint
						case 'TrackMania.PlayerCheckpoint':
							$this->callEvent('PlayerCheckpoint', $cbdata);
						break;
						
						//Player Finish
						case 'TrackMania.PlayerFinish':
							$this->callEvent('PlayerFinish', $cbdata);
						break;
						
						//Player Incoherence
						case 'ManiaPlanet.PlayerIncoherence':
							$this->callEvent('PlayerIncoherence', $cbdata);
						break;
						
						//Bill Updated
						case 'ManiaPlanet.BillUpdated':
							$this->callEvent('BillUpdated', $cbdata);
						break;
						
						//Tunnel Data Received
						case 'ManiaPlanet.TunnelDataReceived':
							$this->callEvent('TunnelDataReceived', $cbdata);
						break;
						
						// Map List Modified
						case 'ManiaPlanet.MapListModified':
							$this->callEvent('MapListModified', $cbdata);
						break;
						
						//Player Info Changed
						case 'ManiaPlanet.PlayerInfoChanged':
							$this->callEvent('PlayerInfoChanged', $cbdata);
						break;
						
						//Manual Flow Control Transition
						case 'ManiaPlanet.ManualFlowControlTransition':
							$this->callEvent('ManualFlowControlTransition', $cbdata);
						break;
						
						//Vote Updated
						case 'ManiaPlanet.VoteUpdated':
							$this->callEvent('VoteUpdated', $cbdata);
						break;
						
						//Rules Script Callback
						case 'ManiaPlanet.ModeScriptCallback':						

							if ($cbdata[0] == 'OnMapParameters') $this->updateMapProperties(json_decode($cbdata[1]));
							
							$this->callEvent('ModeScriptCallback', $cbdata);
					}
				}
			}
		    
			// Uncomment this for debugging
			/*if($this->client->isError()) {
				console('Server error: '.$this->client->getErrorCode().': '.$this->client->getErrorMessage());
				$this->client->resetError(); 
			}*/
			
			usleep(100000);
		}
		
		// REBOOT FOXCONTROL
		if(isset($FoxControl_Reboot))
		{
			if($FoxControl_Reboot == true)
			{
				if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') echo exec("control.cmd start");
				else echo exec("sh control.sh start");
				
				die();
			}
		}
		
		// SHUTDOWN FOXCONTROL
		if(isset($FoxControl_Shutdown) && $FoxControl_Shutdown == true)
		{			
			die();
		}
		
		//$this->client->Terminate(); 
		//console('Shutting server down..');
	}

	//CONNECT TO SERVER
	public function connect($Ip, $Port, $AuthLogin, $AuthPassword) {
		//If cant connect to the server...
		if (!$this->client->InitWithIp(strval($Ip), intval($Port))) {
			echo'ERROR: Cannot connect to server! Used IP: '.$Ip.' Used Port: '.$Port.nz;
		} else {
			//If cant authenticate with superadmin account...
			if(!$this->client->query('Authenticate', strval($AuthLogin), strval($AuthPassword))){
				echo'ERROR: Invalid Password!'.nz;
			}else{
				return true;  
			}
		}
	}
	
	//FORMAT TIME
	public function format_time($time_to_format){
	    $formatedtime_minutes = floor($time_to_format/60000);
	    $formatedtime_seconds = floor(($time_to_format - $formatedtime_minutes*60*1000)/1000);
	    $formatedtime_hseconds = substr($time_to_format, strlen($time_to_format)-3, 2);
		$formatedtime_mseconds = substr($time_to_format, strlen($time_to_format)-1, 1);
	    $formatedtime = sprintf('%02d:%02d.%02d.%01d', $formatedtime_minutes, $formatedtime_seconds, $formatedtime_hseconds, $formatedtime_mseconds);
	    
		if($formatedtime_minutes<'0'){
		    $formatedtime = '???';
		}
		
	    return $formatedtime;
	}
	
	
	
	//AUTOSAVE MATCHSETTINGS AT END OF A ROUND
	public function saveMatchsettings() {
		global $settings, $round;
		
		if($settings['autosave_matchsettings'] != '0') {
			if(!isset($round)) {
				$round = 0;
				
				$filename = 'MatchSettings/'.$settings['matchsettings_filename'];
				$this->client->query('SaveMatchSettings', $filename);
				
				console('MatchSettings saved to '.$filename);
			}
			
			if($round == ((int) $settings['autosave_matchsettings'])) {
				$round = 0;
				
				$filename = 'MatchSettings/'.$settings['matchsettings_filename'];
				$this->client->query('SaveMatchSettings', $filename);
				
				console('MatchSettings saved to '.$filename);
			}
			
			if($round != ((int) $settings['autosave_matchsettings'])) {
				$round++;
			}
		}
	}
	
	
	
}

$control = new control;
$control->run();
?> 
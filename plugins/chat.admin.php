<?php
//* chat.admin.php - Admin Chat Commands
//* Version:   0.5
//* Coded by:  cyrilw, matrix142
//* Copyright: FoxRace, http://www.fox-control.de

global $chall_restarted_admin;
$chall_restarted_admin = false;

class chat_admin extends FoxControlPlugin {
	public $trackdir = 'Downloaded';
	public $commandsPerPage = 17;
	public $helpUsers = array();
	public $scriptSettings;
	
	public function onStartUp() {
		$this->registerCommand('adminhelp', 'Shows the helpwindow for the admin commands.', false);
		$this->registerCommand('set', 'Sets different Server details. Type $s/adminhelp set$s for more details.', true);
		$this->registerCommand('add', 'Adds new Admins, Tracks and more. Type $s/adminhelp add$s for more details.', true);
		$this->registerCommand('remove', 'Removes Admins, Tracks and more. Type $s/adminhelp remove$s for more details.', true);
		$this->registerCommand('save', 'Saves the matchsettings. Type $s/adminhelp save$s for more details.', true);
		$this->registerCommand('kick', 'Kicks the specified player. /kick <login>', true);
		$this->registerCommand('warn', 'Warns the specified player. /warn <login>', true);
		$this->registerCommand('ban', 'Bans the specified player. /ban <login>', true);
		$this->registerCommand('blacklist', 'Blacklists the specified player. /blacklist <login>', true);
		$this->registerCommand('unblacklist', 'Removes the specified player from the blacklist. /unblacklist <login>', true);
		$this->registerCommand('unban', 'Unbans the specified player. /unban <login>', true);
		$this->registerCommand('ignore', 'Ignores a players chat message. /ignore <login>', true);
		$this->registerCommand('unignore', 'Unignores a player', '/unignore <login>', true);
		$this->registerCommand('reboot', 'Reboots FoxControl.', true);
		$this->registerCommand('skip', 'Skips the current map.', true);
		$this->registerCommand('restart', 'Restarts the current map.', true);
		$this->registerCommand('res', 'Restarts the current map. Same command as $s/restart$s.', true);
		$this->registerCommand('replay', 'Queues the current map for restart.', true);
		$this->registerCommand('endround', 'Forces round end.', true);
		$this->registerCommand('planets', 'Shows the planets amount.', true);
		$this->registerCommand('pay', 'Pays planets to the specified login. /pay <amount> <login>', true);
		$this->registerCommand('mode', 'Sets the game mode to the specified mode. Type in $s/adminhelp mode$s for more details.', true);
		$this->registerCommand('forcespec', 'Forces a player into Spectator mode.  /forcespec <login>', true);
		$this->registerCommand('forceplayer', 'Forces a player into Player mode. /forceplayer <login>', true);
		$this->registerCommand('scriptsettings', 'Set Scriptsettings. $i/scriptsettings$i displays a list of available settings', true);
		$this->registerCommand('forcemap', 'Sets the specified map as next map and skips current. /forcemap <mapid>', true);
		$this->registerCommand('chatnick', 'Chat with an others Nickname. /chatnick <login> <message>', true);
		//$this->registerCommand('update', 'Updates FoxControl to the newest version', true);
		$this->registerCommand('callvote', 'Set Timeout for Callvotes. $i/callvote <timeout_in_seconds>$i', true);
		$this->registerCommand('mixmap', 'Mix the Maplist. $i/mixmap$i', true);
		
		$this->registerMLIds(1);
		
		$this->name = 'Admin chat';
		$this->author = 'matrix142 & cyrilw';
		$this->version = '0.6';
		
		$this->getScriptSettings();
	}
	public function onCommand($args) {
		global $settings;
		$this->instance()->client->query('GetDetailedPlayerInfo', $args[1]);
		$CommandAuthor = $this->instance()->client->getResponse();
		$rights = $this->getRights($args[1]);
		if($rights[0] == 0) {
			$this->sendError($CommandAuthor['Login']);
			return;
		}
		else if($rights[0] == 1) require('include/op_rights.php');
		else if($rights[0] == 2) require('include/admin_rights.php');
		else if($rights[0] == 3) require('include/superadmin_rights.php');
		if($args[2] == 'adminhelp') {
			if(!empty($args[3][0])) {
				if(is_numeric($args[3][0])) $site = ($args[3][0]-1);
				else $command = $args[3][0];
			} else $site = 0;
			
			if(isset($site)) {
				$this->helpUsers[$args[1]] = $site;
				
				$window = $this->window;
				$window->init();
				$window->title('$800A$fffdmin $800h$fffelp');
				$window->close(true);
				$window->displayAsTable(true);
				$window->size(70, '');
				$window->posY('36.8');
				$window->target('onButtonPressed', $this);
				
				$window->content('<td width="15">Command</td><td width="2"></td><td width="50">Description</td>');
				$window->content(' ');
				
				$help = $this->instance()->getCommands('admin');
				$commands = 0;
				
				for($i = ($site * $this->commandsPerPage); $i < count($help); $i++) {
					$window->content('<td width="15">/'.$help[$i][0].'</td><td width="2"></td><td width="50">'.$help[$i][1].'</td>');
					$commands++;
					if($commands >= $this->commandsPerPage) break;
				}
				if($site > 0) $window->addButton('<', 7, false);
				else $window->addButton('', 7, false);
				$window->addButton('Close', 15, true);
				if(($i+1) < count($help)) $window->addButton('>', 7, false);
				else $window->addButton('', 7, false);
				$window->show($args[1]);
			} else if(isset($command)) {
				if($command == 'set') {
					$window = $this->window;
					$window->init();
					$window->title('Adminhelp - Command: set');
					$window->close(true);
					
					$window->size(70, '');
					$window->posY('36.8');
					
					$window->content('You can set different things with the $s/set$s command:');
					$window->content('$o/set serverpw <pw>$o Sets the password for players.');
					$window->content('$o/set specpw <pw>$o Sets the password for spectators.');
					$window->content('$o/set servername <name>$o Sets the servername.');
					$window->content('$o/set comment <comment>$o Sets the comment of this server.');
					
					$window->addButton('OK', 15, true);
					
					$window->show($args[1]);
				} else if($command == 'add') {
					$window = $this->window;
					$window->init();
					$window->title('Adminhelp - Command: add');
					$window->close(true);
					
					$window->size(70, '');
					$window->posY('36.8');
					
					$window->content('You can add different things with the $s/add$s command:');
					$window->content('$o/add superadmin <login>$o Adds a new Superadmin.');
					$window->content('$o/add admin <login>$o Adds a new Admin.');
					$window->content('$o/add op <login>$o Adds a new Operator.');
					$window->content('$o/add map <id>$o Adds a new Map with the specified MX-Id.');
					
					$window->addButton('OK', 15, true);
					
					$window->show($args[1]);
				} else if($command == 'remove') {
					$window = $this->window;
					$window->init();
					$window->title('Adminhelp - Command: remove');
					$window->close(true);
					
					$window->size(70, '');
					$window->posY('36.8');
					
					$window->content('You can remove different things with the $s/remove$s command:');
					$window->content('$o/remove admin <login>$o Removes the Admin/Superadmin/OP with the specified login.');
					$window->content('$o/remove track <id|current>$o Removes a track with the specified id..');
					$window->content('..or if you write \'current\', it will remove the current map.');
					
					$window->addButton('OK', 15, true);
					
					$window->show($args[1]);
				} else if($command == 'save') {
					$window = $this->window;
					$window->init();
					$window->title('Adminhelp - Command: save');
					$window->close(true);
					
					$window->size(70, '');
					$window->posY('36.8');
					
					$window->content('You can save the matchsettings with the $s/save$s command:');
					$window->content('$o/save matchsetting <filename>$o Saves the matchsettings to the specified filename.');
					
					$window->addButton('OK', 15, true);
					
					$window->show($args[1]);
				} else if($command == 'mode') {
					$window = $this->window;
					$window->init();
					$window->title('Adminhelp - Command: mode');
					$window->close(true);
					
					$window->size(70, '');
					$window->posY('36.8');
					
					$window->content('You can set the game mode with the $s/mode$s command:');
					$window->content('$o/mode script$o Sets game mode to script.');
					$window->content('$o/mode rounds <points> <forceroundlaps> <pointsnewrules>$o Sets game mode to rounds.');
					$window->content('$o/mode timeattack <timelimit>$o Sets game mode to timeattack.');
					$window->content('$o/mode team <points> <maxpoints> <pointsnewrules>$o Sets game mode to team.');
					$window->content('$o/mode laps <numberoflaps> <timelimit>$o Sets game mode to laps.');
					$window->content('$o/mode cup <points> <roundsperchallenge> <numberwinners> <warmupduration>$o Sets game mode to cup.');
					
					$window->addButton('OK', 15, true);
					
					$window->show($args[1]);
				}
			}
		}
		
		//CHAT WITH NICK
		else if($args[2] == 'chatnick') {
			if(isset($args[3][0]) && isset($args[3][1])) {			
				$this->instance()->chat_with_nick($args[4], $args[3][0]);
			}
		}
		
		//CHANGE GAME MODE
		else if($args[2] == 'mode') {
			if(isset($args[3][0])) {
				$gameInfosArray = array();
			
				$this->instance()->client->query('GetGameInfos');
				$gameInfos = $this->instance()->client->getResponse();
				$gameInfosCurrent = $gameInfos['CurrentGameInfos'];
			
				foreach($gameInfosCurrent as $key => $value) {
					$gameInfosArray[$key] = $value;
				}
			
				//ROUNDS MODE
				if($args[3][0] == 'rounds') {
					$gameInfosArray['GameMode'] = 1;
					
					if(isset($args[3][1]) && isset($args[3][2])) {
						$gameInfosArray['RoundsForcedLaps'] = (int) $args[3][2];
					
						//USE NEW RULES
						if(isset($args[3][3])) {
							$gameInfosArray['RoundsUseNewRules'] = true;
							$gameInfosArray['RoundsPointsLimitNewRules'] = (int) $args[3][3];
						
							$textNewRules = 'Yes';
						}else {
							$gameInfosArray['RoundsPointsLimit'] = (int) $args[3][1];
							$gameInfosArray['RoundsUseNewRules'] = false;
							$textNewRules = 'No';
						}
						
						$points = $args[3][1];
						$forcedLaps = $args[3][2];
					} else {
						$textNewRules = 'Default';
						$points = 'Default';
						$forcedLaps = 'Default';
					}
					
					$this->instance()->client->query('SetGameInfos', $gameInfosArray);
					
					$this->chat($rights[1].' '.$CommandAuthor['NickName'].'$z$s$f90 set GameMode to Rounds, Points '.$points.', ForcedLaps '.$forcedLaps.', Use new Rules '.$textNewRules.'$z$s$f90!', '$f90');
				}
			
				//TIME ATTACK
				else if($args[3][0] == 'timeattack') {			
					$gameInfosArray['GameMode'] = 2;
					
					if(isset($args[3][1])) {
						$gameInfosArray['TimeAttackLimit'] = ($args[3][1]*1000);
						$timeLimit = $args[3][1];
					} else {
						$timeLimit = 'Default';
					}
					
					$this->instance()->client->query('SetGameInfos', $gameInfosArray);
				
					$this->chat($rights[1].' '.$CommandAuthor['NickName'].'$z$s$f90 set GameMode to TimeAttack, Timelimit '.$timeLimit.' seconds$z$s$f90!', '$f90');
				}
				//TEAM MODE
				else if($args[3][0] == 'team') {
					$gameInfosArray['GameMode'] = 3;
					
					if(isset($args[3][1]) && isset($args[3][2])) {
						$gameInfosArray['TeamPointsLimit'] = (int) $args[3][1];
						$gameInfosArray['TeamMaxPoints'] = (int) $args[3][2];
				
						//USE NEW RULES
						if(isset($args[3][3])) {
							$gameInfosArray['TeamUseNewRules'] = true;
							$gameInfosArray['TeamPointsLimitNewRules'] = (int) $args[3][3];
						
							$textNewRules = 'Yes';
						}else {
							$gameInfosArray['TeamUseNewRules'] = false;
							$textNewRules = 'No';
						}
						
						$points = $args[3][1];
						$maxPoints = $args[3][2];
					} else {
						$points = 'Default';
						$maxPoints = 'Default';
						$textNewRules = 'Default';
					}
					
					$this->instance()->client->query('SetGameInfos', $gameInfosArray);
				
					$this->chat($rights[1].' '.$CommandAuthor['NickName'].'$z$s$f90 set GameMode to Team, Points '.$points.', Max Points '.$maxPoints.', Use new Rules '.$textNewRules.'$z$s$f90!', '$f90');
			
					$this->reloadWidgetPosns = true;
				}
				
				//LAPS MODE
				else if($args[3][0] == 'laps') {
					$gameInfosArray['GameMode'] = 4;
					
					if(isset($args[3][1]) && isset($args[3][2])) {
						$gameInfosArray['LapsNbLaps'] = (int) $args[3][1];
						$gameInfosArray['LapsTimeLimit'] = (int) $args[3][2];
					
						$nbLaps = $args[3][1];
						$timeLimit = $args[3][2];
					} else {
						$nbLaps = 'Default';
						$timeLimit = 'Default';
					}
					
					$this->instance()->client->query('SetGameInfos', $gameInfosArray);
					
					$this->chat($rights[1].' '.$CommandAuthor['NickName'].'$z$s$f90 set GameMode to Laps, Number of Laps '.$nbLaps.', Time Limit '.$timeLimit.'$z$s$f90!', '$f90');
				
					$this->reloadWidgetPosns = true;
				}
				
				//CUP MODE
				else if($args[3][0] == 'cup') {
					
					if(isset($args[3][1]) && isset($args[3][2])) {
						$gameInfosArray['GameMode'] = 5;
						$gameInfosArray['CupPointsLimit'] = (int) $args[3][1];
						$gameInfosArray['CupRoundsPerChallenge'] = (int) $args[3][2];
						$gameInfosArray['CupNbWinners'] = (int) $args[3][3];
						$gameInfosArray['CupWarmUpDuration'] = (int) $args[3][4];
						
						$points = $args[3][1];
						$rounds = $args[3][2];
						$nbWinners = $args[3][3];
					} else {
						$points = 'Default';
						$rounds = 'Default';
						$nbWinners = 'Default';
					}
					
					$this->instance()->client->query('SetGameInfos', $gameInfosArray);
					
					$this->chat($rights[1].' '.$CommandAuthor['NickName'].'$z$s$f90 set GameMode to Cup, Points '.$points.', Rounds '.$rounds.', Number of Winners '.$nbWinners.'$z$s$f90!', '$f90');
				
					$this->reloadWidgetPosns = true;
				}
			}
		}
		
		//SET VARIOUS SETTINGS
		else if($args[2] == 'set') {
			if(!empty($args[3][0])) {
			
				//SERVERPASSWORD
				if($args[3][0] == 'serverpw') {
					if($set_spectatorpw == true){
						$this->instance()->client->query('SetServerPassword', $args[3][1]);
						
						if(isset($args[3][1])) {
							$this->chat($rights[1].' $fff'.$CommandAuthor['NickName'].'$z$s '.$settings['Color_SetPW'].'sets the Serverpassword to $fff'.$args[3][1].'$z$s '.$settings['Color_SetPW'].'!', $settings['Color_SetPW']);
						} else {
							$this->chat($rights[1].' $fff'.$CommandAuthor['NickName'].'$z$s '.$settings['Color_SetPW'].'removed the Serverpassword!', $settings['Color_SetPW']);
						}
					} else $this->sendError($CommandAuthor['Login']);
				
				//SPECTATORPASSWORD
				} else if($args[3][0] == 'specpw') {
					if($set_serverpw == true){
						$this->instance()->client->query('SetServerPasswordForSpectator', $args[3][1]);
						
						if(isset($args[3][1])) {
							$this->chat($rights[1].' $fff'.$CommandAuthor['NickName'].'$z$s '.$settings['Color_SetPW'].'sets the Spectatorpassword to $fff'.$args[3][1].'$z$s '.$settings['Color_SetPW'].'!', $settings['Color_SetPW']);
						} else {
							$this->chat($rights[1].' $fff'.$CommandAuthor['NickName'].'$z$s '.$settings['Color_SetPW'].'removed the Spectatorpassword!', $settings['Color_SetPW']);
						}
					}
					else $this->sendError($CommandAuthor['Login']);
					
				//SERVERNAME
				} else if($args[3][0] == 'servername') {
					if($set_servername == true){
						$servername = '';
						for($i = 1; $i < count($args[3]); $i++) $servername .= $args[3][$i].' ';
						
						$this->instance()->client->query('SetServerName', $servername);
						
						$this->instance()->writeInConfig('servername', $servername);
						
						$this->chat($rights[1].' $fff'.$CommandAuthor['NickName'].'$z$s '.$settings['Color_NewServername'].'sets the Servername to $fff'.$servername.'$z$s '.$settings['Color_NewServername'].'!', $settings['Color_NewServername']);
					} else $this->sendError($CommandAuthor['Login']);
					
				//SERVER COMMENT
				} else if($args[3][0] == 'comment') {
					if($set_servercomment == true){
						$servercomment = '';
						for($i = 1; $i < count($args[3]); $i++) $servercomment .= $args[3][$i].' ';
						$this->instance()->client->query('SetServerComment', $servercomment);
						$color_newservername = $settings['Color_NewServername'];
					
						$this->chat($rights[1].' $fff'.$CommandAuthor['NickName'].'$z$s '.$settings['Color_NewServername'].'sets the Servercomment to $fff'.$servercomment.'$z$s '.$settings['Color_NewServername'].'!', $settings['Color_NewServername']);
					} else $this->sendError($CommandAuthor['Login']);
				}
			}
			
		//ADD VARIOUS DATA
		} else if($args[2] == 'add') {
			if(!empty($args[3][0])) {
			
				//SUPERADMIN
				if($args[3][0] == 'superadmin') {
					if($add_new_superadmin == true){
						if(!empty($args[3][1])) {
							$adminAdded = $this->changeRights($args[3][1], 3);
							if($adminAdded !== false) {
								$this->chat($rights[1].' $fff'.$CommandAuthor['NickName'].'$z$s '.$settings['Color_NewAdmin'].'adds $fff'.$adminAdded.'$z$s '.$settings['Color_NewAdmin'].'as a new SuperAdmin!', $settings['Color_NewAdmin']);
							} else $this->chatToLogin($args[1], 'Player \'$fff'.$args[3][1].'$f60\' not found!', 'f60');
						} else $this->chatToLogin($args[1], 'Player \'$fff $f60\' not found!', 'f60');
					} else $this->sendError($CommandAuthor['Login']);
					
				//ADMIN
				} else if($args[3][0] == 'admin') {
					if($add_new_admin == true){
						if(!empty($args[3][1])) {
							$adminAdded = $this->changeRights($args[3][1], 2);
							if($adminAdded !== false) {
								$this->chat($rights[1].' $fff'.$CommandAuthor['NickName'].'$z$s '.$settings['Color_NewAdmin'].'adds $fff'.$adminAdded.'$z$s '.$settings['Color_NewAdmin'].'as a new Admin!', $settings['Color_NewAdmin']);
							} else $this->chatToLogin($args[1], 'Player \'$fff'.$args[3][1].'$f60\' not found!', 'f60');
						} else $this->chatToLogin($args[1], 'Player \'$fff $f60\' not found!', 'f60');
					} else $this->sendError($CommandAuthor['Login']);
				
				//OPERATOR
				} else if($args[3][0] == 'op') {
					if($add_new_op == true){
						if(!empty($args[3][1])) {
							$adminAdded = $this->changeRights($args[3][1], 1);
							if($adminAdded !== false) {
								$this->chat($rights[1].' $fff'.$CommandAuthor['NickName'].'$z$s '.$settings['Color_NewAdmin'].'adds $fff'.$adminAdded.'$z$s '.$settings['Color_NewAdmin'].'as a new Operator!', $settings['Color_NewAdmin']);
							} else $this->chatToLogin($args[1], 'Player \'$fff'.$args[3][1].'$f60\' not found!', 'f60');
						} else $this->chatToLogin($args[1], 'Player \'$fff $f60\' not found!', 'f60');
					} else $this->sendError($CommandAuthor['Login']);
				
				//MAP
				} else if($args[3][0] == 'map') {
					if($admin_add_track == true) {
						if(!empty($args[3][1]) AND is_numeric($args[3][1])){
							include_once('include/gbxdatafetcher.inc.php');
						
							$mxid = $args[3][1];
							//Get Data of the Track from ManiaExchange
							$read = simplexml_load_string($this->getDataFromUrl('http://api.mania-exchange.com/tm/maps/'.$mxid.'?format=xml'));
							
							//Set Filename and Trackname
							if(!isset($read->TrackInfo->Name)) {
								$this->chatToLogin($CommandAuthor['Login'], 'The map with ID '.$mxid.' does not exist or MX is down!', 'f60');
								return;
							}
							
							$filename = $read->TrackInfo->Name.'.Map.Gbx';
							$trackname = $read->TrackInfo->Name;
							
							//Get the Trackfile
							$trackfile = $this->getDataFromUrl('http://tm.mania-exchange.com/tracks/download/'.$mxid.'');	
						
							if(!empty($trackfile) && !empty($filename)) {
								//Get Map Directory
								$this->instance()->client->query('GetMapsDirectory');
								$trackdir = $this->instance()->client->getResponse();							
									
								//Write Trackfile to the server
								$dir = $trackdir.$this->trackdir.'/'.$filename;
								file_put_contents($dir, $trackfile);
								
								//Insert Map
								$this->instance()->client->query('InsertMap', $dir);
								$this->chat($rights[1].' '.$CommandAuthor['NickName'].'$z$s$0f0 added $fff'.$trackname.'$0f0 (ID: $fff'.$mxid.'$0f0) from MX!', '0f0');
							} else $this->chatToLogin($CommandAuthor['Login'], 'The map with ID '.$mxid.' does not exsit or MX is down!', 'f60');
						} else $this->chatToLogin($CommandAuthor['Login'], 'The ID must be numeric!', 'f60');
					} else $this->sendError($CommandAuthor['Login']);
				}
			}
			
		//REMOVE VARIOUS DATA
		} else if($args[2] == 'remove') {
			if(!empty($args[3][0])) {
			
				//ADMIN
				if($args[3][0] == 'admin') {
					if($remove_superadmin == true){
						if(!empty($args[3][1])) {
							$ralogin = trim($args[3][1]);
							$sql = "SELECT * FROM `admins` WHERE playerlogin = '".mysqli_real_escape_string($this->db, $ralogin)."'";
							$mysql = mysqli_query($this->db, $sql);
							if($raplayer = $mysql->fetch_object()){
								$sql = "DELETE FROM `admins` WHERE playerlogin = '".mysqli_real_escape_string($this->db, $ralogin)."'";
								$mysql = mysqli_query($this->db, $sql);
								$this->chat($rights[1].' '.$CommandAuthor['NickName'].'$z$s'.$settings['Color_RemoveAdmin'].' removed Superadmin $fff'.$ralogin.$settings['Color_RemoveAdmin'].' !', $settings['Color_RemoveAdmin']);
							} else $this->chatToLogin($args[1], 'Player \'$fff'.$args[3][1].'$f60\' not found!', 'f60');
						} else $this->chatToLogin($args[3][1], 'Player \'$fff $f60\' not found!', 'f60');
					} else $this->sendError($CommandAuthor['Login']);
				
				//MAP
				} else if($args[3][0] == 'map') {
					if($admin_delete_track == true){
						if(!empty($args[3][1])){
							$trackid = $args[3][1];
							
							//DELETE TRACK WITH ID
							if(is_numeric($trackid)){
								$trackid--;
								global $challenges;
								if(isset($challenges)){
									$remove_chall = $challenges[$trackid];
								
									$this->chat($rights[1].' '.$CommandAuthor['NickName'].'$z$s$f90 removed $fff'.$remove_chall['Name'].'$z$s$f90!', 'f90');
									$this->instance()->client->query('RemoveMap', $remove_chall['FileName']);
								
									$this->instance()->write_challenges();
								} else $this->chatToLogin($CommandAuthor['Login'], 'Plugin \'plugin.challenges.php\' isn\'t enabled!', 'f60');	
							}
							
							//DELETE CURRENT TRACK
							elseif($trackid=='current'){
								$this->instance()->client->query('GetCurrentMapInfo');
								$remove_chall = $this->instance()->client->getResponse();
								
								$this->chat($rights[1].' '.$CommandAuthor['NickName'].'$z$s$f90 removed $fff'.$remove_chall['Name'].'$z$s$f90!', 'f90');
								$this->instance()->client->query('RemoveMap', $remove_chall['FileName']);
							
							} else $this->chatToLogin($CommandAuthor['Login'], 'Invalid Map-ID!', 'f60');
						}
					} else $this->sendError($CommandAuthor['Login']);
				}
			}
		
		//FORCEMAP
		} else if($args[2] == 'forcemap') {
			if($args[3][0] != '') {
				if($force_map == true) {
					$id = $args[3][0] - 1;
				
					$this->instance()->client->query('GetMapList', 1000, 0);
					$mapList = $this->instance()->client->getResponse();
					
					if(isset($mapList[$id]['FileName'])) {
						$this->instance()->client->query('ChooseNextMap', $mapList[$id]['FileName']);
						$this->instance()->challenge_skip();
					
						$this->chat($rights[1].' '.$CommandAuthor['NickName'].'$z$s$f90 forced Map $fff'.$mapList[$id]['Name'].'$z$s$f90!', 'f90');
					} else {
						$this->chatToLogin($CommandAuthor['Login'], 'Invalid Map-ID!', 'f60');
					}
				} else $this->sendError($CommandAuthor['Login']);
			}
		
		//SCRIPTSETTINGS
		} else if($args[2] == 'scriptsettings') {
			if($args[3][0] != '') {
				if(isset($args[3][1])) {
					$this->chat($rights[1].' '.$CommandAuthor['NickName'].'$z$s$f90 set Scriptsettings $fff'.$args[3][0].' $f90to $fff'.$args[3][1].'!', 'f90');
				
					if($args[3][1] == 'true') $args[3][1] = true;
					else if($args[3][1] == 'false') $args[3][1] = false;
					else if(intval($args[3][1]) == $args[3][1]) $args[3][1] = (int) $args[3][1];
					
					$this->scriptSettings[$args[3][0]] = $args[3][1];
					$this->instance()->client->query('SetModeScriptSettings', $this->scriptSettings);
					
					$this->getScriptSettings();
				}
			} else {
				$this->getScriptSettings();
			
				$window = $this->window;
				$window->init();
				$window->title('$f33S$fffcriptsettings - /scriptsettings <name> <value>');
				$window->displayAsTable(true);
					
				$window->size(70, '');
				$window->posY('36.8');
				
				$window->addButton('Close', 15, true);
				$window->content('<td width="25">$iName</td><td width="50">$iValue</td>');
				
				foreach($this->scriptSettings as $key => $value) {
					if($value === false) $value = 'false';
					if($value === true) $value = 'true';
					$window->content('<td width="25">'.$key.'</td><td width="50">'.$value.'</td>');
				}
					
				$window->show($args[1]);
			}
		
		//SAVE MATCHSETTINGS
		} else if($args[2] == 'save') {
			if(!empty($args[3][0])) {
				if($args[3][0] == 'matchsettings') {
					if(!empty($args[3][1])) {
						$filename = 'MatchSettings/';
						$first = true;
						for($i = 1; !empty($args[3][$i]); $i++) {
							if($first == false) $filename .= ' ';
							$first = false;
							$filename .= $args[3][$i];
						}
						$this->chatToLogin($args[1], 'Saving the matchsettings to $fff'.$filename.'$0d0...', '0d0');
						$this->instance()->client->query('SaveMatchSettings', $filename);
						$this->chatToLogin($args[1], 'Matchsettings saved!', '0d0');
					}
				}
			}
			
		//KICK PLAYER
		} else if($args[2] == 'kick') {
			if($kick==true){
				$this->instance()->client->query('GetDetailedPlayerInfo', $args[3][0]);
				$kickedplayer = $this->instance()->client->getResponse();
				if(empty($kickedplayer['Login'])) {
					$this->chatToLogin($args[1], 'Player \'$fff'.$args[3][0].'$f60\' not found!', 'f60');
				} else {
					$this->chat($rights[1].' $fff'.$CommandAuthor['NickName'].'$z$s '.$settings['Color_Kick'].'kicked $fff'.$kickedplayer['NickName'].'$z$s '.$settings['Color_Kick'].'!', $settings['Color_Kick']);
					$this->instance()->client->query('Kick', $args[3][0]);
				}
			} else $this->sendError($CommandAuthor['Login']);
		
		//WARN PLAYER
		} else if($args[2] == 'warn') {
			if($warn==true){
				$this->instance()->client->query('GetDetailedPlayerInfo', $args[3][0]);
				$warnedplayer = $this->instance()->client->getResponse();;
				if(empty($warnedplayer['Login'])) {
					$this->chatToLogin($args[1], 'Player \'$fff'.$args[3][0].'$f60\' not found!', 'f60');
				} else {
					$this->chat($rights[1].' $fff'.$CommandAuthor['NickName'].'$z$s '.$settings['Color_Warn'].'warned $fff'.$warnedplayer['NickName'].'$z$s '.$settings['Color_Warn'].'!', $settings['Color_Warn']);
					
					$window = $this->window;
				
					$window->init();
					$window->title('$f33A$fffdministrative $f33W$fffarning');
					$window->size('60', '');
					$window->close(false);
				
					$window->content('This is an administrative warning!');
					$window->content('What ever you wrote or made is against our server rules.');
					$window->content('An Administrator could kick or ban you next time.');
					$window->content('Please be fair!');
				
					$window->addButton('Ok', '20', true);
					$window->show($args[3][0]);
				}
			} else $this->sendError($CommandAuthor['Login']);
		
		//BAN PLAYER
		} else if($args[2] == 'ban') {
			if($ban==true){
				$sql = "SELECT * FROM `players` WHERE playerlogin = '".trim($args[3][0])."'";
				$mysql = mysqli_query($this->db, $sql);
				if($data = $mysql->fetch_object()){
					$this->chat($rights[1].' $fff'.$CommandAuthor['NickName'].'$z$s '.$settings['Color_Ban'].'banned $fff'.$data->nickname.$settings['Color_Ban'].' !', $settings['Color_Ban']);
					$this->instance()->client->query('Ban', $args[3][0]);
				} else $this->chatToLogin($args[1], 'Player \'$fff'.$args[3][0].'$f60\' not found!', 'f60');
			} else $this->sendError($CommandAuthor['Login']);
		
		//UNBAN PLAYER
		} else if($args[2] == 'unban') {
			if($unban==true){
				$sql = "SELECT * FROM `players` WHERE playerlogin = '".trim($args[3][0])."'";
				$mysql = mysqli_query($this->db, $sql);
				if($data = $mysql->fetch_object()){
					$this->instance()->unban($args[3][0], false, $CommandAuthor, $data);
					$this->chat($rights[1].' $fff'.$CommandAuthor['NickName'].'$z$s '.$settings['Color_Ban'].'unbanned $fff'.$data->nickname.$settings['Color_Ban'].' !', $settings['Color_Ban']);
				} else $this->chatToLogin($args[1], 'Player \'$fff'.$args[3][0].'$f60\' not found!', 'f60');
			} else $this->sendError($CommandAuthor['Login']);
		
		//BLACKLIST PLAYER
		} else if($args[2] == 'blacklist') {
			if($ban==true){
				$sql = "SELECT * FROM `players` WHERE playerlogin = '".trim($args[3][0])."'";
				$mysql = mysqli_query($this->db, $sql);
				if($data = $mysql->fetch_object()){
					$this->chat($rights[1].' $fff'.$CommandAuthor['NickName'].'$z$s '.$settings['Color_Ban'].'blacklisted $fff'.$data->nickname.$settings['Color_Ban'].' !', $settings['Color_Ban']);
					$this->instance()->client->query('BlackList', $args[3][0]);
					$this->instance()->client->query('Kick', $args[3][0]);
				} else $this->chatToLogin($args[1], 'Player \'$fff'.$args[3][0].'$f60\' not found!', 'f60');
			} else $this->sendError($CommandAuthor['Login']);
		
		//UNBLACKLIST PLAYER
		} else if($args[2] == 'unblacklist') {
			if($unban==true){
				$sql = "SELECT * FROM `players` WHERE playerlogin = '".trim($args[3][0])."'";
				$mysql = mysqli_query($this->db, $sql);
				if($data = $mysql->fetch_object()){
					$this->instance()->client->query('UnBlackList', $args[3][0]);
					$this->chat($rights[1].' $fff'.$CommandAuthor['NickName'].'$z$s '.$settings['Color_Ban'].'unblacklisted $fff'.$data->nickname.$settings['Color_Ban'].' !', $settings['Color_Ban']);
				} else $this->chatToLogin($args[1], 'Player \'$fff'.$args[3][0].'$f60\' not found!', 'f60');
			} else $this->sendError($CommandAuthor['Login']);
		
		//FORCE SPECTATOR
		} else if($args[2] == 'forcespec') {
			if($forceSpec == true) {
				$this->instance()->client->query('GetDetailedPlayerInfo', $args[3][0]);
				$playerInfo = $this->instance()->client->getResponse();;
			
				$this->instance()->client->query('ForceSpectator', $args[3][0], 1);
				$this->instance()->client->query('ForceSpectator', $args[3][0], 0);
				
				$this->chat($rights[1].' $fff'.$CommandAuthor['NickName'].'$z$s '.$settings['Color_ForceSpec'].'forced $fff'.$playerInfo['NickName'].'$z$g$s'.$settings['Color_ForceSpec'].' into Spectator mode!', $settings['Color_ForceSpec']);
			} else $this->sendError($CommandAuthor['Login']);
		
		//FORCE PLAYER
		} else if($args[2] == 'forceplayer') {
			if($forceSpec == true) {
				$this->instance()->client->query('GetDetailedPlayerInfo', $args[3][0]);
				$playerInfo = $this->instance()->client->getResponse();;
			
				$this->instance()->client->query('ForceSpectator', $args[3][0], 2);
				$this->instance()->client->query('ForceSpectator', $args[3][0], 0);
				
				$this->chat($rights[1].' $fff'.$CommandAuthor['NickName'].'$z$s '.$settings['Color_ForceSpec'].'forced $fff'.$playerInfo['NickName'].'$z$g$s'.$settings['Color_ForceSpec'].' into Player mode!', $settings['Color_ForceSpec']);
			} else $this->sendError($CommandAuthor['Login']);
		
		//IGNORE PLAYER
		} else if($args[2] == 'ignore') {
			if($ignorePlayer == true) {
				$this->instance()->client->query('GetDetailedPlayerInfo', $args[3][0]);
				$playerInfo = $this->instance()->client->getResponse();
				
				$this->instance()->client->query('Ignore', $args[3][0]);
				$this->chat($rights[1].' $fff'.$CommandAuthor['NickName'].'$z$s '.$settings['Color_ForceSpec'].'ignored $fff'.$playerInfo['NickName'].'$z$g$s'.$settings['Color_ForceSpec'].'!', $settings['Color_ForceSpec']);
			}
			
		//UNIGNORE PLAYER
		} else if($args[2] == 'unignore') {
			if($ignorePlayer == true) {
				$this->instance()->client->query('GetDetailedPlayerInfo', $args[3][0]);
				$playerInfo = $this->instance()->client->getResponse();
				
				$this->instance()->client->query('UnIgnore', $args[3][0]);
				$this->chat($rights[1].' $fff'.$CommandAuthor['NickName'].'$z$s '.$settings['Color_ForceSpec'].'unignored $fff'.$playerInfo['NickName'].'$z$g$s'.$settings['Color_ForceSpec'].'!', $settings['Color_ForceSpec']);
			}
		
		//SHOW PLAYERLIST FOR ADMINS
		} else if($args[2] == 'adminplayers') {
			$this->instance()->show_playerlist($CommandAuthor['Login'], true, 0);		
		}
		
		/* Reboot StuntsControl */
		else if($args[2] == 'reboot')
		{
			if($reboot_script==true) $this->instance()->Reboot();
			else $this->sendError($CommandAuthor['Login']);		
		}
		
		//SKIP TRACK
		else if($args[2] == 'skip') {
			if($skip_challenge==true){
				$this->instance()->challenge_skip();
				$this->chat($rights[1].' $fff'.$CommandAuthor['NickName'].'$z$s $f90skipped the map!', 'f90');
			} else $this->sendError($CommandAuthor['Login']);
		
		//RESTART TRACK
		} else if($args[2] == 'restart' || $args[2] == 'res') {
			if($restart_challenge==true){
				global $chall_restarted_admin;
				$chall_restarted_admin = true;
				$this->instance()->client->query('RestartMap');
				$this->chat($rights[1].' $fff'.$CommandAuthor['NickName'].'$z$s $f90restarted the map!', 'f90');
			} else $this->sendError($CommandAuthor['Login']);
		
		//REPLAY TRACK
		} else if($args[2] == 'replay') {
			$this->instance()->client->query('GetCurrentMapInfo');
			$currentChallenge = $this->instance()->client->getResponse();
			
			$this->instance()->client->query('ChooseNextMap', $currentChallenge['FileName']);
			
			$this->chat($rights[1].' $fff'.$CommandAuthor['NickName'].'$z$s $f90queues the current map for replay!', 'f90');
		
		//FORCE ENDROUND
		} else if($args[2] == 'endround') {
			if($force_end_round==true){
				$this->instance()->client->query('ForceEndRound');
				$this->chat($rights[1].' $fff'.$CommandAuthor['NickName'].'$z$s $f90forced round end!', 'f90');
			} else $this->sendError($CommandAuthor['Login']);
		
		//SHOW PLANETS
		} else if($args[2] == 'planets') {
			$this->instance()->client->query('GetServerPlanets');
			$planets = $this->instance()->client->getResponse();
			$this->chatToLogin($CommandAuthor['Login'], 'This Server has $fff'.$planets.'$0f0 Planets', '0f0');
		
		//PAY PLANETS
		} else if($args[2] == 'pay') {
			if($admin_pay==true){
				global $settings;
				$coppers_tp = trim($args[3][0]);
				$coppers_tl = trim($args[3][1]);
				if(is_numeric($coppers_tp)!==true){
					$this->chatToLogin($CommandAuthor['Login'], 'The number of the Coppers to pay must be an integer!', 'f60');
				}
				elseif(trim($coppers_tl)==''){
					$this->chatToLogin($CommandAuthor['Login'], 'No login set to pay the Coppers!', 'f60');
				}
				else{
					$pay_message = $CommandAuthor['NickName'].'$z$s payed '.$coppers_tp.' to you from the Server '.$settings['ServerName'].'$z$s !';
					
					$this->instance()->client->query('Pay', trim($coppers_tl), intval($coppers_tp), $pay_message);
					
					$this->chat($rights[1].' $fff'.$CommandAuthor['NickName'].'$z$s $0f0payed $fff'.$coppers_tp.'$0f0 Planets to $fff'.$coppers_tl.'$0f0!', '0f0');	
				}
			} else $this->sendError($CommandAuthor['Login']);
		
		//UPDATE
		} else if($args[2] == 'update') {
			if($update == true) {
				$window = $this->window;
				$window->init();
				$window->title('$fffUpdate FoxControl');
				$window->displayAsTable(true);
				$window->size(50, '');
				$window->posY('36.8');
				$window->target('onButtonUpdate', $this);
				
				$content = $this->getUpdateInfo();
		
				if(isset($content)) {
					$version = $content->version;
					$autoupdate = $content->autoupdate;
		
					if(SC_Version < $version) {
						if($autoupdate == 'true') {
							$link = str_replace('&', '&amp;', $content->release_info);
					
							$window->content('<td width="25">Your Version: '.SC_Version.'</td>');
							$window->content('<td width="25">Newest Version: '.$version.'</td>');
							$window->content('<td ml="'.$link.'" width="48">Check if this link links to the release notes to make sure it\'s an official update!</td>');
							$window->content('');
							$window->content('<td width="40">Do you really want to update FoxControl to the newest version?</td>');
						
							$window->addButton('Yes', '7', false);
							$window->addButton('', '3', false);
							$window->addButton('No', '7', true);
						} else {
							$link = str_replace('&', '&amp;', $content->release_info);
					
							$window->content('<td width="25">Your Version: '.SC_Version.'</td>');
							$window->content('<td width="25">Newest Version: '.$version.'</td>');
							$window->content('');
							$window->content('<td width="40">Autoupdate is not possible. Please update manually!</td>');
							$window->content('<td ml="'.$link.'" width="48">Release notes &amp; download!</td>');
						
							$window->addButton('Close', '7', true);
						}
					} else {
						$window->content('<td width="25">No update available!</td>');
						$window->addButton('Close', '7', true);
					}
				} else {
					$window->content('<td width="25">Error while trying to get Update information. Try again later.</td>');
					$window->addButton('Close', '7', true);
				}
				
				$window->show($args[1]);
			} else $this->sendError($CommandAuthor['Login']);

		//CALLVOTE
		} else if($args[2] == 'callvote') {
			if($setCallVote == true) {
				if(isset($args[3][0])) {
					$pluginCallVote = $this->getPluginInstance('plugin_disablevote');
					if($pluginCallVote !== false) {
						$this->instance()->writeInConfig('plugins/config/plugin.disablevote.config.xml', 'defaultVoteTimeOut', $args[3][0]);
					
						$pluginCallVote->setCallVote($args[3][0]*1000);
						$pluginCallVote->defaultVoteTimeOut = (int) $args[3][0]*1000;
						
						$this->chat($rights[1].' '.$CommandAuthor['NickName'].'$z$s$f90 set CallVote Timeout to $fff'.$args[3][0].' $f90seconds!', 'f90');
					} else {
						$this->chat('$f90Plugin not activated!');
					}
				}
			} else $this->sendError($CommandAuthor['Login']);

		//MIX MAP LIST
		} else if($args[2] == 'mixmap') {
console('mixmap:');
			if($mixMap == true) {
				//$this->instance()->client->query('GetDetailedPlayerInfo', $args[3][0]);
				//$playerInfo = $this->instance()->client->getResponse();
console('matchsettings_filename: '.$settings['matchsettings_filename']);				
				$this->instance()->client->query('LoadMatchSettings', 'MatchSettings/'.$settings['matchsettings_filename']);
				$this->chat($rights[1].' $fff'.$CommandAuthor['NickName'].'$z$s '.$settings['Color_ForceSpec'].'mixed the maplist $fff');
			}
			
		}
	}
	public function onBeginMap($args) {
		$this->getScriptSettings();
	}
	public function onManialinkPageAnswer($args) {
		if($args[2] == $this->mlids[0]) {
			$this->closeMl($this->mlids[0], $args[1]);
		}
	}
	public function onButtonPressed($args) {
		if($args[2] == 1) { //<
			$newargs = array(1 => $args[1], 2 => 'adminhelp', 3 => array(0 => $this->helpUsers[$args[1]]));
			$this->onCommand($newargs);
		} else if($args[2] == 3) { //>
			$newargs = array(1 => $args[1], 2 => 'adminhelp', 3 => array(0 => ($this->helpUsers[$args[1]] + 2)));
			$this->onCommand($newargs);
		}
	}
	public function onButtonUpdate($args) {
		if($args[2] == 1) {
			$this->chat('$fff[$06fA$fffuto$06fU$fffpdater] Starting Update...');
				
			$pluginUpdate = $this->getPluginInstance('plugin_autoupdate');
			if($pluginUpdate !== false) {
				$pluginUpdate->startUpdate();
			} else {
				$this->chat('$f90Plugin not activated');
			}
				
			$window = $this->window;
			$window->closeWindow($args[1]);
		}
	}
	public function sendError($login) {
		global $settings;
		$this->chatToLogin($login, $settings['Text_wrong_rights'], 'f00');
	}
	public function changeRights($login, $rights) {
		$sql = "SELECT * FROM `players` WHERE playerlogin = '".$login."'";
		$mysql = mysqli_query($this->db, $sql);
		if($admin = $mysql->fetch_object()) {
			$sql = "SELECT * FROM `admins` WHERE playerlogin = '".$login."'";
			$mysql = mysqli_query($this->db, $sql);
			if(!$if_admin = $mysql->fetch_object()) {
				$sql = "INSERT INTO `admins` (id, playerlogin, rights) VALUES ('', '".$login."', '".$rights."')";
				$mysql = mysqli_query($this->db, $sql);
				return $admin->nickname;
			} else {
				$sql = "UPDATE `admins` SET rights = '".$rights."' WHERE playerlogin = '".$login."'";
				$mysql = mysqli_query($this->db, $sql);
				return $admin->nickname;
			}	
		} else return false;
	}
	public function getDataFromUrl($url) {
		$options = array('http' => array('user_agent' => 'FoxControl', 'max_redirects' => 1000, 'timeout' => 1000));
		$context = stream_context_create($options);
		return @file_get_contents($url,true,$context );
    }
	public function getScriptSettings() {
		$this->instance()->client->query('GetModeScriptSettings');
		$this->scriptSettings = $this->instance()->client->getResponse();
	}
	
	public function getUpdateInfo() {
		$fp = fsockopen("www.global-rebels.de", 80, $errno, $errstr, 5);
	
		if (!$fp) {
			console('!!!FOXCONTROL MASTERSERVER ERROR!!!');
			console($errstr .'('.$errno.')');
		} else {
			fwrite($fp, "GET / HTTP/1.1\r\n");
		
			$content = simplexml_load_file('http://fox.global-rebels.de/newsupdate/TrackMania2/newsupdate.xml');
			return $content;
		}
	}
}
?>
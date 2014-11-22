<?php
//* plugin.players.php - Playerlist
//* Version:   0.6
//* Coded by:  matrix142
//* Copyright: FoxRace, http://www.fox-control.de
//* Version: 0.7
//* Pastis-51
//* 2014.11.09
//* change style windows

class plugin_players extends FoxControlPlugin {
	public $playerListUsers = array();
	public $adminListUsers = array();

	public function onStartUp() {
		$this->name = 'Playerlist';
		$this->author = 'matrix142';
		$this->version = '0.7';
		
		$this->registerCommand('players', 'Shows the Player list.', false);
		$this->registerCommand('admins', 'Shows a list of the admins. $s/admins <all|ops|admins|superadmins>$s', false);
		$this->registerMLIds(125);
	}
	
	public function onCommand($args) {
		//PLAYERLIST
		if($args[2] == 'players') {
			//FOR ADMINS
			if(isset($args[3][0]) AND $args[3][0] == 'admin') {
				$this->displayList($args[1], 'admin');
			}
			//FOR PLAYERS
			else {
				$this->displayList($args[1]);
			}
		}
		
		//ADMINLIST
		else if($args[2] == 'admins') {
			$this->displayList($args[1], 'admins');
		}
	}
	
	public function onManialinkPageAnswer($args) {
		global $playerList, $type_list;
	
		//ADMIN ACTIONS
		if($args[2] >= $this->mlids[0] && $args[2] <= $this->mlids[124]) {			
			//WARN
			if($args[2] >= $this->mlids[0] && $args[2] <= $this->mlids[24]) {
				$min = 0;
				$action = 'warn';
			}
			//KICK
			else if($args[2] >= $this->mlids[25] && $args[2] <= $this->mlids[49]) {
				$min = 25;
				$action = 'kick';
			}
			//BAN
			else if($args[2] >= $this->mlids[50] && $args[2] <= $this->mlids[74]) {
				$min = 50;
				$action = 'ban';
			}
			//BLACKLIST
			else if($args[2] >= $this->mlids[75] && $args[2] <= $this->mlids[99]) {
				$min = 75;
				$action = 'blacklist';
			}
			//SPECTATOR
			else if($args[2] >= $this->mlids[100] && $args[2] <= $this->mlids[124]) {
				$min = 100;
				
				$pageID = $this->playerListUsers[$args[1]];
				$pageID = $pageID * 25;
				$playerID = ($args[2] - $this->mlids[$min] + $pageID);
				
				if($playerList[$playerID]['SpectatorStatus'] != 0) {
					$action = 'forceplayer';
				} else {
					$action = 'forcespec';
				}
			}
			
			$pageID = $this->playerListUsers[$args[1]];
			$pageID = $pageID * 25;
			$playerID = ($args[2] - $this->mlids[$min] + $pageID);
			$login = $playerList[$playerID]['Login'];
			
			$chatAdmin = $this->getPluginInstance('chat_admin');
			if($chatAdmin == true) {
				$chatAdmin->onCommand(array(1 => $args[1], 2 => $action, 3 => array(0 => $login)));
			}
			
			$this->displayList($args[1], 'admin');
		}
	}
	
	public function onPages($args) {
		global $type_list, $playerList;
	
		if($args[2] == 1) $this->playerListUsers[$args[1]] = 0; // <<
		elseif($args[2] == 2 && $this->playerListUsers[$args[1]] > 0) $this->playerListUsers[$args[1]]--; // <
		elseif($args[2] == 6) $this->playerListUsers[$args[1]]++; // >
		elseif($args[2] == 7) $this->playerListUsers[$args[1]] = floor(count($playerList) / 25);
		
		$this->displayList($args[1], $type_list);
	}
	
	public function displayList($login, $type = 'players') {
		global $playerList, $type_list, $settings;
	
		$type_list = $type;
	
		//DISPLAY PLAYERLIST
		if($type == 'players' OR $type == 'admin') {		
			//GET PLAYER LIST
			$this->instance()->client->query('GetPlayerList', 200, 0);
			$playerList = $this->instance()->client->getResponse();
		
			//SET PAGE START ID FOR USER
			if(!isset($this->playerListUsers[$login])) $this->playerListUsers[$login] = 0;
			
			$currentID = $this->playerListUsers[$login] * 25;
			
			//CREATE WINDOW
			$window = $this->window;
			$window->init();
			
			$window->title('$070C$fffurrent $070P$ffflayers');
			
			$window->displayAsTable(true);
			$window->size(70, '');
			$window->posY('36.8');
			$window->target('onPages', $this);
			
			//PREV PAGE BUTTONS
			if(isset($playerList[$currentID - 25])) {
				$window->addButton('<<<', '7', false);
				$window->addButton('<', '7', false);
			}else {
				$window->addButton('', '7', false);
				$window->addButton('', '7', false);
			}
			
			$window->addButton('', '15.5', false);
			$window->addButton('Close', '10', true);
			$window->addButton('', '15.5', false);
			
			//NEXT PAGE BUTTONS
			if(isset($playerList[$currentID + 25])) {
				$window->addButton('>>>', '7', false);
				$window->addButton('>', '7', false);
			}else {
				$window->addButton('', '7', false);
				$window->addButton('', '7', false);
			}
			
			//CHECK ADMIN RIGHTS
			if($this->instance()->is_admin($login) == true) {
				$admin = true;
			}else {
				$admin = false;
			}
			
			if($admin == true AND $type == 'admin') {
				$window->content('<td width="3">$iID</td><td width="20">$iNickName</td><td width="12">$iLogin</td><td width="30">$iActions</td>');
			}else {
				$window->content('<td width="3">$iID</td><td width="15">$iNickName</td><td width="10">$iLogin</td><td width="10">$iPlayed</td><td width="10">$iConnections</td><td width="10">$iLadder Rank</td><td width="10">$iNation</td>');
			}
		
			for($i=0; isset($playerList[$currentID]) && $i<25; $i++) {
				//FOR ADMINS
				if($admin == true AND $type == 'admin') {
					$warnID = $this->mlids[0] + $i;
					$kickID = $this->mlids[25] + $i;
					$banID = $this->mlids[50] + $i;
					$blackID = $this->mlids[75] + $i;
					$specID = $this->mlids[100] + $i;
					
					if($playerList[$currentID]['SpectatorStatus'] != 0) {
						$spec = 'ForcePlayer';
					} else {
						$spec = 'ForceSpectator';
					}
					
					if($playerList[$currentID]['Login'] != $settings['ServerLogin']) {
						$window->content('<td width="3">'.($currentID).'</td><td width="20">'.htmlspecialchars($playerList[$currentID]['NickName']).'</td><td width="12">'.$playerList[$currentID]['Login'].'</td><td width="6" id="'.$warnID.'" align="center">Warn</td><td width="6" id="'.$kickID.'" align="center">Kick</td><td width="6" id="'.$banID.'" align="center">Ban</td><td width="6.5" id="'.$blackID.'" align="center">Blacklist</td><td width="8" id="'.$specID.'">'.$spec.'</td>');
					}
				}
				//FOR PLAYERS
				else {
					if($playerList[$currentID]['Login'] != $settings['ServerLogin']) {
						$this->instance()->client->query('GetDetailedPlayerInfo', $playerList[$currentID]['Login']);
						$playerInfo = $this->instance()->client->getResponse();
						$country = explode('|', $playerInfo['Path']);
					
						$sql = mysqli_query($this->db, "SELECT * FROM `players` WHERE playerlogin = '".$playerList[$currentID]['Login']."'");
						if($row = $sql->fetch_object())
					
						if($this->instance()->formattime_hour($row->timeplayed) != 0) {
							$time = $this->instance()->formattime_hour($row->timeplayed);
						} else if($this->instance()->formattime_minute($row->timeplayed) != 0) {
							$time = $this->instance()->formattime_minute($row->timeplayed);
						} else {
							$time = '0min';
						}
					
						$window->content('<td width="3">'.($currentID).'</td><td width="15">'.htmlspecialchars($playerList[$currentID]['NickName']).'</td><td width="10">'.$playerList[$currentID]['Login'].'</td><td width="10">'.$time.'</td><td width="10">'.$row->connections.'</td><td width="10">'.$playerList[$currentID]['LadderRanking'].'</td><td width="10">'.$country[2].'</td>');
					}
				}
				
				$currentID++;
			}
			
			$window->show($login);
		}
		
		//DISPLAY ADMINLIST
		else if($type = 'admins') {
			$window = $this->window;
			$window->init();
			
			$window->title('$070A$fffdmin $070L$fffist');
			
			$window->displayAsTable(true);
			$window->size(50, '');
			$window->posY('36.8');
			
			$window->addButton('', '15.5', false);
			$window->addButton('Close', '10', true);
			$window->addButton('', '15.5', false);
			
			$window->content('<td width="3">$iID</td><td width="20">$iNickName</td><td width="12">$iLogin</td><td width="10">$iRights</td>');
			
			$sql = mysqli_query($this->db, "SELECT * FROM admins ORDER BY rights");
			$id = 0;
			while($row = $sql->fetch_object()) {
				$sql2 = mysqli_query($this->db, "SELECT * FROM players WHERE playerlogin = '".$row->playerlogin."'");
				if($row2 = $sql2->fetch_object()) {
					$nickname = $row2->nickname;
				}
				
				if(!isset($nickname)) $nickname = $row->playerlogin;
				
				$rights = $this->getRights($row->playerlogin);
				
				$window->content('<td width="3">'.($id+1).'</td><td width="20">'.htmlspecialchars($nickname).'</td><td width="12">'.$row->playerlogin.'</td><td width="10">'.$rights[1].'</td>');
			
				$id++;
			}
			
			$window->show($login);
		}
	}
}
?>
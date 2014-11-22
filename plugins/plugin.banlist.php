<?php
//* plugin.banlist.php - Banlist
//* Version:   0.5
//* Coded by:  matrix142
//* Copyright: FoxRace, http://www.fox-control.de

class plugin_banlist extends FoxControlPlugin {
	public $listLoginPage = array();
	public $banList;

	/*
	STARTUP FUNCTION
	*/
	public function onStartUp() {
		$this->name = 'Banlist';
		$this->author = 'matrix142';
		$this->version = '0.5';
		
		//Register Chat Command
		$this->registerCommand('banlist', 'Displays a list of banned players', true);
		
		//Register ML IDs
		$this->registerMLIds(25);
	}
	
	/*
	ON CHAT COMMAND FUNCTION
	*/
	public function onCommand($args) {
		if($args[2] == 'banlist') {
			//Check Admin rights
			if($this->instance()->is_admin($args[1])) {
				//Getting Banlist
				$this->instance()->client->query('GetBanList', 200, 0);
				$this->banList = $this->instance()->client->getResponse();
			
				$this->showBanlist($args[1]);
			}
		}
	}
	
	/*
	MANIALINK PAGE ANSWER
	*/
	public function onManialinkPageAnswer($args) {
		if($args[2] >= $this->mlids[0] && $args[2] <= $this->mlids[24]) {
			$pageID = $this->listLoginPage[$args[1]];
			$pageID = $pageID * 25;
			$pageID = ($args[2] - $this->mlids[0] + $pageID);
			
			$login = $this->banList[$pageID]['Login'];
			
			$rights = $this->getRights($args[1]);
			if($rights[0] == 0) return;
			else if($rights[0] == 1) require('include/op_rights.php');
			else if($rights[0] == 2) require('include/admin_rights.php');
			else if($rights[0] == 3) require('include/superadmin_rights.php');
			
			if($unban == true) {
				$chatAdmin = $this->getPluginInstance('chat_admin');
				if($chatAdmin == true) {
					$chatAdmin->onCommand(array(1 => $args[1], 2 => 'unban', 3 => array(0 => $login)));
				}
			}
		}
	}
	
	/*
	ON PAGES WINDOW CLASS
	*/
	public function onPages($args) {
		if($args[2] == 1) $this->listLoginPage[$args[1]] = 0; // <<
		elseif($args[2] == 2 && $this->listLoginPage[$args[1]] > 0) $this->listLoginPage[$args[1]]--; // <
		elseif($args[2] == 6) $this->listLoginPage[$args[1]]++; // >
		elseif($args[2] == 7) $this->listLoginPage[$args[1]] = floor(count($this->banList) / 25);
		
		$this->showBanlist($args[1]);
	}
	
	/*
	SHOW BANLIST
	*/
	public function showBanlist($login) {
		if(!isset($this->listLoginPage[$login])) $this->listLoginPage[$login] = 0;
		$currentID = $this->listLoginPage[$login] * 25;
		
		//Create Window
		$window = $this->window;
		$window->init();
		
		$window->title('$fffBanlist');
		
		$window->displayAsTable(true);
		$window->size(55, '');
		$window->posY('40');
		$window->target('onPages', $this);
		
		//Prev Page Button
		if(isset($this->banList[$currentID - 25])) {
			$window->addButton('<<<', '7', false);
			$window->addButton('<', '7', false);
		} else {
			$window->addButton('', '7', false);
			$window->addButton('', '7', false);
		}
		
		//Close Button
		$window->addButton('', '15.5', false);
		$window->addButton('Close', '10', true);
		$window->addButton('', '15.5', false);
		
		//Next Page Button
		if(isset($this->banList[$currentID + 25])) {
			$window->addButton('>>>', '7', false);
			$window->addButton('>', '7', false);
		} else {
			$window->addButton('', '7', false);
			$window->addButton('', '7', false);
		}
		
		//Window Head
		$window->content('<td width="3">$iID</td><td width="12">$iLogin</td><td width="10">$iClient</td><td width="15">$iIP</td><td width="10">$iActions</td>');
		
		for($i=0; isset($this->banList[$currentID]) && $i<=24; $i++) {
			$this->instance()->client->query('GetDetailedPlayerInfo', $this->banList[$currentID]['Login']);
			$playerInfo = $this->instance()->client->getResponse();
			$unBanID = $this->mlids[0]+$i;
		
			$window->content('<td width="3">'.($currentID+1).'</td><td width="12">'.$this->banList[$currentID]['Login'].'</td><td width="10">'.$this->banList[$currentID]['ClientName'].'</td><td width="15">'.$this->banList[$currentID]['IPAddress'].'</td><td width="10" id="'.$unBanID.'" align="center">UnBan</td>');
			
			$currentID++;
		}
		
		$window->show($login);
	}
}
?>
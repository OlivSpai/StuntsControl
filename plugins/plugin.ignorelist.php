<?php
//* plugin.ignorelist.php - IgnoreList
//* Version:   0.4
//* Coded by:  matrix142
//* Copyright: FoxRace, http://www.fox-control.de

class plugin_ignorelist extends FoxControlPlugin {
	public $listLoginPage = array();
	public $ignoreList;

	/*
	STARTUP FUNCTION
	*/
	public function onStartUp() {
		$this->name = 'Ignorelist';
		$this->author = 'matrix142';
		$this->version = '0.4';
		
		//Register Chat Command
		$this->registerCommand('ignorelist', 'Displays a list of ignored players', true);
		
		//Register ML IDs
		$this->registerMLIds(25);
	}
	
	/*
	ON CHAT COMMAND FUNCTION
	*/
	public function onCommand($args) {
		if($args[2] == 'ignorelist') {
			//Check Admin rights
			if($this->instance()->is_admin($args[1])) {
				//Getting Ignorelost
				$this->instance()->client->query('GetIgnoreList', 200, 0);
				$this->ignoreList = $this->instance()->client->getResponse();
			
				$this->showIgnoreList($args[1]);
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
			
			$login = $this->ignoreList[$pageID]['Login'];
			
			$rights = $this->getRights($args[1]);
			if($rights[0] == 0) return;
			else if($rights[0] == 1) require('include/op_rights.php');
			else if($rights[0] == 2) require('include/admin_rights.php');
			else if($rights[0] == 3) require('include/superadmin_rights.php');
			
			if($ignorePlayer == true) {
				$chatAdmin = $this->getPluginInstance('chat_admin');
				if($chatAdmin == true) {
					$chatAdmin->onCommand(array(1 => $args[1], 2 => 'unignore', 3 => array(0 => $login)));
					$this->showIgnoreList($args[1]);
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
		elseif($args[2] == 7) $this->listLoginPage[$args[1]] = floor(count($this->ignoreList) / 25);
		
		$this->showIgnoreList($args[1]);
	}
	
	/*
	SHOW IGNORELIST
	*/
	public function showIgnoreList($login) {
		if(!isset($this->listLoginPage[$login])) $this->listLoginPage[$login] = 0;
		$currentID = $this->listLoginPage[$login] * 25;
		
		//Create Window
		$window = $this->window;
		$window->init();
		
		$window->title('$fffIgnorelist');
		
		$window->displayAsTable(true);
		$window->size(30, '');
		$window->posY('40');
		$window->target('onPages', $this);
		
		//Prev Page Button
		if(isset($this->ignoreList[$currentID - 25])) {
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
		if(isset($this->ignoreList[$currentID + 25])) {
			$window->addButton('>>>', '7', false);
			$window->addButton('>', '7', false);
		} else {
			$window->addButton('', '7', false);
			$window->addButton('', '7', false);
		}
		
		//Window Head
		$window->content('<td width="3">$iID</td><td width="12">$iLogin</td><td width="10">$iActions</td>');
		
		for($i=0; isset($this->ignoreList[$currentID]) && $i<=24; $i++) {
			$unIgnoreID = $this->mlids[0]+$i;
		
			$window->content('<td width="3">'.($currentID+1).'</td><td width="12">'.$this->ignoreList[$currentID]['Login'].'</td><td width="10" id="'.$unIgnoreID.'" align="center">UnIgnore</td>');
			
			$currentID++;
		}
		
		$window->show($login);
	}
}
?>
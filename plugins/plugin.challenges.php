<?php
//* plugin.challenges.php - Maplist
//* Version:   1.1
//* Coded by:  cyrilw, matrix142
//* Copyright: FoxRace, http://www.fox-control.de

//* Version		1.2
// adaptation Stunters
// 2013.09.22  pastis
//* Version: 1.3
//* Pastis-51
//* 2014.11.09
//* change style windows

class plugin_challenges extends FoxControlPlugin {
	public $challenges = array();
	public $chall_users = array();
	public $searchWriteChallenges;
	public $challengesFound;
	
	public function onStartUp() {
		$this->registerCommand('list', 'Shows the Map list', false);
		$this->registerCommand('maps', 'Shows the Map list. Same Command as $s/list$s', false);
		$this->registerMLIds(54);
		$this->writeChallenges();
		
		$this->name = 'Map list';
		$this->author = 'matrix142, Cyril,pastis';
		$this->version = '1.3';
	}
	public function onCommand($args) {
		if($args[2] == 'list' || $args[2] == 'maps') {
			if($args[3][0] == 'search' AND isset($args[3][1])){
				$this->searchWriteChallenges($args[3][1]);
				$this->displayList($args[1]);
			}else {
				$this->searchWriteChallenges = '';
				$this->displayList($args[1]);
			}
		}
	}
	public function onMapListModified($args) {
		if($args[2] == true) $this->writeChallenges();
	}
	public function onManialinkPageAnswer($args) {	
		//SHOW CHALLENGELIST
		if($args[2] == $this->mlids[0]) {
			$this->displayList($args[1]);
		//JUKE CHALLENGE
		}else if($args[2] >= $this->mlids[2] && $args[2] <= $this->mlids[26]) {
			if($this->instance()->pluginIsActive('plugin.jukebox.php') == true) {
				$challenge_page_id = $this->chall_users[$args[1]];
				$challenge_page_id = $challenge_page_id*25;
				$jukedchallengex = ($args[2] - $this->mlids[2]+$challenge_page_id);
				
				if(isset($this->searchWriteChallenges[0])) {
					$jukedchallenge = $this->searchWriteChallenges[$jukedchallengex];
				}else {
					$jukedchallenge = $this->challenges[$jukedchallengex];
				}
				plugin_jukebox::jukeChallenge($jukedchallenge['FileName'], $args[1], true);
			}
		//DELETE TRACK
		
		}else if($args[2] >= $this->mlids[27] && $args[2] <= $this->mlids[52]) {
			$challenge_page_id = $this->chall_users[$args[1]];
			$challenge_page_id = $challenge_page_id*25;
			$challengeid = ($args[2] - $this->mlids[27] + $challenge_page_id);
			$challenge_filename = $this->challenges[$challengeid]['FileName'];
			
			$rights = $this->getRights($args[1]);
			if($rights[0] == 0) return;
			else if($rights[0] == 1) require('include/op_rights.php');
			else if($rights[0] == 2) require('include/admin_rights.php');
			else if($rights[0] == 3) require('include/superadmin_rights.php');
			
			if($admin_delete_track == true) {
				$this->instance()->client->query('GetDetailedPlayerInfo', $args[1]);
				$detailedPlayerInfo = $this->instance()->client->getResponse();
				$nickname = $detailedPlayerInfo['NickName'];
			
				$this->chat($rights[1].' '.$nickname.'$z$s$f90 removed $fff'.$this->challenges[$challengeid]['Name'].'$z$s$f90!', 'f90');
			
				$this->instance()->client->query('RemoveMap', $challenge_filename);
				$removeChallenge = $this->instance()->client->getResponse();
				
				$this->writeChallenges();
				$this->displayList($args[1]);
			}
		}else if($args[2] == $this->mlids[1]) {
			$this->closeMl($this->mlids[1], $args[1]);
		}
	}
	public function onPages($args) {
		if($args[2] == 1) $this->chall_users[$args[1]] = 0; // <<
		elseif($args[2] == 2 && $this->chall_users[$args[1]] > 0) $this->chall_users[$args[1]]--; // <
		elseif($args[2] == 6) $this->chall_users[$args[1]]++; // >
		elseif($args[2] == 7) $this->chall_users[$args[1]] = floor(count($this->challenges) / 25);
		
		$this->displayList($args[1]);
	}
	public function writeChallenges() {
		$this->challenges = array();
		
		//Get Challenge List
		$this->instance()->client->query('GetMapList', 1000, 0);
		$challenge_list = $this->instance()->client->getResponse();
		
		for($i = 0; $i < count($challenge_list); $i++) {
				
		
			$this->challenges[] = array('Name' => $challenge_list[$i]['Name'], 'FileName' => $challenge_list[$i]['FileName'], 'Author' => $challenge_list[$i]['Author'], 'Environnement' => $challenge_list[$i]['Environnement']);
		}
	}
	
	public function searchWriteChallenges($search) {
		//SET VAR AS ARRAY
		$this->searchWriteChallenges = array();
		$this->challengesFound = false;
		
		//SEARCH CHALLENGES ARRAY
		foreach($this->challenges as $key => $value) {
			if(strpos(strtolower(' '.$this->challenges[$key]['Name'].$this->challenges[$key]['Author'].$this->challenges[$key]['FileName'].$this->challenges[$key]['Environnement']), strtolower($search), 0)) {
				//WRITE SEARCH ARRAY
				$this->searchWriteChallenges[] = array('Name' => $this->challenges[$key]['Name'], 'FileName' => $this->challenges[$key]['FileName'], 'Author' => $this->challenges[$key]['Author'], 'Environnement' => $this->challenges[$key]['Environnement']);
				$this->challengesFound = true;
			}
		}
		
		if($this->challengesFound == false) {
			$this->searchWriteChallenges[] = array('Name' => 'No Maps found!', 'FileName' => '', 'Author' => '', 'Environnement' => '');
		}
	}
	
	public function displayList($login) {
		//CALCULATE CHALLENGES START ID
		if(!isset($this->chall_users[$login])) $this->chall_users[$login] = 0;
		$challenge_page_id = $this->chall_users[$login];
		$challenge_page_id = $challenge_page_id*25;
		$challenge_page_id_number_2 = $challenge_page_id-25;
		
		//SET CHALLENGES ARRAY
		if(isset($this->searchWriteChallenges[0])) {
			$challengesArray = $this->searchWriteChallenges;
		}
		else {
			$challengesArray = $this->challenges;
		}
		
		$curr_challid = $this->chall_users[$login] * 25;
		
		//PREV PAGE BUTTON
		if(isset($challengesArray[$curr_challid - 25])) {
			$chall_prev_page = true;
		}else {
			$chall_prev_page = false;
		}
		
		//NEXT PAGE BUTTON
		if(isset($challengesArray[$curr_challid + 25])) {
			$chall_next_page = true;
		}else {
			$chall_next_page = false;
		}
		
		//CREATE WINDOW
		$window = $this->window;
		$window->init();
		
		//DISPLAY HEADLINE FOR NORMAL OR SEARCH WINDOW
		if(isset($this->searchWriteChallenges[0])) {
			$window->title('$070M$fffaps $070S$fffearch');
		}else {
			$window->title('$070M$fffaps');
		}
		
		$window->displayAsTable(true);
		$window->size(60, '');
		$window->posY('40.8');
		$window->target('onPages', $this);
		
		//DISPLAY PREV PAGE BUTTON
		if($chall_prev_page == true){
			$window->addButton('<<<', '7', false);
			$window->addButton('<', '7', false);
		}else {
			$window->addButton('', '7', false);
			$window->addButton('', '7', false);
		}
		
		$window->addButton('', '10.5', false);
		$window->addButton('Close', '10', true);
		$window->addButton('', '10.5', false);
		
		//DISPLAY NEXT PAGE BUTTON
		if($chall_next_page == true){
			$window->addButton('>', '7', false);
			$window->addButton('>>>', '7', false);
		}else {
			$window->addButton('', '7', false);
			$window->addButton('', '7', false);
		}
		
		//CHECK ADMIN RIGHTS
		$rights = $this->getRights($login);
		if($rights[0] == 1) require('include/op_rights.php');
		else if($rights[0] == 2) require('include/admin_rights.php');
		else if($rights[0] == 3) require('include/superadmin_rights.php');
		
		if($rights[0] >= 1 && $rights[0] <= 3) {
			if($admin_delete_track == true) {
				$admin = true;
			}else {
				$admin = false;
			}
		}else {
			$admin = false;
		}
		
		//CREATE WINDOW TOP
		if($this->challengesFound == false AND isset($this->searchWriteChallenges[0])) {
		
		}
		else if($admin == true) {
			$window->content('<td width="3">$iID</td><td width="25">$iMapname</td><td width="1"/><td width="11">$iAuthor</td><td width="1"/><td width="9">$iEnvironment</td><td width="1"></td><td width="10">$iActions</td>');
		}else {
			$window->content('<td width="3">$iID</td><td width="30">$iMapname</td><td width="1"/><td width="13">$iAuthor</td><td width="1"/><td width="10">$iEnvironment</td>');
		}
		
		for($i = 0; isset($challengesArray[$curr_challid]) && $i <= 24; $i++)
		{
			if(!isset($challengesArray[$curr_challid + 1])) $this->isSearched = false;
		
			$chall_ml_id = $this->mlids[2] + $i;
			$delete_id = $this->mlids[27] + $i;
			
			if($this->challengesFound == false AND isset($this->searchWriteChallenges[0])) {
				$window->content('<td width="25"></td>'.htmlspecialchars($challengesArray[$curr_challid]['Name']).'<td width="3"></td><td width="1"/><td width="11"></td><td width="1"/><td width="9"></td><td width="1"></td><td width="10"></td><td width="7"></td>');
			}else {
				//DISPLAY LIST FOR ADMIN
				if($admin == true) {
					if($this->instance()->pluginIsActive('plugin.jukebox.php') == true) {
						$window->content('<td width="3">'.($curr_challid + 1).'</td><td width="25" id="'.$chall_ml_id.'">'.htmlspecialchars($challengesArray[$curr_challid]['Name']).'</td><td width="1"/><td width="13">'.htmlspecialchars($challengesArray[$curr_challid]['Author']).'</td><td width="1"/><td width="7">'.$challengesArray[$curr_challid]['Environnement'].'</td><td width="1"></td><td width="7" id="'.$delete_id.'" align="center">$f33Delete</td>');
					}else {
						$window->content('<td width="3">'.($curr_challid + 1).'</td><td width="25">'.htmlspecialchars($challengesArray[$curr_challid]['Name']).'</td><td width="1"/><td width="13">'.htmlspecialchars($challengesArray[$curr_challid]['Author']).'</td><td width="1"/><td width="7">'.$challengesArray[$curr_challid]['Environnement'].'</td><td width="1"></td><td width="7" id="'.$delete_id.'" align="center">$f33Delete</td>');
					}
				//DISPLAY LIST FOR PLAYER
				}else {
					if($this->instance()->pluginIsActive('plugin.jukebox.php') == true) {
						$window->content('<td width="3">'.($curr_challid + 1).'</td><td width="30" id="'.$chall_ml_id.'">'.htmlspecialchars($challengesArray[$curr_challid]['Name']).'</td><td width="1"/><td width="15">'.htmlspecialchars($challengesArray[$curr_challid]['Author']).'</td><td width="1"/><td width="10">'.$challengesArray[$curr_challid]['Environnement'].'</td>');
					}else {
						$window->content('<td width="3">'.($curr_challid + 1).'</td><td width="30">'.htmlspecialchars($challengesArray[$curr_challid]['Name']).'</td><td width="1"/><td width="15">'.htmlspecialchars($challengesArray[$curr_challid]['Author']).'</td><td width="1"/><td width="10">'.$challengesArray[$curr_challid]['Environnement'].'</td>');
					}
				}
			}
			
			$curr_challid++;
		}
		
		$window->show($login);
	}
}
?>
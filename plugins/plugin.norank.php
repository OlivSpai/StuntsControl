<?php
//* plugin.norank.php - No Rank
//* Version:   0.5
//* Coded by:  matrix142
//* Copyright: FoxRace, http://www.fox-control.de

//* Version		0.6
// adaptation Stunters
// 2013.09.22  pastis
//* Version: 0.7
//* Pastis-51
//* 2014.11.09
//* change style windows

class plugin_norank extends FoxControlPlugin {
public $challenges = array();
	public $chall_users = array();
	public function onStartUp() {
		$this->registerCommand('norank', 'Shows maps without a record from you', false);
		$this->registerMLIds(27);
		
		$this->name = 'No Rank challenges';
		$this->author = 'matrix142';
		$this->version = '0.7';
	}
	public function onCommand($args) {
		if($args[2] == 'norank') {
			$this->displayList($args[1]);
		}
	}
	public function onManialinkPageAnswer($args) {	
		if($args[2] == $this->mlids[0]) {
			$this->displayList($args[1]);
		} else if($args[2] >= $this->mlids[2] && $args[2] <= $this->mlids[26]) {
			// TODO: Add to maps box
		} else if($args[2] == $this->mlids[1]) $this->closeMl($this->mlids[1], $args[1]);
	}
	public function onPages($args) {
		if($args[2] == 1) $this->chall_users[$args[1]] = 0; // <<
		elseif($args[2] == 2 && $this->chall_users[$args[1]] > 0) $this->chall_users[$args[1]]--; // <
		elseif($args[2] == 6) $this->chall_users[$args[1]]++; // >
		elseif($args[2] == 7) $this->chall_users[$args[1]] = floor(count($this->challenges) / 25);
		$this->displayList($args[1]);
	}
	public function writeChallenges($login) {
		$this->challenges = array();
		
		//Get Challenge List
		$this->instance()->client->query('GetMapList', 1000, 0);
		$challenge_list = $this->instance()->client->getResponse();
		
		for($i = 0; $i < count($challenge_list); $i++) {
			$sql = mysqli_query($this->db, "SELECT * FROm `records` WHERE challengeid = '".$challenge_list[$i]['UId']."' AND playerlogin = '".$login."'");
			if(!$row = $sql->fetch_object()) {
				
		
				$this->challenges[] = array('Name' => $challenge_list[$i]['Name'], 'FileName' => $challenge_list[$i]['FileName'], 'Author' => $challenge_list[$i]['Author'], 'Environnement' => $challenge_list[$i]['Environnement']);
			}
		}
	}
	public function displayList($login) {
		$this->writeChallenges($login);
	
		if(!isset($this->chall_users[$login])) $this->chall_users[$login] = 0;
		$challenge_page_id = $this->chall_users[$login];
		$challenge_page_id = $challenge_page_id*25;
		$challenge_page_id_number_2 = $challenge_page_id-25;
		
		$curr_challid = $this->chall_users[$login] * 25;
		if(isset($this->challenges[$curr_challid - 25])) $chall_prev_page = true;
		else $chall_prev_page = false;
		if(isset($this->challenges[$curr_challid + 25])) $chall_next_page = true;
		else $chall_next_page = false;
		
		//Include window class
		$window = $this->window;
		$window->init();
		$window->title('$070N$fffo $070R$fffank');
		$window->displayAsTable(true);
		$window->size(60, '');
		$window->posY('40.8');
		$window->target('onPages', $this);
		if($chall_prev_page == true){
			$window->addButton('<<<', '7', false);
			$window->addButton('<', '7', false);
		} else {
			$window->addButton('', '7', false);
			$window->addButton('', '7', false);
		}
		$window->addButton('', '10.5', false);
		$window->addButton('Close', '10', true);
		$window->addButton('', '10.5', false);
		if($chall_next_page == true){
			$window->addButton('>', '7', false);
			$window->addButton('>>>', '7', false);
		} else {
			$window->addButton('', '7', false);
			$window->addButton('', '7', false);
		}
		
		$window->content('<td width="3">$iID</td><td width="30">$iMapname</td><td width="1"/><td width="13">$iAuthor</td><td width="1"/><td width="10">$iEnvironment</td><td width="1"></td>');
		
		$chall_code = '';
		for($i = 0; isset($this->challenges[$curr_challid]) && $i <= 24; $i++)
		{
			$chall_ml_id = $this->mlids[2] + $i;
			$window->content('<td width="3">$cf1'.($curr_challid + 1).'</td><td width="30">'.htmlspecialchars($this->challenges[$curr_challid]['Name']).'</td><td width="1"/><td width="15">'.htmlspecialchars($this->challenges[$curr_challid]['Author']).'</td><td width="1"/><td width="10">'.$this->challenges[$curr_challid]['Environnement'].'</td><td width="1"></td>');
			$curr_challid++;
		}
		
		$window->show($login);
	}
}
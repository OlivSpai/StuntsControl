<?php
//* plugin.jukebox.php - Track Jukebox
//* Version:   0.6.1
//* Coded by:  cyrilw, matrix142
//* Copyright: FoxRace, http://www.fox-control.de
//* Version: 0.6.2
//* Pastis-51
//* 2014.11.09
//* change style windows

class plugin_jukebox extends FoxControlPlugin {
	public $mapsPerSite = 15;
	private $players = array();
	private $int_jc = 0;

	public function onStartUp() {
		global $jukebox;
		$jukebox = array();
		
		$this->registerCommand('jukebox', 'Displays the jukebox', false);
		
		$this->name = 'Jukebox';
		$this->author = 'Cyril';
		$this->version = '0.6.2';
	}
	
	public function onEndMap($args) {
		global $jukebox;
		for($i = 0; $i < count($jukebox); $i++) {
			if($jukebox[$i]['played'] === 'false'){
				$this->instance()->client->query('GetMapInfo', $jukebox[$i]['fileName']);
				$challenge = $this->instance()->client->getResponse();
				
				$this->instance()->client->query('GetDetailedPlayerInfo', $jukebox[$i]['login']);
				$player = $this->instance()->client->getResponse();
				
				$this->instance()->client->query('ChooseNextMap', $challenge['FileName']);
				
				$jukebox[$i]['played'] = true;
				$this->chat('Next Map will be $fff'.$challenge['Name'].'$z$s$0e0. Juked by: $z'.$player['NickName'].'$z$s$0e0!', '0e0');
				
				break;
			}
		}
	}
	
	public function onCommand($args) {
		if($args[2] == 'jukebox') {
			$this->displayJukebox($args[1], 0);
		}
	}
	
	public function displayJukebox($player, $site) {
		$this->players[$player] = $site;
		
		$window = $this->window;
		$window->init();
		$window->title('$070J$fffukebox');
		$window->close(true);
		$window->size(70, '');
		$window->posY('40.8');
		$window->target('onPages', $this);
		
		global $jukebox;
		$_jukebox = array();
		for($i = 0; $i < count($jukebox); $i++) {
			if($jukebox[$i]['played'] === 'false') {
				$_jukebox[] = $jukebox[$i];
			}
		}
		
		$this->int_jc = count($_jukebox);
		
		if(count($_jukebox) < 1) {
			$window->content('$s$fffNo Maps found!');
		} else {
			$window->displayAsTable(true);
			$window->content('<td width="3">$iID</td><td width="25">$iMapname</td><td width="1"/><td width="25">$iJuked by</td>');
		}
		
		for($i = $site * $this->mapsPerSite; $i < count($_jukebox) && $i < $this->mapsPerSite * ($site + 1); $i++) {
			$window->content('<td width="3">$cf1'.($i + 1).'</td><td width="25">'.$_jukebox[$i]['mapName'].'</td><td width="1"></td><td width="25">'.$_jukebox[$i]['nick'].'</td>');
		}
		
		if($site > 0) {
			$window->addButton('<<<', '7', false);
			$window->addButton('<', '7', false);
		} else {
			$window->addButton('', '7', false);
			$window->addButton('', '7', false);
		}
		$window->addButton('', '7', false);
		$window->addButton('Close', '10', true);
		$window->addButton('', '7', false);
		if($this->mapsPerSite * ($site + 1) < count($_jukebox)) {
			$window->addButton('>', '7', false);
			$window->addButton('>>>', '7', false);
		} else {
			$window->addButton('', '7', false);
			$window->addButton('', '7', false);
		}
		
		$window->show($player);
	}
	
	public function onPages($args) {
		if($args[2] == 1) { // <<<
			$this->displayJukebox($args[1], 0);
		} else if($args[2] == 2) { // <
			$this->displayJukebox($args[1], $this->players[$args[1]] - 1);
		} else if($args[2] == 6) { // >
			$this->displayJukebox($args[1], $this->players[$args[1]] + 1);
		} else if($args[2] == 7) { // >>>
			$this->displayJukebox($args[1], floor(($this->int_jc - 1) / $this->mapsPerSite));
		}
	}
	
	public function jukeChallenge($fileName, $player, $sendChatMessage) {
		$this->instance()->client->query('GetDetailedPlayerInfo', $player);
		$jukedplayer = $this->instance()->client->getResponse();
		
		//Check if player has already juked
		$alreadyjuked = false;
		$isjuked = false;
		global $jukebox;
		for($_i = 0; $_i < count($jukebox); $_i++) {
			if($jukebox[$_i]['played'] === 'false') {
				if($jukebox[$_i]['login'] == $jukedplayer['Login']) {
					$alreadyjuked = true;
				}
				
				if($jukebox[$_i]['fileName'] == $fileName) {
					$isjuked = true;
				}
			}
		}
		
		$rights = $this->getRights($player);
		
		if($alreadyjuked == true && $rights[0] == 0){
			$this->chatToLogin($jukedplayer['Login'], 'You have already juked!', 'f90');
		} else if($isjuked == true) {
			$this->chatToLogin($jukedplayer['Login'], 'This Map is already juked!', 'f90');
		} else {
			$this->instance()->client->query('GetMapInfo', $fileName);
			$jukedchallenge = $this->instance()->client->getResponse();
			if(!isset($jukedchallenge['Name']) || trim($jukedchallenge['Name']) == '') {
				$this->chatToLogin($jukedplayer['Login'], 'Map not found!', 'f90');
				console('[WARNING] [plugin.jukebox.php] Map \''.$fileName.'\' not found!');
				return;
			}
			$jukebox[] = array('played' => 'false', 'fileName' => $fileName, 'mapName' => $jukedchallenge['Name'], 'login' => $jukedplayer['Login'], 'nick' => $jukedplayer['NickName']);
			if($sendChatMessage == true) $this->chat($jukedplayer['NickName'].'$z$s$0e0 juked $fff'.$jukedchallenge['Name'].'$z$s$0f0 !');
		}
	}
	
	function onPlayerDisconnect($args)
	{
		global $jukebox;
		$login = $args[0];
console("onPlayerDisconnect args:");
//var_dump($jukebox);	
		for($_i = 0; $_i < count($jukebox); $_i++) {
			if($jukebox[$_i]['login'] == $login) 
			{
console('in: '.$login);				
				$jukebox[$_i]['played'] = 'true';
			}
		}
var_dump($jukebox);	
		
	} // end onPlayerDisconnect
}
?>
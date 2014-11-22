<?php
//* plugin.music.php - Listen to music :)
//* Version:   0.4
//* Coded by:  matrix142
//* Copyright: FoxRace, http://www.fox-control.de
//* Version: 0.5
//* Pastis-51
//* 2014.11.09
//* change style windows

class plugin_music extends FoxControlPlugin {
	public $songsPerPage = 17;
	public $startList = 0;
	public $config;
	
	public function onStartUp () {
		$this->name = 'Music';
		$this->author = 'matrix142';
		$this->version = '0.5';
		
		//Load XML Data
		$this->config = $this->loadConfig();
		$url = $this->config->url;
		$count = $this->config->songs->song;
		$count2 = count($count);
		
		$this->registerMLIds(($count2+5));
		$this->registerCommand('music', 'Displays the Music Jukebox', false);
		
		$this->music_play();
		$this->displayMusicPanel();
	}

	public function onPlayerConnect ($connectedplayer){
		global $db, $music_mlcode;
	
		$sql = "SELECT * FROM `admins` WHERE playerlogin = '".trim($connectedplayer['Login'])."'";
		$mysql = mysqli_query($db, $sql);
	
		if($mysql->fetch_object()){
			$this->displayMusicPanel($connectedplayer['Login']);
		}
	}

	public function onBeginMap ($args){
		$this->displayMusicPanel();
		$this->music_play();
	}

	function onEndMap ($args) {
		global $music_mlcode, $songID, $newSongID, $name, $play;
	
		if($play != "no"){
			$songID++;
			$this->nextSong($songID, $newSongID, 'no');
			$this->chat('$z$i$s$fc3Next song: '.$name.'');
		}
	
		$this->closeMl($this->mlids[0]);
	}
	
	public function displayMusicPanel ($login = false) {
		global $settings;
	
		$code = '
		<frame posn="50.7 -32.5 0">
		<quad posn="0 0 0" sizen="8.6 3" halign="center" bgcolor="1919194d" bgcolorfocus="000c"/>
		<quad posn="3 0.5 1" sizen="4.2 4.2" halign="center" style="UIConstruction_Buttons" action="'.$this->mlids[1].'" substyle="Redo" />
		<quad posn="0 0 1" sizen="3 3" halign="center" style="UIConstructionBullet_Buttons" action="'.$this->mlids[2].'" substyle="NewBullet" />
		<quad posn="-3 0.5  1" sizen="4.2 4.2" halign="center" style="UIConstruction_Buttons" action="'.$this->mlids[3].'" substyle="Undo" />
	
		</frame>';
		
		if($login != false){
			$this->displayManialinkToLogin($login, $code, $this->mlids[0]);
		}else{
			$this->instance()->client->query('GetPlayerList', 300, 0);
			$playerlist = $this->instance()->client->getResponse();
			
			$id = 0;
			while(isset($playerlist[$id]['Login'])){
				if($this->instance()->is_admin($playerlist[$id]['Login'])){
					$this->displayManialinkToLogin($playerlist[$id]['Login'], $code, $this->mlids[0]);
				}
				$id++;
			}
		}
	}
	
	public function nextSong ($songID, $newSongID, $reset){
		global $songID, $newSongID, $url, $song, $name, $count2;
	
		//Check if $songID exists
		if(!isset($songID)){
			$songID = 0;
		}
	
		//Load XML Data
		$url = $this->config->url;
		$count = $this->config->songs->song;
		$count2 = count($count);
	
		//Check if song with $songID exists
		if($songID > ($count2-1)){
			$songID = 0;
		}
	
		//Check if $songID is lower than 0
		if($songID < 0){
			$songID = $count2-1;
		}
	
		//Check $newSongID (if an admin has pressed a button)
		if(isset($newSongID)){	
			if($newSongID > ($count2-1)){
				$newSongID = 0;
			}
		
			if($newSongID < 0){
				$newSongID = $count2-1;
			}
			$songID = $newSongID;
		}
	
		//Load XML Data
		$song = $this->config->songs->song[$songID];
		$name = $this->songs->name[$songID];
	
		//Reset $newSongID
		if($reset == "yes"){
			$newSongID = null;
		}
	}

	public function music_play (){
		global $url, $song, $play, $songID, $newSongID;
	
		$this->nextSong($songID, $newSongID, 'yes');
	
		//Play Music
		if($play != "no"){
			$this->instance()->client->query('SetForcedMusic', true, $url.$song);
		}
	}

	/*
	Get Answers of pressed buttons
	Next: Set $newSongID +1
	Stop: Set $play = "no"
	Prev: Set $newSongID -1
	*/
	public function onManialinkPageAnswer ($ManialinkPageAnswer){
		global $count2;
		
		//Next Song
		if($ManialinkPageAnswer[2] == $this->mlids[1]){
			$login = $ManialinkPageAnswer[1];
			if($this->instance()->is_admin($login) == true){
				global $songID, $newSongID, $name;
			
				$this->instance()->client->query('GetDetailedPlayerInfo', $ManialinkPageAnswer[1]);
				$Playerinfo = $this->instance()->client->getResponse();
			
				$newSongID = $songID+1;
			
				$this->nextSong($songID, $newSongID, 'no');
				$this->chat('$z$i$s$f90Admin '.$Playerinfo['NickName'].' $z$i$s$fc3sets next song to '.$name.'');
			}
		}
		
		//Stop/start playing
		if($ManialinkPageAnswer[2] == $this->mlids[2]){
			$login = $ManialinkPageAnswer[1];
			if($this->instance()->is_admin($login) == true){
				global $play;
				if($play == "no"){
					$play = "yes";
					$this->chat('$z$i$s$fc3Music will play at begin of a new map!');
				}else{
					$play = "no";
					$this->chat('$z$i$s$fc3Music will stop at end of this map!');
					$this->instance()->client->query('SetForcedMusic', false, '');
				}
			}
		}
		
		//Prev Song
		if($ManialinkPageAnswer[2] == $this->mlids[3]){
			$login = $ManialinkPageAnswer[1];
			if($this->instance()->is_admin($login) == true){
				global $songID, $newSongID, $name;
			
				$this->instance()->client->query('GetDetailedPlayerInfo', $ManialinkPageAnswer[1]);
				$Playerinfo = $this->instance()->client->getResponse();
			
				$newSongID = $songID-1;
			
				$this->nextSong($songID, $newSongID, 'no');
				$this->chat('$z$i$s$f90Admin '.$Playerinfo['NickName'].' $z$i$s$fc3sets prev song to '.$name.'');
			}	
		}
		
		//Open Jukebox
		if($ManialinkPageAnswer[2] == $this->mlids[4]){
			$this->music_jukebox($ManialinkPageAnswer[1]);
		}
		
		if($ManialinkPageAnswer[2] >= $this->mlids[5] AND $ManialinkPageAnswer[2] <= $this->mlids[(5+$count2)-1]){
			global $newSongID, $songID, $name;
		
			$id = $ManialinkPageAnswer[2] - $this->mlids[5];
			$newSongID = $id;
		
			$this->nextSong($songID, $newSongID, 'no');
		
			$this->instance()->client->query('GetDetailedPlayerInfo', $ManialinkPageAnswer[1]);
			$Playerinfo = $this->instance()->client->getResponse();
		
			$this->chat(''.$Playerinfo['NickName'].'$z$i$s$fc3 juked Song: '.$name.'');
		}
	}	

	public function onCommand ($args){
	
		if($args[2] == 'music'){
			$this->music_jukebox($args[1]);
		}
	}

	public function onButtonPressed($args) {
		global $i3;
		
		if($args[2] == 1) { //<
			if($this->startList <= $this->songsPerPage) $this->startList = 0;
			else $this->startList = $this->startList - $this->songsPerPage;
			$this->music_jukebox();
		} else if($args[2] == 3) { //>
			$this->startList = $this->startList + $this->songsPerPage;
			$this->music_jukebox();
		}
	}
	
	public function music_jukebox ($login){
		global $count2, $i3;
	
		if(empty($site)) $site = 0;
		$window = $this->window;
		$window->init();
		$window->title('$070M$fffusic $070J$fffukebox');
		$window->close(true);
		$window->displayAsTable(true);
		
		$window->size(42, '');
		$window->content('<td width="3">ID</td><td width="30">Songname</td>');
		$window->content(' ');
		
		$i2 = 0;
		if($this->startList == 0) $i3 = 0;
		else $i3 = $this->startList;
		for($i = 0; $i < $count2; $i++) {
			if($i2 >= $this->songsPerPage) break;
			if(!isset($this->config->songs->name[$i3])) break;
		
			$window->content('<td width="3">'.($i3 + 1).'</td><td width="30" id="'.$this->mlids[(5+$i3)].'">'.htmlspecialchars($this->config->songs->name[$i3]).'</td>');
				
			$i2++;
			$i3++;
		}
			
		if($this->startList >= ($this->songsPerPage - 1)) $window->addButton('<', 7, false);
		else $window->addButton('', 7, false);
			
		$window->addButton('Close', 15, true);
			
		if($this->startList < $this->songsPerPage AND ($this->startList + $count2) > $this->songsPerPage) $window->addButton('>', 7, false);
		else $window->addButton('', 7, false);
			
		$window->target('onButtonPressed', $this);
		$window->show($login);
	}
}
?>
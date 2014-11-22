<?php

// CatchMe Plugin
// v1.0 2013-10-03
// Coded by Spaï, designed by Pastis

class plugin_catchme extends FoxControlPlugin {
	
	public function SetNextGame()
	{
		global $catchme;
		
		$this->closeMl($this->mlids[0]);
		$this->closeMl($this->mlids[4]);
		
		// Random and set current game
		$randItemNumber 			= rand(0, count($catchme['games'])-1);
		$catchme['name']			= $catchme['games'][$randItemNumber]['name'];
		$catchme['sprite']			= $catchme['games'][$randItemNumber]['sprite'];
		$catchme['music']			= $catchme['games'][$randItemNumber]['music'];
		$catchme['min_sprite_size']	= $catchme['games'][$randItemNumber]['min_sprite_size'];
		$catchme['max_sprite_size']	= $catchme['games'][$randItemNumber]['max_sprite_size'];
		$catchme['payout']			= rand($catchme['games'][$randItemNumber]['min_pay'], $catchme['games'][$randItemNumber]['max_pay']);
		$catchme['next_game_time']	= rand($catchme['min_time_between_games'], $catchme['max_time_between_games']) + time();
		
		$catchme['showed'] = 0;		
		$catchme['play'] = 0;
		
		$catchme['players_count'] = $this->getplayercount();
		
		$this->instance()->client->query('GetServerPlanets');
		$catchme['planets'] = $this->instance()->client->getResponse();
	}
	
	public function onStartUp()
	{
		$this->enabled = True;
		$this->name = 'Catch Me';
		$this->author = 'Spaï,Pastis';
		$this->version = '1.0';
		$this->registerMLIds(5);
			
		// Vars
		global $catchme, $settings;
		$settingsXML = simplexml_load_file('./plugins/config/plugin.catchme.config.xml');
		
		// Set items list from config file
		$catchme['games'] = array();
		$i = 0;
		foreach($settingsXML->games->game as $item)
		{
			$catchme['games'][$i]['name']				= (string)$item->attributes()->name;
			$catchme['games'][$i]['sprite']				= (string)$item->attributes()->sprite;
			$catchme['games'][$i]['music']				= (string)$item->attributes()->music;
			$catchme['games'][$i]['min_sprite_size']	= (real)$item->attributes()->min_sprite_size;
			$catchme['games'][$i]['max_sprite_size']	= (real)$item->attributes()->max_sprite_size;
			$catchme['games'][$i]['min_pay']			= (int)$item->attributes()->min_pay;
			$catchme['games'][$i]['max_pay']			= (int)$item->attributes()->max_pay;
			$i++;
		}
		
		$catchme['min_server_planets']				= (int)$settingsXML->min_server_planets				? (int)$settingsXML->min_server_planets				: 1500;
		$catchme['min_players_to_activate_game']	= (int)$settingsXML->min_players_to_activate_game	? (int)$settingsXML->min_players_to_activate_game	: 16;
		$catchme['min_time_between_games']			= (int)$settingsXML->min_time_between_games			? (int)$settingsXML->min_time_between_games			: 30;
		$catchme['max_time_between_games']			= (int)$settingsXML->max_time_between_games			? (int)$settingsXML->max_time_between_games			: 75;
		
		$catchme['serverlogin'] = (string)$settings['ServerLogin'];
		
		// Alter players table for 'Top Catch Me' window
		mysqli_query($this->db, "ALTER TABLE `players` ADD catchmeplanets mediumint UNSIGNED NOT NULL");
		mysqli_query($this->db, "ALTER TABLE `players` ADD catchmecount mediumint UNSIGNED NOT NULL");
		
		$this->SetNextGame();
	}

		
	public function onEverySecond()
	{
		global $catchme;
		
		//console('current time: '.time() . ' next game at: ' . $catchme['next_game_time'].' start in '.($catchme['next_game_time'] - time()));
		if (!$catchme['showed'])
		{
			if($catchme['next_game_time'] == time() && ($catchme['min_server_planets'] < $catchme['planets']))
			{
				$ml = '<audio data="'.$catchme['music'].'" play="1" looping="0" posn="-90 0 0"/>';
				$catchme['players_count'] = $this->getplayercount();
				if (intval($catchme['min_players_to_activate_game']) <= intval($catchme['players_count'])) $this->displayManialink($ml, $this->mlids[2], 0);
			
				if ($catchme['min_players_to_activate_game'] <= $catchme['players_count'])
				{
					$this->chat('$fff>>$FF0Watch out for '.$catchme['name'].'$z$fff<<');
					$ml='<frame posn="-90 -90 0"><timeout>0</timeout>'
						.'<quad posn="0 0 2" sizen="0 0" image="'.$catchme['sprite'].'"/>'
						.'</frame>';
					$this->displayManialink($ml, $this->mlids[4], 0);
					$this->displayManialink($ml, $this->mlids[1], 0);
				}
				else $this->chat('$FFFCatch me game '.$catchme['name'].' $z$fffwill start with at least $0FF'.$catchme['min_players_to_activate_game'].' $fffPlayers !');			
			}
			
			if(($catchme['next_game_time']) > time()) return;
			
			if ($catchme['min_server_planets'] < $catchme['planets'])
			{
				if (intval($catchme['min_players_to_activate_game']) <= intval( $catchme['players_count'])) { $this->showme(); }
			}
		}
	}
		
	public function getplayercount()
	{
		$this->instance()->client->query('GetPlayerList', 300, 0);
		$playerlist = $this->instance()->client->getResponse();
		return(count($playerlist));
	}
		
	public function onManialinkPageAnswer($args)
	{
		global $catchme;
		
		if ($args[2] == $this->mlids[1] && $catchme['showed'] != 1)
		{
			$this->closeMl($this->mlids[0]);
			$this->closeMl($this->mlids[2]);
			$this->closeMl($this->mlids[4]);
			
			$this->instance()->client->query('GetServerName');
			$server_name = $this->instance()->client->getResponse();
			
			$this->instance()->client->query('GetDetailedPlayerInfo',$args[1]);
			$nickn = $this->instance()->client->getResponse();
			$nickname = $nickn['NickName'];
			
			$oldGameName = $catchme['name'];
			$oldPayOut = $catchme['payout'];
			
			$this->SetNextGame();
			
			$this->chat($nickname.'$z$fff has caught '.$oldGameName.' $z$fffand won $0c0$o'.$oldPayOut.' $z$fffplanets ! ');
			
			$this->instance()->client->query('Pay', $args[1] , intval($oldPayOut) , '$o$fffYou caught !!! $z$o$cc3You won $0f0'.$oldPayOut.' $fffplanets $z$o$cc3on '.$server_name
			.'$fff! Congratulations >>$cc3To play again on this server follow this link $f51$lmaniaplanet://#join='.$catchme['serverlogin']);
			$ret = $this->instance()->client->getResponse();			

			$result = mysqli_query($this->db, 'UPDATE `players` SET `catchmeplanets` = `catchmeplanets`+'.intval($oldPayOut).' WHERE `playerlogin`= "'.$args[1].'"');
			$result = mysqli_query($this->db, 'UPDATE `players` SET `catchmecount` = `catchmecount`+1 WHERE `playerlogin`= "'.$args[1].'"');			
		}
	}

	Public function showme()
	{
		global $catchme;
		
		//console("ok");
		
		if (!$catchme['showed'])
		{
			if (!$catchme['play']) $catchme['play'] = 1;
			
			// Random size
			$size = rand($catchme['min_sprite_size'], $catchme['max_sprite_size']);
	
			// Image with random size
			$x1=(rand(0,80))-40;
			$y1=(rand(0,80))-40;
			$xml = '<frame posn="'.$x1.' '.$y1.' 0"><timeout>0</timeout>'
				.'<quad posn="0 0 2" sizen="'.$size.' '.$size.'" image="'.$catchme['sprite'].'" action="'.$this->mlids[1].'"/>'
				.'</frame>';
			$this->displayManialink($xml, $this->mlids[4], 0);
		}
	}


}

?>
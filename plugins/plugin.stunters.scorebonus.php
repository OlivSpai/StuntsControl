<?php
//* Stunters Title Pack > Score Bonus Plugin
//* Version		0.1
//* Coded by	Spaï
//* Copyright	http://www.stunters.org
//
// v0.1 - 2012.11.26
 
class plugin_stunters_scorebonus extends FoxControlPlugin {

public $ScoresBonus;

	public function onStartUp()
	{
		$this->name = 'Stunters Score Bonus';
		$this->author = 'Spaï';
		$this->version = '0.1';

		// Alter players table for 'Top Catch Me' window
		mysqli_query($this->db, "ALTER TABLE `players` ADD scorebonusplanets mediumint UNSIGNED NOT NULL");
		mysqli_query($this->db, "ALTER TABLE `players` ADD scorebonuscount mediumint UNSIGNED NOT NULL");
		
		
		
		//Load config file (plugin.stunters_scorebonus.config.xml)
		$this->config = $this->loadConfig();

		// Get all bonus scores from config file, trim and convert to integer
		$this->ScoresBonus = explode(",", $this->config->ScoresBonus);
		foreach($this->ScoresBonus as $id=>$value)
		{
			$this->ScoresBonus[$id] = intval(trim($value));
		}
	}

	public function onModeScriptCallback($args)
	{
		// Callback name = $args[0]
		// Callback string value = $args[1]

		if ($args[0] == "Stunters.Player.Finish")
		{	
			$data = json_decode($args[1]);			
	
			// Get 
			$this->instance()->client->query('GetServerPlanets');
			$coppers = $this->instance()->client->getResponse();
			
			// Check if server planets >= $this->config->MinServerPlanets
			if ($coppers < $this->config->MinServerPlanets) return;
			
			// Check if score is a bonus score
			if (!in_array($data->Score, $this->ScoresBonus)) return;
			
			// Get player NickName
			$this->instance()->client->query('GetPlayerInfo', $data->Login);
			$response = $this->instance()->client->getResponse();
			$nickname = $response["NickName"];			
			
			// Player win planets or not (randomize)
			if (rand(0, 1)==0)
			{
				// Format message
				$fromArray = array('{score}');
				$toArray = array($data->Score);
				$message = str_replace($fromArray, $toArray, $this->config->BonusScoreNotWinMessage);
				
				// Send message
				$this->instance()->client->query('ChatSendServerMessageToLogin', $message, $data->Login);
				return;
			}
					
			// How many planets player wins
			$PlanetsToPay = rand(intval($this->config->MinPlanetsToPay), intval($this->config->MaxPlanetsToPay));
			
			// Format message
			$fromArray = array('{nickname}', '{planets}', '{score}');
			$toArray = array($nickname, $PlanetsToPay, $data->Score);
			$message = str_replace($fromArray, $toArray, $this->config->BonusScoreWinMessage);
			
			// Send message
			$this->instance()->client->query('ChatSendServerMessage', $message);
			
			// Pay player
			$this->instance()->client->query('Pay', $data->Login, $PlanetsToPay, $message);
			$result = mysqli_query($this->db, 'UPDATE `players` SET `scorebonusplanets` = `scorebonusplanets`+'.intval($PlanetsToPay).' WHERE `playerlogin`= "'.$data->Login.'"');  
			$result = mysqli_query($this->db, 'UPDATE `players` SET `scorebonuscount` = `scorebonuscount`+1 WHERE `playerlogin`= "'.$data->Login.'"'); 		
		}
	}
} // Class
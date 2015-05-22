<?php
//* plugin.top.players.php - top players
//* Version:   0.1
//* Coded by:  Pastis-51
//* 2014.11.05

class plugin_top_players extends FoxControlPlugin {
	public $listLoginPage = array();
	public $activeList;

	/*
	STARTUP FUNCTION
	*/
	public function onStartUp() {
		$this->name = 'top players';
		$this->author = 'Pastis-51';
		$this->version = '0.1';
		
		//Register Chat Command
		$this->registerCommand('topactive', 'Show top active players',true);
		$this->registerCommand('topdons', 'Show top donators ', true);
		$this->registerCommand('toplottery', 'Show top lottery', true);
		$this->registerCommand('topbets', 'Show top bets', true);
		$this->registerCommand('topbonus', 'Show top score bonus', true);
		$this->registerCommand('topcatchme', 'Show top catchme', true);
		$this->registerCommand('topnations', 'Show top nations', true);
		//Register ML IDs
		$this->registerMLIds(25);
	}
	
	/*
	ON CHAT COMMAND FUNCTION
	*/
	public function onCommand($args) {
		if($args[2] == 'topactive') {
			$this->activeList = array();			
			//Getting Active List
			$sql = mysqli_query($this->db, "SELECT * FROM `players` ORDER BY timeplayed DESC LIMIT 100");
			while($row = $sql->fetch_object()) {
				$this->activeList[] = array('nickname' => htmlspecialchars($row->nickname), 'login' => $row->playerlogin, 'active' => $row->timeplayed, 'connections' => $row->connections, 'country' => $row->country);
			}	
			$this->showActiveList($args[1]);
		}
		
		  elseif ($args[2] == 'topdons')    $this->showTopDons($args[1]);
		  elseif ($args[2] == 'toplottery') $this->showToplottery($args[1]);
		  elseif ($args[2] == 'topbets') 	$this->showTopBets($args[1]);
		  elseif ($args[2] == 'topbonus')   $this->showTopbonus($args[1]);
		  elseif ($args[2] == 'topcatchme') $this->showTopCatchme($args[1]);
		  elseif ($args[2] == 'topnations') $this->showTopNations($args[1]);
	}
	
	
///////// Top Active Players
	/*
	ON PAGES WINDOW CLASS
	*/
	public function onPages($args) {
		if($args[2] == 1) $this->listLoginPage[$args[1]] = 0; // <<
		elseif($args[2] == 2 && $this->listLoginPage[$args[1]] > 0) $this->listLoginPage[$args[1]]--; // <
		elseif($args[2] == 6) $this->listLoginPage[$args[1]]++; // >
		elseif($args[2] == 7) $this->listLoginPage[$args[1]] = floor(count($this->activeList) / 25);
		
		$this->showActiveList($args[1]);
	}
	
	/*
	SHOW BANLIST
	*/
	public function showActiveList($login) {	
		if(!isset($this->listLoginPage[$login])) $this->listLoginPage[$login] = 0;
		$currentID = $this->listLoginPage[$login] * 25;
		
		//Create Window
		$window = $this->window;
		$window->init();
		
		$window->title('$070M$fffost $070A$fffctive');
		
		$window->displayAsTable(true);
		$window->size(70, '');
		$window->posY('37.8');
		$window->target('onPages', $this);
		
		//Prev Page Button
		if(isset($this->activeList[$currentID - 25])) {
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
		if(isset($this->activeList[$currentID + 25])) {
			$window->addButton('>>>', '7', false);
			$window->addButton('>', '7', false);
		} else {
			$window->addButton('', '7', false);
			$window->addButton('', '7', false);
		}
		
		//Window Head
		$window->content('<td width="3">$iID</td><td width="19">$iNickName</td><td width="13">$iLogin</td><td width="10">$iTime Played</td><td width="10.5">$iConnects</td><td width="15">$iNations</td>');
		
		for($i=0; isset($this->activeList[$currentID]) && $i<=24; $i++) {	
			if($this->instance()->formattime_hour($this->activeList[$currentID]['active']) != 0) {$time = $this->instance()->formattime_hour($this->activeList[$currentID]['active']);
   	} else if($this->instance()->formattime_minute($this->activeList[$currentID]['active']) != 0) {$time = $this->instance()->formattime_minute($this->activeList[$currentID]['active']);
			} else {
				$time = 0;
			}
		
			if($time != 0) {
			$window->content('<td width="3">'.($currentID+1).'</td><td width="19">'.htmlspecialchars($this->activeList[$currentID]['nickname']).'</td><td width="15">'.$this->activeList[$currentID]['login'].'</td><td width="10">'.$time.'</td><td width="7">'.$this->activeList[$currentID]['connections'].'</td><td width="15">'.$this->activeList[$currentID]['country'].'</td>');
			}
			
			$currentID++;
		}
		
		$window->show($login);
		}
		
///////// Top Dons
		
			public function showTopDons($login) {
		
		//Create Manialink
		$window = $this->window;
		$window->init();
		
		$window->title('$070D$fffonators');
		
		$window->displayAsTable(true);
		$window->size(55, '');
		$window->posY('40.8');
		
		//Close Button
		$window->addButton('', '15.5', false);
		$window->addButton('Close', '10', true);
		$window->addButton('', '15.5', false);
		
		//Window Head
		$window->content('<td width="3">$iID</td><td width="21">$iNickName</td><td width="18">$iLogin</td><td width="15">$iPlanets</td>');
		
		$sql = mysqli_query($this->db, "SELECT * FROM `players` ORDER BY donations DESC LIMIT 0,25");
		
		$id = 0;
		while($row = $sql->fetch_object()) {
			if($row->donations != 0) {
				$window->content('<td width="3">'.($id + 1).'</td><td width="21">'.htmlspecialchars($row->nickname).'</td><td width="18">'.$row->playerlogin.'</td><td width="15">'.$row->donations.'</td>');
			
				$id++;
			}
		}		
		$window->show($login);
	}
	
///////// Top Lottery

	public function showToplottery($login) {
		
		//Create Window
		$window = $this->window;
		$window->init();
		
		$window->title('$070L$fffottery');
		
		$window->displayAsTable(true);
		$window->size(61.5, '');
		$window->posY('40.8');
		
		//Close Button
		$window->addButton('', '15.5', false);
		$window->addButton('Close', '10', true);
		$window->addButton('', '15.5', false);
		
		//Window Head
		$window->content('<td width="3">$iID</td><td width="20">$iNickName</td><td width="15">$iLogin</td><td width="10">Total Win</td><td width="9">Count</td>');
		
		$sql = mysqli_query($this->db, "SELECT * FROM `players` ORDER BY lotteryplanets DESC LIMIT 0,25");
		
		$id = 0;
		while($row = $sql->fetch_object()) {
			if($row->lotteryplanets != 0) {
				$window->content('<td width="3">'.($id + 1).'</td><td width="20">'.htmlspecialchars($row->nickname).'</td><td width="16">'.$row->playerlogin.'</td><td width="10">'.$row->lotteryplanets.'</td><td width="9">'.$row->lotterycount.'</td>');
			
				$id++;
			}
		}
		$window->show($login);
		}
/////// Top Bets

	public function showTopBets($login) {
		
		//Create Window
		$window = $this->window;
		$window->init();
		
		$window->title('$070B$fffets');
		
		$window->displayAsTable(true);
		$window->size(61.5, '');
		$window->posY('40.8');
		
		//Close Button
		$window->addButton('', '15.5', false);
		$window->addButton('Close', '10', true);
		$window->addButton('', '15.5', false);
		
		//Window Head
		
		$window->content('<td width="3">$iID</td><td width="20">$iNickName</td><td width="13.5">$iLogin</td><td width="9">$iTotal Bet</td><td width="9">$iTotal Win</td><td width="9">$iVictory</td>');
		
		$sql = mysqli_query($this->db, "SELECT * FROM `betting` ORDER BY wins DESC LIMIT 0,25");
		
		$id = 0;
		while($row = $sql->fetch_object()) {
			if($row->stake != 0) {
				$window->content('<td width="3">'.($id + 1).'</td><td width="20">'.htmlspecialchars($row->nickname).'</td><td width="15">'.$row->login.'</td><td width="9">'.$row->stake.'</td><td width="9">'.$row->wins.'</td><td width="9">'.$row->countwins.'</td>');
			
				$id++;
			}
		}
		
		$window->show($login);
				
	}
	
///////// Top Bonus

public function showTopbonus($login) {
		
		//Create Window
		$window = $this->window;
		$window->init();
		
		$window->title('$070S$fffcore $070B$fffonus');
		
		$window->displayAsTable(true);
		$window->size(61.5, '');
		$window->posY('40.8');
		
		//Close Button
		$window->addButton('', '15.5', false);
		$window->addButton('Close', '10', true);
		$window->addButton('', '15.5', false);
		
		//Window Head
		$window->content('<td width="3">$iID</td><td width="20">$iNickName</td><td width="15">$iLogin</td><td width="10">Total Win</td><td width="9">Count</td>');
		
		$sql = mysqli_query($this->db, "SELECT * FROM `players` ORDER BY scorebonusplanets DESC LIMIT 0,25");
		
		$id = 0;
		while($row = $sql->fetch_object()) {
			if($row->scorebonusplanets != 0) {
				$window->content('<td width="3">'.($id + 1).'</td><td width="20">'.htmlspecialchars($row->nickname).'</td><td width="16">'.$row->playerlogin.'</td><td width="10">'.$row->scorebonusplanets.'</td><td width="9">'.$row->scorebonuscount.'</td>');
			
				$id++;
			}
		}
		
		$window->show($login);
	}
///////// Top Catchme

public function showTopcatchme($login) {
		
		//Create Window
		$window = $this->window;
		$window->init();
		
		$window->title('$070C$fffatch $070m$fffe');
		
		$window->displayAsTable(true);
		$window->size(60, '');
		$window->posY('40.8');
		
		//Close Button
		$window->addButton('', '15.5', false);
		$window->addButton('Close', '10', true);
		$window->addButton('', '15.5', false);
		
		//Window Head
		$window->content('<td width="3">$iID</td><td width="20">$iNickName</td><td width="15">$iLogin</td><td width="10">Total Win</td><td width="9">Count</td>');
		
		$sql = mysqli_query($this->db, "SELECT * FROM `players` ORDER BY catchmeplanets DESC LIMIT 0,25");
		
		$id = 0;
		while($row = $sql->fetch_object()) {
			if($row->catchmeplanets != 0) {
				$window->content('<td width="3">'.($id + 1).'</td><td width="20">'.htmlspecialchars($row->nickname).'</td><td width="16">'.$row->playerlogin.'</td><td width="10">'.$row->catchmeplanets.'</td><td width="9">'.$row->catchmecount.'</td>');
			
				$id++;
	
			}
		}
		
		$window->show($login);
	}
///////// Top Nations

public function showTopNations($login) {
		
		//Create Window
		$window = $this->window;
		$window->init();
		
		$window->title('$070N$fffations');
		
		$window->displayAsTable(true);
		$window->size(32, '');
		$window->posY('40.8');
		
		//Close Button
		$window->addButton('', '15.5', false);
		$window->addButton('Close', '10', true);
		$window->addButton('', '15.5', false);
		
		//Window Head
		$window->content('<td width="3">$iID</td><td width="18">$iNations</td><td width="18">$iTotal players</td>');
		
		$sql = mysqli_query($this->db, "SELECT COUNT(*) AS NbResult, country FROM players WHERE country != '' && country != 'Europe' && country != 'North America' && country != 'South America' && country != 'Middle East' && country != 'Africa' && country != 'Asia' && country != 'Oceania' GROUP BY country ORDER BY NbResult DESC LIMIT 0, 30 ");
		$id = 0;
		while($row = $sql->fetch_object())
		{
			if($row->NbResult != 0)
			{
				$window->content('<td width="4">'.($id + 1).'</td><td width="21">'.$row->country.'</td><td width="10">'.$row->NbResult.'</td>');			
				$id++;
			}
		}
		
		$window->show($login);
	}
}
?>
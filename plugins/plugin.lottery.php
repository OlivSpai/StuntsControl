<?php

// Corrected , adapted and designed for Stunters Title Packs by pastis-51

// V1.0.5
// adaptation Stunters
// 2013.09.22  

// v1.0.6 2013-03-10
// Added lotteryplanets and lotterycount in 'players' table of database for top lottery window
//* Version: 1.0.7
//* Pastis-51
//* 2014.11.09
//* change style windows

error_reporting(E_ALL ^ E_NOTICE);
class plugin_lottery extends FoxControlPlugin {
    public function onStartUp()
	{
		$this->enabled=true;
		$this->name = 'lottery'; 
		$this->author ='pastis-51'; 
		$this->version='1.0.7'; 
		$this->registerCommand('jackpot', 'Refills Jackpot' , '/jackpot <ammount>', true);
		global $winmin,$winmax,$minplayers,$winpotmax, $winpot, $anzrunden, $minplanets, $gameserverurl, $servername,$winpercent,$winposy,$winposx,$settings;
		$this->registerMLIds(1);
		$anzrunden=0;
		$xml = simplexml_load_file('./plugins/config/plugin.lottery.config.xml');
		$winpot=intval($xml->winpot);
		$winmin=intval($xml->winmin); //planets
		$winmax=intval($xml->winmax);	//planets
		$minplanets=intval($xml->minplanets); //how many planets on server to start the lottery
		$winpotmax=intval($xml->winpotmax); // maximum potwin
		$minplayers=intval($xml->minplayers); //how many players minimum on server to start lottery
		$gameserverurl=(string)$settings['ServerLogin']; // the login of your gameserver
		$this->instance()->client->query('GetServerName');
		$svname=$this->instance()->client->getResponse();
		$servername = $svname; // Your Server´s Name
		$tmp =$xml->winpercent; //chance in % to win ( ) Never go above 1
		$winposy=$xml->winposy;
		$winposx=$xml->winposx; 
		$winpercent= number_format(($tmp/100),2);
		if ($winpercent > 0.99 or $winpercent < 0.01) $winpercent=0.25; //25 % winpercentage if the pecentage is out of range
	$xml='<timeout>0</timeout><frame posn="'.$winposx.' '.$winposy.' 0">'
			.'<quad posn="0 0 1" sizen="21 11.5" halign="center" valign="center" bgcolor="1919194d" />'
			//.'<quad posn="0 4.25 2" sizen="25 3" halign="center" valign="center" style="BgsPlayerCard" substyle="ProgressBar" />'
			//.'<quad posn="0 4.25 2" sizen="25 3" halign="center" valign="center" style="BgsPlayerCard" substyle="ProgressBar" />'
			//.'<quad posn="-12.5 5.5 6" sizen="2.5 2.5" style="Icons128x128_1" substyle="Vehicles" />'
			.'<label posn="0 4.6 4" halign="center" valign="center" text="$i$o$FFF$sLottery" textfont="Stunts/XBall"  scale="0.8" />'
			.'<label posn="0 1 4" halign="center" valign="center" text="$Fff$iJackpot Planets" textfont="Stunts/XBall"  scale="0.8" />'
			.'<label posn="0 -2.5 4" halign="center" valign="center" text="$cf1$i'.$winpot.'" textfont="Stunts/XBall"  scale="1" />
			</frame>';
		$this->displayManialink($xml, $this->mlids[0], 1);
	
		// Alter players table
		mysqli_query($this->db, "ALTER TABLE `players` ADD lotteryplanets mediumint UNSIGNED NOT NULL");
		mysqli_query($this->db, "ALTER TABLE `players` ADD lotterycount mediumint UNSIGNED NOT NULL");
	}
	
	Public Function OnCommand($args)
	{
		global $winpot;
		
		$CommandAuthor = $args[1];
		if ($args[2]=='jackpot')
		{
			$rights = $this->getRights($CommandAuthor);
			if($rights[0] < 3)	$this->chatToLogin($CommandAuthor, 'No Way!.', 'f60'); 
			else
			{
				$wp=intval($args[3][0]);
				$winpot=$wp;
				$this->onBeginMatch();
				$this->chatToLogin($CommandAuthor, 'New Jackpot:'.$winpot , 'f60'); 
			}
		}
	}//end OnCommand
	
	public function onPlayerConnect($args)
	{
		global $winpot,$winposx,$winposy;
		$player=$args['Login'];
		$xml='<timeout>0</timeout><frame posn="'.$winposx.' '.$winposy.' 0">'
		.'<quad posn="0 0 1" sizen="21 11.5" halign="center" valign="center" bgcolor="1919194d" />'
		//.'<quad posn="0 4.25 2" sizen="25 3" halign="center" valign="center" style="BgsPlayerCard" substyle="ProgressBar" />'
		//.'<quad posn="0 4.25 2" sizen="25 3" halign="center" valign="center" style="BgsPlayerCard" substyle="ProgressBar" />'
		//.'<quad posn="-12.5 5.5 6" sizen="2.5 2.5" style="Icons128x128_1" substyle="Vehicles" />'
		.'<label posn="0 4.6 4" halign="center" valign="center" text="$i$o$FFF$sLottery" textfont="Stunts/XBall"  scale="0.8" />'
		.'<label posn="0 1 4" halign="center" valign="center" text="$Fff$iJackpot Planets" textfont="Stunts/XBall" scale="0.8" />'
		.'<label posn="0 -2.5 4" halign="center" valign="center" text="$cf1$i'.$winpot.'" textfont="Stunts/XBall"  scale="1" />
		</frame>';
		$this->displayManialinkToLogin($player, $xml, $this->mlids[0], 1);
	}
		
	public function onBeginMatch()
	{
		global $winpot,$winposx,$winposy;
		$xml='<timeout>0</timeout><frame posn="'.$winposx.' '.$winposy.' 0">'
		.'<quad posn="0 0 1" sizen="21 11.5" halign="center" valign="center"  bgcolor="1919194d" />'
		//.'<quad posn="0 4.25 2" sizen="25 3" halign="center" valign="center" style="BgsPlayerCard" substyle="ProgressBar" />'
		//.'<quad posn="0 4.25 2" sizen="25 3" halign="center" valign="center" style="BgsPlayerCard" substyle="ProgressBar" />'
		//.'<quad posn="-12.5 5.5 6" sizen="2.5 2.5" style="Icons128x128_1" substyle="Vehicles" />'
		.'<label posn="0 4.6 4" halign="center" valign="center" text="$i$o$FFF$sLottery" textfont="Stunts/XBall" scale="0.8" />'
		.'<label posn="0 1 4" halign="center" valign="center" text="$Fff$iJackpot Planets" textfont="Stunts/XBall"  scale="0.8" />'
		.'<label posn="0 -2.5 4" halign="center" valign="center" text="$cf1$i'.$winpot.'" textfont="Stunts/XBall"  scale="1" />
		</frame>';
		$this->displayManialink($xml, $this->mlids[0], 1);
	}
	
	public function onEndMatch($args)
	{
		global $winmin,$winmax,$minplayers, $winpotmax, $winpot, $anzrunden, $minplanets, $gameserverurl, $servername,$winpercent,$winposy,$winposx;
		$this->closeMl($this->mlids[0]);
		$this->instance()->client->query('GetServerPlanets');
		$planets=$this->instance()->client->getResponse();
		if ($planets < $minplanets) $this->chat ('Server is running low on Planets, please donate to start the Lottery');
		else
		//$this->instance()->client->query('GetCurrentRanking',50,0);
		$player=$args[0]; //$this->instance()->client->getResponse();
		$z=0;
		$a=0;
		$anz=count($player);
		while ($a<=$anz)
		{
			if ($player[$a]['BestTime']>0 or intval($player[$a]['Rank'])>0) // hope it get better sometimes...
			{
				$finisher[$z]->name = $player[$a]['Login'];
				$finisher[$z]->time = $player[$a]['BestTime'];
				$finisher[$z]->rank = $player[$a]['Rank'];
				$z++;
			}
			$a++;
		}
		
		if ($z>=$minplayers)
		{
			console ('finishers:'.$anz.' max random:'.intval($anz * ( 1/$winpercent)));
			$win=rand(0, intval($anz*(1/$winpercent))); //choose a random number from 0 to maxplayers
			console('Random Number:'.$win);
			$winpot=$winpot+rand($winmin, $winmax);
			
			if ($winpot > $winpotmax) $winpot=$winpotmax; //stop growing the jackpot when limit is reached.
			
			if ($finisher[$win]->name != '')
			{ 
				$this->instance()->client->query('GetDetailedPlayerInfo',($finisher[$win]->name));
				$nickname = $this->instance()->client->getResponse();
				$nname=$nickname->NickName;
				if ($nickname['Nickname']=='') $nickname['Nickname']=$finisher[$win]->name; //if the winner has left before round end we just ouput his login
				$rights = $this->getRights($finisher[$win]->name);
				
				$this->chat('$f51$i>>$z '.$nickname['NickName'].'$z$o$cc3 won the pot of $0f0'.$winpot.'$fff Planets $cc3!');
				$this->instance()->client->query ('Pay', $finisher[$win]->name , intval($winpot), '$o$cc3You won $0f0'.$winpot.'$fff Planets $cc3! in the '.$servername.'$fff Lottery$cc3,$fff Congratulations >> $cc3to play again on this server follow the link :$f51 $lmaniaplanet://#join='.$gameserverurl);
				$ret=$this->instance()->client->getResponse();
				
				// Add lottery informations in database
				$result = mysqli_query($this->db, 'UPDATE `players` SET `lotteryplanets` = `lotteryplanets`+'.intval($winpot).' WHERE `playerlogin`= "'.$finisher[$win]->name.'"');
				$result = mysqli_query($this->db, 'UPDATE `players` SET `lotterycount` = `lotterycount`+1 WHERE `playerlogin`= "'.$finisher[$win]->name.'"');
				
				$winpot=0;
				$anzrunden=0;
			}
			else
			{
				$anzrunden++;
				$this->chat ('$f51$iRound '.$anzrunden.':$z$i$fff - No Lottery winner this round ! there are $fd0'.$winpot.'$fff Planets in the Jackpot');
			}
		}
		else
		{ 
			if($planets >= $minplanets)
			{
				$text= '$f51$i'.(intval($minplayers)-$z).'$z$fff more Finishers needed to Start Lottery!';
				$this->chat($text);
			}
		}
		
		$player=null;
	}
	
} // Class End
?>
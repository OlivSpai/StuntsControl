<?php

//original was made by nouse4name for xaseco /xaseco2

//http://www.nouseforname.de

//adapted / modified for Foxontrol by uBm

//V 1.0



// v1.1

// 2012.11.13 - Spaï

// Patched for FoxControl TM 1.1, fixed some bugs

// v1.2
// UI Designer	
// 2013.09.22 - pastis

//* Version: 1.3
//* Pastis-51
//* 2014.11.09
//* change style windows

 /* ----------------------------------------------------------------------------------

 *

 * LICENSE: This program is free software; you can redistribute it and/or

 * modify it under the terms of the GNU General Public License as published

 * by the Free Software Foundation; either version 2 of the License, or

 * (at your option) any later version.

 *

 * This program is distributed in the hope that it will be useful,

 * but WITHOUT ANY WARRANTY; without even the implied warranty of

 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the

 * GNU General Public License for more details.

 *

 * You should have received a copy of the GNU General Public License

 * along with this program; if not, write to the Free Software

 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA

 *

 * ----------------------------------------------------------------------------------

 */

class plugin_nouse_fox_betting extends FoxControlPlugin

{	

	public function onStartUp()

	{

		$this->enabled = True;

		$this->name = 'nouse.betting'; 

		$this->author = 'Spaï'; 

		$this->version = '1.3';

		

		// chat commands
/*  pastis test
		$this->registerCommand('bet', 'Bet amount of planets "/bet planets"',true);

		$this->registerCommand('accept', 'Accept the bet with given amount of planets.',true);

		$this->registerCommand('betstate', 'Enable/Disable betting "/betstate On/Off".',true);
*/
		

		// Load settings

		$this->loadSettings();

		

		$this->bet_mysql_create();

		

		$this->registerMLIds(15);

		

		global $bcp;

		global $db,$settings;

		$bcp = $this->betting_planets(false);

		$valid_players = array();

		$acceptors = array();

		$ranks = array();

		$showaccept = array();

		$bets = array();

		global $index;

		global $minbet, $maxbet, $enabled, $minserverplanets, $state;

		global $timelimit_bet, $timelimit_accept;

		global $bet_active, $bet_starter, $bet_starter_nick, $bet_start, $bet_amount;

		global $bet1, $bet2, $bet3, $bet4, $bet5, $bet6, $bet7;

		global $timebet, $countsec, $player, $clicktime, $clicked;

		global $nickname, $planets, $paybackstarter, $paybackbetstarter;

		global $checkbet, $checkaccept, $betbill;

		global $paybacknowin, $winneronly;

		global $bet_starter_factor, $bet_nowin_factor, $bet_winpayment_factor; 

		global $betpanelmainpos, $acceptpanelmainpos, $bwinpanelmainpos;

		

		$this->validate_players();

		$this->getServerplanets();

		$this->reset_bet();

		

		$this->clicktime = time();

	}

	

	// debug

	function betting_planets ($debug){

		$this->debug = $debug;

		$this->instance()->console($debug);

	}

	

	

	// load xml configs

	function loadSettings(){

	$xml = simplexml_load_file('./plugins/config/nouse.betting.config.xml');

		//$file = file_get_contents('nouse_betting_config.xml');

		//$xml = simplexml_load_string($file);



		$this->betenabled = intval($xml->betenabled);

		

		$this->minserverplanets = intval($xml->minserverplanets);

		

		$this->minbet = intval($xml->minbet);

		$this->maxbet = intval($xml->maxbet);

		

		$this->timelimit_bet = intval($xml->timelimit_bet);

		$this->timelimit_accept = intval($xml->timelimit_accept);

		

		$this->bet1 = intval($xml->bet1);

		$this->bet2 = intval($xml->bet2);

		$this->bet3 = intval($xml->bet3);

		$this->bet4 = intval($xml->bet4);

		$this->bet5 = intval($xml->bet5);
		
		$this->bet6 = intval($xml->bet6);
		
		$this->bet7 = intval($xml->bet7);
		
		
		$this->bet1color = intval($xml->bet1color);

		

		$this->winneronly = intval($xml->winneronly);

		$this->paybacknowin = intval($xml->paybacknowin);

		$this->paybackbetstarter = intval($xml->paybackstarter);

		

		$this->bet_starter_factor = intval($xml->betstarterpayback);

		$this->bet_nowin_factor = intval($xml->betnowinpayback);

		$this->bet_winpayment_factor = intval($xml->betwinpayment);

		

		$this->betpanelmainpos = strval($xml->bet_panel->mainpos);

		$this->acceptpanelmainpos = strval($xml->accept_panel->mainpos);

		$this->winpanelmainpos = strval($xml->win_panel->mainpos);

		$this->statepanelmainpos = strval($xml->state_panel->mainpos);



	}

	

	// create database at startup

	function bet_mysql_create()

	{

		global $db;

		$query = "

		CREATE TABLE IF NOT EXISTS `betting` (

		`ID` mediumint(200) COLLATE utf8_unicode_ci NOT NULL AUTO_INCREMENT,

		`login` varchar(200) COLLATE utf8_unicode_ci NOT NULL,

		`nickname` varchar(100) COLLATE utf8_unicode_ci NOT NULL,

		`stake` mediumint(9) NOT NULL,

		`wins` mediumint(9) NOT NULL,

		`countwins` mediumint(6) NOT NULL,

		PRIMARY KEY (ID),

		UNIQUE (login)

		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

				

		mysqli_query($db, $query);

	}

	

	// insert data into database

	function bet_mysql_insert($login, $nickname, $stake, $win, $countwins) {

	global $db;	

	$query = 'INSERT INTO betting (`login`, `nickname`, `stake`, `wins`, `countwins`) 

		VALUES (\''.$login.'\', \''.$nickname.'\', \''.$stake.'\', \''.$win.'\', \''.$countwins.'\')

		ON DUPLICATE KEY UPDATE 

		stake=stake + '.$stake.', 

		wins=wins + '.$win.',

		countwins=countwins + '.$countwins.'

		';

		mysqli_query($db, $query);

	}



	function reset_bet_counter() {

		$this->countsec = -3;

		if (!$this->index) {

			$this->bet_ml_on();

			$this->index = 1;

		}

	}

	

	function reset_bet_counter2() {

		$this->countsec = -90;

	}

	// reset all states at begin round

	function reset_bet() {

		$this->bet_active = 0;

		$this->bet_amount = 0;

		$this->bet_start = 0;

		$this->paybackstarter = 0;

		$this->checkaccept = 0;

		$this->bet_starter = NULL;

		$this->bet_starter_nick = NULL;

		$this->bets = array();

		$this->acceptors = array();

		$this->checkbet = 1;

		$this->index = 0;

		$this->ranks = array();

		$this->showaccept = array();

		$this->bet_winner_ml_off();

		$this->bet_nowinner_ml_off();

		$this->accept_ml_off_all();

		$this->bet_state_ml_off();

	if (!$this->betenabled) {

		$message = '$ff0> $3c0$i$sBetting is disabled for this round!!!'; 

		$this->chat($message,'0F0');

	} elseif (!$this->state) {

		$message = '$ff0> $3c0$i$sDue to low planets betting is disabled! Please donate some. :))'; 

		$this->chat($message,'0F0');

	}

	}



	// count seconds and check if bet/accept still available

	function onEverySecond() {

		$this->countsec++;

		

		if ($this->countsec == $this->timelimit_bet && $this->checkbet && !$this->bet_active && $this->betenabled && $this->state) {

			$this->checkbet = 0;

			$this->bet_ml_off();

			$message = '$ff0> $cf0$iTimelimit exceeded. Nobody started bet! Try it again next round.'; // chat message if nobody started bet

			$this->chat($message,'0F0');

		}

		elseif ($this->countsec == $this->timelimit_accept && !$this->acceptors && $this->bet_active && !$this->checkaccept) {

			$this->checkaccept = 1;

			if ($this->paybackbetstarter) {

				$this->payback_bet_starter();

			}

			foreach ($this->valid_players as $login) {

				$this->accept_ml_off($login);

			}

			$message = '$ff0> $o$cc0$s$iNobody accepted bet from '. $this->bet_starter_nick.'$g$z$o$cc0$s$i with $fff'. $this->bet_amount.' $oplanets!'; // chat message if nobody accepted

			$this->chat($message,'0F0');

			$this->bet_ml_off();

		}

		elseif ($this->countsec == $this->timelimit_accept && $this->acceptors && $this->bet_active) {

			foreach ($this->valid_players as $login) {

				$this->accept_ml_off($login);

			}

		}

		// if ($this->countsec <= $this->timelimit_accept && $this->checkbet && !$this->bet_active && $this->betenabled && $this->state)

		// {

		//console ($this->countsec);

		// }

		// reset login of double click block

		if (time() >= $this->clicktime + 5) $this->clicked = NULL;

		

	}



	// check if player login is TMU account and put into array

	function validate_players() {

		$this->valid_players = array();

		$this->instance()->client->query('GetPlayerList', 300, 0);

		$player_list=$this->instance()->client->getResponse();

		foreach ($player_list as $player) {

				$this->valid_players[] = $player['Login'];

		}

	}

	

	function getServerplanets() {

		

		$this->instance()->client->query('GetServerplanets');

		$splanets = $this->instance()->client->getResponse();

		if ($splanets <= $this->minserverplanets) {

		 $this->state = 0;

		 

		} else {

			$this->state = 1;

		}

		

	}

	

	// refund planets to bet starter if nobody accepted (nadeo tax deducted to avoid server lose planets

	function payback_bet_starter() {

		$planets = $this->bet_amount;

		if ($this->bet_starter_factor) {

			for ($i = 1; $i <= $this->bet_starter_factor; $i++) {

				$planets = $planets - 1 - $planets * 0.01;

			}

			$planets = floor($planets);

		} 

		foreach ($this->acceptors as $nick) {

		console (print_r($nick)."    ".$login);

		}

		$message = 'Refund '.$this->bet_amount.' planets, due to nobody accepted your bet! Nadeo tax was deducted.'; // text in mail for refund planets to bet starter

		$this->instance()->client->query ('Pay', $this->bet_starter, (int)$planets, $message);

		$this->instance()->client->getResponse();

		$this->paybackstarter = 1;

	}

	

	// refund planets if nobody won

	function bet_paybacknowin() {

		$planets = $this->bet_start;

		if ($this->bet_nowin_factor) {

			for ($i = 1; $i <= $this->bet_nowin_factor; $i++) {

				$planets = $planets - 1 - $planets * 0.01;

			}

			$planets = floor($planets);

		} 

		$message = 'Refund '.$this->bet_start.' planets, due to nobody won the stake! Nadeo tax was deducted.'; // text in mail for refund planets to all participants

		

		$q=0;

		foreach ($this->acceptors as $nick => $login) {

		console ($q.":".$login);

		$q++;

			$this->instance()->client->query ('Pay', $login,(int)$planets, $message);

			$this->instance()->client->getResponse();			

		}

	}

	

	// pay winner, decuct one time tax

	function bet_paywin($winner) {

		$planets = $this->bet_amount;

		if ($this->bet_winpayment_factor) {

			for ($i = 1; $i <= $this->bet_winpayment_factor; $i++) {

				$planets = $planets - 1 - $planets * 0.01;

			}

			$planets = floor($planets);

		}

		$message = '$o$fff Congratulations >> $cc3You won the stake of $0f0'.$this->bet_amount.' $fffplanets$cc3! Nadeo tax was deducted.'; // text in mail to winner

		$this->instance()->client->query ('Pay', $winner[0][0], (int)$planets, $message);						

		$this->instance()->client->getResponse();

	}

	

	// get winner of bet and pay win or refund all planets back if no win

	function get_winner($args) {

		$this->bet_ml_off();

		$this->accept_ml_off_all();

		$this->bet_state_ml_off();

		// reset counter for case of restart and skip

		$this->countsec = -30;

		// do action only if somebody accepted bet

		if ($this->acceptors) {

			// get ranking at rounds end

			//$this->instance()->client->query('GetCurrentRanking', 100, 0);

			$ranking = $args; //$this->instance()->client->getResponse();

			// put all players in array

			foreach ($ranking as $key => $var) {

				$this->ranks[$key] = array($var[Login], $var[BestTime], $var[NickName], $var[Rank], $var[Score]);

			}

			// put bet starter into same array as acceptors

			$this->acceptors[betstarter] = $this->bet_starter;

			$this->bet_starter = NULL;

			$winner = array();

			// check for option winner only and if a participant is rank 1

			if ($this->winneronly) {

				 if ((in_array($this->ranks[0][0], $this->acceptors)) && ($this->ranks[0][1] > 0 || $this->ranks[0][3] > 0)) {

					$winner[] = array($this->ranks[0][0], $this->ranks[0][2]);

				 }

			} else {

				// check if bet participants finished challenge and put them in array

				foreach ($this->ranks as $result) {

					if (($result[1] > 0 || $result[3] > 0) && (in_array($result[0], $this->acceptors))) {

						$winner[] = array($result[0], $result[2]);

					}

				}

			}



			// check who is winner, if no winner pay back planets or not

			if ($winner) {

				$this->bet_winner_ml_on($winner);

				$this->bet_paywin($winner);

				$message = '$ff0> '. $winner[0][1] .'$g$z$o$cc0$s$i won the stake with total amount of $fff'.$this->bet_amount.' planets!'; // chat message winner

				$this->chat($message,'0F0');

				$this->bet_mysql_insert($winner[0][0], $winner[0][1], $planets=0, $this->bet_amount, $countwins=1);

			} else {

				$this->bet_nowinner_ml_on();

				$message = '$ff0> $cc0$i$sNobody won the last stake!'; //chat message no winner

				$this->chat($message,'0F0');

				if ($this->paybacknowin) {

					$this->bet_paybacknowin();

				}

			}

		// payback if no acceptors and next/skip was executed

		} elseif ($this->bet_starter && !$this->paybackstarter) {

			$message = '$ff0> $g$z$o$cc0$s$iNobody accepted bet from '. $this->bet_starter_nick.'$g$z$o$cc0$s$i with $fff'. $this->bet_amount.' planets! .'; // chat message in case of skip

			$this->chat($message,'0F0');

			if ($this->paybackbetstarter) {

				$this->payback_bet_starter();

			}

		}

	}

	

	// get bill and set bet state

	function bet_bill_upd($bill) {	

		$billid = $bill[0];

		if (@array_key_exists($billid, $this->bets)) {

			// get bill info

			$login = $this->bets[$billid][0]; 

			$nickname = $this->bets[$billid][1];

			$planets = $this->bets[$billid][2];

			$checkbet = $this->bets[$billid][3];

			// check bill state

				switch($bill[1]) {

				case 4:  // Payed (Paid)

					if ($planets > 0) {

						if (!$this->bet_active and $login != $this->bet_starter and ($checkbet === true)) {

							$this->bet_active = 1;

							$this->bet_starter = $login;

							$this->bet_starter_nick = $nickname;

							$this->bet_start = $planets;

							$this->bet_amount = $planets;

							$this->bet_ml_off();

							$this->accept_ml_on();

							$message = '$ff0>'.$nickname.'$g$z$o$cc0$s$i set$fff '.$planets.'$fff planets $cc0for next bet!'; // chat message if somebody started bet

							$this->chat($message,'0F0');

							$this->bet_starter = $login;

							$this->bet_mysql_insert($login, $nickname, $planets, $win=0, $countwins=0);

							//$this->bet_state_ml_on();

						}

						elseif (!in_array($login, $this->acceptors) and ($login != $this->bet_starter) and ($checkbet === false)) {

							$this->acceptors[$nickname] = $login;

							$this->accept_ml_off($login);

							$count = count($this->acceptors) + 1;

							$this->bet_amount = $count * $this->bet_start;

							$message = '$ff0> '.$nickname.'$g$z$o$cc0$s$i accepted bet! Total win is $fff'.$this->bet_amount.' planets $cc0now!'; // chat message if somebody accepted bet

							$this->chat($message,'0F0');

							$this->accept_ml_off($login);

							$this->bet_mysql_insert($login, $nickname, $planets, $win=0, $countwins=0);

							$this->bet_state_ml_on();

						}

						elseif ($this->bet_active and $checkbet === true) {

							$message = '$ff0> '.$nickname.'$g$z$o$cc0$s$i - login: '. $login .' $cc0was to slow to bet!'; // chat message if somebody try to cheat

							$this->chat($message,'0F0');

							//@$this->console('$ff0> '. $nickname .'$g$z$9c0$s$i with login:'. $login .' tried to cheat or is just to slow to bet!');

						}

					}

					unset($this->bets[$billid]);

					break;

				case 5:  // Refused

					$message = '> $f00Transaction refused, no bet placed!';

					$this->chatToLogin($login, $message, 'f60'); 

					unset($bets[$billid]);

					break;

				case 6:  // Error

					$message = '> $f00Transaction failed: {#highlite}$i ' . $bill[2];

					if ($login != '')

					$this->chatToLogin($login, $message, 'f60'); 

					else

						$this->chat($message,'0F0');

					unset($this->bets[$billid]);

					break;

				default:  // CreatingTransaction/Issued/ValidatingPay(e)ment

					break;

				}

			}

	} // bet_updated end

	

	// excute button click as chat command

	function onManialinkPageAnswer($command){

		$playerid = $command[0];

		$login = $command[1];

		$action = $command[2];

		

		// only go ahead if not same login or 5 seconds later than first click

		if ($this->clicked != $login) {

			// try to avoid any action for doubleclick

			$this->clicked = $login;

			$this->clicktime = time();

			

			switch ($action) {

				case $this->mlids[1]:

					$chat = array();

					$chat[0] = $playerid;

					$chat[1] = $login;

					$chat[2] = '/bet '. $this->bet1 .'';

					$chat[3] = true;

					$this->playerChat($chat);

					//$this->clicked = $login;

				break;

				case $this->mlids[2]:

					$chat = array();

					$chat[0] = $playerid;

					$chat[1] = $login;

					$chat[2] = '/bet '. $this->bet2 .'';

					$chat[3] = true;

					$this->playerChat($chat);

					//$this->clicked = $login;

				break;

				case $this->mlids[3]:

					$chat = array();

					$chat[0] = $playerid;

					$chat[1] = $login;

					$chat[2] = '/bet '. $this->bet3 .'';

					$chat[3] = true;

					$this->playerChat($chat);

					//$this->clicked = $login;

				break;

				case $this->mlids[4]:

					$chat = array();

					$chat[0] = $playerid;

					$chat[1] = $login;

					$chat[2] = '/bet '. $this->bet4 .'';

					$chat[3] = true;

					$this->playerChat($chat);

					//$this->clicked = $login;

				break;

				case $this->mlids[5]:

					$chat = array();

					$chat[0] = $playerid;

					$chat[1] = $login;

					$chat[2] = '/bet '. $this->bet5 .'';

					$chat[3] = true;

					$this->playerChat($chat);

					//$this->clicked = $login;

				break;
				
				case $this->mlids[13]:

					$chat = array();

					$chat[0] = $playerid;

					$chat[1] = $login;

					$chat[2] = '/bet '. $this->bet6 .'';

					$chat[3] = true;

					$this->playerChat($chat);

					//$this->clicked = $login;

				break;
                    case $this->mlids[14]:

					$chat = array();

					$chat[0] = $playerid;

					$chat[1] = $login;

					$chat[2] = '/bet '. $this->bet7 .'';

					$chat[3] = true;

					$this->playerChat($chat);

					//$this->clicked = $login;

				break;
				case $this->mlids[7]:

					$chat = array();

					$chat[0] = $playerid;

					$chat[1] = $login;

					$chat[2] = '/accept';

					$chat[3] = true;

					$this->playerChat($chat);

					//$this->clicked = $login;

				break;

				case $this->mlids[8]:

					$chat = array();

					$chat[0] = $playerid;

					$chat[1] = $login;

					$chat[2] = '/bettotalstake';

					$chat[3] = true;

					$this->playerChat($chat);

					//$this->clicked = $login;

				break;

				default:

				break;	

			}

		}

	} // placebet end

		

	/************************** MANIALINKS START *********************************/	



		// display manialink for betting buttons

		function bet_ml_on() {

		//$this->chat('we are in Betting'.$this->betenabled.$this->state);

			if ($this->betenabled && $this->state) {

		

		//$xml = '<manialink id="'.$this->mlids[0].'">

		$xml='<frame posn="'.$this->betpanelmainpos.'">

					<format style="TextCardInfoSmall" textsize="2" />
					
					<quad posn="7.45 0 0" sizen="39.5 2.5" bgcolor="1919194d" halign="center" valign="center"/>
					
					<quad posn="-7.25 0 1" sizen="10 2.5" bgcolor="1919194d" halign="center" valign="center"/> 
					
					

					<label posn="-7 0.4 3" sizen="8 2" halign="center" valign="center" scale="1.25" textfont="Stunts/XBall" text="$s$o$i$ff0Place bet" />
					
						
						
				<frame posn="0 0 1">	
		
					<label posn="0 0.4 2" sizen="8 2" halign="center" valign="center" scale="1.25"  textfont="Stunts/XBall" text="$i$0f0'.$this->bet1.'"/>

					<label posn="4.2 0.4 2" sizen="8 2" halign="center" valign="center" scale="1.25"  textfont="Stunts/XBall"  text="$i$fff'.$this->bet2.'"/>

					<label posn="8.4 0.4 2" sizen="8 2" halign="center" valign="center" scale="1.25"  textfont="Stunts/XBall"  text="$i$0f0'.$this->bet3.'"/>

					<label posn="12.6 0.4 2" sizen="8 2" halign="center" valign="center" scale="1.25"  textfont="Stunts/XBall"  text="$i$fff'.$this->bet4.'"/>

					<label posn="16.8 0.4 2" sizen="8 2" halign="center" valign="center" scale="1.25"  textfont="Stunts/XBall"  text="$i$0f0'.$this->bet5.'"/>
					
					<label posn="21 0.4 2" sizen="8 2" halign="center" valign="center" scale="1.25"  textfont="Stunts/XBall"  text="$i$fff'.$this->bet6.'"/>
					
					<label posn="25.2 0.4 2" sizen="8 2" halign="center" valign="center" scale="1.25" textfont="Stunts/XBall"  text="$i$0f0'.$this->bet7.'"/>

					
					
					
					<quad posn="0 0 1" sizen="3.96 2.5" halign="center" valign="center"  bgcolor="1919194d" bgcolorfocus="ffff0058"  action="'.$this->mlids[1].'"/>
					
					<quad posn="4.2 0 1" sizen="3.96 2.5" halign="center" valign="center"  bgcolor="1919194d" bgcolorfocus="ffff0058"   action="'.$this->mlids[2].'"/>
					
					<quad posn="8.4 0 1" sizen="3.96 2.5" halign="center" valign="center"  bgcolor="1919194d" bgcolorfocus="ffff0058"   action="'.$this->mlids[3].'"/>

					<quad posn="12.6 0 1" sizen="3.96 2.5" halign="center" valign="center"  bgcolor="1919194d" bgcolorfocus="ffff0058"   action="'.$this->mlids[4].'"/>
					
                    <quad posn="16.8 0 1" sizen="3.96 2.5" halign="center" valign="center"  bgcolor="1919194d" bgcolorfocus="ffff0058"   action="'.$this->mlids[5].'"/>
					 
					<quad posn="21 0 1" sizen="3.96 2.5" halign="center" valign="center"  bgcolor="1919194d" bgcolorfocus="ffff0058"   action="'.$this->mlids[13].'"/>
					  
					<quad posn="25.2 0 2" sizen="3.96 2.5" halign="center" valign="center"  bgcolor="1919194d" bgcolorfocus="ffff0058"   action="'.$this->mlids[14].'"/>
					 
					
					  
					     </frame>   </frame>';

					

					//</manialink>';

					$this->displayManialinkToLogin(implode(',', $this->valid_players), $xml, $this->mlids[0],0);



			}

	}  // display_manialink

	

	// display manialink for betting buttons off

	function bet_ml_off() {

			$this->closeMl($this->mlids[0]);	

	}  // display_manialink

	

	// display manialink for accept button

	function accept_ml_on() {

		foreach ($this->valid_players as $login) {

			if ($login != $this->bet_starter) {

				$this->showaccept[] = $login;

			}

		}

			$xml='<frame posn="'.$this->acceptpanelmainpos.'">

			<format style="TextCardInfoSmall" textsize="3" />

		    <quad sizen="12 5 0"  bgcolor="1919194d" bgcolorfocus="00ff0058"  halign="center" valign="center" action="'.$this->mlids[7].'"/>

			<label posn="0 1.1 1" sizen="10 4" halign="center" valign="center" scale="0.8" stextfont="Stunts/XBall" text="$i$o$s$fffAccept Bet"/>

			<label posn="0 -1 1" sizen="10 4" halign="center" valign="center" textfont="Stunts/XBall" text="$i$o$s$fffStake $ff0 '.$this->bet_start.' P" />
			
			

			</frame>';

			$this->displayManialinkToLogin(implode(',', $this->showaccept),$xml, $this->mlids[6],0);

	}  // display_manialink



	// display manialink for accept button off

	function accept_ml_off($login) { 

		$xml = '<manialink id="'.$this->mlids[6].'">

			</manialink>';

			$this->closeMl($this->mlids[6], $login);	

	}  // display_manialink

	

	// display manialink for accept button off to all

	function accept_ml_off_all() { 

		$xml = '<manialink id="'.$this->mlids[6].'">

			</manialink>';

			$this->closeMl($this->mlids[6]);	

	}  // display_manialink

	

	// display manialink for betting winner

	function bet_winner_ml_on($winner) {

		$xml = '	<frame posn="'.$this->winpanelmainpos.'">

			<format style="TextCardMedium" textsize="3" />

			<label posn="0 0 0" sizen="40 3" halign="center" valign="center"  text="$i$s$o'.$winner[0][1].' $g$z$i$s$o$c90won  '.$this->bet_amount.' planets!" />

			</frame>';

			$this->displayManialink($xml,$this->mlids[8],1,'',0);	

	}  // display_manialink

	

	// display manialink for betting winner off

	function bet_winner_ml_off() {

		$xml = '<manialink id="'.$this->mlids[8].'">

			</manialink>';

			$this->closeMl($this->mlids[8]);	

	}  // display_manialink

	

	// display manialink for betting if no winner

	function bet_nowinner_ml_on() {

			$xml = '<frame posn="'.$this->winpanelmainpos.'">

			<format style="TextCardMedium" textsize="3" />

			<label posn="0 0 0" sizen="30 5" halign="center" valign="center"  text="$i$s$o$f90Nobody won the last stake!" />

			</frame>';

			$this->displayManialink($xml,$this->mlids[9],1,'',1);		

	}  // display_manialink

	

	// display manialink for betting if no winner winner off

	function bet_nowinner_ml_off() {

		$xml = '<manialink id="'.$this->mlids[9].'">

			</manialink>';

			$this->closeMl($this->mlids[9]);		

	}  // display_manialink

	

	// display manialink state of bet

	function bet_state_ml_on() {

			$xml = '<frame posn="'.$this->statepanelmainpos.'">

			<format style="TextCardInfoSmall" textsize="1" />

			<label posn="0 0 0" sizen="13 3" halign="center" valign="center"  text="$i$s$o$b70Total win '.$this->bet_amount.' P" action="'.$this->mlids[7].'" />

			</frame>';

			$this->displayManialink($xml,$this->mlids[10],1,'',1);		

	}  // display_manialink

	

	// display manialink state of bet off

	function bet_state_ml_off() {

		$xml = '<manialink id="'.$this->mlids[10].'">

			</manialink>';

			$this->closeMl($this->mlids[10]);		

	}  // display_manialink



/************************** MANIALINKS END *********************************/		



/*********************** CHAT COMMANDS START *******************************/

	

// proceed chat command bet and get bill id

	function bet_command($command) {

		$player = $command[0]; 	// get author

		$login = $command[1]; 		// get login of author

		$this->instance()->client->query('GetDetailedPlayerInfo',$command[1]);

		$nickn = $this->instance()->client->getResponse();

		$nickname=$nickn['NickName'];

		$planetsp=explode(" ",$command[2]);

		$planets = $planetsp[1]; 	// get parameter	

        // check if betting is enabled

        if ($this->betenabled && $this->state) {

            // check for valid amount

            if ($planets != '' && is_numeric($planets)) {

                // check for betting time limit

                if (($this->countsec <= $this->timelimit_bet-2) && ($this->bet_active == 0)) {

                    // check for minimum donation

                    if ($planets >= $this->minbet and $planets <= $this->maxbet) {

                        // check for double command

                        if ($login != $this->bet_starter and !in_array($login, $this->acceptors)) {

                            // start the transaction

                            $message = '$f80$iYou have to pay '.$planets.' planets to set the next bet!$g$z'; // text in bill popup "start bet"

                            $this->instance()->client->query('SendBill', $login, (int)$planets, $message, '');

                            $billid = $this->instance()->client->getResponse();

                            $this->bets[$billid] = array($login, $nickname, $planets, true);

                        }

                        else {

                            $message = ('> $f00You don\'t need to double click or double execute the chat command!!!!');

                            $this->chatToLogin($login,$message);

                        }	

                    } else {

                        $message = ('> $f00You\'ll have to set {#highlite}minimum '.$this->minbet.' $f00planets and not more than {#highlite}'. $this->maxbet .' $f00to proceed the bet!');

                         $this->chatToLogin($login,$message);

                    }

                } else {

                    $message = ('> $f00Time limit for bet expired or bet already placed, wait till next round!');

                     $this->chatToLogin($login,$message);

                }

            } else {

                $message = ('> $f00No amount of planets defined. Please use {#interact}"/bet [planets]"');

                 $this->chatToLogin($login,$message);

            }

        } else {

            $message = ('> $f00Betting is disabled!');

             $this->chatToLogin($login,$message);

        }

		 

	} //chat commmand bet planets end



	// proceed chat command accept bet and get bill id

	function accept_command($command) {

		$player = $command[0]; 	// get author

		$login = $command[1]; 		// get login of author

		$this->instance()->client->query('GetDetailedPlayerInfo',$command[1]);

		$nickn = $this->instance()->client->getResponse();

		$nickname=$nickn['NickName'];

		$planetsp=explode(" ",$command[2]);

		$planets = $planetsp[1]; 	// get parameter

		

        // check if acceptor is not bet starter

        if ($login != $this->bet_starter) { 

            // check if bet is set

            if ($this->bet_active) {

                // check if player already accepted

                if (!$this->acceptors || !in_array($login, $this->acceptors)) {

                    // check for betting time limit

                    if (($this->countsec <= $this->timelimit_accept)) {

                            // start the transaction

                            $message = '$f80$s$iYou have to pay '.$this->bet_start.' planets to accept the bet!$g$z'; // text in bill popup "accept bet"

                             $this->instance()->client->query('SendBill', $login, (int)$this->bet_start, $message, '');

                            $billid = $this->instance()->client->getResponse();

                            $this->bets[$billid] = array($login, $nickname, $this->bet_start, false);

                    } else {

                        $message = ('> $f00Time limit to accept expired, wait till next round!');

                        $this->chatToLogin($login,$message);

                    }

                } else {

                    $message = ('> $f00You accepted already!!!');

                    $this->chatToLogin($login,$message);

                }

            } else {

                    $message = ('> $f00No bet started yet!!!');

                    $this->chatToLogin($login,$message);

                }

        } else {

                $message = ('> $f00You\'ve just started the bet by yourself!');

                $this->chatToLogin($login,$message);

            }



	} //chat commmand accept bet end

	

	// admin chat command enable bettting

	function enable_command($command) {

		$player = $command[0]; 	// get author

		$login = $command[1]; 		// get login of author

		$this->instance()->client->query('GetDetailedPlayerInfo',$command[1]);

		$nickn = $this->instance()->client->getResponse();

		$nickname=$nickn['NickName'];

		$comt = explode(" ",$command[2]);

		$com = strtolower($comt[1]);

		// check if chat command was allowed for a masteradmin/admin/operator

		

		$rights = $this->getRights($login);

		console($login.$rights[0]);

		if ($rights[0]==2 || $rights[0]==3) {

					// check for unlocked password (or unlock command)

			if ($com) {

					switch ($com) {

						case 'on':

							$this->betenabled = 1;

							$message = ('> '.$nick.' $g$cf0enabled betting. Start next round!');

							$this->chat($message);

						break;

						case 'off':

							$this->betenabled = 0;

							$message = ('> '.$nick.' $g$cf0disabled betting. This is the last round!');

							$this->chat($message);

						break;

						default:

						break;	

					}

				} else {

					$message = '> $f00Missing parameter. Usage like $fff"/betstate on/off"$f00!';

					$this->chatToLogin($login,$message);

				}



		} else {

			// show chat message

			$this->chatToLogin($login,'$f00You don\'t have the required admin rights to do that, unlock first!');

		}

	}

	

/*********************** CHAT COMMANDS END *******************************/

function betting_planets_startup(){

	global $bcp;

	$this->startUp();

}



function onCommand($command){

	$author=$command[1];  //the Command author

	$com1=$command[2];		// the command

	if ($com1=="accept" ) {

		$command[2]="/accept";

		$this->playerchat($command); }

	elseif ($com1=="bet" && intval($command[3][0])>=1) {

		$command[2]="/bet ".intval($command[3][0]);

		$this->playerchat($command); }

	elseif ($com1=="betstate" ) {

		$command[2]="/betstate ".($command[3][0]);

		$this->playerchat($command); }

}





function playerchat ($command)

{

	global $bcp;

	$tmpc=array();					

	@$tmpc=explode(" ",$command[2]);

	if	($tmpc[0]=='/bet') { $this->chat_Bet($command); }

	else if	($tmpc[0]=='/accept') { $this->chat_accept($command);}

	else if	($tmpc[0]=='/betstate') { $this->chat_betstate($command);}

}

	

function chat_Bet($command) {

	global $bcp;

	$this->bet_command($command);

}



function chat_accept($command) {

	global $bcp;

	$this->accept_command($command);

}



function chat_betstate($command) {

	global $bcp;

	$this->enable_command($command);

}



function onBeginMap()

{

	global $bcp;

	$this->reset_bet_counter2();

}



function onBeginMatch()

{

	global $bcp;

	$this->reset_bet_counter();

	$this->validate_players();

	$this->getServerplanets();

	$this->countsec=0;

	$this->bet_ml_on();

	$this->reset_bet();

}



function onBillUpdated($bill)

{

	global $bcp;

	$this->bet_bill_upd($bill);

}



function onEndMatch ($args)

{

	global $bcp;

	$this->get_winner($args[0]);

}

} // end class



?>
<?php
//* chat.player.php - Player Chat Commands
//* Version:   0.4
//* Coded by:  libero6, cyrilw
//* Copyright: FoxRace, http://www.fox-control.de
//* Version:   0.5
//* adaptation
//* 2013.09.22 pastis
//* Version: 0.6
//* Pastis-51
//* 2014.11.09
//* change style windows

class chat_player extends FoxControlPlugin {           
	public $commandsPerPage = 17;
	public $helpUsers = array();
	
	public function onStartUp() {
		//Register Chat Commands
		$this->registerCommand('afk', 'Sets you in the AFK-mode', false);
		$this->registerCommand('lol', 'Displays $sLooOOooL$s message', false);
		$this->registerCommand('brb', 'Displays $sBe Right Back$s message', false);
		$this->registerCommand('gga', 'Displays $sGood Stunt All$s message', false);
		$this->registerCommand('gg', 'Displays $sGood Stunt$s message or Good Stunts NickName', false);
		$this->registerCommand('gs', 'Displays $sGo Go Go Stunters', false);
		$this->registerCommand('n1', 'Displays $sNice One message or Nice One NickName', false);
		$this->registerCommand('thx', 'Displays $sThanks message or Thanks NickName', false);
		$this->registerCommand('waz', 'Displays $sWaaZaa', false);
		$this->registerCommand('help', false, false);
		$this->registerCommand('me', 'Emotical chat message', false);
		$this->registerCommand('help', false, false);
		$this->registerCommand('ping', 'Shows your ping', false);
		$this->registerCommand('re', 'Displays $sReuh$s message', false);
		$this->registerCommand('bb', 'Displays $sGood night have fun message$s message', false);
		
		$this->registerMLIds(1);
		
		//Set general Plugin information
		$this->name = 'Player Chat';
		$this->author = 'Cyril & Libero';
		$this->version = '0.6';
	}
	public function onCommand($args) {
		global $settings;
	
		//Get Player Infos
		$this->instance()->client->query('GetDetailedPlayerInfo', $args[1]);
		$CommandAuthor = $this->instance()->client->getResponse();
		
		//AFK
		if($args[2] == 'afk') {
			$this->instance()->chat_with_nick('$o$f51A$fffway $f51F$fffrom $f51K$fffeyboard', $CommandAuthor['Login']);
			$this->instance()->client->query('ForceSpectator', $CommandAuthor['Login'], 1);
			$this->instance()->client->query('ForceSpectator', $CommandAuthor['Login'], 0);
			
			$this->displayManialinkToLogin($CommandAuthor['Login'], '<quad posn="0 -27 1" sizen="25 4" halign="center" style="'.$settings['default_style2'].'" substyle="'.$settings['default_substyle2'].'" action="'.$this->mlids[0].'" /><label posn="0 -28 2" halign="center" style="TextPlayerCardName" text="$o$fffClick here to play!" action="'.$this->mlids[0].'" />', $this->mlids[0]);
		

		
		
		
		
		//LOL
		} else if($args[2] == 'lol') {
			$this->instance()->chat_with_nick('$o$f51L$fffo$f510$fffo$f51L', $CommandAuthor['Login']);
			
			
			//Re
		} else if($args[2] == 're') {
			$this->instance()->chat_with_nick('$o$f51R$fffeuh $f51S$ffftunters', $CommandAuthor['Login']);
			
				//Bb
		} else if($args[2] == 'bb') {
			$this->instance()->chat_with_nick('$o$f51G$fffood $f51n$fffight , $f51H$fffave $f51f$fffun', $CommandAuthor['Login']);
		
		//BRB
		} else if($args[2] == 'brb') {
			$this->instance()->chat_with_nick('$o$f51B$fffe $f51R$fffight $f51B$fffack', $CommandAuthor['Login']);
		
		//GGA
		} else if($args[2] == 'gga') {
			$this->instance()->chat_with_nick('$o$f51G$fffood $f51S$ffftunts $f51A$fffll', $CommandAuthor['Login']);
		
		//GG
		
			} else if($args[2] == 'gg') {
			$message = '';
			for($i = 0; isset($args[3][$i]); $i++)
			{
				$message = $message.$args[3][$i].' ';
			}
			$message = $this->instance()->rgb_decode($message);
			$this->chat('$i$fff'.$CommandAuthor['NickName'].'$n$09f>> $z$o$f51G$fffood $f51S$ffftunts $ff0'.$message.'$f51!', false);
	
		
		//Nice One
		
			} else if($args[2] == 'n1') {
			$message = '';
			for($i = 0; isset($args[3][$i]); $i++)
			{
				$message = $message.$args[3][$i].' ';
			}
			$message = $this->instance()->rgb_decode($message);
			$this->chat('$i$fff'.$CommandAuthor['NickName'].'$n$09f>> $z$o$f51N$fffice $f51O$fffne $ff0'.$message.'$f51!', false);
	
	//Gs//Go Stunters
		} else if($args[2] == 'gs') {
			$this->instance()->chat_with_nick('$o$f51G$fffo$f51 $o$f51G$fffo$f51 $o$f51G$fffo$f51 $f51S$ffftunter$f51s $fff!', $CommandAuthor['Login']);
		
		//THX
		
			} else if($args[2] == 'thx') {
			$message = '';
			for($i = 0; isset($args[3][$i]); $i++)
			{
				$message = $message.$args[3][$i].' ';
			}
			$message = $this->instance()->rgb_decode($message);
			$this->chat('$i$fff'.$CommandAuthor['NickName'].'$n$09f>> $z$o$f51T$fffhanks $ff0'.$message.'$f51!', false);
	//Gs
		} else if($args[2] == 'gs') {
			$this->instance()->chat_with_nick('$o$f51G$fffo$f51 $o$f51G$fffo$f51 $o$f51G$fffo$f51 $f51S$ffftunter$f51s $fff!', $CommandAuthor['Login']);
		
		
		//waz
		} else if($args[2] == 'waz') {
			$this->instance()->chat_with_nick('$o$f51W$fffaaa$f51Z$fffaaa $f51!!!', $CommandAuthor['Login']);
		
		//HELP
		} else if($args[2] == 'help') {
			if(!empty($args[3][0])) $site = ($args[3][0]-1);
			else $site = 0;
			$this->helpUsers[$args[1]] = $site;
			
			$window = $this->window;
			$window->init();
			$window->title('$070H$fffelp');
			$window->close(true);
			$window->displayAsTable(true);
			$window->size(70, '');
			$window->posY('36.8');
			$window->target('onButtonPressed', $this);
			
			$window->content('<td width="15">Command</td><td width="2"></td><td width="50">Description</td>');
			$window->content(' ');
			
			$help = $this->instance()->getCommands('player');
			$commands = 0;
			
			for($i = ($site * $this->commandsPerPage); $i < count($help); $i++) {
				$window->content('<td width="15">/'.$help[$i][0].'</td><td width="2"></td><td width="50">'.$help[$i][1].'</td>');
				$commands++;
				if($commands >= $this->commandsPerPage) break;
			}
			
			if($site > 0) $window->addButton('<', 7, false);
			else $window->addButton('', 7, false);
			
			$window->addButton('Close', 15, true);
			
			if(($i+1) < count($help)) $window->addButton('>', 7, false);
			else $window->addButton('', 7, false);
			
			$window->show($args[1]);
		
	
			
			
		//ME
		} else if($args[2] == 'me') {
			$message = '';
			for($i = 0; isset($args[3][$i]); $i++)
			{
				$message = $message.$args[3][$i].' ';
			}
			$message = $this->instance()->rgb_decode($message);
			$this->chat('$i$fff'.$CommandAuthor['NickName'].'$z$i$s$fff  '.$message, 'fff', false);
		} else if($args[2] == 'ping') { //ping
			$this->chatToLogin($args[1], 'Pong!');
		}
	}
	
	public function onManialinkPageAnswer($args) {
		if($args[2] == $this->mlids[0]) {
			$this->instance()->client->query('ForceSpectator', $args[1], 2);
			$this->instance()->client->query('ForceSpectator', $args[1], 0);
			$this->closeMl($this->mlids[0], $args[1]);
			
			$this->instance()->chat_with_nick('$09fI$fff\'m $09fB$fffack', $args[1]);
		}
	}
	
	public function onButtonPressed($args) {
		if($args[2] == 1) { //<
			$newargs = array(1 => $args[1], 2 => 'help', 3 => array(0 => $this->helpUsers[$args[1]]));
			$this->onCommand($newargs);
		} else if($args[2] == 3) { //>
			$newargs = array(1 => $args[1], 2 => 'help', 3 => array(0 => ($this->helpUsers[$args[1]] + 2)));
			$this->onCommand($newargs);
		}
	}
}
?>
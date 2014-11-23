<?php
//* Server Menu Plugin
//* Version:   1.0
//* Coded by:  Spaï
//* 2014.07.09 (Spaï) first Stunts Control version
  
class plugin_servermenu extends FoxControlPlugin {
	
	/* STARTUP FUNCTION	*/
	public function onStartUp()
	{
		$this->name = 'Server Menu';
		$this->author = 'Spaï, Pastis';
		$this->version = '1.1';
		
		// Register Chat Command
		$this->registerCommand('topbets', 'Show top bets', false);
		$this->registerCommand('topbonus', 'Show top bonus', false);
		$this->registerCommand('topcatchme', 'Show top catchme', false);
		$this->registerCommand('topdons', 'Show top dons', false);
		$this->registerCommand('toplottery', 'Show top lottery', false);
		
		// Register ML IDs
		$this->registerMLIds(50);
		
		// Show menu to all
		$this->showServerMenu();
	}
	public function onPlayerConnect($connectedplayer) {
		$this->showServerMenu($connectedplayer['Login']);
	}
	
	
	/* ON CHAT COMMAND FUNCTION	*/
	public function onCommand($args)
	{
		$login = $args[1];
		$command = $args[2];
		
	//	if($command == 'topbets')			$this->getPluginInstance('plugin_stunts')->SendTopWindow('bets', $login);
	//	elseif($command == 'topbonus')		$this->getPluginInstance('plugin_top_players')->SendTopWindow('bonus', $login);
	//	elseif($command == 'topcatchme')	$this->getPluginInstance('plugin_stunts')->SendTopWindow('catchme', $login);
	//	elseif($command == 'topdons')		$this->getPluginInstance('plugin_stunts')->SendTopWindow('dons', $login);
	//	elseif($command == 'toplottery')	$this->getPluginInstance('plugin_stunts')->SendTopWindow('lottery', $login);
	}
	
	public function onManialinkPageAnswer($args)
	{
		$login = $args[1];
		$mlid = $args[2];
		
		switch($mlid)
		{
		
	/* Server Map et Rank */
			case $this->mlids[1]: $this->getPluginInstance('plugin_challenges')->displayList($login); break;
			case $this->mlids[2]: $this->getPluginInstance('plugin_stunters_localrecords')->SendPointsRankTable($login); break;
			case $this->mlids[3]: $this->getPluginInstance('plugin_stunters_localrecords')->SendServerRankTableTest($login); break;
			case $this->mlids[4]: $this->getPluginInstance('plugin_norank')->displayList($login); break;
			case $this->mlids[5]: $this->getPluginInstance('plugin_jukebox')->onCommand( array(1 => $login, 2 => 'jukebox') ); break;
			case $this->mlids[6]: $this->getPluginInstance('plugin_stunters_cup')->SendMapsTable($login); break;
	/* Menu Admin */
	        case $this->mlids[11]: $this->getPluginInstance('chat_admin')->onCommand( array(1 => $login, 2 => 'reboot') ); break;
			case $this->mlids[12]: $this->getPluginInstance('manager_plugins')->onCommand( array(1 => $login, 2 => 'plugins') ); break;
			case $this->mlids[13]: $this->getPluginInstance('chat_admin')->onCommand( array(1 => $login, 2 => 'adminhelp') ); break;		
		    case $this->mlids[14]: $this->getPluginInstance('plugin_mx')->onCommand( array(1 => $login, 2 => 'mx') ); break;
			
	/* TOPS BUTTONS */	
			case $this->mlids[21]: $this->getPluginInstance('plugin_top_players')->onCommand( array(1 => $login, 2 => 'topbets') ); break;
			case $this->mlids[22]: $this->getPluginInstance('plugin_top_players')->onCommand( array(1 => $login, 2 => 'topbonus') ); break;
			case $this->mlids[23]: $this->getPluginInstance('plugin_top_players')->onCommand( array(1 => $login, 2 => 'topcatchme') ); break;
			case $this->mlids[24]: $this->getPluginInstance('plugin_top_players')->onCommand( array(1 => $login, 2 => 'topdons') ); break;
			case $this->mlids[25]: $this->getPluginInstance('plugin_top_players')->onCommand( array(1 => $login, 2 => 'toplottery') ); break;
			case $this->mlids[26]: $this->getPluginInstance('plugin_top_players')->onCommand( array(1 => $login, 2 => 'topactive') ); break;			
			case $this->mlids[27]: $this->getPluginInstance('plugin_top_players')->onCommand( array(1 => $login, 2 => 'topnations') ); break;		
	/* Live Music help BUTTONS */
	        case $this->mlids[31]: $this->getPluginInstance('plugin_players')->onCommand( array(1 => $login, 2 => 'players') ); break;
		    case $this->mlids[32]: $this->getPluginInstance('chat_player')->onCommand( array(1 => $login, 2 => 'help') ); break;
			case $this->mlids[33]: $this->getPluginInstance('plugin_players')->onCommand( array(1 => $login, 2 => 'admins') ); break;
			case $this->mlids[34]: $this->getPluginInstance('plugin_music')->onCommand( array(1 => $login, 2 => 'music') ); break;
			
		}
	}
	
	/* SHOW SERVERMENU */
	public function showServerMenu($login = "")
	{	
		$menuWidth = 70;
		$lineDistance = 6.5;
		$posY = 0;
		
		$ml = '		
		<manialink id="StuntsControlServerMenu" name="SC:ServerMenu" version="1">
		
		<frame posn="142.5 -40">
			<quad id="ServerMenuBtn" posn="0 0 -1" sizen="30 6" halign="center" valign="center" bgcolor="1919194d" bgcolorfocus="000c" scriptevents="1" />
			<label posn="0 0" sizen="28" textprefix="$ddd" text="SERVER MENU" textfont="Stunts/XBall" halign="center" valign="center2" />
		</frame>
		
		<frame id="ServerMenuWindow" posn="160 90 30">
			<quad id="ServerMenuBG" posn="0 0 -2" sizen="'.$menuWidth.' 180" bgcolor="000a" scriptevents="1" />
			';
			
			
			/* Server Map et Rank */
			$ml .= '<frame posn="0 -10">';
			{
				$ml .= '<label posn="22 '.($posY +1).' 3" sizen="30 5" halign="center" valign="center" scale="1.15" textfont="Stunts/XBall" text="$sRankings" />';
				$posY -= $lineDistance;
            }
			if ($this->instance()->pluginIsActive("plugin.challenges.php"))
			{
				$ml .= '<label posn="22 '.($posY +1).' 1" sizen="30 5" halign="center" valign="center" scale="1.15" textfont="Stunts/XBall" text="$sMaps" />';
				$ml .= '<quad posn="22 '.$posY.' 0" sizen="30 5" halign="center" valign="center" bgcolor="030" bgcolorfocus="996c" action="'.$this->mlids[1].'" />';
				$posY -= $lineDistance;
			}
			
			if ($this->instance()->pluginIsActive("plugin.stunters.localrecords.php"))
			{
				$ml .= '<label posn="22 '.($posY +1).' 1" sizen="30 5" halign="center" valign="center" scale="1.15" textfont="Stunts/XBall" text="$sServer Points" />';
				$ml .= '<quad posn="22 '.$posY.' 0" sizen="30 5" halign="center" valign="center" bgcolor="030" bgcolorfocus="996c" action="'.$this->mlids[2].'" />';
				$posY -= $lineDistance;
			}
			
			if ($this->instance()->pluginIsActive("plugin.stunters.localrecords.php"))
			{
				$ml .= '<label posn="22 '.($posY +1).' 1" sizen="30 5" halign="center" valign="center" scale="1.15" textfont="Stunts/XBall" text="$sServer Rank" />';
				$ml .= '<quad posn="22 '.$posY.' 0" sizen="30 5" halign="center" valign="center" bgcolor="030" bgcolorfocus="996c" action="'.$this->mlids[3].'" />';
				$posY -= $lineDistance;
			}	
			
			if ($this->instance()->pluginIsActive("plugin.norank.php"))
			{	
				$ml .= '<label posn="22 '.($posY +1).' 1" sizen="30 5" halign="center" valign="center" scale="1.15" textfont="Stunts/XBall" text="$sNo Rank" />';
				$ml .= '<quad posn="22 '.$posY.' 0" sizen="30 5" halign="center" valign="center" bgcolor="030" bgcolorfocus="996c" action="'.$this->mlids[4].'" />';
				$posY -= $lineDistance;
			}
           
            if ($this->instance()->pluginIsActive("plugin.norank.php"))
			{	
				$ml .= '<label posn="22 '.($posY +1).' 1" sizen="30 5" halign="center" valign="center" scale="1.15" textfont="Stunts/XBall" text="$sJukebox" />';
				$ml .= '<quad posn="22 '.$posY.' 0" sizen="30 5" halign="center" valign="center" bgcolor="030" bgcolorfocus="996c" action="'.$this->mlids[5].'" />';
				$posY -= $lineDistance;
			}	
			
            if ($this->instance()->pluginIsActive("plugin.stunters.cup.php"))
			{	
				$ml .= '<label posn="22 '.($posY +1).' 1" sizen="30 5" halign="center" valign="center" scale="1.15" textfont="Stunts/XBall" text="$sCup Rank" />';
				$ml .= '<quad posn="22 '.$posY.' 0" sizen="30 5" halign="center" valign="center" bgcolor="030" bgcolorfocus="996c" action="'.$this->mlids[6].'" />';
				$posY -= $lineDistance;
			}				
				$ml .='<quad sizen="40 '.(-$posY).'" posn="0 3.25 -1" bgcolor="222f" />';			
			$ml .='</frame>';
			
			
			
			
			/* Menu Admin */
			$menuWidth = 70;
			$lineDistance = 5;
		    $posY = 0;
			
			$ml .= '<frame posn="46 -6">';			
			{
				$ml .= '<label posn="12 '.($posY +0.8).' 1" sizen="20 5" halign="center" valign="center" scale="0.9" textfont="Stunts/XBall" text="$sManager" />';
				$posY -= $lineDistance;
			}
			
			if ($this->instance()->pluginIsActive("chat.admin.php"))
			{
				$ml .= '<label posn="12 '.($posY +0.8).' 1" sizen="20 5" halign="center" valign="center" scale="0.9" textfont="Stunts/XBall" text="$sReboot" />';
				$ml .= '<quad posn="12 '.$posY.' 0" sizen="20 4" halign="center" valign="center" bgcolor="300" bgcolorfocus="996c" action="'.$this->mlids[11].'" />';
				$posY -= $lineDistance;
			}
			
			if ($this->instance()->pluginIsActive("manager.plugins.php"))
			{
				$ml .= '<label posn="12 '.($posY +0.8).' 1" sizen="20 5" halign="center" valign="center" scale="0.9" textfont="Stunts/XBall" text="$sPlugins" />';
				$ml .= '<quad posn="12 '.$posY.' 0" sizen="20 4" halign="center" valign="center" bgcolor="300" bgcolorfocus="996c" action="'.$this->mlids[12].'" />';
				$posY -= $lineDistance;
			}
			
           if ($this->instance()->pluginIsActive("chat.admin.php"))
			{
				$ml .= '<label posn="12 '.($posY +0.8).' 1" sizen="20 5" halign="center" valign="center" scale="0.9" textfont="Stunts/XBall" text="$sHelp" />';
				$ml .= '<quad posn="12 '.$posY.' 0" sizen="20 4" halign="center" valign="center" bgcolor="300" bgcolorfocus="996c" action="'.$this->mlids[13].'" />';
				$posY -= $lineDistance;
			}
			
			
			if ($this->instance()->pluginIsActive("plugin.mx.php"))
			{
				$ml .= '<label posn="12 '.($posY +0.8).' 1" sizen="20 5" halign="center" valign="center" scale="0.9" textfont="Stunts/XBall" text="$sM.X" />';
				$ml .= '<quad posn="12 '.$posY.' 0" sizen="20 4" halign="center" valign="center" bgcolor="300" bgcolorfocus="996c" action="'.$this->mlids[14].'" />';
				$posY -= $lineDistance;
			}
		
				$ml .='<quad sizen="32 '.(-$posY).'" posn="0 2.5 -1" bgcolor="222f" />';			
			$ml .='</frame>';
			
			
			
			
			
		/* TOPS BUTTONS */
			$lineDistance = 6.5;
			$posY = 0;
			
			$ml .= '<frame posn="0 -65">';
			{
				$ml .= '<label posn="22 '.($posY +1).' 3" sizen="30 5" halign="center" valign="center" scale="1.15" textfont="Stunts/XBall" text="$sTops" />';
				$posY -= $lineDistance;
            }
			
			/* AJOUTER LES BOUTONS ICI */ 
			if ($this->instance()->pluginIsActive("plugin.nouse.fox.betting.php"))
			{
				$ml .= '<label posn="22 '.($posY +1).' 1" sizen="30 5" halign="center" valign="center" scale="1.15" textfont="Stunts/XBall" text="$sBets" />';
				$ml .= '<quad posn="22 '.$posY.' 0" sizen="30 5" halign="center" valign="center" bgcolor="030" bgcolorfocus="996c" action="'.$this->mlids[21].'" />';
				$posY -= $lineDistance;
			}
			
			if ($this->instance()->pluginIsActive("plugin.bonusscore.php"))
			{
				$ml .= '<label posn="22 '.($posY +1).' 1" sizen="30 5" halign="center" valign="center" scale="1.15" textfont="Stunts/XBall" text="$sBonus Scores" />';
				$ml .= '<quad posn="22 '.$posY.' 0" sizen="30 5" halign="center" valign="center" bgcolor="030" bgcolorfocus="996c" action="'.$this->mlids[22].'" />';
				$posY -= $lineDistance;
			}
			
			// TODO: vérifier que le plugin est activé
			if ($this->instance()->pluginIsActive("plugin.catchme.php"))
			{
			    $ml .= '<label posn="22 '.($posY +1).' 1" sizen="30 5" halign="center" valign="center" scale="1.15" textfont="Stunts/XBall" text="$sCatch Me" />';
				$ml .= '<quad posn="22 '.$posY.' 0" sizen="30 5" halign="center" valign="center" bgcolor="030" bgcolorfocus="996c" action="'.$this->mlids[23].'" />';
				$posY -= $lineDistance;
			}
			
			if ($this->instance()->pluginIsActive("plugin.donate.php"))
			{
			    $ml .= '<label posn="22 '.($posY +1).' 1" sizen="30 5" halign="center" valign="center" scale="1.15" textfont="Stunts/XBall" text="$sDons" />';
				$ml .= '<quad posn="22 '.$posY.' 0" sizen="30 5" halign="center" valign="center" bgcolor="030" bgcolorfocus="996c" action="'.$this->mlids[24].'" />';	
				$posY -= $lineDistance;
			}
			
			if ($this->instance()->pluginIsActive("plugin.lottery.php"))
			{
			    $ml .= '<label posn="22 '.($posY +1).' 1" sizen="30 5" halign="center" valign="center" scale="1.15" textfont="Stunts/XBall" text="$sLottery" />';
				$ml .= '<quad posn="22 '.$posY.' 0" sizen="30 5" halign="center" valign="center" bgcolor="030" bgcolorfocus="996c" action="'.$this->mlids[25].'" />';
				$posY -= $lineDistance;
			}
				
			if ($this->instance()->pluginIsActive("plugin.top.players.php"))
			{
			    $ml .= '<label posn="22 '.($posY +1).' 1" sizen="30 5" halign="center" valign="center" scale="1.15" textfont="Stunts/XBall" text="$sMost Active" />';
				$ml .= '<quad posn="22 '.$posY.' 0" sizen="30 5" halign="center" valign="center" bgcolor="030" bgcolorfocus="996c" action="'.$this->mlids[26].'" />';
				$posY -= $lineDistance;
			}
			
			if ($this->instance()->pluginIsActive("plugin.top.players.php"))
			{
		    	$ml .= '<label posn="22 '.($posY +1).' 1" sizen="30 5" halign="center" valign="center" scale="1.15" textfont="Stunts/XBall" text="$sNations" />';
				$ml .= '<quad posn="22 '.$posY.' 0" sizen="30 5" halign="center" valign="center" bgcolor="030" bgcolorfocus="996c" action="'.$this->mlids[27].'" />';
				$posY -= $lineDistance;
			}
			
			$ml .='<quad sizen="40 '.(-$posY).'" posn="0 3.25 -1" bgcolor="222f" />';
				$ml .='</frame>';
			
			
			
			
			/* Live Music help BUTTONS */
			$lineDistance = 6.5;
			$posY = 0;
			
			$ml .= '<frame posn="0 -130">';
			{
				$ml .= '<label posn="22 '.($posY +1).' 3" sizen="30 5" halign="center" valign="center" scale="1.15" textfont="Stunts/XBall" text="$sInfo" />';
				$posY -= $lineDistance;
            }
			
			/* AJOUTER LES BOUTONS ICI */ 
			if ($this->instance()->pluginIsActive("plugin.players.php"))
			{
				$ml .= '<label posn="22 '.($posY +1).' 1" sizen="30 5" halign="center" valign="center" scale="1.15" textfont="Stunts/XBall" text="$sPlayer Live" />';
				$ml .= '<quad posn="22 '.$posY.' 0" sizen="30 5" halign="center" valign="center" bgcolor="030" bgcolorfocus="996c" action="'.$this->mlids[31].'" />';
				$posY -= $lineDistance;
			}
			
			if ($this->instance()->pluginIsActive("chat.player.php"))
			{	
				$ml .= '<label posn="22 '.($posY +1).' 1" sizen="30 5" halign="center" valign="center" scale="1.15" textfont="Stunts/XBall" text="$sHelp" />';
				$ml .= '<quad posn="22 '.$posY.' 0" sizen="30 5" halign="center" valign="center" bgcolor="030" bgcolorfocus="996c" action="'.$this->mlids[32].'" />';
				$posY -= $lineDistance;
			}	
			
			if ($this->instance()->pluginIsActive("plugin.players.php"))
			{
				$ml .= '<label posn="22 '.($posY +1).' 1" sizen="30 5" halign="center" valign="center" scale="1.15" textfont="Stunts/XBall" text="$sAdmins" />';
				$ml .= '<quad posn="22 '.$posY.' 0" sizen="30 5" halign="center" valign="center" bgcolor="030" bgcolorfocus="996c" action="'.$this->mlids[33].'" />';
				$posY -= $lineDistance;
			}
			
			if ($this->instance()->pluginIsActive("plugin.music.php"))
			{	
				$ml .= '<label posn="22 '.($posY +1).' 1" sizen="30 5" halign="center" valign="center" scale="1.15" textfont="Stunts/XBall" text="$sMusic" />';
				$ml .= '<quad posn="22 '.$posY.' 0" sizen="30 5" halign="center" valign="center" bgcolor="030" bgcolorfocus="996c" action="'.$this->mlids[34].'" />';
				$posY -= $lineDistance;
			}	
				$ml .='<quad sizen="40 '.(-$posY).'" posn="0 3.25 -1" bgcolor="222f" />
			</frame>
			
			
		</frame>
			
		
		<script><!--
		main ()
		{
			declare AnimateMenuIn = False;
			declare AnimateMenuOut = False;			
			declare ServerMenuWindow <=> (Page.GetFirstChild("ServerMenuWindow") as CMlFrame);
			declare ServerMenuBtn <=> (Page.GetFirstChild("ServerMenuBtn") as CMlQuad);
						
			while(True)
			{
				yield;
				foreach(Event in PendingEvents)
				{
					if(Event.Type == CMlEvent::Type::MouseClick)
					{	
						if (Event.ControlId == "ServerMenuBtn") AnimateMenuIn = True;
						else if (Event.ControlId == "ServerMenuBG") AnimateMenuOut = True;
					}
				}		
			
				if (!AnimateMenuIn && MouseX < 145. - '.$menuWidth.') AnimateMenuOut = True;
				if (AnimateMenuIn)
				{
					ServerMenuWindow.RelativePosition.X -= '.$menuWidth.'/10;
					if (ServerMenuWindow.RelativePosition.X < 160. - '.$menuWidth.')
					{
						ServerMenuWindow.RelativePosition.X = 160. - '.$menuWidth.';
						AnimateMenuIn = False;
					}
				
				}
				else if(AnimateMenuOut)
				{							
					ServerMenuWindow.RelativePosition.X += '.$menuWidth.'/10;
					if (ServerMenuWindow.RelativePosition.X >= 160.)
					{
						ServerMenuWindow.RelativePosition.X = 160.;
						AnimateMenuOut = False;
					}					
				}					
			}
		}
		--></script>
		</manialink>
		';
	
		
		$this->instance()->client->query('SendDisplayManialinkPage', $ml, 0, False);
		
		
	} // showServerMenu End
	
	
	
}
?>
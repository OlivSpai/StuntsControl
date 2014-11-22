<?php
//* plugin.adminpanel.php - Adminpanel
//* Version:   0.1
//* Coded by:  matrix142
//* Copyright: FoxRace, http://www.fox-control.de
//* Version:   0.2
//* UI Designer
//* 2013.09.22 - pastis
//* Version: 0.3
//* Pastis-51
//* 2014.11.09
//* change style windows
class plugin_adminpanel extends FoxControlPlugin {
	public function onStartUp() {
		$this->registerMLIds(8);
		$this->displayAdminpanelToAll();
		
		$this->name = 'Adminpanel';
		$this->author = 'matrix142';
		$this->version = '0.3';
	}
	
	public function onPlayerConnect($args) {
		$this->displayAdminpanel($args['Login']);
	}
	
	public function onBeginMap($args) {
		$this->displayAdminpanelToAll();
	}
	
	public function onEndMap($args) {
		$this->displayAdminpanelToAll(true);
	}
	
	public function displayAdminpanelToAll($is_end_challenge = false) {
		$this->instance()->client->query('GetPlayerList', 300, 0);
		$playerlist = $this->instance()->client->getResponse();
		for($i = 0; $i < count($playerlist); $i++) {
			$this->displayAdminpanel($playerlist[$i]['Login'], $is_end_challenge);
		}
	}
	
	public function displayAdminpanel($login, $is_end_challenge = false) {
		global $settings;
		if($this->enabled == false) return;
		if($this->instance()->is_admin($login) == false) return;
		$ap_mlcode = '
		<frame posn="-5 -16 0">
			<quad posn="55.7 -20 0" sizen="8.5 6" halign="center" bgcolor="1919194d" />
		
			
			
			<quad posn="52.55 -19.75 1" sizen="4 3.5" halign="center" style="UIConstruction_Buttons" action="'.$this->mlids[2].'" substyle="Left" />
			<quad posn="55.55 -19.75 1" sizen="3.5 3.5" halign="center" style="UIConstruction_Buttons" action="'.$this->mlids[3].'" substyle="Reload" />
			<quad posn="58.55 -19.75 2" sizen="4 3.5" halign="center" style="UIConstruction_Buttons" action="'.$this->mlids[0].'" substyle="Right" />
			
			<quad posn="52.55 -23 1" sizen="3 3" halign="center" style="Icons64x64_1" action="'.$this->mlids[5].'" substyle="NotBuddy" />
			<quad posn="55.55 -22.75 2" sizen="3.5 3.5" halign="center" style="Icons64x64_1" action="'.$this->mlids[4].'" substyle="SliderCursor2" />
			<quad posn="58.55 -22.85 2" sizen="3.5 3.5" halign="center" style="UIConstruction_Buttons" action="'.$this->mlids[6].'" substyle="Options" />
			
			</frame>';
		
		if($is_end_challenge == true){
			$ap_mlcode = '
				<frame posn="8 2 0">
					'.$ap_mlcode.'
				</frame>';
		}
		$this->displayManialinkToLogin($login, $ap_mlcode, $this->mlids[0]);
	}
	
	public function onButton($args) {
		if($args[2] == 1) {
			$chatAdmin = $this->getPluginInstance('chat_admin');
			$chatAdmin->onCommand(array(1 => $args[1], 2 => 'remove', 3 => array(0 => 'track', 1 => 'current')));
			
			$window = $this->window;
			$window->closeWindow($args[1]);
		}
	}
	
	public function onManialinkPageAnswer($args) {
		global $settings;
		
		//Get Infos
		$this->instance()->client->query('GetDetailedPlayerInfo', $args[1]);
		$Admin = $this->instance()->client->getResponse();
		
		$rights = $this->getRights($Admin['Login']);
		if($rights[0] == 0) return;
		else if($rights[0] == 1) require('include/op_rights.php');
		else if($rights[0] == 2) require('include/admin_rights.php');
		else if($rights[0] == 3) require('include/superadmin_rights.php');
		
		//SKIP
		if($args[2] == $this->mlids[0]){
			if($skip_challenge==true){
				$this->instance()->challenge_skip();
				$this->chat($rights[1].' $fff'.$Admin['NickName'].'$z$s $f90skipped the map!', 'f90');
			}
			else $this->chatToLogin($Admin['Login'], $settings['Text_wrong_rights']);
		}
		
		//FORCE END ROUND
		elseif($args[2] == $this->mlids[1]){
			if($force_end_round==true){
				$this->instance()->client->query('ForceEndRound');
				$this->chat($rights[1].' $fff'.$Admin['NickName'].'$z$s $f90forced round end!', 'f90');
			}
			else $this->chatToLogin($Admin['Login'], $settings['Text_wrong_rights']);
		}
		
		//RESTART
		elseif($args[2] == $this->mlids[3]){
			if($restart_challenge==true){
				global $chall_restarted_admin;
				$chall_restarted_admin = true;
				$this->instance()->client->query('RestartMap');
				$this->chat($rights[1].' $fff'.$Admin['NickName'].'$z$s $f90restarted the map!', 'f90');
			}
			else $this->chatToLogin($Admin['Login'], $settings['Text_wrong_rights']);
		}
		
		//REPLAY
		else if($args[2] == $this->mlids[2]) {
			$chatAdmin = $this->getPluginInstance('chat_admin');
			
			if($chatAdmin !== false) {
				$chatAdmin->onCommand(array(1 => $args[1], 2 => 'replay'));
			}	
		}
		
		//CANCEL VOTE
		elseif($args[2] == $this->mlids[4]){
			if($cancel_vote==true){
				$this->instance()->client->query('CancelVote');
				$this->chat($rights[1].' $fff'.$Admin['NickName'].'$z$s $f90canceled vote!', 'f90');
			}
			else $this->chatToLogin($Admin['Login'], $settings['Text_wrong_rights']);
		}
		
		//DELETE TRACK
		else if($args[2] == $this->mlids[6]) {
			$chatAdmin = $this->getPluginInstance('chat_admin');
			
			if($chatAdmin !== false) {
				$window = $this->window;
				$window->init();
				$window->title('$fffDelete Map');
				$window->displayAsTable(true);
				$window->size(30, '');
				$window->posY('36.8');
				$window->target('onButton', $this);
				
				$window->content('<td width="25" align="center">Do you really want to delete this Map?</td>');
				
				$window->addButton('Yes', '7', false);
				$window->addButton('', '3', false);
				$window->addButton('No', '7', true);
				
				$window->show($args[1]);
			}
		}
		
		//OPEN PLAYERLIST
		elseif($args[2] == $this->mlids[5]){
			$pluginPlayers = $this->getPluginInstance('plugin_players');
			if($pluginPlayers === false) {
				$this->chatToLogin($args[1], 'Plugin not activated!', 'f60');
			} else {
				$pluginPlayers->onCommand(array(1 => $args[1], 2 => 'players', 3 => array(0 => 'admin')));
			}
		}
	}
}
?>
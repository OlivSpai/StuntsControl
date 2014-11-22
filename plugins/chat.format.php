<?php
//* plugin.customchat.php - Custom Chat
//* Version:   0.4
//* Coded by:  cyrilw
//* Copyright: FoxRace, http://www.fox-control.de

global $chatformat;
$chatformat = array('Default' => '', 'Ops' => '', 'Admins' => '', 'SuperAdmins' => '');

$chatformat['Default'] = '{PLAYER}$06f» $fff{MESSAGE}';
//$chatformat['Ops'] = '{PLAYER}$06f» $fff{MESSAGE}';
//$chatformat['Admins'] = '{PLAYER}$06f» $fff{MESSAGE}';
//$chatformat['SuperAdmins'] = '{PLAYER}$06f» $fff{MESSAGE}';

//-----------------------------------------------------------------------//
// You can use:                                                          //
// {PLAYER}  Nickname of the player                                      //
// {MESSAGE} Chat message                                                //
// {TIME} Current time like this: 12:25                                  //
// {GROUP} Group name of the player: SuperAdmin, Admin, Operator, Player //
//-----------------------------------------------------------------------//
// You MUST set the chatformat for default. It's not required for Ops,   //
// Admins and SuperAdmins, but you can ;)                                //
//-----------------------------------------------------------------------//
// Examples:                                                             //
//                                                                       //
// $chatformat['Default'] = '{PLAYER}$06f» $fff{MESSAGE}';               //
// $chatformat['SuperAdmins'] = '{PLAYER}$f00» $o$ddd{MESSAGE}';         //
// The chatcolor for superadmins is now $ddd and bold, for all normal    //
// players is the chatcolor $fff.                                        //
//                                                                       //
// $chatformat['Default'] = '$ddd[{TIME}] {PLAYER}$06f» $fff{MESSAGE}';  //
// This will generate an output like this:                               //
// [12:25] Cyril» Cool plugin :D                                         //
//-----------------------------------------------------------------------//

class chat_format extends FoxControlPlugin {
	public function onStartUp() {
		$this->instance()->client->query('ChatEnableManualRouting', true);
		
		$this->name = 'Chat format';
		$this->author = 'Cyril';
		$this->version = '0.4';
	}
	public function onChat($args) {
		if(trim($args[2]) !== '') {
			global $chatformat;
			$this->instance()->client->query('GetDetailedPlayerInfo', $args[1]);
			$player = $this->instance()->client->getResponse();
			$rights = $this->getRights($player['Login']);
			if($rights[0] == 0) $message = '$z$s'.$chatformat['Default'];
			else if($rights[0] == 1) {
				if(!empty($chatformat['Ops'])) $message = '$z$s'.$chatformat['Ops'];
				else $message = '$z$s'.$chatformat['Default'];
			} else if($rights[0] == 2) {
				if(!empty($chatformat['Admins'])) $message = '$z$s'.$chatformat['Admins'];
				else $message = '$z$s'.$chatformat['Default'];
			} else if($rights[0] == 3) {
				if(!empty($chatformat['SuperAdmins'])) $message = '$z$s'.$chatformat['SuperAdmins'];
				else $message = '$z$s'.$chatformat['Default'];
			}
			$message = str_replace('{PLAYER}', $player['NickName'].'$z$s', $message);
			$message = str_replace('{MESSAGE}', $args[2], $message);
			$message = str_replace('{TIME}', date('H:i'), $message);
			$message = str_replace('{GROUP}', $rights[1], $message);
			$this->instance()->client->query('ChatSendServerMessage', $message);
		}
	}
}
?>
<?php
//* plugin.donate.php - Donate
//* Version:   1.2
//* Coded by:  matrix142, cyril, libero
//* Copyright: FoxRace, http://www.fox-control.de
//* Version:   1.3
// UI Designer & adaptation & addon config
// 2013.09.22 pastis
//* Version: 1.4
//* Pastis-51
//* 2014.11.09
//* change style windows

global $bills, $donate1, $donate2, $donate3, $donate4, $donate5;

$bills = array();
$donate1 = 50;
$donate2 = 250;
$donate3 = 500;
$donate4 = 1000;
$donate5 = 3000;

class plugin_donate extends FoxControlPlugin {

	public function onStartUp() {
		$this->name = 'Donate';
		$this->author = 'matrix142 & cyril & libero & pastis';
		$this->version = '1.4';
		
		$this->registerMLIds(6);
		
		$this->displayDonatePanel();
		$this->config = $this->loadConfig();
	}
	
	public function onPlayerConnect($args) {
		$this->displayDonatePanel($args['Login']);
	}
	
	public function onBeginMap($args) {
		$this->displayDonatePanel();
	}
	
	public function onEndMap($args){
		$this->displayDonatePanel(false, array(0 => '42.25', 1 => '-77'));
	}
	
	public function displayDonatePanel($login = false, $posn = array(0 => '68.7', 1 => '-92.5')) {
		global $bills, $donate1, $donate2, $donate3, $donate4, $donate5, $settings;
	
			$code = '
		<frame posn="'.$posn[0].' '.$posn[1].' 1">
		
		 <quad posn="-62 48 0" sizen="39.5 2.5" bgcolor="1919194d"/>
	
		<quad posn="-62 48 1" sizen="7.5 2.5" bgcolor="1919194d" bgcolorfocus="00ff0058" action="'.$this->mlids[1].'" />
		<quad posn="-54 48 1" sizen="7.5 2.5"  bgcolor="1919194d" bgcolorfocus="00ff0058"  action="'.$this->mlids[2].'" />
		<quad posn="-46 48 1" sizen="7.5 2.5"   bgcolor="1919194d" bgcolorfocus="00ff0058" action="'.$this->mlids[3].'" />
		<quad posn="-38 48 1" sizen="7.5 2.5"   bgcolor="1919194d" bgcolorfocus="00ff0058"  action="'.$this->mlids[4].'" />
		<quad posn="-30 48 1" sizen="7.5 2.5"   bgcolor="1919194d" bgcolorfocus="00ff0058" action="'.$this->mlids[5].'" />
		
		
		 
		<label posn="-58.25 47.25 2" scale="1" halign="center" valign="center" text="$o$i$fff'.$donate1.'" textfont="Stunts/XBall" />
		<label posn="-50.25 47.25 2" scale="1" halign="center" valign="center" text="$o$i$ff0'.$donate2.'" textfont="Stunts/XBall" />
		<label posn="-42.25 47.25 2" scale="1" halign="center" valign="center" text="$o$i$fff'.$donate3.'" textfont="Stunts/XBall" />
		<label posn="-34.25 47.25 2" scale="1" halign="center" valign="center" text="$o$i$ff0'.$donate4.'" textfont="Stunts/XBall" />
		<label posn="-26.25 47.25 2" scale="1" halign="center" valign="center" text="$o$i$fff'.$donate5.'" textfont="Stunts/XBall" />
		</frame>';
	
		if($login != false){
			$this->displayManialinkToLogin($login, $code, $this->mlids[0]);
		}else{
			$this->displayManialink($code, $this->mlids[0]);
		}
	}

	public function onManialinkPageAnswer($args){
		global $bills, $donate1, $donate2, $donate3, $donate4, $donate5;

		if($args[2] == $this->mlids[1]){
			$this->instance()->client->query('SendBill', $args[1], $donate1, '$o$i$f51Do you want donate $fff'.$donate1.'$f51 Planets?$z', '');
			$billid = $this->instance()->client->getResponse();
			$bills[] = array($args[1], $donate1, $billid);
		}
		
		else if($args[2] == $this->mlids[2]){
			$this->instance()->client->query('SendBill', $args[1], $donate2, '$o$i$f51Do you want donate $fff'.$donate2.'$f51 Planets?$z', '');
			$billid = $this->instance()->client->getResponse();
			$bills[] = array($args[1], $donate2, $billid);
		}
		
		elseif($args[2] == $this->mlids[3]){
			$this->instance()->client->query('SendBill', $args[1], $donate3, '$o$i$f51Do you want donate $fff'.$donate3.'$f51 Planets?$z', '');
			$billid = $this->instance()->client->getResponse();
			$bills[] = array($args[1], $donate3, $billid);
		}

		elseif($args[2] == $this->mlids[4]){
			$this->instance()->client->query('SendBill', $args[1], $donate4, '$o$i$f51Do you want donate $fff'.$donate4.'$f51 Planets?$z', '');
			$billid = $this->instance()->client->getResponse();
			$bills[] = array($args[1], $donate4, $billid);
		}
		elseif($args[2] == $this->mlids[5]){
			$this->instance()->client->query('SendBill', $args[1], $donate5, '$o$i$f51Do you want donate $fff'.$donate5.'$f51 Planets?$z', '');
			$billid = $this->instance()->client->getResponse();
			$bills[] = array($args[1], $donate5, $billid);
		}
	}

	public function onBillUpdated($BillId){
		global $bills, $donate1, $donate2, $donate3, $donate4, $donate5;
	
		$billid = $BillId[0];
	
		$curr_id = 0;
		$billid_is_don = false;
		while(isset($bills[$curr_id])){
			$b_curr_data = $bills[$curr_id];
			if($b_curr_data[2]==$billid){
				$billid_is_don = true;
				break;
			}
			$curr_id++;
		}
	
		if($billid_is_don==true){
			$billarray = $bills[$curr_id];
	
			$billlogin = $billarray[0];
			$billcoppers = $billarray[1];
			$this->instance()->client->query('GetDetailedPlayerInfo', $billlogin);
			$billpdata = $this->instance()->client->getResponse();
	
	
			if($BillId[1]=='4'){
				global $db;
				$this->chat('$fff'.$billpdata['NickName'].'$z$o$i$f51 donated $fff'.$billcoppers.'$f51 Planets! $z'.$this->config->settings->post_thanks.'  ');
				$sql = "SELECT * FROM players WHERE playerlogin='".trim($billlogin)."'";
				if($mysql = mysqli_query($db, $sql)){
					if($donsdata = $mysql->fetch_object()){
						$dons = $donsdata->donations;
						$dons = $dons+$billcoppers;
						$sql = "UPDATE players SET donations='".$dons."' WHERE playerlogin='".trim($billlogin)."'";
						if($mysql = mysqli_query($db, $sql)){
							$updated = true;
						}
					}
				}
			}
			elseif($BillId[1]=='5'){
				$this->chatToLogin($billlogin, '$f00Transaction refused!');
			}
			elseif($BillId[1]=='6'){
				$this->chatToLogin($billlogin, '$f00Transaction error!');
			}
		}
	}
}
?>
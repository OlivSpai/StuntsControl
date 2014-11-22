<?php
//* plugin.podium.php - windows podium
//* Version:   0.6
//* Coded by:  cyrilw, libero6, matrix142
//* Copyright: FoxRace, http://www.fox-control.de
//* Version:   0.7
//* adaptation Stunters
//* 2013.09.22  pastis-51
//* Version:   0.8
//* Pastis-51
//* 2014.11.09
//* change style windows - additional top nation

class plugin_podium extends FoxControlPlugin {
	public $config;
	public $posn_topdonate;
	public $posn_topbet;
	public $posn_mostactive;
	public $posn_topnation;
	
	public function onStartUp() {
		global $settings, $widget_code_topdonate, $widget_code_topbet, $widget_code_mostactive, $widget_code_topnation;
	
		//Register MLIDs
		$this->registerMLIds(1);
		
		$this->name = 'podium';
		$this->author = 'matrix142, Cyril, Libero, pastis-51';
		$this->version = '0.8';
		
		//Load Config file
		$this->config = $this->loadConfig();
		
		//Get posns
		$this->posn_topdonate = $this->config->posns->top_donate;		
		$this->posn_topbet = $this->config->posns->top_bet;
		$this->posn_mostactive = $this->config->posns->most_active;
		$this->posn_topnation = $this->config->posns->top_nation;
		
		
		//TOP DONATE
		if($this->config->settings->show_top_donate == 'true') {
		if($this->posn_topdonate != 'false'){
			$this->posn_topdonate = explode(' ', $this->posn_topdonate);
			/*
			CHANGE HERE THE STYLE OF THE TOP DONATE WIDGET
			$posn_topdonate[0] = X-Posn, $posn_topdonate[1] = Y-Posn set in plugin.scorepanel.config.xml
			*/
			$widget_code_topdonate = '
			<quad posn="'.$this->posn_topdonate[0].' '.$this->posn_topdonate[1].' 1" sizen="16 16.333" bgcolor="1919194d" />
		
			
			<label posn="'.($this->posn_topdonate[0]+7).' '.($this->posn_topdonate[1]-0.5).' 3" scale="0.9" sizen="15" halign="center" textfont="Stunts/XBall" text="$fff$i$o$s'.$this->config->settings->names->headline_top_donate.'" />';
		}else {
			$widget_code_topdonate = '';
		}
		}
		// TOP BETS
		if($this->config->settings->show_top_bet == 'true') {
		if($this->posn_topbet != 'false'){
			$this->posn_topbet = explode(' ', $this->posn_topbet);
			/*
			CHANGE HERE THE STYLE OF THE TOP BET WIDGET
			$posn_topbet[0] = X-Posn, $posn_topbet[1] = Y-Posn set in plugin.scorepanel.config.xml
			*/
			$widget_code_topbet = '
			<quad posn="'.$this->posn_topbet[0].' '.$this->posn_topbet[1].' 1" sizen="16 16.333" bgcolor="1919194d" />
			
		
			<label posn="'.($this->posn_topbet[0]+7).' '.($this->posn_topbet[1]-0.5).' 3" scale="0.9" sizen="15" halign="center" textfont="Stunts/XBall" text="$fff$i$o$s'.$this->config->settings->names->headline_top_bet.'" />';
		}else {
			$widget_code_topbet = '';
		}
		
		}
		// TOP NATION
		if($this->config->settings->show_top_nation == 'true')
		{
			if($this->posn_topnation != 'false')
			{
				$this->posn_topnation = explode(' ', $this->posn_topnation);
				/*
				CHANGE HERE THE STYLE OF THE TOP Nation WIDGET
				$posn_topnation[0] = X-Posn, $posn_topnation[1] = Y-Posn set in plugin.scorepanel.config.xml
				*/
				$widget_code_topnation = '
				<quad posn="'.$this->posn_topnation[0].' '.$this->posn_topnation[1].' 1" sizen="16 16.333" bgcolor="1919194d" />		
				<label posn="'.($this->posn_topnation[0]+7).' '.($this->posn_topnation[1]-0.5).' 3" scale="0.9" sizen="15" halign="center" textfont="Stunts/XBall" text="$fff$i$o$s'.$this->config->settings->names->headline_top_nation.'" />
				';
			}
			else
			{
				$widget_code_topnation = '';
			}		
		}
		/// MOST ACTIVE
		if($this->config->settings->show_most_active == 'true') {
		if($this->posn_mostactive != 'false') {
			$this->posn_mostactive = explode(' ', $this->posn_mostactive);
			/*
			CHANGE HERE THE STYLE OF THE MOST ACTIVE WIDGET
			$posn_mostactive[0] = X-Posn, $posn_mostactive[1] = Y-Posn set in plugin.scorepanel.config.xml
			*/
			$widget_code_mostactive = '
			<quad posn="'.$this->posn_mostactive[0].' '.$this->posn_mostactive[1].' 1" sizen="16 16.333" bgcolor="1919194d" />
			
			
			<label posn="'.($this->posn_mostactive[0]+6.8).' '.($this->posn_mostactive[1]-0.5).' 3" scale="0.9" sizen="15" halign="center" textfont="Stunts/XBall" text="$fff$i$o$s'.$this->config->settings->names->headline_most_active.'" />';
		}else {
			$widget_code_mostactive = '';
		}
		}
		
	}	
	public function onEndMap($args) {
		global $widget_code_topdonate, $widget_code_topbet, $widget_code_mostactive, $widget_code_topnation;
		
		
		
		
		//TOP DONATE
			if($this->config->settings->show_top_donate == 'true') {
		if($this->posn_topdonate != 'false') {
			$code_topdonate = '';
			$y = 0;
			
			for($i=0; $i<5; $i++) {
				$sql = mysqli_query($this->db, "SELECT * FROM `players` ORDER BY donations DESC LIMIT ".$i.", 1");
			
				if($row = $sql->fetch_object() AND $row->donations != '0') {
					$code_topdonate .= '
					<label posn="'.($this->posn_topdonate[0]+4).' '.($this->posn_topdonate[1]-3.2-$y).' 5" scale="0.9"  halign="right" textsize="3" sizen="8" textfont="Stunts/XBall"  text="$fff$s'.$row->donations.'"/>
					<label posn="'.($this->posn_topdonate[0]+5).' '.($this->posn_topdonate[1]-3.65-$y).' 5" sizen="11.5" scale="0.9" style="TextValueSmall" text="$bbb'.htmlspecialchars($row->nickname).'"/>';
			
					$y += 2.3;
				}
			}
		}
		}
		//TOP BETS
		if($this->config->settings->show_top_bet == 'true') {
		if($this->posn_topbet != 'false') {
			$code_topbet = '';
			$y = 0;
			
			for($i=0; $i<5; $i++) {
				$sql = mysqli_query($this->db, "SELECT * FROM `betting` ORDER BY wins DESC LIMIT ".$i.", 1");
				
			
				if($row = $sql->fetch_object() AND $row->wins != '0') {
					$code_topbet .= '
					<label posn="'.($this->posn_topbet[0]+4.5).' '.($this->posn_topbet[1]-3.2-$y).' 5" scale="0.9"  halign="right"  sizen="8 8" textfont="Stunts/XBall"  text="$fff$s'.$row->wins.'"/>
					<label posn="'.($this->posn_topbet[0]+5).' '.($this->posn_topbet[1]-3.65-$y).' 5" sizen="11.5 5" scale="0.9" style="TextValueSmall" text="$bbb'.htmlspecialchars($row->nickname).'"/>';
			
					$y += 2.3;
				}
			}
		}
		}		
		//TOP NATION
		if($this->config->settings->show_top_nation == 'true')
		{
			if($this->posn_topnation != 'false')
			{
				$code_topnation = '';
				$y = 0;
				
				for($i=0; $i<5; $i++)
				{					
					$sql = mysqli_query($this->db, "SELECT COUNT(*) AS `Enregistrements`, `country` FROM `players` WHERE country != '' && country != 'Europe' && country != 'North America' && country != 'South America' && country != 'Middle East' && country != 'Africa' && country != 'Asia' && country != 'Oceania' GROUP BY `country` ORDER BY `Enregistrements` DESC LIMIT ".$i.", 30 ");
				
					if($row = $sql->fetch_object() AND $row->Enregistrements != '0')
					{
						$code_topnation .= '
						<label posn="'.($this->posn_topnation[0]+3.5).' '.($this->posn_topnation[1]-3.2-$y).' 5" scale="0.9"  halign="right"  sizen="8 8" textfont="Stunts/XBall"  text="$fff$s'.$row->Enregistrements.'"/>
						<label posn="'.($this->posn_topnation[0]+4).' '.($this->posn_topnation[1]-3.0-$y).' 5" sizen="11.5 5" textfont="Stunts/XBall" text="$fff$s'.$row->country.'"/>';
					
						$y += 2.3;
					}
				}
			}
		}
		//MOST ACTIVE
			if($this->config->settings->show_most_active == 'true') {
		if($this->posn_mostactive != 'false') {
			$code_mostactive = '';
			$y = 0;
			
			for($i=0; $i<5; $i++) {
				$sql = mysqli_query($this->db, "SELECT * FROM `players` ORDER BY timeplayed DESC LIMIT ".$i.", 1");
			
				if($row = $sql->fetch_object()) {
					if($this->instance()->formattime_hour($row->timeplayed) != 0) {
						$time = $this->instance()->formattime_hour($row->timeplayed);
					} else if($this->instance()->formattime_minute($row->timeplayed) != 0) {
						$time = $this->instance()->formattime_minute($row->timeplayed);
					} else {
						$time = 0;
					}
					
					if($time != 0) {
						$code_mostactive .= '
						<label posn="'.($this->posn_mostactive[0]+4.5).' '.($this->posn_mostactive[1]-3.2-$y).' 5" scale="0.9"  halign="right"  sizen="8 8" textfont="Stunts/XBall"  text="$fff$s'.$time.'"/>
						<label posn="'.($this->posn_mostactive[0]+5).' '.($this->posn_mostactive[1]-3.65-$y).' 5" sizen="11.5 5" scale="0.9" style="TextValueSmall" text="$bbb'.htmlspecialchars(stripslashes($row->nickname)).'"/>';
			
						$y += 2.3;
					}
				}
			}
		}
		
		}
		
		
		$widget_code = $widget_code_topdonate.$widget_code_topbet.$widget_code_topnation.$widget_code_mostactive.$code_topdonate.$code_topbet.$code_topnation.$code_mostactive;
		$this->displayManialink($widget_code, $this->mlids[0]);
	}
	public function onBeginMap($args) {	
		$this->closeMl($this->mlids[0]);
	}
}
?>
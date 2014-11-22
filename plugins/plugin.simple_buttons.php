<?php
//* plugin.simple_buttons.php - Adds buttons very easy
//* Version:   0.5
//* Coded by:  cyrilw, matrix142
//* Copyright: FoxRace, http://www.fox-control.de

class plugin_simple_buttons extends FoxControlPlugin {
	public $config;

	public function onStartUp () {
		global $sb_buttons;
	
		$this->name = 'Simple Butons';
		$this->author = 'matrix142 & cyril';
		$this->version = '0.5';
		
		$this->registerMLIds(1);
		
		$this->config = $this->loadConfig();
		
		if($this->config != false) {
			$simplebuttons = array();	
			$sb_curr_id = 0;
			
			while(isset($this->config->size[$sb_curr_id])){			
				$sb_size = $this->config->size[$sb_curr_id];
				$sb_pos = $this->config->pos[$sb_curr_id];
				$sb_image = $this->config->image[$sb_curr_id];
				$sb_imagefocus = $this->config->imagefocus[$sb_curr_id];
				$sb_linkstyle = $this->config->linkstyle[$sb_curr_id];
				$sb_link = $this->config->link[$sb_curr_id];
				$sb_link = str_replace('&', '&amp;', $sb_link);
				
				if($sb_linkstyle == '') {
					$sb_linkstyle = 'manialink';
				}
				
				if($sb_imagefocus != '') {
					$sb_imagefocus = $sb_imagefocus;
				} else {
					$sb_imagefocus = '';
				}
				
				$simplebuttons[] = '<quad posn="'.$sb_pos.' 10" sizen="'.$sb_size.'" image="'.$sb_image.'" imagefocus="'.$sb_imagefocus.'" '.$sb_linkstyle.'="'.$sb_link.'"/>';
				
				$sb_curr_id++;
			}
			
			$sb_curr_id = 0;
			$sb_buttons = '';
			
			while(isset($simplebuttons[$sb_curr_id])){
				$sb_buttons = $sb_buttons.$simplebuttons[$sb_curr_id];
				$sb_curr_id++;
			}
			
			//DISPLAYING THE BUTTONS
			$this->displayButtons();
		} else {
			console('Can\'t open \'plugin.simple_buttons.config.xml\'!!');
		}
	}
	
	public function onPlayerConnect($connectedplayer) {
		$this->displayButtons($connectedplayer['Login']);
	}
	
	public function onBeginMap($challdata) {
		$this->displayButtons();
	}

	public function displayButtons($login = false) {
		global $sb_buttons;
	
		if($login != false){
			$this->displayManialinkToLogin($login, $sb_buttons, $this->mlids[0]);
		}else{
			$this->displayManialink($sb_buttons, $this->mlids[0]);
		}
	}
	
	public function onEndMap($challdata) {
		$this->closeMl($this->mlids[0]);
	}
}
?>
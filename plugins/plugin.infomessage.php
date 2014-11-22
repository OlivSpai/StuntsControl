<?php
//* plugin.infomessage.php - Chat Info Message
//* Version:   0.6
//* Coded by:  matrix142
//* Copyright: FoxRace, http://www.fox-control.de

class plugin_infomessage extends FoxControlPlugin {
	public $config;
	public $duration = 0;
	public $id = 0;

	public function onStartUp() {
		$this->config = $this->loadConfig();
	}
	
	public function onEndMap($args) {		
		if($this->duration >= $this->config->general->duration) {
			$this->duration = 0;
		
			if(!isset($this->config->messages->message[$this->id])) {
				$this->id = 0;
			}
			
			$this->chat('$s$o$06fINFO: $o$fff'.$this->config->messages->message[$this->id].'$s');
			
			$this->id++;
		}
		
		$this->duration++;
	}
}
?>
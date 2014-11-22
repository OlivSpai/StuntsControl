<?php
//* class.widget.php - Widget Class
//* Version:   1.0
//* Coded by:  matrix142
//* Copyright: FoxRace, http://www.fox-control.de
class widget extends FoxControlPlugin {
	public $fc_widget = array();
	public $userSettings = array();
	public $widgets = array();
	public $playerList;
	
	/*
		StartUp
	*/
	public function onInit() {	
		$this->registerMLIds(1);
	
		$this->instance()->client->query('GetPlayerList', 200, 0);
		$this->playerList = $this->instance()->client->getResponse();
		
		foreach($this->playerList as $key => $value) {
			$this->userSettings[$this->playerList[$key]['Login']] = array();
		}
	}
	
	/*
		Player Connect
	*/
	public function onPlayerConnect($args) {
		global $fc_widgetids;
	
		$this->userSettings[$args['Login']] = array();
		$true = false;
			
		$sql = "SELECT * FROM `widget_settings` WHERE playerlogin = '".$args['Login']."'";
		$mysql = mysqli_query($this->db, $sql);
		
		while($row = $mysql->fetch_object()) {
			$true = true;
		
			$widgetID = (int) $row->widgetid;
			
			if(isset($this->widgets[$widgetID])) {
				$this->userSettings[$args['Login']][$widgetID] = $this->widgets[$widgetID];
			
				$this->userSettings[$args['Login']][$widgetID]['style1'] = $row->style1;
				$this->userSettings[$args['Login']][$widgetID]['substyle1'] = $row->substyle1;
				$this->userSettings[$args['Login']][$widgetID]['style2'] = $row->style2;
				$this->userSettings[$args['Login']][$widgetID]['substyle2'] = $row->substyle2;
				$this->userSettings[$args['Login']][$widgetID]['PosX'] = $row->posx;
				$this->userSettings[$args['Login']][$widgetID]['PosY'] = $row->posy;
			}
		}
		
		if($true == false) {
			for($i = 1; $i <= $fc_widgetids; $i++) {
				$widgetID = $i;
			
				mysqli_query($this->db, "INSERT INTO `widget_settings` VALUES('".$args['Login']."', '".$widgetID."', '".$this->widgets[$widgetID]['style1']."', '".$this->widgets[$widgetID]['substyle1']."', '".$this->widgets[$widgetID]['style2']."', '".$this->widgets[$widgetID]['substyle2']."', '".$this->widgets[$widgetID]['PosX']."', '".$this->widgets[$widgetID]['PosY']."', '".$this->widgets[$widgetID]['PosX']."', '".$this->widgets[$widgetID]['PosY']."')"); 
			
				$this->userSettings[$args['Login']][$widgetID] = $this->widgets[$widgetID];
			}
		}
		
		$this->displayManialinkToLogin($args['Login'], '<quad posn="70 70 0" sizen="0 0" action="widget:disable" actionkey="3" />', $this->mlids[0]);
	}
	
	/*
		Player Disconnect
	*/
	public function onPlayerDisconnect($args) {
		unset($this->userSettings[$args[0]]);
	}
	
	/*
		Initialising
	*/
	public function init() {
		global $settings;
	
		$this->fc_widget['Content'] = array();
		$this->fc_widget['TextAlign'] = 'left';
		$this->fc_widget['Close'] = true;
		$this->fc_widget['Configurable'] = true;
		$this->fc_widget['Title'] = 'Widget';
		$this->fc_widget['SizeX'] = '';
		$this->fc_widget['SizeY'] = '';
		$this->fc_widget['FontSize'] = '2';
		$this->fc_widget['PosX'] = '';
		$this->fc_widget['PosY'] = '';
		$this->fc_widget['Target'] = array();
		$this->fc_widget['Buttons'] = array();
		$this->fc_widget['UseButtons'] = false;
		$this->fc_widget['Code'] = '';
		$this->fc_widget['UseCode'] = false;
		$this->fc_widget['LoginCode'] = '';
		$this->fc_widget['UseLoginCode'] = '';
		$this->fc_widget['DynamicHeight'] = true;
		$this->fc_widget['Table'] = true;
		$this->fc_widget['Enabled'] = true;
		$this->fc_widget['Moveable'] = false;
		$this->fc_widget['Positions'] = array();
		$this->fc_widget['Icon'] = array();
		$this->fc_widget['UseIcon'] = false;
		$this->fc_widget['style1'] = $settings['default_style1'];
		$this->fc_widget['substyle1'] = $settings['default_substyle1'];
		$this->fc_widget['style2'] = $settings['default_style2'];
		$this->fc_widget['substyle2'] = $settings['default_substyle2'];
		$this->fc_widget['GlobalEnabled'] = true;
	}
	
	/*
		Title
	*/
	public function title($title) {
		$this->fc_widget['Title'] = $title;
	}
	
	/*
		Close
	*/
	public function close($close) {
		$this->fc_widget['Close'] = $close;
	}
	
	/*
		Configurable
	*/
	public function configurable($configurable) {
		$this->fc_widget['Configurable'] = $configurable;
	}
	
	/*
		Text Align
	*/
	public function textAlign($textAlign) {
		$this->fc_widget['TextAlign'] = $textAlign;
	}
	
	/*
		Position
	*/
	public function posn($posX, $posY) {
		$this->fc_widget['PosX'] = $posX;
		$this->fc_widget['PosY'] = $posY;
	}
	
	/*
		Size
	*/
	public function size($sizeX, $sizeY) {
		if($sizeX !== '') $this->fc_widget['SizeX'] = $sizeX;
		if($sizeY !== '') $this->fc_widget['SizeY'] = $sizeY;
		if($sizeY !== '') $this->fc_widget['DynamicHeight'] = false;
	}
	
	/*
		Font Size
	*/
	public function fontSize($fontSize) {
		$this->fc_widget['FontSize'] = $fontSize;
	}
	
	/*
		Add Button
	*/
	public function addButton($name = false, $icon = false, $size, $actionID) {
		$this->fc_widget['Buttons'][] = array('name' => $name, 'icon' => array('style' => $icon['style'], 'substyle' => $icon['substyle']), 'size' => $size, 'actionid' => $actionID);
		$this->fc_widget['UseButtons'] = true;
	}
	
	/*
		Code
	*/
	public function addCode($code) {
		$this->fc_widget['Code'] = $code;
		$this->fc_widget['UseCode'] = true;
	}
	
	/*
		Code to login
	*/
	public function addCodeToLogin($code, $login, $widgetID) {
		$this->userSettings[$login][$widgetID]['LoginCode'] = $code;
		
		if(!empty($code)) {
			$this->userSettings[$login][$widgetID]['UseLoginCode'] = true;
		} else {
			$this->userSettings[$login][$widgetID]['UseLoginCode'] = false;
		}
	}
	
	/*
		Alternative Position
	*/
	public function alternativePosition($name, $posX, $posY) {
		$this->fc_widget['Moveable'] = true;
		$this->fc_widget['Positions'][] = array('name' => $name, 'PosX' => $posX, 'PosY' => $posY);
	}
	
	/*
		Icon
	*/
	public function icon($iconStyle, $iconSubStyle) {
		$this->fc_widget['Icon']['style'] = $iconStyle;
		$this->fc_widget['Icon']['substyle'] = $iconSubStyle;
		$this->fc_widget['UseIcon'] = true;
	}
	
	/*
		Save Widget
	*/
	public function saveWidget($widgetID, $mlid) {	
		$this->fc_widget['MLID'] = $mlid;
		
		$this->widgets[$widgetID] = '';
		$this->widgets[$widgetID] = $this->fc_widget;
		
		$this->instance()->client->query('GetPlayerList', 200, 0);
		$playerList = $this->instance()->client->getResponse();
		
		foreach($playerList as $key => $value) {
			$this->userSettings[$value['Login']][$widgetID] = '';
			$this->userSettings[$value['Login']][$widgetID] = $this->widgets[$widgetID];
			
			$sql = "SELECT * FROM `widget_settings` WHERE widgetid = '".$widgetID."' AND playerlogin = '".$value['Login']."'";
			$mysql = mysqli_query($this->db, $sql);
			
			if($row = $mysql->fetch_object()) {
				$this->userSettings[$value['Login']][$widgetID]['style1'] = $row->style1;
				$this->userSettings[$value['Login']][$widgetID]['substyle1'] = $row->substyle1;
				$this->userSettings[$value['Login']][$widgetID]['style2'] = $row->style2;
				$this->userSettings[$value['Login']][$widgetID]['substyle2'] = $row->substyle2;
				$this->userSettings[$value['Login']][$widgetID]['PosX'] = $row->posx;
				$this->userSettings[$value['Login']][$widgetID]['PosY'] = $row->posy;
				$this->userSettings[$value['Login']][$widgetID]['Positions'][] = array('name' => 'Default', 'PosX' => $row->defaultPosX, 'PosY' => $row->defaultPosY);
			} else {
				mysqli_query($this->db, "INSERT INTO `widget_settings` VALUES('".$value['Login']."', '".$widgetID."', '".$this->userSettings[$value['Login']][$widgetID]['style1']."', '".$this->userSettings[$value['Login']][$widgetID]['substyle1']."', '".$this->userSettings[$value['Login']][$widgetID]['style2']."', '".$this->userSettings[$value['Login']][$widgetID]['substyle2']."', '".$this->userSettings[$value['Login']][$widgetID]['PosX']."', '".$this->userSettings[$value['Login']][$widgetID]['PosY']."', '".$this->widgets[$widgetID]['PosX']."', '".$this->widgets[$widgetID]['PosY']."')"); 
			}
		}
		
		$this->displayManialink('<quad posn="70 70 0" sizen="0 0" action="widget:disable" actionkey="3" />', $this->mlids[0]);
	}
	
	/*
		Content
	*/
	public function addContent($content, $login, $widgetID) {	
		$this->userSettings[$login][$widgetID]['Content'][] = $content;
	}
	
	/*
		Clear Content
	*/
	public function clearContent($login, $widgetID) {		
		$this->userSettings[$login][$widgetID]['Content'] = array();
	}
	
	
	/*
		Create Code
	*/
	public function displayWidget($login, $mlid, $widgetID, $removed = false) {
		global $settings;
		
		if(!isset($this->userSettings[$login][$widgetID]['style1'])) {
			$this->userSettings[$login][$widgetID] = $this->widgets[$widgetID];
		}
		
		$widgetSettings = $this->userSettings[$login][$widgetID];
		$this->widgets[$widgetID]['Removed'] = $removed;

		if($widgetSettings['Enabled'] == true && $widgetSettings['GlobalEnabled'] == true && $this->widgets[$widgetID]['Removed'] != true) {		
			$sql = mysqli_query($this->db, "SELECT * FROM `widget_settings` WHERE playerlogin = '".$login."' AND widgetid = '".$widgetID."'");
			if($row = $sql->fetch_object()) {
				if($this->widgets[$widgetID]['PosX'] != $row->defaultPosX || $this->widgets[$widgetID]['PosY'] != $row->defaultPosY) {
					mysqli_query($this->db, "UPDATE `widget_settings` SET posx = '".$this->widgets[$widgetID]['PosX']."', posy = '".$this->widgets[$widgetID]['PosY']."', defaultPosX = '".$this->widgets[$widgetID]['PosX']."', defaultPosY = '".$this->widgets[$widgetID]['PosY']."' WHERE widgetid = '".$widgetID."' AND playerlogin = '".$login."'");
				
					$widgetSettings['PosX'] = $this->widgets[$widgetID]['PosX'];
					$widgetSettings['PosY'] = $this->widgets[$widgetID]['PosY'];
					$this->userSettings[$login][$widgetID]['PosX'] = $this->widgets[$widgetID]['PosX'];
					$this->userSettings[$login][$widgetID]['PosY'] = $this->widgets[$widgetID]['PosY'];
					$this->userSettings[$login][$widgetID]['Positions'][] = array('name' => 'Default', 'PosX' => $this->widgets[$widgetID]['PosX'], 'PosY' => $this->widgets[$widgetID]['PosY']);
				}
			}
			
			$posX = $widgetSettings['PosX'];
			$posY = $widgetSettings['PosY'];
			$sizeX = $widgetSettings['SizeX'];
			$sizeY = $widgetSettings['SizeY'];
		
			$headlineX = $posX;
			$headlineY = $posY - 0.5;
			$headlineBgHeight = 3;
			$headlineBgWidth = $sizeX;
		
			$contentX = $posX + 0.2;
			$contentY = $posY - 3 - 0.5;
			$contentWidth = $sizeX - 0.2;
			$contentHeight = $sizeY - 3 - 0.5;
		
			$mlCode = '';
			
			if($widgetSettings['Table'] == true) {
				$mlCode .= $this->createTable($widgetSettings);
			}
		
			$ml_display_code = '
			<quad posn="'.$posX.' '.$posY.' 1" sizen="'.$widgetSettings['SizeX'].' '.$widgetSettings['SizeY'].'" halign="center" style="'.$widgetSettings['style2'].'" substyle="'.$widgetSettings['substyle2'].'"/>
			<quad posn="'.$posX.' '.($posY - 0.2).' 2" sizen="'.$headlineBgWidth.' '.$headlineBgHeight.'" halign="center" style="'.$widgetSettings['style1'].'" substyle="'.$widgetSettings['substyle1'].'"/>
			<quad posn="'.$posX.' '.($posY - 0.2).' 3" sizen="'.$headlineBgWidth.' '.$headlineBgHeight.'" halign="center" style="'.$widgetSettings['style1'].'" substyle="'.$widgetSettings['substyle1'].'"/>
			<label posn="'.$headlineX.' '.($headlineY - 0.4).' 4" halign="center" scale="0.6" text="$o$FFF'.$widgetSettings['Title'].'"/>';
			
			if($widgetSettings['Close'] == true) {
				$ml_display_code .= '<quad posn="'.($posX + $sizeX / 2 - 2.2).' '.($posY - 0.4).' 5" sizen="2.5 2.5" style="Icons64x64_1" substyle="QuitRace" action="widget:close:'.$widgetID.'"/>';
			}
			
			if($widgetSettings['Configurable'] == true) {
				$ml_display_code .= '<quad posn="'.($posX + $sizeX / 2 - 2.9).' '.($posY - 0.9).' 5" sizen="1.5 1.5" style="Icons128x32_1" substyle="Settings" action="widget:edit:'.$widgetID.'"/>';
			}
			
			if($widgetSettings['UseIcon'] == true) {
				$ml_display_code .= '<quad posn="'.($posX - $sizeX / 2).' '.($posY - 0.6).' 4" sizen="2.3 2.3" style="'.$widgetSettings['Icon']['style'].'" substyle="'.$widgetSettings['Icon']['substyle'].'" />';
			}
			
			if($widgetSettings['UseCode'] == true) {
				$replace1 = array('{style1}', '{substyle1}', '{style2}', '{substyle2}');
				$replace2 = array($widgetSettings['style1'], $widgetSettings['substyle1'], $widgetSettings['style2'], $widgetSettings['substyle2']);
				$widgetSettings['Code'] = str_replace($replace1, $replace2, $widgetSettings['Code']);
			
				$ml_display_code .= '
				<frame posn="'.$posX.' '.$posY.' 2">'.$widgetSettings['Code'].'</frame>';
			}
			
			if($widgetSettings['UseLoginCode'] == true) {
				if($widgetSettings['LoginCode'] != false) {
					$replace1 = array('{style1}', '{substyle1}', '{style2}', '{substyle2}');
					$replace2 = array($widgetSettings['style1'], $widgetSettings['substyle1'], $widgetSettings['style2'], $widgetSettings['substyle2']);
					$widgetSettings['LoginCode'] = str_replace($replace1, $replace2, $widgetSettings['LoginCode']);
					
					$ml_display_code .= '
					<frame posn="'.$posX.' '.$posY.' 2">'.$widgetSettings['LoginCode'].'</frame>';
				}
			}
				
			$ml_display_code .= $mlCode;
		
			$this->displayManialinkToLogin($login, $ml_display_code, $mlid);
		} else {
			if($this->widgets[$widgetID]['Removed'] != true) {
				$this->closeWidget($login, $widgetID, $mlid);
			}
		}
	}
	
	/*
		Update Settings
	*/
	public function updateSettings($login, $widgetID) {
		mysqli_query($this->db, "UPDATE `widget_settings` SET style1 = '".$this->userSettings[$login][$widgetID]['style1']."', substyle1 = '".$this->userSettings[$login][$widgetID]['substyle1']."', style2 = '".$this->userSettings[$login][$widgetID]['style2']."', substyle2 = '".$this->userSettings[$login][$widgetID]['substyle2']."', posx = '".$this->userSettings[$login][$widgetID]['PosX']."', posy = '".$this->userSettings[$login][$widgetID]['PosY']."' WHERE playerlogin = '".$login."' AND widgetid = '".$widgetID."'");
	}
	
	/*
		Manialink Page Answer
	*/
	public function onManialinkPageAnswer($args) {
		if(preg_match('/widget:/', $args[2])) {
			//Global Disable
			if(preg_match('/disable/', $args[2])) {
				global $fc_widgetids;
			
				for($i = 1; $i <= $fc_widgetids; $i++) {
					if($this->userSettings[$args[1]][$i]['GlobalEnabled'] === true) {
						$this->userSettings[$args[1]][$i]['GlobalEnabled'] = false;
						
						$enabled = false;
					} else {
						$this->userSettings[$args[1]][$i]['GlobalEnabled'] = true;
					
						$enabled = true;
					}
				
					if($this->widgets[$i]['Removed'] == true) {
						$this->displayWidget($args[1], $this->userSettings[$args[1]][$i]['MLID'], $i, true);
					} else {
						$this->displayWidget($args[1], $this->userSettings[$args[1]][$i]['MLID'], $i, false);
					}
				}
			
				if($enabled == false) {
					$this->chatToLogin($args[1], '$s$iYour widgets are now hidden$i$s');
				} else {
					$this->chatToLogin($args[1], '$s$iYour widgets are now enabled$i$s');
				}
			}		
			//Close Widget
			else if(preg_match('/close:/', $args[2])) {
				$explode = explode(':', $args[2]);
				
				$this->userSettings[$args[1]][$explode[2]]['Enabled'] = false;
				
				$this->closeWidget($args[1], $explode[2], $this->userSettings[$args[1]][$explode[2]]['MLID']);
			} 
			//Open Widget
			else if(preg_match('/open:/', $args[2])) {
				$explode = explode(':', $args[2]);
				
				$this->userSettings[$args[1]][$explode[2]]['Enabled'] = true;
				
				$this->displayWidget($args[1], $this->userSettings[$args[1]][$explode[2]]['MLID'], $explode[2]);
			}
			//Edit Widget
			else if(preg_match('/edit:/', $args[2])) {
				$explode = explode(':', $args[2]);
				
				$this->editWidget($args[1], $explode[2]);
			}
			//New Position
			else if(preg_match('/newpos:/', $args[2])) {
				$explode = explode(':', $args[2]);
				
				$this->userSettings[$args[1]][$explode[6]]['PosX'] = $explode[3];
				$this->userSettings[$args[1]][$explode[6]]['PosY'] = $explode[5];
				
				$this->updateSettings($args[1], $explode[6]);
				
				$this->displayWidget($args[1], $this->userSettings[$args[1]][$explode[6]]['MLID'], $explode[6]);
			}
			//New Style
			else if(preg_match('/newstyle:/', $args[2])) {
				global $_widgetStyles;
			
				$explode = explode(':', $args[2]);
				
				$this->userSettings[$args[1]][$explode[3]]['style1'] = $_widgetStyles[$explode[2]]['style1'];
				$this->userSettings[$args[1]][$explode[3]]['style2'] = $_widgetStyles[$explode[2]]['style2'];
				$this->userSettings[$args[1]][$explode[3]]['substyle1'] = $_widgetStyles[$explode[2]]['substyle1'];
				$this->userSettings[$args[1]][$explode[3]]['substyle2'] = $_widgetStyles[$explode[2]]['substyle2'];
				
				$this->updateSettings($args[1], $explode[3]);
				
				$this->displayWidget($args[1], $this->userSettings[$args[1]][$explode[3]]['MLID'], $explode[3]);
			}
		}
	}
	
	/*
		Close Widget
	*/
	public function closeWidget($login, $widgetID, $mlid) {
		$posX = $this->userSettings[$login][$widgetID]['PosX'];
		$posY = $this->userSettings[$login][$widgetID]['PosY'];
		$sizeX = $this->userSettings[$login][$widgetID]['SizeX'];
	
		if($posX < 0) {
			$code = '
			<quad posn="'.($posX - $sizeX / 2).' '.$posY.' 2" sizen="2.5 2.5" style="Icons64x64_1" substyle="Add" action="widget:open:'.$widgetID.'" />';
		} else {
			$code = '
			<quad posn="'.($posX + $sizeX / 2 - 3).' '.$posY.' 2" sizen="2.5 2.5" style="Icons64x64_1" substyle="Add" action="widget:open:'.$widgetID.'" />';
		}

		$this->displayManialinkToLogin($login, $code, $mlid);
	}
	
	/*
		Edit Widget
	*/
	public function editWidget($login, $widgetID) {
		global $window, $_widgetStyles;

		$window->init();
		
		$window->title('Settings - '.$this->widgets[$widgetID]['Title']);
		
		$window->displayAsTable(true);
		$window->size(50, '');
		$window->posY('40');
		
		$window->addButton('', '15.5', false);
		$window->addButton('Close', '10', true);
		$window->addButton('', '15.5', false);
		
		$window->content('<td width="12">$iPositions</td><td width="3"></td><td width="15">$iStyle</td>');
		
		if($this->userSettings[$login][$widgetID]['Moveable'] == true) {
			$i = 0;
			
			foreach($this->userSettings[$login][$widgetID]['Positions'] as $key => $value) {
				if(isset($_widgetStyles[$i])) {
					$window->content('<td width="12" id="widget:newpos:x:'.$value['PosX'].':y:'.$value['PosY'].':'.$widgetID.'">'.$value['name'].'</td><td width="3"></td><td width="15" id="widget:newstyle:'.$i.':'.$widgetID.'">'.$_widgetStyles[$i]['name'].'</td>');
				} else {
					$window->content('<td width="12" id="widget:newpos:x:'.$value['PosX'].':y:'.$value['PosY'].':'.$widgetID.'">'.$value['name'].'</td>');
				}
				
				$i++;
			}
			
			if(isset($_widgetStyles[$i])) {
				while(isset($_widgetStyles[$i])) {
					$window->content('<td width="12"></td><td width="3"></td><td width="15" id="widget:newstyle:'.$i.':'.$widgetID.'">'.$_widgetStyles[$i]['name'].'</td>');
					
					$i++;
				}
			}
		}
		
		$window->show($login);
	}
	
	/*
		Remove Widget
	*/
	public function remove($widgetID) {
		$this->closeMl($this->widgets[$widgetID]['MLID']);
		$this->widgets[$widgetID]['Removed'] = true;
	}
	
	/*
		Create Table
	*/
	public function createTable($widgetSettings) {
		$table = '';
		
		$posy = $widgetSettings['PosY'] - 4;
		$sizey = 0;
		
		//Calculating every line
		for($i = 0; isset($widgetSettings['Content'][$i]); $i++) {
			$td = explode('<td', $widgetSettings['Content'][$i]);
			$posx = $widgetSettings['PosX'] - $widgetSettings['SizeX'] / 2;
			
			for($tdi = 0; isset($td[$tdi]); $tdi++) {
				$width = '';
				$c = 0;
				$width_began = false;
				$is_link = false;
				$align_center = false;
				$link = '';
				$content = '';
				$ml = '';
				
				if(strpos($td[$tdi], 'width') !== false) {
					$widthStart = strpos($td[$tdi], 'width') + 7;
					
					for($ci = $widthStart; true; $ci++) {
						if(substr($td[$tdi], $ci, 1) == '\'') break;
						elseif(substr($td[$tdi], $ci, 1) == '"') break;
						elseif(substr($td[$tdi], $ci, 1) == ' ') break;
						else $width .= substr($td[$tdi], $ci, 1);
					}
				}
				
				if(strpos($td[$tdi], 'id=') !== false) {
					$idStartPos = strpos($td[$tdi], 'id=') + 4;
					
					for($ci = $idStartPos; true; $ci++) {
						if(substr($td[$tdi], $ci, 1) == '\'') break;
						elseif(substr($td[$tdi], $ci, 1) == '"') break;
						elseif(substr($td[$tdi], $ci, 1) == ' ') break;
						else $link .= substr($td[$tdi], $ci, 1);
					}
				}
				
				if(strpos($td[$tdi], 'ml=') !== false) {
					$mlStartPos = strpos($td[$tdi], 'ml=') + 4;
					
					for($ci = $mlStartPos; true; $ci++) {
						if(substr($td[$tdi], $ci, 1) == '\'') break;
						elseif(substr($td[$tdi], $ci, 1) == '"') break;
						elseif(substr($td[$tdi], $ci, 1) == ' ') break;
						else $ml .= substr($td[$tdi], $ci, 1);
					}
				}
				
				if(strpos($td[$tdi], 'align="center"') !== false || strpos($td[$tdi], 'align=\'center\'') !== false) $align_center = true;
				
				$text = $td[$tdi];
				$text = str_replace(' width="'.$width.'">', '', $text);
				$text = str_replace('width="'.$width.'">', '', $text);
				$text = str_replace(' width="'.$width.'"/>', '', $text);
				$text = str_replace(' width="'.$width.'" ', '', $text);
				$text = str_replace(' width="'.$width.'"', '', $text);
				$text = str_replace(' width=\''.$width.'\'>', '', $text);
				$text = str_replace(' width=\''.$width.'\'/>', '', $text);
				$text = str_replace(' width=\''.$width.'\' ', '', $text);
				$text = str_replace(' width=\''.$width.'\'', '', $text);
				$text = str_replace(' align="center">', '', $text);
				$text = str_replace('align="center">', '', $text);
				$text = str_replace(' align="center"/>', '', $text);
				$text = str_replace(' align="center" ', '', $text);
				$text = str_replace(' align="center"', '', $text);
				$text = str_replace(' align=\'center\'>', '', $text);
				$text = str_replace(' align=\'center\'/>', '', $text);
				$text = str_replace(' align=\'center\' ', '', $text);
				$text = str_replace(' align=\'center\'', '', $text);
				$text = str_replace('id="'.$link.'">', '', $text);
				$text = str_replace('id="'.$link.'"/>', '', $text);
				$text = str_replace('id="'.$link.'" ', '', $text);
				$text = str_replace('id="'.$link.'"', '', $text);
				$text = str_replace('id=\''.$link.'\'>', '', $text);
				$text = str_replace('id=\''.$link.'\'/>', '', $text);
				$text = str_replace('id=\''.$link.'\' ', '', $text);
				$text = str_replace('id=\''.$link.'\'', '', $text);
				$text = str_replace('ml="'.$ml.'">', '', $text);
				$text = str_replace('ml="'.$ml.'"/>', '', $text);
				$text = str_replace('ml="'.$ml.'" ', '', $text);
				$text = str_replace('ml="'.$ml.'"', '', $text);
				$text = str_replace('ml=\''.$ml.'\'>', '', $text);
				$text = str_replace('ml=\''.$ml.'\'/>', '', $text);
				$text = str_replace('ml=\''.$ml.'\' ', '', $text);
				$text = str_replace('ml=\''.$ml.'\'', '', $text);
				$text = str_replace('</td>', '', $text);
				$text = str_replace('>', '', $text);
				
				if($align_center == true) $table .= '<label posn="'.($posx+(($width)/2)).' '.$posy.' 4" sizen="'.($width - 0.5).' 2" textsize="'.$widgetSettings['FontSize'].'" halign="center" text="$fff'.$text.'"/>';
				else $table .= '<label posn="'.($posx + 0.2).' '.$posy.' 4" sizen="'.($width - 0.5).' 2" textsize="'.$widgetSettings['FontSize'].'" text="$fff'.$text.'"/>';
				
				if(trim($link) !== '') $table .= '<quad posn="'.$posx.' '.($posy + 0.3).' 4" sizen="'.($width + 0.2).' 2" style="'.$widgetSettings['style2'].'" substyle="'.$widgetSettings['substyle2'].'" action="'.$link.'"/>';
				if(trim($ml) !== '') $table .= '<quad posn="'.$posx .' '.($posy + 0.3).' 4" sizen="'.($width + 0.2).' 2" style="'.$widgetSettings['style2'].'" substyle="'.$widgetSettings['substyle2'].'" manialink="'.$ml.'"/>';
				
				$posx = $posx + $width;
			}
			$posy = $posy - ($widgetSettings['FontSize'] + 1);
			$sizey = $sizey + 2.5;
		}
		return $table;
	}
	
	public function getWidgetList() {
		return $this->widgets;
	}
}
?>
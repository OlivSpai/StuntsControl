<?php
/* StuntsControl Manialink Class */

global $sc_ml;
$sc_ml = array();

class manialink extends FoxControlPlugin
{
	public function onInit() {
		$this->registerMLIds(50);
	}
	public function init() //init function of the window. call this init function when you create a new window
	{
		global $sc_ml;
		$sc_ml['TableHeaders'] 	= array();
		$sc_ml['DataLines'] 	= array();
		$sc_ml['TableColumnSize'] 	= array();
		$sc_ml['Content'] 		= array();
		$sc_ml['TextAlign'] 	= 'left';
		$sc_ml['Close'] = true;
		$sc_ml['Title'] = 'Fox Control';
		$sc_ml['SizeX'] = '40';
		$sc_ml['SizeY'] = '20';
		$sc_ml['FontSize'] = '2';
		$sc_ml['PosY'] = '30';
		$sc_ml['Buttons'] = array();
		$sc_ml['ButtonsAutoWidth'] = false;
		$sc_ml['UseButtons'] = false;
		$sc_ml['Target'] = array();
		$sc_ml['UseCode'] = false;
		$sc_ml['Code'] = '';
		$sc_ml['DynamicHeight'] = true;
		$sc_ml['Table'] = false;
		$sc_ml['TableLink'] = array('Style' => 'BgsPlayerCard', 'SubStyle' => 'BgCard');
	}
	
	/* Set table headers in form ["header name"=>Size, ...] */
	public function setTableHeaders($headers = array())
	{
		global $sc_ml;
		
		$count = 0;
		foreach($headers as $name=>$size)
		{
			$sc_ml['TableHeaders'][$count]['Name'] 		= $name;
			$sc_ml['TableHeaders'][$count]['Size'] 		= $size;
			$sc_ml['TableHeaders'][$count]['HAlign'] 	= 'left';
			$sc_ml['TableHeaders'][$count]['VAlign'] 	= 'top';
			$sc_ml['TableColumnSize'][$count] 			= $size;
			$count++;
		}
		//print_r($sc_ml['TableHeaders']);
	}
	
	/* Set line in form ["linecontent1", "linecontent2", ...] */
	public function AddLine($line = array())
	{
		global $sc_ml;
		
		$lineId = count($sc_ml['DataLines']);
		$column = 0;
		foreach($line as $id=>$content)
		{
			$sc_ml['DataLines'][$lineId][$column]['Content'] 	= $content;
			$sc_ml['DataLines'][$lineId][$column]['HAlign'] 	= 'left';
			$sc_ml['DataLines'][$lineId][$column]['VAlign'] 	= 'top';
			$column++;
		}
		print_r($sc_ml['DataLines']);
	}
	
	public function title($title) //set the title of the window
	{
		global $sc_ml;
		$sc_ml['Title'] = $title;
	}
	public function close($close) //set the close box (true or false)
	{
		global $sc_ml;
		$sc_ml['Close'] = $close;
	}

	
	public function show($player)
	{
		global $sc_ml, $settings;
		
		$ml = '
		<manialink id="'.$this->mlids[0].'" version="2">
			<timeout>0</timeout>
			'.$this->getManiaLink().'
		</manialink>
		';
		
		$this->instance()->client->query('SendDisplayManialinkPageToLogin', $player, $ml, 0, false);
	}
	
	public function onManialinkPageAnswer($args)
	{
		if($args[2] == $this->mlids[0]) $this->closeWindow($args[1]);
	}
	
	public function closeWindow($player)
	{
		//Close Manialink when a player clicked on the closebox or a plugin called it
		if(trim($player)!=='') $this->closeMl($this->mlids[0], $player);
		else $this->closeMl($this->mlids[0]);
	}
	
	private function GetTable()
	{
		global $sc_ml;
		
		$ml = '';		
		$posY = 0;
		
		// Lines
		foreach($sc_ml['DataLines'] as $lineId=>$line)
		{
			$posX = 0;
			
			// Columns
			foreach($line as $columnId=>$cell)
			{
				$ml .= '<label text="'.$cell['Content'].'" posn="'.$posX.' '.$posY.'" />';
				$posX += $sc_ml['TableColumnSize'][$columnId];
			}
			$posY -= 5;
		}
		
		return $ml;
	}
	
	private function GetCommonContent()
	{
		global $sc_ml, $showHelpers;
		
		$showHelpers 	= true;
		$mlUrl 			= 'stunters';		
		$ml 			= '';
		
		if ($showHelpers)
		{
			/* Helpers hozizontaux */
			$ml .= '
			<quad posn="0 85 5" sizen="320 0.2" valign="center" halign="center" bgcolor="f51" />
			<label posn="157 85 6" text="85" valign="center2" halign="center" scale="0.75" />
			
			<quad posn="0 80 5" sizen="320 0.2" valign="center" halign="center" bgcolor="f51" />
			<label posn="157 80 6" text="80" valign="center2" halign="center" scale="0.75" />
			
			<quad posn="0 75 5" sizen="320 0.2" valign="center" halign="center" bgcolor="f51" />
			<label posn="157 75 6" text="75" valign="center2" halign="center" scale="0.75" />
			
			<quad posn="0 70 5" sizen="320 0.2" valign="center" halign="center" bgcolor="f51" />
			<label posn="157 70 6" text="70" valign="center2" halign="center" scale="0.75" />
			
			<quad posn="0 65 5" sizen="320 0.2" valign="center" halign="center" bgcolor="f51" />
			<label posn="157 65 6" text="65" valign="center2" halign="center" scale="0.75" />
			
			<quad posn="0 55 5" sizen="320 0.2" valign="center" halign="center" bgcolor="f51" />
			<label posn="157 55 6" text="55" valign="center2" halign="center" scale="0.75" />
			
			<quad posn="0 -65 5" sizen="320 0.2" valign="center" halign="center" bgcolor="f51" />
			<label posn="157 -65 6" text="-65" valign="center2" halign="center" scale="0.75" />
			
			<!--
			<quad posn="0 55 5" sizen="320 0.2" valign="center" halign="center" bgcolor="f51" />
			<label posn="157 55 6" text="55" valign="center2" halign="center" scale="0.75" />
			
			<quad posn="0 50 5" sizen="320 0.2" valign="center" halign="center" bgcolor="f51" />
			<label posn="157 50 6" text="50" valign="center2" halign="center" scale="0.75" />
			-->
			
			<quad posn="0 0 5" sizen="320 0.2" valign="center" halign="center" bgcolor="f51" />
			<label posn="157 0 6" text="0" valign="center2" halign="center" scale="0.75" />

		
			/* Helpers verticaux */
			<quad posn="-95 0 5" sizen="0.2 180" valign="center" halign="center" bgcolor="f51" />
			<label posn="-95 -85 6" text="-95" valign="center2" halign="center" scale="0.75" />
			
			<quad posn="10 0 5" sizen="0.2 180" valign="center" halign="center" bgcolor="f51" />
			<label posn="10 -85 6" text="10" valign="center2" halign="center" scale="0.75" />
			
			<quad posn="115 0 5" sizen="0.2 180" valign="center" halign="center" bgcolor="f51" />
			<label posn="115 -85 6" text="115" valign="center2" halign="center" scale="0.75" />
			';
		
		}
		
		
		/* Logo */
		$ml .= '
		<quad id="TitleLogo" posn="-130 70 5" sizen="51 51" valign="center" halign="center" image="http://images.stunters.org/ml/StuntsLogo.png" imagefocus="http://images.stunters.org/ml/StuntsLogoFocus.png" scale="0.9" manialink="stunters" />
		
		/* BackGrounds */
		<!--
		<quad sizen="320 180" posn="0 0 -6" halign="center" valign="center" image="http://images.stunters.org/ml/ManiaLinkBG.jpg" />		
		-->
		<quad sizen="320 180" posn="0 0 -2" halign="center" valign="center" image="http://images.stunters.org/ml/ManiaLinkBGMenu2.png" />
		 
		 
		/* Footer */
		<quad sizen="330 20" posn="-165 -90 -2" halign="left" valign="bottom" image="http://images.stunters.org/ml/Footer.png" />
		<quad sizen="330 20" posn="0 80 -2" halign="center" valign="center" image="http://images.stunters.org/ml/Footer.png" rot="180" />	
		
		/* Page Title */		
		<frame posn="-160 47.5" >
			<label id="PageTitle" posn="5 0" text="'.htmlspecialchars($sc_ml["name"]).'" style="TextValueSmallSm" translate="1" valign="center2" scale="1.25" sizen="'.(61*1.25).'" />
		</frame>
		<label posn="-120 67.5" valign="center2" text="'.htmlspecialchars($sc_ml["name"]).'" scale="0.8" rot="-90" />
		
		/* Menu */
		<frame posn="0 80">
			<quad hidden="1" posn="0 0" sizen="320 13" halign="center" valign="center" image="http://images.stunters.org/ml/TopMenuBG.png" />
			<quad hidden="1" posn="-80 65" sizen="160 11.5" halign="center" valign="center" image="http://images.stunters.org/ml/TopMenuBG.png" />
			<quad hidden="1" posn="80 65" sizen="160 11.5" halign="center" valign="center" image="http://images.stunters.org/ml/TopMenuBG.png" />
		</frame>
		<frame posn="-155 30">
			<quad id="commonmenuitemBG" posn="-10 0" sizen="50 10" 	rot="-17" valign="center" halign="center" image="http://images.stunters.org/ml/TopMenuBG.png" scale="1.2" />
			
			<quad class="commonmenuitem" posn="20 0" halign="center" valign="center" sizen="50 10" rot="-17" manialink="'.$mlUrl.'?maps" image="http://images.stunters.org/ml/TopMenuBG.png" bgcolorfocus="f00" scriptevents="1" />
			<label text="Maps" rot="-17" posn="20 0" halign="center" valign="center2" translate="1" />
			
			<quad class="commonmenuitem" posn="20 -10" halign="center" valign="center" sizen="50 10" rot="-17" manialink="'.$mlUrl.'?cups" image="http://images.stunters.org/ml/TopMenuBG.png" bgcolorfocus="f00" scriptevents="1" />
			<label text="Cups" rot="-17" posn="20 -10" halign="center" valign="center2" translate="1" />
			
			<quad class="commonmenuitem" posn="20 0" halign="center" valign="center" sizen="50 10" rot="-17" manialink="'.$mlUrl.'?maps" image="http://images.stunters.org/ml/TopMenuBG.png" bgcolorfocus="f00" scriptevents="1" />
			<label text="Maps" rot="-17" posn="20 0" halign="center" valign="center2" translate="1" />
			
			<quad class="commonmenuitem" posn="20 -20" halign="center" valign="center" sizen="50 10" rot="-17" manialink="'.$mlUrl.'?ranking" image="http://images.stunters.org/ml/TopMenuBG.png" bgcolorfocus="f00" scriptevents="1" />
			<label text="Ranking" rot="-17" posn="20 -20" halign="center" valign="center2" translate="1" />
			
			<quad class="commonmenuitem" posn="20 -30" halign="center" valign="center" sizen="50 10" rot="-17" manialink="'.$mlUrl.'?bonus" image="http://images.stunters.org/ml/TopMenuBG.png" bgcolorfocus="f00" scriptevents="1" />
			<label text="Bonus scores" rot="-17" posn="20 -30" halign="center" valign="center2" translate="1" />
			
			<quad class="commonmenuitem" posn="20 -40" halign="center" valign="center" sizen="50 10" rot="-17" manialink="'.$mlUrl.'?karma" image="http://images.stunters.org/ml/TopMenuBG.png" bgcolorfocus="f00" scriptevents="1" />
			<label text="Karma" rot="-17" posn="20 -40" halign="center" valign="center2" translate="1" />
			
			<quad class="commonmenuitem" posn="20 -50" halign="center" valign="center" sizen="50 10" rot="-17" manialink="stuntips" image="http://images.stunters.org/ml/TopMenuBG.png" bgcolorfocus="f00" scriptevents="1" />
			<label text="Need some help?" rot="-17" posn="20 -50" halign="center" valign="center2" translate="1" />
			
			<label hidden="1" class="commonmenuitem" text="Best Players" 		rot="-17" posn="0 -30" manialink="'.$mlUrl.'?best" 		valign="center2" scriptevents="1" translate="1" />
			<label hidden="1" class="commonmenuitem" text="Cumulative data" 	rot="-17" posn="0 -40" manialink="'.$mlUrl.'?cumul" 	valign="center2" scriptevents="1" translate="1" />
		</frame>

		
		/* Player informations */
		<frame id="PlayerInfo" posn="5 74.5 5">
			<quad id="PlayerAvatar" posn="142 0" halign="center" valign="center" sizen="15 15" />	
			<quad posn="142 0 -1" halign="center" valign="center" bgcolor="0009" sizen="20 20" />	
			<label id="PlayerName" posn="125 1" halign="right" valign="center" scale="1.25" />
		</frame>

		
		/* ManiaLink Tools */
		<!-- ToolTip -->
		<frame id="ToolTip" posn="0 115 20">
			<quad id="ToolTipBg" sizen="40 2" posn="0 0 -1" halign="center" valign="center" image="http://images.stunters.org/ml/TooltipBG.png" opacity="0.95" />
			<label id="ToolTipLabel" textprefix="$fff" posn="0 0"  halign="center" valign="center2" text="All" style="TextValueSmall" translate="1" scale="1.25" />
		</frame>
		
		<!-- Fullscreen image -->
		<frame posn="0 0 25" id="FullImageFrame" hidden="1">
			<quad id="FullImageBG" posn="0 0 -1" sizen="320 180" bgcolor="000a" halign="center" valign="center" scriptevents="1" />
			<quad id="FullImage" posn="0 0" sizen="320 180" halign="center" valign="center" scale="0.98" />
		</frame>
		
		<!-- Hovered image -->
		<frame posn="0 160 15" id="HoveredImageFrame">
			<quad id="HoveredImageBG" posn="0 0 -1" sizen="80 45" bgcolor="000a" halign="center" valign="center" />
			<quad id="HoveredImage" posn="0 0" sizen="80 45" halign="center" valign="center" scale="0.98" />
		</frame>
		
		<!-- Execution Time -->
		<label id="ExecutionTime" textprefix="$fff" posn="-103 80" text="" valign="center2" style="TextValueSmall" />
		
		<!-- Please wait -->
		<frame id="PleaseWait" posn="0 0 15" hidden="1">
			<quad posn="0 0 -1" sizen="80 45" bgcolor="000a" halign="center" valign="center" />
			<label textprefix="$fff" posn="0 0" halign="center" valign="center2" text="Please wait..." multiline="1" translate="1" />
		</frame>		
		
		/* Contact, Social, etc */
		<frame id="Contact" posn="-75 20 25" hidden="1" scale="1.5">
			<quad sizen="320 180" posn="-160 100 -2" bgcolor="222a" scriptevents="1" />
			<quad posn="0 5 0" sizen="100 40"  bgcolor="000000aa" />
			<quad posn="2 -2 0" sizen="96 25"  bgcolor="00000066" />
			<quad posn="1 4 0" sizen="97 5.5"  style="BgsPlayerCard" substyle="ProgressBar" />
			<quad posn="2 4 5" sizen="5.5 5.5"  style="Icons64x64_1" substyle="NewMessage" />
			<label posn="9 3 3" textid="From" style="TextValueSmall" />
			<label id="FromNickName" posn="20 3 3" text="???" style="TextValueSmall" />
			<quad  id="ContactCloseButton" posn="93 4 5" sizen="5.5 5.5"  style="Icons64x64_1" substyle="QuitRace" scriptevents="1" />
			<entry id="MessageEntry" posn="2 -2 0" sizen="96 25" style="TextValueSmall" name="inputValue" autonewline="1" maxline="10" />
			<entry id="LoginEntry" posn="0 -5 0" sizen="30 5" style="TextValueSmall" name="inputValue2" default="???" />
			<entry id="NickNameEntry" posn="0 -5 0" sizen="30 5" style="TextValueSmall" name="inputValue3" default="???" /> 
			<label id="SendButton" posn="98 -27.5" style="CardButtonSmall" text="Send" halign="right" manialink="http://manialink.stunters.org/contact.php?value=inputValue&amp;value2=inputValue2&amp;value3=inputValue3" scriptevents="1" translate="1" />
		</frame>
		
		<frame posn="-155 -65 3">
			<quad sizen="20 6" posn="0 0 -1" valign="center" bgcolor="111a"  />		
			<quad id="ContactButton" data-tooltip="Send message to admin" posn="6 0" sizen="9 9" halign="center" valign="center" style="Icons64x64_1" substyle="NewMessage" scale="1" scriptevents="1" />
			<quad id="Facebook" data-tooltip="Join TM2 Stunters group" posn="15 0" sizen="5 5" halign="center" valign="center"  image="http://images.stunters.org/ml/LogoFacebook_64x64.png" imagefocus="http://images.stunters.org/ml/LogoFacebook_64x64Focus.png" url="https://www.facebook.com/groups/TM2Stunters/" scriptevents="1" />
			<!-- <label class="sharetitle" posn="42 0" text="Share" scriptevents="1" style="TextCardMedium" valign="center2" translate="1" goto="http://maniahome.maniaplanet.com/share/send/?link=Stunters&amp;message=%24fff%24oHave%20fun%20on%20Stunts%20title" /> -->
		</frame>
		';
		
		return $ml;		
	}

	
	private function getManiaLink()
	{
		global $sc_ml, $sc_toRender;
		
		$ml = '<?xml version="1.0" encoding="UTF-8" ?>
		<manialink id="'.$this->mlids[0].'" name="test" version="2"/>
		<timeout>0</timeout>
		';
		
		// common content
		$ml .= $this->GetCommonContent();
		
		$ml .= $this->GetTable();
		
		$ml .= '
		<label text="test" sizen="50" posn="0 0" />
		';
		
		$ml .= '</manialink>';
		
		return $ml;
	}
	
	
	
	
	
}
?>
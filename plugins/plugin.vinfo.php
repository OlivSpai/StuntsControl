<?php
/*howto:
-----------------------------------------------------------------------
on Startup all online players are updated / inserted into playerlist table (at least one player should be online when firing up the Plugin.
-----------------------------------------------------------------------
Used Vars:
Globals:$VINFO
			$Vinfo['playing']
			$Vinfo['spectating']
			$Vinfo['Cmaps']
			$Vinfo['maxP']
			$Vinfo['maxS']
		$cstats
			Top 10 Countrys as array
			cstats['Anzahl']
			$cstats['Country']
		$psum
			count Unique players as INT
		$Vconfig
			Playercount x,y
			$Vconfig['pstats'][0] 
			$Vconfig['pstats'][1] 
			Countrstats x,y
			$Vconfig['cstats'][0]
			$Vconfig['cstats'][1]
			Mapstats x,y
			$Vconfig['mstats'][0]
			$Vconfig['mstats'][1]
			unique Players x,y
			$Vconfig['ustats'][0]
			$Vconfig['ustats'][1]
-----------------------------------------------------------------------			
		TODO: Show DetailInfo on Click (Country, Maplist, 
		Eventually fill up Playernames from Table players (no country will be added , as this is not stored yet.) but at least we have the unique visitors then.
-----------------------------------------------------------------------
*/

/*
Changes: V 0.0.2: (23/July/2012)
		Replaced GetChallengeList with GetMapList 
		Playerlist : check if Login is serverlogin
		Added:Clock / Ladderlimits widget
Changes: V 0.0.3: (24/July/2012)		
		Bugfixes for show ingame and endround (be aware that config for show = 1 and not show = 0)
		maps and playerlist now clickable
Changes: V 0.0.4: (13/01/2012)
         Bugfixes for Stunters
		 design
Changes: V 0.0.5: (2013.09.22) 
//* Version: 0.0.6
//* Pastis-51
//* 2014.11.09
//* change style windows
		 
*/
error_reporting(E_ALL & ~ E_NOTICE);
class plugin_Vinfo extends FoxControlPlugin {
 
	public function onStartUp()
	{
		//Plugin infos
		$this->enabled=true;
		$this->name = 'Vinfo'; 
		$this->author ='ubm & pastis-51'; 
		$this->version='0.0.6'; 
		$this->registerCommand('vinstall', 'install(fill players from playertable into stats base' , '/vinstall', true);
		//register Globals
		global $Vinfo,$Vconfig,$cstats,$psum;
		 //4 Manilink ID´s well lets say 4 or so is needed ;)
		$this->registerMLIds(4);
		//Load config Online Players, Spectators
		$settingsXML = simplexml_load_file('./plugins/config/plugin.vinfo.config.xml');
		$Vconfig['pstats'][0]=(int) $settingsXML->pstatsx;
		$Vconfig['pstats'][1]=(int) $settingsXML->pstatsy;
		$Vconfig['pstats'][2]= $settingsXML->pstats;
		//Load Config Mapstats
		$Vconfig['mstats'][0]=(int) $settingsXML->mstatsx;
		$Vconfig['mstats'][1]=(int) $settingsXML->mstatsy;
		$Vconfig['mstats'][2]= $settingsXML->mstats;
		//Load Config Unique Players
		$Vconfig['lstats'][0]=(int) $settingsXML->lstatsx ;
		$Vconfig['lstats'][1]=(int) $settingsXML->lstatsy ;
		$Vconfig['lstats'][2]= $settingsXML->lstats;
		//Load Config Countr Stats
		$Vconfig['cstats'][0]=(int) $settingsXML->cstatsx ;
		$Vconfig['cstats'][1]=(int) $settingsXML->cstatsy ;
		$Vconfig['cstats'][2]= $settingsXML->cstats;
		//show When ? (ingame / endround)
		$Vconfig['showingame']=$settingsXML->showingame;
		$Vconfig['showendround']=$settingsXML->showendround;
		//reload essentials from config in root directory
		$xml = @simplexml_load_file('./config.xml');
		$this->settings['ServerLogin'] = $xml->serverlogin;
		
		//emulate Mapstart on startup
			$this->instance()->client->query('GetPlayerList',200,0);
			$playerlist=$this->instance()->client->getResponse();
			foreach ($playerlist as $info)
			{
				$args[1]=$info['Login'];
				if ($args[1] != $this->settings['ServerLogin']) $this->OnPlayerConnect($args);
			}
	}


	public function onBeginMap()
	{
		global $Vinfo,$Vconfig,$cstats,$psum;
		
		$Vconfig['counter']=0;
		$Vconfig['Ended']=0;
		$this->getVdata();
		// console('INGAME:'.$Vconfig['showingame']);
		if ($Vconfig['showingame']==True) $this->showStats(1);
		if ($Vconfig['showingame']==False) $this->showStats(0);
		$Vconfig['Ended']=0;
	}
	
	public function onEndMap()
	{
		global $Vinfo,$Vconfig,$cstats,$psum;
		$this->getVdata();
		$Vconfig['Ended']=1;
		if ($Vconfig['showendround']==True) $this->showStats(1);
		if ($Vconfig['showendround']==False) $this->showStats(0);
	}
	
	public function onManialinkPageAnswer($args)
	{
		if($args[2] == $this->mlids[0])
		{
			$args[2]='topactive';
			$plugintopactive = $this->getPluginInstance('plugin_top_players');
			$plugintopactive->onCommand($args);
		}	
		elseif ($args[2] == $this->mlids[3])
		{
			$args[2]='topnations';
			$plugintopnation = $this->getPluginInstance('plugin_top_players');
			$plugintopnation->onCommand($args);
		}		
		elseif ($args[2] == $this->mlids[1])
		{
			$args[2]='list';
			$pluginChallenges = $this->getPluginInstance('plugin_challenges');
			if ($pluginChallenges != false) $pluginChallenges->onCommand($args);
		}
	}

	//show widgets	
	Public function showStats($args)
	{
		global $Vinfo,$Vconfig,$cstats,$psum;
		if ($args==1)
		{
			$xml='
			<frame posn="'.$Vconfig['pstats'][0].' '.$Vconfig['pstats'][1].' 0" >
				<quad  bgcolor="1919194d" bgcolorfocus="000c" posn="25.8 -8.09 0" sizen="13.33 7.5" action="'.$this->mlids[1].'"/>
				<quad  bgcolor="1919194d" bgcolorfocus="000c" posn="12.46 -8.09 0" sizen="13.33 7.5" action="'.$this->mlids[0].'"/>
				<quad  bgcolor="1919194d" bgcolorfocus="000c" posn="-0.86 -8.09 0" sizen="13.33 7.5" action="'.$this->mlids[3].'"/>
				
				 // Map Info
				<frame posn="17 -3.5 0" >
					<label posn="16 -6.2 1" halign="center" valign="center" textfont="Stunts/XBall" text="$cf1'.$Vinfo['Cmaps'].'" scale="0.95" sizen="10 2"/>
					<label posn="16 -9.5 1" halign="center" valign="center" textfont="Stunts/XBall" text="$FFfMaps" scale="0.85" sizen="18 2"/>
				</frame>
				 
				// Country Stats
				<frame posn="4 -3.5 0" >
					<label posn="2 -6.2 1" halign="center" valign="center" textfont="Stunts/XBall" text="$cf1'.count($cstats).'" scale="0.95" sizen="10 2"/>
					<label posn="2 -9.5 1" halign="center" valign="center" textfont="Stunts/XBall" text="$FFFNations" scale="0.85" sizen="18 2"/>
					<label posn="15 -6.2 1" halign="center" valign="center" textfont="Stunts/XBall" text="$cf1'.$psum.'" scale="0.95" sizen="10 2"/>
					<label posn="15 -9.5 1" halign="center" valign="center" textfont="Stunts/XBall" text="$FFFVisitors" scale="0.85" sizen="18 2"/>
				</frame>
			</frame>';
			if ($Vconfig['cstats'][2]==True )$this->displayManialink($xml, $this->mlids[2],1);
		}
		else if ($args==0)
		{
			$this->closeMl($this->mlids[0]);
			$this->closeMl($this->mlids[1]);
			$this->closeMl($this->mlids[2]);
			$this->closeMl($this->mlids[3]);
		}
	}
		
	Public Function OnCommand($args)
	{
		global $Vinfo,$Vconfig,$cstats,$psum;
		
	}
	
	Public Function GetVData()
	{
		global $Vinfo,$Vconfig,$cstats,$psum;
		
		//Get needed Data for playerlist
		$Vinfo = array();
		//maxplayers
		$this->instance()->client->query('GetMaxPlayers');
		$Vinfo['maxP']=$this->instance()->client->getResponse();
			
		//maxspectators
		$this->instance()->client->query('GetMaxSpectators');
		$Vinfo['maxS']=$this->instance()->client->getResponse();
			
		//online Players
		//maximum of 200 players
		$playerlist =array();
		$this->instance()->client->query('GetPlayerList',200,0);
		$playerlist=$this->instance()->client->getResponse();
		$a=0;
		$s=0;
		$Vinfo['playing']=0;
		$Vinfo['spectating']=0;
		while ($a<count($playerlist))
		{
			if ($playerlist[$a]['Login']!=$this->settings['ServerLogin'] and $playerlist[$a]['IsSpectator']==1) $Vinfo['spectating']=$Vinfo['spectating']+1;
			if ($playerlist[$a]['Login']!=$this->settings['ServerLogin'] and $playerlist[$a]['IsSpectator']!=1) $Vinfo['playing']=$Vinfo['playing']+1;
			if ($playerlist[$a]['Login']==$this->settings['ServerLogin']) $s=-1; //if we have the serverlogin in the playerlist , reduce $b on 1 to get correct data.
			$a++;
		}
		$a=$a+$s;
		//get Mapstatistics
		//How many Maps ? (max 1000)
		$this->instance()->client->query('GetMapList', 1000, 0);
		$challenge_list = $this->instance()->client->getResponse();
		$Vinfo['Cmaps']=count($challenge_list);
		//countrystats
		$cstats=array();
		$a=0;
		$sql="SELECT count(*) as anzahl , country FROM players group by country order by anzahl desc";
		$result=mysqli_query($this->db,$sql);
		while ($row = mysqli_fetch_array( $result )){;
			$cstats[$a]['Anzahl']=$row['anzahl'];
			$cstats[$a]['Country']=$row['country'];
			$a++;
		}
		$sql="SELECT count(*) as anzahl FROM players";
		$result=mysqli_query($this->db,$sql);
		$row = mysqli_fetch_array( $result );
		$psum=$row['anzahl'];
	}
		
		
	function onPlayerDisconnect($args)
	{
		global $Vconfig;
		if($Vconfig['Ended'] != True) $this->onBeginMap();
	}

	function onPlayerInfoChanged($args)
	{
		global $Vconfig;
		if($Vconfig['Ended'] != True) $this->onBeginMap();
	}
	// When a player connects, check out what country, and send/update into DB, show /refresh widgets
	Public function onPlayerConnect($args)
	{
		global $Vconfig;
		if($Vconfig['Ended'] != True) $this->onBeginMap();
	}
		
	Public function onEverySecond()
	{
		global $Vconfig;
		if ($Vconfig['Ended'] == True) $this->last = $this->last - 10;
		if ( ($this->last + 10) > time() ) return;
		$this->last = time();
	}
}
?>
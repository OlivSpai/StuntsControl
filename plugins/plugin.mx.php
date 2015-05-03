<?php
//* Stunts Title Pack > Mania eXchange Plugin for FoxControl
//* Coded by	Spaï
//* UI Designer	Pastis
//* Copyright	http://stunters.org

class plugin_mx extends FoxControlPlugin {

    public function onStartUp()
	{
        $this->registerCommand('mx', 'Checks mX for maps /mx auth <authorname>', '/mx map <mapname>', true);

		$this->name = 'Mania eXchange Maps'; 
		$this->author ='Spaï'; 
		$this->version = '0.2'; 
		$this->registerMLIds(60);
		
		$this->registerPageAction('mxid');
		
		global $TrackID, $savePath, $mxid, $mx_maps_to_show;
		
		// Load config
		error_reporting(E_ALL ^ E_NOTICE);
		
		$xml 				= $this->getConfig('mxinfo');
		$savePath 			= $xml->savepath;
		$mx_maps_to_show 	= intval($xml->mx_maps_to_show);
		
		// Debug
		$this->onCommand( array(1 => 'spaii2', 2 => 'mx') );
	}
		
	public function onManialinkPageAnswer($args)
	{
		global $TrackID;
		global $savePath;
		global $mxid;

		// $args[1]  Login
		// $args[2]  Id/PageAction
		
		$close = $this->mlids[0];
			
		// Close window
		if ($args[2] == $this->mlids[0]) $this->closeMl($this->mlids[0], $args[1]);
		
		/* Based on Stunts Title DB */
		if (explode(':', $args[2])[0] == 'mxid')
		{
			global $settings;
			$mxid = explode(':', $args[2])[1];
			$login = $args[1];

			$admin_add_track = False;
			$rights = $this->getRights($login);
			
			if($rights[0] == 0)
			{ 
				$this->chatToLogin($login, 'Sorry, you don\'t have the rights to add $f00M$fffania e$f00X$fffchange tracks.', 'f60'); 
				return;
			}
		
			else if($rights[0] == 1) require('include/op_rights.php');
			else if($rights[0] == 2) require('include/admin_rights.php');
			else if($rights[0] == 3) require('include/superadmin_rights.php');
		
			if($admin_add_track == true)
			{
				/* get map info from stunts db */
				$data = $this->getHTTPdata('http://xml.stunters.org/?rubric=map&mxid='.$mxid);
				
				$read = simplexml_load_string($data);
				$map = $read->map->attributes();

				if(!isset($map->mxid))
				{
					$this->chatToLogin($login, 'The track with ID '.$mxid.' does not exist or stunts database is down!', 'f60');
					return;
				}
			
				$filename = $map->login.' '.$mxid.'.Map.Gbx';
				echo $filename;
				//Get Map File
				$trackfile = $this->getHTTPdata('http://getmap.stunters.org/'.$mxid);
				
				//Get Maps Directory
				$this->instance()->client->query('GetMapsDirectory');
				$trackdir = $this->instance()->client->getResponse();
				
				// Checks if the folder exists. If not, create it.
				if(!file_exists($trackdir.$savePath)) mkdir($trackdir.$savePath);

				$trackdir .= $savePath;					
				// Checks if the folder exists. If not, create it.
				if(!file_exists($trackdir)) mkdir($trackdir); 
				
				$trackdir .= $map->maptype.'/';
				if(!file_exists($trackdir)) mkdir($trackdir);
				
				$trackdir .= $map->enviro.'/';
				if(!file_exists($trackdir)) mkdir($trackdir);
				
				$trackdir .= $map->car.'/';
				if(!file_exists($trackdir)) mkdir($trackdir);
					
				// Write Map File to server							
				$dir = $trackdir.$filename;
				file_put_contents($dir, $trackfile);
				
				$this->instance()->client->query('GetDetailedPlayerInfo', $args[1]);
				$login = $this->instance()->client->getResponse();
				
				// Insert Map in playlist
				$this->instance()->client->query('InsertMap', $dir);
				$ans = $this->instance()->client->getResponse();	
				
				$this->chat($login['NickName'].'$z$s$0f0  added  $fff'.($map->name).'$z  $0f0map by  $z'.($map->nickname).'', 'AfA');
			}
			else
			{
				$this->chatToLogin($login['Login'], 'Map with ID '.$mxid.' does not exist or $f00M$fffania e$f00X$fffchange is down!', 'f60');
			}
		}
	} // onManialinkPageAnswer end
		
	// search tmx
	public function onCommand($args)
	{
		// $args[1]		Login
		// $args[2]		Command : mx
		// $args[3][0] 	author
		// $args[3][1]	pastis
		
		global $TrackID;
		global $mx_maps_to_show;
		

		/* Based on Stunts Title DB */
		$ml = '<manialink name="StuntsControl/MX Plugin" version="2">';
		$lineHeight = 6;
		$frameWidth = 160;
		
		if ($args[2] == 'mx')
		{
			{
				$ML = $this->manialink;		
				$ML->init();
				
				$ML->setTableHeaders([
					"Rank"=>50,
					"Name"=>25
				]);
				
				$ML->addLine([
					"Line 1 column 1",
					"Line 1 column 2"
				]);
				
				$ML->addLine([
					"Line 2 column 1",
					"Line 2 column 2"
				]);
				
				$ML->show($args[1]);
				return;				
			}			
			
			$posY = 0;
			$frameInstances = '';
			
			for ($i=0;$i<$mx_maps_to_show;$i++)
			{
				$frameInstances .= '<frameinstance posn="0 '.$posY.'" modelid="DataLine" hidden="1" />'.PHP_EOL;
				$posY -= $lineHeight;
			}
		
			$ml .= '
			<framemodel id="DataLine">
				<quad id="background" data-mxid="" posn="0 0 -2" sizen="'.$frameWidth.' '.$lineHeight.'" bgcolor="000a" bgcolorfocus="444a" valign="center2" scriptevents="1" />
				<quad id="enviro" data-name="" posn="1 0" sizen="5 5" image="" valign="center2" scriptevents="1" />
				<quad id="car" data-name="" posn="7 0" sizen="6 5" image="" valign="center2" scriptevents="1" />
				<label id="maptype" posn="14" sizen="44" valign="center2"/>
				<label id="name" posn="14" sizen="44" valign="center2" />
				<label id="nickname" posn="59" sizen="45" valign="center2"/>
				<label id="mxid" posn="'.($frameWidth-1).'" sizen="15" halign="right" valign="center2" />									
			</framemodel>
			
			<frame id="DataWindow" posn="'.(-$frameWidth/2).' 75" hidden="1">
				<quad posn="0 0 -2" sizen="'.$frameWidth.' 4" bgcolor="000a" valign="center2" />
				<label posn="2 0" text="Mx Maps" valign="center2" sizen="68" />
				<label id="DataMessage" posn="'.($frameWidth-2).' -5" text="Loading data..." halign="right" valign="center2" sizen="68" />
				<quad posn="'.($frameWidth-2).' 0" sizen="5 5" bgcolor="d00a" valign="center2" action="'.$this->mlids[0].'" />
				
				<frame posn="0" scale="1.25">
					<quad class="maptype" 	data-name="" 					data-tooltip="All maptypes" 	posn="0 0" 	sizen="5 5" style="Icons64x64_1" substyle="LvlGreen" scriptevents="1" halign="center" valign="center2" />
					<quad class="maptype" 	data-name="stunters" 			data-tooltip="Stunters" 		posn="6 0" 	sizen="5 5" style="Icons128x32_1" substyle="RT_Stunts" scriptevents="1" halign="center" valign="center2" />
					<quad class="maptype" 	data-name="stuntersreachscore" 	data-tooltip="Reach Score" 		posn="12 0" sizen="5 5" style="Icons128x32_1" substyle="RT_Rounds" scriptevents="1" halign="center" valign="center2" />
					<quad class="maptype" 	data-name="stuntersracers" 		data-tooltip="Racers" 			posn="18 0" sizen="5 5" style="Icons128x32_1" substyle="RT_TimeAttack" scriptevents="1" halign="center" valign="center2" />
				</frame>
				
				<frame posn="40" scale="1.25">
					<quad class="enviro" 	data-name="" 					data-tooltip="All environments" posn="0 0" 	sizen="5 5" style="Icons64x64_1" substyle="LvlGreen" scriptevents="1" halign="center" valign="center2" />
					<quad class="enviro" 	data-name="canyon" 				data-tooltip="Canyon" 			posn="6 0" 	sizen="5 5" image="http://images.stunters.org/ml/CanyonIcon.png" scriptevents="1" halign="center" valign="center2" />
					<quad class="enviro" 	data-name="valley" 				data-tooltip="Valley" 			posn="12 0" sizen="5 5" image="http://images.stunters.org/ml/ValleyIcon.png" scriptevents="1" halign="center" valign="center2" />
					<quad class="enviro" 	data-name="stadium" 			data-tooltip="Stadium" 			posn="18 0" sizen="5 5" image="http://images.stunters.org/ml/StadiumIcon.png" scriptevents="1" halign="center" valign="center2" />
				</frame>

				<frame posn="80" scale="1.25">
					<quad class="car" 		data-name=""					data-tooltip="All vehicles" 	posn="0 0" 	sizen="5 5" style="Icons64x64_1" substyle="LvlGreen" scriptevents="1" halign="center" valign="center2" />
					<quad class="car fit" 	data-name="canyoncar" 			data-tooltip="Canyon car" 		posn="6 0" 	sizen="5 5" image="http://images.stunters.org/ml/CanyonCar.jpg" scriptevents="1" halign="center" valign="center2" />
					<quad class="car fit" 	data-name="valleycar" 			data-tooltip="Valley car" 		posn="12 0" sizen="5 5" image="http://images.stunters.org/ml/ValleyCar.jpg" scriptevents="1" halign="center" valign="center2" />
					<quad class="car fit" 	data-name="stadiumcar" 			data-tooltip="Stadium car" 		posn="18 0" sizen="5 5" image="http://images.stunters.org/ml/StadiumCar.jpg" scriptevents="1" halign="center" valign="center2" />
				</frame>

				<frame posn="120" scale="1.25">
					<quad class="difficulty" 	data-name=""					data-tooltip="All" 			posn="0 0" 	sizen="5 5" style="Icons64x64_1" substyle="LvlGreen" scriptevents="1" halign="center" valign="center2" />
					<quad class="difficulty" 	data-name="beginner" 			data-tooltip="Beginner" 	posn="6 0" sizen="5 5" style="Icons128x128_1" substyle="Easy" scriptevents="1" halign="center" valign="center2" />
					<quad class="difficulty" 	data-name="intermediate" 		data-tooltip="Intermediate" posn="12 0" sizen="5 5" style="Icons128x128_1" substyle="Medium" scriptevents="1" halign="center" valign="center2" />
					<quad class="difficulty" 	data-name="advanced" 			data-tooltip="Advanced" 	posn="18 0" sizen="5 5" style="Icons128x128_1" substyle="Hard" scriptevents="1" halign="center" valign="center2" />
					<quad class="difficulty" 	data-name="expert" 				data-tooltip="Expert" 		posn="24 0" sizen="5 5" style="Icons128x128_1" substyle="Extreme" scriptevents="1" halign="center" valign="center2" />
				</frame>				
				
				<frame posn="0 -11" id="DataFrame">
					'.$frameInstances.'
				</frame>
			</frame>
			
			<frame id="DataLoading" posn="0 65">
				<quad posn="0 0 -2" sizen="70 5" bgcolor="000a" halign="center" valign="center2" />					
			</frame>
			
			<frame id="PageSelector" posn="-25 -60">
				<quad posn="0 0 -1" sizen="50 6" bgcolor="0007" valign="center2" />
				<quad id="PrevPage" data-page="" posn="1 0" sizen="5 5" valign="center2" image="http://images.stunters.org/ml/previous.png" imagefocus="http://images.stunters.org/ml/previous-focus.png" scriptevents="1" />
				<label id="Paginate" posn="25 0" halign="center" valign="center2" text="??/??" scale="1.1" />
				<quad id="NextPage" data-page=""  posn="49 0" sizen="5 5" halign="right" valign="center2" image="http://images.stunters.org/ml/next.png" imagefocus="http://images.stunters.org/ml/next-focus.png" scriptevents="1" />
			</frame>
			
			<script><!--
			#Include "TextLib"
			#Include "MathLib"
			
			declare Text[Text]		Filters;
			declare CMlFrame[] 		DataLines;
			declare CHttpRequest 	Req;
			declare Integer 		ReqTimeOut;
			declare CMlLabel 		DataMessage;
			
			Text GetUrl()
			{
				declare url = "http://xmltest.stunters.org/?rubric=allmaps&results='.$mx_maps_to_show.'";	
				// url	^= "&login=" ^ LocalUser.Login;	TODO: Add &serverlogin= for having maps not played on server (a little bit as norank)
				foreach(id=>value in Filters) url ^= "&"^id^"="^value;
				return url;
			}

			Void Request()
			{	
				if(Req != Null && Http.Requests.exists(Req)) Http.Destroy(Req);
				Req = Http.CreateGet(GetUrl(), False);					
				ReqTimeOut = Now + 10000;
			}
							
			Void UpdateLine(Integer _LineNumber, Text[Text] _Data)
			{
				if (!DataLines.existskey(_LineNumber)) return;
				
				declare Line <=> DataLines[_LineNumber];
				(Line.GetFirstChild("mxid") 		as CMlLabel).SetText(_Data["mxid"]);
				(Line.GetFirstChild("name") 		as CMlLabel).SetText(_Data["name"]);
				(Line.GetFirstChild("nickname") 	as CMlLabel).SetText(_Data["nickname"]);
				(Line.GetFirstChild("enviro") 		as CMlQuad).ImageUrl = "http://images.stunters.org/ml/" ^ _Data["enviro"] ^ "Icon.png";
				(Line.GetFirstChild("car") 			as CMlQuad).ImageUrl = "http://images.stunters.org/ml/" ^ _Data["car"] ^ ".jpg";
				(Line.GetFirstChild("background") 	as CMlQuad).DataAttributeSet("mxid", _Data["mxid"]);
				(Line.GetFirstChild("enviro") 		as CMlQuad).DataAttributeSet("name", _Data["enviro"]);
				
				Line.Show();
			}
			
			Void DisplayData(Text _Result)
			{
				declare CXmlDocument xml <=> Xml.Create(_Result);
				
				if (xml != Null) 
				{
					declare LineNumber = 0;
					foreach(Node in xml.Nodes)
					{	
						if (Node.Name == "map")
						{				
							UpdateLine(LineNumber,
										[
										"mxid"=>Node.GetAttributeText("mxid", "0"),
										"enviro"=>Node.GetAttributeText("enviro", ""),
										"car"=>Node.GetAttributeText("car", ""),
										"maptype"=>Node.GetAttributeText("maptype", ""),
										"name"=>Node.GetAttributeText("name", ""),
										"nickname"=>Node.GetAttributeText("nickname", "")
										]
									);
							LineNumber += 1;
						}
						else if (Node.Name == "page")
						{
							declare PrevPage <=> (Page.GetFirstChild("PrevPage") as CMlQuad);
							declare NextPage <=> (Page.GetFirstChild("NextPage") as CMlQuad);
														
							NextPage.Visible = (Node.GetAttributeText("next", "") != "");
							PrevPage.Visible = (Node.GetAttributeText("previous", "") != "");
							
							NextPage.DataAttributeSet("page", Node.GetAttributeText("next", ""));
							PrevPage.DataAttributeSet("page", Node.GetAttributeText("previous", ""));
								
							(Page.GetFirstChild("Paginate") as CMlLabel).SetText("$o$s"^Node.GetAttributeText("current", "")^"$o $ddd/ "^Node.GetAttributeText("total", ""));
						
							DataMessage.SetText(Node.GetAttributeText("resultsTotal", "")^ " maps");
						}
					}	
					
					for(J,LineNumber,DataLines.count-1) DataLines[J].Hide();
					
					Page.GetFirstChild("DataWindow").Visible = (LineNumber > 0);
				}
				Xml.Destroy(xml);
				xml = Null;
			}
			
			Void CheckRequest()
			{
				if (Req == Null) return;
				if(Req.IsCompleted || Now > ReqTimeOut)
				{
					if (Req.StatusCode == 200) DisplayData(Req.Result);
					else DataMessage.SetText("Map list unreachable...");
					
					Http.Destroy(Req);
					Req = Null;
				}
				else if (Now < ReqTimeOut)
				{
					DataMessage.SetText("Loading data... "^Abs((Now-ReqTimeOut)/1000)+1);
				}
			}
			
			main() {
			
				foreach (Control in (Page.GetFirstChild("DataFrame") as CMlFrame).Controls) DataLines.add((Control as CMlFrame));
				
				DataMessage <=> (Page.GetFirstChild("DataMessage") as CMlLabel);
				
				Filters = [
					"enviro"=>"",
					"maptype"=>"",
					"car"=>"",
					"difficulty"=>"",
					"page"=>"1"
						];
						
				declare persistent SC_Mx_Filters = Text[Text];
				// SC_Mx_Filters.clear();
				if (SC_Mx_Filters.count != Filters.count) SC_Mx_Filters = Filters;
				Filters = SC_Mx_Filters;				

				Request();
				
				while(True)
				{
					yield;
					
					CheckRequest();

					foreach(Event in PendingEvents)
					{
						if(Event.Type == CMlEvent::Type::MouseClick)
						{	
							if(Event.ControlId == "background") TriggerPageAction("mxid:"^Event.Control.DataAttributeGet("mxid"));
							else if(Event.ControlId == "PrevPage" || Event.ControlId == "NextPage")
							{
								Filters["page"] = Event.Control.DataAttributeGet("page");
								Request();
							}
							else if(Event.Control.HasClass("maptype"))
							{								
								Filters["maptype"] = Event.Control.DataAttributeGet("name");
								Filters["page"] = "1";
								Request();
							}
							else if(Event.Control.HasClass("enviro"))
							{								
								Filters["enviro"] = Event.Control.DataAttributeGet("name");
								Filters["page"] = "1";
								Request();
							}
							else if(Event.Control.HasClass("car"))
							{								
								Filters["car"] = Event.Control.DataAttributeGet("name");
								Filters["page"] = "1";
								Request();
							}
							else if(Event.Control.HasClass("difficulty"))
							{								
								Filters["difficulty"] = Event.Control.DataAttributeGet("name");
								Filters["page"] = "1";
								Request();
							}
						}
					}
				}
			
			}
			
			--></script>
			</manialink>
			';
			
			$this->displayManialinkToLogin($args[1], $ml, $this->mlids[0], 1);
			
		}		
	} // onCommand function End

	function getHTTPdata($url)
	{
		$options =	array
					(
						'http' => array
						(
                         'user_agent'    => 'Stunts Control',
                         'max_redirects' => 1000,
                         'timeout'       => 1000,
						)
					);
        $context = stream_context_create( $options );

        return @file_get_contents( $url, true, $context );
	}
	
}
?>
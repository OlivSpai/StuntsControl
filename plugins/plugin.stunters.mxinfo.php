<?php
//* Stunters Title Pack > Mania eXchange Plugin for FoxControl
//* Version		0.1
//* Coded by	Spaï
//* UI Designer	Pastis
//* Copyright	http://stunters.org
//
// v0.1 - 2012.11.27

// v0.2 - 2013.09.22
// Environement is now automatic, no need to change Stunters to ValleyStunters, etc...
// Logo replaced by Title Pack Logo

class plugin_stunters_mxinfo extends FoxControlPlugin {

private $useStuntsXml;

    public function onStartUp()
	{
        $this->registerCommand('mx', 'Checks mX for maps /mx auth <authorname>', '/mx map <mapname>', true);

		$this->name = 'Mania eXchange Maps'; 
		$this->author ='Spaï'; 
		$this->version = '0.2'; 
		$this->registerMLIds(60);
		
		global $TrackID, $savePath, $mxid, $mx_maps_to_show, $useStuntsXml;
		
		// Load config
		error_reporting(E_ALL ^ E_NOTICE);
		$xml = simplexml_load_file('./plugins/config/plugin.stunters.mxinfo.config.xml');
		$savePath = $xml->savepath;
		$mx_maps_to_show = intval($xml->mx_maps_to_show);
		$this->useStuntsXml = intval($xml->use_stunts_xml);
		
		// Debug
		$this->onCommand( array(1 => 'spaii', 2 => 'mx') );
	}
		
	public function onManialinkPageAnswer($args)
	{
		global $TrackID;
		global $savePath;
		global $mxid;

		
		// $args[1]  Login
		// $args[2]  Id
		
		$close = $this->mlids[0];
			
		// Close window
		if ($args[2] == $this->mlids[0]) $this->closeMl($this->mlids[0], $args[1]);
		
		/* Based on Stunts Title DB */
		if ($this->useStuntsXml)
		{
			print_r($args);
		}		
		else // Use Mx Fetcher
		{
			$a = 1;


			$a = 0;
			while ($a < 15)
			{
				if ($args[2] == $this->mlids[($a+1)])
				{
					global $settings;
					$mxid = $TrackID[$a-1];
					$CommandAuthor = $args[1];

					$admin_add_track = False;
					$rights = $this->getRights($CommandAuthor);
					
					if($rights[0] == 0)
					{ 
						$this->chatToLogin($CommandAuthor, 'Sorry, you don\'t have the rights to add $f00M$fffania e$f00X$fffchange tracks.', 'f60'); 
						return;
					}
				
					else if($rights[0] == 1) require('include/op_rights.php');
					else if($rights[0] == 2) require('include/admin_rights.php');
					else if($rights[0] == 3) require('include/superadmin_rights.php');
				
					if($admin_add_track == true)
					{
						$mxid = $TrackID[$a];
						$data = $this->getHTTPdata('http://tm.mania-exchange.com/api/tracks/get_track_info/id/'.$mxid.'?format=xml');
						$read = simplexml_load_string($data);
						
						//Set Filename and Trackname
						if(!isset($read->Name))
						{
							$this->chatToLogin($CommandAuthor, 'The track with ID '.$mxid.' does not exist or $f00M$fffania e$f00X$fffchange is down!', 'f60');
							return;
						}
						
						$fname = $read->Name.'.Map.Gbx';
						$except = array('\\', '/', ':', '*', '?', '"', '<', '>', '|', 'ß');
						
						// Replace invalid character file name
						$fname = str_replace($except, '', $fname); 

						$filename = $mxid.'_'.$fname;
						
						$trackname = $read->Name;
						
						//Get Map File
						$trackfile = $this->getHTTPdata('http://tm.mania-exchange.com/tracks/download/'.$mxid);
						
						//Get Maps Directory
						$this->instance()->client->query('GetMapsDirectory');
						$trackdir = $this->instance()->client->getResponse();
							
						// Checks if the folder exists. If not, create it.
						if(!file_exists($trackdir.$savePath)) mkdir($trackdir.$savePath); 
							
						// Write Map File to server							
						$dir = $trackdir.$this->trackdir.$savePath.$filename;
						file_put_contents($dir, $trackfile);
						
						$this->instance()->client->query('GetDetailedPlayerInfo', $args[1]);
						$CommandAuthor = $this->instance()->client->getResponse();
						
						// Insert Map in playlist
						$this->instance()->client->query('InsertMap', $dir);
						$ans = $this->instance()->client->getResponse();	
						
						$this->chat($CommandAuthor['NickName'].'$z$s$0f0 added $fff'.$trackname.'$0f0 from $f00M$fffania e$f00X$fffchange', 'AfA');
					}
					else
					{
						$this->chatToLogin($CommandAuthor['Login'], 'Map with ID '.$mxid.' does not exist or $f00M$fffania e$f00X$fffchange is down!', 'f60');
					}
				}
				
				$a++;					
			} // While 
		

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
		if ($this->useStuntsXml)
		{
			$ml = '<manialink name="StuntsControl/MX Plugin" version="2">';
			$lineHeight = 6;
			$frameWidth = 120;
			
			if ($args[2] == 'mx')
			{
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
					<quad id="mapcar" data-name="" posn="7 0" sizen="6 5" image="" valign="center2" scriptevents="1" />
					<label id="maptype" posn="14" sizen="45" valign="center2"/>
					<label id="name" posn="14" sizen="45" valign="center2"/>
					<label id="mxid" posn="'.($frameWidth-1).'" sizen="15" halign="right" valign="center2" />									
				</framemodel>
				
				<frame id="DataWindow" posn="'.(-$frameWidth/2).' 75" hidden="1">
					<quad posn="0 0 -2" sizen="'.$frameWidth.' 4" bgcolor="000a" valign="center2" />
					<label posn="2 0" text="Mx Maps" valign="center2" sizen="68" />
					<frame posn="0 -5" id="FiltersFrame">
						<quad posn="0 0 -2" sizen="'.$frameWidth.' 7" bgcolor="000e" valign="center2" />
						<label id="" class="enviro" posn="2 0" text="All" valign="center2" sizen="15" translate="1" scriptevents="1" />
						<quad id="Canyon" class="enviro" posn="15 0" sizen="5 5" image="http://images.stunters.org/ml/CanyonIcon.png" valign="center2" scriptevents="1" />
						<quad id="Valley" class="enviro" posn="21 0" sizen="5 5" image="http://images.stunters.org/ml/ValleyIcon.png" valign="center2" scriptevents="1" />
						<quad id="Stadium" class="enviro" posn="27 0" sizen="5 5" image="http://images.stunters.org/ml/StadiumIcon.png" valign="center2" scriptevents="1" />
					</frame>
					<frame posn="0 -11" id="DataFrame">
						'.$frameInstances.'
					</frame>
				</frame>
				
				<frame id="DataLoading" posn="0 65">
					<quad posn="0 0 -2" sizen="70 5" bgcolor="000a" halign="center" valign="center2" />
					<label id="DataMessage" posn="0 0" text="Loading data..." halign="center" valign="center2" sizen="68" />
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
					declare url = "http://xml.stunters.org/?rubric=allmaps&results='.$mx_maps_to_show.'";	
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
					(Line.GetFirstChild("mxid") as CMlLabel).SetText(_Data["mxid"]);
					(Line.GetFirstChild("name") as CMlLabel).SetText(_Data["name"]);
					(Line.GetFirstChild("enviro") as CMlQuad).ImageUrl = "http://images.stunters.org/ml/" ^ _Data["enviro"] ^ "Icon.png";
					(Line.GetFirstChild("mapcar") as CMlQuad).ImageUrl = "http://images.stunters.org/ml/" ^ _Data["mapcar"] ^ ".jpg";
					(Line.GetFirstChild("background") as CMlQuad).DataAttributeSet("mxid", _Data["mxid"]);
					
					(Line.GetFirstChild("enviro") as CMlQuad).DataAttributeSet("name", _Data["enviro"]);
					(Line.GetFirstChild("mapcar") as CMlQuad).DataAttributeSet("name", _Data["mapcar"]);
					
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
											"mapcar"=>Node.GetAttributeText("mapcar", ""),
											"maptype"=>Node.GetAttributeText("maptype", ""),
											"name"=>Node.GetAttributeText("name", "")
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
						"page"=>"1"
							];
					
					Request();
					
					while(True)
					{
						yield;
						
						CheckRequest();

						foreach(Event in PendingEvents)
						{
							if(Event.Type == CMlEvent::Type::MouseClick)
							{	
								if(Event.ControlId == "background") TriggerPageAction("/mx"^Event.Control.DataAttributeGet("mxid"));
								else if(Event.ControlId == "PrevPage" || Event.ControlId == "NextPage")
								{
									Filters["page"] = Event.Control.DataAttributeGet("page");
									Request();
								}
								else if(Event.ControlId == "enviro")
								{
									
									Filters["enviro"] = Event.Control.DataAttributeGet("name");
									Filters["page"] = "1";
									Request();
								}
								else if(Event.Control.HasClass("enviro"))
								{
									
									Filters["enviro"] = Event.ControlId;
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
		}		
		else // Use Mx Fetcher
		{
			$searchtype = '';
			$searchvalue = '';
			$tosearch = '';
			$tochat = '';
			$page = 1;
			
			$CommandAuthor = $args[1];		
				
			if ($args[2] == 'mx')
			{
				switch ($args[3][0])
				{
					case "auth":
					case "author":
					case "login":
					$searchtype = "author"; 
					break;
					
					case "map":
					case "track":
					case "name": 
					case "trackname": 
					$searchtype = "trackname"; 
					break;
				}
				
				if ($searchtype)
				{	
					$searchvalue = ($args[3][1]); 
					$tosearch = '&'.$searchtype.'='.$searchvalue;
					$tochat = ' with '.$searchtype.' $cc0'.$searchvalue;
				}
				
				$this->chatToLogin($CommandAuthor,'$fffSearching for Stunters Maps on $f00M$fffania e$f00X$fffchange'.$tochat );

				// Get data from MX and parse
				
				// pastis  // $url = 'http://tm.mania-exchange.com/tracksearch?api=on&tpack='.$this->titleid.$tosearch.'&limit='.($mx_maps_to_show+1).'&page='.$page;
				$url = 'http://tm.mania-exchange.com/tracksearch?api=on&mtype=Stunters'.'&limit='.($mx_maps_to_show+1).'&page='.$page;
				
				// $url2 = 'http://xml.stunters.org/?rubric=maps';
				// $data2 = $this->getHTTPdata($url2);
				// $xml2 = simplexml_load_string($data2);
				// print_r($xml2);
				
				
				$close = $this->mlids[0];		
				$previousPage = $this->mlids[45];
				$nextPage = $this->mlids[46];
				
				$a = 1;
				while($a < 16)
				{
					$myid[($a)] = $this->mlids[($a)]; //reserve ID for Links
					$a++;
				}
			
				global $xml;
				global $mapscount;
				global $a;
				
				$xml = Array();
				$data = $this->getHTTPdata($url);
				$data = json_decode($data, true);

				$posY = 5;
				$mapscount = count($data);
				$overrun=0;
				
				if ($mapscount > 15)
				{
					$overrun = $mapscount;
					$mapscount = 15; 
				}
				
				$a = 0;		
				while ($a < $mapscount)
				{
					$TrackID[$a] = $data[$a]['TrackID'];
					$Username[$a] = $data[$a]['Username'];
					$Name[$a] = $data[$a]['Name'];
					$EnvironmentName[$a] = $data[$a]['EnvironmentName'];
					$VehicleName[$a] = $data[$a]['VehicleName'];
					$Awards[$a] = $data[$a]['AwardCount'];
					$Tracklength[$a] = $data[$a]['LengthName'];
					$StyleName[$a] = $data[$a]['StyleName'];
					
					$a++;
				}
			
				// Height of rows
				$h = 11.5;
				$heigh = (28+$h +((int)$mapscount)*(4.8));
				$zr = "\r\n";
				
				// Title
				$xml = '<timeout>0</timeout>'.$zr;
				$xml .= '<frame posn="0 29.1 3" sizen="120">'.$zr;

				// General window
				$xml .= '<quad posn="0 39.9 -3" sizen="175.5 '.$heigh.'" halign="center" style="BgsPlayerCard" substyle="BgCard" />'.PHP_EOL;

				// Title background
				$xml .= '<quad posn="-3.4 38.8 -2" sizen="167.7 4.6" halign="center" style="BgsPlayerCard" substyle="BgActivePlayerScore" />'.$zr

				// window title
				.'<label posn="0 38.25 0" halign="center" textsize="2" url="http://tm.mania-exchange.com/tracksearch?tpack='.$this->titleid.'" text="$o$FFFStunters $FFFMaps from $f00M$fffania-e$f00X$fffchange $z" />'.$zr
				.'<quad posn="0 34.05 0" sizen="44.8 19.1" halign="center" url="http://tm.mania-exchange.com/tracksearch?tpack='.$this->titleid.'" style="TitleLogos" substyle="Title" />'.$zr

				.'<label posn="-85 29.8 3" halign="left" textsize="1" text="$i$FFFExample:
		/mx author pastis 
		/mx name 2013" />'.$zr
					
				// Colums title
				.'<label posn="-85 10 3" halign="left" textsize="2" text="$i$FFFmX ID" />'.$zr
				.'<label posn="-70 10 3" halign="left" textsize="2" text="$i$FFFMap Name" />'.$zr
				.'<label posn="-32 10 3" halign="left" textsize="2" text="$i$FFFAuthor" />'.$zr
				.'<label posn="-13 10 3" halign="left" textsize="2" text="$i$FFFEnvir." />'.$zr
				.'<label posn="5 10 3" halign="left" textsize="2" text="$i$FFFCar" />'.$zr
				.'<label posn="34 10 3" halign="left" textsize="2" text="$i$FFFAwards" />'.$zr
				.'<label posn="52 10 3" halign="left" textsize="2" text="$i$FFFLength" />'.$zr
				.'<label posn="69 10 3" halign="left" textsize="2" text="$i$FFFDownload" />'.$zr;
			
				// Preparing ManiaScript Part 1
				$else = '';
				$script = '';
				
				$script = '<script><!-- 
				main() {			
					declare Screenshot <=> (Page.GetFirstChild("Screenshot") as CMlQuad);
					declare Thumbnail <=> (Page.GetFirstChild("Thumbnail") as CMlQuad);
					declare ScreensFrame <=> (Page.GetFirstChild("ScreensFrame") as CMlFrame);				
					while(True)
					{
						yield;
						foreach(Event in PendingEvents)
						{
							if(Event.Type == CMlEvent::Type::MouseOver)
							{
								Screenshot.ImageUrl = "http://maps.stunters.org/screenshots/" ^ Event.ControlId ^ ".jpg";
								Thumbnail.ImageUrl = http://maps.stunters.org/thumbnails/" ^ Event.ControlId ^ ".jpg";
								ScreensFrame.Show();
							}
							else if(Event.Type == CMlEvent::Type::MouseOut)
							{
								ScreensFrame.Hide();
							}
						}
						
						ScreensFrame.PosnX = MouseX;
						ScreensFrame.PosnY = MouseY + 5.0;
					}
				}
				--></script>'.PHP_EOL;
				
				$a = 0;
				//main data
				
				// Screenshot and Thumbnail to show when rollover (MX images)
				$xml .= '<frame id="ScreensFrame" hidden="1">';
					$xml .= '<quad posn="0 1 20" sizen="84 31" style="BgsPlayerCard" substyle="ProgressBar" halign="center" />';
					$xml .= '<quad posn="1 0 25" sizen="40 29" id="Screenshot" image="http://tm.mania-exchange.com/tracks/screenshot/normal/49084?=.jpg" />';
					$xml .= '<quad posn="-1 0 25" sizen="40 29" id="Thumbnail" halign="right" image="http://tm.mania-exchange.com/tracks/thumbnail/normal/49084?=.jpg" />';
				$xml .= '</frame>';
				
				while ($a < $mapscount)
				{
					$imgurl = 'http://tm.mania-exchange.com/tracks/screenshot/normal/'.$TrackID[$a].'&?=.jpg';
					$mapUrl = 'http://tm.mania-exchange.com/tracks/'.$TrackID[$a];
									
					//Map Name Button	
					$xml .= '<quad id="'.$TrackID[$a].'" posn="-85 '.($posY+0.3).' 1" sizen="105 4" bgcolor="666d" bgcolorfocus="090c" action="'.$myid[($a+1)].'" ScriptEvents="1" />'.$zr
						
					// MX ID	
					.'<label posn="-85 '.$posY.' 3" valign="top" halign="left" scale="0.8" text="$000'.$TrackID[$a].'" />'.$zr
					
					// Map Name	
					.'<label posn="-70 '.$posY.' 3" valign="top" halign="left" scale="0.8"  sizen="45 4" text="$ff5'.$Name[$a].'" />'.$zr
					
					// Author	
					.'<label posn="-32 '.$posY.' 3" valign="top" halign="left" scale="0.8"  sizen="18 4" text="$000'.$Username[$a].'" />'.$zr
					
					// EnvironmentName	
					.'<label posn="-14 '.$posY.' 3" valign="top" halign="left" scale="0.8"  sizen="16 4" text="$fff'.$EnvironmentName[$a].'" />'.$zr
					
					// VehicleName	
					.'<label posn="3 '.$posY.' 3" valign="top" halign="left" scale="0.8"  sizen="20 4" text="$fff'.$VehicleName[$a].'" />'.$zr
					
					// Thumbnail image	
					.'<quad id="'.$TrackID[$a].'" image="'.$imgurl.'" posn="22 '.($posY+0).' 3" sizen="5 3.9" ScriptEvents="1" />'.$zr
						
					// Awards	
					.'<quad posn="29 '.($posY+0.1).' 3" url="'.$mapUrl.'" style="Icons64x64_1" substyle="OfficialRace" sizen="4 4" />'.$zr
					.'<label posn="34 '.($posY-0.5).' 3" valign="top" halign="left" scale="0.7" text="'.$Awards[$a].'" />'.$zr
					.'<label  posn="39.25 '.$posY.' 3" valign="top" halign="left" scale="0.7" text="$fffAward" />' .$zr
					.'<quad posn="38 '.($posY+0.3).' 1" sizen="12 4" style="BgsPlayerCard" substyle="ProgressBar" url="'.$mapUrl.'" />'.$zr
						
					// Map time lenght
					.'<label posn="52 '.$posY.' 3" valign="top" halign="left" scale="0.8" text="'.$Tracklength[$a].'" />'.$zr
					
					// Add Map on Server
					.'<label posn="73 '.$posY.' 3" valign="top" halign="left" scale="0.8" text="$fffAdd" />' .$zr
					.'<quad posn="69 '.($posY+0.3).' 1" sizen="15 4" style="BgsPlayerCard" substyle="ProgressBar" action="'.$myid[($a+1)].'" />'.$zr;
					$posY -= 4.7;
					$a++;
				}
			
				// Bottom close button	
				$xml .= '<label posn="0 '.($posY-2.5).' 3" valign="center" halign="center" textsize="2" sizen="10 5.5" textprefix="$o$fff" text="Close" translate="1" />
				<quad posn="0 '.($posY-1.2).' 2.2" sizen="24.5 3.5" halign="center" style="BgsPlayerCard" substyle="BgCard" action="'.$close.'" />
				<quad posn="0 '.($posY-1.2).' 2.1" sizen="24.5 3.5" halign="center" style="BgsPlayerCard" substyle="BgRacePlayerName" />'.PHP_EOL;
				 
				// Bottm white line
				$xml .= '<quad posn="0 '.($posY-0.6).' 2" sizen="175.3 0.5" halign="center" bgcolor="fffb" />'.$zr;
				 
				// Top right close button
				$xml .= '<quad posn="83 39.2 -1" sizen="5.5 5.3" halign="center" style="Icons64x64_1" substyle="Close" action="'.$close.'" />'.$zr;
				$xml .= '<quad posn="83 38.8 -2" sizen="6.5 4.6" halign="center" style="BgsPlayerCard" substyle="BgActivePlayerScore" />'.$zr;
			
				$xml .= '<quad posn="-40 '.($posY).' 0" sizen="8 8" style="UIConstruction_Buttons" substyle="Left" />';
				$xml .= '<quad posn="40 '.($posY).' 0" sizen="8 8" style="UIConstruction_Buttons" substyle="Right" />';
				
				$xml .= '</frame>'.$zr;
				$xml .= $script.$zr;
				
				
				$this->displayManialinkToLogin($CommandAuthor, $xml, $this->mlids[0], 1);

			} // if ($args[2] == 'mx') End
		
		} // End of mx fetcher
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
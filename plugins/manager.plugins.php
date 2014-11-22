<?php
//* manager.plugins.php - Ingame plugin-manager
//* Version:   0.4
//* Coded by:  cyrilw
//* Copyright: FoxRace, http://www.fox-control.de
//* Version: 0.5
//* Pastis-51
//* 2014.11.09
//* change style windows

global $pluginmanager;
//       Settings        //
// !!! DONT EDIT THIS !! //
//  EDIT FROM LINE 20!!  //
$pluginmanager['PluginTypes'] = array();
$pluginmanager['ExpectedPlugins'] = array();
$pluginmanager['Player'] = array();
$pluginmanager['Player']['login'] = '';
$pluginmanager['Player']['time'] = '';
$pluginmanager['Changes'] = array();
$pluginmanager['Players'] = array();

//START EDIT
//PluginTypes are the types of all plugins. You can see the types from the plugin files.
//For example: The type of plugin.name.php is plugin, the type of chat.name.php is chat and the type of manager.plugins.php is manager
$pluginmanager['PluginTypes'][] = 'chat';
$pluginmanager['PluginTypes'][] = 'plugin';
$pluginmanager['PluginTypes'][] = 'manager';

//You can't deactivate or activate the following plugins on the server. You must deactivate or activate these plugins in the plugins.xml file
$pluginmanager['ExpectedPlugins'][] = 'chat.admin.php';
$pluginmanager['ExpectedPlugins'][] = 'manager.plugins.php';
$pluginmanager['ExpectedPlugins'][] = 'plugin.newsupdate.php';
$pluginmanager['ExpectedPlugins'][] = 'class.foxcontrolplugin.php';

//END EDIT

class manager_plugins extends FoxControlPlugin {
	public function onStartUp() {
		$this->registerCommand('plugins', 'Opens the Plugin Manager.', true);
		$this->registerMLIds(16);
		$this->name = 'Plugin Manager';
		$this->author = 'Cyril';
		$this->version = '0.5';
	}
	public function onCommand($args) {
		if($args[2] == 'plugins') {
			global $pluginmanager;
			$window = $this->window;
			$pmPlugins = $this->getPlugins();
			$rights = $this->getRights($args[1]);
			$this->instance()->client->query('GetDetailedPlayerInfo', $args[1]);
			$CommandAuthor = $this->instance()->client->getResponse();
			if($rights[0] !== 3) {
				$window->init();
				$window->title('$800P$ffflugin $800M$fffanager');
				$window->size(42, '');
				$window->content('You don\'t have the required admin rights.');
				$window->textAlign('center');
				$window->addButton('Ok', 20, true);
				$window->close(false);
				$window->show($CommandAuthor['Login']);
				return;
			} else if($pluginmanager['Player']['login'] !== $CommandAuthor['Login'] && $pluginmanager['Player']['time'] + 120 > time()) {
				$window->init();
				$window->title('Plugin Manager');
				$window->size(42, '');
				$this->instance()->client->query('GetDetailedPlayerInfo', $pluginmanager['Player']['login']);
				$ePlayer = $this->instance()->client->getResponse();
				$window->content($ePlayer['NickName'].'$z$fff is already editing the plugin list.');
				$window->content('Please wait until '.$ePlayer['NickName'].'$z$fff finished editing.');
				$window->textAlign('center');
				$window->addButton('Ok', 20, true);
				$window->close(false);
				$window->show($CommandAuthor['Login']);
				return;
			} elseif($pluginmanager['Player']['login'] !== $CommandAuthor['Login'] && trim($pluginmanager['Player']['login']) !== '') {
				$window->init();
				$window->title('$800P$ffflugin $800M$fffanager');
				$window->size(42, '');
				$window->content('Automatically finished editing after an inactivity of 2 minutes!');
				$window->content($CommandAuthor['NickName'].'$z$fff is now editing the plugin list.');
				$window->textAlign('center');
				$window->addButton('Ok', 20, true);
				$window->close(false);
				$window->show($pluginmanager['Player']['login']);
			}
			$pluginmanager['Player']['login'] = $CommandAuthor['Login'];
			$pluginmanager['Player']['time'] = time();
			
			if(isset($pluginmanager['Players'][$CommandAuthor['Login']]) == false) {
				$pluginmanager['Players'][$CommandAuthor['Login']] = 0;
			}
			$pluginSite = $pluginmanager['Players'][$CommandAuthor['Login']] * 16;
			
			$window->init();
			$window->title('$800P$ffflugin $800M$fffanager');
			$window->size(67, '');
			$window->content('<td width="30">Plugin file</td><td width="20"></td><td width="15">Activate/Deactivate</td>');
			$window->content('');
			for($i = $pluginSite; $i < count($pmPlugins['names']) && $i <= 15+$pluginSite; $i++) {
				if(isset($pluginmanager['Changes'][$pmPlugins['names'][$i]]) == true) $activeWithoutChanges = $pluginmanager['Changes'][$pmPlugins['names'][$i]];
				else $activeWithoutChanges = '1';
				if($activeWithoutChanges == '1') $activeWithoutChanges = true;
				else $activeWithoutChanges = false;
				if(isset($pluginmanager['Changes'][$pmPlugins['names'][$i]]) == true) console($activeWithoutChanges.':'.$pluginmanager['Changes'][$pmPlugins['names'][$i]]);
				if(isset($pluginmanager['Changes'][$pmPlugins['names'][$i]]) == true && $activeWithoutChanges !== $this->isActive($pluginmanager['Changes'][$pmPlugins['names'][$i]], false))
				{
					if($pluginmanager['Changes'][$pmPlugins['names'][$i]] == true) $window->content('<td width="30">'.$pmPlugins['names'][$i].'</td><td width="20"></td><td width="15" id="'.($this->mlids[($i-$pluginSite)]).'" align="center">Deactivate</td>'); //$0f0Deactivate
					else $window->content('<td width="30">'.$pmPlugins['names'][$i].'</td><td width="20"></td><td width="15" id="'.($this->mlids[$i]).'" align="center">Activate</td>'); //$0f0Activate
				}
				else
				{
					if($pmPlugins['edit'][$pmPlugins['names'][$i]] == 'locked') $window->content('<td width="30">'.$pmPlugins['names'][$i].'</td><td width="20"></td><td width="15" align="center">$f00locked</td>');
					elseif($this->isActive($pmPlugins['names'][$i], true) == true) $window->content('<td width="30">'.$pmPlugins['names'][$i].'</td><td width="20"></td><td width="15" id="'.($this->mlids[($i-$pluginSite)]).'" align="center">Deactivate</td>');
					else $window->content('<td width="30">'.$pmPlugins['names'][$i].'</td><td width="20"></td><td width="15" id="'.($this->mlids[($i-$pluginSite)]).'" align="center">Activate</td>');
				}
			}
			if($pluginSite !== 0) $window->addButton('<< Prev', '', false);
			else $window->addButton('', '', false);
			$window->addButton('OK', '', false);
			if($pluginSite + 15 < count($pmPlugins['names'])-1) $window->addButton('Next >>', '', false);
			else $window->addButton('', '', false);
			$window->buttonsAutoWidth(true);
			$window->target('buttonPressed', $this);
			$window->displayAsTable(true);
			$window->close(false);
			$window->show($CommandAuthor['Login']);
		}
	}
	public function onManialinkPageAnswer($args) {
		if($args[2] >= $this->mlids[0] && $args[2] <= $this->mlids[15]) {
			global $pluginmanager;
			if($pluginmanager['Player']['login'] !== $args[1]) {
				$window = $this->window;
				$window->init();
				$window->title('$800P$ffflugin $800M$fffanager');
				$window->content('An error is occurred!');
				$window->content('Please write $o/plugins$o in the chat and try it again.');
				$window->textAlign('center');
				$window->close(false);
				$window->addButton('Ok', 20, true);
				$window->show($answer[1]);
				return;
			}
			$pluginId = $pluginmanager['Players'][$args[1]] * 16 + ($args[2] - $this->mlids[0]);
			$pmPlugins = $this->getPlugins(false);
			$selectedPlugin = $pmPlugins['names'][$pluginId];
			if($this->isActive($selectedPlugin, true) == true) $pluginmanager['Changes'][$selectedPlugin] = '0';
			else $pluginmanager['Changes'][$selectedPlugin] = '1';
			
			$data = array();
			$data[1] = $args[1];
			$data[2] = 'plugins';
			$this->onCommand($data);
		}
	}
	public function getPlugins() {
		global $pluginmanager;
		$files = array();
		$files['names'] = array();
		$files['edit'] = array();
		if($dir = opendir('./plugins/')) {
			while (false !== ($file = readdir($dir))) {
				if ($file != "." && $file != "..") {
					$addPlugin = false;
					$editable = false;
					for($i = 0; $i < count($pluginmanager['PluginTypes']); $i++) {
						if(strpos($file, $pluginmanager['PluginTypes'][$i]) !== false && strpos($file, '.php') !== false) {
							$addPlugin = true;
							break;
						}
					}
					if($addPlugin == true) {
						for($i = 0; $i < count($pluginmanager['ExpectedPlugins']); $i++) {
							if($file == $pluginmanager['ExpectedPlugins'][$i]) {
								$editable = 'locked';
								break;
							}
						}
						$files['names'][] = $file;
						$files['edit'][$file] = $editable;
					}
				}
			}
			closedir($dir);
		}
		else console('[PluginManager] Directoy \'plugins\' not found!');
		sort($files['names']);
		return $files;
	}
	public function isActive($pname, $withChanges) {
		global $fc_active_plugins, $pluginmanager;
		if($withChanges == true)
		{
			if(isset($pluginmanager['Changes'][$pname]))
			{
				if($pluginmanager['Changes'][$pname] == '1') return true;
				else return false;
			}
		}
		for($i = 0; $i < count($fc_active_plugins); $i++)
		{
			if($fc_active_plugins[$i] == $pname) 
			{
				return true;
			}
		}
		return false;
	}
	public function deactivatePlugin($pname) {
		$file = file('./plugins.xml');
		$found = false;
		for($i = 0; $i < count($file); $i++) {
			if(strpos($file[$i], '<plugin>'.$pname.'</plugin>') !== false) {
				unset($file[$i]);
				$found = true;
				break;
			}
		}
		if($found == true) {
			$fileString = '';
			for($i = 0; $i < count($file); $i++)
			{
				$fileString .= $file[$i];
			}
			$fileString .= '</plugins>';
			file_put_contents('./plugins.xml', $fileString);
		}
	}
	public function activatePlugin($pname) {
		$file = file('./plugins.xml');
		$endTag = 0;
		for($i = 0; $i < count($file); $i++) {
			if(strpos($file[$i], '</plugins>') !== false) {
				$endTag = $i;
				break;
			}
		}
		$lines = count($file);
		for($i = $endTag; $i < $lines + 1; $i++) {
			$file[$i+1] = $file[$i];
		}
		unset($file[count($file)-1]);
		unset($file[count($file)-1]);
		$file[$endTag] = '	<plugin>'.$pname.'</plugin>'."\n";
		$fileString = '';
		for($i = 0; $i < count($file); $i++) {
			$fileString .= $file[$i];
		}
		$fileString .= '</plugins>';
		file_put_contents('./plugins.xml', $fileString);
	}
	public function getChanges($makeChanges) {
		global $pluginmanager;
		$activates = 0;
		$deactivates = 0;
		$plugins = $this->getPlugins();
		for($i = 0; $i < count($plugins['names']); $i++)
		{
			$pluginIsActive = $this->isActive($plugins['names'][$i], false);
			if(isset($pluginmanager['Changes'][$plugins['names'][$i]]) == true) {
				if($pluginmanager['Changes'][$plugins['names'][$i]] == '1' && $pluginIsActive == false) {
					if($makeChanges == true) $this->activatePlugin($plugins['names'][$i]);
					$activates++;
				} else if($pluginmanager['Changes'][$plugins['names'][$i]] == '0' && $pluginIsActive == true) {
					if($makeChanges == true) $this->deactivatePlugin($plugins['names'][$i]);
					$deactivates++;
				}
			}
		}
		$return = array('a' => $activates, 'd' => $deactivates);
		return $return;
	}
	public function buttonPressed($button) {
		global $pluginmanager;
		$window = $this->window;
		if(trim($button[3]) == 'OK')
		{
			$changes = $this->getChanges(true);
			$activates = $changes['a'];
			$deactivates = $changes['d'];
			if($activates == 0 && $deactivates == 0) {
				$window->closeWindow($button[1]);
			} else {
				$pluginmanager['Player']['time'] = 0;
				$pluginmanager['Players'][$button[1]] = 0;
				$window->init();
				$window->title('$800P$ffflugin $800M$fffanager');
				$window->size(42, '');
				$window->content('You must restart FoxControl to see the changes.');
				$window->content('Would you like to restart FoxControl now?');
				$window->textAlign('center');
				$window->addButton('YES', 15, false);
				$window->addButton('', 5, false);
				$window->addButton('NO', 15, true);
				$window->target('buttonPressed', $this);
				$window->show($button[1]);
			}
		}
		elseif($button[3] == 'YES')
		{
			$changes = $this->getChanges('');
			$activates = $changes['a'];
			$deactivates = $changes['d'];
			if($activates == 0 && $deactivates == 0)
			{
				$this->chatToLogin($button[1], '$fff[$06fP$ffflugin$06fM$fffanager] Nothing has changed.');
				$window->closeWindow($button[1]);
				return;
			}
			if($activates == 1) $activateString = 'activate '.$activates.' plugin and ';
			elseif($activates > 1) $activateString = 'activate '.$activates.' plugins and ';
			else $activateString = '';
			if($deactivates == 1) $deactivateString = 'deactivate '.$deactivates.' plugin';
			elseif($deactivates > 1) $deactivateString = 'deactivate '.$deactivates.' plugins';
			else
			{
				$deactivateString = '';
				$activateString = str_replace('and ', '', $activateString);
			}
			$this->chat('$fff[$f51P$ffflugin$f51M$fffanager] Restarting $f51S$ffftunters$f51C$fffontrol after '.$activateString.$deactivateString.'..');
			$window->closeWindow($button[1]);
			sleep(2);
			$this->instance()->FoxControl_reboot();
		}
		elseif($button[3] == 'Next >>')
		{
			$pluginmanager['Players'][$button[1]] = $pluginmanager['Players'][$button[1]] + 1;
			$data = array();
			$data[1] = $button[1];
			$data[2] = 'plugins';
			$this->onCommand($data);
		}
		elseif($button[3] == '<< Prev')
		{
			$pluginmanager['Players'][$button[1]] = $pluginmanager['Players'][$button[1]] - 1;
			$data = array();
			$data[1] = $button[1];
			$data[2] = 'plugins';
			$this->onCommand($data);
		}
	}
}
?>
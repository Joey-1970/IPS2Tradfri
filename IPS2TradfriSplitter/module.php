<?
    // Klassendefinition
    class IPS2TradfriSplitter extends IPSModule 
    {
	   
	// Überschreibt die interne IPS_Create($id) Funktion
        public function Create() 
        {
            	// Diese Zeile nicht löschen.
            	parent::Create();
		$this->RegisterPropertyBoolean("Open", false);
		$this->RegisterPropertyString("GatewayIP", "Gateway IP");
		$this->RegisterPropertyString("SecurityID", "Security ID");
		
		$this->RegisterAttributeString("PresharedKey", "");
		$this->RegisterAttributeString("Identifier", "");
		$this->RegisterAttributeString("GatewayFirmware", "");
        }
 	
	public function GetConfigurationForm() 
	{ 
		$arrayStatus = array(); 
		$arrayStatus[] = array("code" => 101, "icon" => "inactive", "caption" => "Instanz wird erstellt"); 
		$arrayStatus[] = array("code" => 102, "icon" => "active", "caption" => "Instanz ist aktiv");
		$arrayStatus[] = array("code" => 104, "icon" => "inactive", "caption" => "Instanz ist inaktiv");
		$arrayStatus[] = array("code" => 202, "icon" => "error", "caption" => "Kommunikationfehler!");
		$arrayStatus[] = array("code" => 203, "icon" => "error", "caption" => "Fehlerhafter Key!");
				
		$arrayElements = array(); 
		$arrayElements[] = array("name" => "Open", "type" => "CheckBox",  "caption" => "Aktiv");
		$arrayElements[] = array("type" => "Label", "label" => "Tradfri-Gateway-Zugangsdaten");
		$arrayElements[] = array("type" => "ValidationTextBox", "name" => "GatewayIP", "caption" => "Gateway IP");
		$arrayElements[] = array("type" => "ValidationTextBox", "name" => "SecurityID", "caption" => "Security ID (auf der Unterseite des Gateway)");
		$GatewayFirmware = "Gateway Firmware: ".($this->ReadAttributeString("GatewayFirmware"));
		$arrayElements[] = array("type" => "Label", "label" => $GatewayFirmware);
		$arrayElements[] = array("type" => "Label", "label" => "_____________________________________________________________________________________________________");
		$arrayElements[] = array("type" => "Label", "label" => "Vom Modul erzeugte Zugangsdaten");
		$Identifier = "Schlüsselwort: ".($this->ReadAttributeString("Identifier"));
		$PresharedKey = "Schlüssel: ".($this->ReadAttributeString("PresharedKey"));
		$arrayElements[] = array("type" => "Label", "label" => $Identifier);
		$arrayElements[] = array("type" => "Label", "label" => $PresharedKey);
		$arrayActions = array();
		$arrayActions[] = array("type" => "Label", "label" => "Zur Erzeugung neuer Zugangsdaten: Selbst gewähltes Schlüsselwort eingeben, Button betätigen");
		$arrayActions[] = array("type" => "ValidationTextBox", "name" => "NewKeyWord", "caption" => "Neues Schlüsselwort");
		$arrayActions[] = array("type" => "Button", "name" => "Button", "caption" => "Zugsngsdaten erzeugen", "onClick" => 'IPS2TradfriSplitter_GetPresharedKey($id, $NewKeyWord);');
        	
 		return JSON_encode(array("status" => $arrayStatus, "elements" => $arrayElements, "actions" => $arrayActions)); 		 
 	}       
	   
        // Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() 
        {
            	// Diese Zeile nicht löschen
            	parent::ApplyChanges();
		
		If ($this->ReadPropertyBoolean("Open") == true) {
			$Key = $this->ReadAttributeString("PresharedKey");
			$Identifier = $this->ReadAttributeString("Identifier");
			$IP = $this->ReadPropertyString("GatewayIP");
			If ((strlen($Identifier) > 0) AND (strlen($Key) == 16) AND (filter_var($IP, FILTER_VALIDATE_IP))) {
				$this->GatewayInfo();
				$this->SetStatus(102);
			}
			else {
				Echo "Syntax der IP inkorrekt, Kein gültiger Key oder kein korrektes Schlüsselwort!";
				$this->SendDebug("ApplyChanges", "Syntax der IP inkorrekt, Kein gültiger Key oder kein korrektes Schlüsselwort!", 0);
				$this->SetStatus(203);
			}
			
		}
		else {
			$this->SetStatus(104);
			
		}	
	}
	
	public function ForwardData($JSONString) 
	{
	 	// Empfangene Daten von der Device Instanz
	    	$data = json_decode($JSONString);
	    	$Result = false;
	 	switch ($data->Function) {
			case "getDeviceList":
				$DeviceListArray = array();
				$DeviceListArray = $this->DeviceList();
				$Result = serialize($DeviceListArray);
				break;
			case "DeviceState":
				$DeviceStateArray = array();
				$DeviceStateArray = $this->DeviceState($data->DeviceID);
				$Result = serialize($DeviceStateArray);
				break;
			case "DeviceInfo":
				$DeviceInfoArray = array();
				$DeviceInfoArray = $this->DeviceInfo($data->DeviceID);
				$Result = serialize($DeviceInfoArray);
				break;
			case "BulbSwitch":
				$this->BulbSwitch($data->DeviceID, $data->State);
				break;
			case "BulbIntensity":
				$this->BulbIntensity($data->DeviceID, $data->Intensity);
				break;
			case "BulbAmbiente":
				$this->BulbAmbiente($data->DeviceID, $data->Value);
				break;
			case "BulbFadetime":
				$this->BulbFadetime($data->DeviceID, $data->Value);
				break;
		}
	return $Result;
	}
	    
	// Beginn der Funktionen
	private function BulbSwitch($DeviceID, $State)
	{
		$IP = $this->ReadPropertyString("GatewayIP");
		$Key = $this->ReadAttributeString("PresharedKey");
		$Identifier = $this->ReadAttributeString("Identifier");
		$State = intval($State);
		
		If (($this->ReadPropertyBoolean("Open") == true) AND (strlen($Identifier) > 0) AND (strlen($Key) == 16)) {
			$this->SendDebug("SwitchBulb", "Ausfuehrung: ".$DeviceID, 0);
			
			$Message = 'sudo coap-client -m put -u "'.$Identifier.'" -k "'.$Key.'" -e \'{ "3311": [{ "5850": '.$State.' }] }\' "coaps://'.$IP.':5684/15001/'.$DeviceID.'"'; 
			$Response = exec($Message." 2>&1", $Output);
		}
	}
	
	private function BulbIntensity($DeviceID, $Intensity)
	{
		$IP = $this->ReadPropertyString("GatewayIP");
		$Key = $this->ReadAttributeString("PresharedKey");
		$Identifier = $this->ReadAttributeString("Identifier");
		If (($this->ReadPropertyBoolean("Open") == true) AND (strlen($Identifier) > 0) AND (strlen($Key) == 16)) {
			$this->SendDebug("BulbIntensity", "Ausfuehrung: ".$DeviceID, 0);
			
			$Message = 'sudo coap-client -m put -u "'.$Identifier.'" -k "'.$Key.'" -e \'{ "3311": [{ "5851": '.$Intensity.' }] }\' "coaps://'.$IP.':5684/15001/'.$DeviceID.'"'; 
			$Response = exec($Message." 2>&1", $Output);
		}
	}    
	
	private function BulbAmbiente($DeviceID, $Value)
	{
		$IP = $this->ReadPropertyString("GatewayIP");
		$Key = $this->ReadAttributeString("PresharedKey");
		$Identifier = $this->ReadAttributeString("Identifier");
		If (($this->ReadPropertyBoolean("Open") == true) AND (strlen($Identifier) > 0) AND (strlen($Key) == 16)) {
			$this->SendDebug("BulbAmbiente", "Ausfuehrung: ".$DeviceID." - ".$Value, 0);
			
			$Message = 'sudo coap-client -m put -u "'.$Identifier.'" -k "'.$Key.'" -e \'{ "3311": [{ "5706": "'.$Value.'" }] }\' "coaps://'.$IP.':5684/15001/'.$DeviceID.'"'; 
			$Response = exec($Message." 2>&1", $Output);
			$this->SendDebug("BulbAmbiente", "Ergebnis: ".serialize($Output), 0);
		}
	}        
	
	private function BulbFadetime($DeviceID, $Value)
	{
		$IP = $this->ReadPropertyString("GatewayIP");
		$Key = $this->ReadAttributeString("PresharedKey");
		$Identifier = $this->ReadAttributeString("Identifier");
		If (($this->ReadPropertyBoolean("Open") == true) AND (strlen($Identifier) > 0) AND (strlen($Key) == 16)) {
			$this->SendDebug("BulbFadetime", "Ausfuehrung: ".$DeviceID, 0);
			
			$Message = 'sudo coap-client -m put -u "'.$Identifier.'" -k "'.$Key.'" -e \'{ "3311": [{ "5712": '.$Value.' }] }\' "coaps://'.$IP.':5684/15001/'.$DeviceID.'"'; 
			$Response = exec($Message." 2>&1", $Output);
		}
	}        
	
	private function DeviceState($DeviceID)
	{
		$IP = $this->ReadPropertyString("GatewayIP");
		$Key = $this->ReadAttributeString("PresharedKey");
		$Identifier = $this->ReadAttributeString("Identifier");
		$ResultArray = array();
		If (($this->ReadPropertyBoolean("Open") == true) AND (strlen($Identifier) > 0) AND (strlen($Key) == 16)) {
			$this->SendDebug("DeviceState", "Ausfuehrung: ".$DeviceID, 0);
			
			$Message = 'sudo coap-client -m get -u "'.$Identifier.'" -k "'.$Key.'" "coaps://'.$IP.':5684/15001/'.$DeviceID.'"';
			$Response = exec($Message." 2>&1", $Output);
			
			If (is_array($Output)) {
				If (isset($Output[3])) {
					$data = json_decode($Output[3]);
					If (isset($data->{'9019'})) {
						$ResultArray[9019] = $data->{'9019'};
					}
					If (isset($data->{'3311'})) {
						$StateArray = $data->{'3311'}{'0'};
						foreach ($StateArray as $Key => $State) {
							$ResultArray[$Key] = $State;
						}
					}
					elseif (isset($data->{'3300'})) {
						$StateArray = $data->{'3300'}{'0'};
						foreach ($StateArray as $Key => $State) {
							$ResultArray[$Key] = $State;
						}
					}
					elseif (isset($data->{'3312'})) {
						$StateArray = $data->{'3312'}{'0'};
						foreach ($StateArray as $Key => $State) {
							$ResultArray[$Key] = $State;
						}
					}
					elseif (isset($data->{'15015'})) {
						$StateArray = $data->{'15015'}{'0'};
						foreach ($StateArray as $Key => $State) {
							$ResultArray[$Key] = $State;
						}
					}
				}
			}
    		}
	return $ResultArray;
	} 
	    
	private function DeviceList()
	{
		$this->SendDebug("DeviceList", "Ausfuehrung", 0);
		$IP = $this->ReadPropertyString("GatewayIP");
		$Key = $this->ReadAttributeString("PresharedKey");
		$Identifier = $this->ReadAttributeString("Identifier");
		$DeviceInfoArray = array();
		If (($this->ReadPropertyBoolean("Open") == true) AND (strlen($Identifier) > 0) AND (strlen($Key) == 16)) {
			$this->SendDebug("DeviceList", "Ausfuehrung", 0);
			$Message = 'sudo coap-client -m get -u "'.$Identifier.'" -k "'.$Key.'" "coaps://'.$IP.':5684/15001"';
			$Response = exec($Message." 2>&1", $Output);
			$DeviceArray = array();
			If (is_array($Output)) {
				If (isset($Output[3])) {
					$Search = array("[", "]");
					$Devices = str_replace($Search, "", $Output[3]);
					$DeviceArray = explode(",", $Devices);
					foreach ($DeviceArray as $DeviceID) {
						$DeviceInfoArray[$DeviceID] = $this->DeviceInfo($DeviceID);
					}
				}
			}
		}
	return $DeviceInfoArray;
	}

	private function DeviceInfo($DeviceID)
	{
		$IP = $this->ReadPropertyString("GatewayIP");
		$Key = $this->ReadAttributeString("PresharedKey");
		$Identifier = $this->ReadAttributeString("Identifier");
		$DeviceInfo = array();
		If (($this->ReadPropertyBoolean("Open") == true) AND (strlen($Identifier) > 0) AND (strlen($Key) == 16)) {
			$this->SendDebug("DeviceInfo", "Ausfuehrung: ".$DeviceID, 0);
			$Message = 'sudo coap-client -m get -u "'.$Identifier.'" -k "'.$Key.'" "coaps://'.$IP.':5684/15001/'.$DeviceID.'"';
			$Response = exec($Message." 2>&1", $Output);
			
			If (is_array($Output)) {
				If (isset($Output[3])) {
					$data = json_decode($Output[3]);
					$DeviceInfo["Name"] = $data->{'9001'};
					$DeviceInfo["Typ"] = $data->{'3'}->{'1'};
					$DeviceInfo["Firmware"] = $data->{'3'}->{'3'};
					If (isset($data->{'3311'})) {
						$DeviceInfo["Class"] = "Bulb";
						$DeviceInfo["Specification"] = $this->BulbDeviceType($data->{'3'}->{'1'});
					}
					elseif (isset($data->{'3300'})) {
						$DeviceInfo["Class"] = "MotionSensor";
					}
					elseif (isset($data->{'3312'})) {
						$DeviceInfo["Class"] = "Plug";
					}
					elseif (isset($data->{'15009'})) {
						$DeviceInfo["Class"] = "Remote";
					}
					elseif (isset($data->{'15015'})) {
						$DeviceInfo["Class"] = "Blind";
					}
					else {
						$DeviceInfo["Class"] = "Unknown";
					}
				}
			}
		}
	return $DeviceInfo;
	}
	
	private function BulbDeviceType(string $DeviceTypeText) 
	{
    		$Result = 0;
		If ((strpos($DeviceTypeText, " bulb ")) AND (strpos($DeviceTypeText, " W "))) {
        		$Result = 1; // GU 10 Dimmbar
    		}
    		elseif ((strpos($DeviceTypeText, " bulb ")) AND (strpos($DeviceTypeText, " WS "))) {
        		$Result = 2; // Weißtöne
    		}
    		elseif ((strpos($DeviceTypeText, " bulb ")) AND (strpos($DeviceTypeText, " CWS "))) {
        		$Result = 3; // Farbe
    		}
    		elseif (strpos($DeviceTypeText, " transformer ")) {
        		$Result = 4; // Transformator
    		}
    		else {
        		$Result = 0; // unbekannter Typ
    		}
	return $Result;
	}    
	    
	private function GatewayInfo()
	{
    		$IP = $this->ReadPropertyString("GatewayIP");
		$Key = $this->ReadAttributeString("PresharedKey");
		$Identifier = $this->ReadAttributeString("Identifier");
		$GatewayInfoArray = array();
		If (($this->ReadPropertyBoolean("Open") == true) AND (strlen($Identifier) > 0) AND (strlen($Key) == 16)) {
			$this->SendDebug("GatewayInfo", "Ausfuehrung", 0);
			$Message = 'sudo coap-client -m get -u "'.$Identifier.'" -k "'.$Key.'" "coaps://'.$IP.':5684/15011/15012"';
			$Response = exec($Message." 2>&1", $Output);
			
			If (is_array($Output)) {
				If (isset($Output[3])) {
					$data = json_decode($Output[3]);
					$GatewayInfoArray["Firmware"] = $data->{'9029'};
					$this->WriteAttributeString("GatewayFirmware", $data->{'9029'});
				}
			}
		}
	return $GatewayInfoArray;
	}      
	    
	private function TestPresharedKey()
	{
		If ($this->ReadPropertyBoolean("Open") == true) {
			$this->SendDebug("TestPresharedKey", "Ausfuehrung", 0);
			$IP = $this->ReadPropertyString("GatewayIP");
			$Key = $this->ReadAttributeString("PresharedKey");
			$Identifier = $this->ReadAttributeString("Identifier");
			$Message = 'sudo coap-client -m get -u "'.$Identifier.'" -k "'.$Key.'" "coaps:///'.$IP.':5684/15001" -v 9 -B 10';
			$Response = exec($Message." 2>&1", $Output);
			$this->SendDebug("TestPresharedKey", "Ergebnis: ".$Output, 0);
		}
	} 
	    
	public function GetPresharedKey(string $Identifier)
	{
		$Result = false;
		If (strlen($Identifier) == 0) {
			$this->SendDebug("GetPresharedKey", "Das Schlüsselwort darf nicht leer sein!", 0);
			Echo "Das Schlüsselwort darf nicht leer sein!";
			return $Result;
		}
		If ($this->ReadPropertyBoolean("Open") == true) {
			$this->SendDebug("GetPresharedKey", "Ausfuehrung mit Schluesselwort: ".$Identifier, 0);
			$IP = $this->ReadPropertyString("GatewayIP");
			$SecurityID = $this->ReadPropertyString("SecurityID");
			$Message = 'sudo coap-client -m post -u "Client_identity" -k "'.$SecurityID.'" -e \'{"9090":"'.$Identifier.'"}\' "coaps://"'.$IP.'":5684/15011/9063"';
			$Response = exec($Message." 2>&1", $Output);
			If (is_array($Output)) {
				If (isset($Output[3])) {
					$data = json_decode($Output[3]);
        				If (isset($data)) {
            					// Key wurde generiert
						$this->SendDebug("GetPresharedKey", "Key wurde erfolgreich generiert", 0);
            					$Result = $data->{'9091'};
						$this->WriteAttributeString("PresharedKey", $data->{'9091'});
						$this->WriteAttributeString("Identifier", $Identifier);
						$this->ReloadForm();
        				}
        				else {
            					// Key konnte nicht generiert werden
						$this->SendDebug("GetPresharedKey", "Key konnte nicht generiert werden!", 0);
						Echo "Key konnte nicht generiert werden!\nMöglicherweise ist das Schlüsselwort schon einmal verwendet worden?";
            					$Result = false;
        				}
    				}
			}
		}
	return $Result;
	}     
	
	 
}
?>

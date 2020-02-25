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
		
		$this->RegisterPropertyString("PresharedKey", "Preshared Key");
		$this->RegisterPropertyString("Identifier", "ip-symcon");
		
        }
 	
	public function GetConfigurationForm() 
	{ 
		$arrayStatus = array(); 
		$arrayStatus[] = array("code" => 101, "icon" => "inactive", "caption" => "Instanz wird erstellt"); 
		$arrayStatus[] = array("code" => 102, "icon" => "active", "caption" => "Instanz ist aktiv");
		$arrayStatus[] = array("code" => 104, "icon" => "inactive", "caption" => "Instanz ist inaktiv");
		$arrayStatus[] = array("code" => 202, "icon" => "error", "caption" => "Kommunikationfehler!");
				
		$arrayElements = array(); 
		$arrayElements[] = array("name" => "Open", "type" => "CheckBox",  "caption" => "Aktiv");
		$arrayElements[] = array("type" => "Label", "label" => "Tradfri-Gateway-Zugriffsdaten");
		$arrayElements[] = array("type" => "ValidationTextBox", "name" => "GatewayIP", "caption" => "Gateway IP");
		$arrayElements[] = array("type" => "ValidationTextBox", "name" => "SecurityID", "caption" => "Security ID");
		$Identifier = "Schlüsselwort: ".($this->ReadAttributeString("Identifier"));
		$PresharedKey = "Schlüssel: ".($this->ReadAttributeString("PresharedKey"));
		$arrayElements[] = array("type" => "Label", "label" => $Identifier);
		$arrayElements[] = array("type" => "Label", "label" => $PresharedKey);
		//$arrayElements[] = array("type" => "ValidationTextBox", "name" => "PresharedKey", "caption" => "Preshared Key");
		$arrayElements[] = array("type" => "Label", "label" => "_____________________________________________________________________________________________________");
		$arrayActions = array();
		$arrayActions[] = array("type" => "Label", "label" => "Zur Erzeugung eines neuen Schlüssel, Schlüsselwort eingeben");
		$arrayActions[] = array("type" => "ValidationTextBox", "name" => "NewKeyWord", "caption" => "Neues Schlüsselwort");
		$arrayActions[] = array("type" => "Button", "name" => "Button", "caption" => "Schlüssel erzeugen", "onClick" => "IPS2TradfriSplitter_GetPresharedKey($id, $NewKeyWord);");
        	
 		return JSON_encode(array("status" => $arrayStatus, "elements" => $arrayElements, "actions" => $arrayActions)); 		 
 	}       
	   
        // Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() 
        {
            	// Diese Zeile nicht löschen
            	parent::ApplyChanges();
		
		If ($this->ReadPropertyBoolean("Open") == true) {
			$this->SetStatus(102);
			
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
		If ($this->ReadPropertyBoolean("Open") == true) {
			$this->SendDebug("SwitchBulb", "Ausfuehrung: ".$DeviceID, 0);
			$IP = $this->ReadPropertyString("GatewayIP");
			$Key = $this->ReadPropertyString("PresharedKey");
			$Identifier = $this->ReadPropertyString("Identifier");
			$State = intval($State);
			$Message = 'sudo coap-client -m put -u "'.$Identifier.'" -k "'.$Key.'" -e \'{ "3311": [{ "5850": '.$State.' }] }\' "coaps://'.$IP.':5684/15001/'.$DeviceID.'"'; 
			$Response = exec($Message." 2>&1", $Output);
		}
	}
	
	private function BulbIntensity($DeviceID, $Intensity)
	{
		If ($this->ReadPropertyBoolean("Open") == true) {
			$this->SendDebug("BulbIntensity", "Ausfuehrung: ".$DeviceID, 0);
			$IP = $this->ReadPropertyString("GatewayIP");
			$Key = $this->ReadPropertyString("PresharedKey");
			$Identifier = $this->ReadPropertyString("Identifier");
			$Message = 'sudo coap-client -m put -u "'.$Identifier.'" -k "'.$Key.'" -e \'{ "3311": [{ "5851": '.$Intensity.' }] }\' "coaps://'.$IP.':5684/15001/'.$DeviceID.'"'; 
			$Response = exec($Message." 2>&1", $Output);
		}
	}    
	
	private function BulbAmbiente($DeviceID, $Value)
	{
		If ($this->ReadPropertyBoolean("Open") == true) {
			$this->SendDebug("BulbAmbiente", "Ausfuehrung: ".$DeviceID, 0);
			$IP = $this->ReadPropertyString("GatewayIP");
			$Key = $this->ReadPropertyString("PresharedKey");
			$Identifier = $this->ReadPropertyString("Identifier");
			$AmmbienteArray = array(0 => "f1e0b5", 1 => "f5faf6", 2 => "efd275");
			$Message = 'sudo coap-client -m put -u "'.$Identifier.'" -k "'.$Key.'" -e \'{ "3311": [{ "5706": "'.$AmmbienteArray[$Value].'" }] }\' "coaps://'.$IP.':5684/15001/'.$DeviceID.'"'; 
			$Response = exec($Message." 2>&1", $Output);
			$this->SendDebug("BulbAmbiente", "Ergebnis: ".serialize($Output), 0);
		}
	}        
	
	private function BulbFadetime($DeviceID, $Value)
	{
		If ($this->ReadPropertyBoolean("Open") == true) {
			$this->SendDebug("BulbFadetime", "Ausfuehrung: ".$DeviceID, 0);
			$IP = $this->ReadPropertyString("GatewayIP");
			$Key = $this->ReadPropertyString("PresharedKey");
			$Identifier = $this->ReadPropertyString("Identifier");
			$Message = 'sudo coap-client -m put -u "'.$Identifier.'" -k "'.$Key.'" -e \'{ "3311": [{ "5712": '.$Value.' }] }\' "coaps://'.$IP.':5684/15001/'.$DeviceID.'"'; 
			$Response = exec($Message." 2>&1", $Output);
		}
	}        
	
	private function DeviceState($DeviceID)
	{
		$this->SendDebug("DeviceState", "Ausfuehrung: ".$DeviceID, 0);
		$IP = $this->ReadPropertyString("GatewayIP");
		$Key = $this->ReadPropertyString("PresharedKey");
		$Identifier = $this->ReadPropertyString("Identifier");
		
    		$Message = 'sudo coap-client -m get -u "'.$Identifier.'" -k "'.$Key.'" "coaps://'.$IP.':5684/15001/'.$DeviceID.'"';
    		$Response = exec($Message." 2>&1", $Output);
    		$ResultArray = array();
    		If (is_array($Output)) {
        		If (isset($Output[3])) {
            			$data = json_decode($Output[3]);
            
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
	return $ResultArray;
	} 
	    
	private function DeviceList()
	{
		$this->SendDebug("DeviceList", "Ausfuehrung", 0);
		$IP = $this->ReadPropertyString("GatewayIP");
		$Key = $this->ReadPropertyString("PresharedKey");
		$Identifier = $this->ReadPropertyString("Identifier");
		
		$Message = 'sudo coap-client -m get -u "'.$Identifier.'" -k "'.$Key.'" "coaps://'.$IP.':5684/15001"';
		$Response = exec($Message." 2>&1", $Output);
		$DeviceArray = array();
		If (is_array($Output)) {
			If (isset($Output[3])) {
				$Search = array("[", "]");
				$Devices = str_replace($Search, "", $Output[3]);
				$DeviceArray = explode(",", $Devices);
				$DeviceInfoArray = array();
				foreach ($DeviceArray as $DeviceID) {
					$DeviceInfoArray[$DeviceID] = $this->DeviceInfo($IP, $Key, $Identifier, $DeviceID);
				}
			}
		}
	return $DeviceInfoArray;
	}

	private function DeviceInfo($IP, $Key, $Identifier, $DeviceID)
	{
		$Message = 'sudo coap-client -m get -u "'.$Identifier.'" -k "'.$Key.'" "coaps://'.$IP.':5684/15001/'.$DeviceID.'"';
		$Response = exec($Message." 2>&1", $Output);
		$DeviceInfo = array();
		If (is_array($Output)) {
			If (isset($Output[3])) {
				$data = json_decode($Output[3]);
				$DeviceInfo["Name"] = $data->{'9001'};
				$DeviceInfo["Typ"] = $data->{'3'}->{'1'};
				$DeviceInfo["Firmware"] = $data->{'3'}->{'3'};
				If (isset($data->{'3311'})) {
					$DeviceInfo["Class"] = "Bulb";
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
	return $DeviceInfo;
	}
	
	private function TestPresharedKey()
	{
		If ($this->ReadPropertyBoolean("Open") == true) {
			$this->SendDebug("TestPresharedKey", "Ausfuehrung", 0);
			$IP = $this->ReadPropertyString("GatewayIP");
			$Key = $this->ReadPropertyString("PresharedKey");
			$Identifier = $this->ReadPropertyString("Identifier");
			$Message = 'sudo coap-client -m get -u "'.$Identifier.'" -k "'.$Key.'" "coaps:///'.$IP.':5684/15001" -v 9 -B 10';
			$Response = exec($Message." 2>&1", $Output);
			$this->SendDebug("TestPresharedKey", "Ergebnis: ".$Output, 0);
		}
	} 
	    
	public function GetPresharedKey(string $Identifier)
	{
		$Result = false;
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
            					$Result = false;
        				}
    				}
			}
		}
	return $Result;
	}     

}
?>

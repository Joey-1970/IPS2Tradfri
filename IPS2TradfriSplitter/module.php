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
		$this->RegisterPropertyString("PresharedKey", "Preshared Key");
		
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
		$arrayElements[] = array("type" => "ValidationTextBox", "name" => "PresharedKey", "caption" => "Preshared Key");
		
 		return JSON_encode(array("status" => $arrayStatus, "elements" => $arrayElements)); 		 
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
			case "SwitchBulb":
				$this->SwitchBulb($data->DeviceID, $data->State);
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
	private function SwitchBulb($DeviceID, $State)
	{
		$this->SendDebug("SwitchBulb", "Ausfuehrung: ".$DeviceID, 0);
		$IP = $this->ReadPropertyString("GatewayIP");
		$Key = $this->ReadPropertyString("PresharedKey");
		$Identifier = "ip-symcon";
		$State = intval($State);
		$Message = 'sudo coap-client -m put -u "'.$Identifier.'" -k "'.$Key.'" -e \'{ "3311": [{ "5850": '.$State.' }] }\' "coaps://'.$IP.':5684/15001/'.$DeviceID.'"'; 
		$Response = exec($Message." 2>&1", $Output);
	}
	
	private function BulbIntensity($DeviceID, $Intensity)
	{
		$this->SendDebug("BulbIntensity", "Ausfuehrung: ".$DeviceID, 0);
		$IP = $this->ReadPropertyString("GatewayIP");
		$Key = $this->ReadPropertyString("PresharedKey");
		$Identifier = "ip-symcon";
		$Message = 'sudo coap-client -m put -u "'.$Identifier.'" -k "'.$Key.'" -e \'{ "3311": [{ "5851": '.$Intensity.' }] }\' "coaps://'.$IP.':5684/15001/'.$DeviceID.'"'; 
		$Response = exec($Message." 2>&1", $Output);
	}    
	
	private function BulbAmbiente($DeviceID, $Value)
	{
		$this->SendDebug("BulbAmbiente", "Ausfuehrung: ".$DeviceID, 0);
		$IP = $this->ReadPropertyString("GatewayIP");
		$Key = $this->ReadPropertyString("PresharedKey");
		$Identifier = "ip-symcon";
		$AmmbienteArray = array(0 => "f1e0b5", 1 => "f5faf6", 2 => "efd275");
		$Message = 'sudo coap-client -m put -u "'.$Identifier.'" -k "'.$Key.'" -e \'{ "3311": [{ "5706": "'.$AmmbienteArray[$Value].'" }] }\' "coaps://'.$IP.':5684/15001/'.$DeviceID.'"'; 
		$Response = exec($Message." 2>&1", $Output);
		$this->SendDebug("BulbAmbiente", "Ergebnis: ".serialize($Output), 0);
	}        
	
	private function BulbFadetime($DeviceID, $Value)
	{
		$this->SendDebug("BulbFadetime", "Ausfuehrung: ".$DeviceID, 0);
		$IP = $this->ReadPropertyString("GatewayIP");
		$Key = $this->ReadPropertyString("PresharedKey");
		$Identifier = "ip-symcon";
		$Message = 'sudo coap-client -m put -u "'.$Identifier.'" -k "'.$Key.'" -e \'{ "3311": [{ "5712": '.$Value.' }] }\' "coaps://'.$IP.':5684/15001/'.$DeviceID.'"'; 
		$Response = exec($Message." 2>&1", $Output);
	}        
	private function DeviceList()
	{
		$this->SendDebug("DeviceList", "Ausfuehrung", 0);
		$IP = $this->ReadPropertyString("GatewayIP");
		$Key = $this->ReadPropertyString("PresharedKey");
		$Identifier = "ip-symcon";
		
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
	    
}
?>

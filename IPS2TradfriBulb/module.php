<?
    // Klassendefinition
    class IPS2TradfriBulb extends IPSModule 
    {
	public function Destroy() 
	{
		//Never delete this line!
		parent::Destroy();
		$this->SetTimerInterval("Timer_1", 0);
	}  
	    
	// Überschreibt die interne IPS_Create($id) Funktion
        public function Create() 
        {
            	// Diese Zeile nicht löschen.
            	parent::Create();
		$this->ConnectParent("{562389F8-739F-644A-4FC7-36F2CE3AFE4F}");
		$this->RegisterPropertyBoolean("Open", false);
		$this->RegisterPropertyInteger("DeviceID", 0);
		$this->RegisterTimer("Timer_1", 0, 'IPS2TradfriBulb_GetState($_IPS["TARGET"]);');
		
		$this->RegisterAttributeString("Name", "");
		$this->RegisterAttributeString("Typ", "");
		$this->RegisterAttributeString("Firmware", "");
		
		$this->RegisterProfileInteger("Tradfri.Ambiente", "Bulb", "", "", 0, 3, 0);
		IPS_SetVariableProfileAssociation("Tradfri.Ambiente", 0, "Alltag", "Bulb", 0xf1e0b5);
		IPS_SetVariableProfileAssociation("Tradfri.Ambiente", 1, "Fokus", "Bulb", 0xf5faf6);
		IPS_SetVariableProfileAssociation("Tradfri.Ambiente", 2, "Entspannung", "Bulb", 0xefd275);
		
		$this->RegisterProfileInteger("Tradfri.Color", "Bulb", "", "", 0, 20, 0);
		IPS_SetVariableProfileAssociation("Tradfri.Color", 0, "Blau", "Bulb", 0x4a418a); 
		IPS_SetVariableProfileAssociation("Tradfri.Color", 1, "Helles Blau", "Bulb", 0x6c83ba); 
		IPS_SetVariableProfileAssociation("Tradfri.Color", 2, "Gesättigtes Lila", "Bulb", 0x8f2686); 
		IPS_SetVariableProfileAssociation("Tradfri.Color", 3, "Limette", "Bulb", 0xa9d62b); 
		IPS_SetVariableProfileAssociation("Tradfri.Color", 4, "Helles Lila", "Bulb", 0xc984bb); 
		IPS_SetVariableProfileAssociation("Tradfri.Color", 5, "Gelb", "Bulb", 0xd6e44b); 
		IPS_SetVariableProfileAssociation("Tradfri.Color", 6, "Gesättigtes Rosa", "Bulb", 0xd9337c); 
		IPS_SetVariableProfileAssociation("Tradfri.Color", 7, "Dunkler Pfirsich", "Bulb", 0xda5d41); 
		IPS_SetVariableProfileAssociation("Tradfri.Color", 8, "Gesättigtes Rot", "Bulb", 0xdc4b31); 
		IPS_SetVariableProfileAssociation("Tradfri.Color", 9, "Kalter Himmel", "Bulb", 0xdcf0f8); 
		IPS_SetVariableProfileAssociation("Tradfri.Color", 10, "Rosa", "Bulb", 0xe491af); 
		IPS_SetVariableProfileAssociation("Tradfri.Color", 11, "Pfirsich", "Bulb", 0xe57345); 
		IPS_SetVariableProfileAssociation("Tradfri.Color", 12, "Warmer Bernstein", "Bulb", 0xe78834); 
		IPS_SetVariableProfileAssociation("Tradfri.Color", 13, "Helles Rosa", "Bulb", 0xe8bedd); 
		IPS_SetVariableProfileAssociation("Tradfri.Color", 14, "Kaltes Tageslicht", "Bulb", 0xeaf6fb); 
		IPS_SetVariableProfileAssociation("Tradfri.Color", 15, "Kerzenlicht", "Bulb", 0xebb63e); 
		IPS_SetVariableProfileAssociation("Tradfri.Color", 16, "Entspannung", "Bulb", 0xefd275); 
		IPS_SetVariableProfileAssociation("Tradfri.Color", 17, "Alltag", "Bulb", 0xf1e0b5); 
		IPS_SetVariableProfileAssociation("Tradfri.Color", 18, "Sonnenaufgang", "Bulb", 0xf2eccf); 
		IPS_SetVariableProfileAssociation("Tradfri.Color", 19, "Fokus", "Bulb", 0xf5faf6); 
		
		// Status-Variablen anlegen
		$this->RegisterVariableBoolean("State", "Status", "~Switch", 10);
	        $this->EnableAction("State");
		
	        $this->RegisterVariableInteger("Intensity", "Intensity", "~Intensity.255", 20);
	        $this->EnableAction("Intensity");
		
		$this->RegisterVariableInteger("Ambiente", "Ambiente", "Tradfri.Ambiente", 30);
	        $this->EnableAction("Ambiente");
		
		$this->RegisterVariableInteger("Color", "Farbe", "Tradfri.Color", 40);
	        $this->EnableAction("Color");
		
		//$this->RegisterVariableInteger("RGB", "Farbe", "~HexColor", 50);
           	//$this->EnableAction("RGB");
		
		$this->RegisterVariableBoolean("Available", "Verfügbar", "~Alert.Reversed", 60);
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
		$arrayElements[] = array("type" => "ValidationTextBox", "name" => "DeviceID", "caption" => "Device ID");
		
		$arrayElements[] = array("type" => "Label", "label" => "Name: ".$this->ReadAttributeString("Name")); 
		$arrayElements[] = array("type" => "Label", "label" => "Typ: ".$this->ReadAttributeString("Typ")); 
		$arrayElements[] = array("type" => "Label", "label" => "Firmware: ".$this->ReadAttributeString("Firmware")); 
		$arrayElements[] = array("type" => "Label", "label" => "_____________________________________________________________________________________________________");
		$arrayElements[] = array("type" => "Label", "label" => "Test Center"); 
		$arrayElements[] = array("type" => "TestCenter", "name" => "TestCenter");
		
 		return JSON_encode(array("status" => $arrayStatus, "elements" => $arrayElements)); 		 
 	}       
	   
        // Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() 
        {
            	// Diese Zeile nicht löschen
            	parent::ApplyChanges();
		
		If ($this->ReadPropertyBoolean("Open") == true) {
			$this->SetStatus(102);
			$this->GetDeviceInfo();
			$this->GetState();
			$this->SetTimerInterval("Timer_1", 1000);
		}
		else {
			$this->SetStatus(104);
			$this->SetTimerInterval("Timer_1", 0);
		}	
	}
	
	public function RequestAction($Ident, $Value) 
	{
  		switch($Ident) {
	        case "State":
	            	$Result = $this->SendDataToParent(json_encode(Array("DataID"=> "{4AA318CB-CA9A-2467-3079-A35AD1577771}", 
				"Function" => "BulbSwitch", "DeviceID" => $this->ReadPropertyInteger("DeviceID"), "State" => $Value )));
	            	SetValueBoolean($this->GetIDForIdent($Ident), $Value);
			$this->GetState();
		break;
	        case "Intensity":
	            	$Value = min(254, max(1, $Value));
			$Result = $this->SendDataToParent(json_encode(Array("DataID"=> "{4AA318CB-CA9A-2467-3079-A35AD1577771}", 
				"Function" => "BulbIntensity", "DeviceID" => $this->ReadPropertyInteger("DeviceID"), "Intensity" => $Value )));
	            	SetValueInteger($this->GetIDForIdent($Ident), $Value);
			$this->GetState();
	            break;
		case "Ambiente":
			$AmmbienteArray = array(0 => "f1e0b5", 1 => "f5faf6", 2 => "efd275");
	            	$Result = $this->SendDataToParent(json_encode(Array("DataID"=> "{4AA318CB-CA9A-2467-3079-A35AD1577771}", 
				"Function" => "BulbAmbiente", "DeviceID" => $this->ReadPropertyInteger("DeviceID"), "Value" => $AmmbienteArray[$Value] )));
	            	SetValueInteger($this->GetIDForIdent($Ident), $Value);
			$this->GetState();
		break;
		case "Color":
	            	$ColorArray = array(0 => "4a418a", 1 => "6c83ba", 2 => "8f2686", 3 => "a9d62b", 4 => "c984bb", 
					    5 => "d6e44b", 6 => "d9337c", 7 => "da5d41", 8 => "dc4b31", 9 => "dcf0f8", 
					    10 => "e491af", 11 => "e57345", 12 => "e78834", 13 => "e8bedd", 14 => "eaf6fb", 
					    15 => "ebb63e", 16 => "efd275", 17 => "f1e0b5", 18 => "f2eccf", 19 => "f5faf6");
			$Result = $this->SendDataToParent(json_encode(Array("DataID"=> "{4AA318CB-CA9A-2467-3079-A35AD1577771}", 
				"Function" => "BulbAmbiente", "DeviceID" => $this->ReadPropertyInteger("DeviceID"), "Value" => $ColorArray[$Value] )));
	            	SetValueInteger($this->GetIDForIdent($Ident), $Value);
			$this->GetState();
		break;
	        default:
	            throw new Exception("Invalid Ident");
	    }
	}
	    
	// Beginn der Funktionen
	public function GetState()
	{
		If ($this->ReadPropertyBoolean("Open") == true) {
			$Result = $this->SendDataToParent(json_encode(Array("DataID"=> "{4AA318CB-CA9A-2467-3079-A35AD1577771}", 
					"Function" => "DeviceState", "DeviceID" => $this->ReadPropertyInteger("DeviceID") )));
			$this->SendDebug("GetState", "Ergebnis: ".$Result, 0);
			$DeviceStateArray = array();
			$DeviceStateArray = unserialize($Result);
			
			If (GetValueBoolean($this->GetIDForIdent("Available")) <> $DeviceStateArray[9019]) {
				SetValueBoolean($this->GetIDForIdent("Available"), $DeviceStateArray[9019]);
			}	

			If (GetValueBoolean($this->GetIDForIdent("State")) <> $DeviceStateArray[5850]) {
				SetValueBoolean($this->GetIDForIdent("State"), $DeviceStateArray[5850]);
			}
			If (GetValueInteger($this->GetIDForIdent("Intensity")) <> $DeviceStateArray[5851]) {
				SetValueInteger($this->GetIDForIdent("Intensity"), $DeviceStateArray[5851]);
			}
			
			If (isset($DeviceStateArray[5706])) {
				$ColorValue = $DeviceStateArray[5706];
				$AmmbienteArray = array("f1e0b5" => 0, "f5faf6" => 1, "efd275" => 2);
				if (array_key_exists($ColorValue, $AmmbienteArray)) {
					If (GetValueInteger($this->GetIDForIdent("Ambiente")) <> $AmmbienteArray[$ColorValue]) {
						SetValueInteger($this->GetIDForIdent("Ambiente"), $AmmbienteArray[$ColorValue]);
					}
				}

				$ColorArray = array("4a418a" => 0, "6c83ba" => 1, "8f2686" => 2, "a9d62b" => 3, "c984bb" => 4, "d6e44b" => 5, 
							"d9337c" => 6, "da5d41" => 7, "dc4b31" => 8, "dcf0f8" => 9, "e491af" => 10, "e57345" => 11, 
							"e78834" => 12, "e8bedd" => 13, "eaf6fb" => 14, "ebb63e" => 15, "efd275" => 16, 
							"f1e0b5" => 17, "f2eccf" => 18, "f5faf6" => 19);
				if (array_key_exists($ColorValue, $ColorArray)) {
					If (GetValueInteger($this->GetIDForIdent("Color")) <> $ColorArray[$ColorValue]) {
						SetValueInteger($this->GetIDForIdent("Color"), $ColorArray[$ColorValue]);
					}
				}
			}
		}
	}
	
	private function GetDeviceInfo()
	{
		If ($this->ReadPropertyBoolean("Open") == true) {
			$Result = $this->SendDataToParent(json_encode(Array("DataID"=> "{4AA318CB-CA9A-2467-3079-A35AD1577771}", 
					"Function" => "DeviceInfo", "DeviceID" => $this->ReadPropertyInteger("DeviceID") )));
			$this->SendDebug("GetDeviceInfo", "Ergebnis: ".$Result, 0);
			$DeviceInfo = array();
			$DeviceInfo = unserialize($Result);
			$this->WriteAttributeString("Name", $DeviceInfo["Name"]);
			$this->WriteAttributeString("Typ", $DeviceInfo["Typ"]);
			$this->WriteAttributeString("Firmware", $DeviceInfo["Firmware"]);
			//$this->ReloadForm();
		}
	}    
	    
	private function RegisterProfileInteger($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize)
	{
	        if (!IPS_VariableProfileExists($Name))
	        {
	            IPS_CreateVariableProfile($Name, 1);
	        }
	        else
	        {
	            $profile = IPS_GetVariableProfile($Name);
	            if ($profile['ProfileType'] != 1)
	                throw new Exception("Variable profile type does not match for profile " . $Name);
	        }
	        IPS_SetVariableProfileIcon($Name, $Icon);
	        IPS_SetVariableProfileText($Name, $Prefix, $Suffix);
	        IPS_SetVariableProfileValues($Name, $MinValue, $MaxValue, $StepSize);        
	}
	    
}
?>

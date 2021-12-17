<?
    // Klassendefinition
    class IPS2TradfriBlind extends IPSModule 
    {
	// Überschreibt die interne IPS_Create($id) Funktion
        public function Create() 
        {
            	// Diese Zeile nicht löschen.
            	parent::Create();
		$this->RegisterMessage(0, IPS_KERNELSTARTED);
		
		$this->ConnectParent("{562389F8-739F-644A-4FC7-36F2CE3AFE4F}");
		$this->RegisterPropertyBoolean("Open", false);
		$this->RegisterPropertyInteger("DeviceID", 0);
		$this->RegisterTimer("Timer_1", 0, 'IPS2TradfriBlind_GetState($_IPS["TARGET"]);');
		
		$this->RegisterAttributeString("Name", "");
		$this->RegisterAttributeString("Typ", "");
		$this->RegisterAttributeString("Firmware", "");
		
		$this->RegisterProfileFloat("IPS2Tradfri.Position", "Intensity", "", " %", 0, 100, 0.1, 1);
		
		
		//Status-Variablen anlegen
		$this->RegisterVariableInteger("Move", "Steuerung", "~ShutterMoveStop", 10);
		$this->EnableAction("Move");
		
		$this->RegisterVariableFloat("Position", "Position", "IPS2Tradfri.Position", 20);
		$this->EnableAction("Position");
		
		$this->RegisterVariableBoolean("Available", "Verfügbar", "~Alert.Reversed", 30);
        }
 	
	public function GetConfigurationForm() 
	{ 
		$arrayStatus = array(); 
		$arrayStatus[] = array("code" => 101, "icon" => "inactive", "caption" => "Instanz wird erstellt"); 
		$arrayStatus[] = array("code" => 102, "icon" => "active", "caption" => "Instanz ist aktiv");
		$arrayStatus[] = array("code" => 104, "icon" => "inactive", "caption" => "Instanz ist inaktiv");
		$arrayStatus[] = array("code" => 202, "icon" => "error", "caption" => "Kommunikationfehler!");
				
		$arrayElements = array(); 
		$arrayElements[] = array("type" => "Label", "caption" => "UNGETESTET!!"); 
		
		$arrayElements[] = array("name" => "Open", "type" => "CheckBox",  "caption" => "Aktiv");
		$arrayElements[] = array("type" => "NumberSpinner", "name" => "DeviceID", "caption" => "Device ID", "minimum" => 65537, "maximum" => 66000);
		$arrayElements[] = array("type" => "Label", "caption" => "_____________________________________________________________________________________________________");
		$arrayElements[] = array("type" => "Label", "caption" => "Name: ".$this->ReadAttributeString("Name")); 
		$arrayElements[] = array("type" => "Label", "caption" => "Typ: ".$this->ReadAttributeString("Typ")); 
		$arrayElements[] = array("type" => "Label", "caption" => "Firmware: ".$this->ReadAttributeString("Firmware")); 
		
		$arrayActions = array(); 
		$arrayActions[] = array("type" => "Label", "caption" => "Test Center"); 
		$arrayActions[] = array("type" => "TestCenter", "name" => "TestCenter");
		
 		return JSON_encode(array("status" => $arrayStatus, "elements" => $arrayElements, "actions" => $arrayActions)); 		 
 	}       
	   
        // Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() 
        {
            	// Diese Zeile nicht löschen
            	parent::ApplyChanges();
		
		If (($this->ReadPropertyBoolean("Open") == true) AND (IPS_GetKernelRunlevel() == KR_READY)) {
			If ($this->ReadPropertyInteger("DeviceID") >= 65537) {
				If ($this->GetStatus() <> 102) {
					$this->SetStatus(102);
				}
				If (IPS_GetKernelRunlevel() == KR_READY) {
					$this->GetDeviceInfo();
					$this->GetState();
					$this->SetTimerInterval("Timer_1", 1000);
				}
			}
			else {
				Echo "Syntax der Device ID inkorrekt!";
				$this->SendDebug("ApplyChanges", "Syntax der Device ID inkorrekt!", 0);
				If ($this->GetStatus() <> 202) {
					$this->SetStatus(202);
				}
			}
		}
		else {
			If ($this->GetStatus() <> 104) {
				$this->SetStatus(104);
			}
			$this->SetTimerInterval("Timer_1", 0);
		}	
	}
	
	public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
    	{
		switch ($Message) {
			case IPS_KERNELSTARTED:
				// IPS_KERNELSTARTED
				$this->ApplyChanges();
				break;
			
		}
    	}               
	    
	public function RequestAction($Ident, $Value) 
	{
  		switch($Ident) {
	        case "Position":
	            	$Result = $this->SendDataToParent(json_encode(Array("DataID"=> "{4AA318CB-CA9A-2467-3079-A35AD1577771}", 
				"Function" => "Blind", "DeviceID" => $this->ReadPropertyInteger("DeviceID"), "Value" => $Value )));
	            	SetValueFloat($this->GetIDForIdent($Ident), $Value);
			$this->GetState();
		break;
		case "Move":
			If ($Value == 0) { // Öffnen
	            		$Target = 100.0;
			}
			elseif ($Value == 2) { // Stop
	            		$Target = GetValueFloat($this->GetIDForIdent("Position"));
			}
			elseif ($Value == 4) { // Schließen
	            		$Target = 0.0;
			}
			$Result = $this->SendDataToParent(json_encode(Array("DataID"=> "{4AA318CB-CA9A-2467-3079-A35AD1577771}", 
					"Function" => "Blind", "DeviceID" => $this->ReadPropertyInteger("DeviceID"), "Value" => $Target)));
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
			
			If (isset($DeviceStateArray[9019])) {
				If (GetValueBoolean($this->GetIDForIdent("Available")) <> $DeviceStateArray[9019]) {
					SetValueBoolean($this->GetIDForIdent("Available"), $DeviceStateArray[9019]);
				}
			}
			If (isset($DeviceStateArray[5536])) {
				If (GetValueFloat($this->GetIDForIdent("Position")) <> $DeviceStateArray[5536]) {
					SetValueFloat($this->GetIDForIdent("Position"), $DeviceStateArray[5536]);
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
	
	private function RegisterProfileFloat($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize, $Digits)
	{
	        if (!IPS_VariableProfileExists($Name))
	        {
	            IPS_CreateVariableProfile($Name, 2);
	        }
	        else
	        {
	            $profile = IPS_GetVariableProfile($Name);
	            if ($profile['ProfileType'] != 2)
	                throw new Exception("Variable profile type does not match for profile " . $Name);
	        }
	        IPS_SetVariableProfileIcon($Name, $Icon);
	        IPS_SetVariableProfileText($Name, $Prefix, $Suffix);
	        IPS_SetVariableProfileValues($Name, $MinValue, $MaxValue, $StepSize);
	        IPS_SetVariableProfileDigits($Name, $Digits);
	}
}
?>

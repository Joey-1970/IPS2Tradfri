<?
    // Klassendefinition
    class IPS2TradfriBlind extends IPSModule 
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
		$this->RegisterMessage(0, IPS_KERNELMESSAGE);
		
		$this->ConnectParent("{562389F8-739F-644A-4FC7-36F2CE3AFE4F}");
		$this->RegisterPropertyBoolean("Open", false);
		$this->RegisterPropertyInteger("DeviceID", 0);
		$this->RegisterTimer("Timer_1", 0, 'IPS2TradfriBlind_GetState($_IPS["TARGET"]);');
		
		$this->RegisterAttributeString("Name", "");
		$this->RegisterAttributeString("Typ", "");
		$this->RegisterAttributeString("Firmware", "");
		
		//Status-Variablen anlegen
		$this->RegisterVariableInteger("Move", "Status", "~ShutterMoveStop", 10);
		$this->EnableAction("Move");
		
		$this->RegisterVariableInteger("State", "Status", "", 20);
		$this->EnableAction("State");
		
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
		$arrayElements[] = array("type" => "Label", "label" => "UNGETESTET!!"); 
		
		$arrayElements[] = array("name" => "Open", "type" => "CheckBox",  "caption" => "Aktiv");
		$arrayElements[] = array("type" => "NumberSpinner", "name" => "DeviceID", "caption" => "Device ID", "minimum" => 65537, "maximum" => 66000);
		$arrayElements[] = array("type" => "Label", "label" => "_____________________________________________________________________________________________________");
		$arrayElements[] = array("type" => "Label", "label" => "Name: ".$this->ReadAttributeString("Name")); 
		$arrayElements[] = array("type" => "Label", "label" => "Typ: ".$this->ReadAttributeString("Typ")); 
		$arrayElements[] = array("type" => "Label", "label" => "Firmware: ".$this->ReadAttributeString("Firmware")); 
		
		$arrayActions = array(); 
		$arrayActions[] = array("type" => "Label", "label" => "Test Center"); 
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
				$this->SetStatus(102);
				If (IPS_GetKernelRunlevel() == KR_READY) {
					$this->GetDeviceInfo();
					$this->GetState();
					$this->SetTimerInterval("Timer_1", 1000);
				}
			}
			else {
				Echo "Syntax der Device ID inkorrekt!";
				$this->SendDebug("ApplyChanges", "Syntax der Device ID inkorrekt!", 0);
				$this->SetStatus(203);
			}
		}
		else {
			$this->SetStatus(104);
			$this->SetTimerInterval("Timer_1", 0);
		}	
	}
	
	public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
    	{
		switch ($Message) {
			case 10100:
				// IPS_KERNELSTARTED
				$this->GetDeviceInfo();
				$this->GetState();
				$this->SetTimerInterval("Timer_1", 1000);
				break;
			
		}
    	}               
	    
	public function RequestAction($Ident, $Value) 
	{
  		switch($Ident) {
	        case "State":
	            	//$Result = $this->SendDataToParent(json_encode(Array("DataID"=> "{4AA318CB-CA9A-2467-3079-A35AD1577771}", 
			//	"Function" => "PlugSwitch", "DeviceID" => $this->ReadPropertyInteger("DeviceID"), "State" => $Value )));
	            	SetValueBoolean($this->GetIDForIdent($Ident), $Value);
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
			/*
			If (isset($DeviceStateArray[9019])) {
				If (GetValueBoolean($this->GetIDForIdent("Available")) <> $DeviceStateArray[9019]) {
					SetValueBoolean($this->GetIDForIdent("Available"), $DeviceStateArray[9019]);
				}
			}
			If (isset($DeviceStateArray[5850])) {
				If (GetValueBoolean($this->GetIDForIdent("State")) <> $DeviceStateArray[5850]) {
					SetValueBoolean($this->GetIDForIdent("State"), $DeviceStateArray[5850]);
				}
			}
			*/
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
	    
}
?>

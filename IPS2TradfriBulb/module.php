<?
    // Klassendefinition
    class IPS2TradfriBulb extends IPSModule 
    {
	   
	// Überschreibt die interne IPS_Create($id) Funktion
        public function Create() 
        {
            	// Diese Zeile nicht löschen.
            	parent::Create();
		$this->ConnectParent("{562389F8-739F-644A-4FC7-36F2CE3AFE4F}");
		$this->RegisterPropertyBoolean("Open", false);
		$this->RegisterPropertyInteger("DeviceID", 0);
		
		$this->RegisterProfileInteger("Tradfri.Ambiente", "Bulb", "", "", 0, 3, 0);
		IPS_SetVariableProfileAssociation("Tradfri.Ambiente", 0, "Alltag", "Bulb", 0xf1e0b5);
		IPS_SetVariableProfileAssociation("Tradfri.Ambiente", 1, "Fokus", "Bulb", 0xf5faf6);
		IPS_SetVariableProfileAssociation("Tradfri.Ambiente", 2, "Entspannung", "Bulb", 0xefd275);
		
		$this->RegisterProfileInteger("Tradfri.Fadetime", "Clock", "", "", 0, 10, 0);
		
		// Status-Variablen anlegen
		$this->RegisterVariableBoolean("State", "Status", "~Switch", 10);
	        $this->EnableAction("State");
		
	        $this->RegisterVariableInteger("Intensity", "Intensity", "~Intensity.255", 20);
	        $this->EnableAction("Intensity");
		
		$this->RegisterVariableInteger("Ambiente", "Ambiente", "Tradfri.Ambiente", 30);
	        $this->EnableAction("Ambiente");
		
		$this->RegisterVariableInteger("Fadetime", "Fadetime", "Tradfri.Fadetime", 40);
	        $this->EnableAction("Fadetime");
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
	
	public function RequestAction($Ident, $Value) 
	{
  		switch($Ident) {
	        case "State":
	            	$Result = $this->SendDataToParent(json_encode(Array("DataID"=> "{4AA318CB-CA9A-2467-3079-A35AD1577771}", 
				"Function" => "SwitchBulb", "DeviceID" => $this->ReadPropertyInteger("DeviceID"), "State" => $Value )));
	            	SetValueBoolean($this->GetIDForIdent($Ident), $Value);
		break;
	        case "Intensity":
	            	$Value = min(254, max(0, $Value));
			$Result = $this->SendDataToParent(json_encode(Array("DataID"=> "{4AA318CB-CA9A-2467-3079-A35AD1577771}", 
				"Function" => "BulbIntensity", "DeviceID" => $this->ReadPropertyInteger("DeviceID"), "Intensity" => $Value )));
	            	SetValueInteger($this->GetIDForIdent($Ident), $Value);
			If ($Value == 0) {
				SetValueBoolean($this->GetIDForIdent("State"), false);
			}
			else {
				SetValueBoolean($this->GetIDForIdent("State"), true);
			}
	            break;
		case "Ambiente":
	            	$Result = $this->SendDataToParent(json_encode(Array("DataID"=> "{4AA318CB-CA9A-2467-3079-A35AD1577771}", 
				"Function" => "BulbAmbiente", "DeviceID" => $this->ReadPropertyInteger("DeviceID"), "Value" => $Value )));
	            	SetValueInteger($this->GetIDForIdent($Ident), $Value);
		break;
		case "Fadetime":
	            	$Result = $this->SendDataToParent(json_encode(Array("DataID"=> "{4AA318CB-CA9A-2467-3079-A35AD1577771}", 
				"Function" => "BulbFadetime", "DeviceID" => $this->ReadPropertyInteger("DeviceID"), "Value" => $Value )));
	            	SetValueInteger($this->GetIDForIdent($Ident), $Value);
		break;
	        default:
	            throw new Exception("Invalid Ident");
	    }
	}
	    
	// Beginn der Funktionen
	
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

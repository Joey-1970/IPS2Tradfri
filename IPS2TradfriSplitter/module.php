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
			case "getAccessData":
				$AccessArray = Array();
				$AccessArray["GatewayIP"] = $this->ReadPropertyString("GatewayIP");
				$AccessArray["SecurityID"] = $this->ReadPropertyString("SecurityID");
				$AccessArray["PresharedKey"] = $this->ReadPropertyString("PresharedKey");
				$Result = $AccessArray;
				break;
			case "getConfiguratorData":
				
				break;
			
		}
	return $Result;
	}
	    
	// Beginn der Funktionen
	
}
?>

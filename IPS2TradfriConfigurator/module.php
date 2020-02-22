<?
    // Klassendefinition
    class IPS2TradfriConfigurator extends IPSModule 
    {
	    
	// Überschreibt die interne IPS_Create($id) Funktion
        public function Create() 
        {
            	// Diese Zeile nicht löschen.
            	parent::Create();
		$this->ConnectParent("{562389F8-739F-644A-4FC7-36F2CE3AFE4F}");
		$this->RegisterPropertyInteger("Category", 0);  
		
        }
 	
	public function GetConfigurationForm() 
	{ 
		$arrayStatus = array(); 
		$arrayStatus[] = array("code" => 101, "icon" => "inactive", "caption" => "Instanz wird erstellt"); 
		$arrayStatus[] = array("code" => 102, "icon" => "active", "caption" => "Instanz ist aktiv");
		$arrayStatus[] = array("code" => 104, "icon" => "inactive", "caption" => "Instanz ist inaktiv");
		$arrayStatus[] = array("code" => 202, "icon" => "error", "caption" => "Kommunikationfehler!");
				
		$arrayElements = array(); 
		$arrayElements[] = array("type" => "SelectCategory", "name" => "Category", "caption" => "Zielkategorie");
		$arrayElements[] = array("type" => "Label", "label" => "_____________________________________________________________________________________________________");
		$arraySort = array();
		$arraySort = array("column" => "DeviceID", "direction" => "ascending");
		
		$arrayColumns = array();
		$arrayColumns[] = array("caption" => "Geräte ID", "name" => "DeviceID", "width" => "100px", "visible" => true);
		$arrayColumns[] = array("caption" => "Name", "name" => "Name", "width" => "250px", "visible" => true);
		$arrayColumns[] = array("caption" => "Typ", "name" => "Typ", "width" => "200px", "visible" => true);
		$arrayColumns[] = array("caption" => "Firmware", "name" => "Firmware", "width" => "200px", "visible" => true);
		$arrayColumns[] = array("caption" => "Klasse", "name" => "Class", "width" => "auto", "visible" => true);
		
		$Category = $this->ReadPropertyInteger("Category");
		$RootNames = [];
		$RootId = $Category;
		while ($RootId != 0) {
		    	if ($RootId != 0) {
				$RootNames[] = IPS_GetName($RootId);
		    	}
		    	$RootId = IPS_GetParent($RootId);
			}
		$RootNames = array_reverse($RootNames);
		
		$StationArray = array();
		If ($this->HasActiveParent() == true) {
			$DeviceArray = unserialize($this->GetData());
		}
		$arrayValues = array();
		for ($i = 0; $i < Count($DeviceArray); $i++) {
			
			$arrayCreate = array();
			If (($DeviceArray[$i]["DeviceID"] >= 65537) AND ($DeviceArray[$i]["Class"] <> "Unknown")) {
				$arrayCreate[] = array("moduleID" => 0, 
					       "configuration" => array("DeviceID" => $DeviceArray[$i][$DeviceID], "Open" => true) );
				$arrayValues[] = array("DeviceID" => $DeviceArray[$i]["DeviceID"], "Name" => $DeviceArray[$i]["Name"], "Firmware" => $DeviceArray[$i]["Firmware"], "Class" => $DeviceArray[$i]["Class"],
					       "instanceID" => $DeviceArray[$i]["Instance"], "create" => $arrayCreate);
			}
			else {
				$arrayValues[] = array("DeviceID" => $DeviceArray[$i]["DeviceID"], "Name" => $DeviceArray[$i]["Name"], "Firmware" => $DeviceArray[$i]["Firmware"], "Class" => $DeviceArray[$i]["Class"],
					       "instanceID" => $DeviceArray[$i]["Instance"]);
			}
			
		}	
		$arrayElements[] = array("type" => "Configurator", "name" => "DeviceList", "caption" => "Tradri-Geräte", "rowCount" => 10, "delete" => false, "sort" => $arraySort, "columns" => $arrayColumns, "values" => $arrayValues);

		$arrayElements[] = array("type" => "Label", "label" => "_____________________________________________________________________________________________________");
		
 		return JSON_encode(array("status" => $arrayStatus, "elements" => $arrayElements)); 		 
 	}       
	   
        // Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() 
        {
            	// Diese Zeile nicht löschen
            	parent::ApplyChanges();
		
		If (IPS_GetKernelRunlevel() == 10103) {	
			If ($this->HasActiveParent() == true) {
				$this->SetStatus(102);
			}
			else {
				$this->SetStatus(104);
			}
		}
	}
	    
	// Beginn der Funktionen
	private function GetData()
	{
		$Result = $this->SendDataToParent(json_encode(Array("DataID"=> "{4AA318CB-CA9A-2467-3079-A35AD1577771}", 
				"Function" => "getDeviceList" )));
		//$this->SendDebug("GetData", $Result, 0);
		$DeviceArray = unserialize($Result);
		If (is_array($DeviceArray)) {
			$this->SetStatus(102);
			$this->SendDebug("GetData", $Result, 0);
			$Devices = array();
			$i = 0;
			foreach($DeviceArray as $Key => $Device) {
				$Devices[$i]["Name"] = $Device["Name"];
				$Devices[$i]["Typ"] = $Device["Typ"];
				$Devices[$i]["Firmware"] = $Device["Firmware"];
				$Devices[$i]["Class"] = $Device["Class"];
				$Devices[$i]["DeviceID"] = $Key;
				
				$Devices[$i]["Instance"] = 0;//$this->GetGeCoSInstanceID($Key);
				$i = $i + 1;
			}
		}
	return serialize($Devices);;
	}
	
	function GetStationInstanceID(string $StationID)
	{
		$guid = "{47286CAD-187A-6D88-89F0-BDA50CBF712F}";
	    	$Result = 0;
	    	// Modulinstanzen suchen
	    	$InstanceArray = array();
	    	$InstanceArray = @(IPS_GetInstanceListByModuleID($guid));
	    	If (is_array($InstanceArray)) {
			foreach($InstanceArray as $Module) {
				If (strtolower(IPS_GetProperty($Module, "StationID")) == strtolower($StationID)) {
					$this->SendDebug("GetStationInstanceID", "Gefundene Instanz: ".$Module, 0);
					$Result = $Module;
					break;
				}
				else {
					$Result = 0;
				}
			}
		}
	return $Result;
	}
}
?>

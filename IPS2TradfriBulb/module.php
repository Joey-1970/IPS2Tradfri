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
		$this->RegisterMessage(0, IPS_KERNELMESSAGE);
		
		$this->ConnectParent("{562389F8-739F-644A-4FC7-36F2CE3AFE4F}");
		$this->RegisterPropertyBoolean("Open", false);
		$this->RegisterPropertyInteger("DeviceID", 0);
		$this->RegisterPropertyInteger("DeviceSpecification", 0);
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
		
		$this->RegisterProfileInteger("Tradfri.Fadetime", "Clock", "", "", 0, 10, 1);
		
		// Status-Variablen anlegen
		$this->RegisterVariableBoolean("State", "Status", "~Switch", 10);
	        $this->EnableAction("State");
		
	        $this->RegisterVariableInteger("Intensity", "Intensität", "~Intensity.255", 20);
	        $this->EnableAction("Intensity");
		
		$this->RegisterVariableInteger("Fadetime", "Fadezeit", "Tradfri.Fadetime", 40);
		$this->EnableAction("Fadetime");
		
		$this->RegisterVariableInteger("Ambiente", "Ambiente", "Tradfri.Ambiente", 50);
		
		$this->RegisterVariableInteger("Color", "Farbe", "Tradfri.Color", 60);
	       
		//$this->RegisterVariableInteger("RGB", "Farbe", "~HexColor", 70);
		
		$this->RegisterVariableBoolean("Available", "Verfügbar", "~Alert.Reversed", 80);
        }
 	
	public function GetConfigurationForm() 
	{ 
		$arrayStatus = array(); 
		$arrayStatus[] = array("code" => 101, "icon" => "inactive", "caption" => "Instanz wird erstellt"); 
		$arrayStatus[] = array("code" => 102, "icon" => "active", "caption" => "Instanz ist aktiv");
		$arrayStatus[] = array("code" => 104, "icon" => "inactive", "caption" => "Instanz ist inaktiv");
		$arrayStatus[] = array("code" => 202, "icon" => "error", "caption" => "Kommunikationfehler!");
		$arrayStatus[] = array("code" => 203, "icon" => "error", "caption" => "Falsche Dateneingabe!");
				
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
		
		If (($this->ReadPropertyBoolean("Open") == true) AND (IPS_GetKernelRunlevel() == KR_READY)) {
			If ($this->ReadPropertyInteger("DeviceID") >= 65537) {
				If (($this->ReadPropertyInteger("DeviceSpecification") == 2) OR ($this->ReadPropertyInteger("DeviceSpecification") == 0)) {
					$this->EnableAction("Ambiente");
					IPS_SetHidden($this->GetIDForIdent("Ambiente"), false); 
				}
				else {
					$this->DisableAction("Ambiente");
					IPS_SetHidden($this->GetIDForIdent("Ambiente"), true); 
				}
				 If (($this->ReadPropertyInteger("DeviceSpecification") == 3) OR ($this->ReadPropertyInteger("DeviceSpecification") == 0)) {
					$this->EnableAction("Color");
					IPS_SetHidden($this->GetIDForIdent("Color"), false); 
					//$this->EnableAction("RGB");
					//IPS_SetHidden($this->GetIDForIdent("RGB"), false); 
				}
				else {
					$this->DisableAction("Color");
					IPS_SetHidden($this->GetIDForIdent("Color"), true); 
					//$this->DisableAction("RGB");
					//IPS_SetHidden($this->GetIDForIdent("RGB"), true); 
				}

				$this->SetStatus(102);
				$this->GetDeviceInfo();
				$this->GetState();
				$this->SetTimerInterval("Timer_1", 1000);
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
			case 10001:
				// IPS_KERNELSTARTED
				$this->ApplyChanges;
				break;
			
		}
    	}            
	    
	public function RequestAction($Ident, $Value) 
	{
  		If ($this->ReadPropertyBoolean("Open") == true) {
			switch($Ident) {
				case "State":
					$Result = $this->SendDataToParent(json_encode(Array("DataID"=> "{4AA318CB-CA9A-2467-3079-A35AD1577771}", 
						"Function" => "BulbSwitch", "DeviceID" => $this->ReadPropertyInteger("DeviceID"), "State" => $Value, "Fadetime" => GetValueInteger($this->GetIDForIdent("Fadetime")) )));
					SetValueBoolean($this->GetIDForIdent($Ident), $Value);
					$this->GetState();
				break;
				case "Intensity":
					$Value = min(254, max(0, $Value));
					$Result = $this->SendDataToParent(json_encode(Array("DataID"=> "{4AA318CB-CA9A-2467-3079-A35AD1577771}", 
						"Function" => "BulbIntensity", "DeviceID" => $this->ReadPropertyInteger("DeviceID"), "Intensity" => $Value, "Fadetime" => GetValueInteger($this->GetIDForIdent("Fadetime")) )));
					SetValueInteger($this->GetIDForIdent($Ident), $Value);
					$this->GetState();
				break;
				case "Fadetime":
					SetValueInteger($this->GetIDForIdent($Ident), $Value);
				break;
				case "Ambiente":
					$AmmbienteArray = array(0 => "f1e0b5", 1 => "f5faf6", 2 => "efd275");
					$Result = $this->SendDataToParent(json_encode(Array("DataID"=> "{4AA318CB-CA9A-2467-3079-A35AD1577771}", 
						"Function" => "BulbAmbiente", "DeviceID" => $this->ReadPropertyInteger("DeviceID"), "Value" => $AmmbienteArray[$Value], "Fadetime" => GetValueInteger($this->GetIDForIdent("Fadetime")) )));
					SetValueInteger($this->GetIDForIdent($Ident), $Value);
					$this->GetState();
				break;
				case "Color":
					$ColorArray = array(0 => "4a418a", 1 => "6c83ba", 2 => "8f2686", 3 => "a9d62b", 4 => "c984bb", 
							    5 => "d6e44b", 6 => "d9337c", 7 => "da5d41", 8 => "dc4b31", 9 => "dcf0f8", 
							    10 => "e491af", 11 => "e57345", 12 => "e78834", 13 => "e8bedd", 14 => "eaf6fb", 
							    15 => "ebb63e", 16 => "efd275", 17 => "f1e0b5", 18 => "f2eccf", 19 => "f5faf6");
					$Result = $this->SendDataToParent(json_encode(Array("DataID"=> "{4AA318CB-CA9A-2467-3079-A35AD1577771}", 
						"Function" => "BulbAmbiente", "DeviceID" => $this->ReadPropertyInteger("DeviceID"), "Value" => $ColorArray[$Value], "Fadetime" => GetValueInteger($this->GetIDForIdent("Fadetime")) )));
					SetValueInteger($this->GetIDForIdent($Ident), $Value);
					$this->GetState();
				break;
				/*
				case "RGB":
					// Wert von RGB in xyY wandeln
					$CIE = $this->HexToCIE($Value);
					$Result = $this->SendDataToParent(json_encode(Array("DataID"=> "{4AA318CB-CA9A-2467-3079-A35AD1577771}", 
						"Function" => "BulbRGB", "DeviceID" => $this->ReadPropertyInteger("DeviceID"), "ValueX" => intval($CIE['x'] * 1000), "ValueY" => intval($CIE['y'] * 1000) )));
					SetValueInteger($this->GetIDForIdent($Ident), $Value);
					$this->GetState();
				break;
				*/
				default:
				    throw new Exception("Invalid Ident");
		    	}
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
			If (isset($DeviceStateArray[5850])) {
				If (GetValueBoolean($this->GetIDForIdent("State")) <> $DeviceStateArray[5850]) {
					SetValueBoolean($this->GetIDForIdent("State"), $DeviceStateArray[5850]);
				}
			}
			If (isset($DeviceStateArray[5851])) {
				If (GetValueInteger($this->GetIDForIdent("Intensity")) <> $DeviceStateArray[5851]) {
					SetValueInteger($this->GetIDForIdent("Intensity"), $DeviceStateArray[5851]);
				}
			}
			If (isset($DeviceStateArray[5712])) {
				If (GetValueInteger($this->GetIDForIdent("Fadetime")) <> $DeviceStateArray[5712]) {
					SetValueInteger($this->GetIDForIdent("Fadetime"), $DeviceStateArray[5712]);
				}
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
			/*
			If ((isset($DeviceStateArray[5709])) AND (isset($DeviceStateArray[5710]))) {
				$ValueX = $DeviceStateArray[5709] / 1000;
				$ValueY = $DeviceStateArray[5710] / 1000;
				// Umwandlung von CIE zu RGB
				$ValueRGB = $this->CIEToHex($ValueX, $ValueY);
				If (GetValueInteger($this->GetIDForIdent("RGB")) <> $ValueRGB) {
					SetValueInteger($this->GetIDForIdent("RGB"), $ValueRGB);
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
			If (isset($DeviceInfo["Name"])) {
				If ($this->ReadAttributeString("Name") <> $DeviceInfo["Name"]) {
					$this->WriteAttributeString("Name", $DeviceInfo["Name"]);
				}
			}
			If (isset($DeviceInfo["Typ"])) {
				If ($this->ReadAttributeString("Typ") <> $DeviceInfo["Typ"]) {
					$this->WriteAttributeString("Typ", $DeviceInfo["Typ"]);
				}
			}
			If (isset($DeviceInfo["Firmware"])) {
				If ($this->ReadAttributeString("Firmware") <> $DeviceInfo["Firmware"]) {
					$this->WriteAttributeString("Firmware", $DeviceInfo["Firmware"]);
				}
			}
		}
	}    
	 
	private function HexToCIE($Hex)
    	{
        	$this->SendDebug("HexToCIE", "Wert: ".$Hex, 0);
		$red = (($Hex >> 16) & 0xFF);
		$green = (($Hex >> 8) & 0xFF);
		$blue = (($Hex >> 0) & 0xFF);	
        
		$red = ($red > 0.04045) ? pow(($red + 0.055) / (1.0 + 0.055), 2.4) : ($red / 12.92);
		$green = ($green > 0.04045) ? pow(($green + 0.055) / (1.0 + 0.055), 2.4) : ($green / 12.92);
		$blue = ($blue > 0.04045) ? pow(($blue + 0.055) / (1.0 + 0.055), 2.4) : ($blue / 12.92);

		$X = $red * 0.664511 + $green * 0.154324 + $blue * 0.162028;
		$Y = $red * 0.283881 + $green * 0.668433 + $blue * 0.047685;
		$Z = $red * 0.000088 + $green * 0.072310 + $blue * 0.986039;
		
		$CIE['x'] = round(($X / ($X + $Y + $Z)), 4);
		$CIE['y'] = round(($Y / ($X + $Y + $Z)), 4);
		$this->SendDebug("HexToCIE", "Ergebnis X: ".$CIE['x']." Ergebnis y: ".$CIE['y'], 0);
        return $CIE;
    	}

    	private function CIEToHex(float $x, float $y)
    	{
		$z = 1.0 - $x - $y;
		$Y = 1;
		$X = ($Y / $y) * $x; $Z = ($Y / $y) * $z;

		$red = $X * 1.656492 - $Y * 0.354851 - $Z * 0.255038;
		$green = -$X * 0.707196 + $Y * 1.655397 + $Z * 0.036152;
		$blue = $X * 0.051713 - $Y * 0.121364 + $Z * 1.011530;

		if ($red > $blue && $red > $green && $red > 1.0) {
			$green = $green / $red;
			$blue = $blue / $red;
			$red = 1.0;
		} elseif ($green > $blue && $green > $red && $green > 1.0) {
			$red = $red / $green;
			$blue = $blue / $green;
			$green = 1.0;
		} elseif ($blue > $red && $blue > $green && $blue > 1.0) {
			$red = $red / $blue;
			$green = $green / $blue;
			$blue = 1.0;
		}

		$red = $red <= 0.0031308 ? 12.92 * $red : (1.0 + 0.055) * $red ** (1.0 / 2.4) - 0.055;
		$green = $green <= 0.0031308 ? 12.92 * $green : (1.0 + 0.055) * $green ** (1.0 / 2.4) - 0.055;
		$blue = $blue <= 0.0031308 ? 12.92 * $blue : (1.0 + 0.055) * $blue ** (1.0 / 2.4) - 0.055;

		$red = ceil($red * 255);
		$green = ceil($green * 255);
		$blue = ceil($blue * 255);

		//$color = sprintf('#%02x%02x%02x', $red, $green, $blue);
		$Hex = hexdec(str_pad(dechex($red), 2,'0', STR_PAD_LEFT).str_pad(dechex($green), 2,'0', STR_PAD_LEFT).str_pad(dechex($blue), 2,'0', STR_PAD_LEFT));
	return $Hex;
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

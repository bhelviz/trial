<?php
require_once 'config.php';

$_SESSION["HOU_ID"] = "30011";
$_SESSION["HOU_NAME"] = "Srujana Engineering";
$_SESSION["HOU_ROLE"] = "Vendor";

$_POST["vendorCode"] = "30011";
$_POST["vendorName"] = "Srujana Engineering";
$_POST["repName"] = "John Doe";
$_POST["repID"] = "ID123";
$_POST["osPO"] = "8200000490";
$_POST["bhelOfficial"] = "2767333";
$_POST["sourceFrom"] = "BHEL";
$_POST["destinationTo"] = "VENDOR";
$_POST["date"] = date('Y-m-d');
$_POST["returnDate"] = date('Y-m-d', strtotime('+5 days'));
$_POST["purpose"] = "Testing";
$_POST["materialOwner"] = "Vendor";
$_POST["itemDescription"] = ["Item 1 Description"];
$_POST["itemQuantity"] = [10];
$_POST["uom"] = ["NOS"];

try {
    $vendorCode = $_POST["vendorCode"];
    $vendorName = $_POST["vendorName"];
    $repName = $_POST["repName"];
    $repID = $_POST["repID"];
    $osPO = $_POST["osPO"];
    $bhelOfficial = $_POST["bhelOfficial"];
    $sourceFrom = $_POST["sourceFrom"];
    $destinationTo = $_POST["destinationTo"];
    $passDate = $_POST["date"];
    $returnDate = $_POST["returnDate"];
    $purpose = $_POST["purpose"];
    $materialOwner = $_POST["materialOwner"];
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $hostName = gethostbyaddr($ipAddress) ?: 'UNKNOWN';

    $today = date('Y-m-d');
    $errors = [];
    if ($passDate < $today) {
        $errors[] = "Gatepass Date must be today or in the future.";
    }
    if ($returnDate <= $today) {
        $errors[] = "Expected Date of Return must be ahead of today's date.";
    }
    if ($returnDate <= $passDate) {
        $errors[] = "Expected Date of Return must be after Gatepass Date.";
    }

    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "Validation Error: " . $error . "\n";
        }
    } else {
        $executeValues = array(
            "vendorCode" => $vendorCode,
            "vendorName" => $vendorName,
            "repName" => $repName,
            "repID" => $repID,
            "osPO" => $osPO,
            "bhelOfficial" => $bhelOfficial,
            "sourceFrom" => $sourceFrom,
            "destinationTo" => $destinationTo,
            "passDate" => $passDate,
            "returnDate" => $returnDate,
            "purpose" => $purpose,
            "materialOwner" => $materialOwner,
            "ipAddress" => $ipAddress,
            "hostName" => $hostName,
        );

        $statement = $dBhandler->prepare("INSERT INTO HPVP_OSGP (
                                                HO_VENDOR_CODE        , 
                                                HO_VENDOR_NAME        , 
                                                HO_REPRESENTATIVE_NAME, 
                                                HO_REPRESENTATIVE_ID  , 
                                                HO_ORDER              , 
                                                HO_BHEL_OFFICIAL      , 
                                                HO_SOURCE_FROM        , 
                                                HO_DESTINATION_TO     , 
                                                HO_DATE               , 
                                                HO_DATE_RETURN        , 
                                                HO_PURPOSE            , 
                                                HO_MATERIAL_OWNER     , 
                                                HO_STATUS             , 
                                                HO_IP_ADDRESS         , 
                                                HO_HOSTNAME           , 
                                                HO_TIME_CREATED        

                                            ) VALUES (
                                                :vendorCode, 	
                                                :vendorName, 	
                                                :repName, 	
                                                :repID, 	
                                                :osPO, 	
                                                :bhelOfficial, 	
                                                :sourceFrom, 	
                                                :destinationTo, 	
                                                :passDate, 	
                                                :returnDate, 	
                                                :purpose,
                                                :materialOwner, 	
                                                'GATEPASS_REQUESTED', 	
                                                :ipAddress, 	
                                                :hostName, 	
                                                NOW()
                                            )");

        $statement->execute($executeValues);

        if ($statement->rowCount() > 0) {
            $gatepassId = $dBhandler->lastInsertId();
            echo "Successfully inserted into HPVP_OSGP with ID: " . $gatepassId . "\n";

            $itemStatement = $dBhandler->prepare("INSERT INTO hpvp_osgp_item (
                HOI_GATEPASS_SLNO,
                HOI_ITEM_SLNO,
                HOI_ITEM_DESCRIPTION,
                HOI_ITEM_QUANTITY,
                HOI_ITEM_UOM
            ) VALUES (
                :gatepassId,
                :itemSlno,
                :itemDesc,
                :itemQty,
                :itemUom
            )");

            $descriptions = $_POST['itemDescription'] ?? [];
            $quantities = $_POST['itemQuantity'] ?? [];
            $uoms = $_POST['uom'] ?? [];

            $count = min(count($descriptions), 25);
            for ($i = 0; $i < $count; $i++) {
                $desc = trim($descriptions[$i] ?? '');
                $qty = trim($quantities[$i] ?? '');
                $uom = trim($uoms[$i] ?? '');

                if ($desc !== '') {
                    $itemStatement->execute([
                        'gatepassId' => $gatepassId,
                        'itemSlno' => $i + 1,
                        'itemDesc' => $desc,
                        'itemQty' => $qty !== '' ? (int) $qty : null,
                        'itemUom' => $uom !== '' ? $uom : null
                    ]);
                    echo "Successfully inserted item: " . ($i + 1) . "\n";
                }
            }
        } else {
            echo "Insert failed, rowCount is 0\n";
        }
    }
} catch (Exception $e) {
    echo "EXCEPTION THROWN:\n";
    echo $e->getMessage() . "\n";
}

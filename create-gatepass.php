<?php
require_once 'config.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Inward Gatepass</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <link href="css/create-gatepass.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>

<body class="sb-nav-fixed">
    <?php
    try {
        $id = $_SESSION["HOU_ID"];
        $name = $_SESSION["HOU_NAME"];
        $role = $_SESSION["HOU_ROLE"];

        if($role != "Vendor" && $role != "Administrator"){
            header("Location: main.php");
            exit();
        }
        ?>
        <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
            <?php include 'header.php' ?>
        </nav>
        <div id="layoutSidenav">
            <div id="layoutSidenav_nav">
                <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                    <?php include 'sidebar.php' ?>
                </nav>
            </div>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-2 my-2">Create Material Gatepass</h1>
                        <?php

                        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                            ?>
                            <form name="f1" method="post" onsubmit="return confirm('Are you sure you want to create this Material Gatepass?');">
                                <div class="row mb-3">
                                    <div class="col-md-3">

                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <div class="form-floating mb-3 mb-md-0">
                                            <input class="form-control form-control-sm" id="vendorCode" name="vendorCode"
                                                type="text" value="<?php echo $id; ?>" readonly required />
                                            <label for="vendorCode">Vendor Code</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-floating">
                                            <input class="form-control form-control-sm" id="vendorName" name="vendorName"
                                                type="text" value="<?php echo $name; ?>" required />
                                            <label for="vendorName">Vendor Name</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-floating mb-3 mb-md-0">
                                            <input class="form-control form-control-sm" id="repName" name="repName" type="text"
                                                placeholder="Representative Name" required />
                                            <label for="repName">Representative Name</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-floating">
                                            <input class="form-control form-control-sm" id="repID" name="repID" type="text"
                                                placeholder="Representative ID" required />
                                            <label for="repID">Representative ID</label>
                                        </div>
                                    </div>
                                </div>
                                <?php
                                $prepstmtpo = $dBhandler->prepare("SELECT DISTINCT EBELN FROM HPVP_OSGP_PODATA WHERE LPAD(LIFNR,10,'0') = LPAD(?,10,'0') ORDER BY EBELN");
                                $prepstmtpo->execute([$id]);
                                ?>
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <div class="form-floating mb-3 mb-md-0">
                                            <select class="form-select form-select-sm ps-3" id="osPO" name="osPO" required>
                                                <option value="">SELECT PO</option>
                                                <?php
                                                while ($row = $prepstmtpo->fetch(PDO::FETCH_OBJ)) {
                                                    echo "<option value=" . $row->EBELN . ">" . $row->EBELN . "</option>";
                                                }
                                                ?>
                                            </select>
                                            <label for="osPO">Purchase / Work Order</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-floating mb-3 mb-md-0">
                                            <select class="form-select form-select-sm ps-3" id="bhelOfficial"
                                                name="bhelOfficial" required>
                                                <option value="">SELECT</option>
                                                <?php
                                                $prepstmtOfficial = $dBhandler->prepare("SELECT HOU_ID, HOU_NAME, HOU_DESIGNATION FROM hpvp_osgp_user WHERE HOU_ROLE = 'Recommender' ORDER BY HOU_NAME");
                                                $prepstmtOfficial->execute();
                                                while ($official = $prepstmtOfficial->fetch(PDO::FETCH_OBJ)) {
                                                    $displayName = strtoupper($official->HOU_NAME);
                                                    if (!empty($official->HOU_DESIGNATION)) {
                                                        $displayName .= " (" . strtoupper($official->HOU_DESIGNATION) . ")";
                                                    }
                                                    echo "<option value='" . htmlspecialchars($official->HOU_ID) . "'>" . htmlspecialchars($displayName) . "</option>";
                                                }
                                                ?>
                                            </select>
                                            <label for="bhelOfficial">BHEL Official</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-floating mb-3 mb-md-0">
                                            <input class="form-control form-control-sm" id="sourceFrom" name="sourceFrom"
                                                type="text" placeholder="Source / From" required />
                                            <label for="sourceFrom">Source / From</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-floating">
                                            <input class="form-control form-control-sm" id="destinationTo" name="destinationTo"
                                                type="text" placeholder="Destination / To" required />
                                            <label for="destinationTo">Destination / To</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <div class="form-floating mb-3 mb-md-0">
                                            <input class="form-control form-control-sm" id="date" name="date" type="date"
                                                placeholder="Date" min="<?php echo date('Y-m-d'); ?>" required />
                                            <label for="date">Date</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-floating">
                                            <input class="form-control form-control-sm" id="returnDate" name="returnDate"
                                                type="date" placeholder="Expected Date of Return" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required />
                                            <label for="returnDate">Expected Date of Return</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-floating mb-3 mb-md-0">
                                            <input class="form-control form-control-sm" id="purpose" name="purpose" type="text"
                                                placeholder="Purpose" required />
                                            <label for="purpose">Purpose</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-floating mb-3 mb-md-0">
                                            <select class="form-select form-select-sm ps-3" id="materialOwner"
                                                name="materialOwner" required>
                                                <option value="">SELECT</option>
                                                <option value="BHEL-HPVP">BHEL-HPVP</option>
                                                <option value="Vendor">Vendor</option>
                                            </select>
                                            <label for="materialOwner">Material Owner</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-12 text-center">
                                        <h4>ITEMS</h4>
                                    </div>
                                </div>
                                <div id="itemsContainer">
                                    <div class="row mb-3 item-row">
                                        <div class="col-md-5">
                                            <div class="form-floating mb-3 mb-md-0">
                                                <input class="form-control form-control-sm item-desc" name="itemDescription[]"
                                                    type="text" placeholder="Item Description" required id="desc_1" />
                                                <label for="desc_1" class="item-desc-label">1. Item Description</label>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-floating">
                                                <input class="form-control form-control-sm item-qty" name="itemQuantity[]"
                                                    type="number" placeholder="Item Quantity" required id="qty_1" />
                                                <label for="qty_1" class="item-qty-label">1. Item Quantity</label>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-floating">
                                                <input class="form-control form-control-sm item-uom" name="uom[]" type="text"
                                                    placeholder="UOM" required id="uom_1" />
                                                <label for="uom_1" class="item-uom-label">1. UOM</label>
                                            </div>
                                        </div>
                                        <div class="col-md-1 d-flex align-items-center">
                                            <button type="button" class="btn btn-danger btn-sm remove-item-btn" title="Remove Item"><i class="fas fa-trash"></i></button>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-12 text-end">
                                        <button type="button" class="btn btn-success btn-sm" id="addItemBtn">
                                            <i class="fas fa-plus"></i> Add Item
                                        </button>
                                    </div>
                                </div>

                                <div class="mt-4 mb-0">
                                    <div class="d-grid">
                                        <input class="btn btn-primary btn-block" type="submit"
                                            value="Create Material Gatepass" />
                                    </div>
                                </div>
                            </form>
                            <?php
                        } else {
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
                                    echo "<h4 class='text-danger'>Error: " . htmlspecialchars($error) . "</h4>";
                                }
                                echo "<p><a href='create-gatepass.php' class='btn btn-primary btn-sm mt-2'>Go Back</a></p>";
                            } else {

                                $dBhandler->beginTransaction();

                                try{
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
                                            }
                                        }

                                        echo "<h3 class='text-primary'>Gatepass #" . $gatepassId . " Created. Check in <a href='history.php'>History</a>.</h3>";
                                    }

                                    $dBhandler->commit();
                                } catch (Exception $e) {
                                    $dBhandler->rollBack();
                                    echo "<h4 class='text-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</h4>";
                                    echo "<p><a href='create-gatepass.php' class='btn btn-primary btn-sm mt-2'>Go Back</a></p>";
                                }
                            }
                        }
                        ?>
                    </div>
                </main>
                <footer class="py-4 bg-light mt-auto">
                    <?php include 'footer.php' ?>
                </footer>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"
            crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
        <!--<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>-->
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"
            crossorigin="anonymous"></script>
        <script src="js/datatables-simple-demo.js"></script>
        <script src="js/create-gatepass.js"></script>
        <?php
    } catch (Exception $e) {
        echo $e;
    } finally {

    }
    ?>
</body>

</html>
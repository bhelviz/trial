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
    <title>Security - In</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>

<body class="sb-nav-fixed">
    <?php
    try {

        $id = $_SESSION["HOU_ID"];
        $name = $_SESSION["HOU_NAME"];
        $role = $_SESSION["HOU_ROLE"];
        if($role != "Security" && $role != "Administrator"){
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
                        <h1 class="mt-2 my-2">Security - In</h1>
                        <?php
                        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gatepass_id'])) {
                            $gpslno = $_POST['gatepass_id'];
                            $remark = isset($_POST['remark']) ? trim($_POST['remark']) : '';
                            $currentTime = date('Y-m-d H:i:s');

                            if ($remark === '') {
                                echo "<h4 class='text-danger'>Error: Remark is required.</h4>";
                            } else {
                                // Check status and owner of this gatepass
                                $checkGPSMT = $dBhandler->prepare("SELECT HO_STATUS, HO_MATERIAL_OWNER FROM HPVP_OSGP WHERE HO_GATEPASS_SLNO = ?");
                                $checkGPSMT->execute([$gpslno]);
                                $gpRow = $checkGPSMT->fetch(PDO::FETCH_OBJ);

                                if ($gpRow) {
                                    $dBhandler->beginTransaction();
                                    try {
                                        if ($gpRow->HO_STATUS == 'GATEPASS_APPROVED' && $gpRow->HO_MATERIAL_OWNER == 'Vendor') {
                                            // 1st movement of Vendor materials (no items input boxes)
                                            $prepstmtupd = $dBhandler->prepare("UPDATE HPVP_OSGP SET HO_TIME_IN_OUT = ?, HO_TIME_IN_OUT_BY = ?, HO_STATUS = 'SECURITY_IN_DONE', HO_SECURITY_REMARK = ? WHERE HO_GATEPASS_SLNO = ?");
                                            $prepstmtupd->execute([$currentTime, $id, $remark, $gpslno]);
                                            if ($prepstmtupd->rowCount() > 0) {
                                                echo "<h4 class='text-primary'>Gatepass #" . htmlspecialchars($gpslno) . " is made IN</h4>";
                                            }
                                            $dBhandler->commit();
                                        } else {
                                            // Return movement BHEL-HPVP materials (contains item inputs)
                                            $retslno = $_POST['return_id'];
                                            $hasUpdates = false;
                                            // Update return header status to 'SECURITY_IN_DONE'

                                            $prepstmtupdHdr = $dBhandler->prepare("UPDATE HPVP_OSGP_RETURN 
                                                SET HOR_STATUS = 'SECURITY_IN_DONE', HOR_RETURN_DATE = ?, HOR_SECURITY_IN_OUT_BY = ?, HOR_SECURITY_REMARK = ?
                                                WHERE HOR_GATEPASS_SLNO = ? AND HOR_RETURN_SLNO = ?");
                                            $prepstmtupdHdr->execute([$currentTime, $id, $remark, $gpslno, $retslno]);
                                            foreach ($_POST as $postName => $qtyValue) {
                                                if (str_starts_with($postName, 'qty-')) {
                                                    $itemslno = substr($postName, 4); // get item slno
                                                    if (is_numeric($itemslno) && $qtyValue >= 0 && $qtyValue !== '') {
                                                        // Update the return table the quantity for this item
                                                        $prepstmtupd = $dBhandler->prepare("UPDATE HPVP_OSGP_RETURN_ITEM 
                                                            SET HORI_ITEM_RETURN_QUANTITY = ?
                                                            WHERE HORI_GATEPASS_SLNO = ? AND HORI_ITEM_SLNO = ? AND HORI_RETURN_SLNO = ?");
                                                        $prepstmtupd->execute([$qtyValue, $gpslno, $itemslno, $retslno]);

                                                        if ($prepstmtupd->rowCount() > 0) {
                                                            echo "<h4 class='text-primary'>Gatepass #" . htmlspecialchars($gpslno) . ", Item #" . htmlspecialchars($itemslno) . " is made IN</h4>";
                                                            $hasUpdates = true;
                                                        }
                                                    }
                                                }
                                            }  
                                            $dBhandler->commit();                                      
                                        }
                                    
                                    } catch (Exception $e) {
                                        $dBhandler->rollBack();
                                        echo "<h4 class='text-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</h4>";
                                    }
                                }
                            }
                        }

                        $prepstmt = $dBhandler->prepare("SELECT HO_GATEPASS_SLNO, HO_REPRESENTATIVE_NAME, HO_REPRESENTATIVE_ID, HO_ORDER, HO_BHEL_OFFICIAL, HO_SOURCE_FROM, HO_DESTINATION_TO, 
                            DATE_FORMAT(HO_DATE,'%d.%m.%Y') HO_DATE, DATE_FORMAT(HO_DATE_RETURN,'%d.%m.%Y') HO_DATE_RETURN, HO_PURPOSE, HO_STATUS, HO_VENDOR_CODE, HO_MATERIAL_OWNER, HOR_STATUS
                            FROM hpvp_osgp 
                            LEFT JOIN hpvp_osgp_return ON HOR_GATEPASS_SLNO = HO_GATEPASS_SLNO
                            WHERE (HO_STATUS = 'GATEPASS_APPROVED' AND HO_MATERIAL_OWNER = 'Vendor') 
                            OR (HOR_STATUS = 'RETURN_APPROVED' AND HO_MATERIAL_OWNER = 'BHEL-HPVP')");
                        $prepstmt->execute();
                        ?>
                        <?php if ($prepstmt->rowCount() == 0) { ?>
                            <div class="alert alert-info text-center my-4">No data found</div>
                        <?php } else { ?>
                        <table class="table small">
                            <thead>
                                <tr>
                                    <th>Sl No</th>
                                    <th>Date, Return</th>
                                    <th>Vendor, Work Order</th>
                                    <th>Purpose</th>
                                    <th>Source</th>
                                    <th>Destination</th>
                                    <th>Representative</th>
                                    <th>Material Owner</th>
                                    <th>Items</th>
                                    <th>In</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                while ($row = $prepstmt->fetch(PDO::FETCH_OBJ)) {
                                    ?>
                                         <tr>
                                            <td><?php echo $row->HO_GATEPASS_SLNO; ?></td>
                                            <td><?php echo $row->HO_DATE; ?><br /><?php echo $row->HO_DATE_RETURN; ?></td>
                                            <td><?php echo $row->HO_VENDOR_CODE; ?><br /><?php echo $row->HO_ORDER; ?></td>
                                            <td><?php echo $row->HO_PURPOSE; ?></td>
                                            <td><?php echo $row->HO_SOURCE_FROM; ?></td>
                                            <td><?php echo $row->HO_DESTINATION_TO; ?></td>
                                            <td><?php echo $row->HO_REPRESENTATIVE_NAME; ?><br /><?php echo $row->HO_REPRESENTATIVE_ID; ?></td>
                                            <td><span class="badge rounded-pill text-bg-<?php echo ($row->HO_MATERIAL_OWNER === 'BHEL-HPVP') ? 'primary' : 'secondary'; ?>"><?php echo htmlspecialchars($row->HO_MATERIAL_OWNER); ?></span></td>
                                            <td>
                                                <?php
                                                $itemStmt = $dBhandler->prepare("SELECT HOI_ITEM_SLNO, HOI_ITEM_DESCRIPTION, HOI_ITEM_QUANTITY, HOI_ITEM_UOM FROM hpvp_osgp_item 
                                                WHERE HOI_GATEPASS_SLNO = ? ORDER BY HOI_ITEM_SLNO");
                                                $itemStmt->execute([$row->HO_GATEPASS_SLNO]);
                                                $items = $itemStmt->fetchAll(PDO::FETCH_OBJ);

                                                // Filter items first for return cases to only include those with active RETURN_APPROVED requests
                                                if (!($row->HO_STATUS == 'GATEPASS_APPROVED' && $row->HO_MATERIAL_OWNER == 'Vendor')) {
                                                    $filtered_items = [];
                                                    foreach ($items as $item) {
                                                        $reqStmt = $dBhandler->prepare("SELECT HORI_ITEM_RETURN_QUANTITY_REQUEST FROM HPVP_OSGP_RETURN_ITEM , HPVP_OSGP_RETURN
                                                        WHERE HOR_RETURN_SLNO = HORI_RETURN_SLNO AND HORI_GATEPASS_SLNO = ? AND HORI_ITEM_SLNO = ? AND HOR_STATUS = 'RETURN_APPROVED'");
                                                        $reqStmt->execute([$row->HO_GATEPASS_SLNO, $item->HOI_ITEM_SLNO]);
                                                        $reqRow = $reqStmt->fetch(PDO::FETCH_OBJ);
                                                        if ($reqRow) {
                                                            $item->reqRow = $reqRow; // Cache it to avoid querying again
                                                            $filtered_items[] = $item;
                                                        }
                                                    }
                                                    $items = $filtered_items;
                                                }

                                                foreach ($items as $idx => $item) {
                                                    $isLast = ($idx === count($items) - 1);
                                                    $divClass = $isLast ? "mb-2 pb-2" : "mb-2 border-bottom pb-2";
                                                    $desc = $item->HOI_ITEM_DESCRIPTION;
                                                    $desc_html = htmlspecialchars($desc);

                                                    if ($row->HO_STATUS == 'GATEPASS_APPROVED' && $row->HO_MATERIAL_OWNER == 'Vendor') {
                                                        ?>
                                                        <div class="<?php echo $divClass; ?>">
                                                            <div><strong><?php echo $desc_html; ?></strong></div>
                                                            <div class="small text-muted">
                                                                Qty: <?php echo htmlspecialchars($item->HOI_ITEM_QUANTITY) . " " . htmlspecialchars($item->HOI_ITEM_UOM); ?>
                                                            </div>
                                                        </div>
                                                        <?php
                                                    } else {
                                                        $retStmt = $dBhandler->prepare("SELECT IFNULL(SUM(HORI_ITEM_RETURN_QUANTITY),0) RET_QTY FROM HPVP_OSGP_RETURN_ITEM , HPVP_OSGP_RETURN
                                                            WHERE HOR_RETURN_SLNO = HORI_RETURN_SLNO AND HORI_GATEPASS_SLNO = ? AND HORI_ITEM_SLNO = ? AND HOR_STATUS IN ('SECURITY_IN_DONE', 'SECURITY_OUT_DONE')");
                                                        $retStmt->execute([$row->HO_GATEPASS_SLNO, $item->HOI_ITEM_SLNO]);
                                                        $retRow = $retStmt->fetch(PDO::FETCH_OBJ);
                                                        $retqty = $retRow ? $retRow->RET_QTY : 0;
                                                        
                                                        $remaining = $item->HOI_ITEM_QUANTITY - $retqty;
                                                        
                                                        // Retrieve cached reqRow
                                                        $reqRow = isset($item->reqRow) ? $item->reqRow : null;
                                                        $reqqty = $reqRow ? $reqRow->HORI_ITEM_RETURN_QUANTITY_REQUEST : $remaining;
                                                        ?>
                                                        <div class="<?php echo $divClass; ?>">
                                                            <div><strong><?php echo $desc_html; ?></strong></div>
                                                            <div class="small text-muted">
                                                                Qty: <?php echo htmlspecialchars($item->HOI_ITEM_QUANTITY) . " " . htmlspecialchars($item->HOI_ITEM_UOM); ?> | Returned: <?php echo htmlspecialchars($retqty); ?>
                                                                <?php if ($reqRow) { ?>
                                                                    <br/><span class="text-info font-weight-bold">Requested: <?php echo htmlspecialchars($reqqty); ?></span>
                                                                <?php } ?>
                                                            </div>
                                                            <input name="qty-<?php echo $item->HOI_ITEM_SLNO; ?>"
                                                                class="form-control form-control-sm mt-1" type="number" step="any"
                                                                value="<?php echo htmlspecialchars($reqqty); ?>" min="0"
                                                                max="<?php echo htmlspecialchars($reqqty); ?>" required
                                                                form="form-<?php echo $row->HO_GATEPASS_SLNO; ?>" />
                                                        </div>
                                                        <?php
                                                    }
                                                }
                                                ?>
                                            </td>
                                             <td>
                                                 <form id="form-<?php echo $row->HO_GATEPASS_SLNO; ?>" method="post"></form>
                                                 <input type="hidden" name="gatepass_id" value="<?php echo $row->HO_GATEPASS_SLNO; ?>" form="form-<?php echo $row->HO_GATEPASS_SLNO; ?>" />
                                                 <input name="remark" class="form-control form-control-sm mt-1"
                                                     type="text" placeholder="Remark" required form="form-<?php echo $row->HO_GATEPASS_SLNO; ?>" />
                                                    <?php if ($row->HOR_STATUS == "RETURN_APPROVED") { 
                                                        $retIdStmt = $dBhandler->prepare("SELECT HOR_RETURN_SLNO FROM HPVP_OSGP_RETURN WHERE HOR_GATEPASS_SLNO = ? AND HOR_STATUS = 'RETURN_APPROVED'");
                                                        $retIdStmt->execute([$row->HO_GATEPASS_SLNO]);
                                                        $retIdRow = $retIdStmt->fetch(PDO::FETCH_OBJ);
                                                        if ($retIdRow) {?>
                                                            <input type="hidden" name="return_id" value="<?php echo $retIdRow->HOR_RETURN_SLNO;?>" form="form-<?php echo $row->HO_GATEPASS_SLNO; ?>"/>
                                                            <?php
                                                        }
                                                    ?>                                                    
                                                    <?php } ?>    
                                                 <input class="btn btn-sm btn-primary my-2" type="submit" value="IN" form="form-<?php echo $row->HO_GATEPASS_SLNO; ?>" />
                                             </td>
                                         </tr>
                                    <?php
                                }
                                ?>
                            </tbody>
                        </table>
                        <?php } ?>

                    </div>
                </main>
                <footer class="py-4 bg-light mt-auto">
                    <?php include 'footer.php' ?>
                </footer>
            </div>
        </div>

        <!-- Full Text Modal -->
        <div class="modal fade" id="itemDetailModal" tabindex="-1" aria-labelledby="itemDetailModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="itemDetailModalLabel">Item Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="itemDetailModalBody" style="word-break: break-all; white-space: pre-line;">
                        <!-- Full text injected here -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="itemDetailCloseBtn">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"
            crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"
            crossorigin="anonymous"></script>
        <script src="js/datatables-simple-demo.js"></script>
        <script src="js/security-in.js"></script>
        <?php

    } catch (Exception $e) {
        echo $e;
    } finally {

    }
    ?>
</body>

</html>
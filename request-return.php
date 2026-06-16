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
        <title>Request Return</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
        <link href="css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    </head>
    <body class="sb-nav-fixed">
        <?php
            try{                
                $id = $_SESSION["HOU_ID"]; 
                $name = $_SESSION["HOU_NAME"];                
                $role = $_SESSION["HOU_ROLE"];

                if($role != "Vendor" && $role != "Administrator"){
                    header("Location: main.php");
                    exit();
                }
        ?>
        <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
            <?php include 'header.php'?>  
        </nav>
        <div id="layoutSidenav">
            <div id="layoutSidenav_nav">
                <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                    <?php include 'sidebar.php'?>
                </nav>
            </div>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-2 my-2">Material Gatepass (Request Return)</h1>
                        <?php
                            if (isset($_POST['submit-return'])) {
                                $gpslno = $_POST['submit-return'];
                                if (is_numeric($gpslno)) {
                                    $return_items = $_POST['return_items'] ?? [];
                                    if (!is_array($return_items)) {
                                        $return_items = [$return_items];
                                    }
                                    $has_selected_item = false;
                                    foreach ($return_items as $item_key) {
                                        $parts = explode("-", $item_key);
                                        if (count($parts) === 2 && is_numeric($parts[0]) && is_numeric($parts[1])) {
                                            if ($parts[0] == $gpslno) {
                                                $has_selected_item = true;
                                                break;
                                            }
                                        }
                                    }
                                    
                                    if ($has_selected_item) {
                                        // Insert into hpvp_osgp_return table

                                        $dBhandler->beginTransaction();
                                        try{
                                            $stmtReturnHdr = $dBhandler->prepare("INSERT INTO HPVP_OSGP_RETURN (HOR_GATEPASS_SLNO, HOR_STATUS) VALUES (?, 'RETURN_REQUESTED')");
                                            $stmtReturnHdr->execute([$gpslno]);
                                            $gpretslno = $dBhandler->lastInsertId();

                                            // Insert newly requested items
                                            $stmtReturnItm = $dBhandler->prepare("INSERT INTO HPVP_OSGP_RETURN_ITEM (HORI_GATEPASS_SLNO, HORI_RETURN_SLNO,HORI_ITEM_SLNO, HORI_ITEM_RETURN_QUANTITY_REQUEST) VALUES (?, ?, ?, ?)");

                                            foreach ($return_items as $item_key) {
                                                $parts = explode("-", $item_key);
                                                if (count($parts) === 2 && is_numeric($parts[0]) && is_numeric($parts[1])) {
                                                    if ($parts[0] == $gpslno) {
                                                        $itemslno = $parts[1];
                                                        $qty_key = 'return_qty-' . $item_key;
                                                        $qty = isset($_POST[$qty_key]) ? floatval($_POST[$qty_key]) : 0;
                                                        if ($qty > 0) {
                                                            $stmtReturnItm->execute([$gpslno, $gpretslno, $itemslno, $qty]);
                                                        }
                                                    }
                                                }
                                            }
                                            $dBhandler->commit();
                                            echo "<h4 class='text-primary'>Return request made for Gatepass #" . htmlspecialchars($gpslno) . "</h4>";
                                        } catch(Exception $e){
                                            $dBhandler->rollBack();
                                            echo "<h4 class='text-danger'>Error processing return request for Gatepass #" . htmlspecialchars($gpslno) . "</h4>";    
                                        }
                                    } else {
                                        echo "<h4 class='text-danger'>Please check at least one item to return for Gatepass #" . htmlspecialchars($gpslno) . "</h4>";
                                    }
                                }
                            }

                            $prepstmt = $dBhandler->prepare("SELECT HO_GATEPASS_SLNO, HO_REPRESENTATIVE_NAME, HO_REPRESENTATIVE_ID, HO_ORDER, HO_BHEL_OFFICIAL, HO_SOURCE_FROM, HO_DESTINATION_TO, 
                                                         DATE_FORMAT(HO_DATE,'%d.%m.%Y') HO_DATE, DATE_FORMAT(HO_DATE_RETURN,'%d.%m.%Y') HO_DATE_RETURN, HO_PURPOSE, HO_STATUS, HO_VENDOR_CODE, HO_MATERIAL_OWNER 
                                                         FROM HPVP_OSGP A 
                                                         WHERE A.HO_VENDOR_CODE = ? 
                                                           AND (
                                                             (A.HO_MATERIAL_OWNER = 'BHEL-HPVP' AND A.HO_STATUS IN ('SECURITY_OUT_DONE'))
                                                             OR 
                                                             (A.HO_MATERIAL_OWNER = 'Vendor' AND A.HO_STATUS IN ('SECURITY_IN_DONE'))
                                                           )
                                                           AND EXISTS (
                                                               SELECT 1 
                                                               FROM hpvp_osgp_item I 
                                                               WHERE I.HOI_GATEPASS_SLNO = A.HO_GATEPASS_SLNO 
                                                                 AND I.HOI_ITEM_QUANTITY > (
                                                                     SELECT IFNULL(SUM(B.HORI_ITEM_RETURN_QUANTITY), 0) 
                                                                     FROM HPVP_OSGP_RETURN_ITEM B 
                                                                     WHERE B.HORI_GATEPASS_SLNO = A.HO_GATEPASS_SLNO 
                                                                       AND B.HORI_ITEM_SLNO = I.HOI_ITEM_SLNO
                                                                 )
                                                           )
                                                           AND NOT EXISTS (
                                                                SELECT 1 FROM hpvp_osgp_return R
                                                                WHERE R.HOR_GATEPASS_SLNO = A.HO_GATEPASS_SLNO
                                                                AND R.HOR_RETURN_SLNO = (SELECT MAX(R1.HOR_RETURN_SLNO) FROM hpvp_osgp_return R1 WHERE R1.HOR_GATEPASS_SLNO = A.HO_GATEPASS_SLNO)
                                                                AND R.HOR_STATUS IN ('RETURN_REQUESTED','RETURN_RECOMMENDED','RETURN_APPROVED')
                                                        )
                                                         ORDER BY HO_GATEPASS_SLNO");
                            $prepstmt->execute([$id]);                              
                        ?>
                        <?php if ($prepstmt->rowCount() == 0) { ?>
                            <div class="alert alert-info text-center my-4">No data found</div>
                        <?php } else { ?>
                        <form method="post">
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
                                        <th>Items (Check to Return & Input Qty)</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        while($row = $prepstmt->fetch(PDO::FETCH_OBJ)){
                                            ?>
                                            <tr>
                                                <td><?php echo $row->HO_GATEPASS_SLNO;?></td>
                                                <td><?php echo $row->HO_DATE;?><br/><?php echo $row->HO_DATE_RETURN;?></td>
                                                <td><?php echo $row->HO_VENDOR_CODE;?><br/><?php echo $row->HO_ORDER;?></td>
                                                <td><?php echo $row->HO_PURPOSE;?></td>
                                                <td><?php echo $row->HO_SOURCE_FROM;?></td>
                                                <td><?php echo $row->HO_DESTINATION_TO;?></td>
                                                <td><?php echo $row->HO_REPRESENTATIVE_NAME;?><br/><?php echo $row->HO_REPRESENTATIVE_ID;?></td>
                                                <td><span class="badge rounded-pill text-bg-<?php echo ($row->HO_MATERIAL_OWNER === 'BHEL-HPVP') ? 'primary' : 'secondary'; ?>"><?php echo htmlspecialchars($row->HO_MATERIAL_OWNER); ?></span></td>
                                                <td>
                                                    <?php
                                                    $itemStmt = $dBhandler->prepare("SELECT HOI_ITEM_SLNO, HOI_ITEM_DESCRIPTION, HOI_ITEM_QUANTITY, HOI_ITEM_UOM FROM hpvp_osgp_item WHERE HOI_GATEPASS_SLNO = ? ORDER BY HOI_ITEM_SLNO");
                                                    $itemStmt->execute([$row->HO_GATEPASS_SLNO]);
                                                    $items = $itemStmt->fetchAll(PDO::FETCH_OBJ);
                                                    
                                                    foreach ($items as $idx => $item) {
                                                        $isLast = ($idx === count($items) - 1);
                                                        $divClass = $isLast ? "mb-3 pb-2" : "mb-3 border-bottom pb-2";
                                                        $retStmt = $dBhandler->prepare("SELECT IFNULL(SUM(HORI_ITEM_RETURN_QUANTITY),0) RET_QTY FROM HPVP_OSGP_RETURN_ITEM WHERE HORI_GATEPASS_SLNO = ? AND HORI_ITEM_SLNO = ? ");
                                                        $retStmt->execute([$row->HO_GATEPASS_SLNO, $item->HOI_ITEM_SLNO]);
                                                        $retRow = $retStmt->fetch(PDO::FETCH_OBJ);
                                                        $retqty = $retRow ? $retRow->RET_QTY : 0;
                                                        
                                                        $remaining = $item->HOI_ITEM_QUANTITY - $retqty;
                                                        $item_key = $row->HO_GATEPASS_SLNO . '-' . $item->HOI_ITEM_SLNO;
                                                        ?>
                                                        <div class="<?php echo $divClass; ?>">
                                                            <?php if ($remaining > 0) { ?>
                                                                <div class="form-check">
                                                                    <input type="checkbox" name="return_items[]" value="<?php echo $item_key; ?>" class="form-check-input" id="chk-<?php echo $item_key; ?>" />
                                                                    <label class="form-check-label" for="chk-<?php echo $item_key; ?>">
                                                                        <strong><?php echo htmlspecialchars($item->HOI_ITEM_DESCRIPTION); ?></strong>
                                                                    </label>
                                                                </div>
                                                                <div class="small text-muted ms-4">
                                                                    Qty: <?php echo htmlspecialchars($item->HOI_ITEM_QUANTITY) . " " . htmlspecialchars($item->HOI_ITEM_UOM); ?> | Remaining: <?php echo htmlspecialchars($remaining) . " " . htmlspecialchars($item->HOI_ITEM_UOM); ?>
                                                                </div>
                                                                <div class="ms-4 mt-1 row g-2 align-items-center">
                                                                    <div class="col-auto">
                                                                        <span class="small text-muted">Return Qty:</span>
                                                                    </div>
                                                                    <div class="col-auto">
                                                                        <input type="number" name="return_qty-<?php echo $item_key; ?>" 
                                                                               class="form-control form-control-sm" style="width: 100px;" 
                                                                               value="<?php echo htmlspecialchars($remaining); ?>" 
                                                                               min="0.01" step="any" max="<?php echo htmlspecialchars($remaining); ?>" required />
                                                                    </div>
                                                                </div>
                                                            <?php } else { ?>
                                                                <div class="ms-1">
                                                                    <span class="text-muted"><del><strong><?php echo htmlspecialchars($item->HOI_ITEM_DESCRIPTION); ?></strong></del></span>
                                                                    <span class="badge bg-success ms-2">Fully Returned</span>
                                                                </div>
                                                                <div class="small text-muted ms-1">
                                                                    Qty: <?php echo htmlspecialchars($item->HOI_ITEM_QUANTITY) . " " . htmlspecialchars($item->HOI_ITEM_UOM); ?> | Returned: <?php echo htmlspecialchars($retqty) . " " . htmlspecialchars($item->HOI_ITEM_UOM); ?>
                                                                </div>
                                                            <?php } ?>
                                                        </div>
                                                        <?php
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <button type="submit" name="submit-return" value="<?php echo $row->HO_GATEPASS_SLNO;?>" class="btn btn-primary btn-sm">Submit</button>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                    ?>                                        
                                </tbody>
                            </table>   
                        </form>
                        <?php } ?>
                    </div>       
                </main>
                <footer class="py-4 bg-light mt-auto">
                    <?php include 'footer.php'?>  
                </footer>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
        <script src="js/datatables-simple-demo.js"></script>
        <?php
            
            }catch(Exception $e){
                echo $e;
            }finally{
                
            }
        ?> 
    </body>
</html>

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
        <title>Approve</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
        <link href="css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    </head>
    <body class="sb-nav-fixed">
        <?php
            try{
                
                $id = $_SESSION["HOU_ID"]; 
                $role = $_SESSION["HOU_ROLE"];
                if($role != "Recommender" && $role != "Administrator"){
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
                        <h1 class="mt-2 my-2">Gatepass Recommendation</h1>
                        <?php
                            if (isset($_POST['submit-gatepass'])) {
                                $gpslno = $_POST['submit-gatepass'];
                                $actionName = "action-" . $gpslno;
                                $remarkName = "remark-" . $gpslno;
                                $returnIdName = "retid-" . $gpslno;

                                if (isset($_POST[$actionName])) {
                                    $actionValue = $_POST[$actionName];
                                    $recStatus = (str_contains($actionValue, "RECOMMENDED")) ? 'RECOMMENDED' : 'REJECTED';

                                    
                                    $remarkValue = $_POST[$remarkName];                                    

                                    if($actionValue == 'GATEPASS_RECOMMENDED' || $actionValue == 'GATEPASS_REJECTED'){
                                        $prepstmtupd = $dBhandler->prepare("UPDATE HPVP_OSGP SET HO_STATUS = ?, HO_RECOMMENDER_STATUS = ?, HO_RECOMMENDER_REMARK = ?, HO_RECOMMENDER_TIME = NOW() 
                                            WHERE HO_GATEPASS_SLNO = ?");
                                        $prepstmtupd->execute([$actionValue, $recStatus, $remarkValue, $gpslno]); 
                                        if ($prepstmtupd->rowCount() > 0) {
                                        echo "<h4 class='text-".(str_contains($actionValue,"RECOMMENDED") ? "success" : "danger")."'>Gatepass #" . $gpslno . " " . (str_contains($actionValue,"RECOMMENDED") ? "Recommended" : "Rejected") . "</h4>";
                                        } else {
                                            echo "<h4 class='text-danger'>Failed to update Gatepass #" . $gpslno . "</h4>";
                                        }
                                    }
                                    if ($actionValue == 'RETURN_RECOMMENDED' || $actionValue == 'RETURN_REJECTED') {
                                        $returnIdValue = $_POST[$returnIdName];
                                        $prepstmtupdReturn = $dBhandler->prepare("UPDATE HPVP_OSGP_RETURN SET HOR_STATUS = ?, HOR_RECOMMENDER = ? , HOR_RECOMMENDER_STATUS = ?, HOR_RECOMMENDER_REMARK = ?, HOR_RECOMMENDER_TIME = NOW() 
                                            WHERE HOR_GATEPASS_SLNO = ? AND HOR_STATUS = 'RETURN_REQUESTED' AND HOR_RETURN_SLNO = ?");
                                        $prepstmtupdReturn->execute([$actionValue, $id, $recStatus, $remarkValue, $gpslno, $returnIdValue]);
                                        if ($prepstmtupdReturn->rowCount() > 0) {
                                            echo "<h4 class='text-".(str_contains($actionValue,"RECOMMENDED") ? "success" : "danger")."'>Return for Gatepass #" . $gpslno . " " . (str_contains($actionValue,"RECOMMENDED") ? "Recommended" : "Rejected") . "</h4>";
                                        } else {
                                            echo "<h4 class='text-danger'>Failed to update Return for Gatepass #" . $gpslno . "</h4>";
                                        }
                                    }
                                    
                                }                                                            
                            }

                        ?>

                        <?php
                            $prepstmt = $dBhandler->prepare("SELECT HO_GATEPASS_SLNO, HO_REPRESENTATIVE_NAME, HO_REPRESENTATIVE_ID, HO_ORDER, HO_BHEL_OFFICIAL, HO_SOURCE_FROM, HO_DESTINATION_TO, 
                                DATE_FORMAT(HO_DATE,'%d.%m.%Y') HO_DATE, DATE_FORMAT(HO_DATE_RETURN,'%d.%m.%Y') HO_DATE_RETURN, HO_PURPOSE, HO_STATUS, HO_VENDOR_CODE , 
                                HO_MATERIAL_OWNER, HOR_STATUS
                                FROM hpvp_osgp 
                                LEFT JOIN hpvp_osgp_return ON HO_GATEPASS_SLNO = HOR_GATEPASS_SLNO
                                WHERE (HO_STATUS = 'GATEPASS_REQUESTED' OR HOR_STATUS = 'RETURN_REQUESTED') AND HO_BHEL_OFFICIAL = ?");
                            $prepstmt->execute([$id]);                            
                        ?>
                        <?php if ($prepstmt->rowCount() == 0) { ?>
                            <div class="alert alert-info text-center my-4">No data found</div>
                        <?php } else { ?>
                            <form name="f1" method="post">
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
                                    <th>Items</th>
                                    <th>Material Owner</th>                                    
                                    <th>Action</th>
                                </tr>
                            </thead><!--
                            <tfoot>
                                <tr>
                                    <th>Sl No</th>
                                    <th>Date, Return</th>
                                    <th>Work Order</th>
                                    <th>Purpose</th>
                                    <th>Source From</th>
                                    <th>Destination To</th>
                                    <th>Representative</th>
                                    <th>Items</th>
                                    <th>Status</th>
                                </tr>
                            </tfoot>-->
                            <tbody>
                                <?php
                                    while($row = $prepstmt->fetch(PDO::FETCH_OBJ)){ 
                                        ?>
                                        <tr>
                                            <td><?php echo $row->HO_GATEPASS_SLNO ; ?></td>
                                            <td><?php echo $row->HO_DATE ; ?><br/><?php echo $row->HO_DATE_RETURN ; ?></td>
                                            <td><?php echo $row->HO_VENDOR_CODE;?><br/><?php echo $row->HO_ORDER;?></td>
                                            <td><?php echo $row->HO_PURPOSE;?></td>
                                            <td><?php echo $row->HO_SOURCE_FROM;?></td>
                                            <td><?php echo $row->HO_DESTINATION_TO;?></td>
                                            <td><?php echo $row->HO_REPRESENTATIVE_NAME ; ?><br/><?php echo $row->HO_REPRESENTATIVE_ID ; ?></td>
                                            <td>
                                                <?php
                                                if ($row->HOR_STATUS == "RETURN_REQUESTED") {
                                                    //List the items in the Gatepass
                                                    $itemStmt = $dBhandler->prepare("SELECT HOI_ITEM_SLNO, HOI_ITEM_DESCRIPTION, HOI_ITEM_QUANTITY, HOI_ITEM_UOM FROM hpvp_osgp_item WHERE HOI_GATEPASS_SLNO = ? ORDER BY HOI_ITEM_SLNO");
                                                    $itemStmt->execute([$row->HO_GATEPASS_SLNO]);
                                                    $item_rows = $itemStmt->fetchAll(PDO::FETCH_OBJ);
                                                    foreach ($item_rows as $item_row) {
                                                        // Calculate returned quantity for the item
                                                        $retStmt = $dBhandler->prepare("SELECT IFNULL(SUM(HORI_ITEM_RETURN_QUANTITY),0) RET_QTY FROM hpvp_osgp_return_item, hpvp_osgp_return WHERE HOR_RETURN_SLNO = HORI_RETURN_SLNO AND HORI_GATEPASS_SLNO = ? AND HORI_ITEM_SLNO = ? AND (HOR_STATUS IS NULL OR HOR_STATUS <> 'RETURN_REQUESTED')");
                                                        $retStmt->execute([$row->HO_GATEPASS_SLNO, $item_row->HOI_ITEM_SLNO]);
                                                        $retRow = $retStmt->fetch(PDO::FETCH_OBJ);
                                                        $retqty = $retRow ? $retRow->RET_QTY : 0;

                                                        $reqStmt = $dBhandler->prepare("SELECT HORI_ITEM_RETURN_QUANTITY_REQUEST FROM HPVP_OSGP_RETURN_ITEM, HPVP_OSGP_RETURN WHERE HOR_RETURN_SLNO = HORI_RETURN_SLNO AND HORI_GATEPASS_SLNO = ? AND HORI_ITEM_SLNO = ? AND HOR_STATUS = 'RETURN_REQUESTED'");
                                                        $reqStmt->execute([$row->HO_GATEPASS_SLNO, $item_row->HOI_ITEM_SLNO]);
                                                        $reqRow = $reqStmt->fetch(PDO::FETCH_OBJ);

                                                        echo htmlspecialchars($item_row->HOI_ITEM_DESCRIPTION) . " (" . htmlspecialchars($item_row->HOI_ITEM_QUANTITY) . " " . htmlspecialchars($item_row->HOI_ITEM_UOM) . ")";
                                                        if ($reqRow) {
                                                            echo "<br/><span class='text-info font-weight-bold'>Return Requested: " . htmlspecialchars($reqRow->HORI_ITEM_RETURN_QUANTITY_REQUEST) . " " . htmlspecialchars($item_row->HOI_ITEM_UOM) . "</span><br/>";
                                                        } else {
                                                            echo "<br/>";
                                                        }
                                                    }
                                                } else {
                                                    $itemStmt = $dBhandler->prepare("SELECT HOI_ITEM_DESCRIPTION, HOI_ITEM_QUANTITY, HOI_ITEM_UOM FROM hpvp_osgp_item WHERE HOI_GATEPASS_SLNO = ? ORDER BY HOI_ITEM_SLNO");
                                                    $itemStmt->execute([$row->HO_GATEPASS_SLNO]);
                                                    $item_rows = $itemStmt->fetchAll(PDO::FETCH_OBJ);
                                                    $item_output_plain = [];
                                                    foreach ($item_rows as $item_row) {
                                                        $item_output_plain[] = $item_row->HOI_ITEM_DESCRIPTION . " - " . $item_row->HOI_ITEM_QUANTITY . " " . $item_row->HOI_ITEM_UOM;
                                                    }
                                                    $combined_full_text = implode("\n", $item_output_plain);
                                                    echo nl2br(htmlspecialchars($combined_full_text));
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <span class="badge rounded-pill text-bg-<?php echo ($row->HO_MATERIAL_OWNER === 'BHEL-HPVP') ? 'primary' : 'secondary'; ?>"><?php echo $row->HO_MATERIAL_OWNER;?></span>               
                                            </td>
                                            <td>
                                                <select name="action-<?php echo $row->HO_GATEPASS_SLNO;?>" class="form-select form-select-sm">
                                                    <?php if ($row->HOR_STATUS == "RETURN_REQUESTED") { ?>
                                                        <option value="RETURN_RECOMMENDED">Recommend</option>
                                                        <option value="RETURN_REJECTED">Reject</option>
                                                    <?php } else { ?>
                                                        <option value="<?php echo ($row->HO_STATUS == "GATEPASS_REQUESTED") ? "GATEPASS_RECOMMENDED" : "GATEPASS_REQUESTED";?>">Recommend</option>
                                                        <option value="<?php echo ($row->HO_STATUS == "GATEPASS_REQUESTED") ? "GATEPASS_REJECTED" : "GATEPASS_REQUESTED";?>">Reject</option>
                                                    <?php } ?>
                                                </select>
                                                <input name="remark-<?php echo $row->HO_GATEPASS_SLNO;?>" class="form-control form-control-sm my-2" type="text" placeholder="Remark"/>
                                                <?php if ($row->HOR_STATUS == "RETURN_REQUESTED") { 
                                                        $retIdStmt = $dBhandler->prepare("SELECT HOR_RETURN_SLNO FROM HPVP_OSGP_RETURN WHERE HOR_GATEPASS_SLNO = ? AND HOR_STATUS = 'RETURN_REQUESTED'");
                                                        $retIdStmt->execute([$row->HO_GATEPASS_SLNO]);
                                                        $retIdRow = $retIdStmt->fetch(PDO::FETCH_OBJ);
                                                        if ($retIdRow) {?>
                                                            <input type="hidden" name="retid-<?php echo $row->HO_GATEPASS_SLNO;?>" value="<?php echo $retIdRow->HOR_RETURN_SLNO;?>"/>
                                                            <?php
                                                        }
                                                    ?>
                                                    
                                                <?php } ?>
                                                 <button type="submit" name="submit-gatepass" value="<?php echo $row->HO_GATEPASS_SLNO;?>" class="btn btn-primary btn-sm w-100">Submit</button>
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

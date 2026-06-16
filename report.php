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
    <title>Report</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="css/history.css" rel="stylesheet" />
    <link href="css/report.css" rel="stylesheet" />
</head>

<body class="sb-nav-fixed">
    <?php
    try {
        if (isset($_SESSION["HOU_ID"])) {

            $id = $_SESSION["HOU_ID"];
            $name = $_SESSION["HOU_NAME"];

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
                            <h1 class="mt-2 my-2">Summary Report</h1>
                            <!-- Category A -->
                            <div class="row g-3 mb-3 align-items-stretch">

                                <div class="col-md-2">
                                    <div class="kpi-box bg-primary text-white h4 d-flex justify-content-center align-items-center">
                                        BHEL-HPVP Material
                                    </div>
                                </div>

                                <div class="col">
                                    <div class="kpi-box">
                                        <div class="kpi-title">Generated</div>
                                        <?php 
                                            $prepstmt = $dBhandler->prepare("SELECT COUNT(*) hpvp_tot FROM hpvp_osgp WHERE HO_MATERIAL_OWNER = ?");
                                            $prepstmt->execute(['BHEL-HPVP']);
                                            $result = $prepstmt->fetch(PDO::FETCH_ASSOC);
                                        ?>    
                                        <div class="kpi-value text-primary"><?php echo $result['hpvp_tot']; ?></div>
                                    </div>
                                </div>

                                <div class="col">
                                    <div class="kpi-box">
                                        <div class="kpi-title">Rejected</div>
                                        <?php 
                                            $prepstmt = $dBhandler->prepare("SELECT COUNT(*) hpvp_rej FROM hpvp_osgp WHERE HO_MATERIAL_OWNER = ? AND HO_STATUS = 'GATEPASS_REJECTED'");
                                            $prepstmt->execute(['BHEL-HPVP']);
                                            $result = $prepstmt->fetch(PDO::FETCH_ASSOC);
                                        ?>    
                                        <div class="kpi-value text-danger"><?php echo $result['hpvp_rej']; ?></div>
                                    </div>
                                </div>

                                <div class="col">
                                    <div class="kpi-box">
                                        <div class="kpi-title">Security Out</div>
                                        <?php 
                                            $prepstmt = $dBhandler->prepare("SELECT COUNT(*) hpvp_sec_out FROM hpvp_osgp WHERE HO_MATERIAL_OWNER = ? AND HO_STATUS = 'SECURITY_OUT_DONE'");
                                            $prepstmt->execute(['BHEL-HPVP']);
                                            $result = $prepstmt->fetch(PDO::FETCH_ASSOC);
                                        ?>  
                                        <div class="kpi-value text-warning"><?php echo $result['hpvp_sec_out']; ?></div>
                                    </div>
                                </div>

                                <div class="col">
                                    <div class="kpi-box">
                                        <div class="kpi-title">Partial Return</div>
                                        <?php 
                                            $prepstmt = $dBhandler->prepare("SELECT * FROM (SELECT HOI_GATEPASS_SLNO, SUM(A.DIFF) DEFICIT FROM(
                                                SELECT HOI_GATEPASS_SLNO, HOI_ITEM_SLNO, HOI_ITEM_DESCRIPTION, HOI_ITEM_QUANTITY, HOI_ITEM_UOM, HORI_GATEPASS_SLNO, HORI_ITEM_SLNO, RET_QTY, IFNULL(HOI_ITEM_QUANTITY,0) - IFNULL(RET_QTY,0) DIFF
                                                FROM hpvp_osgp_item
                                                LEFT JOIN (SELECT HORI_GATEPASS_SLNO, HORI_ITEM_SLNO, SUM(IFNULL(HORI_ITEM_RETURN_QUANTITY,0)) RET_QTY FROM hpvp_osgp_return_item 
                                                GROUP BY HORI_GATEPASS_SLNO, HORI_ITEM_SLNO) B ON B.HORI_GATEPASS_SLNO = HOI_GATEPASS_SLNO AND B.HORI_ITEM_SLNO = HOI_ITEM_SLNO) A GROUP BY HOI_GATEPASS_SLNO) X, hpvp_osgp GP
                                                WHERE X.HOI_GATEPASS_SLNO = GP.HO_GATEPASS_SLNO AND GP.HO_MATERIAL_OWNER = 'BHEL-HPVP'
                                                AND X.DEFICIT > 0 AND HO_STATUS = 'SECURITY_OUT_DONE'");
                                            $prepstmt->execute();
                                            $result = $prepstmt->fetchAll(PDO::FETCH_ASSOC);
                                        ?> 
                                        <div class="kpi-value text-info"><?php echo count($result); ?></div>
                                    </div>
                                </div>

                                <div class="col">
                                    <div class="kpi-box">
                                        <div class="kpi-title">Total Return</div>
                                        <?php 
                                            $prepstmt = $dBhandler->prepare("SELECT * FROM (SELECT HOI_GATEPASS_SLNO, SUM(A.DIFF) DEFICIT FROM(
                                                SELECT HOI_GATEPASS_SLNO, HOI_ITEM_SLNO, HOI_ITEM_DESCRIPTION, HOI_ITEM_QUANTITY, HOI_ITEM_UOM, HORI_GATEPASS_SLNO, HORI_ITEM_SLNO, RET_QTY, IFNULL(HOI_ITEM_QUANTITY,0) - IFNULL(RET_QTY,0) DIFF
                                                FROM hpvp_osgp_item
                                                LEFT JOIN (SELECT HORI_GATEPASS_SLNO, HORI_ITEM_SLNO, SUM(IFNULL(HORI_ITEM_RETURN_QUANTITY,0)) RET_QTY FROM hpvp_osgp_return_item 
                                                GROUP BY HORI_GATEPASS_SLNO, HORI_ITEM_SLNO) B ON B.HORI_GATEPASS_SLNO = HOI_GATEPASS_SLNO AND B.HORI_ITEM_SLNO = HOI_ITEM_SLNO) A GROUP BY HOI_GATEPASS_SLNO) X, hpvp_osgp GP
                                                WHERE X.HOI_GATEPASS_SLNO = GP.HO_GATEPASS_SLNO AND GP.HO_MATERIAL_OWNER = 'BHEL-HPVP'
                                                AND X.DEFICIT = 0 AND X.DEFICIT = 0");
                                            $prepstmt->execute();
                                            $result = $prepstmt->fetchAll(PDO::FETCH_ASSOC);
                                        ?>                                          
                                        <div class="kpi-value text-success"><?php echo count($result); ?></div>
                                    </div>
                                </div>

                            </div>

                            <!-- Category B -->
                            <div class="row g-3 align-items-stretch">

                                <div class="col-md-2">
                                    <div class="kpi-box bg-secondary text-white h4 d-flex justify-content-center align-items-center">
                                        Vendor Material
                                    </div>
                                </div>

                                <div class="col">
                                    <div class="kpi-box">
                                        <div class="kpi-title">Generated</div>
                                        <?php 
                                            $prepstmt = $dBhandler->prepare("SELECT COUNT(*) vend_tot FROM hpvp_osgp WHERE HO_MATERIAL_OWNER = ?");
                                            $prepstmt->execute(['Vendor']);
                                            $result = $prepstmt->fetch(PDO::FETCH_ASSOC);
                                        ?>  
                                        <div class="kpi-value text-primary"><?php echo $result['vend_tot']; ?></div>
                                    </div>
                                </div>

                                <div class="col">
                                    <div class="kpi-box">
                                        <div class="kpi-title">Rejected</div>
                                        <?php 
                                            $prepstmt = $dBhandler->prepare("SELECT COUNT(*) vend_rej FROM hpvp_osgp WHERE HO_MATERIAL_OWNER = ? AND HO_STATUS = 'GATEPASS_REJECTED'");
                                            $prepstmt->execute(['Vendor']);
                                            $result = $prepstmt->fetch(PDO::FETCH_ASSOC);
                                        ?>  
                                        <div class="kpi-value text-danger"><?php echo $result['vend_rej']; ?></div>
                                    </div>
                                </div>

                                <div class="col">
                                    <div class="kpi-box">
                                        <div class="kpi-title">Security In</div>
                                        <?php 
                                            $prepstmt = $dBhandler->prepare("SELECT COUNT(*) vend_sec_in FROM hpvp_osgp WHERE HO_MATERIAL_OWNER = ? AND HO_STATUS = 'SECURITY_IN_DONE'");
                                            $prepstmt->execute(['Vendor']);
                                            $result = $prepstmt->fetch(PDO::FETCH_ASSOC);
                                        ?>  
                                        <div class="kpi-value text-warning"><?php echo $result['vend_sec_in']; ?></div>
                                    </div>
                                </div>

                                <div class="col">
                                    <div class="kpi-box">
                                        <div class="kpi-title">Partial Return</div>
                                        <?php 
                                            $prepstmt = $dBhandler->prepare("SELECT * FROM (SELECT HOI_GATEPASS_SLNO, SUM(A.DIFF) DEFICIT FROM(
                                                SELECT HOI_GATEPASS_SLNO, HOI_ITEM_SLNO, HOI_ITEM_DESCRIPTION, HOI_ITEM_QUANTITY, HOI_ITEM_UOM, HORI_GATEPASS_SLNO, HORI_ITEM_SLNO, RET_QTY, IFNULL(HOI_ITEM_QUANTITY,0) - IFNULL(RET_QTY,0) DIFF
                                                FROM hpvp_osgp_item
                                                LEFT JOIN (SELECT HORI_GATEPASS_SLNO, HORI_ITEM_SLNO, SUM(IFNULL(HORI_ITEM_RETURN_QUANTITY,0)) RET_QTY FROM hpvp_osgp_return_item 
                                                GROUP BY HORI_GATEPASS_SLNO, HORI_ITEM_SLNO) B ON B.HORI_GATEPASS_SLNO = HOI_GATEPASS_SLNO AND B.HORI_ITEM_SLNO = HOI_ITEM_SLNO) A GROUP BY HOI_GATEPASS_SLNO) X, hpvp_osgp GP
                                                WHERE X.HOI_GATEPASS_SLNO = GP.HO_GATEPASS_SLNO AND GP.HO_MATERIAL_OWNER = 'Vendor'
                                                AND X.DEFICIT > 0 AND HO_STATUS = 'SECURITY_IN_DONE'");
                                            $prepstmt->execute();
                                            $result = $prepstmt->fetchAll(PDO::FETCH_ASSOC);
                                        ?> 
                                        <div class="kpi-value text-info"><?php echo count($result); ?></div>
                                    </div>
                                </div>

                                <div class="col">
                                    <div class="kpi-box">
                                        <div class="kpi-title">Total Return</div>
                                        <?php 
                                            $prepstmt = $dBhandler->prepare("SELECT * FROM (SELECT HOI_GATEPASS_SLNO, SUM(A.DIFF) DEFICIT FROM(
                                                SELECT HOI_GATEPASS_SLNO, HOI_ITEM_SLNO, HOI_ITEM_DESCRIPTION, HOI_ITEM_QUANTITY, HOI_ITEM_UOM, HORI_GATEPASS_SLNO, HORI_ITEM_SLNO, RET_QTY, IFNULL(HOI_ITEM_QUANTITY,0) - IFNULL(RET_QTY,0) DIFF
                                                FROM hpvp_osgp_item
                                                LEFT JOIN (SELECT HORI_GATEPASS_SLNO, HORI_ITEM_SLNO, SUM(IFNULL(HORI_ITEM_RETURN_QUANTITY,0)) RET_QTY FROM hpvp_osgp_return_item 
                                                GROUP BY HORI_GATEPASS_SLNO, HORI_ITEM_SLNO) B ON B.HORI_GATEPASS_SLNO = HOI_GATEPASS_SLNO AND B.HORI_ITEM_SLNO = HOI_ITEM_SLNO) A GROUP BY HOI_GATEPASS_SLNO) X, hpvp_osgp GP
                                                WHERE X.HOI_GATEPASS_SLNO = GP.HO_GATEPASS_SLNO AND GP.HO_MATERIAL_OWNER = 'Vendor'
                                                AND X.DEFICIT = 0 AND X.DEFICIT = 0");
                                            $prepstmt->execute();
                                            $result = $prepstmt->fetchAll(PDO::FETCH_ASSOC);
                                        ?>    
                                        <div class="kpi-value text-success"><?php echo count($result); ?></div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </main>
                    <footer class="py-4 bg-light mt-auto">
                        <?php include 'footer.php' ?>
                    </footer>
                </div>
            </div>
            
            <!-- Item Trail Modal -->
            <div class="modal fade" id="itemTrailModal" tabindex="-1" aria-labelledby="itemTrailModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="itemTrailModalLabel">Item Movement Trail</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <strong>Item Description:</strong>
                                <div id="modalItemDescription"></div>
                            </div>
                            <div class="mb-3">
                                <strong>Original Quantity:</strong>
                                <div id="modalItemQty"></div>
                            </div>
                            <hr/>
                            <div>
                                <strong>Physical Movement Trail:</strong>
                                <div id="modalItemTrail" class="mt-2 p-2 bg-light border rounded"></div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gatepass Trail Modal -->
            <div class="modal fade" id="gatepassTrailModal" tabindex="-1" aria-labelledby="gatepassTrailModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="gatepassTrailModalLabel">Gatepass Lifecycle Trail</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div id="gatepassTrailLoading" class="text-center p-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                            <div id="gatepassTrailContent">
                                <!-- Dynamic timeline goes here -->
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>

            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
            <script src="js/scripts.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
            <script src="js/datatables-simple-demo.js"></script>
            <script src="js/history.js"></script>                
            <?php
        } else {
            header("Location: index.php?err=Session Expired");
        }
    } catch (Exception $e) {
        echo $e;
    } finally {

    }
    ?>
</body>

</html>
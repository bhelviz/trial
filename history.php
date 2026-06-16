<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>History</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="css/history.css" rel="stylesheet" />
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
                            <h1 class="mt-2 my-2">Gatepass History</h1>
                            <?php
                            if ($role == "Vendor") {
                                $prepstmt = $dBhandler->prepare("SELECT GP.HO_GATEPASS_SLNO, GP.HO_REPRESENTATIVE_NAME, GP.HO_REPRESENTATIVE_ID, GP.HO_ORDER, GP.HO_BHEL_OFFICIAL, GP.HO_SOURCE_FROM, GP.HO_DESTINATION_TO, 
                                    DATE_FORMAT(GP.HO_DATE,'%d.%m.%Y') HO_DATE, DATE_FORMAT(GP.HO_DATE_RETURN,'%d.%m.%Y') HO_DATE_RETURN, GP.HO_PURPOSE, GP.HO_APPROVER_STATUS, GP.HO_RECOMMENDER_STATUS,
                                    GP.HO_MATERIAL_OWNER, GP.HO_STATUS, DATE_FORMAT(GP.HO_TIME_IN_OUT,'%d.%m.%Y %H:%i') SECURITY_IN_TIME, GP.HO_TIME_IN_OUT_BY,
                                    U1.HOU_NAME AS BHEL_OFFICIAL_NAME, U2.HOU_NAME AS SECURITY_OFFICER_NAME,
                                    (SELECT GROUP_CONCAT(CONCAT(HOI_ITEM_DESCRIPTION, ':::', HOI_ITEM_QUANTITY, ':::', HOI_ITEM_UOM) ORDER BY HOI_ITEM_SLNO SEPARATOR '|||') 
                                    FROM hpvp_osgp_item WHERE HOI_GATEPASS_SLNO = GP.HO_GATEPASS_SLNO) AS ITEMS_LIST,
                                    (SELECT GROUP_CONCAT(
                                        CONCAT(
                                            IFNULL(B.HORI_ITEM_RETURN_QUANTITY, 0),
                                            '::',
                                            IFNULL(B.HOR_RETURN_DATE, '')
                                        )
                                        ORDER BY A.HOI_ITEM_SLNO
                                        SEPARATOR '||'
                                    )
                                    FROM hpvp_osgp_item A
                                    LEFT JOIN (
                                        SELECT HOR_GATEPASS_SLNO, HORI_ITEM_SLNO, SUM(HORI_ITEM_RETURN_QUANTITY) HORI_ITEM_RETURN_QUANTITY,
                                            GROUP_CONCAT(
                                                CONCAT(
                                                    HORI_ITEM_RETURN_QUANTITY,
                                                    ' ',
                                                    HOI_ITEM_UOM,
                                                    ' on ',
                                                    DATE_FORMAT(HOR_RETURN_DATE, '%d.%m.%Y %H:%i'),
                                                    ' by ',
                                                    HOU_NAME, ' (',HOU_ID,')'
                                                )
                                                ORDER BY HOR_RETURN_DATE
                                                SEPARATOR ', '
                                            ) AS HOR_RETURN_DATE 
                                            FROM hpvp_osgp_return, hpvp_osgp_return_item , hpvp_osgp_item, HPVP_OSGP_USER
                                            WHERE HOR_RETURN_SLNO = HORI_RETURN_SLNO AND HOR_GATEPASS_SLNO = HOI_GATEPASS_SLNO AND HOI_ITEM_SLNO = HORI_ITEM_SLNO
                                            AND IFNULL(HORI_ITEM_RETURN_QUANTITY,0) > 0 AND HOR_SECURITY_IN_OUT_BY = HOU_ID
                                            GROUP BY HOR_GATEPASS_SLNO, HORI_ITEM_SLNO
                                    ) B ON A.HOI_GATEPASS_SLNO = B.HOR_GATEPASS_SLNO AND A.HOI_ITEM_SLNO = B.HORI_ITEM_SLNO
                                    WHERE A.HOI_GATEPASS_SLNO = GP.HO_GATEPASS_SLNO
                                    ) AS RETURNS_LIST
                                    FROM HPVP_OSGP GP 
                                    LEFT JOIN HPVP_OSGP_USER U1 ON GP.HO_BHEL_OFFICIAL = U1.HOU_ID
                                    LEFT JOIN HPVP_OSGP_USER U2 ON GP.HO_TIME_IN_OUT_BY = U2.HOU_ID 
                                    WHERE GP.HO_VENDOR_CODE = ?
                                    ORDER BY GP.HO_GATEPASS_SLNO DESC
                                    ");
                                $prepstmt->execute([$id]);
                            } else {
                                $prepstmt = $dBhandler->prepare("SELECT GP.HO_GATEPASS_SLNO, GP.HO_REPRESENTATIVE_NAME, GP.HO_REPRESENTATIVE_ID, GP.HO_ORDER, GP.HO_BHEL_OFFICIAL, GP.HO_SOURCE_FROM, GP.HO_DESTINATION_TO, 
                                    DATE_FORMAT(GP.HO_DATE,'%d.%m.%Y') HO_DATE, DATE_FORMAT(GP.HO_DATE_RETURN,'%d.%m.%Y') HO_DATE_RETURN, GP.HO_PURPOSE, GP.HO_APPROVER_STATUS, GP.HO_RECOMMENDER_STATUS,
                                    GP.HO_MATERIAL_OWNER, GP.HO_STATUS, DATE_FORMAT(GP.HO_TIME_IN_OUT,'%d.%m.%Y %H:%i') SECURITY_IN_TIME, GP.HO_TIME_IN_OUT_BY,
                                    U1.HOU_NAME AS BHEL_OFFICIAL_NAME, U2.HOU_NAME AS SECURITY_OFFICER_NAME,
                                    (SELECT GROUP_CONCAT(CONCAT(HOI_ITEM_DESCRIPTION, ':::', HOI_ITEM_QUANTITY, ':::', HOI_ITEM_UOM) ORDER BY HOI_ITEM_SLNO SEPARATOR '|||') 
                                    FROM hpvp_osgp_item WHERE HOI_GATEPASS_SLNO = GP.HO_GATEPASS_SLNO) AS ITEMS_LIST,
                                    (SELECT GROUP_CONCAT(
                                        CONCAT(
                                            IFNULL(B.HORI_ITEM_RETURN_QUANTITY, 0),
                                            '::',
                                            IFNULL(B.HOR_RETURN_DATE, '')
                                        )
                                        ORDER BY A.HOI_ITEM_SLNO
                                        SEPARATOR '||'
                                    )
                                    FROM hpvp_osgp_item A
                                    LEFT JOIN (
                                        SELECT HOR_GATEPASS_SLNO, HORI_ITEM_SLNO, SUM(HORI_ITEM_RETURN_QUANTITY) HORI_ITEM_RETURN_QUANTITY,
                                            GROUP_CONCAT(
                                                CONCAT(
                                                    HORI_ITEM_RETURN_QUANTITY,
                                                    ' ',
                                                    HOI_ITEM_UOM,
                                                    ' on ',
                                                    DATE_FORMAT(HOR_RETURN_DATE, '%d.%m.%Y %H:%i'),
                                                    ' by ',
                                                    HOU_NAME, ' (',HOU_ID,')'
                                                )
                                                ORDER BY HOR_RETURN_DATE
                                                SEPARATOR ', '
                                            ) AS HOR_RETURN_DATE 
                                            FROM hpvp_osgp_return, hpvp_osgp_return_item , hpvp_osgp_item, HPVP_OSGP_USER
                                            WHERE HOR_RETURN_SLNO = HORI_RETURN_SLNO AND HOR_GATEPASS_SLNO = HOI_GATEPASS_SLNO AND HOI_ITEM_SLNO = HORI_ITEM_SLNO
                                            AND IFNULL(HORI_ITEM_RETURN_QUANTITY,0) > 0 AND HOR_SECURITY_IN_OUT_BY = HOU_ID
                                            GROUP BY HOR_GATEPASS_SLNO, HORI_ITEM_SLNO
                                    ) B ON A.HOI_GATEPASS_SLNO = B.HOR_GATEPASS_SLNO AND A.HOI_ITEM_SLNO = B.HORI_ITEM_SLNO
                                    WHERE A.HOI_GATEPASS_SLNO = GP.HO_GATEPASS_SLNO
                                    ) AS RETURNS_LIST
                                    FROM HPVP_OSGP GP 
                                    LEFT JOIN HPVP_OSGP_USER U1 ON GP.HO_BHEL_OFFICIAL = U1.HOU_ID
                                    LEFT JOIN HPVP_OSGP_USER U2 ON GP.HO_TIME_IN_OUT_BY = U2.HOU_ID ORDER BY GP.HO_GATEPASS_SLNO DESC");
                                $prepstmt->execute();
                            }
                            ?>
                            <div class="card mb-4">
                                <div class="card-header">
                                    <i class="fas fa-table me-1"></i>
                                    Gatepass History
                                </div>
                                <div class="card-body">
                                    <table id="datatablesSimple" class="small">
                                        <thead>
                                            <tr>
                                                <th>Sl No</th>
                                                <th>Date, Return</th>
                                                <th>Work Order</th>
                                                <th>Purpose</th>
                                                <th>Source</th>
                                                <th>Destination</th>
                                                <th>Representative</th>
                                                <th>Material Owner</th>
                                                <th>Items</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tfoot>
                                            <tr>
                                                <th>Sl No</th>
                                                <th>Date, Return</th>
                                                <th>Work Order</th>
                                                <th>Purpose</th>
                                                <th>Source</th>
                                                <th>Destination</th>
                                                <th>Representative</th>
                                                <th>Material Owner</th>
                                                <th>Items</th>
                                                <th>Status</th>
                                            </tr>
                                        </tfoot>
                                        <tbody>
                                            <?php
                                            while ($row = $prepstmt->fetch(PDO::FETCH_OBJ)) {
                                                ?>
                                                <tr>
                                                    <td>
                                                        <?php
                                                        if ($row->HO_STATUS != "GATEPASS_REJECTED" && $row->HO_STATUS != "GATEPASS_REQUESTED" && $row->HO_STATUS != "GATEPASS_RECOMMENDED") {
                                                            echo "<a href='print.php?gpslno=" . $row->HO_GATEPASS_SLNO . "' target='_blank'>" . $row->HO_GATEPASS_SLNO . "</a>";
                                                        } else {
                                                            echo $row->HO_GATEPASS_SLNO;
                                                        }
                                                        ?>
                                                    </td>
                                                    <td><?php echo $row->HO_DATE . "<br/>" . $row->HO_DATE_RETURN; ?></td>
                                                    <td><?php echo $row->HO_ORDER; ?></td>
                                                    <td><?php echo $row->HO_PURPOSE; ?></td>
                                                    <td><?php echo $row->HO_SOURCE_FROM; ?></td>
                                                    <td><span title="<?php echo htmlspecialchars($row->HO_DESTINATION_TO); ?>"><?php echo htmlspecialchars($row->HO_DESTINATION_TO); ?></span>
                                                    </td>
                                                    <td><?php echo $row->HO_REPRESENTATIVE_NAME . "<br/>" . $row->HO_REPRESENTATIVE_ID; ?>
                                                    </td>
                                                    <td><span class="badge rounded-pill text-bg-<?php echo ($row->HO_MATERIAL_OWNER === 'BHEL-HPVP') ? 'primary' : 'secondary'; ?>"><?php echo htmlspecialchars($row->HO_MATERIAL_OWNER); ?></span></td>
                                                    <td>
                                                         <?php
                                                         $items_raw = $row->ITEMS_LIST;
                                                         $returns_raw = $row->RETURNS_LIST;
                                                         
                                                         if ($items_raw !== null && $items_raw !== '') {
                                                             $items_arr = explode('|||', $items_raw);
                                                             $returns_arr = ($returns_raw !== null && $returns_raw !== '') ? explode('||', $returns_raw) : [];
                                                             
                                                             $uomOriginal = '';
                                                             foreach ($items_arr as $item_idx => $item_raw) {
                                                                 $parts = explode(':::', $item_raw);
                                                                 $desc = isset($parts[0]) ? $parts[0] : '';
                                                                 $qty = isset($parts[1]) ? $parts[1] : '';
                                                                 $uom = isset($parts[2]) ? $parts[2] : '';
                                                                 if($item_idx === 0) {
                                                                     $uomOriginal = $uom; // Store the original UOM for comparison
                                                                 }else {
                                                                    $uom = $uomOriginal;                                                                    
                                                                 }
                                                                 $trail_lines = [];
                                                                 
                                                                 // 1. First movement trail
                                                                 if ($row->SECURITY_IN_TIME !== null && $row->SECURITY_IN_TIME !== '') {
                                                                     $officer = $row->SECURITY_OFFICER_NAME ? $row->SECURITY_OFFICER_NAME : $row->HO_TIME_IN_OUT_BY;
                                                                     if ($row->HO_MATERIAL_OWNER == 'Vendor') {
                                                                         $trail_lines[] = "IN: " . $qty . " " . $uom . " on " . $row->SECURITY_IN_TIME . " by " . $officer . " (" . $row->HO_TIME_IN_OUT_BY . ")";
                                                                     } else {
                                                                         $trail_lines[] = "OUT: " . $qty . " " . $uom . " on " . $row->SECURITY_IN_TIME . " by " . $officer . " (" . $row->HO_TIME_IN_OUT_BY . ")";
                                                                     }
                                                                 }
                                                                 
                                                                 // 2. Return movement trail
                                                                 $ret_qty = 0;
                                                                 if (isset($returns_arr[$item_idx])) {
                                                                     $ret_parts = explode('::', $returns_arr[$item_idx]);
                                                                     $ret_qty = isset($ret_parts[0]) ? floatval($ret_parts[0]) : 0;
                                                                     $ret_trail = isset($ret_parts[1]) ? $ret_parts[1] : '';
                                                                     
                                                                     if ($ret_qty > 0 && $ret_trail !== '') {
                                                                         $individual_returns = explode(', ', $ret_trail);
                                                                         foreach ($individual_returns as $indiv) {
                                                                             if (trim($indiv) !== '') {
                                                                                 if ($row->HO_MATERIAL_OWNER == 'Vendor') {
                                                                                     $trail_lines[] = "OUT: " . $indiv;
                                                                                 } else {
                                                                                     $trail_lines[] = "IN: " . $indiv;
                                                                                 }
                                                                             }
                                                                         }
                                                                     }
                                                                 }
                                                                 
                                                                 $trail_text = implode("\n", $trail_lines);
                                                                 if (empty($trail_text)) {
                                                                     $trail_text = "No movement trail recorded yet.";
                                                                 }
 
                                                                  // Determine return state and background styling
                                                                  $styleClass = "defaultCard";
                                                                  $bgStyle = "background-color: #f8f9fa; color: #41464b; border: 1px solid #e2e3e5;";
                                                                  if ($row->SECURITY_IN_TIME !== null && $row->SECURITY_IN_TIME !== '') {
                                                                      if ($ret_qty >= floatval($qty)) {
                                                                          $bgStyle = "background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc;";
                                                                          $styleClass = "greenCard";
                                                                      } elseif ($ret_qty > 0) {
                                                                          $bgStyle = "background-color: #fff3cd; color: #664d03; border: 1px solid #ffecb5;";
                                                                          $styleClass = "orangeCard";
                                                                      } else {
                                                                          $bgStyle = "background-color: #f8f9fa; color: #41464b; border: 1px solid #e2e3e5;";
                                                                          $styleClass = "defaultCard";
                                                                      }
                                                                  }
                                                                  if ($row->HO_RECOMMENDER_STATUS === 'REJECTED' || $row->HO_APPROVER_STATUS === 'REJECTED') {
                                                                        $bgStyle = "background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7;";
                                                                        $styleClass = "redCard";
                                                                  }
                                                                  
                                                                  $cardClass = $bgStyle !== "" ? "p-2 rounded mb-1" : "";
                                                                  ?>
                                                                  <div class="<?php echo $cardClass; ?> <?php echo $styleClass; ?>">
                                                                      <a href="#" class="item-trail-link text-decoration-none"
                                                                         data-bs-toggle="modal" 
                                                                         data-bs-target="#itemTrailModal" 
                                                                         data-bs-gatepass="<?php echo htmlspecialchars($row->HO_GATEPASS_SLNO); ?>" 
                                                                         data-bs-description="<?php echo htmlspecialchars($desc); ?>"
                                                                         data-bs-qty="<?php echo htmlspecialchars($qty . ' ' . $uom); ?>"
                                                                         data-bs-trail="<?php echo htmlspecialchars($trail_text); ?>">
                                                                          <strong><?php echo htmlspecialchars($desc); ?></strong>
                                                                      </a>
                                                                      <br/>
                                                                      <small class="<?php echo $bgStyle !== '' ? '' : 'text-muted'; ?> cardQty">Qty: <?php echo htmlspecialchars($qty . " " . $uom); ?><?php if ($ret_qty > 0) { echo " | Returned: " . htmlspecialchars($ret_qty) . " " . htmlspecialchars($uom); } ?></small>
                                                                  </div>
                                                                  <?php
                                                                  if ($item_idx < count($items_arr) - 1) {
                                                                      echo "<hr class='my-1'/>";
                                                                  }
                                                             }
                                                         }
                                                         ?>
                                                     </td>
                                                     <td>
                                                        Onward:
                                                        <?php
                                                            $statusColor = "warning";
                                                            switch ($row->HO_STATUS) {
                                                                case 'SECURITY_OUT_DONE':
                                                                    $statusColor = "success";
                                                                    break;
                                                                case 'SECURITY_IN_DONE':
                                                                    $statusColor = "success";
                                                                    break;
                                                                case 'GATEPASS_REJECTED':
                                                                    $statusColor = "danger";
                                                                    break;
                                                                default:
                                                                    $statusColor = "warning";
                                                            }
                                                        ?>
                                                         <a href="#" class="status-trail-link text-<?php echo $statusColor; ?> text-decoration-none fw-bold"
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#gatepassTrailModal" 
                                                            data-bs-gatepass="<?php echo htmlspecialchars($row->HO_GATEPASS_SLNO); ?>">
                                                             <?php echo htmlspecialchars($row->HO_STATUS); ?>
                                                         </a>
                                                         <?php
                                                            $prepstmtRet = $dBhandler->prepare("SELECT (SELECT HOR_STATUS
                                                            FROM hpvp_osgp_return
                                                            WHERE HOR_GATEPASS_SLNO = ?
                                                            ORDER BY HOR_RETURN_SLNO DESC
                                                            LIMIT 1) AS HOR_STATUS");
                                                            $prepstmtRet->execute([$row->HO_GATEPASS_SLNO]);
                                                            while ($rowRet = $prepstmtRet->fetch(PDO::FETCH_OBJ)) {
                                                                if(isset($rowRet->HOR_STATUS) && $rowRet->HOR_STATUS !== null) {
                                                                    $statusColor = "warning";
                                                                    switch ($rowRet->HOR_STATUS) {
                                                                        case 'SECURITY_OUT_DONE':
                                                                            $statusColor = "success";
                                                                            break;
                                                                        case 'SECURITY_IN_DONE':
                                                                            $statusColor = "success";
                                                                            break;
                                                                        case 'RETURN_REJECTED':
                                                                            $statusColor = "danger";
                                                                            break;
                                                                        default:
                                                                            $statusColor = "warning";
                                                                    }       
                                                                    ?>
                                                                        <br/><br/>
                                                                        Return:  
                                                                        <a href="#" class="status-trail-link text-<?php echo $statusColor; ?> text-decoration-none fw-bold"
                                                                            data-bs-toggle="modal" 
                                                                            data-bs-target="#gatepassTrailModal" 
                                                                            data-bs-gatepass="<?php echo htmlspecialchars($row->HO_GATEPASS_SLNO); ?>">
                                                                            <?php echo htmlspecialchars($rowRet->HOR_STATUS); ?>
                                                                        </a>                                                                    
                                                                    <?php
                                                                }
                                                            }
                                                         ?>                                                         
                                                     </td>
                                                </tr>
                                                <?php
                                            }
                                            ?>
                                        </tbody>
                                    </table>
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
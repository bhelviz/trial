<?php
require_once 'config.php';
?>
<!doctype html>
<html lang="en">

<head>
    <title>Material Gate Pass</title>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <!--<link href="css/style.css" rel="stylesheet" type="text/css" />-->
    <style>
        #ctr {}

        #left {
            width: 49%;
            float: left;
            /* border-radius: 10px; border: 1px solid black; */
        }

        #right {
            width: 49%;
            float: right;
            /*border-radius: 10px;
                    border: 1px solid black;
                    padding-left: 25px;
                    border-width: 1px; border-style: solid; border-color: red;*/
        }

        * {
            font-family: Verdana, Geneva, sans-serif;
            font-size: 12px;
        }

        .row {
            display: flex;
            /* equal height of the children */
        }

        .col {
            flex: 1;
            /* additionally, equal width */
            padding: 12px;
        }
    </style>
</head>

<body data-auto-print="true">
    <?php
    try {
        if (isset($_SESSION["HOU_ID"])) {
            $id = $_SESSION["HOU_ID"];
            $name = $_SESSION["HOU_NAME"];

            $gpslno = $_GET["gpslno"];

            $prepstmt = $dBhandler->prepare("SELECT HO_GATEPASS_SLNO, HO_REPRESENTATIVE_NAME, HO_REPRESENTATIVE_ID, HO_ORDER, HO_BHEL_OFFICIAL, HO_SOURCE_FROM, HO_DESTINATION_TO, 
                DATE_FORMAT(HO_DATE,'%d.%m.%Y') HO_DATE, DATE_FORMAT(HO_DATE_RETURN,'%d.%m.%Y') HO_DATE_RETURN, HO_PURPOSE, HO_MATERIAL_OWNER,
                HO_STATUS, HO_VENDOR_CODE, DATE_FORMAT(HO_TIME_IN_OUT,'%d.%m.%Y %H:%i') HO_TIME_IN_OUT 
                FROM HPVP_OSGP WHERE HO_GATEPASS_SLNO = ? AND HO_STATUS NOT IN ('GATEPASS_REQUESTED','GATEPASS_RECOMMENDED')");
            $prepstmt->execute([$gpslno]);

            while ($row = $prepstmt->fetch(PDO::FETCH_OBJ)) {

                ?>
                <div align="center" id="ctr" class="row">
                    <div id="left" class="col">
                        <table width="100%">
                            <tr>
                                <td colspan='2' align="center">
                                    <table width="100%">
                                        <col width="15%" />
                                        <col width="70%" />
                                        <col width="15%" />
                                        <tr>
                                            <td style='text-align:center'>
                                                <span style="float: left">
                                                    <img src='assets/img/logo.png' />
                                                </span>
                                            </td>
                                            <td style='text-align:center'>
                                                <span style="font-size: large">भारत हेवी इलेक्ट्रिकल्स लिमिटेड </span><br />
                                                <span style="font-size: large">Bharat Heavy Electricals Limited</span><br />
                                                हेवी प्लेट्स एंड वेसल्स प्लांट / Heavy Plates & Vessels Plant<br />
                                                विशाखापट्टणम / Visakhapatnam-530012
                                            </td>
                                            <td style='text-align:center'>

                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                        <table cellspacing="0" cellpadding="4" width="100%">
                            <col width="30%" />
                            <col width="40%" />
                            <col width="30%" />
                            <tr>
                                <td style="text-align: left">SLNO: <b><?php echo $row->HO_GATEPASS_SLNO; ?></b></td>
                                <td style="text-align: center"><b>MATERIAL GATEPASS</b><br />(<?php echo $row->HO_MATERIAL_OWNER; ?> Material)</td>
                                <td style="text-align: right"></td>
                            </tr>
                        </table>
                        <br />
                        <table cellspacing="0" cellpadding="4" width="100%">
                            <col width="10%">
                            <col width="15%">
                            <col width="25%">
                            <col width="25%">
                            <col width="15%">
                            <col width="10%">
                            <tr>
                                <td colspan="2">Vendor: </td>
                                <td colspan="4"><u><?php echo $row->HO_VENDOR_CODE; ?></u></td>
                            </tr>
                            <tr>
                                <td colspan="2">Representative:</td>
                                <td colspan="4"><u><?php echo $row->HO_REPRESENTATIVE_ID; ?></u></td>
                            </tr>
                            <tr>
                                <td colspan="2">Order: </td>
                                <td colspan="4"><u><?php echo $row->HO_ORDER; ?></u></td>
                            </tr>
                            <tr>
                                <td colspan="2">Source: </td>
                                <td><u><?php echo $row->HO_SOURCE_FROM; ?></u></td>
                                <td>Destination: </td>
                                <td colspan="2"><u><?php echo $row->HO_DESTINATION_TO; ?></u></td>
                            </tr>
                            <tr>
                                <td colspan="2">Approver: </td>
                                <td colspan="4"><u><?php echo $row->HO_BHEL_OFFICIAL; ?></u></td>
                            </tr>
                            <tr>
                                <td colspan="2">Date: </td>
                                <td colspan="1"><u><?php echo $row->HO_DATE; ?></u></td>
                                <td colspan="1">Return Date: </td>
                                <td colspan="2"><u><?php echo $row->HO_DATE_RETURN; ?></u></td>
                            </tr>
                            <tr>
                                <td colspan="2">Purpose: </td>
                                <td colspan="4"><u><?php echo $row->HO_PURPOSE; ?></u></td>
                            </tr>
                            <tr>
                                <td colspan="6" style="text-align: center; font-size: x-small">Please allow the items mentioned
                                    below to be taken out for the purpose mentioned above.</td>
                            </tr>
                            <tr>
                                <td colspan="1"
                                    style="border-top: 1px solid; border-right: 1px solid; border-bottom: 1px solid; text-align: center">
                                    ITEM No.</td>
                                <td colspan="4" style="border-top: 1px solid; border-bottom: 1px solid; text-align: center">
                                    DESCRIPTION</td>
                                <td colspan="1"
                                    style="border-top: 1px solid; border-left: 1px solid; border-bottom: 1px solid; text-align: center">
                                    QTY.</td>
                            </tr>
                            <?php
                                $prepStmtItem = $dBhandler->prepare("SELECT * FROM hpvp_osgp_item WHERE HOI_GATEPASS_SLNO = ? ORDER BY HOI_ITEM_SLNO ASC");
                                $prepStmtItem->execute([$gpslno]);

                                while ($rowItem = $prepStmtItem->fetch(PDO::FETCH_OBJ)) {
                                    ?>
                                    <tr>
                                        <td colspan="1" style="border-right: 1px solid; text-align: right"><?php echo $rowItem->HOI_ITEM_SLNO; ?></td>
                                        <td colspan="4"><?php echo $rowItem->HOI_ITEM_DESCRIPTION; ?></td>
                                        <td colspan="1" style="border-left: 1px solid; text-align: right">
                                            <?php echo $rowItem->HOI_ITEM_QUANTITY . " " . $rowItem->HOI_ITEM_UOM; ?>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            ?>                            
                            <tr>
                                <td colspan="1" style="border-right: 1px solid; border-bottom: 1px solid;"></td>
                                <td colspan="4" style="border-bottom: 1px solid"></td>
                                <td colspan="1" style="border-left: 1px solid; border-bottom: 1px solid;"></td>
                            </tr>
                        </table>
                        <table width="100%">
                            <col width="50%" />
                            <col width="50%" />
                            <tr>
                                <td>
                                    Time <?php echo $row->HO_MATERIAL_OWNER == 'Vendor' ? 'In' : 'Out'; ?>: <?php echo $row->HO_TIME_IN_OUT; ?><br /><br /><br />
                                    Signature of Representative<br />(<?php echo $row->HO_REPRESENTATIVE_NAME; ?>)<br /><br /><br />
                                    Signature of Security Officer<br /><br /><br />
                                    Security Stamp
                                </td>
                                <td>
                                    <br /><br />
                                    Signature: <br /><br /><br />
                                    Authorizing Officer: <br />
                                    (<?php echo $row->HO_BHEL_OFFICIAL; ?>)<br />
                                    
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div id="right" class="col">
                        <table width="100%">
                            <tr>
                                <td colspan='2' align="center">
                                    <table width="100%">
                                        <col width="15%" />
                                        <col width="70%" />
                                        <col width="15%" />
                                        <tr>
                                            <td style='text-align:center'>
                                                <span style="float: left">
                                                    <img src='assets/img/logo.png' />
                                                </span>
                                            </td>
                                            <td style='text-align:center'>
                                                <span style="font-size: large">भारत हेवी इलेक्ट्रिकल्स लिमिटेड </span><br />
                                                <span style="font-size: large">Bharat Heavy Electricals Limited</span><br />
                                                हेवी प्लेट्स एंड वेसल्स प्लांट / Heavy Plates & Vessels Plant<br />
                                                विशाखापट्टणम / Visakhapatnam-530012
                                            </td>
                                            <td style='text-align:center'>

                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                        <table cellspacing="0" cellpadding="4" width="100%">
                            <col width="30%" />
                            <col width="40%" />
                            <col width="30%" />
                            <tr>
                                <td style="text-align: left">SLNO: <b><?php echo $row->HO_GATEPASS_SLNO; ?></b></td>
                                <td style="text-align: center"><b>MATERIAL GATEPASS</b><br />(<?php echo $row->HO_MATERIAL_OWNER; ?> Material)</td>
                                <td style="text-align: right"></td>
                            </tr>
                        </table>
                        <br />
                        <table cellspacing="0" cellpadding="4" width="100%">
                            <col width="10%">
                            <col width="15%">
                            <col width="25%">
                            <col width="25%">
                            <col width="15%">
                            <col width="10%">
                            <tr>
                                <td colspan="2">Vendor: </td>
                                <td colspan="4"><u><?php echo $row->HO_VENDOR_CODE; ?></u></td>
                            </tr>
                            <tr>
                                <td colspan="2">Representative:</td>
                                <td colspan="4"><u><?php echo $row->HO_REPRESENTATIVE_ID; ?></u></td>
                            </tr>
                            <tr>
                                <td colspan="2">Order: </td>
                                <td colspan="4"><u><?php echo $row->HO_ORDER; ?></u></td>
                            </tr>
                            <tr>
                                <td colspan="2">Source: </td>
                                <td><u><?php echo $row->HO_SOURCE_FROM; ?></u></td>
                                <td>Destination: </td>
                                <td colspan="2"><u><?php echo $row->HO_DESTINATION_TO; ?></u></td>
                            </tr>
                            <tr>
                                <td colspan="2">Approver: </td>
                                <td colspan="4"><u><?php echo $row->HO_BHEL_OFFICIAL; ?></u></td>
                            </tr>
                            <tr>
                                <td colspan="2">Date: </td>
                                <td colspan="1"><u><?php echo $row->HO_DATE; ?></u></td>
                                <td colspan="1">Return Date: </td>
                                <td colspan="2"><u><?php echo $row->HO_DATE_RETURN; ?></u></td>
                            </tr>
                            <tr>
                                <td colspan="2">Purpose: </td>
                                <td colspan="4"><u><?php echo $row->HO_PURPOSE; ?></u></td>
                            </tr>
                            <tr>
                                <td colspan="6" style="text-align: center; font-size: x-small">Please allow the items mentioned
                                    below to be taken out for the purpose mentioned above.</td>
                            </tr>
                            <tr>
                                <td colspan="1"
                                    style="border-top: 1px solid; border-right: 1px solid; border-bottom: 1px solid; text-align: center">
                                    ITEM No.</td>
                                <td colspan="4" style="border-top: 1px solid; border-bottom: 1px solid; text-align: center">
                                    DESCRIPTION</td>
                                <td colspan="1"
                                    style="border-top: 1px solid; border-left: 1px solid; border-bottom: 1px solid; text-align: center">
                                    QTY.</td>
                            </tr>
                            <?php
                                $prepStmtItem = $dBhandler->prepare("SELECT * FROM hpvp_osgp_item WHERE HOI_GATEPASS_SLNO = ? ORDER BY HOI_ITEM_SLNO ASC");
                                $prepStmtItem->execute([$gpslno]);

                                while ($rowItem = $prepStmtItem->fetch(PDO::FETCH_OBJ)) {
                                    ?>
                                    <tr>
                                        <td colspan="1" style="border-right: 1px solid; text-align: right"><?php echo $rowItem->HOI_ITEM_SLNO; ?></td>
                                        <td colspan="4"><?php echo $rowItem->HOI_ITEM_DESCRIPTION; ?></td>
                                        <td colspan="1" style="border-left: 1px solid; text-align: right">
                                            <?php echo $rowItem->HOI_ITEM_QUANTITY . " " . $rowItem->HOI_ITEM_UOM; ?>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            ?>                           
                            <tr>
                                <td colspan="1" style="border-right: 1px solid; border-bottom: 1px solid;"></td>
                                <td colspan="4" style="border-bottom: 1px solid"></td>
                                <td colspan="1" style="border-left: 1px solid; border-bottom: 1px solid;"></td>
                            </tr>
                        </table>
                        <table width="100%">
                            <col width="50%" />
                            <col width="50%" />
                            <tr>
                                <td>
                                    Time <?php echo $row->HO_MATERIAL_OWNER == 'Vendor' ? 'In' : 'Out'; ?>: <?php echo $row->HO_TIME_IN_OUT; ?><br /><br /><br />
                                    Signature of Representative<br />(<?php echo $row->HO_REPRESENTATIVE_NAME; ?>)<br /><br /><br />
                                    Signature of Security Officer<br /><br /><br />
                                    Security Stamp
                                </td>
                                <td>
                                    <br /><br />
                                    Signature: <br /><br /><br />
                                    Authorizing Officer: <br />
                                    (<?php echo $row->HO_BHEL_OFFICIAL; ?>)<br />
                                    
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                <?php
            }

        } else {
            header("Location: index.php?err=Session Expired");
        }
    } catch (Exception $e) {
        echo $e;
    } finally {

    }
    ?>
    <script src="js/jquery-3.7.1.min.js"></script>
    <script src="js/osgp.js"></script>
</body>

</html>
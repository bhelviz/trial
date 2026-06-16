<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION["HOU_ID"])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$gpslno = $_GET['gpslno'] ?? '';

if (!is_numeric($gpslno)) {
    echo json_encode(['error' => 'Invalid Gatepass ID']);
    exit;
}

try {
    // 1. Fetch gatepass main data
    $gpStmt = $dBhandler->prepare("SELECT GP.HO_GATEPASS_SLNO, GP.HO_STATUS, GP.HO_MATERIAL_OWNER, GP.HO_VENDOR_NAME, GP.HO_RECOMMENDER_REMARK, GP.HO_APPROVER_REMARK, GP.HO_SECURITY_REMARK,
                                          DATE_FORMAT(GP.HO_DATE, '%d.%m.%Y') as HO_DATE_F,
                                          DATE_FORMAT(GP.HO_TIME_CREATED, '%d.%m.%Y %H:%i') as TIME_CREATED_F,
                                          DATE_FORMAT(GP.HO_RECOMMENDER_TIME, '%d.%m.%Y %H:%i') as TIME_RECOMMENDED_F,
                                          DATE_FORMAT(GP.HO_APPROVER_TIME, '%d.%m.%Y %H:%i') as TIME_APPROVED_F,
                                          DATE_FORMAT(GP.HO_TIME_IN_OUT, '%d.%m.%Y %H:%i') as TIME_IN_OUT_F,
                                          U1.HOU_NAME as RECOMMENDER_NAME,
                                          U2.HOU_NAME as SECURITY_NAME,
                                          U3.HOU_NAME as APPROVER_NAME
                                   FROM HPVP_OSGP GP
                                   LEFT JOIN HPVP_OSGP_USER U1 ON GP.HO_BHEL_OFFICIAL = U1.HOU_ID
                                   LEFT JOIN HPVP_OSGP_USER U2 ON GP.HO_TIME_IN_OUT_BY = U2.HOU_ID
                                   LEFT JOIN HPVP_OSGP_USER U3 ON GP.HO_APPROVER = U3.HOU_ID
                                   WHERE GP.HO_GATEPASS_SLNO = ?");
    $gpStmt->execute([$gpslno]);
    $gp = $gpStmt->fetch(PDO::FETCH_ASSOC);

    if (!$gp) {
        echo json_encode(['error' => 'Gatepass not found']);
        exit;
    }

    $events = [];

    // Step 1: Created
    $createTime = !empty($gp['TIME_CREATED_F']) ? $gp['TIME_CREATED_F'] : $gp['HO_DATE_F'];
    $events[] = [
        'title' => 'Gatepass Created',
        'time' => $createTime,
        'details' => 'Created by Vendor: ' . htmlspecialchars($gp['HO_VENDOR_NAME']),
        'type' => 'created',
        'timestamp' => !empty($gp['TIME_CREATED_F']) ? strtotime($gp['TIME_CREATED_F']) : strtotime($gp['HO_DATE_F'])
    ];

    // Step 2: Recommended (if status is past REQUESTED)
    $hasRecommended = false;
    $hasApproved = false;
    $hasFirstMovement = false;

    $status = $gp['HO_STATUS'];
    
    // Determine from status if it has been recommended/approved
    if ($status !== 'GATEPASS_REQUESTED' && $status !== 'GATEPASS_REJECTED' && $status !== 'IR') {
        $hasRecommended = true;
    }
    if ($status !== 'GATEPASS_REQUESTED' && $status !== 'GATEPASS_RECOMMENDED' && $status !== 'GATEPASS_REJECTED' && $status !== 'IR') {
        $hasApproved = true;
    }
    if ($status === 'SECURITY_IN_DONE' || $status === 'SECURITY_OUT_DONE' || $status === 'OS' || $status === 'IS' || str_starts_with($status, 'RETURN_')) {
        $hasFirstMovement = true;
    }

    // Recommend event
    if ($hasRecommended) {
        $recTime = !empty($gp['TIME_RECOMMENDED_F']) ? $gp['TIME_RECOMMENDED_F'] : '';
        $events[] = [
            'title' => 'Gatepass Recommended',
            'time' => $recTime,
            'details' => 'Recommended by BHEL Official: ' . htmlspecialchars($gp['RECOMMENDER_NAME'] ?? 'Official'),
            'remark' => !empty($gp['HO_RECOMMENDER_REMARK']) ? trim(strip_tags($gp['HO_RECOMMENDER_REMARK'])) : '',
            'type' => 'recommended',
            'timestamp' => !empty($gp['TIME_RECOMMENDED_F']) ? strtotime($gp['TIME_RECOMMENDED_F']) : 0
        ];
    } elseif ($status === 'GATEPASS_REJECTED') {
        $events[] = [
            'title' => 'Gatepass Rejected',
            'time' => $gp['TIME_RECOMMENDED_F'] ?? '',
            'details' => 'Rejected by BHEL Official: ' . htmlspecialchars($gp['RECOMMENDER_NAME'] ?? 'Official'),
            'remark' => !empty($gp['HO_RECOMMENDER_REMARK']) ? trim(strip_tags($gp['HO_RECOMMENDER_REMARK'])) : '',
            'type' => 'rejected',
            'timestamp' => !empty($gp['TIME_RECOMMENDED_F']) ? strtotime($gp['TIME_RECOMMENDED_F']) : 0
        ];
    }

    // Approve event
    if ($hasApproved) {
        $appTime = !empty($gp['TIME_APPROVED_F']) ? $gp['TIME_APPROVED_F'] : '';
        $events[] = [
            'title' => 'Gatepass Approved',
            'time' => $appTime,
            'details' => 'Approved by BHEL Approver: ' . htmlspecialchars($gp['APPROVER_NAME'] ?? 'Approver'),
            'remark' => !empty($gp['HO_APPROVER_REMARK']) ? trim(strip_tags($gp['HO_APPROVER_REMARK'])) : '',
            'type' => 'approved',
            'timestamp' => !empty($gp['TIME_APPROVED_F']) ? strtotime($gp['TIME_APPROVED_F']) : 0
        ];
    }

    // First Movement (Security In/Out)
    if ($hasFirstMovement && !empty($gp['TIME_IN_OUT_F'])) {
        $mvtType = ($gp['HO_MATERIAL_OWNER'] === 'Vendor') ? 'Inward' : 'Outward';
        $events[] = [
            'title' => 'Initial ' . $mvtType . ' Movement (Security)',
            'time' => $gp['TIME_IN_OUT_F'],
            'details' => 'Processed ' . $mvtType . ' by Security Officer: ' . htmlspecialchars($gp['SECURITY_NAME'] ?? $gp['HO_TIME_IN_OUT_BY'] ?? 'Officer'),
            'remark' => !empty($gp['HO_SECURITY_REMARK']) ? trim($gp['HO_SECURITY_REMARK']) : '',
            'type' => 'security_movement',
            'timestamp' => strtotime($gp['TIME_IN_OUT_F'])
        ];
    }

    // 2. Fetch return events from HPVP_OSGP_RETURN
    $retStmt = $dBhandler->prepare("SELECT RET.HOR_ITEM_SLNO, RET.HOR_ITEM_RETURN_QUANTITY, RET.HOR_STATUS, RET.HOR_RECOMMENDER_REMARK, RET.HOR_APPROVER_REMARK, RET.HOR_SECURITY_REMARK,
                                          DATE_FORMAT(RET.HOR_ITEM_RETURN_DATE, '%d.%m.%Y %H:%i') as RETURN_DATE_F,
                                          IT.HOI_ITEM_DESCRIPTION, IT.HOI_ITEM_UOM,
                                          U.HOU_NAME as SECURITY_NAME
                                   FROM HPVP_OSGP_RETURN RET
                                   LEFT JOIN hpvp_osgp_item IT ON RET.HOR_GATEPASS_SLNO = IT.HOI_GATEPASS_SLNO AND RET.HOR_ITEM_SLNO = IT.HOI_ITEM_SLNO
                                   LEFT JOIN HPVP_OSGP_USER U ON RET.HOR_ITEM_IN_OUT_BY = U.HOU_ID
                                   WHERE RET.HOR_GATEPASS_SLNO = ?");
    $retStmt->execute([$gpslno]);
    $returns = $retStmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($returns as $ret) {
        $itemDesc = $ret['HOI_ITEM_DESCRIPTION'] ?? ('Item #' . $ret['HOR_ITEM_SLNO']);
        $qtyText = $ret['HOR_ITEM_RETURN_QUANTITY'] . ' ' . ($ret['HOI_ITEM_UOM'] ?? '');
        $retStatus = $ret['HOR_STATUS'];

        if ($retStatus === 'RETURN_REQUESTED') {
            $events[] = [
                'title' => 'Return Requested',
                'time' => '',
                'details' => 'Requested return for: ' . htmlspecialchars($itemDesc) . ' (Qty: ' . $qtyText . ')',
                'type' => 'return_requested',
                'timestamp' => 0
            ];
        } elseif ($retStatus === 'RETURN_RECOMMENDED') {
            $events[] = [
                'title' => 'Return Recommended',
                'time' => '',
                'details' => 'Recommended return for: ' . htmlspecialchars($itemDesc) . ' (Qty: ' . $qtyText . ')',
                'remark' => !empty($ret['HOR_RECOMMENDER_REMARK']) ? trim($ret['HOR_RECOMMENDER_REMARK']) : '',
                'type' => 'return_recommended',
                'timestamp' => 0
            ];
        } elseif ($retStatus === 'RETURN_APPROVED') {
            $events[] = [
                'title' => 'Return Approved',
                'time' => '',
                'details' => 'Approved return for: ' . htmlspecialchars($itemDesc) . ' (Qty: ' . $qtyText . ')',
                'remark' => !empty($ret['HOR_APPROVER_REMARK']) ? trim($ret['HOR_APPROVER_REMARK']) : '',
                'type' => 'return_approved',
                'timestamp' => 0
            ];
        } elseif ($retStatus === 'RETURN_REJECTED') {
            $events[] = [
                'title' => 'Return Rejected',
                'time' => '',
                'details' => 'Rejected return for: ' . htmlspecialchars($itemDesc) . ' (Qty: ' . $qtyText . ')',
                'remark' => !empty($ret['HOR_APPROVER_REMARK']) ? trim($ret['HOR_APPROVER_REMARK']) : '',
                'type' => 'return_rejected',
                'timestamp' => 0
            ];
        } elseif ($retStatus === 'SECURITY_IN_DONE' || $retStatus === 'SECURITY_OUT_DONE' || $retStatus === 'OUT') {
            $mvtType = ($gp['HO_MATERIAL_OWNER'] === 'Vendor') ? 'Outward (Return)' : 'Inward (Return)';
            $events[] = [
                'title' => 'Return ' . (($gp['HO_MATERIAL_OWNER'] === 'Vendor') ? 'Outward' : 'Inward') . ' Completed',
                'time' => $ret['RETURN_DATE_F'] ?? '',
                'details' => 'Processed return for: ' . htmlspecialchars($itemDesc) . ' (Qty: ' . $qtyText . ') by Security Officer: ' . htmlspecialchars($ret['SECURITY_NAME'] ?? 'Officer'),
                'remark' => !empty($ret['HOR_SECURITY_REMARK']) ? trim($ret['HOR_SECURITY_REMARK']) : '',
                'type' => 'return_security_movement',
                'timestamp' => !empty($ret['RETURN_DATE_F']) ? strtotime($ret['RETURN_DATE_F']) : 0
            ];
        }
    }

    // Sort events:
    // Events with timestamp > 0 are sorted chronologically by timestamp.
    // Events with timestamp == 0 are placed in order after the last timestamped event, or at the end.
    // Let's separate them:
    $timestamped = [];
    $nonTimestamped = [];
    foreach ($events as $ev) {
        if ($ev['timestamp'] > 0) {
            $timestamped[] = $ev;
        } else {
            $nonTimestamped[] = $ev;
        }
    }

    usort($timestamped, function($a, $b) {
        return $a['timestamp'] <=> $b['timestamp'];
    });

    // Merge them: timestamped first, then non-timestamped
    $sortedEvents = array_merge($timestamped, $nonTimestamped);

    echo json_encode([
        'gpslno' => $gpslno,
        'current_status' => $status,
        'owner' => $gp['HO_MATERIAL_OWNER'],
        'events' => $sortedEvents
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

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
    <title>Outsourcing - Material Gatepass</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="css/main.css" rel="stylesheet" />
</head>

<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <?php include 'header.php'; ?>
    </nav>
    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                <?php include 'sidebar.php'; ?>
            </nav>
        </div>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    
                    <?php
                    try {
                        if (isset($_SESSION["HOU_ID"])) {
                            $id = $_SESSION["HOU_ID"];
                            ?>
                            <div class="card shadow-sm border-0 mt-3 mb-3 mx-auto" style="max-width: 720px; border-radius: 12px; background: linear-gradient(135deg, #ffffff, #f8f9fa); border: 1px solid rgba(0,0,0,0.04);">
                                <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center" style="font-size: 0.85rem;">
                                    <div>
                                        <span class="text-secondary small">ID:</span>
                                        <strong class="text-dark ms-1"><?php echo htmlspecialchars($id); ?></strong>
                                    </div>
                                    <div class="vr mx-2" style="height: 15px;"></div>
                                    <div>
                                        <span class="text-secondary small">Name:</span>
                                        <strong class="text-dark ms-1"><?php echo htmlspecialchars($_SESSION["HOU_NAME"] ?? ''); ?></strong>
                                    </div>
                                    <div class="vr mx-2" style="height: 15px;"></div>
                                    <div>
                                        <span class="text-secondary small">Role:</span>
                                        <span class="badge bg-primary ms-1" style="font-size: 0.75rem; font-weight: 500;"><?php echo htmlspecialchars($_SESSION["HOU_ROLE"] ?? ''); ?></span>
                                    </div>
                                </div>
                            </div>
                            <?php
                        } else {
                            header("Location: index.php?err=Session Expired");
                            exit;
                        }
                    } catch (Exception $e) {
                        echo $e;
                    }
                    ?>

                    <h6 class="pt-3 text-center text-muted mb-3 fs-6">Workflow & Steps of Movement Process</h6>
                    
                    <div class="process-timeline-container">
                        <!-- Phase 1 -->
                        <div class="phase-section">
                            <div class="phase-header mb-2">
                                <span class="badge bg-primary text-uppercase px-3 py-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">Phase 1: Movement Process</span>
                            </div>
                            <div class="phase-row phase-1-row">
                                <!-- Step 1 -->
                                <div class="timeline-item-new">
                                    <div class="timeline-icon-new"><i class="fas fa-file-signature"></i></div>
                                    <div class="timeline-content-new">
                                        <span class="step-badge-new bg-phase1">Step 1</span>
                                        <h5 class="step-title-new">Vendor Creates Request</h5>
                                        <h6 class="step-subtitle-new text-primary">Movement of Returnable Material</h6>
                                        <p class="step-desc-new">Vendor initiates the gatepass request for movement of returnable material owned by BHEL-HPVP or the Vendor.</p>
                                    </div>
                                </div>

                                <!-- Step 2 -->
                                <div class="timeline-item-new">
                                    <div class="timeline-icon-new"><i class="fas fa-user-check"></i></div>
                                    <div class="timeline-content-new">
                                        <span class="step-badge-new bg-phase1" style="background: linear-gradient(135deg, #0dcaf0, #0aa2c0) !important;">Step 2</span>
                                        <h5 class="step-title-new">Executive Recommendation</h5>
                                        <h6 class="step-subtitle-new text-info">BHEL-HPVP Executive Review</h6>
                                        <p class="step-desc-new">The designated BHEL-HPVP executive reviews the gatepass request and either recommends it or rejects it.</p>
                                    </div>
                                </div>

                                <!-- Step 3 -->
                                <div class="timeline-item-new">
                                    <div class="timeline-icon-new"><i class="fas fa-file-contract"></i></div>
                                    <div class="timeline-content-new">
                                        <span class="step-badge-new bg-phase1">Step 3</span>
                                        <h5 class="step-title-new">Approver Final Action</h5>
                                        <h6 class="step-subtitle-new text-primary">BHEL-HPVP Approving Authority</h6>
                                        <p class="step-desc-new">The approving authority evaluates the recommended request to make the final decision (Approve/Reject).</p>
                                    </div>
                                </div>

                                <!-- Step 4 -->
                                <div class="timeline-item-new">
                                    <div class="timeline-icon-new"><i class="fas fa-shield-alt"></i></div>
                                    <div class="timeline-content-new">
                                        <span class="step-badge-new bg-phase1" style="background: linear-gradient(135deg, #0dcaf0, #0aa2c0) !important;">Step 4</span>
                                        <h5 class="step-title-new">Security Gate Entry/Exit</h5>
                                        <h6 class="step-subtitle-new text-info">Security Logging</h6>
                                        <p class="step-desc-new">Security records <strong>IN</strong> log for Vendor material or <strong>OUT</strong> log for BHEL-HPVP material at the gate.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Phase 2 -->
                        <div class="phase-section">
                            <div class="phase-header mb-2 mt-2">
                                <span class="badge bg-purple text-uppercase px-3 py-1" style="font-size: 0.72rem; letter-spacing: 0.5px; background: linear-gradient(135deg, #6f42c1, #4c2c90) !important;">Phase 2: Return Process</span>
                            </div>
                            <div class="phase-row phase-2-row">
                                <!-- Step 5 -->
                                <div class="timeline-item-new">
                                    <div class="timeline-icon-new"><i class="fas fa-undo-alt"></i></div>
                                    <div class="timeline-content-new">
                                        <span class="step-badge-new bg-phase2">Step 5</span>
                                        <h5 class="step-title-new">Vendor Return Request</h5>
                                        <h6 class="step-subtitle-new" style="color: #6f42c1;">Request for Return of Material</h6>
                                        <p class="step-desc-new">Vendor initiates an outward return request. This supports partial returns of the material quantities.</p>
                                    </div>
                                </div>

                                <!-- Step 6 -->
                                <div class="timeline-item-new">
                                    <div class="timeline-icon-new"><i class="fas fa-clipboard-check"></i></div>
                                    <div class="timeline-content-new">
                                        <span class="step-badge-new bg-phase2">Step 6</span>
                                        <h5 class="step-title-new">Return Recommendation</h5>
                                        <h6 class="step-subtitle-new" style="color: #6f42c1;">Executive Review of Return</h6>
                                        <p class="step-desc-new">The BHEL-HPVP executive reviews the return gatepass and issues their recommendation or rejection.</p>
                                    </div>
                                </div>

                                <!-- Step 7 -->
                                <div class="timeline-item-new">
                                    <div class="timeline-icon-new"><i class="fas fa-check-double"></i></div>
                                    <div class="timeline-content-new">
                                        <span class="step-badge-new bg-phase2">Step 7</span>
                                        <h5 class="step-title-new">Return Final Approval</h5>
                                        <h6 class="step-subtitle-new" style="color: #6f42c1;">Approver Final Sign-Off</h6>
                                        <p class="step-desc-new">The BHEL-HPVP approver makes the final decision on the return request (Approve/Reject).</p>
                                    </div>
                                </div>

                                <!-- Step 8 -->
                                <div class="timeline-item-new">
                                    <div class="timeline-icon-new"><i class="fas fa-door-open"></i></div>
                                    <div class="timeline-content-new">
                                        <span class="step-badge-new bg-phase2">Step 8</span>
                                        <h5 class="step-title-new">Security Gate Entry/Exit</h5>
                                        <h6 class="step-subtitle-new" style="color: #6f42c1;">Security Logging</h6>
                                        <p class="step-desc-new">Security records <strong>OUT</strong> log for Vendor material or <strong>IN</strong> log for BHEL-HPVP material at the gate.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
            <footer class="py-4 bg-light mt-auto">
                <?php include 'footer.php'; ?>
            </footer>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"
        crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"
        crossorigin="anonymous"></script>
    <script src="js/datatables-simple-demo.js"></script>
</body>

</html>
<div class="sb-sidenav-menu">
    <?php
    $role = isset($_SESSION["HOU_ROLE"]) ? $_SESSION["HOU_ROLE"] : "";
    //echo $role;
    $url = $_SERVER['REQUEST_URI'];
    ?>
    <div class="nav">
        <a class="nav-link <?php if (str_contains($url, "main.php"))
            echo "active"; ?>" href="main.php">
            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
            Dashboard
        </a>
        <?php

        if ($role == "Vendor" || $role == "Administrator") {
            ?>
            <a class="nav-link <?php if (str_contains($url, "create-gatepass.php"))
                echo "active"; ?>" href="create-gatepass.php">
                <div class="sb-nav-link-icon"><i class="fas fa-file-alt"></i></div>
                Create Gatepass
            </a>
            <a class="nav-link <?php if (str_contains($url, "request-return.php"))
                echo "active"; ?>" href="request-return.php">
                <div class="sb-nav-link-icon"><i class="fas fa-file-alt"></i></div>
                Request Return
            </a>
            <?php
        }
        if ($role == "Recommender" || $role == "Administrator") {
            ?>
            <a class="nav-link <?php if (str_contains($url, "recommend.php"))
                echo "active"; ?>" href="recommend.php">
                <div class="sb-nav-link-icon"><i class="fas fa-thumbs-up"></i></div>
                Recommend
            </a>
            <?php
        }
        if ($role == "Approver" || $role == "Administrator") {
            ?>
            <a class="nav-link <?php if (str_contains($url, "approve.php"))
                echo "active"; ?>" href="approve.php">
                <div class="sb-nav-link-icon"><i class="fas fa-thumbs-up"></i></div>
                Approve
            </a>
            <?php
        }
        if ($role == "Security" || $role == "Administrator") {
            ?>
            <a class="nav-link <?php if (str_contains($url, "security-in.php"))
                echo "active"; ?>" href="security-in.php">
                <div class="sb-nav-link-icon"><i class="fas fa-arrow-left"></i></div>
                Security In
            </a>
            <a class="nav-link <?php if (str_contains($url, "security-out.php"))
                echo "active"; ?>" href="security-out.php">
                <div class="sb-nav-link-icon"><i class="fas fa-arrow-right"></i></div>
                Security Out
            </a>
            <?php
        }
        ?>
        <a class="nav-link <?php if (str_contains($url, "history.php"))
            echo "active"; ?>" href="history.php">
            <div class="sb-nav-link-icon"><i class="fas fa-box-archive"></i></div>
            History
        </a>
    </div>
</div>
<div class="sb-sidenav-footer">
    <div class="small">Logged in as:</div>
    <?php echo $_SESSION["HOU_NAME"]; ?>
</div>
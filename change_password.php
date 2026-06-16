<?php
    session_start();
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
        <link href="css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
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
                        <h4 class="mt-4 text-center">Change Password</h4>						
                        <?php
                            try{
                                if(isset($_SESSION["HOU_ID"])){
                                    $id = $_SESSION["HOU_ID"]; 
									?>
									<form name="f1" method="post">
										<div class="row mb-3">
											<div class="col-md-4">
												
											</div>
											<div class="col-md-4">
												<div class="form-floating">
													<input class="form-control form-control-sm" id="currPassword" name="currPassword" type="password" required />
													<label for="currPassword">Current Password</label>
												</div>
											</div>
											<div class="col-md-4">
												
											</div>
										</div>
										<div class="row mb-3">
											<div class="col-md-4">
												
											</div>
											<div class="col-md-4">
												<div class="form-floating">
													<input class="form-control form-control-sm" id="newPassword" name="newPassword" type="password"  minlength="10" required />
													<label for="newPassword">New Password</label>
												</div>
											</div>
											<div class="col-md-4">
												
											</div>
										</div>
										<div class="row mb-3">
											<div class="col-md-4">
												
											</div>
											<div class="col-md-4">
												<div class="form-floating">
													<input class="form-control form-control-sm" id="confirmNewPassword" name="confirmNewPassword" type="password"  minlength="10" required />
													<label for="confirmNewPassword">Confirm New Password</label>
												</div>
											</div>
											<div class="col-md-4">
												
											</div>
										</div>
										<div class="row mb-3">
											<div class="col-md-4">
												
											</div>
											<div class="col-md-4">                                            
												<input class="btn btn-primary btn-block" type="submit" value="Change Password" />                                            
											</div>
											<div class="col-md-4">
												
											</div>
										</div>
									</form>
									<?php
									if(isset($_POST["currPassword"])){
										$currPass = $_POST["currPassword"]; 
										$newPass = $_POST["newPassword"]; 
										$confPass = $_POST["confirmNewPassword"]; 
										if($newPass == $confPass){
											$options = [
														  PDO::ATTR_EMULATE_PREPARES   => false, // turn off emulation mode for "real" prepared statements
														  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, //turn on errors in the form of exceptions
														  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, //make the default fetch be an associative array
														];			
											$dBhandler = new PDO('mysql:host=localhost;dbname=hpvpweb','hpvpweb','HpVpWeb@115',$options);
											//$dBhandler->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
									
											$prepstmt = $dBhandler->prepare("SELECT * FROM HPVP_OSGP_USER WHERE HOU_ID = ? AND HOU_PASSWORD = SHA2(?,256)");
											$prepstmt->execute([$id, $currPass]);
											
											if($prepstmt->rowCount()){
												
												$statement = $dBhandler->prepare("UPDATE HPVP_OSGP_USER SET HOU_PASSWORD = SHA2(:newPass,256) WHERE HOU_ID = :id");
                                
												$statement->execute(array(
															"newPass" => $newPass,
															"id" => $id                             			
														));	
												
												if ($statement->rowCount() > 0) {
													echo "Password Updated</h3>";
												} 											
												
											}else{
												echo "Incorrect Current Password";
											}
										}else{
											echo "New Passwords do not Match";
										}
									}
                                }else{
                                    header("Location: index.php?err=Session Expired");
                                }
                            }catch(Exception $e){
                                echo $e;
                            }finally{
                                
                            }
                        ?> 
                    </div>
                </main>
                <footer class="py-4 bg-light mt-auto">
                    <?php include 'footer.php'; ?>  
                </footer>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
        <script src="js/datatables-simple-demo.js"></script>
    </body>
</html>

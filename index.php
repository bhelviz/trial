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
        <title>Login - OS Material Gatepass</title>
        <link href="css/styles.css" rel="stylesheet" />
		<link href="css/osgp.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    </head>
    <body class="bg-primary">
        <div id="layoutAuthentication">
            <div id="layoutAuthentication_content">
                <main>
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-5">
                                <div class="card shadow-lg border-0 rounded-lg mt-5">
                                    <div class="card-header">
                                        <div class="row">
                                            <div class="col-3 d-flex align-items-center justify-content-center">
                                                <img src="assets/img/logo.png" alt="BHEL" />
                                            </div>
                                            <div class="col-9">
                                                <h3 class="text-center font-weight-light">BHEL-HPVP, Visakhapatnam</h3>
                                                <h4 class="text-center font-weight-light">Outsourcing Gatepass</h4>
                                            </div>
                                        </div>                                        
                                    </div>
                                    <div class="card-body">
                                        <form name="login" class="needs-validation" method="post" action="login.php">
                                            <div class="form-floating mb-3">
                                                <input class="form-control" id="userid" name="userid" type="number" placeholder="Staffno / Vendor Code" />
                                                <label for="inputUserID">User ID</label>
                                            </div>
                                            <div class="form-floating mb-3">
                                                <input class="form-control" id="password" name="password" type="password" placeholder="Password" />
                                                <label for="inputPassword">Password</label>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <img src="captchaImage.php" alt="CAPTCHA Image" id="captchaImage">
                                                </div>
                                                <div id="captchaRefresh">
													<i class="fa fa-refresh" aria-hidden="true"></i>
                                                </div>
                                            </div>                                            
                                            <div class="form-floating mb-3">
                                                <input class="form-control" id="captcha" name="captcha" type="text" placeholder="Captcha" />
                                                <label for="inputCaptcha">Captcha</label>                                                
                                                <input type="hidden" name="anticsrf" id="anticsrf" value="<?php echo hash_hmac("sha256", session_id() , "Gosthani", false);?>" />
                                            </div>
                                        </form>
                                            <div class="d-flex align-items-center justify-content-between mt-4 mb-0">                                                
                                                <button class="btn btn-primary w-100" type="submit" id="login-btn">Login</button>
                                            </div>                                        
                                    </div>
                                    <div class="card-footer text-center py-3">
                                        <div class="small d-flex align-items-center justify-content-between">
                                            <a href="#" data-bs-toggle="modal" data-bs-target="#basicModal">Forgot Password?</a>                                            
                                            <p class="mb-0 text-danger" id="loginmsg"><?php echo htmlspecialchars($_GET['err'] ?? '', ENT_QUOTES, 'UTF-8') ;?></p>                                
                                            <a href="#" data-bs-toggle="modal" data-bs-target="#basicModal">Trouble Logging In?</a>
                                        </div>
                                        <div class="modal fade" id="basicModal" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                      <h5 class="modal-title">Contact</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body text-start">
                                                        0891-288-1167, <a href="mailto:ssmohanta@bhel.in">ssmohanta[at]bhel[dot]in</a> 
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
            <div id="layoutAuthentication_footer">
                <footer class="py-4 bg-light mt-auto">
                    <div class="container-fluid px-4">
                        <div class="d-flex align-items-center justify-content-between small">
                            <div class="text-muted">&copy; DTG Department, BHEL - HPVP Visakhapatnam</div>
                            <div>
                                <!--<a href="#">Disclaimer</a>-->
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
        <!-- Custom JS File -->
        <script src="js/jquery-3.7.1.min.js"></script>
        <script src="js/crypto-js.min.js"></script>
        <script src="js/osgp.js"></script>
    </body>
</html>

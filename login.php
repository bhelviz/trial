<?php
require_once 'config.php';

/*if($_SERVER['HTTP_HOST'] == "172.17.111.115"){ */
	//if($_SERVER['HTTP_HOST'] == "hpvp.bhel.com"){
							
		if(isset($_POST['userid']) && isset($_POST['password'])){
			
			try{	
				//echo $_SESSION['captcha_code'];
				//echo $_POST['captcha'];
				//echo strcasecmp($_SESSION['captcha_code'], $_POST['captcha']);
				
				if($_SESSION['captcha_code'] == $_POST['captcha']){
					
					if(hash_hmac("sha256", session_id() , "Gosthani", false) == $_POST['anticsrf']){
						$userid = $_POST['userid'];
						$encryptedPassword = $_POST['password'];
						
						/*Decrypt Password*/
						$key = "1234567890ABCDEF"; // 16-byte key
						$iv = "1234567890ABCDEF";  // 16-byte IV

						// Decode the base64 encoded string
						$encryptedData = base64_decode($encryptedPassword);

						// Decrypt the data
						$decryptedPassword = openssl_decrypt($encryptedData, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
											
						$accLockstmt = $dBhandler->prepare("SELECT * FROM HPVP_OSGP_USER WHERE HOU_ID = ? AND HOU_FAILED_LOGIN >= 5");
						$accLockstmt->execute([$userid]);
						if($accLockstmt->rowCount()){
							header("Location: index.php?tab=3&err=Account%20Locked.%20Contact%20Admin");
							exit();
						}

						$prepstmt = $dBhandler->prepare("SELECT * FROM HPVP_OSGP_USER WHERE HOU_ID = ? AND HOU_PASSWORD = SHA2(?,256)");
						$prepstmt->execute([$userid, $decryptedPassword]);
						
						if($prepstmt->rowCount()){
							
							while($row = $prepstmt->fetch(PDO::FETCH_OBJ)){								
								session_regenerate_id(true);
								$_SESSION["HOU_ID"] = $row->HOU_ID;
                                $_SESSION["HOU_NAME"] = $row->HOU_NAME;
                                $_SESSION["HOU_ROLE"] = $row->HOU_ROLE;

								$loginStmt = $dBhandler->prepare("UPDATE HPVP_OSGP_USER SET HOU_LAST_LOGIN = NOW(), HOU_FAILED_LOGIN = 0 WHERE HOU_ID = ?");
								$loginStmt->execute([$userid]);								
								
							}
							if($decryptedPassword == "Bhel@2025"){
								header("Location: change_password.php?tab=5");	
							}else{
								header("Location: main.php?tab=5");	
							}
							
						}else{
							$loginFailStmt = $dBhandler->prepare("UPDATE HPVP_OSGP_USER SET HOU_FAILED_LOGIN = IFNULL(HOU_FAILED_LOGIN, 0) + 1 WHERE HOU_ID = ?");
							$loginFailStmt->execute([$userid]);	
							header("Location: index.php?tab=3&err=Login%20Failed");
						}
					}else{/*
                        echo hash_hmac("sha256", session_id() , "Gosthani", false);
                        echo "<br/>";
                        echo $_POST['anticsrf'];
                        echo "<br/>";
                        echo session_id();*/
						header("Location: index.php?tab=3&err=CSRF%20Alert.%20Request%20Denied.");
						exit();
					}
					
				}else{
					header("Location: index.php?tab=3&err=Wrong%20Captcha");
					exit();
				}
			}
			catch(Exception $e){
				echo $e;
			}
			
		}else{
			header("Location: index.php?tab=3&err=Try%20Again");
			exit();
		}
	/*}else{
		header("Location: /ErrorDocument.php?tab=0&error=403");
	}*/
?>
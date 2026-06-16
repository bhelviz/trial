/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$(document).ready(function(){  
    
    $("#captchaRefresh").click(function(){  
        $("#captchaImage").attr("src","captchaImage.php?t=" + new Date().getTime());
    });
    
    $("#login-btn").click(function() {
        if($("#userid").val() == ''){
            alert('Enter Staff-No');
            return false;
        }
        else if($("#password").val() == ''){
            alert('Enter Password');
            return false;
        }
        else if($("#captcha").val() == ''){
            alert('Enter Captcha');
            return false;
        }
        else{
            let pwd = $("#password").val();

            let key = CryptoJS.enc.Utf8.parse("1234567890ABCDEF"); // 16-byte key
            let iv = CryptoJS.enc.Utf8.parse("1234567890ABCDEF"); // 16-byte IV

            // Encrypt
            let encrypted = CryptoJS.AES.encrypt(pwd, key, {
                    iv: iv,
                    padding: CryptoJS.pad.Pkcs7,
                    mode: CryptoJS.mode.CBC
            });
            let encryptedBase64 = encrypted.toString();
            $("#password").val(encryptedBase64);
            //$("#login-password").val(md5($("#login-password").val()));
            document.login.submit();
        }
    });
	
	$(window).on('load', function () {
		if ($('body').data('auto-print') === true) {
			window.print();
		}
	});
    
});


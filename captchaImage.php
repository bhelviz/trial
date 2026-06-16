<?php 
/********************************************************************************************************************
* This script is brought to you by Vasplus Programming Blog by whom all copyrights are reserved.
* Website: www.vasplus.info
* Email: info@vasplus.info
* Please, do not remove this information from the top of this page.
*********************************************************************************************************************/
session_start();

try{
	error_reporting(E_ERROR);
	//You can do any necessary settings as you wish here
	//If you reduce the width and height of the captcha here then you have to change it in the css file as well
	$image_width = 280;
	$image_height = 50;
	$characters_on_image = 6; //Number of characters to display on the captcha image
	$font = 'images/monofont.ttf';

	//The characters that can be used in the CAPTCHA code. Avoid confusing characters (l 1 and i for example)
	$possible_letters = 'abcdfghjkmnpqrstvwxyz123456789ABC';
	$random_dots = 0;
	$random_lines = 20;
	$captcha_text_color="4caf50";    //Text color
	$captcha_noice_color = "7fbf4d"; //Noise color

	$code = '';
	$i = 0;
	while ($i < $characters_on_image) 
	{ 
		$code .= substr($possible_letters, mt_rand(0, strlen($possible_letters)-1), 1);
		$i++;
	}
	$font_size = $image_height * 0.75;
	$image = @imagecreate($image_width, $image_height);


	/*Setting the background, text and noise colours here */
	$background_color = imagecolorallocate($image, 255, 255, 255);
	
	$int = hexdec($captcha_text_color);	
	$arr_text_color = array("red" => 0xFF & ($int >> 0x10),"green" => 0xFF & ($int >> 0x8),"blue" => 0xFF & $int);
	
	$text_color = imagecolorallocate($image, $arr_text_color['red'], $arr_text_color['green'], $arr_text_color['blue']);
	
	$int = hexdec($captcha_noice_color);	
	$arr_noice_color = array("red" => 0xFF & ($int >> 0x10),"green" => 0xFF & ($int >> 0x8),"blue" => 0xFF & $int);
	
	$image_noise_color = imagecolorallocate($image, $arr_noice_color['red'], $arr_noice_color['green'], $arr_noice_color['blue']);

	/*This generates the dots randomly strings in background */
	for( $i=0; $i<$random_dots; $i++ ) 
	{
		imagefilledellipse($image, mt_rand(0,$image_width),
		mt_rand(0,$image_height), 2, 3, $image_noise_color);
	}

	/*This generates lines randomly strings in background of image */
	for( $i=0; $i<$random_lines; $i++ ) 
	{
		imageline($image, mt_rand(0,$image_width), mt_rand(0,$image_height),
		mt_rand(0,$image_width), mt_rand(0,$image_height), $image_noise_color);
	}

	/*This creates a text box and add 6 letters code in it */
	$textbox = imagettfbbox($font_size, 0, $font, $code); 
	$x = ($image_width - $textbox[4])/2;
	$y = ($image_height - $textbox[5])/2;
	imagettftext($image, $font_size, 0, $x, $y, $text_color, $font , $code);
	/* Show captcha image in the page html page */
	header('Content-Type: image/jpeg');// defining the image type to be shown in browser widow
	imagejpeg($image);//showing the image
	imagedestroy($image);//destroying the image instance
	$_SESSION['captcha_code'] = $code;


}catch(Error $e){
	echo $e;
}
?>
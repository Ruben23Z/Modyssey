<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*  Gerar um código aleatório de 5 caracteres.
 * Utiliza str_shuffle() para misturar uma string base com caracteres permitidos
 * (excluindo caracteres ambíguos como '1', 'I', 'O', '0') e substr() para
 * extrair os primeiros 5 caracteres. */
$captcha_code = substr(str_shuffle("ABCDEFGHJKLMNPQRSTUVWXYZ23456789"), 0, 5);

$_SESSION['captcha'] = $captcha_code;

$width = 120;
$height = 40;

$image = imagecreate($width, $height);

$fundo = imagecolorallocate($image, 230, 230, 230); // Cinza
$cor_texto = imagecolorallocate($image, 0, 0, 0);     // Preto
$cor_ruido = imagecolorallocate($image, 150, 150, 150); // Cinza
for ($i = 0; $i < 5; $i++) {
    imageline($image, 0, rand(0, $height), $width, rand(0, $height), $cor_ruido);
}


imagestring($image, 5, 35, 12, $captcha_code, $cor_texto);

/*  Definir o cabeçalho HTTP para informar o navegador que o conteúdo não é HTML, mas sim uma PNG. */
header("Content-type: image/png");

imagepng($image);

imagedestroy($image);
?>
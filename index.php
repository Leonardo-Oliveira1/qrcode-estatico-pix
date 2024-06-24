<?php 
    include_once('./vendor/autoload.php');    
    require_once "pix.php";

    $pix = new PayloadPix;
    $pix->setChave('56707027-7c32-4d1b-9e9a-15741e90edfc');
    $pix->setMerchant_category_code('0000');
    $pix->setMerchant_name('FULANO DE TAL');
    $pix->setMerchant_city('Natal');
    $pix->setTxid('***');
    $pix->setValor('0.00');

    $qrcode = (new \chillerlan\QRCode\QRCode())->render($pix->getPayload());
    echo "<img src='$qrcode' width=300>";
    echo '<br>';
    echo $pix->getPayload();
?>
<?php
function passato($data){
        return $data < date("Y-m-d");
    }
function checkmail($mail){
    $dominio = substr($mail,strpos($mail,"@")+1);
    return checkdnsrr($dominio, "MX");
}
?>
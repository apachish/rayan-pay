<?php

require_once("rayanpay.php");
$response = [];
$rayan_pay = new rayanpay();
$rayan_pay->request_id = $_GET['request_id'];
$token = $rayan_pay->auth();
if ($token['Status'] == 200) {
    $data = [
        'referenceId' => (int)$_GET['referenceId'],
        'header' => "",
        'content' => http_build_query($_POST)
    ];
    try {
        $response = $rayan_pay->verify($token['Response'], $data);
    } catch (Exception $exception) {
        $rayan_pay->dd($exception);
    }
} else {
    $_SESSION['error'][] = $token["Status"] . " : " . $token["Message"];
    @header('Location: ' . $rayan_pay->getUrl());
    exit;
}
include "layout.php";
?>

<?php
session_start();

require_once("rayanpay.php");

$_SESSION=[];
$rayan_pay = new rayanpay();
$validation = $rayan_pay->validationForm($_POST);
if(!empty($validation)){
    $_SESSION['error'] = $validation;
    @header('Location: ' . $rayan_pay->getUrl());
    exit;
}
$result = $rayan_pay->auth();
if ($result['Status'] == 200) {
    $order_id = $_POST['order_id'];
    $price = $_POST['price'];
    $mobile = !empty($_POST['mobile'])?$_POST['mobile']:"";
    try {
        $data = [

            "referenceId" => (int)$order_id,
            "amount" => (int)$price,
            "msisdn" => $mobile,
            "gatewayId" => 100,
            "callbackUrl" => $rayan_pay->getUrl()."verify.php?referenceId=".$order_id."&price=".$price."&request_id=".$rayan_pay->request_id."&mobile=".$mobile,
            "gateSwitchingAllowed" => true

        ];
        $result_start = $rayan_pay->start($result['Response'], $data);
        if ($result_start['Status'] != 200)
        {

            $response = $result_start;
            include "layout.php";
        }
        elseif ($result_start['Status'] == 200)
        {
            $response =  json_decode($result_start['Response'],true);
            echo $response['bankRedirectHtml'];
            exit;
        }
        else
        {
            echo "<br />کد خطا : ". $result_start["Status"];
            echo "<br />return : ". json_encode($result_start["Response"]);
            echo "نتوانست انتقال یابد";
        }
    } catch (HttpRequestException $exception) {
        $rayan_pay->dd($exception);
    } catch (Exception $exception) {
        $rayan_pay->dd($exception);
    }
}else{
    $_SESSION['error'][] =  $result["Status"] ." : " .$result["Message"];
    @header('Location: ' . $rayan_pay->getUrl());
    exit;
}


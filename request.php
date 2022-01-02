<?php

require_once("rayanpay.php");


$rayan_pay = new rayanpay();
$result = $rayan_pay->auth();
if ($result) {
    $order_id = $_POST['order_id'];
    $price = $_POST['price'];
    $mobile = !empty($_POST['mobile'])?$_POST['mobile']:"";
    try {
        $data = [

            "referenceId" => (int)$order_id,
            "amount" => (int)$price,
            "msisdn" => $mobile,
            "gatewayId" => 100,
            "callbackUrl" => $rayan_pay->getUrl()."verify.php?referenceId=".$order_id."&price=".$price,
            "gateSwitchingAllowed" => true

        ];
        $result_start = $rayan_pay->start($result, $data);
        if ($result_start['Status'] != 200)
        {
            echo "<br />کد خطا : ". $result_start["Status"];
            echo $result_start["Message"];
            echo "<br />مبلغ : ". $price;
            echo "<br />کد پیگیری : ". $order_id;
            echo "<br />data : ". json_encode($data);
            echo "<br />return : ". json_encode($result_start["Response"]);
            echo "<a href='".$_SERVER['HTTP_REFERER']."'> return form</a>";
        }
        elseif ($result_start['Status'] == 200)
        {
            $response =  json_decode($result_start['Response'],true);
            echo $response['bankRedirectHtml'];
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
}
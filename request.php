<?php

require_once("rayanpay.php");


$rayan_pay = new rayanpay();
$result = $rayan_pay->auth();
if ($result) {
    $order_id = $_POST['order_id'];
    $price = $_POST['price'];
    try {
        $data = [

            "referenceId" => (int)$order_id,
            "amount" => (int)$price,
            "msisdn" => "",
            "gatewayId" => 100,
            "callbackUrl" => $_SERVER['HTTP_REFERER']."verify.php?referenceId=".$order_id."&price=".$price,
            "gateSwitchingAllowed" => true

        ];
        $result_start = $rayan_pay->start($result, $data);
        if (is_string($result_start))
        {
            echo $result_start;
            header("Location: ".$_SERVER['HTTP_REFERER']."?message=".$result_start);
            exit;
        }
        elseif (!empty($result_start['bankRedirectHtml']))
            echo $result_start['bankRedirectHtml'];
        else
            echo "نتوانست انتقال یابد";
    } catch (HttpRequestException $exception) {
        $rayan_pay->dd($exception);
    } catch (Exception $exception) {
        $rayan_pay->dd($exception);
    }
}


//if (isset($result["Status"]) && $result["Status"] == 100)
//{
//	// Success and redirect to pay
//	$zp->redirect($result["StartPay"]);
//} else {
//	// error
//	echo "خطا در ایجاد تراکنش";
//	echo "<br />کد خطا : ". $result["Status"];
//	echo "<br />تفسیر و علت خطا : ". $result["Message"];
//}
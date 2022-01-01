<?php


require_once("rayanpay.php");


$rayan_pay = new rayanpay();
$token = $rayan_pay->auth();
if ($token) {
    $data = [
        'referenceId' => (int)$_GET['referenceId'],
        'header' => "",
        'content' => http_build_query($_POST)
    ];
    try {
        $response = $rayan_pay->verify($token,$data);

        if ($response["Status"] == 200)
        {
            // Success
            echo "تراکنش با موفقیت انجام شد";
            echo "<br />مبلغ : ". $response["Amount"];
            echo "<br />کد پیگیری : ". $response["RefID"];
            echo "<br />data : ". json_encode($data);
            echo "<br />return : ". json_encode($response["Response"]);
        } else {
            // error
            echo "پرداخت ناموفق";
            echo "<br />کد خطا : ". $response["Status"];
            echo "<br />کد پیگیری : ". $response["RefID"];
            echo "<br />تفسیر و علت خطا : ". $response["Message"];
            echo "<br />return : ". json_encode($data);
            echo "<br />return : ". json_encode($response["Response"]);

        }
    }catch (Exception $exception){
        $rayan_pay->dd($exception);
    }
}

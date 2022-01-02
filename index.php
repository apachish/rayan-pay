<!DOCTYPE html>
<html>
<head>
    <title>تست پرداخت رایان پی</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>
<style>
    input[type=text], select {
        width: 100%;
        padding: 12px 20px;
        margin: 8px 0;
        display: inline-block;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
    }

    input[type=submit] {
        width: 100%;
        background-color: #4CAF50;
        color: white;
        padding: 14px 20px;
        margin: 8px 0;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    input[type=submit]:hover {
        background-color: #45a049;
    }

    div {
        border-radius: 5px;
        background-color: #f2f2f2;
        padding: 20px;
    }

    .help {
        font-size: 11px;
    }
</style>
<body>

<h3>Rayan pay</h3>
<?php

if (!empty($_GET["message"])) {
    ?>
    <div class="alert alert-danger text-right" dir="rtl" role="alert">
        <?= $_GET["message"] ?>
    </div>
    <?php
}
?>
<div>
    <form action="./request.php" method="post">
        <label for="baseUrl">baseUrl</label>
        <input type="text" id="baseUrl" name="baseUrl" class="form-control" placeholder="Your baseUrl.."
               value="https://pms.rayanpay.com/api/v1/">
        <span class="help-block" dir="rtl"> آدرس پایه درخواست اطلاعات در اخرش حتما / باشد</span>
        <label for="clientId">clientId</label>
        <input type="text" id="clientId" name="clientId" class="form-control" placeholder="Your clientId..">
        <span class="help-block" dir="rtl">شناسه شما از شرکت رایان پی.</span>
        <label for="userName">userName</label>
        <input type="text" id="userName" name="userName" class="form-control" placeholder="Your userName..">
        <span class="help-block" dir="rtl">نام کاربری شما از شرکت رایان پی.</span>
        <label for="password">password</label>
        <input type="password" id="password" class="form-control" name="password" placeholder="Your password..">
        <span class="help-block" dir="rtl">پسورد شما از شرکت رایان پی.</span>

        <label for="mobile">mobile</label>
        <input type="text" id="mobile" name="mobile" class="form-control" placeholder="Your mobile..">
        <span class="help-block" dir="rtl">شماره تلفن مورد نظر وارد نکنید یا به این صورت 989120001122 کنید</span>

        <label for="price">price</label>
        <input type="text" id="price" name="price" class="form-control" placeholder="Your price..">
        <span class="help-block" dir="rtl">مبلغ مورد نظر را وارد کنید.</span>

        <label for="order_id">order Id</label>
        <input type="text" id="order_id" name="order_id" class="form-control" placeholder="Your order id..">
        <span class="help-block" dir="rtl">شناسه ارسالی باید برای هر درخواست ارسالی یکتا باشد که بهتر است در پایگاه داده خود ذخیره کنید که تکراری ارسال نکنید.</span>


        <input type="submit" value="Submit">
    </form>
</div>

</body>
</html>



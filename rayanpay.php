<?php

class rayanpay
{
    private $clientId;
    private $userName;
    private $password;
    private $baseUrl;


    private function saveStorage()
    {
        if (!empty($_POST['clientId']) && !empty($_POST['userName']) &&
            !empty($_POST['password']) && !empty($_POST['baseUrl'])) {
            $this->clientId = $_POST['clientId'];
            $this->userName = $_POST['userName'];
            $this->password = $_POST['password'];
            $this->baseUrl = $_POST['baseUrl'];
            $data = [
                'clientId' => $_POST['clientId'],
                'userName' => $_POST['userName'],
                'password' => $_POST['password'],
                'baseUrl' => $_POST['baseUrl'],
            ];
            $text = json_encode($data);
            $mystore = file_put_contents("storage.txt", $text);


        }
    }

    private function readStorage()
    {
        $text = file_get_contents("storage.txt");
        $data = json_decode($text, true);
        $this->clientId = !empty($data['clientId']) ? $data['clientId'] : $this->clientId;
        $this->userName = !empty($data['userName']) ? $data['userName'] : $this->userName;
        $this->password = !empty($data['password']) ? $data['password'] : $this->password;
        $this->baseUrl = !empty($data['baseUrl']) ? $data['baseUrl'] : $this->baseUrl;
    }

    public function auth()
    {
        $this->saveStorage();

        $this->readStorage();

        $data = [
            "clientId" => $this->clientId,
            "userName" => $this->userName,
            "password" => $this->password,
        ];
        $url = $this->baseUrl . "auth/token/generate";//https://pms.rayanpay.com/api/v1/
        $header = [
            'Content-Type: application/json'
        ];
        list($response, $http_status) = $this->getResponse($url, $data, $header);

        return $response;
    }


    public function start($token, $data)
    {
        $header = [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ];
        $url = $this->baseUrl.'ipg/payment/start';
        list($response, $http_status) = $this->getResponse($url, $data, $header);

        if ($http_status == 401) {
            $token = $this->auth();
            return $this->start($token, $data);
        }
        $message = $this->getError($http_status, 'payment_start');
        return [
            "Status" => $http_status,
            "RefID" => $data['referenceId'],
            "Message" => $message,
            "Response" => $response
        ];
    }

    public function verify($token, $data)
    {
        $header = [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ];
        $error = "";

        $url = $this->baseUrl.'ipg/payment/response/parse';
        list($response, $http_status) = $this->getResponse($url, $data, $header);
        $result = json_decode($response, true);
        if (!empty($result['paymentId']) && !empty($result['hashedBankCardNumber'])) {
            return [
                "Status" => $http_status,
                "RefID" => $_GET['referenceId'],
                "Amount" => $_GET['price'],
                "Response" => $response
            ];
        } else {
            $status = 'failed';

            if (!empty($result['ErrorDesc'])) {
                $error = $result['ErrorDesc'];
            } else if (!empty($result['errors'])) {
                $error = json_encode($result['errors']);
            } elseif (!empty($result['error'])) {
                $error = $result['error'];
            }

            $error = $this->getError($http_status, 'payment_parse', $error);
            return [
                "Status" => $http_status,
                "RefID" => $_GET['referenceId'],
                "Message" => $error,
                "Response" => $response
            ];
        }
    }

    /**
     * @param string $url
     * @param array $data
     * @param array $header
     * @return bool|string
     */
    public function getResponse($url, array $data, array $header)
    {
        $jsonData = json_encode($data);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $jsonData,
            CURLOPT_HTTPHEADER => $header,
        ));

        $response = curl_exec($curl);

        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        return [$response, $http_status];
    }

    private function curl_check()
    {
        return (function_exists('curl_version')) ? true : false;
    }


    /**
     * @param $error
     * @param $method
     * @param $prepend
     * @return string
     */
    private function getError($error, $method, $prepend = '')
    {
        $message = "";
        if ($method == 'token') {
            switch ($error) {

                case '400' :
                    $message = 'نقص در پارامترهای ارسالی';
                    break;

                case '401' :
                    $message = 'کد کاربری/رمز عبور /کلاینت/آی پی نامعتبر است';
                    break;

                case '500' :
                    $message = 'خطایی سمت سرور رخ داده است';
                    break;
            }
        } elseif ($method == 'payment_start') {
            switch ($error) {

                case '401' :
                    $message = 'توکن نامعتبر';
                    break;

                case '601' :
                    $message = 'اتصال به درگاه خطا دارد (پرداخت ناموفق)';
                    break;

                case '500' :
                    $message = 'خطایی سمت سرور رخ داده است (احتمال تکراری بودن شماره ref شما یا اگر شماره موبایل دارید باید فرمت زیر باشد 989121112233 )';
                    break;
            }

        } elseif ($method == 'payment_parse') {
            switch ($error) {

                case '401' :
                    $message = 'توکن نامعتبر است';
                    break;

                case '500' :
                    $message = 'خطایی سمت سرور رخ داده است';
                    break;

                case '600' :
                    $message = 'وضعیت نامشخص';
                    break;

                case '601' :
                    $message = 'پرداخت ناموفق';
                    break;

                case '602' :
                    $message = 'پرداخت یافت نشد';
                    break;

                case '608' :
                    $message = 'قوانین پرداخت یافت نشد (برای پرداخت هایی که قوانین دارند)';
                    break;

                case '609' :
                    $message = 'وضعیت پرداخت نامعتبر میباشد';
                    break;
            }
        }
        return $message . " " . $prepend;
    }

    public function redirect($url)
    {
        @header('Location: ' . $url);
        echo "<meta http-equiv='refresh' content='0; url={$url}' />";
        echo "<script>window.location.href = '{$url}';</script>";
        exit;
    }

    public function dd($data)
    {
        var_dump($data);
        exit();
    }

    public function getUrl()
    {
        $protocl = "http:";
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
            $protocl = "https://";
        }
        $url = $protocl.'//'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);
        return $url;
    }


}

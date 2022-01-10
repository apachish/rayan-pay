<?php

class rayanpay
{

    private $clientId;
    private $userName;
    private $password;
    private $baseUrl;
    public $request_id = 10;

    /*
     * تابع ذخیره سازی داده های شناسه و نام کاربری و پسورد  چون از ورودی دریافت می شود  و زمان بازگشت از بانک دوباره توکن گرفته می شود  در انجا از فایل خوانده می شود
     */
    private function saveStorage()
    {
        if (!empty($_POST['clientId']) && !empty($_POST['userName']) &&
            !empty($_POST['password']) && !empty($_POST['baseUrl'])) {
            $this->clientId = $_POST['clientId'];
            $this->userName = $_POST['userName'];
            $this->password = $_POST['password'];
            $this->baseUrl = $_POST['baseUrl'];
            $text = file_get_contents("storage.txt");
            $data = json_decode($text, true);
            /*
             * request_id مقداری برای اینکه اگر چند شناسه کاربری وارد شد در زمان بازگشت به شناسه کاربری درست وصل شود
             */
            if (!empty($data)) {
                $this->request_id = array_key_last($data) + 1;
            }
            $data[$this->request_id] = [
                'clientId' => $_POST['clientId'],
                'userName' => $_POST['userName'],
                'password' => $_POST['password'],
                'baseUrl' => $_POST['baseUrl'],
            ];
            $text = json_encode($data);
            $mystore = file_put_contents("storage.txt", $text);


        }
    }

    /*
     * برای خواندن فایل ذخیره شده و پر کردن مقدار شناسه و نام کاربری و پسورد
     */
    private function readStorage()
    {
        $text = file_get_contents("storage.txt");
        $data = json_decode($text, true);

        $this->clientId = !empty($data[$this->request_id]['clientId']) ? $data[$this->request_id]['clientId'] : $this->clientId;
        $this->userName = !empty($data[$this->request_id]['userName']) ? $data[$this->request_id]['userName'] : $this->userName;
        $this->password = !empty($data[$this->request_id]['password']) ? $data[$this->request_id]['password'] : $this->password;
        $this->baseUrl = !empty($data[$this->request_id]['baseUrl']) ? $data[$this->request_id]['baseUrl'] : $this->baseUrl;
    }

    /*
     * برای چک کردن موارد ارسالی در فرم که عدد وارد شود خالی نباشد
     */
    public function validationForm($data)
    {
        $error = [];
        if (empty($data['price']) || empty($data['order_id']) || empty($data['baseUrl']) || empty($data['clientId'])
            || empty($data['userName']) || empty($data['password'])) {
            $error['fill'] = "فیلد های ستاره دار اجباری می باشد";
        }
        if ( !filter_var($data['price'], FILTER_VALIDATE_INT) ) {
            $error["price"] = "مقدار  مبلغ ارسالی عدد باشد.";
        }

        if ( !filter_var($data['order_id'], FILTER_VALIDATE_INT) ) {
            $error["price"] = "مقدار  شماره سفارش ارسالی عدد باشد.";
        }

        if ( $data['price'] <= 1000) {

            echo $error["price-gt"] = "مقدار مبلغ ارسالی بزگتر از 1000 باشد";
        }

        if ($data['mobile'] && !$this->perfix_mobile($data['mobile'])) {

            echo $error["mobile"] = " شماره موبایل باید با 98 شروع شود و یا تعداد اعداد وارد شده موبایل درست نیست";
        }
        return $error;
    }

    /*
     * برای سنجش اطلاعات مرچنت و گرفتن توکن برای ارسال درخواست های بعدی
     */
    public function auth()
    {
        $message = "";
        /*
         * اگر مقدار های شناسه و نام کاربری و پسورد در بدنه درخواست باشد گرفته شده و ذخیره می وشد
         */
        $this->saveStorage();
        /*
         * در صورتی که در بدنه درخواست مقدار های پایه نباشد از فایل ذخیره سازی خوانده شده
         */
        $this->readStorage();

        if(!$this->clientId || !$this->userName || !$this->password)
            $message = "مقدار پارمتره های شناسه و نام کاربری و پسورد خالی می باشد";
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

        $message = $this->getError($http_status, 'token');

        return [
            "Status" => $http_status,
            "Message" => $message,
            "Response" => $response
        ];
    }

    /*
     * تابع درخواست شروع و اتصال به درگاه بانک می باشد که در صورت درست بودن موارد ارسالی بدون خطا به درگاه رفته
     */
    public function start($token, $data)
    {
        $header = [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ];
        $url = $this->baseUrl . 'ipg/payment/start';
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

    /*
 * تابع درخواست  تایید بود که با توجه به گذاشتن شماره سفارش در ادرس بازگشتی در این تلبع بررسی شده و در صورت درست بودن پول از حساب کاربر کم می شود
 */
    public function verify($token, $data)
    {
        $header = [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ];
        $error = "";

        $url = $this->baseUrl . 'ipg/payment/response/parse';
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
     * تابعی برای ارسال درخواست به سرور رایان پی با تابع curl
     * @param string $url ادرس درخواست
     * @param array $data داده ارسالی  در درخواست
     * @param array $header مقدار ارایه ست شد در هد  درخواست
     * @return bool|string
     */
    public function getResponse($url, array $data, array $header)
    {
        /*
         * داده ارسالی در داخل بدنه درخواست
         */
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

    /*
     * تابعی برای چک کردن تابع curl در سروری که پروژه اجرا می شود
     */
    private function curl_check()
    {
        return (function_exists('curl_version')) ? true : false;
    }

    /*
     * تابع بررس شماره موبایل با ۹۸ شروع شود
     */
    public  function perfix_mobile($phone_number)
    {

        $pattern = "/^989[0-9]{9}$/";
        if (preg_match($pattern, $phone_number)) {
            return true;
        }
        return false;

    }

    /**
     * تابعی برای مشخص کردن پیام خطا با استفاده از کد بازگشتی از درخواست پاسخ
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


    /*
     * تابعی برای دریافت آدرس اجرای محل پروژه
     */
    public function getUrl()
    {
        $protocl = "http:";
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
            $protocl = "https://";
        }
        $url = $protocl . '//' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);

        /*
         * برای اینکه ادرس از مسیر مرور گر برداشته شده احتمال دارد اخرش به صورت پیش فرض / باشد اگر نبود گذاشته شود
         */
        if (substr($url, -1) != "/")
            $url = $url . "/";
        return $url;
    }


}

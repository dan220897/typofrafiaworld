<?php

class TinkoffPayment {
    private $terminalKey = '1744391857550';
    private $password = '1l2M#Rh4MklXdppe';
    private $apiUrl = 'https://securepay.tinkoff.ru/v2/';
    
    /**
     * Создание платежа
     */
    public function createPayment($orderId, $amount, $description, $customerData = []) {
        // Логируем входные данные
        error_log("TinkoffPayment: Создание платежа для заказа $orderId, сумма: $amount");
        
        // Проверяем минимальную сумму
        if ($amount < 1) {
            error_log("TinkoffPayment: Сумма меньше минимальной (1 руб)");
            return [
                'success' => false,
                'error' => 'Сумма должна быть не менее 1 рубля'
            ];
        }
        
        $params = [
            'TerminalKey' => $this->terminalKey,
            'Amount' => intval($amount * 100), // Сумма в копейках (целое число!)
            'OrderId' => (string)$orderId, // OrderId должен быть строкой
            'Description' => mb_substr($description, 0, 250), // Ограничиваем длину описания
        ];
        
        // Добавляем данные покупателя
        $data = [];
        if (!empty($customerData['email'])) {
            $data['Email'] = $customerData['email'];
        }
        if (!empty($customerData['phone'])) {
            // Форматируем телефон для Тинькофф (только цифры)
            $phone = preg_replace('/\D/', '', $customerData['phone']);
            if (strlen($phone) == 11 && $phone[0] == '7') {
                $data['Phone'] = '+' . $phone;
            }
        }
        
        if (!empty($data)) {
            $params['DATA'] = $data;
        }
        
        // ВАЖНО: URL-ы должны быть реальными!
        // Замените на ваш домен
        $params['SuccessURL'] = 'https://anikannx.beget.tech/payment/success';
        $params['FailURL'] = 'https://anikannx.beget.tech/payment/fail';
        
        // Добавляем токен
        $params['Token'] = $this->generateToken($params);
        
        // Логируем параметры запроса (без токена)
        $logParams = $params;
        unset($logParams['Token']);
        error_log("TinkoffPayment: Параметры запроса: " . json_encode($logParams, JSON_UNESCAPED_UNICODE));
        
        // Отправляем запрос
        $response = $this->sendRequest('Init', $params);
        
        // Логируем ответ
        error_log("TinkoffPayment: Ответ от API: " . json_encode($response, JSON_UNESCAPED_UNICODE));
        
        if ($response && isset($response['Success']) && $response['Success'] === true) {
            return [
                'success' => true,
                'paymentId' => $response['PaymentId'],
                'paymentUrl' => $response['PaymentURL']
            ];
        }
        
        // Детальная информация об ошибке
        $errorMessage = 'Unknown error';
        if (isset($response['ErrorCode'])) {
            $errorMessage = $response['ErrorCode'];
            if (isset($response['Message'])) {
                $errorMessage .= ': ' . $response['Message'];
            }
            if (isset($response['Details'])) {
                $errorMessage .= ' (' . $response['Details'] . ')';
            }
        }
        
        return [
            'success' => false,
            'error' => $errorMessage,
            'response' => $response // Добавляем полный ответ для отладки
        ];
    }
    
    /**
     * Проверка статуса платежа
     */
    public function checkPaymentStatus($paymentId) {
        $params = [
            'TerminalKey' => $this->terminalKey,
            'PaymentId' => $paymentId
        ];
        
        $params['Token'] = $this->generateToken($params);
        
        $response = $this->sendRequest('GetState', $params);
        
        if ($response && isset($response['Success']) && $response['Success'] === true) {
            return [
                'success' => true,
                'status' => $response['Status'],
                'paymentId' => $response['PaymentId']
            ];
        }
        
        return [
            'success' => false,
            'error' => $response['ErrorCode'] ?? 'Unknown error'
        ];
    }
    
    /**
     * Генерация токена для подписи запроса
     */
    private function generateToken($params) {
        // Массив для токена
        $token = [];
        
        // Добавляем пароль
        $token['Password'] = $this->password;
        
        // Добавляем все параметры кроме DATA и Receipt
        foreach ($params as $key => $value) {
            if (!is_array($value) && !in_array($key, ['Token', 'DATA', 'Receipt'])) {
                $token[$key] = $value;
            }
        }
        
        // Сортируем по ключам
        ksort($token);
        
        // Конкатенируем значения
        $tokenString = implode('', array_values($token));
        
        // Логируем для отладки
        error_log("TinkoffPayment: Строка для токена: " . $tokenString);
        
        // Возвращаем SHA-256 хеш
        return hash('sha256', $tokenString);
    }
    
    /**
     * Отправка запроса к API Тинькофф
     */
    private function sendRequest($method, $params) {
        $url = $this->apiUrl . $method;
        
        error_log("TinkoffPayment: Отправка запроса на $url");
        
        $curl = curl_init();
        
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($params),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_TIMEOUT => 30
        ]);
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);
        
        if ($error) {
            error_log("TinkoffPayment: CURL ошибка: $error");
            return false;
        }
        
        error_log("TinkoffPayment: HTTP код ответа: $httpCode");
        error_log("TinkoffPayment: Тело ответа: $response");
        
        $decodedResponse = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("TinkoffPayment: Ошибка декодирования JSON: " . json_last_error_msg());
            return false;
        }
        
        return $decodedResponse;
    }
}
<?php
// /admin/classes/SMS.php - Класс для работы с SMS.ru
class SMS {
    private $api_key;
    
    public function __construct() {
        $this->api_key = SMS_RU_API_KEY;
    }
    
    public function send($phone, $message) {
        try {
            $url = 'https://sms.ru/sms/send';
            
            $data = [
                'api_id' => $this->api_key,
                'to' => $phone,
                'msg' => $message,
                'json' => 1
            ];
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            
            $response = curl_exec($ch);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($error) {
                throw new Exception('CURL Error: ' . $error);
            }
            
            $result = json_decode($response, true);
            
            if ($result['status'] == 'OK') {
                return [
                    'success' => true,
                    'sms_id' => $result['sms'][$phone]['sms_id'] ?? null
                ];
            } else {
                throw new Exception('SMS.ru error: ' . ($result['status_text'] ?? 'Unknown error'));
            }
            
        } catch (Exception $e) {
            error_log('SMS sending error: ' . $e->getMessage());
            
            // В режиме разработки можно вернуть успех
            if (defined('DEBUG_MODE') && DEBUG_MODE) {
                return ['success' => true];
            }
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    public function getBalance() {
        $url = 'https://sms.ru/my/balance';
        
        $data = [
            'api_id' => $this->api_key,
            'json' => 1
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        if ($result['status'] == 'OK') {
            return $result['balance'];
        }
        
        return 0;
    }
}
?>
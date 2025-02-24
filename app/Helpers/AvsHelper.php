<?php

class AvsHelper{

    private  static $test_access_key = "BP7N0R26HH";
    private static $test_secret_key = "cONhB7pAXyYE3qxT"; // Replace with the actual secret key

    private  static $live_access_key = "BF7GU33TMH";
    private static $live_secret_key = "tw7PInaL8duvWbnz"; // Replace with the actual secret key
    private static $testEndPoint = "https://uat.api.hyphen.co.za/webservice/avs/avsRequest";
    private static $liveEndPoint = "https://api.hyphen.co.za/webservice/avs/avsRequest";

    public static function generate_request_token($request_data) {
        // Validate the input
        if (!is_array($request_data)) {
            throw new InvalidArgumentException("Request data must be an associative array.");
        }
    
        // Get the current date and time with milliseconds
        $now = new DateTime();
        $request_date_time = $now->format("Y-m-d H:i:s.v"); // Format: YYYY-MM-DD HH:mm:ss.SSS
    
        // Combine the values in the specified order
        $raw_token = self::$live_access_key.' '.$request_date_time.' "RequestData":';

        $raw_token .= json_encode($request_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    
        // Encode the data and secret key in UTF-8
        $data_bytes = mb_convert_encoding($raw_token, 'UTF-8');
        $live_secret_key_bytes = mb_convert_encoding(self::$live_secret_key, 'UTF-8');
    
        // Generate HMAC-SHA256 hash
        $hashed_token = strtoupper(hash_hmac('sha256', $data_bytes, $live_secret_key_bytes));
       
        return [$hashed_token, $request_date_time];
    }

    public static function send_request($request_data) {

        // Generate token and timestamp
        list($token, $request_date_time) = self::generate_request_token($request_data);

        // Prepare the request payload
        $request_payload = [
            "Authorisation" => [
                "AccessKey" => self::$live_access_key,
                "RequestToken" => $token,
                "RequestDateTime" => $request_date_time
            ],
            "RequestData" => $request_data
        ];
    
        
        // Serialize the payload to JSON
        $payload_str = json_encode($request_payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    
        // Initialize CURL to send the request
        $ch = curl_init(self::$liveEndPoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json"
        ]);
        
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload_str);
    
        // Execute CURL request
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

         // Error handling
         if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            return [
                "status_code" => $http_code,
                "error" => $error_msg,
            ];
        }
    
        // Close CURL
        curl_close($ch);
    
        // Return the response and status code
        return ["status_code" => $http_code, "body" => $response];
    }

    // Send the request
    /*echo "<pre>";
    $response = send_request($testEndPoint, $test_access_key, $token, $request_date_time, $request_data);
    echo "Response Status Code: " . $response['status_code'] . "\n";
    echo "Response Body: " . $response['body'] . "\n";

    echo "</pre>"; */

}
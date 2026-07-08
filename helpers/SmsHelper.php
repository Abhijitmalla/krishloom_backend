<?php

class SmsHelper
{
    private static $lastError = '';

    /**
     * Send OTP via Fast2SMS Quick SMS (OTP route).
     *
     * @param string $mobile  10-digit Indian mobile number
     * @param string $otp     The OTP to send
     * @return bool           true on success, false on failure
     */
    public static function sendOtp(string $mobile, string $otp): bool
    {
        self::$lastError = '';
        $env = self::loadEnv();

        if (
            !empty($env['TWILIO_ACCOUNT_SID']) &&
            !empty($env['TWILIO_AUTH_TOKEN']) &&
            !empty($env['TWILIO_PHONE'])
        ) {
            return self::sendViaTwilio($mobile, $otp, $env);
        }

        return self::sendViaFast2Sms($mobile, $otp, $env);
    }

    public static function getLastError(): string
    {
        return self::$lastError;
    }

    private static function loadEnv(): array
    {
        $envPath = __DIR__ . '/../.env';
        $env = [];

        if (!file_exists($envPath)) {
            return $env;
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || strpos($line, '#') === 0 || strpos($line, '=') === false) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $env[trim($key)] = trim($value, " \t\n\r\0\x0B\"'");
        }

        return $env;
    }

    private static function sendViaFast2Sms(string $mobile, string $otp, array $env): bool
    {
        $apiKey = $env['FAST2SMS_API_KEY'] ?? '';

        if (empty($apiKey)) {
            self::setLastError('FAST2SMS_API_KEY not found in .env');
            return false;
        }

        // Fast2SMS Quick SMS API (works without DLT/OTP verification)
        $url = 'https://www.fast2sms.com/dev/bulkV2';

        $params = http_build_query([
            'authorization' => $apiKey,
            'message'       => 'Your Krishloom Vastram affiliate registration OTP is: ' . $otp . '. Valid for 5 minutes. Do not share it with anyone.',
            'language'      => 'english',
            'route'         => 'q',
            'numbers'       => $mobile,
        ]);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url . '?' . $params,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_HTTPHEADER     => ['cache-control: no-cache'],
            CURLOPT_SSL_VERIFYPEER => false,   // XAMPP local dev
        ]);

        $response = curl_exec($ch);
        $err      = curl_error($ch);
        curl_close($ch);

        if ($err) {
            self::setLastError('Fast2SMS cURL error: ' . $err);
            return false;
        }

        $result = json_decode($response, true);

        // Fast2SMS returns {"return":true,...} on success
        if (!empty($result['return']) && $result['return'] === true) {
            return true;
        }

        self::setLastError('Fast2SMS response: ' . $response);
        return false;
    }

    private static function sendViaTwilio(string $mobile, string $otp, array $env): bool
    {
        $accountSid = $env['TWILIO_ACCOUNT_SID'];
        $authToken = $env['TWILIO_AUTH_TOKEN'];
        $from = self::formatPhoneNumber($env['TWILIO_PHONE']);
        $to = self::formatPhoneNumber($mobile);

        if (strpos($to, '+') !== 0) {
            $to = '+91' . $to;
        }

        $message = 'Your Krishloom Vastram affiliate registration OTP is: ' . $otp . '. Valid for 5 minutes. Do not share it with anyone.';
        $postFields = [
            'To'   => $to,
            'Body' => $message,
        ];

        if (!empty($env['TWILIO_MESSAGING_SERVICE_SID'])) {
            $postFields['MessagingServiceSid'] = $env['TWILIO_MESSAGING_SERVICE_SID'];
        } else {
            $postFields['From'] = $from;
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => 'https://api.twilio.com/2010-04-01/Accounts/' . rawurlencode($accountSid) . '/Messages.json',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_USERPWD        => $accountSid . ':' . $authToken,
            CURLOPT_POSTFIELDS     => http_build_query($postFields),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($err) {
            self::setLastError('Twilio cURL error: ' . $err);
            return false;
        }

        $result = json_decode($response, true);

        if ($statusCode >= 200 && $statusCode < 300 && !empty($result['sid'])) {
            return true;
        }

        $message = $result['message'] ?? $response;
        self::setLastError('Twilio response: ' . $message);
        return false;
    }

    private static function formatPhoneNumber(string $phone): string
    {
        $phone = trim($phone);
        $hasPlus = strpos($phone, '+') === 0;
        $digits = preg_replace('/\D+/', '', $phone);

        return ($hasPlus ? '+' : '') . $digits;
    }

    private static function setLastError(string $error): void
    {
        self::$lastError = $error;
        error_log('[SmsHelper] ' . $error);
    }
}

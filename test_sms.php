<?php
$key = 'ySBn0cqEQGCa2AvOVfH8gmhoPJMtT36FxdIZkL9l1UuY7RjNpwUqzuvbtdj7gixh56AaEcRyDIsJ9kPm';
$params = http_build_query([
    'authorization' => $key,
    'message'       => 'Your Krishloom Vastram affiliate registration OTP is: 123456. Valid for 5 minutes. Do not share it with anyone.',
    'language'      => 'english',
    'route'         => 'q',
    'numbers'       => '9999999999',
]);
$ch = curl_init('https://www.fast2sms.com/dev/bulkV2?' . $params);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
$resp = curl_exec($ch);
echo $resp ? $resp : 'cURL error: ' . curl_error($ch);
curl_close($ch);

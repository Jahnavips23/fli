<?php
// Google reCAPTCHA configuration
define('RECAPTCHA_SITE_KEY', '6LevP8YrAAAAAFJ7Pj_LqNdOGG8c5b1ek49m9BWU');
define('RECAPTCHA_SECRET_KEY', '6LevP8YrAAAAAMORYg6ym5ZWLBBVOjoVVCQ4PkiC');

function verifyRecaptcha($recaptchaResponse) {
    $url = 'https://www.google.com/recaptcha/api/siteverify';

    $data = [
        'secret'   => RECAPTCHA_SECRET_KEY,
        'response' => $recaptchaResponse,
        'remoteip' => $_SERVER['REMOTE_ADDR'] ?? null
    ];

    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data),
            'timeout' => 10
        ]
    ];

    $context  = stream_context_create($options);
    $result   = file_get_contents($url, false, $context);
    $resultJson = json_decode($result, true);

    // For v3 you might also check $resultJson['score'] >= 0.5
    return !empty($resultJson['success']) && $resultJson['success'] === true;
}
?>

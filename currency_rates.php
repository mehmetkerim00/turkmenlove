<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currencies = ['USD', 'RUB', 'TRY', 'MYR'];

if (
    !isset($_SESSION['exchange_rates']) || 
    !isset($_SESSION['rates_time']) || 
    (time() - $_SESSION['rates_time'] > 43200) 
) {
    $api_key = "c99cc16bfc514c9e959ea706f2cfae3b";
    $symbols = implode(',', $currencies);
    $url = "https://api.currencyfreaks.com/latest?apikey=$api_key&symbols=$symbols";

    $response = @file_get_contents($url);
    $data = json_decode($response, true);

    $rates = [];

    if (isset($data['rates']) && is_array($data['rates'])) {
        foreach ($currencies as $curr) {
            if (isset($data['rates'][$curr])) {
                $rates[$curr] = floatval($data['rates'][$curr]);
            }
        }
    }

    if (count($rates) === count($currencies)) {
        $_SESSION['exchange_rates'] = $rates;
        $_SESSION['rates_time'] = time();
    } else {
    
        $_SESSION['exchange_rates'] = [
            'USD' => 1,
            'RUB' => 90,
            'TRY' => 32,
            'MYR' => 4.7,
          
        ];
        $_SESSION['rates_time'] = time();
    }
}
?>

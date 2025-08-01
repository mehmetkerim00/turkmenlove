<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currencies = ['USD', 'RUB', 'TRY', 'MYR', 'BYN', 'EUR', 'GBP'];

if (
    !isset($_SESSION['exchange_rates']) || 
    !isset($_SESSION['rates_time']) || 
    (time() - $_SESSION['rates_time'] > 43200) // обновление каждые 12 часов
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
        // Если что-то не пришло от API — резервный курс
        $_SESSION['exchange_rates'] = [
            'USD' => 1,
            'RUB' => 90,
            'TRY' => 32,
            'MYR' => 4.7,
            'BYN' => 3.2,
            'EUR' => 0.9,
            'GBP' => 0.78
        ];
        $_SESSION['rates_time'] = time();
    }
}
?>

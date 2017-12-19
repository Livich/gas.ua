# 104.ua Client

Unofficial client for 104.ua website.

## Usage Example

```php
$client = new Livich\Gasua\GasuaClient();
$client->login("your@email", "your password");
$result = $client->getAccountInfo();
/*
    $result = [
        'balance_date' => DateTime object,
        'balance' => float,
        'debt' => float,
        'estimation' => float
    ];
*/
$result = $client->setMeterData('meter ID', 'value', time());
```
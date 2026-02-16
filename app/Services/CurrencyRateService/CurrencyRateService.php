<?php
namespace App\Services\CurrencyRateService;

use Illuminate\Support\Facades\Http;
use App\Models\CurrencyRate;

class CurrencyRateService
{
  protected $apiKey;

  public function __construct()
  {
    $this->apiKey = config('services.apilayer.key');
  }

  public function updateRates()
  {
    $response = Http::withHeaders([
      'Content-Type' => 'text/plain',
      'apikey' => $this->apiKey
    ])->get('https://api.apilayer.com/currency_data/live', [
          'source' => 'USD'
        ]);

    if (!$response->ok()) {
      throw new \Exception('Currency API failed');
    }

    $data = $response->json();

    foreach ($data['quotes'] as $pair => $value) {

      // USDEUR → EUR
      $currency = str_replace('USD', '', $pair);

      CurrencyRate::updateOrCreate(
        ['currency' => $currency],
        [
          // 1 EUR = ? USD
          'rate_to_usd' => $value > 0 ? 1 / $value : 0,
          // 1 USD = ? EUR
          'usd_to_rate' => $value,
          'last_updated_at' => now()
        ]
      );
    }
  }

  public function convertToUSD($amount, $currency)
  {
    $rate = CurrencyRate::where('currency', $currency)->first();

    if (!$rate)
      return 0;

    return $amount * $rate->rate_to_usd;
  }
}

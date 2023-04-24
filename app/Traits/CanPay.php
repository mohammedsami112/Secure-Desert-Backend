<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;

trait CanPay
{
    /**
     * Paytabs request endpoint.
     *
     * @var string
     */
    private static $PAYTABS_REQUEST = 'https://secure.paytabs.sa/payment/request';

    /**
     * Paytabs query endpoint.
     *
     * @var string
     */
    private static $PAYTABS_QUERY = 'https://secure.paytabs.sa/payment/query';

    /**
     * Get the payment url.
     *
     * @param  float  $amount
     * @param  array  $data
     * @return string
     */
    public function pay(float $amount, array $data = []): string
    {
        $data = $data + [
            'profile_id' => env('PAYTABS_PROFILE_ID'),
            'tran_type' => 'sale',
            'tran_class' => 'ecom',
            'cart_amount' => $amount,
            'cart_currency' => 'SAR',
        ];

        $response = Http::withHeaders([
            'Authorization' => env('PAYTABS_SERVER_KEY'),
        ])->post(static::$PAYTABS_REQUEST, $data);

        if (! $response->successful()) {
            return $this->fail('Payment gateway is not available.');
        }

        $response = $response->json();

        return $response['redirect_url'];
    }

    /**
     * Check if the payment is paid.
     *
     * @param  string  $reference
     * @return bool
     */
    public function paid(string $reference): bool
    {
        $response = Http::withHeaders([
            'Authorization' => env('PAYTABS_SERVER_KEY'),
        ])->post(static::$PAYTABS_QUERY, [
            'profile_id' => env('PAYTABS_PROFILE_ID'),
            'tran_ref' => $reference,
        ]);

        if (! $response->successful()) {
            return $this->fail('Payment gateway is not available.');
        }

        $response = $response->json();

        return $response['payment_result']['response_status'] === 'A';
    }
}

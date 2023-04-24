<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

trait Notify
{
    /**
     * Send firebase request
     *
     * @param  string  $url
     * @param  array  $fields
     * @return \Illuminate\Http\Client\Response
     */
    public function sendFirebaseRequest(string $url, array $fields)
    {
        return Http::withHeaders([
            'Authorization: key='.env('FIREBASE_SERVER_KEY'),
            'Content-Type: application/json',
        ])->post($url, $fields);
    }

    /**
     * Notify single token
     *
     * @param  string  $token
     * @param  string  $title
     * @param  string  $body
     * @param  array  $data
     * @return \Illuminate\Http\Client\Response
     */
    public function notifyOne(string $token, string $title, string $body, array $data = [])
    {
        return $this->sendFirebaseRequest('https://fcm.googleapis.com/fcm/send', [
            'to' => $token,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
            'data' => $data,
        ]);
    }

    /**
     * Notify multiple tokens
     *
     * @param  \Illuminate\Support\Collection  $tokens
     * @param  string  $title
     * @param  string  $body
     * @param  array  $data
     * @return \Illuminate\Http\Client\Response
     */
    public function notifyMany(Collection $tokens, string $title, string $body, array $data = [])
    {
        return $this->sendFirebaseRequest('https://fcm.googleapis.com/fcm/send', [
            'registration_ids' => $tokens,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
            'data' => $data,
        ]);
    }

    /**
     * Notify all users
     *
     * @param  string  $title
     * @param  string  $body
     * @param  array  $data
     * @return \Illuminate\Http\Client\Response
     */
    public function notifyAll(string $title, string $body, array $data = [])
    {
        $tokens = User::whereNotNull('firebase_token')->pluck('firebase_token');

        return $this->notifyMany($tokens, $title, $body, $data);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FirebaseTokenController extends Controller
{
    /**
     * Update Authenticated User's Firebase Token
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $request->validate([
            'firebase_token' => 'required|string',
        ]);

        $user = $request->user();
        $user->firebase_token = $request->firebase_token;
        $user->save();

        return $this->respond('Firebase token updated successfully.');
    }

    /**
     * Remove Authenticated User's Firebase Token
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $user = $request->user();
        $user->firebase_token = null;
        $user->save();

        return $this->respond('Firebase token removed successfully.');
    }
}

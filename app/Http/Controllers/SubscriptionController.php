<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Traits\CanPay;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    use CanPay;

    /**
     * Create a new payment intent.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $request->validate([
            'plan_id' => 'required',
        ]);

        if ($request->has('user_id')) {
            $user = User::where('username', $request->user_id)->first();

            if (!$user) {
                return $this->fail('User Not Found', 404);
            }

            if (!$user->email_verified_at) {
                return $this->fail('User Not Verified', 403);
            }

            $request['user_id'] = $user->id;
        }

        if (Subscription::where('user_id', $request->has('user_id') ? $user->id : $request->user()->id)->where('expires_at', '>', now())->exists()) {
            return $this->fail('User already subscribed.', 400);
        }

        $plan = Plan::where('id', $request->plan_id)->firstOrFail();

        $cart = Cart::create([
            'plan_id' => $request->plan_id,
            'cart_id' => uniqid('cart-'),
            'cart_description' => env('APP_NAME').' Subscription',
            'user_id' => $request->has('user_id') ? $request['user_id'] : $request->user()->id,
        ]);

        return $this->pay($plan->price, [
            'cart_id' => $cart->cart_id,
            'cart_description' => $cart->cart_description,
            'return' => route('subscription.subscribe', ['user' => $request->user()->id])
        ]);

    }

    /**
     * Subscribe the user.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'tranRef' => 'required',
            'cartId' => 'required',
        ]);
        if ($this->paid($validated['tranRef'])) {
            $cart = Cart::where('cart_id', $validated['cartId'])->firstOrFail();
            $user = $cart->user;
            $plan = $cart->plan;

            Subscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'subscribed_at' => now(),
                'expires_at' => now()->addDays($plan->days),
                'price' => $plan->price,
                'days' => $plan->days,
                'type' => $user->id != $request->user ? 1 : 0,
            ]);

            $user->save();
            return redirect(env('SUBSCRIPTION_RETURN_URL'));
        }
    }

    /**
     * Check if user is subscribed.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function check(Request $request)
    {
        $user = $request->has('user_id') ? User::findOrFail($request->user_id) : $request->user();

        return $this->respond('User subscription status.', 200, [
            'subscribed' => $user->subscribed(),
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CourseEnrollment;
use App\Models\User;
use App\Traits\CanPay;
use Illuminate\Http\Request;

class CourseEnrollmentController extends Controller
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
        $validated = $request->validate([
            'course_id' => 'required',
            'course_price' => 'required' // Added By Mohammed
        ]);

        if ($request->has('user_id')) {
            $user_id = $request->user_id;
        } else if ($request->has('username')) {
            $user_id = User::where('username', $request->username)->first()->id;
        } else {
            $user_id = $request->user()->id;
        }

        $cart = Cart::create([
            'cart_id' => uniqid('cart-'),
            'cart_description' => env('APP_NAME').' Subscription',
            'user_id' => $user_id,
            'course_id' => $validated['course_id'],
        ]);


        // return $this->pay(100, [
        //     'cart_id' => $cart->cart_id,
        //     'cart_description' => $cart->cart_description,
        //     'return_url' => route('enrollment.subscribe'),
        // ]);

        // Edited By Mohammed
        return $this->pay($validated['course_price'], [
            'cart_id' => $cart->cart_id,
            'cart_description' => $cart->cart_description,
            'return' => route('enrollment.subscribe')
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
            $course = $cart->course;

            CourseEnrollment::create([
                'user_id' => $user->id,
                'course_id' => $course->id,
            ]);
        }

        return redirect(env('SUBSCRIPTION_RETURN_URL'));
    }

    /**
     * Check if user is subscribed.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function check(Request $request)
    {
        $validated = $request->validate(['course_id' => 'required']);
        $user = $request->has('user_id') ? User::findOrFail($request->user_id) : $request->user();

        return $this->respond('Course subscription status.', 200, [
            'subscribed' => CourseEnrollment::where('user_id', $user->id)->where('course_id', $validated['course_id'])->exists(),
        ]);
    }
}

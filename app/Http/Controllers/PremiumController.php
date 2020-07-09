<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Cashier\Cashier;

class PremiumController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            $user = Auth::user();
            $intent = $user->createSetupIntent();
            if (!empty($user->invoices())) {
                $invoices = $user->invoices();
                $payments = [];
                foreach ($invoices as $invoice) {
                    foreach ($invoice->subscriptions() as $subscription) {
                        $item = $subscription->asStripeInvoiceLineItem();
                        $payments[] = [
                            'date' => date('j M, Y', $item->price->created),
                            'name' => $item->plan->nickname,
                            'amount' => Cashier::formatAmount($item->amount, $item->currency),
                            'pdf' => $invoice->asStripeInvoice()->invoice_pdf,
                        ];
                    }
                }
            }
            if (!empty($user->upcomingInvoice())) {
                $nextInvoiceDate = date('j M, Y', $user->upcomingInvoice()->next_payment_attempt);
            }
            if (!empty($user->subscription('premium'))) {
                $endsAt = $user->subscription('premium')->ends_at;
                if (date('dmY') === date('dmY', strtotime($endsAt))) {
                    $format = 'H:i';
                } else {
                    $format = 'j M, Y';
                }
                $endDate = date($format, strtotime($endsAt));
            }
            if (!empty($user->defaultPaymentMethod())) {
                $defaultCard = $user->defaultPaymentMethod()->card;
                $card = ucfirst($defaultCard->brand) . ' ending ' . $defaultCard->last4 . ' expires ' . str_pad($defaultCard->exp_month, 2, '0', STR_PAD_LEFT) . '/' . $defaultCard->exp_year;
            }
        }

        return view('premium', [
            'intent' => $intent ?? null,
            'payments' => $payments ?? null,
            'nextInvoiceDate' => $nextInvoiceDate ?? null,
            'endDate' => $endDate ?? null,
            'card' => $card ?? null,
            'cardBrand' => $defaultCard->brand ?? null,
        ]);
    }

    public function register(Request $request)
    {
        if (!$request->ajax()) {
            return back();
        }

        if (!Auth::user()->subscribedToPlan(env('STRIPE_PLAN'), 'premium')) {
            Auth::user()->newSubscription('premium', env('STRIPE_PLAN'))->create($request['paymentMethod']);
        }

        return false;
    }

    public function update(Request $request)
    {
        if (!$request->ajax()) {
            return back();
        }

        $user = Auth::user();
        $user->deletePaymentMethods();
        $user->updateDefaultPaymentMethod($request['paymentMethod']);

        return false;
    }

    public function cancel()
    {
        Auth::user()->subscription('premium')->cancel();

        return back();
    }

    public function resume()
    {
        if (Auth::user()->subscription('premium')->onGracePeriod()) {
            Auth::user()->subscription('premium')->resume();
        }

        return back();
    }

    public function restart()
    {
        if (!Auth::user()->subscribedToPlan(env('STRIPE_PLAN'), 'premium')) {
            Auth::user()->newSubscription('premium', env('STRIPE_PLAN'))->add();
        }

        return back();
    }
}

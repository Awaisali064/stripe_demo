<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Codenixsv\CoinGeckoApi\CoinGeckoClient;
// use Session;
use Stripe;
use Auth;
use App\User;

class StripePaymentController extends Controller
{

  public function subscribe(Request $request, $type)
  {
    if($type == 'monthly')
    {
      $price = env('MONTHLY_PRICE');     
    }else if($type == 'anualy')
    {
      $price = env('ANUAL_PRICE');
    }

    Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
    $customer = \Stripe\Customer::create();
    Auth::user()->stripe_customer_id = $customer->id;
    Auth::user()->save();
    $session = \Stripe\Checkout\Session::create([
      'success_url' => env('APP_URL') . '/success/'. $type. '/{CHECKOUT_SESSION_ID}',
      'cancel_url' => env('APP_URL') . '/cancel',
      'mode' => 'subscription',
      'customer' =>  $customer->id, 
      // 'automatic_tax' => ['enabled' => true],
      'line_items' => [[
        'price' => $price,
        'quantity' => 1,
      ]]
    ]);
    header("Location: " . $session->url);
          exit();
  }

  public function subscreption()
  {
    return view('subscreption');
  }
  public function stripe_success(Request $request, $subscreption_type, $session_id)
  {
    Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
    $checkout_session = \Stripe\Checkout\Session::retrieve($session_id);

   if($checkout_session->payment_status == 'paid')
   {
    Auth::user()->stripe_subscreption_id = $checkout_session->subscription;
    Auth::user()->stripe_subscreption_type = $subscreption_type; 
    Auth::user()->auto_renew = 1;   
    Auth::user()->save();
    return view('stripe_success', compact('subscreption_type'));
   }
  }

  public function cancel()
  {
    return view('cancel');
  }

  public function stripe_customer_portal()
  {
    Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
         $session = \Stripe\BillingPortal\Session::create([
                  'customer' => Auth::user()->stripe_customer_id,
                  'return_url' => env('APP_URL') . '/home',
                ]);

                // Redirect to the customer portal.
                header("Location: " . $session->url);
                exit();
  }

  public function stripe_webhook()
  {
         Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
          //  liveWEBHOOK_SECRET
          $endpoint_secret = env('WEBHOOK_SECRET');
          $prise_mon = env('MONTHLY_PRICE');
          $prise_year = env('ANUAL_PRICE');
          $payload = @file_get_contents('php://input');
          $event = null;

          try {
            $event = \Stripe\Event::constructFrom(
              json_decode($payload, true)
            );
          } catch(\UnexpectedValueException $e) {
            // Invalid payload
            echo '⚠️  Webhook error while parsing basic request.';
            http_response_code(400);
            exit();
          }
          if ($endpoint_secret) {
            // Only verify the event if there is an endpoint secret defined
            // Otherwise use the basic decoded event
            $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
            try {
              $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
              );
            } catch(\Stripe\Exception\SignatureVerificationException $e) {
              // Invalid signature
              echo '⚠️  Webhook error while validating signature.';
              http_response_code(400);
              exit();
            }
          }

          $user = User::where('stripe_customer_id', $event->data->object->customer)->first();
          // Handle the event
          switch ($event->type) {
              case 'customer.subscription.updated':
                  if($event->data->object->cancel_at_period_end)
                  {
                    $user->auto_renew = 0;
                    $user->save();
                   // action here
                  }else{
                      if($event->data->object->items->data[0]->price->id == $prise_mon)
                      {
                        $user->stripe_subscreption_type = 'monthly';
                        $user->save();
                          // action here
                      }elseif($event->data->object->items->data[0]->price->id == $prise_year)
                      {
                        $user->stripe_subscreption_type = 'anualy';
                        $user->save();
                        //  action here
                      }
                        } 
                    break;
                    
                    case 'customer.subscription.deleted':
                      $user->stripe_subscreption_id = null;
                      $user->stripe_subscreption_type = null;
                      $user->auto_renew = 0;
                      $user->save();
                        // action here
                        break;
                  default:
                    // Unexpected event type
                    error_log('Received unknown event type');
                }
                http_response_code(200);
  }
  public function onboard_account()
  {    
        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
        $account = $stripe->accounts->create(['type' => 'express']);        
        Auth::user()->stripe_connect_account_id = $account->id;   
        Auth::user()->save();
         $accountLinks =    $stripe->accountLinks->create(
                [
                  'account' => $account->id,
                  'refresh_url' => env('APP_URL') . '/onboard_account',
                  'return_url' =>  env('APP_URL') . '/complete_onboard',
                  'type' => 'account_onboarding',
                ]
              );

              header("Location: " . $accountLinks->url);
              exit();
  }

  public function complete_onboard()
  {
         Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
         $stripe_connected_account = \Stripe\Account::retrieve(auth()->user()->stripe_connect_account_id);
         if($stripe_connected_account->capabilities->transfers == 'active')
         {
            Auth::user()->stripe_connected_account_status = $stripe_connected_account->capabilities->transfers;
            Auth::user()->save();
            $status = 'Your account activated to accept stripe payments.';
            return view('connected_status', compact('status'));
         }else if($stripe_connected_account->capabilities->transfers == 'inactive')
         {
          Auth::user()->stripe_connected_account_status = $stripe_connected_account->capabilities->transfers;
          Auth::user()->save();
          $status = 'Your account was not activated. Try again and provide correct details.';
          return view('connected_status', compact('status'));
         }
  }

  public function make_payment()
  {
    $sample_amount = 100;
    $sample_user_id = 1;
    $user = User::find($sample_user_id);
    Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
    $session = \Stripe\Checkout\Session::create([
      'line_items' => [[
        'price_data' => [
          'currency' => 'USD',
          'product_data' => [
            'name' =>  'Test product',
          ],
          'unit_amount' => $sample_amount * 100,
        ],
        'quantity' => 1,
      ]],
      'mode' => 'payment',
      'success_url' => env('APP_URL') . '/payment_success',
      'cancel_url' => env('APP_URL') . '/payment_failure',
      'payment_intent_data' => [
        'application_fee_amount' => 10 * 100,
        'transfer_data' => [
          'destination' => $user->stripe_connect_account_id,
        ],
      ],
    ]);
    header("Location: " . $session->url);
          exit();
  }
  public function payment_success()
  {
    $status = 'Your payment received and your order details are here.';
    return view('payment_status', compact('status'));

  }

  public function payment_failure()
  {
    $status = 'Your payment canceled.';
    return view('payment_status', compact('status'));
  }

  }



<?php

namespace App;

class Stripe
{
    protected $stripe;

    public function createCustomerByCardToken($token)
    {
        // return 'create customer by card token';
        // die();
        $customer = $this->addCustomer();
        // die();
        if ($customer == null) {
            print_r('No customer id');
            exit;
        }

        $stripe = $this->getStripe();

        $resp = $stripe->customers->createSource(
          $customer['id'],
          [
                'source' => $token,
          ]
        );

        $customer['card'] = $resp;

        return $customer;
    }

    public function addCustomer()
    {
        // echo 'oky in add customer';
        $stripe = $this->getStripe();

        // return json_encode($stripe->customers);
        // var_dump($stripe);
        // die();

        try {
            $customerResp = $stripe->customers->create([
                'description' => 'Customer for fairfare app',
            ]);
            // die();
        } catch (Exception $e) {
            print_r('In add customer error');
            exit;
        }

        if (isset($customerResp['id']) && isset($customerResp['object']) && $customerResp['object'] == 'customer') {
            return $customerResp;
        } else {
            print_r('In customer id not found error');
            exit;
        }
    }

    public function getStripe()
    {
        // echo 'in get stripe';
        if ($this->stripe == null) {
            $this->stripe = new \Stripe\StripeClient(
                env('STRIPE_SECRET')
            );
        }

        return $this->stripe;
    }

    public function createCardToken($card_number, $exp_month, $exp_year, $cvc)
    {
        $stripe = $this->getStripe();

        try {
            $cardTokenResp = $stripe->tokens->create([
              'card' => [
                'number' => $card_number,
                'exp_month' => $exp_month,
                'exp_year' => $exp_year,
                'cvc' => $cvc,
              ],
            ]);
        } catch (Exception $e) {
            return 'In card token create error';
        }

        return $cardTokenResp;
    }

    public function getCardTokenId($card_number, $exp_month, $exp_year, $cvc)
    {
        $cardResp = $this->createCardToken($card_number, $exp_month, $exp_year, $cvc);

        if (isset($cardResp['object']) && $cardResp['object'] == 'token' && isset($cardResp['id'])) {
            return $cardResp['id'];
        } else {
            return $cardResp;
        }
    }
}

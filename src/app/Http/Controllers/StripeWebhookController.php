<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Exception;

use Laravel\Cashier\Cashier;
use Stripe\Event as StripeEvent;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Support\Facades\Log;

use \App\Plan;
use \App\Setting;

use \App\Http\Traits\SwitchPlans;

class StripeWebhookController extends Controller
{
    use SwitchPlans;
    /**
     * Handle a Stripe webhook call.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handleWebhook(Request $request)
    {
        
        $payload = json_decode($request->getContent(), true);

        if (! $this->eventExistsOnStripe($payload['id'])) {
            return;
        }

        $method = 'handle'.studly_case(str_replace('.', '_', $payload['type']));

        if (method_exists($this, $method)) {
            return $this->{$method}($payload);
        } else {
            return $this->missingMethod();
        }
    }

    

        /**
     * Handle a renew event for customer from a Stripe subscription.
     *
     * @param  array  $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handleInvoicePaymentSucceeded(array $payload)
    {
        // get the school account
        $school = $this->getUserByStripeId($payload['data']['object']['customer']);
        // log the cancellation event
        Log::info("Account of ". $school->name ." renews the subscription");

        $billing_cycle = Setting::where('name', 'Billing cycle')->first()->value;
        //renew plan
        if($billing_cycle === "year")
            $school->plan_renews_at = date('Y-m-d', strtotime('+1 years'));
        if($billing_cycle === "month")
            $school->plan_renews_at = date('Y-m-d', strtotime('+1 month'));
            
        $school->save();
        Log::info("Subscription of ". $school->name ." is renewed successfully");
        return new Response('Webhook Handled', 200);
    }

    /**
     * Handle a cancelled customer from a Stripe subscription.
     *
     * @param  array  $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handleCustomerSubscriptionDeleted(array $payload)
    {
        // get the school account
        $school = $this->getUserByStripeId($payload['data']['object']['customer']);
        // log the cancellation event
        Log::info("Account of ". $school->name ." cancels the subscription");
        // cancel the subscription
        if ($school) {
            $school->subscriptions->filter(function ($subscription) use ($payload) {
                return $subscription->stripe_id === $payload['data']['object']['id'];
            })->each(function ($subscription) {
                $subscription->markAsCancelled();
            });
        }
        //switch account to free plan
        $free_plan = Plan::where('price',0)->first();
        $this->updatePlanAndAdjustLimit($school, $free_plan);
        Log::info("Subscription of ". $school->name ." is canceled successfully");
        return new Response('Webhook Handled', 200);
    }

    /**
     * Get the billable entity instance by Stripe ID.
     *
     * @param  string  $stripeId
     * @return \Laravel\Cashier\Billable
     */
    protected function getUserByStripeId($stripeId)
    {
        $model = Cashier::stripeModel();

        return (new $model)->where('stripe_id', $stripeId)->first();
    }

    /**
     * Verify with Stripe that the event is genuine.
     *
     * @param  string  $id
     * @return bool
     */
    protected function eventExistsOnStripe($id)
    {
        try {
            $stripe_key = Setting::where('name','Stripe Secret key')->first()->value;
            return ! is_null(StripeEvent::retrieve($id, $stripe_key));
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Verify if cashier is in the testing environment.
     *
     * @return bool
     */
    protected function isInTestingEnvironment()
    {
        return getenv('CASHIER_ENV') === 'testing';
    }

    /**
     * Handle calls to missing methods on the controller.
     *
     * @param  array  $parameters
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function missingMethod($parameters = [])
    {
        return new Response;
    }
}

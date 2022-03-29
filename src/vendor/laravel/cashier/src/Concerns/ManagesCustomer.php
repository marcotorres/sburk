<?php

namespace Laravel\Cashier\Concerns;

use Laravel\Cashier\Cashier;
use Laravel\Cashier\Exceptions\CustomerAlreadyCreated;
use Laravel\Cashier\Exceptions\InvalidCustomer;
use Stripe\Customer as StripeCustomer;

trait ManagesCustomer
{
    /**
     * Retrieve the Stripe customer ID.
     *
     * @return string|null
     */
    public function stripeId()
    {
        return $this->stripe_id;
    }

    /**
     * Determine if the entity has a Stripe customer ID.
     *
     * @return bool
     */
    public function hasStripeId()
    {
        return ! is_null($this->stripe_id);
    }

    /**
     * Determine if the entity has a Stripe customer ID and throw an exception if not.
     *
     * @return void
     *
     * @throws \Laravel\Cashier\Exceptions\InvalidCustomer
     */
    protected function assertCustomerExists()
    {
        if (! $this->hasStripeId()) {
            throw InvalidCustomer::notYetCreated($this);
        }
    }

    /**
     * Create a Stripe customer for the given model.
     *
     * @param  array  $options
     * @return \Stripe\Customer
     *
     * @throws \Laravel\Cashier\Exceptions\CustomerAlreadyCreated
     */
    public function createAsStripeCustomer(array $options = [])
    {
        if ($this->hasStripeId()) {
            throw CustomerAlreadyCreated::exists($this);
        }

        if (! array_key_exists('email', $options) && $email = $this->stripeEmail()) {
            $options['email'] = $email;
        }

        // Here we will create the customer instance on Stripe and store the ID of the
        // user from Stripe. This ID will correspond with the Stripe user instances
        // and allow us to retrieve users from Stripe later when we need to work.
        $customer = StripeCustomer::create(
            $options, $this->stripeOptions()
        );

        $this->stripe_id = $customer->id;

        $this->save();

        return $customer;
    }

    /**
     * Update the underlying Stripe customer information for the model.
     *
     * @param  array  $options
     * @return \Stripe\Customer
     */
    public function updateStripeCustomer(array $options = [])
    {
        return StripeCustomer::update(
            $this->stripe_id, $options, $this->stripeOptions()
        );
    }

    /**
     * Get the Stripe customer instance for the current user or create one.
     *
     * @param  array  $options
     * @return \Stripe\Customer
     */
    public function createOrGetStripeCustomer(array $options = [])
    {
        if ($this->hasStripeId()) {
            return $this->asStripeCustomer();
        }

        return $this->createAsStripeCustomer($options);
    }

    /**
     * Get the Stripe customer for the model.
     *
     * @return \Stripe\Customer
     */
    public function asStripeCustomer()
    {
        $this->assertCustomerExists();

        return StripeCustomer::retrieve($this->stripe_id, $this->stripeOptions());
    }

    /**
     * Get the email address used to create the customer in Stripe.
     *
     * @return string|null
     */
    public function stripeEmail()
    {
        return $this->email;
    }

    /**
     * Apply a coupon to the billable entity.
     *
     * @param  string  $coupon
     * @return void
     */
    public function applyCoupon($coupon)
    {
        $this->assertCustomerExists();

        $customer = $this->asStripeCustomer();

        $customer->coupon = $coupon;

        $customer->save();
    }

    /**
     * Get the Stripe supported currency used by the entity.
     *
     * @return string
     */
    public function preferredCurrency()
    {
        return config('cashier.currency');
    }

    /**
     * Determine if the customer is not exempted from taxes.
     *
     * @return bool
     */
    public function isNotTaxExempt()
    {
        return $this->asStripeCustomer()->tax_exempt === StripeCustomer::TAX_EXEMPT_NONE;
    }

    /**
     * Determine if the customer is exempted from taxes.
     *
     * @return bool
     */
    public function isTaxExempt()
    {
        return $this->asStripeCustomer()->tax_exempt === StripeCustomer::TAX_EXEMPT_EXEMPT;
    }

    /**
     * Determine if reverse charge applies to the customer.
     *
     * @return bool
     */
    public function reverseChargeApplies()
    {
        return $this->asStripeCustomer()->tax_exempt === StripeCustomer::TAX_EXEMPT_REVERSE;
    }

    /**
     * Get the default Stripe API options for the current Billable model.
     *
     * @param  array  $options
     * @return array
     */
    public function stripeOptions(array $options = [])
    {
        return Cashier::stripeOptions($options);
    }
}

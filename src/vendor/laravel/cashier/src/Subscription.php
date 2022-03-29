<?php

namespace Laravel\Cashier;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Laravel\Cashier\Concerns\Prorates;
use Laravel\Cashier\Exceptions\IncompletePayment;
use Laravel\Cashier\Exceptions\SubscriptionUpdateFailure;
use LogicException;
use Stripe\Subscription as StripeSubscription;

class Subscription extends Model
{
    use Prorates;

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['items'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'quantity' => 'integer',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'trial_ends_at', 'ends_at',
        'created_at', 'updated_at',
    ];

    /**
     * The date on which the billing cycle should be anchored.
     *
     * @var string|null
     */
    protected $billingCycleAnchor = null;

    /**
     * Get the user that owns the subscription.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->owner();
    }

    /**
     * Get the model related to the subscription.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        $model = config('cashier.model');

        return $this->belongsTo($model, (new $model)->getForeignKey());
    }

    /**
     * Get the subscription items related to the subscription.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany(SubscriptionItem::class);
    }

    /**
     * Determine if the subscription has multiple plans.
     *
     * @return bool
     */
    public function hasMultiplePlans()
    {
        return is_null($this->stripe_plan);
    }

    /**
     * Determine if the subscription has a single plan.
     *
     * @return bool
     */
    public function hasSinglePlan()
    {
        return ! $this->hasMultiplePlans();
    }

    /**
     * Determine if the subscription has a specific plan.
     *
     * @param  string  $plan
     * @return bool
     */
    public function hasPlan($plan)
    {
        if ($this->hasMultiplePlans()) {
            return $this->items->contains(function (SubscriptionItem $item) use ($plan) {
                return $item->stripe_plan === $plan;
            });
        }

        return $this->stripe_plan === $plan;
    }

    /**
     * Get the subscription item for the given plan.
     *
     * @param  string  $plan
     * @return \Laravel\Cashier\SubscriptionItem
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findItemOrFail($plan)
    {
        return $this->items()->where('stripe_plan', $plan)->firstOrFail();
    }

    /**
     * Determine if the subscription is active, on trial, or within its grace period.
     *
     * @return bool
     */
    public function valid()
    {
        return $this->active() || $this->onTrial() || $this->onGracePeriod();
    }

    /**
     * Determine if the subscription is incomplete.
     *
     * @return bool
     */
    public function incomplete()
    {
        return $this->stripe_status === StripeSubscription::STATUS_INCOMPLETE;
    }

    /**
     * Filter query by incomplete.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeIncomplete($query)
    {
        $query->where('stripe_status', StripeSubscription::STATUS_INCOMPLETE);
    }

    /**
     * Determine if the subscription is past due.
     *
     * @return bool
     */
    public function pastDue()
    {
        return $this->stripe_status === StripeSubscription::STATUS_PAST_DUE;
    }

    /**
     * Filter query by past due.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopePastDue($query)
    {
        $query->where('stripe_status', StripeSubscription::STATUS_PAST_DUE);
    }

    /**
     * Determine if the subscription is active.
     *
     * @return bool
     */
    public function active()
    {
        return (is_null($this->ends_at) || $this->onGracePeriod()) &&
            $this->stripe_status !== StripeSubscription::STATUS_INCOMPLETE &&
            $this->stripe_status !== StripeSubscription::STATUS_INCOMPLETE_EXPIRED &&
            (! Cashier::$deactivatePastDue || $this->stripe_status !== StripeSubscription::STATUS_PAST_DUE) &&
            $this->stripe_status !== StripeSubscription::STATUS_UNPAID;
    }

    /**
     * Filter query by active.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeActive($query)
    {
        $query->where(function ($query) {
            $query->whereNull('ends_at')
                ->orWhere(function ($query) {
                    $query->onGracePeriod();
                });
        })->where('stripe_status', '!=', StripeSubscription::STATUS_INCOMPLETE)
            ->where('stripe_status', '!=', StripeSubscription::STATUS_INCOMPLETE_EXPIRED)
            ->where('stripe_status', '!=', StripeSubscription::STATUS_UNPAID);

        if (Cashier::$deactivatePastDue) {
            $query->where('stripe_status', '!=', StripeSubscription::STATUS_PAST_DUE);
        }
    }

    /**
     * Sync the Stripe status of the subscription.
     *
     * @return void
     */
    public function syncStripeStatus()
    {
        $subscription = $this->asStripeSubscription();

        $this->stripe_status = $subscription->status;

        $this->save();
    }

    /**
     * Determine if the subscription is recurring and not on trial.
     *
     * @return bool
     */
    public function recurring()
    {
        return ! $this->onTrial() && ! $this->cancelled();
    }

    /**
     * Filter query by recurring.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeRecurring($query)
    {
        $query->notOnTrial()->notCancelled();
    }

    /**
     * Determine if the subscription is no longer active.
     *
     * @return bool
     */
    public function cancelled()
    {
        return ! is_null($this->ends_at);
    }

    /**
     * Filter query by cancelled.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeCancelled($query)
    {
        $query->whereNotNull('ends_at');
    }

    /**
     * Filter query by not cancelled.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeNotCancelled($query)
    {
        $query->whereNull('ends_at');
    }

    /**
     * Determine if the subscription has ended and the grace period has expired.
     *
     * @return bool
     */
    public function ended()
    {
        return $this->cancelled() && ! $this->onGracePeriod();
    }

    /**
     * Filter query by ended.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeEnded($query)
    {
        $query->cancelled()->notOnGracePeriod();
    }

    /**
     * Determine if the subscription is within its trial period.
     *
     * @return bool
     */
    public function onTrial()
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    /**
     * Filter query by on trial.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeOnTrial($query)
    {
        $query->whereNotNull('trial_ends_at')->where('trial_ends_at', '>', Carbon::now());
    }

    /**
     * Filter query by not on trial.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeNotOnTrial($query)
    {
        $query->whereNull('trial_ends_at')->orWhere('trial_ends_at', '<=', Carbon::now());
    }

    /**
     * Determine if the subscription is within its grace period after cancellation.
     *
     * @return bool
     */
    public function onGracePeriod()
    {
        return $this->ends_at && $this->ends_at->isFuture();
    }

    /**
     * Filter query by on grace period.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeOnGracePeriod($query)
    {
        $query->whereNotNull('ends_at')->where('ends_at', '>', Carbon::now());
    }

    /**
     * Filter query by not on grace period.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    public function scopeNotOnGracePeriod($query)
    {
        $query->whereNull('ends_at')->orWhere('ends_at', '<=', Carbon::now());
    }

    /**
     * Increment the quantity of the subscription.
     *
     * @param  int  $count
     * @param  string|null  $plan
     * @return $this
     *
     * @throws \Laravel\Cashier\Exceptions\SubscriptionUpdateFailure
     */
    public function incrementQuantity($count = 1, $plan = null)
    {
        $this->guardAgainstIncomplete();

        if ($plan) {
            $this->findItemOrFail($plan)->setProrate($this->prorate)->incrementQuantity($count);

            return $this->refresh();
        }

        $this->guardAgainstMultiplePlans();

        $this->updateQuantity($this->quantity + $count, $plan);

        return $this;
    }

    /**
     *  Increment the quantity of the subscription, and invoice immediately.
     *
     * @param  int  $count
     * @param  string|null  $plan
     * @return $this
     *
     * @throws \Laravel\Cashier\Exceptions\IncompletePayment
     * @throws \Laravel\Cashier\Exceptions\SubscriptionUpdateFailure
     */
    public function incrementAndInvoice($count = 1, $plan = null)
    {
        $this->guardAgainstIncomplete();

        if ($plan) {
            $this->findItemOrFail($plan)->setProrate($this->prorate)->incrementQuantity($count);

            return $this->refresh();
        }

        $this->guardAgainstMultiplePlans();

        $this->incrementQuantity($count, $plan);

        $this->invoice();

        return $this;
    }

    /**
     * Decrement the quantity of the subscription.
     *
     * @param  int  $count
     * @param  string|null  $plan
     * @return $this
     *
     * @throws \Laravel\Cashier\Exceptions\SubscriptionUpdateFailure
     */
    public function decrementQuantity($count = 1, $plan = null)
    {
        $this->guardAgainstIncomplete();

        if ($plan) {
            $this->findItemOrFail($plan)->setProrate($this->prorate)->decrementQuantity($count);

            return $this->refresh();
        }

        $this->guardAgainstMultiplePlans();

        return $this->updateQuantity(max(1, $this->quantity - $count), $plan);
    }

    /**
     * Update the quantity of the subscription.
     *
     * @param  int  $quantity
     * @param  string|null  $plan
     * @return $this
     *
     * @throws \Laravel\Cashier\Exceptions\SubscriptionUpdateFailure
     */
    public function updateQuantity($quantity, $plan = null)
    {
        $this->guardAgainstIncomplete();

        if ($plan) {
            $this->findItemOrFail($plan)->setProrate($this->prorate)->updateQuantity($quantity);

            return $this->refresh();
        }

        $this->guardAgainstMultiplePlans();

        $stripeSubscription = $this->asStripeSubscription();

        $stripeSubscription->quantity = $quantity;

        $stripeSubscription->proration_behavior = $this->prorateBehavior();

        $stripeSubscription->save();

        $this->quantity = $quantity;

        $this->save();

        return $this;
    }

    /**
     * Change the billing cycle anchor on a plan change.
     *
     * @param  \DateTimeInterface|int|string  $date
     * @return $this
     */
    public function anchorBillingCycleOn($date = 'now')
    {
        if ($date instanceof DateTimeInterface) {
            $date = $date->getTimestamp();
        }

        $this->billingCycleAnchor = $date;

        return $this;
    }

    /**
     * Force the trial to end immediately.
     *
     * This method must be combined with swap, resume, etc.
     *
     * @return $this
     */
    public function skipTrial()
    {
        $this->trial_ends_at = null;

        return $this;
    }

    /**
     * Extend an existing subscription's trial period.
     *
     * @param  \Carbon\CarbonInterface  $date
     * @return $this
     */
    public function extendTrial(CarbonInterface $date)
    {
        if (! $date->isFuture()) {
            throw new InvalidArgumentException("Extending a subscription's trial requires a date in the future.");
        }

        $subscription = $this->asStripeSubscription();

        $subscription->trial_end = $date->getTimestamp();

        $subscription->save();

        $this->trial_ends_at = $date;

        $this->save();

        return $this;
    }

    /**
     * Swap the subscription to new Stripe plans.
     *
     * @param  string|string[]  $plans
     * @param  array  $options
     * @return $this
     *
     * @throws \Laravel\Cashier\Exceptions\SubscriptionUpdateFailure
     */
    public function swap($plans, $options = [])
    {
        if (empty($plans = (array) $plans)) {
            throw new InvalidArgumentException('Please provide at least one plan when swapping.');
        }

        $this->guardAgainstIncomplete();

        $items = $this->mergeItemsThatShouldBeDeletedDuringSwap(
            $this->parseSwapPlans($plans)
        );

        $stripeSubscription = StripeSubscription::update(
            $this->stripe_id, $this->getSwapOptions($items, $options), $this->owner->stripeOptions()
        );

        $this->fill([
            'stripe_plan' => $stripeSubscription->plan ? $stripeSubscription->plan->id : null,
            'quantity' => $stripeSubscription->quantity,
            'ends_at' => null,
        ])->save();

        foreach ($stripeSubscription->items as $item) {
            $this->items()->updateOrCreate([
                'stripe_id' => $item->id,
            ], [
                'stripe_plan' => $item->plan->id,
                'quantity' => $item->quantity,
            ]);
        }

        // Delete items that aren't attached to the subscription anymore...
        $this->items()->whereNotIn('stripe_plan', $items->pluck('plan')->filter())->delete();

        $this->unsetRelation('items');

        return $this;
    }

    /**
     * Swap the subscription to new Stripe plans, and invoice immediately.
     *
     * @param  string|string[]  $plans
     * @param  array  $options
     * @return $this
     *
     * @throws \Laravel\Cashier\Exceptions\IncompletePayment
     * @throws \Laravel\Cashier\Exceptions\SubscriptionUpdateFailure
     */
    public function swapAndInvoice($plans, $options = [])
    {
        $subscription = $this->swap($plans, $options);

        $this->invoice();

        return $subscription;
    }

    /**
     * Parse the given plans for a swap operation.
     *
     * @param  string|string[]  $plans
     * @return \Illuminate\Support\Collection
     */
    protected function parseSwapPlans($plans)
    {
        return collect($plans)->mapWithKeys(function ($options, $plan) {
            $plan = is_string($options) ? $options : $plan;
            $options = is_string($options) ? [] : $options;

            return [$plan => array_merge([
                'plan' => $plan,
                'tax_rates' => $this->getPlanTaxRatesForPayload($plan),
            ], $options)];
        });
    }

    /**
     * Merge the items that should be deleted during swap into the given items collection.
     *
     * @param  \Illuminate\Support\Collection  $items
     * @return \Illuminate\Support\Collection
     */
    protected function mergeItemsThatShouldBeDeletedDuringSwap(Collection $items)
    {
        /** @var \Stripe\SubscriptionItem $stripeSubscriptionItem */
        foreach ($this->asStripeSubscription()->items->data as $stripeSubscriptionItem) {
            $plan = $stripeSubscriptionItem->plan->id;

            if (! $item = $items->get($plan, [])) {
                $item['deleted'] = true;
            }

            $items->put($plan, $item + ['id' => $stripeSubscriptionItem->id]);
        }

        return $items;
    }

    /**
     * Get the options array for a swap operation.
     *
     * @param  \Illuminate\Support\Collection  $items
     * @param  array  $options
     * @return array
     */
    protected function getSwapOptions(Collection $items, $options)
    {
        $options = array_merge([
            'items' => $items->values()->all(),
            'proration_behavior' => $this->prorateBehavior(),
            'cancel_at_period_end' => false,
        ], $options);

        if (! is_null($this->billingCycleAnchor)) {
            $options['billing_cycle_anchor'] = $this->billingCycleAnchor;
        }

        $options['trial_end'] = $this->onTrial()
                        ? $this->trial_ends_at->getTimestamp()
                        : 'now';

        return $options;
    }

    /**
     * Add a new Stripe plan to the subscription.
     *
     * @param  string  $plan
     * @param  int  $quantity
     * @param  array  $options
     * @return $this
     *
     * @throws \Laravel\Cashier\Exceptions\SubscriptionUpdateFailure
     */
    public function addPlan($plan, $quantity = 1, $options = [])
    {
        $this->guardAgainstIncomplete();

        if ($this->items->contains('stripe_plan', $plan)) {
            throw SubscriptionUpdateFailure::duplicatePlan($this, $plan);
        }

        $subscription = $this->asStripeSubscription();

        $item = $subscription->items->create(array_merge([
            'plan' => $plan,
            'quantity' => $quantity,
            'tax_rates' => $this->getPlanTaxRatesForPayload($plan),
            'proration_behavior' => $this->prorateBehavior(),
        ], $options));

        $this->items()->create([
            'stripe_id' => $item->id,
            'stripe_plan' => $plan,
            'quantity' => $quantity,
        ]);

        $this->unsetRelation('items');

        if ($this->hasSinglePlan()) {
            $this->fill([
                'stripe_plan' => null,
                'quantity' => null,
            ])->save();
        }

        return $this;
    }

    /**
     * Add a new Stripe plan to the subscription, and invoice immediately.
     *
     * @param  string  $plan
     * @param  int  $quantity
     * @param  array  $options
     * @return $this
     *
     * @throws \Laravel\Cashier\Exceptions\IncompletePayment
     * @throws \Laravel\Cashier\Exceptions\SubscriptionUpdateFailure
     */
    public function addPlanAndInvoice($plan, $quantity = 1, $options = [])
    {
        $subscription = $this->addPlan($plan, $quantity, $options);

        $this->invoice();

        return $subscription;
    }

    /**
     * Remove a Stripe plan from the subscription.
     *
     * @param  string  $plan
     * @return $this
     *
     * @throws \Laravel\Cashier\Exceptions\SubscriptionUpdateFailure
     */
    public function removePlan($plan)
    {
        if ($this->hasSinglePlan()) {
            throw SubscriptionUpdateFailure::cannotDeleteLastPlan($this);
        }

        $item = $this->findItemOrFail($plan);

        $item->asStripeSubscriptionItem()->delete([
            'proration_behavior' => $this->prorateBehavior(),
        ]);

        $this->items()->where('stripe_plan', $plan)->delete();

        $this->unsetRelation('items');

        if ($this->items()->count() < 2) {
            $item = $this->items()->first();

            $this->fill([
                'stripe_plan' => $item->stripe_plan,
                'quantity' => $item->quantity,
            ])->save();
        }

        return $this;
    }

    /**
     * Cancel the subscription at the end of the billing period.
     *
     * @return $this
     */
    public function cancel()
    {
        $subscription = $this->asStripeSubscription();

        $subscription->cancel_at_period_end = true;

        $subscription = $subscription->save();

        $this->stripe_status = $subscription->status;

        // If the user was on trial, we will set the grace period to end when the trial
        // would have ended. Otherwise, we'll retrieve the end of the billing period
        // period and make that the end of the grace period for this current user.
        if ($this->onTrial()) {
            $this->ends_at = $this->trial_ends_at;
        } else {
            $this->ends_at = Carbon::createFromTimestamp(
                $subscription->current_period_end
            );
        }

        $this->save();

        return $this;
    }

    /**
     * Cancel the subscription immediately.
     *
     * @return $this
     */
    public function cancelNow()
    {
        $subscription = $this->asStripeSubscription();

        $subscription->cancel();

        $this->markAsCancelled();

        return $this;
    }

    /**
     * Mark the subscription as cancelled.
     *
     * @return void
     * @internal
     */
    public function markAsCancelled()
    {
        $this->fill([
            'stripe_status' => StripeSubscription::STATUS_CANCELED,
            'ends_at' => Carbon::now(),
        ])->save();
    }

    /**
     * Resume the cancelled subscription.
     *
     * @return $this
     *
     * @throws \LogicException
     */
    public function resume()
    {
        if (! $this->onGracePeriod()) {
            throw new LogicException('Unable to resume subscription that is not within grace period.');
        }

        $subscription = $this->asStripeSubscription();

        $subscription->cancel_at_period_end = false;

        if ($this->onTrial()) {
            $subscription->trial_end = $this->trial_ends_at->getTimestamp();
        } else {
            $subscription->trial_end = 'now';
        }

        $subscription = $subscription->save();

        // Finally, we will remove the ending timestamp from the user's record in the
        // local database to indicate that the subscription is active again and is
        // no longer "cancelled". Then we will save this record in the database.
        $this->fill([
            'stripe_status' => $subscription->status,
            'ends_at' => null,
        ])->save();

        return $this;
    }

    /**
     * Invoice the subscription outside of the regular billing cycle.
     *
     * @param  array  $options
     * @return \Laravel\Cashier\Invoice|bool
     *
     * @throws \Laravel\Cashier\Exceptions\IncompletePayment
     */
    public function invoice(array $options = [])
    {
        try {
            return $this->user->invoice(array_merge($options, ['subscription' => $this->stripe_id]));
        } catch (IncompletePayment $exception) {
            // Set the new Stripe subscription status immediately when payment fails...
            $this->fill([
                'stripe_status' => $exception->payment->invoice->subscription->status,
            ])->save();

            throw $exception;
        }
    }

    /**
     * Sync the tax percentage of the user to the subscription.
     *
     * @return void
     * @deprecated Please migrate to the new Tax Rates API.
     */
    public function syncTaxPercentage()
    {
        $subscription = $this->asStripeSubscription();

        $subscription->tax_percent = $this->user->taxPercentage();

        $subscription->save();
    }

    /**
     * Sync the tax rates of the user to the subscription.
     *
     * @return void
     */
    public function syncTaxRates()
    {
        $stripeSubscription = $this->asStripeSubscription();

        $stripeSubscription->default_tax_rates = $this->user->taxRates();

        $stripeSubscription->save();

        foreach ($this->items as $item) {
            $stripeSubscriptionItem = $item->asStripeSubscriptionItem();

            $stripeSubscriptionItem->tax_rates = $this->getPlanTaxRatesForPayload($item->stripe_plan);

            $stripeSubscriptionItem->save();
        }
    }

    /**
     * Get the plan tax rates for the Stripe payload.
     *
     * @param  string  $plan
     * @return array|null
     */
    public function getPlanTaxRatesForPayload($plan)
    {
        if ($taxRates = $this->owner->planTaxRates()) {
            return $taxRates[$plan] ?? null;
        }
    }

    /**
     * Determine if the subscription has an incomplete payment.
     *
     * @return bool
     */
    public function hasIncompletePayment()
    {
        return $this->pastDue() || $this->incomplete();
    }

    /**
     * Get the latest payment for a Subscription.
     *
     * @return \Laravel\Cashier\Payment|null
     */
    public function latestPayment()
    {
        $paymentIntent = $this->asStripeSubscription(['latest_invoice.payment_intent'])
            ->latest_invoice
            ->payment_intent;

        return $paymentIntent
            ? new Payment($paymentIntent)
            : null;
    }

    /**
     * Make sure a subscription is not incomplete when performing changes.
     *
     * @return void
     *
     * @throws \Laravel\Cashier\Exceptions\SubscriptionUpdateFailure
     */
    public function guardAgainstIncomplete()
    {
        if ($this->incomplete()) {
            throw SubscriptionUpdateFailure::incompleteSubscription($this);
        }
    }

    /**
     * Make sure a plan argument is provided when the subscription is a multi plan subscription.
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function guardAgainstMultiplePlans()
    {
        if ($this->hasMultiplePlans()) {
            throw new InvalidArgumentException(
                'This method requires a plan argument since the subscription has multiple plans.'
            );
        }
    }

    /**
     * Update the underlying Stripe subscription information for the model.
     *
     * @param  array  $options
     * @return \Stripe\Subscription
     */
    public function updateStripeSubscription(array $options = [])
    {
        return StripeSubscription::update(
            $this->stripe_id, $options, $this->owner->stripeOptions()
        );
    }

    /**
     * Get the subscription as a Stripe subscription object.
     *
     * @param  array  $expand
     * @return \Stripe\Subscription
     */
    public function asStripeSubscription(array $expand = [])
    {
        return StripeSubscription::retrieve(
            ['id' => $this->stripe_id, 'expand' => $expand], $this->owner->stripeOptions()
        );
    }
}

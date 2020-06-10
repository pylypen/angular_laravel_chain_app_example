<?php

namespace App\Http\Controllers\API\v1\Subscriptions;

use App\Http\Requests\API\v1\Subscriptions\CreateRequest;
use App\Http\Controllers\Controller;
use App\Models\UsersOrganisations;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Http\Traits\ManageableTrait;
use Illuminate\Support\Facades\Log;
use Stripe\Token;
use Stripe\Stripe;
use Stripe\Plan;

class SubscriptionsController extends Controller
{
    use ManageableTrait;

    const MAIN_PLAN = 'main';

    const WITHOUT_PLANS = [
        'plan_FdCAGHzmjFuflG',
        'plan_FdNXE7GhUonJn4'
    ];

    /**
     * Subscribe user
     *
     * @param CreateRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function subscribe(CreateRequest $request)
    {
        $user = Auth::user();
        
        if ($user->subscribed(self::MAIN_PLAN)) {
            Log::debug('Subscribe Plan Error.', [
                'Exception' => __('subscription.only_one'),
                'user_id' => $user->id
            ]);

            return $this->_set_error([__('subscription.only_one')], 422);
        }
        
        Stripe::setApiKey(env('STRIPE_KEY'));

        /* Catch Card Error */
        try {
            $stripeToken = Token::create(array(
                "card" => array(
                    "number"    => $request->number,
                    "exp_month" => $request->exp_month,
                    "exp_year"  => $request->exp_year,
                    "cvc"       => $request->cvc,
                    "name"      => $request->name
                )
            ));
        } catch (\Exception $e){
            
            Log::debug('Subscribe Card error.', [
                'Exception' => $e,
                'user_id' => $user->id
            ]);
            
            if (!empty($e->jsonBody['error'])) {
                return $this->_set_error([$e->jsonBody['error']], 422);
            } else {
                return $this->_set_error([__('subscription.error')], 500);
            }
        }

        /* Catch Subscribe Error */
        try {
            $user->newSubscription(self::MAIN_PLAN, $request->plan)->create($stripeToken->id);
        } catch (\Exception $e){
            
            Log::debug('Subscribe error.', [
                'Exception' => $e,
                'user_id' => $user->id
            ]);
            
            if (!empty($e->jsonBody['error'])) {
                return $this->_set_error(['number' => [$e->jsonBody['error']]], 422);
            } else {
                return $this->_set_error([__('subscription.error')], 500);
            }
        }

        /* Remove Trial Perido after success payment */
        $user->trial_ends_at = null;
        $user->save();

        return $this->_set_success([]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function listPlans()
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $data = [];
        $plans = Plan::all();

        foreach ($plans->data as $p) {
            if ($p->product == env('STRIPE_PRODUCT') && !in_array($p->id, self::WITHOUT_PLANS)) {
                $data['data'][] = $p;
            }
        }

        return $this->_set_success($data);
    }

    /**
     * Cancel Subscription
     *
     * @return \Illuminate\Http\Response
     */
    public function cancelSubscription()
    {
        $subscription = [];
        
        if (Auth::user()->subscribed(self::MAIN_PLAN)) {
            /* Catch Error */
            try {
                $subscription = Auth::user()->subscription(self::MAIN_PLAN)->cancel();
            } catch (\Exception $e){
                
                Log::debug('Cancel Subscription error.', [
                    'Exception' => $e,
                    'user_id' => Auth::user()->id
                ]);
                
                if (!empty($e->jsonBody['error'])) {
                    return $this->_set_error([$e->jsonBody['error']], 500);
                } else {
                    return $this->_set_error([__('subscription.error')], 500);
                }
            }
        }

        return $this->_set_success($subscription);
    }

    /**
     * Check Subscription
     *
     * @return \Illuminate\Http\Response
     */
    public function checkSubscription()
    {
        $org_owner = UsersOrganisations::where([
            'organisation_id' => Auth::user()->last_seen_org_id,
            'is_owner' => 1
        ])->first();

        if (!$org_owner) {
            return $this->_set_error([__('subscription.error')], 422);
        }

        Stripe::setApiKey(env('STRIPE_SECRET'));

        $plan = [];
        $plans = Plan::all();

        if ($org_owner->user->subscription(self::MAIN_PLAN)) {
            foreach ($plans->data as $v) {
                if ($v->id == $org_owner->user->subscription(self::MAIN_PLAN)->stripe_plan)
                    $plan = $v;
            }
        }

        if (!$org_owner->user->subscribed(self::MAIN_PLAN)) {
            Log::debug('Subscription expired or not subscribed yet.', [
                'user_id' => Auth::user()->id,
                'org_id' => Auth::user()->last_seen_org_id
            ]);
        }

        return $this->_set_success([
            'is_trial' => $org_owner->user->trial_ends_at >= Carbon::now(),
            'trial_ends' => $org_owner->user->trial_ends_at,
            'is_subscribed' => $org_owner->user->subscribed(self::MAIN_PLAN),
            'is_owner' => (bool)$this->isOwnerOrganisation(),
            'subscribed_plan' => $plan
        ]);
    }
}

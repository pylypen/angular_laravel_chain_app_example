<?php

namespace App\Http\Middleware;

use Closure;
use App\Http\Traits\ManageableTrait;
use Illuminate\Support\Facades\Auth;

class OrganisationAdminOnly
{
    use ManageableTrait;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            if (!$this->isAdminOrganisation()) {
                return \Response::make('Forbidden', 403);
            }
        }


        return $next($request);
    }
}

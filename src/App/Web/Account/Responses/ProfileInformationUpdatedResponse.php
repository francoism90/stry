<?php

declare(strict_types=1);

namespace App\Web\Account\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\ProfileInformationUpdatedResponse as ProfileInformationUpdatedResponseContract;
use Symfony\Component\HttpFoundation\Response;

class ProfileInformationUpdatedResponse implements ProfileInformationUpdatedResponseContract
{
    public function toResponse($request): JsonResponse|Response
    {
        if ($request->wantsJson()) {
            return response()->json();
        }

        toast(title: __('Profile updated'), description: __('Your profile information has been saved.'));

        return back();
    }
}

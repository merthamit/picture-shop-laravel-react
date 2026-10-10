<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class UserController extends Controller
{
    public function store(StoreUserRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'locale' => app()->getLocale(),
        ]);

        // Queued on RabbitMQ, sent in the user's language
        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => __('messages.account_created'),
        ]);
    }

    public function auth(AuthUserRequest $request)
    {
        $user = User::whereEmail($request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'error' => __('messages.invalid_credentials'),
            ]);
        }

        return response()->json([
            'user' => UserResource::make($user),
            'access_token' => $user->createToken('new_user')->plainTextToken,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => __('messages.logged_out'),
        ]);
    }

    public function updateLocale(Request $request)
    {
        $request->validate(['locale' => 'required|in:' . implode(',', config('app.supported_locales'))]);
        $request->user()->update(['locale' => $request->locale]);

        return response()->json(['locale' => $request->locale]);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // Always answer the same way so e-mail addresses can't be enumerated
        Password::sendResetLink($request->only('email'));

        return response()->json([
            'message' => __('messages.reset_link_sent'),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', PasswordRule::min(6)],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
                // Log out every device
                $user->tokens()->delete();
                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return response()->json(['errors' => ['email' => [__($status)]]], 422);
        }

        return response()->json(['message' => __('messages.password_reset')]);
    }

    /**
     * Target of the signed link in the verification e-mail.
     */
    public function verifyEmail(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);
        $frontend = config('app.frontend_url');

        if (!$request->hasValidSignature()) {
            return redirect($frontend . '/login?verified=expired');
        }

        if (!hash_equals(sha1($user->getEmailForVerification()), (string) $hash)) {
            return redirect($frontend . '/login?verified=invalid');
        }

        if (!$user->hasVerifiedEmail() && $user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect($frontend . '/login?verified=1');
    }

    public function resendVerification(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => __('messages.already_verified')]);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json(['message' => __('messages.verification_sent')]);
    }
}

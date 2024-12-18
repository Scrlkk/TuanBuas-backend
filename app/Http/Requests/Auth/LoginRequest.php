<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $user = \App\Models\User::where('email', $this->input('email'))->first();

        if ($user) {

            if ($user->is_banned) {
                if ($user->banned_until && Carbon::parse($user->banned_until)->isFuture()) {
                    throw ValidationException::withMessages([
                        'email' => 'Akun Anda dibanned hingga ' . Carbon::parse($user->banned_until)->format('d-m-Y H:i:s'),
                    ]);
                } else {

                    $user->is_banned = false;
                    $user->login_attempts = 0;
                    $user->banned_until = null;
                    $user->save();
                }
            }
        }

        if (!Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            if ($user) {

                $user->increment('login_attempts');


                if ($user->login_attempts >= 5) {
                    $user->is_banned = true;
                    $user->banned_until = now()->addMinutes(1);
                    $user->save();

                    throw ValidationException::withMessages([
                        'email' => 'Akun Anda telah dibanned selama 1 menit.',
                    ]);
                }
                throw ValidationException::withMessages([
                    'email' => 'Login gagal. Anda telah mencoba sebanyak ' . $user->login_attempts . ' kali.',
                ]);
            }

            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        if ($user) {
            $user->login_attempts = 0;
            $user->save();
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')) . '|' . $this->ip());
    }
}

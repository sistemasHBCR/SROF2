<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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
            'email_username' => ['required', 'string'],
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

        $credentials = $this->only('email_username', 'password');
        $field = filter_var($credentials['email_username'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (! Auth::attempt([$field => $credentials['email_username'], 'password' => $credentials['password']], $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email_username' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        $this->captureUserRole();
    }
    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
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
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }


    
    //logear por username o email
   
    /**
     * Capture the authenticated user's role.
     */
    protected function captureUserRole(): void
    {
        /*
        $user = Auth::user();
        $roles = $user->roles;

        if ($roles->isNotEmpty()) {
            // Check if the user is suspended
            if ($roles->first()->id == 2) { // Assuming '2' is the ID of the suspended role
                Auth::logout();
                throw ValidationException::withMessages([
                    'email_username' => trans('auth.suspended'),
                ]);
            }
        } */
    }

     //capturar rol del usuario login
    /*
     protected function authenticated()
     {
         $user = Auth::user()->roles;
 
         if (!$user->isEmpty())
         {
             //comprobar si el esta suspendido.
             if (Auth::user()->roles->first()->id == '2')
             {
                     return redirect('/login')->with('info', 'Su cuenta está suspendida. Consulte con el administrador.', Auth::logout());
             }
         }   
 
     } */
}

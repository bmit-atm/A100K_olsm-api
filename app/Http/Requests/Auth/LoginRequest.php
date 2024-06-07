<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
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
            //'email' => ['required', 'string', 'email'],
            'username' => ['required', 'string'], // 'username' => 'required|string
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
    
        $username = $this->input('username') . '@EDU.LAN';
        $password = $this->input('password');
    
        // Erstellen Sie eine neue LDAP-Verbindung
        $connection = new \LdapRecord\Connection([
            'hosts' => [env('LDAP_HOST')],
            'port' => env('LDAP_PORT'),
            'base_dn' => env('LDAP_BASE_DN'),
            'username' => $username,
            'password' => $password,
        ]);
    
        // Versuchen Sie, sich beim LDAP-Server zu authentifizieren
        if (! $connection->auth()->attempt($username, $password)) {
            RateLimiter::hit($this->throttleKey());
    
            throw ValidationException::withMessages([
                'username' => __('auth.failed'),
            ]);
        }
    
        // Setzen Sie die Standard-Anmeldeinformationen auf die Anmeldeinformationen des Benutzers
        \LdapRecord\Container::getDefaultConnection()->setConfiguration([
            'hosts' => [env('LDAP_HOST')],
            'port' => env('LDAP_PORT'),
            'base_dn' => env('LDAP_BASE_DN'),
            'username' => $username,
            'password' => $password,
        ]);
    
         // Fetch all groups from the "Gruppen" OU in the Active Directory
        $groups = \LdapRecord\Models\ActiveDirectory\Group::in('ou=Gruppen,dc=EDU,dc=LAN')->get();

        // Process the groups...
        $groupNames = [];
        foreach ($groups as $group) {
            // Access the 'cn' attribute and add it to the array
            $groupNames[] = $group->cn[0];
        }

        // Get all existing groups from the database
        $existingGroups = \App\Models\AdGroup::all();

        // Delete groups that are not in the Active Directory
        foreach ($existingGroups as $existingGroup) {
            if (!in_array($existingGroup->name, $groupNames)) {
                $existingGroup->delete();
            }
        }

        // Add new groups to the database
        foreach ($groupNames as $groupName) {
            $group = \App\Models\AdGroup::firstOrCreate(['name' => $groupName]);
        }
        // Authentifizieren Sie den Benutzer in Laravel
        $user = \App\Models\User::where('username', $this->input('username'))->first();
        Auth::guard('api')->login($user);
        RateLimiter::clear($this->throttleKey());
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
        return Str::transliterate(Str::lower($this->input('email')).'|'.$this->ip());
    }
}

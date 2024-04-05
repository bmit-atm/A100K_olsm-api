<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use LdapRecord\Models\ActiveDirectory\Group;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use LdapRecord\Connection;
use LdapRecord\Container; 
class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
{
    $request->authenticate();

    $request->session()->regenerate();

    // Generate a JWT and return it in the response
    $token = JWTAuth::fromUser(Auth::guard('api')->user());

    \Log::info($token);
    // Validate the token and authenticate the user
    try {
        JWTAuth::setToken($token)->authenticate();
    } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
        // Handle the exception...
        return response()->json(['error' => 'Failed to authenticate JWT token'], 401);
    }

    return response()->json(compact('token'));
}
    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request)
    {
        // Invalidate the JWT
        JWTAuth::invalidate(JWTAuth::getToken());
    
        $request->session()->invalidate();
    
        $request->session()->regenerateToken();
    
        return response()->noContent();
    }

    public function getAllGroups()
    {
        // Get the existing default connection
        $connection = Container::getDefaultConnection();

        // Get the currently authenticated user
        $user = Auth::guard('api')->user();
        
        // Get the token from the request
        $token = JWTAuth::getToken();
        // Validate the token and authenticate the user
        try {
            JWTAuth::setToken($token)->authenticate();
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            // Handle the exception...
            return response()->json(['error' => 'Failed to authenticate JWT token'], 401);
        }
        // Update the connection configuration
        $connection->setConfiguration([
            'hosts' => [env('LDAP_HOST', 'ZH-EDU-SRV01.EDU.LAN')],
            'base_dn' => env('LDAP_BASE_DN', 'dc=EDU,dc=LAN'),
            'username' => 'signatur.testing@EDU.LAN',
            'password' => 'Test312.',
        ]);
    
        // Fetch all groups from the "Gruppen" OU in the Active Directory
        $groups = Group::in('ou=Gruppen,dc=EDU,dc=LAN')->get();
    
        // Process the groups...
        $groupNames = [];
        foreach ($groups as $group) {
            // Access the 'cn' attribute and add it to the array
            $groupNames[] = $group->cn;
        }
    
        return response()->json($groupNames);
    }
}

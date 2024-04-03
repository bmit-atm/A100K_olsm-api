<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use LdapRecord\Models\ActiveDirectory\Group;
use Tymon\JWTAuth\Facades\JWTAuth;
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
        $token = JWTAuth::fromUser($request->user());

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

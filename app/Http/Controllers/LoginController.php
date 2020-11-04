<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Throwable;

class LoginController extends Controller
{
    public function loginUser (Request $request) {
        // validate request
        try {
            $user = User::firstWhere('username', $request->username);
            if ($user) {
                $id = $user->id;
            } else {
                $id = -1;
            }
            $userProfile = UserProfile::firstWhere('user_id', $id);
        } catch (Throwable $e) {
            return response()->json([
                'msg' => 'Database error'
            ], 500);
        }

        if ($user) {
            if (!$user->isActivated) {
                return response()->json([
                    'msg' => 'This user it not activated, Please go to email and validate this account first'
                ], 200);
            }

            if (Hash::check($request->password, $user->password)) {
                return response()->json([
                    'user' => $user,
                    'userProfile' => $userProfile
                ], 200);
            } else {
                return response()->json([
                    'msg' => 'Username or password not correct'
                ], 200);
            }
        } else {
            return response()->json([
                'msg' => 'Username or password not correct'
            ], 200);
        }
    }
}

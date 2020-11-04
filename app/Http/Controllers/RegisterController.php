<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Throwable;

class RegisterController extends Controller
{
    public function createUser (Request $request) {
        // validate request
        try {
            $this->validate($request, [
                'username' => 'required|alpha_dash',
                'password' => 'required',
                'firstName' => 'required',
                'lastName' => 'required',
                'mobile' => 'required',
                'email' => 'required'
            ]);
            $password = Hash::make($request->password, [
                'rounds' => 12
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'msg' => $e
            ], 500);
        }

        // start transaction
        DB::beginTransaction();

        try {
            $user = User::create([
                'username' => $request->username,
                'password' => $password,
                'activatedCode' => Str::uuid()
            ]);

            $userProfile = UserProfile::create([
                'firstName' => $request->firstName,
                'lastName' => $request->lastName,
                'email' => $request->email,
                'mobile' => $request->mobile,
                'user_id' => $user->id
            ]);

            // finish transaction
            DB::commit();

            return response()->json([
                'user' => $user,
                'userProfile' => $userProfile
            ], 201);
        } catch (Throwable $e) {
            // finish transaction
            DB::rollBack();

            return response()->json([
                'msg' => $e
            ], 500);
        }

    }
}

<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Throwable;
use Carbon\Carbon;

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
            // DB::commit();

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

    public function getRegisterCountByDate (Request $request, $scopeBackDays=30) {
        $endDate = Carbon::now();
        $startDate = $endDate->subDays($scopeBackDays);

        // init dict of date
        $rawData = [];
        for ($i=0 ; $i <= $scopeBackDays; $i++) {
            $tmpDate = $startDate->copy()->addDays($i)->toDateString();
            $rawData[$tmpDate] = 0;
        }

        try {
            $registerCountData = User::where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->get(array(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as "count"')
            ));

            // update detail to date
            foreach ($registerCountData as $detail) {
                $rawData[$detail['date']] = $detail['count'];
            }

            $chartData = [
                'labels' => array_keys($rawData),
                'datasets' => [[
                    'data' => array_values($rawData),
                    'backgroundColor' => ['rgba(15, 169, 183, 1)']
                ]],
            ];

            return response()->json([
                'data' => $chartData
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
                'msg' => $e
            ], 500);
        }
    }

    public function getActivatedCountByDate (Request $request) {
        try {
            $activatedCountData = User::get(array(
                DB::raw('SUM(CASE WHEN isActivated=1 THEN 1 ELSE 0 END) as "activated"'),
                DB::raw('SUM(CASE WHEN isActivated=0 THEN 1 ELSE 0 END) as "inActivated"')
            ))->toArray();

            $chartData = [
                'labels' => array_keys($activatedCountData[0]),
                'datasets' => [[
                    'data' => array_values($activatedCountData[0]),
                    'backgroundColor' => ['rgba(15, 169, 183, 1)','rgba(183, 26, 15, 1)']
                ]]
            ];

            return response()->json([
                'data' => $chartData
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
                'msg' => $e
            ], 500);
        }
    }
}

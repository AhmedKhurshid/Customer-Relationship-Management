<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Emp_attend;
use App\Models\Leave;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DashboardController extends Controller
{
    function dashboardCount()
    {

        $today = Carbon::today('Asia/Karachi');

        $totalUser = User::where('role', 'User')->count();
        if (!$totalUser) {
            return response()->json([
                'success' => false,
                'message' => "User not found",
                'data' => null,

            ], 404);
        }

        $activeUser = User::where('role', 'User')->where('status', 'Active')->count();
        $userOnLeave = Leave::where('dateFrom', $today)->where('status', 'Approve')->count();


        return response()->json([
            'success' => true,
            'message' => "Data found",
            'data' => [
                'totalUser' => $totalUser,
                'activeUser' => $activeUser,
                'userOnLeave' => $userOnLeave
            ],

        ], 200);
    }

    // function dashboardStatistics(Request $request)
    // {
    //     $validator = Validator::make(
    //         $request->all(),
    //         [
    //             'startDate' => ['date'],
    //             'endDate' => ['date']
    //         ]
    //     );
    //     if ($validator->fails()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => $validator->messages(),
    //             'data' => null,

    //         ], 400);
    //     }

    //     // $statisticsData = [];
    //     // for ($i=0; $i <= 15; $i++) {

    //     // // for ($i=0; $i <$request->date; $i++) {

    //     //     $usersOnLeave = Leave::whereDate('dateFrom' ,'<=', $request->date->subDays(15))->count();

    //     //     // $activeUsers = Emp_attend::whereDate('checkIn',$request->date)->count();
    //     //     // if ($usersOnLeave) {
    //     //     //     $status = 'Leave'.$usersOnLeave;
    //     //     // } else {
    //     //     //     $status = 'Absent';
    //     //     // }

    //     //     // }
    //     //     // $week_data[] = [
    //     //     //     'date' => $request->date->toDateString(),
    //     //     //     'status' => $usersOnLeave,
    //     //     // ];

    //     //     // $request->date->subDay();
    //     // }



    //     // return response()->json([
    //     //     'success' => true,
    //     //     'message' => "Data found",
    //     //     'data' => [
    //     //         'totalUser' => $usersOnLeave,
    //     //         // 'activeUser' => $activeUsers,
    //     //         // 'userOnLeave' => $userOnLeave
    //     //     ],

    //     // ], 200);
    // }


    function dashboardStatistics(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'startDate' => 'date',
                'endDate' => 'date', // Add this line if you need an 'endDate'
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages(),
                'data' => null,
            ], 400);
        }

        $startDate = Carbon::parse($request->startDate);
        $endDate = Carbon::parse($request->endDate);

        // Initialize an array to store statistics for each day.
        $statisticsData = [];

        while ($startDate->lte($endDate)) {
            $usersOnLeave = Leave::whereDate('dateFrom', '<=', $startDate)
                ->whereDate('dateTo', '>=', $startDate)
                ->count();

            $statisticsData[] = [
                'date' => $startDate->toDateString(),
                'usersOnLeave' => $usersOnLeave,
            ];

            $startDate->addDay(); // Move to the next day
        }

        return response()->json([
            'success' => true,
            'message' => "Data found",
            'data' => $statisticsData,
        ], 200);
    }
}

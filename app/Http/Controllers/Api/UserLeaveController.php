<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Leaves_type;
use App\Models\User;
use App\Models\User_leave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use function PHPUnit\Framework\isNull;

class UserLeaveController extends Controller
{
    function updateUserLeave(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'sick' => ['required_without_all:annual,casual', 'integer'],
            'annual' => ['required_without_all:casual,sick', 'integer'],
            'casual' => ['required_without_all:annual,sick', 'integer'],
        ]);
        if ($validator->fails()) {
            return response()->json(
                [
                    'success' => false,
                    'message' => $validator->messages(),
                    'data' => null
                ],
                400
            );
        }
        $findUserLeaves = User_leave::where('userId', $id)->first();

        if ($findUserLeaves == null) {
            return response()->json([
                'success' => false,
                'message' => "User leave doesn't exist",
                'data' => null
            ], 404);
        }

        $findUserLeaves->sick = $request->sick;
        $findUserLeaves->annual = $request->annual;
        $findUserLeaves->casual = $request->casual;
        $findUserLeaves->save();
        return response()->json([
            'success' => true,
            'message' => 'User leave updated successfully',
            'data' => $findUserLeaves,
        ], 200);
    }


    // function showUsersLeave(Request $request)
    // {

    //     $perPage = $request->input('perPage', 10);
    //     $allUsersLeave = User_leave::paginate($perPage);

    //     if ($allUsersLeave) {
    //         return response()->json([
    //             'success' => true,
    //             'message' => 'All users leave found',
    //             'data' => $allUsersLeave,
    //         ], 200);
    //     }
    //     return response()->json([
    //         'success' => false,
    //         'message' => "Users leave doesn't exist",
    //         'data' => null
    //     ], 404);
    // }

    function showUsersLeave(Request $request)
    {

        $allUsersLeave = DB::table('users')
            ->where('role', 'User')
            ->join('user_leaves', 'users.id', '=', 'user_leaves.userId')
            ->select('users.id as userId','user_leaves.id as userLeaveId', 'users.name', 'users.email', 'user_leaves.leaveTypeId', 'user_leaves.sick', 'user_leaves.annual', 'user_leaves.casual')
            ->get();


            if ($allUsersLeave) {
                return response()->json([
                    'success' => true,
                    'message' => 'All users leave found',
                    'data' => $allUsersLeave,
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => "Users leave doesn't exist",
                'data' => null
            ], 404);
    }


    function showSingleUserLeave(Request $request, $id)
    {
        // $singleUserLeave = User_leave::where('userId', $id)->first();
        // $singleUserLeave = User::where('id', $id)->first();

        $usersLeave = DB::table('users')
            ->where('users.id', $id) // Specify 'users.id'
            ->join('user_leaves', 'users.id', '=', 'user_leaves.userId')
            ->select('user_leaves.id as userLeaveId', 'user_leaves.leaveTypeId', 'user_leaves.sick', 'user_leaves.casual', 'user_leaves.annual', 'users.name', 'users.email')
            ->first();

        if ($usersLeave) {
            return response()->json([
                'success' => true,
                'message' => 'Single user Leave found',
                'data' => $usersLeave,
            ], 200);
        }
        return response()->json([
            'success' => false,
            'message' => "User doesn't exist",
            'data' => null
        ], 404);
    }

    function userRemainingLeaves()
    {
        $auth = auth()->user();

        $remainingUserLeave = DB::table('user_leaves')
            ->where('userId', $auth->id)
            ->join('leaves_types', 'user_leaves.leaveTypeId', '=', 'leaves_types.id')
            ->select('leaves_types.sick as totalSickLeave', 'user_leaves.sick as availSickLeave', 'leaves_types.casual as totalCasualLeave', 'user_leaves.casual as availCasualLeave', 'leaves_types.annual as totalAnnualLeave', 'user_leaves.annual as availAnnualLeave')
            ->first();


        if ($remainingUserLeave) {
            return response()->json([
                'success' => true,
                'message' => 'User Leave found',
                'data' => $remainingUserLeave

            ], 200);
        }
        return response()->json([
            'success' => false,
            'message' => "User doesn't exist",
            'data' => null
        ], 404);
    }
}

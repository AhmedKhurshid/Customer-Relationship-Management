<?php

namespace App\Http\Controllers\Api;
use App\Enums\LeaveTypeEnum;
use App\Enums\LeaveStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Leave;
use App\Models\Leaves_type;
use App\Models\User;
use App\Models\User_leave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LeavesController extends Controller
{
    public function applyLeaves(Request $request)
    {
        $user = Auth::user();
        if ($user->id != null) {
            $user_id = $user->id;
        }

        // $status = $request->input('status');

        $userLeave = User_leave::where('userId', $user_id)->first();
        $userLeaveType = Leaves_type::where('id', $userLeave->leaveTypeId)->first();

        if (!$userLeave || !$userLeaveType) {
            return response()->json([
                'success' => false,
                'message' => "User does not exist",
                'data' => null,

            ], 404);
        }

        $validator = Validator::make(
            $request->all(),
            [
                $requestedDates =
                    'dateFrom' => ['required', 'array'],
                // 'leaveType' => ['required', 'string'],
                'leaveType' => [new Enum(LeaveTypeEnum::class)],
                'reason' => ['required', 'string'],
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages(),
                'data' => null
            ], 400);
        }


        $today = Carbon::today('Asia/Karachi');
        $dateFromArray = $request->dateFrom;
        $totalDays = count($dateFromArray);


        $datefrom = ['dateFrom' => $request->dateFrom];
        $dateto = ['dateTo' => $request->dateFrom];
        $existingLeaves = Leave::where('userId', $user_id)
            ->where(function ($query) use ($datefrom, $dateto) {
                $query->whereBetween('dateFrom', [$datefrom, $dateto])
                    ->orWhereBetween('dateTo', [$datefrom, $dateto]);
            })
            ->get();

        if ($existingLeaves->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Leaves already exists.',
                'data' => null
            ], 400);
        }

        if ($request->leaveType == 'Sick') {

            if ($userLeaveType->sick == $userLeave->sick) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can not apply more sick leaves',
                    'data' => null
                ], 400);
            }

            $sickLeaves = $userLeave->sick + $totalDays;

            if ($sickLeaves > $userLeaveType->sick) {
                $remainingSickLeaves = ($userLeaveType->sick) - ($userLeave->sick);

                return response()->json([
                    'success' => false,
                    'message' => "You have " . $remainingSickLeaves . " sick remaining leaves and you applying " . $totalDays . " leaves",
                    'data' => null
                ], 400);
            }

            for ($i = 0; $i < count($dateFromArray); $i++) {

                if ($dateFromArray[$i] <= ($today)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid date input. It should be later than today.',
                        'data' => null
                    ], 400);
                }

                $data = [
                    'reason' => $request->reason,
                    'dateFrom' => $request->dateFrom[$i],
                    'dateTo' => $request->dateFrom[$i],
                    'userId' => $user_id,
                    'leaveType' => $request->leaveType,
                ];
                $leaves = Leave::create($data);

                User_leave::where('userId', $user_id)->update(['sick' => $sickLeaves]);
            }

            if (!empty($leaves)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Your sick leave has been forwarded and wait for approval',
                    'data' => null

                ], 200,);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Internal server error.',
                    'data' => null
                ], 500);
            }
        }

        if ($request->leaveType == 'Annual') {

            if ($userLeaveType->annual == $userLeave->annual) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can not apply more annual leaves',
                    'data' => null
                ], 400);
            }

            $annualLeaves = $userLeave->annual + $totalDays;

            if ($annualLeaves > $userLeaveType->annual) {
                $remainingAnnualLeaves = ($userLeaveType->annual) - ($userLeave->annual);

                return response()->json([
                    'success' => false,
                    'message' => "You have " . $remainingAnnualLeaves . " annual remaining leaves and you applying " . $totalDays . " leaves",
                    'data' => null
                ], 400);
            }

            for ($i = 0; $i < count($dateFromArray); $i++) {

                if ($dateFromArray[$i] <= ($today)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid date input. It should be later than today.',
                        'data' => null
                    ], 400);
                }

                $data = [
                    'reason' => $request->reason,
                    'dateFrom' => $request->dateFrom[$i],
                    'dateTo' => $request->dateFrom[$i],
                    'userId' => $user_id,
                    'leaveType' => $request->leaveType,
                ];
                $leaves = Leave::create($data);

                User_leave::where('userId', $user_id)->update(['casual' => $annualLeaves]);
            }

            if (!empty($leaves)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Your annual leave has been forwarded and wait for approval',
                    'data' => null

                ], 200,);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Internal server error.',
                    'data' => null
                ], 500);
            }

            return response()->json([
                'success' => false,
                'message' => "User does not exist",
                'data' => null,

            ], 404);
        }

        if ($request->leaveType == 'Casual') {

            if ($userLeaveType->casual == $userLeave->casual) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can not apply more casual leaves',
                    'data' => null
                ], 400);
            }

            $casualLeaves = $userLeave->casual + $totalDays;

            if ($casualLeaves > $userLeaveType->casual) {
                $remainingCasualLeaves = ($userLeaveType->casual) - ($userLeave->casual);

                return response()->json([
                    'success' => false,
                    'message' => "You have " . $remainingCasualLeaves . " casual remaining leaves and you applying " . $totalDays . " leaves",
                    'data' => null
                ], 400);
            }

            for ($i = 0; $i < count($dateFromArray); $i++) {

                if ($dateFromArray[$i] <= ($today)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid date input. It should be later than today.',
                        'data' => null
                    ], 400);
                }

                $data = [
                    'reason' => $request->reason,
                    'dateFrom' => $request->dateFrom[$i],
                    'dateTo' => $request->dateFrom[$i],
                    'userId' => $user_id,
                    'leaveType' => $request->leaveType,
                ];
                $leaves = Leave::create($data);



                User_leave::where('userId', $user_id)->update(['casual' => $casualLeaves]);
            }

            if (!empty($leaves)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Your casual leave has been forwarded and wait for approval',
                    'data' => null

                ], 200,);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Internal server error.',
                    'data' => null
                ], 500);
            }

            return response()->json([
                'success' => false,
                'message' => "User does not exist",
                'data' => null,

            ], 404);
        }
        return response()->json([
            'success' => false,
            'message' => "Status does not exist",
            'data' => null,

        ], 404);
    }

    public function updateleaves(Request $request, $id)
    {
        $userAut = auth()->user();
        // $leaves=Leave::find($id);
        $leave = Leave::where('id', $id)->where('userId', $userAut->id)->first();

        $leaveStatus = Leave::where('id', $id)->where('userId', $userAut->id)->where('status', 'Pending')->first();

        $validator = Validator::make(
            $request->all(),
            [
                'dateFrom' => ['required', 'date'],
                // 'dateTo' => ['required', 'date'],
                'leaveType' => [new Enum(LeaveTypeEnum::class)],
                'reason' => ['required', 'string'],
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages(),
                'data' => null
            ], 400);
        }

        $today = Carbon::today('Asia/Karachi');
        if ($request->dateFrom <= ($today)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid date input. It should be later than today.',
                'data' => null
            ], 400);
        }

        if ($leave == null) {
            return response()->json([
                'success' => false,
                'message' => 'Leave does not exist',
                'data' => null
            ], 404);
        }
        if (!$leaveStatus) {
            return response()->json([
                'success' => false,
                'message' => "You can't change approved leave",
                'data' => null
            ], 400);
        }

        if ($leave !== null) {

            if ($request->leaveType == "Casual") {

                $remainingUserLeave = User_leave::where("userId", $userAut->id)->first();
                $remainingLeaveType = Leaves_type::where("id", $remainingUserLeave->leaveTypeId)->first();

                if ($remainingUserLeave->casual == $remainingLeaveType->casual) {
                    return response()->json([
                        'success' => false,
                        'message' => "You don't have sick leaves anymore.",
                        'data' => null
                    ], 500);
                }
                $totalCasualLeave = $remainingUserLeave->casual + 1;
                User_leave::where("userId", $userAut->id)->update(['casual' => $totalCasualLeave]);


                $leave->reason = $request->reason;
                $leave->dateFrom = $request->dateFrom;
                $leave->dateTo = $request->dateFrom;
                $leave->leaveType = $request->leaveType;
                $leave->save();
            }
            if ($request->leaveType == "Annual") {

                $remainingUserLeave = User_leave::where("userId", $userAut->id)->first();
                $remainingLeaveType = Leaves_type::where("id", $remainingUserLeave->leaveTypeId)->first();

                if ($remainingUserLeave->annual == $remainingLeaveType->annual) {
                    return response()->json([
                        'success' => false,
                        'message' => "You don't have annual leaves anymore.",
                        'data' => null
                    ], 500);
                }
                $totalAnnualLeave = $remainingUserLeave->annual + 1;
                User_leave::where("userId", $userAut->id)->update(['annual' => $totalAnnualLeave]);


                $leave->reason = $request->reason;
                $leave->dateFrom = $request->dateFrom;
                $leave->dateTo = $request->dateFrom;
                $leave->leaveType = $request->leaveType;
                $leave->save();
            }
            if ($request->leaveType == "Sick") {

                $remainingUserLeave = User_leave::where("userId", $userAut->id)->first();
                $remainingLeaveType = Leaves_type::where("id", $remainingUserLeave->leaveTypeId)->first();

                if ($remainingUserLeave->sick == $remainingLeaveType->sick) {
                    return response()->json([
                        'success' => false,
                        'message' => "You don't have sick leaves anymore.",
                        'data' => null
                    ], 500);
                }
                $totalSickLeave = $remainingUserLeave->sick + 1;
                User_leave::where("userId", $userAut->id)->update(['sick' => $totalSickLeave]);


                $leave->reason = $request->reason;
                $leave->dateFrom = $request->dateFrom;
                $leave->dateTo = $request->dateFrom;
                $leave->leaveType = $request->leaveType;
                $leave->save();
            }

            $data = [
                'reason' => $request->reason,
                'date' => $request->dateFrom,
                'leaveType' => $request->leaveType,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Leave updated successfully',
                'data' => $data,
            ], 200);
        }
        return response()->json([
            'success' => false,
            'message' => 'Internal server error',
            'data' => null
        ], 500);
    }

    public function showleaves()
    {
        $user = auth()->user();
        $leaves = Leave::where('userId', $user->id)->get();

        if (count($leaves) == 0) {
            return response()->json([
                'success' => false,
                'message' => 'This user does not have any leave',
                'data' => null,
            ], 404);
        } else {

            return response()->json([
                'success' => true,
                'message' => 'Leaves found',
                'data' => $leaves,
            ], 200);
        }
    }

    function userAppliedLeaves(Request $request)
    {
        $perPage = $request->input('perPage', 10);
        $usersLeave = DB::table('users')
            ->where('role', 'User')
            ->join('leaves', 'users.id', '=', 'leaves.userId')
            ->join('designations', 'users.designation', '=', 'designations.id')
            ->select('users.id as userId', 'leaves.id as leaveId', 'users.image', 'users.name', 'leaves.reason', 'leaves.dateFrom', 'leaves.dateTo', 'leaves.status', 'leaves.adminId', 'leaves.created_at', 'leaves.updated_at', 'designations.designation')
            ->paginate($perPage);

        if (!$usersLeave) {

            return response([
                'success' => false,
                'data' => null,
                'message' => "Leave doesn't Exist"
            ]);
        }

        return response([
            'success' => True,
            'message' => 'Leaves found',
            'data' => $usersLeave
        ], 200);
    }


    function leaveApprovedReject(Request $request, $id)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'status' => [new Enum(LeaveStatusEnum::class)],
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages(),
                'data' => null,
            ], 400);
        }
        $userAut = auth()->user();

        $leaveId = Leave::where('id', $id)->first();
        if ($leaveId == null) {
            return response()->json([
                'success' => false,
                'message' => "Leave doesn't exist",
                'data' => null,
            ]);
        }
        $userId = User::where('id', $leaveId->userId)->first();

        $fromDate = Carbon::parse($leaveId->dateFrom);
        $toDate = Carbon::parse($leaveId->dateTo);

        if ($leaveId && $request->status == 'Approve') {
            if ($leaveId->status == 'Approve') {
                return response()->json([
                    'success' => false,
                    'message' => 'Leave already approved',
                    'data' => null,
                ]);
            }


            Leave::where(['id' => $id])->update(['status' => 'Approve', 'adminId' => $userAut->id]);

            return response()->json([
                'success' => true,
                'message' => 'Leave approved successfully',
                'data' => null,
            ]);
        }
        if ($leaveId && $request->status == 'Reject') {

            $leave = Leave::where(['id' => $id])->first();

            if ($leave->leaveType == 'Annual') {
                if ($leave->status == 'Reject') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Leave already rejected',
                        'data' => null,
                    ]);
                }
                $userLeave = User_leave::where('userId', $leave->userId)->first();
                $rejectedLeave = $userLeave->annual - 1;
                User_leave::where('userId', $leave->userId)->update(['annual' => $rejectedLeave]);
                $leave = Leave::where(['id' => $id])->update(['status' => 'Reject', 'adminId' => $userAut->id]);
                return response()->json([
                    'success' => true,
                    'message' => 'Leave rejected successfully',
                    'data' => null,
                ]);
            }
            if ($leave->leaveType == 'Sick') {
                if ($leave->status == 'Reject') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Leave already rejected',
                        'data' => null,
                    ]);
                }
                $userLeave = User_leave::where('userId', $leave->userId)->first();

                $rejectedLeave = $userLeave->sick - 1;
                $userLeave = User_leave::where('userId', $leave->userId)->update(['sick' => $rejectedLeave]);
                $leave = Leave::where(['id' => $id])->update(['status' => 'Reject', 'adminId' =>
                $userAut->id]);
                // print_r($userLeave);
                // die;
                return response()->json([
                    'success' => true,
                    'message' => 'Leave rejected successfully',
                    'data' => $leave,
                ]);
            }
            if ($leave->leaveType == 'Casual') {
                if ($leave->status == 'Reject') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Leave already rejected',
                        'data' => null,
                    ]);
                }
                $userLeave = User_leave::where('userId', $leave->userId)->first();
                $rejectedLeave = $userLeave->casual - 1;
                User_leave::where('userId', $leave->userId)->update(['casual' => $rejectedLeave]);
                $leave = Leave::where(['id' => $id])->update(['status' => 'Reject', 'adminId' => $userAut->id]);
                return response()->json([
                    'success' => true,
                    'message' => 'Leave rejected successfully',
                    'data' => null,
                ]);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Data not found',
            'data' => null,
        ]);
    }
}

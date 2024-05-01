<?php

namespace App\Http\Controllers\Api;

use App\Enums\ShiftTypeEnum;
use App\Http\Controllers\Controller;
use App\Models\Emp_attend;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Time_schedule;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Enum;

class ScheduleController extends Controller
{
    function createSchedule(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make(
            $request->all(),
            [
                // 'type' => ['required', 'string'],
                'type' => [new Enum(ShiftTypeEnum::class)],
                'timeIn' => ['required'],
                'timeOut' => ['required'],
                'late' => ['required'],

            ]
        );
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages(),
                'data' => null
            ], 400);
        }

        DB::beginTransaction();
        $data = [
            'adminId' => $user->id,
            'type' => $request->type,
            'timeIn' => $request->timeIn,
            'timeOut' => $request->timeOut,
            'late' => $request->late,
        ];
        try {
            $users = Time_schedule::create($data);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollback();
            echo $e->getMessage();
            $users = null;
        }
        if ($users != null) {
            return response()->json(
                [
                    'success' => true,
                    'message' => 'Time schedule insert successfully',
                    'data' => null,
                ],
                200
            );
        } else {
            return response()->json(
                [
                    'message' => 'Internal server error',
                    'success' => false,
                    'data' => null,

                ],
                500
            );
        }
    }

    function showSchedule()
    {
        $notifications = Time_schedule::paginate(10);

        if ($notifications == null) { {
                return response()->json([
                    'success' => false,
                    'message' => "Time schedule doesn't exist",
                    'data' => null,
                ], 404);
            }
        } else {
            return response()->json([
                'success' => true,
                'message' => 'Time schedule found',
                'data' => $notifications,
            ], 200);
        }
    }

    function showSingleSchedule($id)
    {
        $timeSchedule = Time_schedule::where('id', $id)->first();

        if ($timeSchedule == null) { {
                return response()->json([
                    'success' => false,
                    'message' => "Time schedule doesn't exist",
                    'data' => null,
                ], 404);
            }
        } else {
            return response()->json([
                'success' => true,
                'message' => 'Time schedule found',
                'data' => $timeSchedule,
            ], 200);
        }
    }

    public function updateSchedule(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            // 'type' => ['required_without_all:timeIn,timeOut,late', 'string'],
            'type' => [new Enum(ShiftTypeEnum::class)],
            'timeIn' => ['required_without_all:type,timeOut,late'],
            'timeOut' => ['required_without_all:timeIn,type,late'],
            'late' => ['required_without_all:timeIn,timeOut,type'],
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
        $schedule = Time_schedule::find($id);

        if ($schedule == null) {
            return response()->json([
                'success' => false,
                'message' => "Time schedule doesn't exist",
                'data' => null
            ], 200,);
        }

        $schedule->type = $request->type;
        $schedule->timeIn = $request->timeIn;
        $schedule->timeOut = $request->timeOut;
        $schedule->late = $request->late;
        $schedule->save();
        return response()->json([
            'success' => true,
            'message' => 'Time schedule updated successfully',
            'data' => $schedule,
        ], 200,);
    }

    public function deleteSchedule(string $id)
    {
        $schedule = Time_schedule::find($id);
        if (is_null($schedule)) {
            return response()->json([
                'success' => false,
                'message' => "Time schedule doesn't exist",
                'data' => null
            ], 200,);
        } else {

            $schedule->delete();
            return response()->json([
                'success' => true,
                'message' => 'Time schedule deleted successfully',
                'data' => $schedule,
            ], 200,);
        }
    }

    // function shiftAssign(Request $request, $id)
    // {
    //     $validator = Validator::make(
    //         $request->all(),
    //         [
    //             'schedule' => ['required', 'integer'],
    //         ]
    //     );

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'success' => false,
    //             'data' => null,
    //             'message' => $validator->messages(),
    //         ], 400);
    //     }
    //     $userId = User::where('id', $id)->first();
    //     if ($userId == null) {
    //         return response([
    //             'success' => false,
    //             'message' => "User doesn't exist",
    //             'data' => null,
    //         ]);
    //     }
    //     User::where('id', $userId->id)->update(['schedule' => $request->schedule]);

    //     return response([
    //         'success' => true,
    //         'message' => 'schedule assigned successfully',
    //         'data' => null,
    //     ]);
    // }
}

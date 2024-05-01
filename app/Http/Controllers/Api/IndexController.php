<?php

namespace App\Http\Controllers\Api;

use App\Enums\LeaveTypeEnum;
use App\Enums\UserRoleEnum;
use App\Http\Controllers\Controller;
use App\Models\Designation;
use App\Models\Emp_attend;
use App\Models\Leave;
use App\Models\Leaves_type;
use App\Models\Oauth_access_token;
use App\Models\Time_schedule;
use App\Models\User;
use App\Models\User_leave;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Mail;
use File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;

use function PHPUnit\Framework\isNull;

// use Illuminate\Support\Facades\Mail;

class IndexController extends Controller
{
    public function usersignup(Request $request)
    {
        //
        $validator = Validator::make(
            $request->all(),
            [
                'name' => ['required', 'string'],
                'schedule' => ['required', 'integer'],
                'email' => ['required', 'email', 'unique:users,email'],
                'password' => ['required', 'min:8',],
                // 'role' => ['required', 'string'],
                'role' => [new Enum(UserRoleEnum::class)],
                'leaveTypeId' => ['required', 'integer'],
                'address' => ['required', 'string'],
                'contactNo' => ['required', 'min:10', 'string'],
                'employeeId' => ['required', 'string', 'min:4'],
                'joinDate' => ['required','date'],
                'famContactNo' => ['required', 'min:10', 'string'],
                'cnic' => ['required', 'min:10', 'string'],
                'designation' => ['required', 'integer'],
                'image' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],

            ]
        );
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages(),
                'data' => null
            ], 400);
        }

        $scheduleExist = Time_schedule::where('id', $request->schedule)->first();

        if ($scheduleExist == null) {
            return response()->json([
                'success' => false,
                'message' => 'Schedule does not exist',
                'data' => null,

            ], 404);
        }

        $designationExist = Designation::where('id', $request->designation)->first();

        if ($designationExist == null) {
            return response()->json([
                'success' => false,
                'message' => 'Designation does not exist',
                'data' => null,

            ], 404);
        }
        $employeeIdExist = User::where('employeeId', $request->employeeId)->first();

        if ($employeeIdExist != null) {
            return response()->json([
                'success' => false,
                'message' => 'Employee Id already exist',
                'data' => null,

            ], 400);
        }
        $leaveTypeExist = Leaves_type::where('id', $request->leaveTypeId)->first();

        if ($leaveTypeExist == null) {
            return response()->json([
                'success' => false,
                'message' => 'Leave Type does not exist',
                'data' => null,

            ], 404);
        }

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('image', 'public');
            $imageURL = ('storage/' . $imagePath);


            DB::beginTransaction();
            $data = [
                'name' => $request->name,
                'schedule' => $request->schedule,
                'email' => $request->email,
                'role' => $request->role,
                'password' => Hash::make($request->password),
                'status' => 'Inactive',
                'isEmployee' => 'Employee',
                'address' => $request->address,
                'contactNo' => $request->contactNo,
                'famContactNo' => $request->famContactNo,
                'cnic' => $request->cnic,
                'employeeId' => $request->employeeId,
                'joinDate' =>$request->joinDate,
                'designation' => $request->designation,
                'image' => $imageURL ?? null,
            ];

            try {
                $users = User::create($data);
                $data1 = [
                    'leaveTypeId' => $request->leaveTypeId,
                    'userId' => $users->id
                ];
                $userLeave = User_leave::create($data1);
                DB::commit();
            } catch (\Throwable $e) {
                DB::rollback();
                echo $e->getMessage();
                $users = null;
                $userLeave = null;
            }
            if ($users != null) {
                return response()->json(
                    [
                        'success' => true,
                        'message' => 'User have registered successfully',
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
    }

    public function adminsignup(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:8'],
            // 'password_confirmation' => ['required'],
            // 'totalLeaves' => ['required'],
            // 'employeeId' => ['required', 'string'],
            // 'joinDate' => ['required','date'],
            'address' => ['required', 'string'],
            'contactNo' => ['required', 'min:7', 'string'],
            'famContactNo' => ['required', 'min:7'],
            'cnic' => ['required', 'min:7', 'string'],
            'designation' => ['required', 'integer'],
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],

        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false, 'message' => $validator
                    ->messages(),
                'data' => null
            ], 400);
        }

        $designationExist = Designation::where('id', $request->designation)->first();

        if ($designationExist == null) {
            return response()->json([
                'success' => false,
                'message' => 'Designation does not exist',
                'data' => null,

            ], 404);
        }

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('image', 'public');
            $imageURL = ('storage/' . $imagePath);
            DB::beginTransaction();
            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'role' => 'Admin',
                'password' => Hash::make($request->password),
                'status' => 'Inactive',
                'isEmployee' => 'Employee',
                'address' => $request->address,
                'contactNo' => $request->contactNo,
                'famContactNo' => $request->famContactNo,
                'cnic' => $request->cnic,
                'designation' => $request->designation,
                'employeeId' => $request->employeeId,
                'joinDate' =>$request->joinDate,
                'image' => $imageURL ?? null,


            ];
            try {
                $users = User::create($data);
                DB::commit();
            } catch (\Exception $e) {
                echo $e->getMessage();
                die;
                DB::rollback();
                $users = null;
            }
        }
        if ($users != null) {
            return response()->json(
                [
                    'success' => true,
                    'message' => 'User registered successfully',
                    'data' => null,

                ],
                200
            );
        } else {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Internal server error',
                    'data' => null,
                ],
                500
            );
        }
    }

    public function loginuser(Request $request)
    {


        $validator = Validator::make(
            $request->all(),
            [

                'email' => ['required', 'email'],
                'password' => ['required', 'string'],
                'deviceToken' => ['string'],

            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false, 'message' => $validator
                    ->messages(),
                'data' => null
            ], 400);
        }

        $unEmployedUser = User::where('email', $request->email)->where('isEmployee', 'Suspend')->first();
        if ($unEmployedUser) {
            return response()->json([
                'success' => false,
                'message' => "You are suspend",
                'data' => null,

            ], 404);
        }

        $user = User::where('email', $request->email)->first();

        if ($user == null) {
            return response()->json([
                'success' => false,
                'message' => "User does not exist",
                'data' => null,

            ], 404);
        }

        if ($users = User::where('password', Hash::check($request->password, $user->password))->first()) {
            return response()->json([
                'success' => false,
                'message' => 'Wrong password',
                'data' => null,
            ], 404);
        }
        if ($user->role != 'User') {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'You are not user',
                    'data' => null,

                ],
                400
            );
        }

        if (!$users) {

            $deviceToken = User::where('id', $user->id)->update(['deviceToken'=> $request->deviceToken]);
            $tokenExist = Oauth_access_token::where('user_id', $user->id)->get();
            if ($tokenExist) {
                foreach ($tokenExist as $tokenExpire) {
                    $tokenExpire->delete();
                }
            }

            try {
                $token = $user->createToken('Authtoken')->accessToken;
                return response()->json([
                    'success' => true,
                    'message' => 'You have logged in successfully',
                    // 'token' => $token,
                    'data' => ['token' => $token, 'user' => $user],

                ]);
            } catch (\Exception $e) {
                $users = null;
                return response()->json([
                    'success' => false,
                    'message' => 'Internal server error',
                    'data' => null,
                ], 500);
            }
        }
    }

    public function loginadmin(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'email' => ['required', 'email'],
                'password' => ['required', 'string'],
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false, 'message' => $validator
                    ->messages(),
                'data' => null
            ], 400);
        }

        $unEmployedUser = User::where('email', $request->email)->where('isEmployee', 'Suspend')->first();
        if ($unEmployedUser) {
            return response()->json([
                'success' => false,
                'message' => "You are suspend",
                'data' => null,

            ], 404);
        }

        $user = User::where('email', $request->email)->first();

        if ($user == null) {
            return response()->json([
                'success' => false,
                'message' => 'User does not exist',
                'data' => null,
            ], 404);
        }

        if ($users = User::where('password', Hash::check($request->password, $user->password))->first()) {
            return response()->json([
                'success' => false,
                'message' => 'Wrong password',
                'data' => null,
            ], 404);
        }

        if ($user->role != 'Admin') {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'You are not admin',
                    'data' => null,

                ],
                400
            );
        }

        if (!$users) {

            $tokenExist = Oauth_access_token::where('user_id', $user->id)->get();
            if ($tokenExist) {
                foreach ($tokenExist as $tokenExpire) {
                    $tokenExpire->delete();
                }
            }

            try {
                $token = $user->createToken('Authtoken')->accessToken;
                return response()->json([
                    'success' => true,
                    'message' => 'You have logged in successfully',
                    // 'token' => $token,
                    'data' => ['token' => $token, 'user' => $user],

                ]);
            } catch (\Exception $e) {
                $users = null;
                return response()->json([
                    'success' => false,
                    'message' => 'Internal server error',
                    // 'message' => $e->getMessage(),
                    'data' => null,
                ], 500);
            }
        }
    }



    // public function forgetPassword(Request $request)
    // {


    //     // try {
    //     $users = User::where('email', $request->email)->first();
    //     if ($users != null) {
    //         $otpcode =  random_int(10, 10000);


    //         DB::beginTransaction();
    //         try {
    //             $data['otpcode'] = $otpcode;
    //             $data['email'] = $request->email;
    //             $data['title'] = "Reset Password";
    //             $data['body'] = "Enter this otp code to reset your password";
    //             Mail::send('email', ['data' => $data], function ($message) use ($data) {
    //                 $message->from('codeaugment@gmail.com');
    //                 $message->to($data['email']);
    //                 $message->subject($data['title']);
    //             });
    //             // if (Mail::failures()) {
    //             return response()->json([
    //                 'success' => true,
    //                 'message' => 'Mail send successfully',
    //                 'data' => null,

    //             ], 200);

    //             // }
    //         } catch (\Exception $e) {
    //             echo $e->getMessage();
    //             DB::rollback();
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Internal server error',
    //                 'data' => null,
    //             ], 200,);
    //         }
    //     } else {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'User not found',
    //             'data' => null,
    //         ], 400);
    //     }
    // }

    // public function newPassword(Request $request)
    // {

    //     $validator = Validator::make(
    //         $request->all(),

    //         [
    //             'otpCode' => ['required', 'digits:4'],
    //             'email' => ['required', 'email'],
    //             'password' => ['required', 'min:8'],
    //         ]
    //     );
    //     if ($validator->fails()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => $validator->messages(),
    //             'data' => null
    //         ], 400);
    //     }
    //     $matchThese = ['email' => $request->email, 'otpCode' => $request->otpCode];
    //     $users = User::where($matchThese)->first();


    //     if ($users != null) {
    //         try {
    //             DB::beginTransaction();
    //             $users->password = Hash::make($request->password);
    //             $users->otpCode = null;
    //             $users->save();
    //             DB::commit();
    //             return response()->json(
    //                 [
    //                     'success' => true,
    //                     'message' => 'Password updated successfully',
    //                     'data' => null
    //                 ],
    //                 200
    //             );
    //         } catch (\Exception $e) {
    //             $users = null;
    //             DB::rollback();
    //         }
    //     } else {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Invalid credentials',
    //             'data' => null
    //         ], 200);
    //     }
    // }




    // public function userDetailsShowToAdmin($id)
    // {
    //     // $auth = auth()->user();
    //     $userData = User::where('id', $id)->first();

    //     if ($userData == null) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'The user doest exist',
    //             'data' => null
    //         ], 404);
    //     } else {
    //         $designation =  Designation::select('designation')->where('id', $userData->designation)->first();
    //         $designations = $designation->designation;

    //         $users = Emp_attend::where('userId', $id)->get();

    //         $now = Carbon::now('Asia/Karachi');


    //         $attendance = Emp_attend::select('checkIn', 'checkOut')->whereBetween('checkIn', [now()->startOfWeek(), now()->endOfWeek()])->where('userId', $id)->get();

    //         if ($users) {

    //             $todayCheckInOnly = Emp_attend::whereNotNull('checkIn')->whereNull('checkOut')->where('userId', $id)->first();
    //             if ($todayCheckInOnly) {

    //                 $todayTime = $todayCheckInOnly->checkIn;
    //             }

    //             $todayCheckInOut = Emp_attend::select('checkIn', 'checkOut')->whereBetween('checkIn', [now()->startOfDay(), now()->endOfDay()])->where('userId', $id)->latest()->first();

    //             if (!$todayCheckInOut) {
    //                 $todayTime = "User doesn't check in today";
    //             }

    //             if ($todayCheckInOut) {
    //                 $todaytotalTime = 0;

    //                 $todaycheckInTime = Carbon::parse($todayCheckInOut->checkIn);
    //                 $todaycheckOutTime = Carbon::parse($todayCheckInOut->checkOut);

    //                 $todaytotalTime += $todaycheckOutTime->diffInMinutes($todaycheckInTime);

    //                 $todayhours = intdiv($todaytotalTime, 60);
    //                 $todayremainingMinutes = $todaytotalTime % 60;
    //                 $todayremainingSeconds =  $todayremainingMinutes % 60;

    //                 $todayTime = sprintf('%02d:%02d:%02d', $todayhours, $todayremainingMinutes,  $todayremainingSeconds);
    //             }
    //         }

    //         $totalTime = 0;
    //         foreach ($attendance as $att) {
    //             $checkInTime = Carbon::parse($att->checkIn);
    //             $checkOutTime = Carbon::parse($att->checkOut);

    //             $totalTime += $checkOutTime->diffInMinutes($checkInTime);
    //         }
    //         $hours = intdiv($totalTime, 60);
    //         $remainingMinutes = $totalTime % 60;
    //         $remainingSeconds =  $remainingMinutes % 60;

    //         $timeFormat = sprintf('%02d:%02d:%02d', $hours, $remainingMinutes,  $remainingSeconds);

    //         $userData["totalDurationOfTheWeek"] = $timeFormat;
    //         $userData["todayTime"] = $todayTime;
    //         $userData['designation'] = $designations;

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Data found',
    //             'data' => $userData

    //         ], 200);
    //     }
    // }








    // function completeDetails($id)
    // {
    //     $current_dates = Carbon::now('Asia/Karachi');
    //     $userId = User::where('id', $id)->first();
    //     if (!$userId) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'User not found',
    //             'data' => null
    //         ], 404);
    //     }

    //     $week_data = [];

    //     for ($day = 0; $day <= 7; $day++) {
    //         $attendance = Emp_attend::where('userId', $id)
    //             ->whereDate('checkIn', $current_dates)
    //             ->get();

    //         if (count($attendance) == 0) {
    //             $userLeaves = Leave::where('userId', $id)->whereDate('dateFrom', '<=', $current_dates)
    //                 ->whereDate('dateTo', '>=', $current_dates)->first();
    //             if ($userLeaves) {
    //                 $status = 'Leave';
    //             } else {
    //                 $status = 'Absent';
    //             }
    //         } else {
    //             $total = [];

    //             for ($i = 0; $i < count($attendance); $i++) {
    //                 # code...

    //                 $checkInTime = Carbon::parse($attendance[$i]->checkIn);
    //                 $checkOutTime = Carbon::parse($attendance[$i]->checkOut);

    //                 $totalTime = $checkInTime->diffInMinutes($checkOutTime);
    //                 $total[$i] = $totalTime;
    //                 $sum = array_sum($total);

    //                 $hours = intdiv($sum, 60);
    //                 $remainingMinutes = $sum % 60;
    //                 $remainingSeconds =  $sum % 60;
    //                 $status = sprintf('%02d:%02d:%02d', $hours, $remainingMinutes,  $remainingSeconds);
    //             }
    //         }

    //         $week_data[] = [
    //             'date' => $current_dates->toDateString(),
    //             'status' => $status,
    //             // 'timeIn'=>$status,
    //             // 'timeOut'=>$status,

    //         ];

    //         $current_dates->subDay();
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Monthly Attendance',
    //         'data' => $week_data,
    //     ], 200);
    // }

    // function getAdmin()
    // {
    //     $admin = User::where('role', 'Admin')->get();

    //     if ($admin == null) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Admin not found',
    //             'data' => null,
    //         ], 404);
    //     }
    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Admin found',
    //         'data' => $admin,
    //     ], 200);
    // }


}

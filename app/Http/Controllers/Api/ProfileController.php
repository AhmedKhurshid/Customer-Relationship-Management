<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Designation;
use App\Models\Emp_attend;
use App\Models\Leaves_type;
use App\Models\Time_schedule;
use App\Models\User;
use App\Models\User_leave;
use Carbon\Carbon;
use App\Enums\UserRoleEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;
use App\Enums\EmployeeStatusEnum;
use App\Models\Emp_break;
use File;


class ProfileController extends Controller
{
    public function updateUserDetails(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [


            'status' => ['required_without_all:name,schedule,email,password,role,address,contactNo,famContactNo,cnic,designation,image', 'string',],
            'name' => ['required_without_all:status,schedule,email,password,role,address,contactNo,famContactNo,cnic,designation,image', 'string'],
            'schedule' => ['required_without_all:status,name,email,password,role,address,contactNo,famContactNo,cnic,designation,image', 'integer'],
            'email' => ['required_without_all:status,name,schedule,password,role,address,contactNo,famContactNo,cnic,designation,image', 'email', 'email'],
            'password' => ['required_without_all:status,name,schedule,email,role,address,contactNo,famContactNo,cnic,designation,image', 'min:8',],
            // 'role' => ['required_without_all:status,name,schedule,email,password,address,contactNo,famContactNo,cnic,designation,image', 'string'],
            'role' => [new Enum(UserRoleEnum::class)],
            'employeeId' => ['required_without_all:status,name,schedule,email,password,role,address,contactNo,famContactNo,cnic,designation,joinDate,image','string'],
            'joinDate' =>['required_without_all:status,name,schedule,email,password,role,address,contactNo,famContactNo,cnic,designation,employeeId,image', 'date'],
            'address' => ['required_without_all:status,name,schedule,email,password,role,contactNo,famContactNo,cnic,designation,image', 'string'],
            'contactNo' => ['required_without_all:status,name,schedule,email,password,role,address,famContactNo,cnic,designation,image', 'min:10', 'string'],
            'famContactNo' => ['required_without_all:status,name,schedule,email,password,role,address,contactNo,cnic,designation,image', 'min:10', 'string'],
            'cnic' => ['required_without_all:status,name,schedule,email,password,role,address,contactNo,famContactNo,designation,image', 'min:10', 'string'],
            'designation' => ['required_without_all:status,name,schedule,email,password,role,address,contactNo,famContactNo,cnic,image', 'integer'],
            // 'image' => ['required_without_all:status,name,schedule,email,password,role,address,contactNo,famContactNo,cnic,designation', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],



        ]);
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
        $users = User::find($id);
        if ($users == null) {
            return response()->json([
                'success' => false,
                'message' => 'User does not exist',
                'data' => null,

            ], 404);
        }

        if ($request->hasFile('image')) {

            $filename = time() . $request->file('image')->getClientOriginalName();
            $imageURL = $request->file('image')->storeAs('image', $filename, 'public');
            $newImageUrl = ('storage/' . $imageURL);

            $imagePath = $users->image;

            if ($imagePath) {
                File::delete($imagePath);
            }
        }

        if ($users == null) {
            return response()->json([
                'success' => false,
                'message' => 'User does not exist',
                'data' => null,

            ], 404);
        } else {

            DB::beginTransaction();
            try {
                $users->password = Hash::make($request->password) ?? $users->password;
                $users->name = $request->name ?? $users->name;
                $users->schedule = $request->schedule ?? $users->schedule;
                $users->email = $request->email ?? $users->email;
                $users->role = $request->role ?? $users->role;
                $users->status = $request->status ?? $users->status;
                $users->address = $request->address ?? $users->address;
                $users->designation = $request->designation ?? $users->designation;
                $users->cnic = $request->cnic ?? $users->cnic;
                $users->joinDate = $request->joinDate ?? $users->joinDate;
                $users->employeeId = $request->employeeId ?? $users->employeeId;
                $users->famContactNo = $request->famContactNo ?? $users->famContactNo;
                $users->contactNo = $request->contactNo ?? $users->contactNo;
                $users->image = $newImageUrl ?? $users->image;

                $users->save();
                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
                $users = null;
            }

            return response()->json(
                [
                    'success' => true,
                    'message' => 'Details updated successfully',
                    'data' => null
                ],
                200
            );
        }

    }

    public function userDetailsShowToAdmin($id)
    {
        $userData = User::where('id', $id)->where('role', 'User')->first();

        if (!$userData) {
            return response()->json([
                'success' => false,
                'message' => "The user doesn't exist",
                'data' => null
            ], 404);
        } else {
            $designation =  Designation::select('id','designation')->where('id', $userData->designation)->first();


            $userLeave =  User_leave::where('userId', $userData->id)->first();
            if ($userLeave == null) {
                return response()->json([
                    'success' => false,
                    'message' => "User leave not found",
                    'data' => null
                ], 404);
            }

            $leaveType =  Leaves_type::where('id', $userLeave->leaveTypeId)->first();
            if ($leaveType == null) {
                return response()->json([
                    'success' => false,
                    'message' => "Leave type not found",
                    'data' => null
                ], 404);
            }

            $sick = $leaveType->sick -  $userLeave->sick;
            $casual = $leaveType->casual -  $userLeave->casual;
            $annual = $leaveType->annual -  $userLeave->annual;

            $totalleave = $sick + $casual + $annual;

            $attendance = Emp_attend::select('checkIn', 'checkOut')->whereBetween('checkIn', [now()->startOfWeek(), now()->endOfWeek()])->where('userId', $id)->get();
            $userLeave = User_leave::where('userId',$id)->first();
            $leaveType = Leaves_type::select('id','name')->where('id', $userLeave->leaveTypeId)->first();

            $totalTime = 0;
            foreach ($attendance as $att) {
                $checkInTime = Carbon::parse($att->checkIn);
                $checkOutTime = Carbon::parse($att->checkOut);
                // $breakInTime = Carbon::parse($att->breakIn);
                // $breakOutTime = Carbon::parse($att->breakOut);

                $totalTime += $checkOutTime->diffInMinutes($checkInTime);
                // $totalBreakTime = $breakOutTime->diffInMinutes($breakInTime);

                // $totalTime += $totalCheckTime - $totalBreakTime;
            }

            $hours = intdiv($totalTime, 60);
            $remainingMinutes = $totalTime % 60;
            $remainingSeconds =  $totalTime % 60;

            $timeFormat = sprintf('%02d:%02d:%02d', $hours, $remainingMinutes,  $remainingSeconds);

            $userData["totalDurationOfTheWeek"] = $timeFormat;
            $userData['designation'] = $designation->id;
            $userData['designationName'] = $designation->designation;
            $userData['remainingLeave'] = $totalleave;
            $userData['leaveTypeId'] = $leaveType->id;
            $userData['leaveTypeIdName'] = $leaveType->name;


            return response()->json([
                'success' => true,
                'message' => 'Data found',
                'data' => $userData,

            ], 200);
        }
    }

    // public function userDetailsShowToAdmin($id)
    // {
    //     $userData = User::where('id', $id)->where('role', 'User')->first();
    //     $totalBreak = [];
    //     $totalAttendence = [];
    //     $total = [];

    //     if (!$userData) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => "The user doesn't exist",
    //             'data' => null
    //         ], 404);
    //     } else {
    //         $designation =  Designation::select('designation')->where('id', $userData->designation)->first();
    //         $designations = $designation->designation;

    //         $userLeave =  User_leave::where('userId', $userData->id)->first();
    //         if ($userLeave == null) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => "User leave not found",
    //                 'data' => null
    //             ], 404);
    //         }

    //         $leaveType =  Leaves_type::where('id', $userLeave->leaveTypeId)->first();
    //         if ($leaveType == null) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => "Leave type not found",
    //                 'data' => null
    //             ], 404);
    //         }

    //         $sick = $leaveType->sick -  $userLeave->sick;
    //         $casual = $leaveType->casual -  $userLeave->casual;
    //         $annual = $leaveType->annual -  $userLeave->annual;

    //         $totalleave = $sick + $casual + $annual;

    //         $attendance = Emp_attend::select('checkIn', 'checkOut')->whereBetween('checkIn', [now()->startOfWeek(), now()->endOfWeek()])->where('userId', $id)->get();
    //         array_push($totalAttendence, $attendance);
    //         $breaks = Emp_break::select('breakIn', 'breakOut')->whereBetween('breakIn', [now()->startOfWeek(), now()->endOfWeek()])->where('userId', $id)->get();
    //         array_push($totalBreak, $breaks);

    //         $totalCheckInTime = 0;
    //         foreach ($totalAttendence[0] as $att) {
    //             $checkInTime = Carbon::parse($att['checkIn']);
    //             $checkOutTime = Carbon::parse($att['checkOut']);

    //             $totalCheckInTime += $checkOutTime->diffInSeconds($checkInTime);
    //         }

    //         $totalBreakInTime = 0;

    //         foreach ($totalBreak[0] as $break) {
    //             $breakInTime = Carbon::parse($break['breakIn']);
    //             $breakOutTime = Carbon::parse($break['breakOut']);


    //             $totalBreakInTime += $breakInTime->diffInSeconds($breakOutTime);
    //         }

    //         // $checkInTotalTime = $checkInTime->diffInMinutes($checkOutTime);

    //         $total[] = $totalCheckInTime - $totalBreakInTime;

    //         $sum = array_sum($total);

    //         $hours = floor($sum / 3600);
    //         $remainingMinutes = floor(($sum / 60) % 60);
    //         $remainingSeconds = $sum % 60;

    //         $timeFormat = sprintf('%02d:%02d:%02d', $hours, $remainingMinutes,  $remainingSeconds);

    //         $userData["totalDurationOfTheWeek"] = $timeFormat;
    //         $userData['designation'] = $designations;
    //         $userData['remainingLeave'] = $totalleave;

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Data found',
    //             'data' => $userData

    //         ], 200);
    //     }
    // }

    public function userDetailsShowToUser()
    {
        $auth = auth()->user();
        $userData = User::where('id', $auth->id)->where('role', 'User')->first();

        if (!$userData) {
            return response()->json([
                'success' => false,
                'message' => "The user doesn't exist",
                'data' => null
            ], 404);
        } else {
            $designation =  Designation::select('designation')->where('id', $userData->designation)->first();
            $designations = $designation->designation;

            $userLeave =  User_leave::where('userId', $userData->id)->first();
            if ($userLeave == null) {
                return response()->json([
                    'success' => false,
                    'message' => "User leave not found",
                    'data' => null
                ], 404);
            }

            $leaveType =  Leaves_type::where('id', $userLeave->leaveTypeId)->first();
            if ($leaveType == null) {
                return response()->json([
                    'success' => false,
                    'message' => "Leave type not found",
                    'data' => null
                ], 404);
            }

            $sick = $leaveType->sick -  $userLeave->sick;
            $casual = $leaveType->casual -  $userLeave->casual;
            $annual = $leaveType->annual -  $userLeave->annual;

            $totalleave = $sick + $casual + $annual;

            $attendance = Emp_attend::select('checkIn', 'checkOut')->whereBetween('checkIn', [now()->startOfWeek(), now()->endOfWeek()])->where('userId', $auth->id)->get();

            $totalTime = 0;
            foreach ($attendance as $att) {
                $checkInTime = Carbon::parse($att->checkIn);
                $checkOutTime = Carbon::parse($att->checkOut);
                // $breakInTime = Carbon::parse($att->breakIn);
                // $breakOutTime = Carbon::parse($att->breakOut);

                $totalTime += $checkOutTime->diffInMinutes($checkInTime);
                // $totalBreakTime = $breakOutTime->diffInMinutes($breakInTime);

                // $totalTime += $totalCheckTime - $totalBreakTime;
            }

            $hours = intdiv($totalTime, 60);
            $remainingMinutes = $totalTime % 60;
            $remainingSeconds =  $totalTime % 60;

            $timeFormat = sprintf('%02d:%02d:%02d', $hours, $remainingMinutes,  $remainingSeconds);

            $userData["totalDurationOfTheWeek"] = $timeFormat;
            $userData['designation'] = $designations;
            $userData['remainingLeave'] = $totalleave;

            return response()->json([
                'success' => true,
                'message' => 'Data found',
                'data' => $userData

            ], 200);
        }
    }

    function checkInTimeShowToUser()
    {
        $auth = auth()->user();
        $userData = User::where('id', $auth->id)->first();
        $now = Carbon::now('Asia/Karachi');
        // $todaytotalTime = [];
        // $totalBreak = [];

        // $totalBreaksTime = 0;


        if ($userData == null) {
            return response()->json([
                'success' => false,
                'message' => "The user doesn't exist",
                'data' => null
            ], 404);
        } else {
            $userCheckIn = Emp_attend::where('userId', $auth->id)->orderBy('id', 'DESC')->first();
            // $userBreak = Emp_break::where('userId', $auth->id)->where('empAttendanceId', $userCheckIn->id)->latest()->first();
            // $userBreaks = Emp_break::where('userId', $auth->id)->where('empAttendanceId', $userCheckIn->id)->get();

            if ($userCheckIn == null) {
                return response()->json([
                    'success' => false,
                    'message' => 'User attendence not exist',
                    'data' => null
                ], 404);
            }


            if ($userCheckIn->checkIn == null && $userCheckIn->checkOut == null) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not checkin today',
                    'data' => null
                ], 200);
            }
            if ($userCheckIn->checkIn != null && $userCheckIn->checkOut == null) {

                $checkInTime = Carbon::parse($userCheckIn->checkIn);
                $todaytotalTime = $now->diffInSeconds($checkInTime);

                $todayhours = floor($todaytotalTime / 3600);
                $todayremainingMinutes = floor(($todaytotalTime / 60) % 60);
                $todayremainingSeconds = $todaytotalTime % 60;

                $todayCheckTime = sprintf('%02d:%02d:%02d', $todayhours, $todayremainingMinutes,  $todayremainingSeconds);
                return response()->json([
                    'success' => true,
                    'message' => 'data found',
                    'data' => [
                        'todayCheckTime' => $todayCheckTime,
                        'userStatus' => $userData->status,
                    ]
                ], 200);
            }
            if ($userCheckIn->checkIn != null &&  $userCheckIn->checkOut != null) {
                $userLastCheckIn = Emp_attend::where('userId', $auth->id)->orderBy('id', 'DESC')->first();

                $checkInTime = Carbon::parse($userLastCheckIn->checkIn);
                $checkOutTime = Carbon::parse($userLastCheckIn->checkOut);
                $todaytotalTime = $checkOutTime->diffInSeconds($checkInTime);

                $todayhours = floor($todaytotalTime / 3600);
                $todayremainingMinutes = floor(($todaytotalTime / 60) % 60);
                $todayremainingSeconds = $todaytotalTime % 60;

                $todayCheckTime = sprintf('%02d:%02d:%02d', $todayhours, $todayremainingMinutes,  $todayremainingSeconds);
                return response()->json([
                    'success' => true,
                    'message' => 'data found',
                    'data' => [
                        'todayCheckTime' => $todayCheckTime,
                        'userStatus' => $userData->status,
                    ]
                ], 200);
            }
            // if ($userCheckIn->checkIn != null && $userBreak->breakIn != null && $userBreak->breakOut == null && $userCheckIn->checkOut == null) {

            //     // $userBreaks = Emp_break::where('userId', $auth->id)->where('empAttendanceId', $userCheckIn->id)->get();

            //     // print_r($userBreaks);
            //     // die;

            //     array_push($totalBreak, $userBreaks);

            //     $totalBreaksTime = 0;
            //     $totalBreaksInTime = 0;

            //     $checkInTime = Carbon::parse($userCheckIn->checkIn);
            //     $totalCheckInTime = $now->diffInSeconds($checkInTime);

            //     foreach ($totalBreak[0] as $break) {
            //         $breakInTime = Carbon::parse($break['breakIn']);
            //         $breakOutTime = Carbon::parse($break['breakOut']);

            //         // if ($break === $userBreaks->last()) {
            //         if ($break->count === $userBreaks->last()) {
            //             $lastBreakIn = Carbon::parse($break['breakIn']);
            //             $totalBreaksInTime += $now->diffInSeconds($lastBreakIn);
            //         }

            //         $totalBreaks = $breakInTime->diffInSeconds($breakOutTime);
            //         $totalBreaksTime += $totalBreaksInTime + $totalBreaks;
            //     }



            //     $todaytotalTime[] = $totalCheckInTime - $totalBreaksTime;

            //     $sum = array_sum($todaytotalTime);

            //     $todayhours = floor($sum / 3600);
            //     $todayremainingMinutes = floor(($sum / 60) % 60);
            //     $todayremainingSeconds = $sum % 60;

            //     $todayCheckTime = sprintf('%02d:%02d:%02d', $todayhours, $todayremainingMinutes,  $todayremainingSeconds);
            //     return response()->json([
            //         'success' => true,
            //         'message' => 'data found',
            //         'data' => [
            //             'todayCheckTime' => $todayCheckTime,
            //             'userStatus' => $userData->status,
            //         ]
            //     ], 200);
            // }
            // if ($userCheckIn->checkIn != null && $userBreak->breakIn != null && $userBreak->breakOut != null && $userCheckIn->checkOut == null) {

            //     array_push($totalBreak, $userBreaks);


            //     foreach ($totalBreak[0] as $break) {
            //         $breakInTime = Carbon::parse($break['breakIn']);
            //         $breakOutTime = Carbon::parse($break['breakOut']);

            //         $totalBreaksTime += $breakInTime->diffInSeconds($breakOutTime);
            //     }

            //     $checkInTime = Carbon::parse($userCheckIn->checkIn);
            //     $todayCheckIntotalTime = $now->diffInSeconds($checkInTime);

            //     // $todaytotalTime[] = $totalBreaksTime - $todayCheckIntotalTime;
            //     $todaytotalTime[] = $todayCheckIntotalTime - $totalBreaksTime;

            //     $sum = array_sum($todaytotalTime);

            //     $todayhours = floor($sum / 3600);
            //     $todayremainingMinutes = floor(($sum / 60) % 60);
            //     $todayremainingSeconds = $sum % 60;

            //     $todayCheckTime = sprintf('%02d:%02d:%02d', $todayhours, $todayremainingMinutes,  $todayremainingSeconds);
            //     return response()->json([
            //         'success' => true,
            //         'message' => 'data found',
            //         'data' => [
            //             'todayCheckTime' => $todayCheckTime,
            //             'userStatus' => $userData->status,
            //         ]
            //     ], 200);



            //     // $checkInTime = Carbon::parse($userCheckIn->checkIn);
            //     // $breakInTime = Carbon::parse($userBreak->breakIn);
            //     // $breakOutTime = Carbon::parse($userBreak->breakOut);

            //     // $todayCheckIntotalTime = $now->diffInSeconds($checkInTime);
            //     // $todayBreakIntotalTime = $breakOutTime->diffInSeconds($breakInTime);

            //     // $totalTime = $todayCheckIntotalTime - $todayBreakIntotalTime;

            //     // $todayhours = floor($totalTime / 3600);
            //     // $todayremainingMinutes = floor(($totalTime / 60) % 60);
            //     // $todayremainingSeconds = $totalTime % 60;


            //     // $todayCheckTime = sprintf('%02d:%02d:%02d', $todayhours, $todayremainingMinutes,  $todayremainingSeconds);
            //     // return response()->json([
            //     //     'success' => true,
            //     //     'message' => 'data found',
            //     //     'data' => [
            //     //         'todayCheckTime' => $todayCheckTime,
            //     //         'userStatus' => $userData->status,
            //     //         'userBreakOut' => 'breakOut',
            //     //     ]
            //     // ], 200);
            // }
            // if ($userCheckIn->checkIn != null && $userBreak->breakIn != null && $userBreak->breakOut != null && $userCheckIn->checkOut != null) {

            //     // $checkInTime = Carbon::parse($userCheckIn->checkIn);
            //     // $checkOutTime = Carbon::parse($userCheckIn->checkOut);
            //     // $breakInTime = Carbon::parse($userBreak->breakIn);
            //     // $breakOutTime = Carbon::parse($userBreak->breakOut);

            //     // $todayCheckIntotalTime = $checkOutTime->diffInSeconds($checkInTime);
            //     // $todayBreakIntotalTime = $breakOutTime->diffInSeconds($breakInTime);

            //     // $totalTime = $todayCheckIntotalTime - $todayBreakIntotalTime;


            //     // $todayhours = floor($totalTime / 3600);
            //     // $todayremainingMinutes = floor(($totalTime / 60) % 60);
            //     // $todayremainingSeconds = $totalTime % 60;

            //     // $todayCheckTime = sprintf('%02d:%02d:%02d', $todayhours, $todayremainingMinutes,  $todayremainingSeconds);
            //     // return response()->json([
            //     //     'success' => true,
            //     //     'message' => 'data found',
            //     //     'data' => [
            //     //         'todayCheckTime' => $todayCheckTime,
            //     //         'userStatus' => $userData->status,
            //     //     ]
            //     // ], 200);

            //     array_push($totalBreak, $userBreaks);

            //     foreach ($totalBreak[0] as $break) {
            //         $breakInTime = Carbon::parse($break['breakIn']);
            //         $breakOutTime = Carbon::parse($break['breakOut']);

            //         $totalBreaksTime += $breakInTime->diffInSeconds($breakOutTime);
            //     }

            //     $checkInTime = Carbon::parse($userCheckIn->checkIn);
            //     $checkOutTime = Carbon::parse($userCheckIn->checkOut);

            //     $todayCheckIntotalTime = $checkOutTime->diffInSeconds($checkInTime);

            //     $todaytotalTime[] = $todayCheckIntotalTime - $totalBreaksTime;
            //     $sum = array_sum($todaytotalTime);

            //     $todayhours = floor($sum / 3600);
            //     $todayremainingMinutes = floor(($sum / 60) % 60);
            //     $todayremainingSeconds = $sum % 60;

            //     $todayCheckTime = sprintf('%02d:%02d:%02d', $todayhours, $todayremainingMinutes,  $todayremainingSeconds);
            //     return response()->json([
            //         'success' => true,
            //         'message' => 'data found',
            //         'data' => [
            //             'todayCheckTime' => $todayCheckTime,
            //             'userStatus' => $userData->status,
            //         ]
            //     ], 200);
            // }
        }
        return response()->json([
            'success' => false,
            'message' => 'data not found',
            'data' => null
        ], 500);
    }


    // function checkInTimeShowToAdmin($id)
    // {

    //     $userData = User::where('id', $id)->first();
    //     $now = Carbon::now('Asia/Karachi');


    //     if ($userData == null) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => "The user doesn't exist",
    //             'data' => null
    //         ], 404);
    //     } else {
    //         $userCheckIn = Emp_attend::where('userId', $id)->latest()->first();
    //         $userBreak = Emp_break::where('userId', $id)->where('empAttendanceId', $userCheckIn->checkIn)->latest()->first();
    //         if ($userCheckIn == null) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'User attendence not exist',
    //                 'data' => null
    //             ], 404);
    //         }


    //         if ($userCheckIn->checkIn == null && $userBreak->breakIn == null && $userCheckIn->checkOut == null) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'User not checkin today',
    //                 'data' => null
    //             ], 200);
    //         }
    //         if ($userCheckIn->checkIn != null && $userBreak->breakIn == null && $userCheckIn->checkOut == null) {

    //             $checkInTime = Carbon::parse($userCheckIn->checkIn);
    //             $todaytotalTime = $now->diffInSeconds($checkInTime);

    //             $todayhours = floor($todaytotalTime / 3600);
    //             $todayremainingMinutes = floor(($todaytotalTime / 60) % 60);
    //             $todayremainingSeconds = $todaytotalTime % 60;

    //             $todayCheckTime = sprintf('%02d:%02d:%02d', $todayhours, $todayremainingMinutes,  $todayremainingSeconds);
    //             return response()->json([
    //                 'success' => true,
    //                 'message' => 'data found',
    //                 'data' => [
    //                     'todayCheckTime' => $todayCheckTime,
    //                     'userStatus' => $userData->status,
    //                 ]
    //             ], 200);
    //         }
    //         if ($userCheckIn->checkIn != null && $userBreak->breakIn == null && $userCheckIn->checkOut != null) {

    //             $checkInTime = Carbon::parse($userCheckIn->checkIn);
    //             $checkOutTime = Carbon::parse($userCheckIn->checkOut);
    //             $todaytotalTime = $checkOutTime->diffInSeconds($checkInTime);

    //             $todayhours = floor($todaytotalTime / 3600);
    //             $todayremainingMinutes = floor(($todaytotalTime / 60) % 60);
    //             $todayremainingSeconds = $todaytotalTime % 60;

    //             $todayCheckTime = sprintf('%02d:%02d:%02d', $todayhours, $todayremainingMinutes,  $todayremainingSeconds);
    //             return response()->json([
    //                 'success' => true,
    //                 'message' => 'data found',
    //                 'data' => [
    //                     'todayCheckTime' => $todayCheckTime,
    //                     'userStatus' => $userData->status,
    //                 ]
    //             ], 200);
    //         }
    //         if ($userCheckIn->checkIn != null && $userBreak->breakIn != null && $userBreak->breakOut == null && $userCheckIn->checkOut == null) {

    //             $checkInTime = Carbon::parse($userCheckIn->checkIn);
    //             $breakInTime = Carbon::parse($userBreak->breakIn);
    //             $todaytotalTime = $breakInTime->diffInSeconds($checkInTime);

    //             $todayhours = floor($todaytotalTime / 3600);
    //             $todayremainingMinutes = floor(($todaytotalTime / 60) % 60);
    //             $todayremainingSeconds = $todaytotalTime % 60;

    //             $todayCheckTime = sprintf('%02d:%02d:%02d', $todayhours, $todayremainingMinutes,  $todayremainingSeconds);
    //             return response()->json([
    //                 'success' => true,
    //                 'message' => 'data found',
    //                 'data' => [
    //                     'todayCheckTime' => $todayCheckTime,
    //                     'userStatus' => $userData->status,
    //                 ]
    //             ], 200);
    //         }
    //         if ($userCheckIn->checkIn != null && $userBreak->breakIn != null && $userBreak->breakOut != null && $userCheckIn->checkOut == null) {
    //             $checkInTime = Carbon::parse($userCheckIn->checkIn);
    //             $breakInTime = Carbon::parse($userBreak->breakIn);
    //             $breakOutTime = Carbon::parse($userBreak->breakOut);

    //             $todayCheckIntotalTime = $now->diffInSeconds($checkInTime);
    //             $todayBreakIntotalTime = $breakOutTime->diffInSeconds($breakInTime);

    //             $totalTime = $todayCheckIntotalTime - $todayBreakIntotalTime;

    //             $todayhours = floor($totalTime / 3600);
    //             $todayremainingMinutes = floor(($totalTime / 60) % 60);
    //             $todayremainingSeconds = $totalTime % 60;


    //             $todayCheckTime = sprintf('%02d:%02d:%02d', $todayhours, $todayremainingMinutes,  $todayremainingSeconds);
    //             return response()->json([
    //                 'success' => true,
    //                 'message' => 'data found',
    //                 'data' => [
    //                     'todayCheckTime' => $todayCheckTime,
    //                     'userStatus' => $userData->status,
    //                     'userBreakOut' => 'breakOut',
    //                 ]
    //             ], 200);
    //         }
    //         if ($userCheckIn->checkIn != null && $userBreak->breakIn != null && $userBreak->breakOut != null && $userCheckIn->checkOut != null) {

    //             $checkInTime = Carbon::parse($userCheckIn->checkIn);
    //             $checkOutTime = Carbon::parse($userCheckIn->checkOut);
    //             $breakInTime = Carbon::parse($userBreak->breakIn);
    //             $breakOutTime = Carbon::parse($userBreak->breakOut);

    //             $todayCheckIntotalTime = $checkOutTime->diffInSeconds($checkInTime);
    //             $todayBreakIntotalTime = $breakOutTime->diffInSeconds($breakInTime);

    //             $totalTime = $todayCheckIntotalTime - $todayBreakIntotalTime;
    //             // print_r($totalTime);
    //             // die;

    //             $todayhours = floor($totalTime / 3600);
    //             $todayremainingMinutes = floor(($totalTime / 60) % 60);
    //             $todayremainingSeconds = $totalTime % 60;

    //             $todayCheckTime = sprintf('%02d:%02d:%02d', $todayhours, $todayremainingMinutes,  $todayremainingSeconds);
    //             return response()->json([
    //                 'success' => true,
    //                 'message' => 'data found',
    //                 'data' => [
    //                     'todayCheckTime' => $todayCheckTime,
    //                     'userStatus' => $userData->status,
    //                 ]
    //             ], 200);
    //         }
    //     }
    //     return response()->json([
    //         'success' => false,
    //         'message' => 'data not found',
    //         'data' => null
    //     ], 500);
    // }

    function checkInTimeShowToAdmin($id)
    {
        $auth = auth()->user();
        $userData = User::where('id', $id)->first();
        $now = Carbon::now('Asia/Karachi');
        $todaytotalTime = [];
        $totalBreak = [];

        $totalBreaksTime = 0;


        if ($userData == null) {
            return response()->json([
                'success' => false,
                'message' => "The user doesn't exist",
                'data' => null
            ], 404);
        } else {
            $userCheckIn = Emp_attend::where('userId', $id)->latest()->first();
            $userBreak = Emp_break::where('userId', $id)->where('empAttendanceId', $userCheckIn->id)->latest()->first();
            $userBreaks = Emp_break::where('userId', $id)->where('empAttendanceId', $userCheckIn->id)->get();

            if ($userCheckIn == null) {
                return response()->json([
                    'success' => false,
                    'message' => 'User attendence not exist',
                    'data' => null
                ], 404);
            }


            if ($userCheckIn->checkIn == null && !$userBreak && $userCheckIn->checkOut == null) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not checkin today',
                    'data' => null
                ], 200);
            }
            if ($userCheckIn->checkIn != null && !$userBreak && $userCheckIn->checkOut == null) {

                $checkInTime = Carbon::parse($userCheckIn->checkIn);
                $todaytotalTime = $now->diffInSeconds($checkInTime);

                $todayhours = floor($todaytotalTime / 3600);
                $todayremainingMinutes = floor(($todaytotalTime / 60) % 60);
                $todayremainingSeconds = $todaytotalTime % 60;

                $todayCheckTime = sprintf('%02d:%02d:%02d', $todayhours, $todayremainingMinutes,  $todayremainingSeconds);
                return response()->json([
                    'success' => true,
                    'message' => 'data found',
                    'data' => [
                        'todayCheckTime' => $todayCheckTime,
                        'userStatus' => $userData->status,
                    ]
                ], 200);
            }
            if ($userCheckIn->checkIn != null && !$userBreak && $userCheckIn->checkOut != null) {

                $checkInTime = Carbon::parse($userCheckIn->checkIn);
                $checkOutTime = Carbon::parse($userCheckIn->checkOut);
                $todaytotalTime = $checkOutTime->diffInSeconds($checkInTime);

                $todayhours = floor($todaytotalTime / 3600);
                $todayremainingMinutes = floor(($todaytotalTime / 60) % 60);
                $todayremainingSeconds = $todaytotalTime % 60;

                $todayCheckTime = sprintf('%02d:%02d:%02d', $todayhours, $todayremainingMinutes,  $todayremainingSeconds);
                return response()->json([
                    'success' => true,
                    'message' => 'data found',
                    'data' => [
                        'todayCheckTime' => $todayCheckTime,
                        'userStatus' => $userData->status,
                    ]
                ], 200);
            }
            if ($userCheckIn->checkIn != null && $userBreak->breakIn != null && $userBreak->breakOut == null && $userCheckIn->checkOut == null) {

                array_push($totalBreak, $userBreaks);

                $totalBreaksTime = 0;
                $totalBreaksInTime = 0;

                $checkInTime = Carbon::parse($userCheckIn->checkIn);
                $totalCheckInTime = $now->diffInSeconds($checkInTime);

                foreach ($totalBreak[0] as $break) {
                    $breakInTime = Carbon::parse($break['breakIn']);
                    $breakOutTime = Carbon::parse($break['breakOut']);

                    if ($break === $userBreaks->last()) {
                        $lastBreakIn = Carbon::parse($break['breakIn']);
                        $totalBreaksInTime += $now->diffInSeconds($lastBreakIn);
                    }

                    $totalBreaks = $breakInTime->diffInSeconds($breakOutTime);
                    $totalBreaksTime += $totalBreaksInTime + $totalBreaks;
                }

                $todaytotalTime[] = $totalCheckInTime - $totalBreaksTime;

                $sum = array_sum($todaytotalTime);

                $todayhours = floor($sum / 3600);
                $todayremainingMinutes = floor(($sum / 60) % 60);
                $todayremainingSeconds = $sum % 60;

                $todayCheckTime = sprintf('%02d:%02d:%02d', $todayhours, $todayremainingMinutes,  $todayremainingSeconds);
                return response()->json([
                    'success' => true,
                    'message' => 'data found',
                    'data' => [
                        'todayCheckTime' => $todayCheckTime,
                        'userStatus' => $userData->status,
                    ]
                ], 200);
            }
            if ($userCheckIn->checkIn != null && $userBreak->breakIn != null && $userBreak->breakOut != null && $userCheckIn->checkOut == null) {

                array_push($totalBreak, $userBreaks);


                foreach ($totalBreak[0] as $break) {
                    $breakInTime = Carbon::parse($break['breakIn']);
                    $breakOutTime = Carbon::parse($break['breakOut']);

                    $totalBreaksTime += $breakInTime->diffInSeconds($breakOutTime);
                }

                $checkInTime = Carbon::parse($userCheckIn->checkIn);
                $todayCheckIntotalTime = $now->diffInSeconds($checkInTime);

                $todaytotalTime[] = $todayCheckIntotalTime - $totalBreaksTime;

                $sum = array_sum($todaytotalTime);

                $todayhours = floor($sum / 3600);
                $todayremainingMinutes = floor(($sum / 60) % 60);
                $todayremainingSeconds = $sum % 60;

                $todayCheckTime = sprintf('%02d:%02d:%02d', $todayhours, $todayremainingMinutes,  $todayremainingSeconds);
                return response()->json([
                    'success' => true,
                    'message' => 'data found',
                    'data' => [
                        'todayCheckTime' => $todayCheckTime,
                        'userStatus' => $userData->status,
                    ]
                ], 200);

            }
            if ($userCheckIn->checkIn != null && $userBreak->breakIn != null && $userBreak->breakOut != null && $userCheckIn->checkOut != null) {

                array_push($totalBreak, $userBreaks);

                foreach ($totalBreak[0] as $break) {
                    $breakInTime = Carbon::parse($break['breakIn']);
                    $breakOutTime = Carbon::parse($break['breakOut']);

                    $totalBreaksTime += $breakInTime->diffInSeconds($breakOutTime);
                }

                $checkInTime = Carbon::parse($userCheckIn->checkIn);
                $checkOutTime = Carbon::parse($userCheckIn->checkOut);

                $todayCheckIntotalTime = $checkOutTime->diffInSeconds($checkInTime);

                $todaytotalTime[] = $todayCheckIntotalTime - $totalBreaksTime;
                $sum = array_sum($todaytotalTime);

                $todayhours = floor($sum / 3600);
                $todayremainingMinutes = floor(($sum / 60) % 60);
                $todayremainingSeconds = $sum % 60;

                $todayCheckTime = sprintf('%02d:%02d:%02d', $todayhours, $todayremainingMinutes,  $todayremainingSeconds);
                return response()->json([
                    'success' => true,
                    'message' => 'data found',
                    'data' => [
                        'todayCheckTime' => $todayCheckTime,
                        'userStatus' => $userData->status,
                    ]
                ], 200);
            }
        }
        return response()->json([
            'success' => false,
            'message' => 'data not found',
            'data' => null
        ], 500);
    }

    function updateEmployeeStatus(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'isEmployee' => [new Enum(EmployeeStatusEnum::class)],
            // 'isEmployee' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages(),
                'data' => null
            ], 400);
        }

        $userData = User::where('id', $id)->first();

        if ($userData == null) {
            return response()->json([
                'success' => false,
                'message' => 'The user doest exist',
                'data' => null
            ], 404);
        }
        if ($userData) {

            $userData->isEmployee = $request->isEmployee;

            $userData->save();

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully',
                'data' => null
            ], 200);
        }
        return response()->json([
            'success' => false,
            'message' => 'Internal server error',
            'data' => null
        ], 500);
    }
    
    
//   function getCheckBreakTimefromDatePicker(Request $request, $id)
//     {
//         $userId = User::where('id', $id)->first();
//         if (!$userId) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'User not found',
//                 'data' => null
//             ]);
//         }

//         $validator = Validator::make($request->all(), [
//             'date' => ['required', 'date'],
//         ]);
//         $date = Carbon::parse($request->date)->format('Y-m-d');;

//         if ($validator->fails()) {
//             return response()->json([
//                 'success' => false,
//                 'message' => $validator->messages(),
//                 'data' => null
//             ], 400);
//         }


//         $CheckIn = DB::table('emp_attends')
//             ->whereDate('checkIn', $date)
//             ->get();

//         if ($CheckIn->isEmpty()) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'Data not found',
//                 'data' => null
//             ], 404);
//         }

//         $userCheckIn = DB::table('users')
//             ->join('emp_attends', 'users.id', '=', 'emp_attends.userId')
//             ->whereDate('checkIn', $date)
//             ->select('users.id', 'users.name', 'emp_attends.checkIn', 'emp_attends.checkOut', 'emp_attends.late')
//             ->get();

//         return response()->json([
//             'success' => true,
//             'message' => 'data found',
//             'data' => $userCheckIn,

//         ], 200);
//     }

 function getCheckBreakTimefromDatePicker(Request $request, $id)
    {
        $userId = User::where('id', $id)->first();
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
                'data' => null
            ]);
        }

        $validator = Validator::make($request->all(), [
            'date' => ['required', 'date'],
        ]);
        $date = Carbon::parse($request->date)->format('Y-m-d');;

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages(),
                'data' => null
            ], 400);
        }


        $CheckIn = DB::table('emp_attends')
            ->whereDate('checkIn', $date)
            ->get();

        if ($CheckIn->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found',
                'data' => null
            ], 404);
        }

        $userCheckIn = DB::table('users')
            ->join('emp_attends', 'users.id', '=', 'emp_attends.userId')
            ->whereDate('checkIn', $date)
            ->select('users.id', 'users.name', 'emp_attends.checkIn', 'emp_attends.checkOut', 'emp_attends.late')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'data found',
            'data' => $userCheckIn,

        ], 200);
    }
}

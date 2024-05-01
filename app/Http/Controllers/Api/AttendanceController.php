<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Emp_attend;
use App\Models\Leave;
use App\Models\Time_schedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Emp_break;
use App\Exports\UserExport;
use Maatwebsite\Excel\Facades\Excel;
use DB;





class AttendanceController extends Controller
{
    // function weeklyAttendance($id)
    // {
    //     $current_date = Carbon::now('Asia/Karachi');
    //     $userId = User::find($id);

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
    //             ->whereDate('checkIn', $current_date->toDateString())
    //             ->get();

    //         $status = '';
    //         $timeIn = null;
    //         $timeOut = null;

    //         if (count($attendance) == 0) {
    //             $userLeaves = Leave::where('userId', $id)
    //                 ->whereDate('dateFrom', '<=', $current_date)
    //                 ->whereDate('dateTo', '>=', $current_date)
    //                 ->first();

    //             if ($userLeaves) {
    //                 $status = 'Leave';
    //             } else {
    //                 $status = 'Absent';
    //             }
    //         } else {
    //             $total = 0;

    //             foreach ($attendance as $entry) {
    //                 $checkInTime = Carbon::parse($entry->checkIn);
    //                 $checkOutTime = Carbon::parse($entry->checkOut);
    //                 $entryDuration = $checkInTime->diffInMinutes($checkOutTime);
    //                 $total += $entryDuration;
    //                 $timeIn = $checkInTime->format('H:i:s');
    //                 $timeOut = $checkOutTime->format('H:i:s');
    //             }

    //             $hours = intdiv($total, 60);
    //             $remainingMinutes = $total % 60;
    //             $remainingSeconds = $total % 60;
    //             $status = sprintf('%02d:%02d:%02d', $hours, $remainingMinutes, $remainingSeconds);
    //         }

    //         $week_data[] = [
    //             'date' => $current_date->toDateString(),
    //             'status' => $status,
    //             'timeIn' => $timeIn,
    //             'timeOut' => $timeOut,

    //         ];

    //         $current_date->subDay();
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Weekly Attendance',
    //         'data' => $week_data,
    //     ], 200);
    // }
    
     function weeklyAttendance()
    {
        $userAut = auth()->user();
        $current_date = Carbon::now('Asia/Karachi');
        $userId = User::find($userAut->id);

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
                'data' => null
            ], 404);
        }

        $week_data = [];
        $hoursPerWeek = 0;

        for ($day = 1; $day <= 7; $day++) {
            $attendance = Emp_attend::where('userId', $userAut->id)
                ->whereDate('checkIn', $current_date->toDateString())
                ->get();

            $status = '';
            $timeIn = null;
            $timeOut = null;

            if (count($attendance) == 0) {
                $userLeaves = Leave::where('userId', $userAut->id)
                    ->whereDate('dateFrom', '<=', $current_date)
                    ->whereDate('dateTo', '>=', $current_date)
                    ->first();

                if ($userLeaves) {
                    $status = 'Leave';
                } else {
                    $status = 'Absent';
                }
            } else {
                $total = 0;
                $totalHours = 0;

                foreach ($attendance as $entry) {
                    $checkInTime = Carbon::parse($entry->checkIn);
                    $checkOutTime = Carbon::parse($entry->checkOut);
                    $entryDuration = $checkInTime->diffInSeconds($checkOutTime);
                    $totalHours = $checkInTime->diffInSeconds($checkOutTime);
                    $total += $entryDuration;
                    $hoursPerWeek += $totalHours;

                    // $timeIn = Carbon::parse($totalHhoursPerWeek)->format('H:i:s');
                    // $timeOut = $checkOutTime->format('H:i:s');
                }

                $hours = floor($total / 3600);
                $remainingMinutes = floor(($total / 60) % 60);
                $remainingSeconds = $total % 60;
                $status = sprintf('%02d:%02d:%02d', $hours, $remainingMinutes, $remainingSeconds);

                $todayhours = floor($hoursPerWeek / 3600);
                $todayremainingMinutes = floor(($hoursPerWeek / 60) % 60);
                $todayremainingSeconds = $hoursPerWeek % 60;

                $totalHoursPerWeek = sprintf('%02d:%02d:%02d', $todayhours, $todayremainingMinutes,  $todayremainingSeconds);
            }

            $week_data[] = [
                'name' => $userId->name,
                'date' => $current_date->toDateString(),
                'status' => $status,
                'timeInOut' => $attendance,
                // 'timeOut' => $timeOut,
            ];

            $current_date->subDay();
        }

        return response()->json([
            'success' => true,
            'message' => 'Weekly Attendance',
            'data' => [
                'timeInOut' => $week_data,
                'totalHoursPerWeek' => $totalHoursPerWeek
            ],
        ], 200);
    }

    // function userCheckInOut(Request $request)
    // {

    //     $userAut = auth()->user();
    //     $totalBreak = [];
    //     $total = [];


    //     $today = Carbon::now('Asia/Karachi');
    //     $userLeaves = Leave::where('userId', $userAut->id)->whereDate('dateFrom', '<=', $today)
    //         ->whereDate('dateTo', '>=', $today)->where('status', 'Approved')->first();
    //     if (!$userLeaves) {

    //         $empCheckOut = Emp_attend::where('userId', $userAut->id)
    //             ->whereNotNull('checkIn')
    //             ->whereNull('checkOut')
    //             ->latest()
    //             ->first();
    //         if ($empCheckOut) {
    //             $empBreakIn = Emp_break::where('userId', $userAut->id)
    //                 ->whereNotNull('breakIn')
    //                 ->whereNull('breakOut')
    //                 ->latest()
    //                 ->first();
    //             if ($empBreakIn) {
    //                 return response()->json([
    //                     'success' => false,
    //                     'message' => 'Break Out first',
    //                     'data' => null,
    //                 ], 400);
    //             } else {
    //                 $checkOut = Emp_attend::where('userId', $userAut->id)->where('checkOut', null)->update(['checkOut' => $today]);
    //                 User::where('id', $userAut->id)->update(['status' => 'Inactive']);
    //                 if ($checkOut) {

    //                     $checkInTime = Carbon::parse($empCheckOut->checkIn);
    //                     $checkOutTime = Carbon::parse($empCheckOut->checkOut);

    //                     $breaks = Emp_break::where('empAttendanceId', $empCheckOut->id)->where('userId', $userAut->id)->get();
    //                     array_push($totalBreak, $breaks);

    //                     $totalBreakInTime = 0;

    //                     foreach ($totalBreak[0] as $break) {
    //                         $breakInTime = Carbon::parse($break['breakIn']);
    //                         $breakOutTime = Carbon::parse($break['breakOut']);


    //                         $totalBreakInTime += $breakInTime->diffInSeconds($breakOutTime);
    //                     }

    //                     $checkInTotalTime = $checkInTime->diffInSeconds($checkOutTime);

    //                     $total[] = $checkInTotalTime - $totalBreakInTime;

    //                     $sum = array_sum($total);

    //                     $todayhours = floor($sum / 3600);
    //                     $todayremainingMinutes = floor(($sum / 60) % 60);
    //                     $todayremainingSeconds = $sum % 60;

    //                     $todayCheckTime = sprintf('%02d:%02d:%02d', $todayhours, $todayremainingMinutes,  $todayremainingSeconds);

    //                 }
    //                 return response()->json([
    //                     'success' => True,
    //                     'message' => 'Check out successfully',
    //                     'data' => $todayCheckTime
    //                 ], 200);
    //             }
    //         }
    //         $empCheckInn = Emp_attend::where('userId', $userAut->id)
    //             ->latest()
    //             ->first();
    //         if ($empCheckInn) {

    //             $empCheckIn = Emp_attend::where('userId', $userAut->id)
    //                 ->whereNotNull('checkOut')
    //                 ->latest()
    //                 ->first();
    //             if ($empCheckIn) {
    //                 $userSchedule = Time_schedule::where('id', $userAut->schedule)->first();
    //                 $check = Carbon::parse($userSchedule->late);

    //                 if ($today > $check) {


    //                     $data = [
    //                         'userId' => $userAut->id,
    //                         'checkIn' => $today,
    //                     ];
    //                     $saveCheckIn = Emp_attend::create($data);


    //                     User::where('id', $userAut->id)->update(['status' => 'Active']);
    //                     Emp_attend::where('userId', $userAut->id)->where('id', $saveCheckIn->id)->update(['late' => 'late']);

    //                     return response()->json([
    //                         'success' => True,
    //                         'message' => 'Check in successfully',
    //                         'data' => null,
    //                     ], 200);
    //                 } else {
    //                     $data = [
    //                         'userId' => $userAut->id,
    //                         'checkIn' => $today,
    //                     ];
    //                     $saveCheckIn = Emp_attend::create($data);

    //                     User::where('id', $userAut->id)->update(['status' => 'Active']);

    //                     return response()->json([
    //                         'success' => True,
    //                         'message' => 'Check in successfully',
    //                         'data' => null,
    //                     ], 200);
    //                 }
    //             }
    //         } else {
    //             $userSchedule = Time_schedule::where('id', $userAut->schedule)->first();
    //             $check = Carbon::parse($userSchedule->late);

    //             if ($today > $check) {


    //                 $data = [
    //                     'userId' => $userAut->id,
    //                     'checkIn' => $today,
    //                 ];
    //                 $saveCheckIn = Emp_attend::create($data);


    //                 User::where('id', $userAut->id)->update(['status' => 'Active']);
    //                 Emp_attend::where('userId', $userAut->id)->where('id', $saveCheckIn->id)->update(['late' => 'late']);

    //                 return response()->json([
    //                     'success' => True,
    //                     'message' => 'Check in successfully',
    //                     'data' => null,
    //                 ], 200);
    //             } else {
    //                 $data = [
    //                     'userId' => $userAut->id,
    //                     'checkIn' => $today,
    //                 ];
    //                 $saveCheckIn = Emp_attend::create($data);

    //                 User::where('id', $userAut->id)->update(['status' => 'Active']);

    //                 return response()->json([
    //                     'success' => True,
    //                     'message' => 'Check in successfully',
    //                     'data' => null,
    //                 ], 200);
    //             }
    //         }
    //     } else {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'You are leaved today',
    //             'data' => null,
    //         ], 404);
    //     }
    // }
    
//     function userCheckInOut(Request $request)
//     {

//         $userAut = auth()->user();
//         // $totalBreak = [];
//         // $total = [];


//         $today = Carbon::now('Asia/Karachi');
//         // print_r($today);
//         // die;

//         $currentDate = Carbon::today('Asia/Karachi');
// //         print_r($currentDate);
// // die;
//         $yesterdayDates = Carbon::yesterday('Asia/Karachi');

//         // $temp = explode(' ', $yesterdayDates);
//         // $startDate = "$temp[0] 23:59:59";

//         // print_r($startDate);
//         // // die;

//         $userLeaves = Leave::where('userId', $userAut->id)->whereDate('dateFrom', '<=', $today)
//             ->whereDate('dateTo', '>=', $today)->where('status', 'Approved')->first();
//         if (!$userLeaves) {

//             $empCheckOut = Emp_attend::where('userId', $userAut->id)
//                 ->whereNotNull('checkIn')
//                 ->whereNull('checkOut')
//                 ->latest()
//                 ->first();
//             if ($empCheckOut) {
//                 // $empBreakIn = Emp_break::where('userId', $userAut->id)
//                 //     ->whereNotNull('breakIn')
//                 //     ->whereNull('breakOut')
//                 //     ->latest()
//                 //     ->first();
//                 // if ($empBreakIn) {
//                 //     return response()->json([
//                 //         'success' => false,
//                 //         'message' => 'Break Out first',
//                 //         'data' => null,
//                 //     ], 400);
//                 // } else {
//                 $checkCheckInTime = Carbon::parse($empCheckOut->checkIn);
//                 // print_r($checkCheckInTime);
//                 // die;


//                 if (!$checkCheckInTime->isSameDay($currentDate)) {
//                     // $addDay = $checkCheckInTime;
//                     $temp = explode(' ', $checkCheckInTime);
//                     // $startDate = "$temp[0] 23:59:59";
//                     $endDate = Carbon::parse($temp[0] . ' 23:59:59');
//                     $startDate = Carbon::parse($temp[0] . '00:00:00');

//                     $nextDayStart = $startDate->addDay();
//                     // $nextDayEnd = $endDate->addDay();

//                     // print_r(intval($startDate));
//                     // print_r($addDay);
//                     // die;
//                     $diffInDays = $checkCheckInTime->diffInDays($today);
//                     // print_r($diffInDays);
//                     // print_r($today);
//                     // print_r($checkCheckInTime);

//                     // die;

//                     for ($i = 1; $i <= $diffInDays; $i++) {

//                         $checkOutAt12 = Emp_attend::where('userId', $userAut->id)->where('checkOut', null)->update(['checkOut' => $endDate]);
//                         $nextDayEnd = $endDate->addDay();

//                         if ($i == $diffInDays) {
//                             print_r('a');
//                             // $checkOutAtNewDate = Emp_attend::where('userId', $userAut->id)->where('checkOut', null)->update(['checkOut' => $today]);
//                             $data = [
//                                 'userId' => $userAut->id,
//                                 'checkIn' => $nextDayStart,
//                                 'checkOut' => $today,

//                             ];
//                             $createCheckIn = Emp_attend::create($data);
//                             User::where('id', $userAut->id)->update(['status' => 'Inactive']);
//                             $checkInTime = Carbon::parse($empCheckOut->checkIn);
//                             $checkOutTime = Carbon::parse($empCheckOut->checkOut);

//                             $checkInTotalTime = $checkInTime->diffInSeconds($checkOutTime);

//                             $todayhours = floor($checkInTotalTime / 3600);
//                             $todayremainingMinutes = floor(($checkInTotalTime / 60) % 60);
//                             $todayremainingSeconds = $checkInTotalTime % 60;

//                             $todayCheckTime = sprintf('%02d:%02d:%02d', $todayhours, $todayremainingMinutes, $todayremainingSeconds);

//                             return response()->json([
//                                 'success' => True,
//                                 'message' => 'Check out successfully',
//                                 'data' => $todayCheckTime
//                             ], 200);
//                         }

//                         $data = [
//                             'userId' => $userAut->id,
//                             'checkIn' => $nextDayStart,
//                             'checkOut' => $nextDayEnd,
//                         ];
//                         $createCheckIn = Emp_attend::create($data);
//                         $nextDay = $startDate->addDay();
//                     }

//                     // $checkOutAt12 = Emp_attend::where('userId', $userAut->id)->where('checkOut', null)->update(['checkOut' => $startDate]);
//                     // $data = [
//                     //     'userId' => $userAut->id,
//                     //     'checkIn' => $currentDate,
//                     // ];
//                     // $createCheckIn = Emp_attend::create($data);

//                     // $checkOutAtNewDate = Emp_attend::where('userId', $userAut->id)->where('checkOut', null)->update(['checkOut' => $today]);
//                     // User::where('id', $userAut->id)->update(['status' => 'Inactive']);

//                     // $checkInTime = Carbon::parse($empCheckOut->checkIn);
//                     // $checkOutTime = Carbon::parse($empCheckOut->checkOut);

//                     // $checkInTotalTime = $checkInTime->diffInSeconds($checkOutTime);

//                     // $todayhours = floor($checkInTotalTime / 3600);
//                     // $todayremainingMinutes = floor(($checkInTotalTime / 60) % 60);
//                     // $todayremainingSeconds = $checkInTotalTime % 60;

//                     // $todayCheckTime = sprintf('%02d:%02d:%02d', $todayhours, $todayremainingMinutes,  $todayremainingSeconds);

//                     // return response()->json([
//                     //     'success' => True,
//                     //     'message' => 'Check out successfully',
//                     //     'data' => $todayCheckTime
//                     // ], 200);
//                 } 
//                 // else {

//                 //     $checkOut = Emp_attend::where('userId', $userAut->id)->where('checkOut', null)->update(['checkOut' => $today]);
//                 //     User::where('id', $userAut->id)->update(['status' => 'Inactive']);
//                 //     if ($checkOut) {

//                 //         $checkInTime = Carbon::parse($empCheckOut->checkIn);
//                 //         $checkOutTime = Carbon::parse($empCheckOut->checkOut);

//                 //         // $breaks = Emp_break::where('empAttendanceId', $empCheckOut->id)->where('userId', $userAut->id)->get();
//                 //         // array_push($totalBreak, $breaks);

//                 //         // $totalBreakInTime = 0;

//                 //         // foreach ($totalBreak[0] as $break) {
//                 //         //     $breakInTime = Carbon::parse($break['breakIn']);
//                 //         //     $breakOutTime = Carbon::parse($break['breakOut']);


//                 //         //     $totalBreakInTime += $breakInTime->diffInSeconds($breakOutTime);
//                 //         // }

//                 //         $checkInTotalTime = $checkInTime->diffInSeconds($checkOutTime);

//                 //         // $total[] = $checkInTotalTime - $totalBreakInTime;

//                 //         // $sum = array_sum($total);

//                 //         $todayhours = floor($checkInTotalTime / 3600);
//                 //         $todayremainingMinutes = floor(($checkInTotalTime / 60) % 60);
//                 //         $todayremainingSeconds = $checkInTotalTime % 60;

//                 //         $todayCheckTime = sprintf('%02d:%02d:%02d', $todayhours, $todayremainingMinutes, $todayremainingSeconds);
//                 //     }
//                 //     return response()->json([
//                 //         'success' => True,
//                 //         'message' => 'Check out successfully',
//                 //         'data' => $todayCheckTime
//                 //     ], 200);
//                 //     // }
//                 // }
//             }
//             $empCheckInn = Emp_attend::where('userId', $userAut->id)
//                 ->latest()
//                 ->first();
//             if ($empCheckInn) {

//                 $empCheckIn = Emp_attend::where('userId', $userAut->id)
//                     ->whereNotNull('checkOut')
//                     ->latest()
//                     ->first();
//                 if ($empCheckIn) {
//                     $userSchedule = Time_schedule::where('id', $userAut->schedule)->first();
//                     $check = Carbon::parse($userSchedule->late);

//                     if ($today > $check) {


//                         $data = [
//                             'userId' => $userAut->id,
//                             'checkIn' => $today,
//                         ];
//                         $saveCheckIn = Emp_attend::create($data);


//                         User::where('id', $userAut->id)->update(['status' => 'Active']);
//                         Emp_attend::where('userId', $userAut->id)->where('id', $saveCheckIn->id)->update(['late' => 'late']);

//                         return response()->json([
//                             'success' => True,
//                             'message' => 'Check in successfully',
//                             'data' => null,
//                         ], 200);
//                     } else {
//                         $data = [
//                             'userId' => $userAut->id,
//                             'checkIn' => $today,
//                         ];
//                         $saveCheckIn = Emp_attend::create($data);

//                         User::where('id', $userAut->id)->update(['status' => 'Active']);

//                         return response()->json([
//                             'success' => True,
//                             'message' => 'Check in successfully',
//                             'data' => null,
//                         ], 200);
//                     }
//                 }
//             } else {
//                 $userSchedule = Time_schedule::where('id', $userAut->schedule)->first();
//                 $check = Carbon::parse($userSchedule->late);

//                 if ($today > $check) {


//                     $data = [
//                         'userId' => $userAut->id,
//                         'checkIn' => $today,
//                     ];
//                     $saveCheckIn = Emp_attend::create($data);


//                     User::where('id', $userAut->id)->update(['status' => 'Active']);
//                     Emp_attend::where('userId', $userAut->id)->where('id', $saveCheckIn->id)->update(['late' => 'late']);

//                     return response()->json([
//                         'success' => True,
//                         'message' => 'Check in successfully',
//                         'data' => null,
//                     ], 200);
//                 } else {
//                     $data = [
//                         'userId' => $userAut->id,
//                         'checkIn' => $today,
//                     ];
//                     $saveCheckIn = Emp_attend::create($data);

//                     User::where('id', $userAut->id)->update(['status' => 'Active']);

//                     return response()->json([
//                         'success' => True,
//                         'message' => 'Check in successfully',
//                         'data' => null,
//                     ], 200);
//                 }
//             }
//         } else {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'You are leaved today',
//                 'data' => null,
//             ], 404);
//         }
//     }
    
     function userCheckInOut(Request $request)
    {

        $userAut = auth()->user();
        // $totalBreak = [];
        // $total = [];


        $today = Carbon::now('Asia/Karachi');

        $currentDate = Carbon::today('Asia/Karachi');
        $yesterdayDates = Carbon::yesterday('Asia/Karachi');

        $temp = explode(' ', $yesterdayDates);
        $startDate = "$temp[0] 23:59:59";

        // print_r($startDate);
        // die;

        $userLeaves = Leave::where('userId', $userAut->id)->whereDate('dateFrom', '<=', $today)
            ->whereDate('dateTo', '>=', $today)->where('status', 'Approved')->first();
        if (!$userLeaves) {

            $empCheckOut = Emp_attend::where('userId', $userAut->id)
                ->whereNotNull('checkIn')
                ->whereNull('checkOut')
                ->latest()
                ->first();
            if ($empCheckOut) {
                // $empBreakIn = Emp_break::where('userId', $userAut->id)
                //     ->whereNotNull('breakIn')
                //     ->whereNull('breakOut')
                //     ->latest()
                //     ->first();
                // if ($empBreakIn) {
                //     return response()->json([
                //         'success' => false,
                //         'message' => 'Break Out first',
                //         'data' => null,
                //     ], 400);
                // } else {
                $checkCheckInTime = Carbon::parse($empCheckOut->checkIn);

                if (!$checkCheckInTime->isSameDay($today)) {
                    $checkOutAt12 = Emp_attend::where('userId', $userAut->id)->where('checkOut', null)->update(['checkOut' => $startDate]);
                    $data = [
                        'userId' => $userAut->id,
                        'checkIn' => $currentDate,
                    ];
                    $createCheckIn = Emp_attend::create($data);

                    $checkOutAtNewDate = Emp_attend::where('userId', $userAut->id)->where('checkOut', null)->update(['checkOut' => $today]);
                    User::where('id', $userAut->id)->update(['status' => 'Inactive']);

                    $checkInTime = Carbon::parse($empCheckOut->checkIn);
                    $checkOutTime = Carbon::parse($empCheckOut->checkOut);

                    $checkInTotalTime = $checkInTime->diffInSeconds($checkOutTime);

                    $todayhours = floor($checkInTotalTime / 3600);
                    $todayremainingMinutes = floor(($checkInTotalTime / 60) % 60);
                    $todayremainingSeconds = $checkInTotalTime % 60;

                    $todayCheckTime = sprintf('%02d:%02d:%02d', $todayhours, $todayremainingMinutes,  $todayremainingSeconds);

                    return response()->json([
                        'success' => True,
                        'message' => 'Check out successfully',
                        'data' => $todayCheckTime
                    ], 200);
                } else {

                    $checkOut = Emp_attend::where('userId', $userAut->id)->where('checkOut', null)->update(['checkOut' => $today]);
                    User::where('id', $userAut->id)->update(['status' => 'Inactive']);
                    if ($checkOut) {

                        $checkInTime = Carbon::parse($empCheckOut->checkIn);
                        $checkOutTime = Carbon::parse($empCheckOut->checkOut);

                        // $breaks = Emp_break::where('empAttendanceId', $empCheckOut->id)->where('userId', $userAut->id)->get();
                        // array_push($totalBreak, $breaks);

                        // $totalBreakInTime = 0;

                        // foreach ($totalBreak[0] as $break) {
                        //     $breakInTime = Carbon::parse($break['breakIn']);
                        //     $breakOutTime = Carbon::parse($break['breakOut']);


                        //     $totalBreakInTime += $breakInTime->diffInSeconds($breakOutTime);
                        // }

                        $checkInTotalTime = $checkInTime->diffInSeconds($checkOutTime);

                        // $total[] = $checkInTotalTime - $totalBreakInTime;

                        // $sum = array_sum($total);

                        $todayhours = floor($checkInTotalTime / 3600);
                        $todayremainingMinutes = floor(($checkInTotalTime / 60) % 60);
                        $todayremainingSeconds = $checkInTotalTime % 60;

                        $todayCheckTime = sprintf('%02d:%02d:%02d', $todayhours, $todayremainingMinutes,  $todayremainingSeconds);
                    }
                    return response()->json([
                        'success' => True,
                        'message' => 'Check out successfully',
                        'data' => $todayCheckTime
                    ], 200);
                    // }
                }
            }
            $empCheckInn = Emp_attend::where('userId', $userAut->id)
                ->latest()
                ->first();
            if ($empCheckInn) {

                $empCheckIn = Emp_attend::where('userId', $userAut->id)
                    ->whereNotNull('checkOut')
                    ->latest()
                    ->first();
                if ($empCheckIn) {
                    $userSchedule = Time_schedule::where('id', $userAut->schedule)->first();
                    $check = Carbon::parse($userSchedule->late);

                    if ($today > $check) {


                        $data = [
                            'userId' => $userAut->id,
                            'checkIn' => $today,
                        ];
                        $saveCheckIn = Emp_attend::create($data);


                        User::where('id', $userAut->id)->update(['status' => 'Active']);
                        Emp_attend::where('userId', $userAut->id)->where('id', $saveCheckIn->id)->update(['late' => 'late']);

                        return response()->json([
                            'success' => True,
                            'message' => 'Check in successfully',
                            'data' => null,
                        ], 200);
                    } else {
                        $data = [
                            'userId' => $userAut->id,
                            'checkIn' => $today,
                        ];
                        $saveCheckIn = Emp_attend::create($data);

                        User::where('id', $userAut->id)->update(['status' => 'Active']);

                        return response()->json([
                            'success' => True,
                            'message' => 'Check in successfully',
                            'data' => null,
                        ], 200);
                    }
                }
            } else {
                $userSchedule = Time_schedule::where('id', $userAut->schedule)->first();
                $check = Carbon::parse($userSchedule->late);

                if ($today > $check) {


                    $data = [
                        'userId' => $userAut->id,
                        'checkIn' => $today,
                    ];
                    $saveCheckIn = Emp_attend::create($data);


                    User::where('id', $userAut->id)->update(['status' => 'Active']);
                    Emp_attend::where('userId', $userAut->id)->where('id', $saveCheckIn->id)->update(['late' => 'late']);

                    return response()->json([
                        'success' => True,
                        'message' => 'Check in successfully',
                        'data' => null,
                    ], 200);
                } else {
                    $data = [
                        'userId' => $userAut->id,
                        'checkIn' => $today,
                    ];
                    $saveCheckIn = Emp_attend::create($data);

                    User::where('id', $userAut->id)->update(['status' => 'Active']);

                    return response()->json([
                        'success' => True,
                        'message' => 'Check in successfully',
                        'data' => null,
                    ], 200);
                }
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'You are leaved today',
                'data' => null,
            ], 404);
        }
    }
    

//   function breakInOut(Request $request)
//     {

//         $userAut = auth()->user();

//         $today = Carbon::now('Asia/Karachi');
//         $userLeaves = Leave::where('userId', $userAut->id)->whereDate('dateFrom', '<=', $today)->whereDate('dateTo', '>=', $today)->first();

//         if (!$userLeaves) {

//             $empCheckOut = Emp_attend::where('userId', $userAut->id)
//                 ->whereNotNull('checkIn')
//                 // ->whereNotNull('breakIn')
//                 // ->whereNull('breakOut')
//                 ->whereNull('checkOut')
//                 ->latest()
//                 ->first();
//             $empCheckOutt = Emp_break::where('userId', $userAut->id)
//                 // ->whereNotNull('checkIn')
//                 ->whereNotNull('breakIn')
//                 ->whereNull('breakOut')
//                 // ->whereNull('checkOut')
//                 ->latest()
//                 ->first();
//             if ($empCheckOut && $empCheckOutt) {
//                 $checkOut = Emp_break::where('userId', $userAut->id)->where('breakOut', null)->update(['breakOut' => $today]);
//                 User::where('id', $userAut->id)->update(['status' => 'Active']);

//                 return response()->json([
//                     'success' => True,
//                     'message' => 'Break out successfully',
//                     'data' => null,
//                     'isBreakIn' => false,
//                 ], 200);
//             }
//             // else {
//                 // return response()->json([
//                 //     'success' => false,
//                 //     'message' => 'Checkin first',
//                 //     'data' => null,
//                 // ], 400);
//             // }

//             $empCheckIn = Emp_attend::where('userId', $userAut->id)
//                 ->whereNotNull('checkIn')
//                 ->whereNull('checkOut')
//                 // ->whereNull('breakIn')
//                 ->latest()
//                 ->first();
//             if ($empCheckIn) {

//                 $data = [
//                     'userId' => $userAut->id,
//                     'empAttendanceId' => $empCheckIn->id,
//                     'breakIn' => $today,
//                 ];
//                 // $breakIn = Emp_break::where('userId', $userAut->id)->where('breakIn', null)->update(['breakIn' => $today]);
//                 $breakIn = Emp_break::create($data);

//                 User::where('id', $userAut->id)->update(['status' => 'break']);
//                 Emp_attend::where('userId', $userAut->id)->where('id', $breakIn->empAttendanceId)->update(['isBreakIn' => 'true']);
//                 return response()->json([
//                     'success' => True,
//                     'message' => 'Break in successfully',
//                     'data' => null,
//                     'isBreakIn' => true,
//                 ], 200);
//             }
//         } else {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'You are leaved today',
//                 'data' => null,
//             ], 400);
//         }
//         return response()->json([
//             'success' => false,
//             'message' => 'Checkin first',
//             'data' => null,
//         ], 400);
//     }
    function todayLeaveOrCheckIn()
    {
        $users = User::get();
        $today = Carbon::now('Asia/Karachi')->format('Y-m-d');
        if ($users) {
            $usersWithAttends = DB::table('users')
                ->where('role', 'User')
                ->whereDate('checkIn', '=', $today)
                // ->whereDate('checkOut', '=', $today)
                // ->where('status', 'Active')
                ->leftJoin('emp_attends', 'users.id', '=', 'emp_attends.userId')
                ->select('users.id', 'users.name', 'users.status', 'emp_attends.checkIn as todayCheckIn', 'emp_attends.checkout as todayCheckOut')
                ->get();

            $UsersWithLeave = DB::table('users')
                // ->where('role', 'User')
                ->whereDate('dateFrom', '<=', $today)
                // ->whereDate('dateTo', '>=', $today)
                ->join('leaves', 'users.id', '=', 'leaves.userId')
                ->where('leaves.status', 'Approve')
                // ->select('users.id', 'users.name', 'users.status', 'leaves.dateTo')
                ->select('users.id', 'users.name', 'users.status')
                ->get();

            // if ($UsersWithLeave == null) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'No user leave at this time',
            //         'data' => null,
            //     ]);
            // }

            // if ($usersWithAttends ==  null) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'No user active or leave at this time',
            //         'data' => null,
            //     ]);
            // }




            return response()->json([
                'success' => true,
                'message' => 'Today leave and check in found',
                'data' => [
                    'activeUsers' => $usersWithAttends,
                    'usersOnLeave' => $UsersWithLeave
                ],
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => 'data not found',
            'data' => 'null',
        ]);
    }

//   public function getMonthlyData(Request $request, $id)
//     {
//         $current_dates = Carbon::now('Asia/Karachi');
//         $temp = explode('-', $current_dates);
//         $startDate = "$temp[0]-$temp[1]-01";


//         $userId = User::where('id', $id)->first();
//         if (!$userId) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'User not found',
//                 'data' => null
//             ]);
//         }

//         $validator = Validator::make($request->all(), [
//             'dateFrom' => ['required'],
//             'dateTo' =>  ['required'],
//         ]);

//         $dateFrom = $request->dateFrom;
//         $dateTo = $request->dateTo;

//         $from = Carbon::parse($request->dateFrom);
//         $to = Carbon::parse($request->dateTo)->addDay();
//         $dates = $from->diffInDays($to);

//         if ($request->has('dateFrom') && $request->has('dateTo')) {

//             $week_data = [];
//             $performance = [];

//             for ($day = 1; $day <= $dates; $day++) {
//                 $startDates = date('Y-m-d', strtotime("-{$day} day", strtotime($dateTo)));


//                 $attendance = Emp_attend::where('userId', $id)
//                     ->whereDate('checkIn', $startDates)
//                     ->get();
//                 $checkInOut = Emp_attend::where('userId', $id)
//                     ->whereBetween('checkIn', [$from, $to])
//                     ->get();

//                 $break = Emp_break::where('userId', $id)
//                     ->whereDate('breakIn', $startDates)
//                     ->get();
//                 $breakInOut = Emp_break::where('userId', $id)
//                     ->whereBetween('breakIn', [$from, $to])
//                     ->get();

//                 $week_data['userData'] = $userId;
//                 $week_data['checkInOut'] = $checkInOut;
//                 $week_data['breakInOut'] = $breakInOut;
//                 $week_data['checkLogs'] = $performance;

//                 if (count($attendance) == 0) {
//                     $userLeaves = Leave::where('userId', $id)->whereDate('dateFrom', '<=', $startDates)
//                         ->whereDate('dateTo', '>=', $startDates)->first();
//                     if ($userLeaves) {
//                         $status = 'Leave';
//                     } else {
//                         $status = 'Absent';
//                     }
//                 } else {
//                     $total = [];
//                     $totalBreak = [];

//                     for ($i = 0; $i < count($attendance); $i++) {

//                         if ($attendance[$i]->isBreakIn == true) {

//                             $checkInTime = Carbon::parse($attendance[$i]->checkIn);
//                             $checkOutTime = Carbon::parse($attendance[$i]->checkOut);

//                             $breaks = Emp_break::where('empAttendanceId', $attendance[$i]->id)->whereDate('created_at', '<=', $checkInTime)->get();
//                             array_push($totalBreak, $breaks);

//                             $totalBreakInTime = 0;

//                             foreach ($totalBreak[0] as $break) {
//                                 $breakInTime = Carbon::parse($break['breakIn']);
//                                 $breakOutTime = Carbon::parse($break['breakOut']);


//                                 $totalBreakInTime += $breakInTime->diffInMinutes($breakOutTime);
//                             }
//                             $hours = intdiv($totalBreakInTime, 60);
//                             $remainingMinutes = $totalBreakInTime % 60;
//                             $remainingSeconds =  $totalBreakInTime % 60;
//                             $status = sprintf('%02d:%02d:%02d', $hours, $remainingMinutes,  $remainingSeconds);

//                             $checkInTotalTime = $checkInTime->diffInMinutes($checkOutTime);

//                             $total[] = $checkInTotalTime - $totalBreakInTime;

//                             $sum = array_sum($total);

//                             $hours = intdiv($sum, 60);
//                             $remainingMinutes = $sum % 60;
//                             $remainingSeconds =  $sum % 60;
//                             $status = sprintf('%02d:%02d:%02d', $hours, $remainingMinutes,  $remainingSeconds);
//                         } else {

//                             $checkInTime = Carbon::parse($attendance[$i]->checkIn);
//                             $checkOutTime = Carbon::parse($attendance[$i]->checkOut);

//                             $totalTime = $checkInTime->diffInMinutes($checkOutTime);
//                             $total[$i] = $totalTime;
//                             $sum = array_sum($total);

//                             $hours = intdiv($sum, 60);
//                             $remainingMinutes = $sum % 60;
//                             $remainingSeconds =  $sum % 60;
//                             $status = sprintf('%02d:%02d:%02d', $hours, $remainingMinutes,  $remainingSeconds);
//                         }
//                     }
//                 }

//                 // $date = Carbon::parse($day);


//                 $performance[] =
//                     [
//                         // 'date' => $date->toDateString(),
//                         'date' => $startDates,
//                         'status' => $status,
//                     ];

//                 // $startDates->subDay();
//             }

//             return response()->json([
//                 'success' => true,
//                 'message' => 'Monthly Attendance',
//                 'data' => $week_data,
//             ]);
//         } else {

//             $week_data = [];
//             $performance = [];

//             for ($day = $startDate; $day <= $current_dates; $day++) {

//                 $attendance = Emp_attend::where('userId', $id)
//                     ->whereDate('checkIn', $day)
//                     ->get();
//                 $checkInOut = Emp_attend::where('userId', $id)
//                     ->whereBetween('checkIn', [$startDate, $current_dates])
//                     ->get();

//                 $break = Emp_break::where('userId', $id)
//                     ->whereDate('breakIn', $day)
//                     ->get();
//                 $breakInOut = Emp_break::where('userId', $id)
//                     ->whereBetween('breakIn', [$startDate, $current_dates])
//                     ->get();

//                 $week_data['userData'] = $userId;
//                 $week_data['checkInOut'] = $checkInOut;
//                 $week_data['breakInOut'] = $breakInOut;
//                 $week_data['checkLogs'] = $performance;

//                 if (count($attendance) == 0) {
//                     $userLeaves = Leave::where('userId', $id)->whereDate('dateFrom', '<=', $day)
//                         ->whereDate('dateTo', '>=', $day)->first();
//                     if ($userLeaves) {
//                         $status = 'Leave';
//                     } else {
//                         $status = 'Absent';
//                     }
//                 } else {
//                     $total = [];
//                     $totalBreak = [];

//                     for ($i = 0; $i < count($attendance); $i++) {

//                         if ($attendance[$i]->isBreakIn == true) {

//                             $checkInTime = Carbon::parse($attendance[$i]->checkIn);
//                             $checkOutTime = Carbon::parse($attendance[$i]->checkOut);

//                             $breaks = Emp_break::where('empAttendanceId', $attendance[$i]->id)->whereDate('created_at', '<=', $checkInTime)->get();
//                             array_push($totalBreak, $breaks);

//                             $totalBreakInTime = 0;

//                             foreach ($totalBreak[0] as $break) {
//                                 $breakInTime = Carbon::parse($break['breakIn']);
//                                 $breakOutTime = Carbon::parse($break['breakOut']);


//                                 $totalBreakInTime += $breakInTime->diffInMinutes($breakOutTime);
//                             }
//                             $hours = intdiv($totalBreakInTime, 60);
//                             $remainingMinutes = $totalBreakInTime % 60;
//                             $remainingSeconds =  $totalBreakInTime % 60;
//                             $status = sprintf('%02d:%02d:%02d', $hours, $remainingMinutes,  $remainingSeconds);

//                             $checkInTotalTime = $checkInTime->diffInMinutes($checkOutTime);

//                             $total[] = $checkInTotalTime - $totalBreakInTime;

//                             $sum = array_sum($total);

//                             $hours = intdiv($sum, 60);
//                             $remainingMinutes = $sum % 60;
//                             $remainingSeconds =  $sum % 60;
//                             $status = sprintf('%02d:%02d:%02d', $hours, $remainingMinutes,  $remainingSeconds);
//                         } else {

//                             $checkInTime = Carbon::parse($attendance[$i]->checkIn);
//                             $checkOutTime = Carbon::parse($attendance[$i]->checkOut);

//                             $totalTime = $checkInTime->diffInMinutes($checkOutTime);
//                             $total[$i] = $totalTime;
//                             $sum = array_sum($total);

//                             $hours = intdiv($sum, 60);
//                             $remainingMinutes = $sum % 60;
//                             $remainingSeconds =  $sum % 60;
//                             $status = sprintf('%02d:%02d:%02d', $hours, $remainingMinutes,  $remainingSeconds);
//                         }
//                     }
//                 }

//                 $date = Carbon::parse($day);

//                 $performance[] = [
//                     'date' => $date->toDateString(),
//                     'status' => $status,
//                 ];

//                 $date->subDay();
//             }

//             return response()->json([
//                 'success' => true,
//                 'message' => 'Monthly Attendance',
//                 'data' => $week_data,
//             ]);
//         }

//     }

      public function getMonthlyData(Request $request, $id)
    {

        $current_dates = Carbon::now('Asia/Karachi');
        $temp = explode('-', $current_dates);
        $startDate = "$temp[0]-$temp[1]-01";

        $userId = User::where('id', $id)->first();
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
                'data' => null
            ]);
        }

        $dateFrom = Carbon::parse($request->query('dateFrom'))->addDay();
        $dateTo = Carbon::parse($request->query('dateTo'))->addDay();

        $from = Carbon::parse($dateFrom);
        $to = Carbon::parse($dateTo);

        $dates = $from->diffInDays($to);

        if ($request->has('dateFrom') && $request->has('dateTo')) {

            $toDate = $dateTo->subDay()->toDateString();
            $current_date = Carbon::now('Asia/Karachi')->toDateString();

            if ($dateFrom > ($dateTo)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid date input. It should be later than dateFrom',
                    'data' => null
                ], 400);
            }
            if ($current_date < ($toDate)) {

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid date input.It should be equal or less than today',
                    'data' => null,
                ], 400);
            }

            $week_data = [];

            for ($day = $dates; $day >= 0; $day--) {

                $startDates = date('Y-m-d', strtotime("-{$day} day", strtotime($dateTo)));

                $attendance = Emp_attend::where('userId', $id)
                    ->whereDate('checkIn', $startDates)
                    ->get();

                $checkInOut = Emp_attend::where('userId', $id)
                    ->whereDate('checkIn', $startDates)
                    ->get();

                if (count($attendance) == 0) {
                    $userLeaves = Leave::where('userId', $id)->whereDate('dateFrom', '<=', $startDates)
                        ->whereDate('dateTo', '>=', $startDates)->first();
                    if ($userLeaves) {
                        $status = 'Leave';
                    } else {
                        $status = 'Absent';
                    }
                } else {
                    $total = [];

                    for ($i = 0; $i < count($attendance); $i++) {

                        $checkInTime = Carbon::parse($attendance[$i]->checkIn);
                        $checkOutTime = Carbon::parse($attendance[$i]->checkOut);

                        $totalTime = $checkInTime->diffInSeconds($checkOutTime);
                        $total[$i] = $totalTime;
                        $sum = array_sum($total);

                        // $hours = intdiv($sum, 60);
                        // $remainingMinutes = $sum % 60;
                        // $remainingSeconds =  $sum % 60;
                        // $status = sprintf('%02d:%02d:%02d', $hours, $remainingMinutes,  $remainingSeconds);

                        $todayhours = floor($sum / 3600);
                        $todayremainingMinutes = floor(($sum / 60) % 60);
                        $todayremainingSeconds = $sum % 60;

                        $status = sprintf('%02d:%02d:%02d', $todayhours, $todayremainingMinutes,  $todayremainingSeconds);
                    }
                }
                $date = Carbon::parse($day);

                $week_data[] = [
                    'name' => $userId->name,
                    'date' => $startDates,
                    'status' => $status,
                    'timeInOut' => $checkInOut
                ];
                // $date->subDay();
                // $day++;
            }

            return response()->json([
                'success' => true,
                'message' => 'Monthly Attendance',
                'data' => $week_data,
            ]);
        } else {

            // $week_data = [];

            // for ($day = $startDate; $day <= $current_dates; $day++) {
            //     $startDates = date('Y-m-d', strtotime("-{$day} day", strtotime($current_dates)));

            //     $attendance = Emp_attend::where('userId', $id)
            //         ->whereDate('checkIn', $day)
            //         ->get();
            //     $checkInOut = Emp_attend::where('userId', $id)
            //         ->whereDate('checkIn', $day)
            //         ->get();


            //     if (count($attendance) == 0) {
            //         $userLeaves = Leave::where('userId', $id)->whereDate('dateFrom', '<=', $day)
            //             ->whereDate('dateTo', '>=', $day)->first();
            //         if ($userLeaves) {
            //             $status = 'Leave';
            //         } else {
            //             $status = 'Absent';
            //         }
            //     } else {
            //         $total = [];

            //         for ($i = 0; $i < count($attendance); $i++) {

            //             $checkInTime = Carbon::parse($attendance[$i]->checkIn);
            //             $checkOutTime = Carbon::parse($attendance[$i]->checkOut);

            //             $totalTime = $checkInTime->diffInMinutes($checkOutTime);
            //             $total[$i] = $totalTime;
            //             $sum = array_sum($total);

            //             $hours = intdiv($sum, 60);
            //             $remainingMinutes = $sum % 60;
            //             $remainingSeconds =  $sum % 60;
            //             $status = sprintf('%02d:%02d:%02d', $hours, $remainingMinutes,  $remainingSeconds);
            //             $id = $attendance[$i]->id;
            //         }
            //     }
            //     $date = Carbon::parse($day);
            //     $week_data[] = [
            //         // 'id' => $id,
            //         'name' => $userId->name,
            //         'date' => $date->toDateString(),
            //         'status' => $status,
            //         'timeInOut' => $checkInOut
            //     ];

            //     $date->subDay();
            // }


            $week_data = [];

            for ($day = $startDate; $day <= $current_dates; $day++) {
                $startDates = date('Y-m-d', strtotime("-{$day} day", strtotime($current_dates)));

                $attendance = Emp_attend::where('userId', $id)
                    ->whereDate('checkIn', $day)
                    ->get();

                $status = '';
                $checkInOut = [];

                if (count($attendance) > 0) {
                    $total = 0;

                    foreach ($attendance as $attend) {
                        $checkInTime = Carbon::parse($attend->checkIn);
                        $checkOutTime = Carbon::parse($attend->checkOut);

                        $total += $checkInTime->diffInSeconds($checkOutTime);
                        $checkInOut[] = [
                            'id' => $attend->id,
                            // 'userId' => $attend->userId,
                            'checkIn' => $attend->checkIn,
                            'checkOut' => $attend->checkOut,
                            'late' => $attend->late,
                        ];
                    }

                    $todayhours = floor($total / 3600);
                    $todayremainingMinutes = floor(($total / 60) % 60);
                    $todayremainingSeconds = $total % 60;

                    $status = sprintf('%02d:%02d:%02d', $todayhours, $todayremainingMinutes,  $todayremainingSeconds);


                    // $hours = intdiv($total, 60);
                    // $remainingMinutes = $total % 60;
                    // $status = sprintf('%02d:%02d:%02d', $hours, $remainingMinutes, $remainingMinutes);
                } else {
                    // $status = 'Absent';
                    $userLeaves = Leave::where('userId', $id)->whereDate('dateFrom', '<=', $day)
                        ->whereDate('dateTo', '>=', $day)->first();
                    if ($userLeaves) {
                        $status = 'Leave';
                    } else {
                        $status = 'Absent';
                    }
                }

                $date = Carbon::parse($day);

                $week_data[] = [
                    'name' => $userId->name,
                    'date' => $date->toDateString(),
                    'status' => $status,
                    'timeInOut' => $checkInOut,
                ];

                $date->subDay();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Monthly Attendance',
            'data' => $week_data,
        ]);
    }

 function activeUser()
    {

        $users = User::get()->where('role', 'User');

        $today = Carbon::now('Asia/Karachi')->format('Y-m-d');
        if ($users) {
            $attendence = [];
            foreach ($users as $user) {
                $users = User::get()->where('role', 'User');

                $usersWithAttends = DB::table('emp_attends')
                    ->whereDate('checkIn', '=', $today)
                    ->where('userId', $user->id)
                    ->select('checkIn as todayCheckIn', 'checkout as todayCheckOut')
                    ->get();

                $attendence[] = [
                    'name' => $user->name,
                    'image' => $user->image,
                    'todayCheckInOut' => $usersWithAttends,
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Today check in users found',
                'data' => $attendence,


            ]);
        }
        return response()->json([
            'success' => false,
            'message' => 'data not found',
            'data' => 'null',
        ]);
    }
    
     public function updateCheckInOutTime(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'checkIn' => ['required_without_all:checkOut', 'date_format:"Y-m-d H:i:s"'],
            'checkOut' => ['required_without_all:checkIn', 'date_format:"Y-m-d H:i:s"'],

        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages(),
                'data' => null
            ], 400);
        }
        $empAttendExist = Emp_attend::where('id', $id)->first();

        if ($empAttendExist == null) {
            return response()->json([
                'success' => false,
                'message' => 'Employee attendance does not exist',
                'data' => null,

            ], 404);
        }

        DB::beginTransaction();
        try {
            // print_r($request->checkIn);
            // die;
            $empAttendExist->checkIn = $request->checkIn ?? $empAttendExist->checkIn;
            $empAttendExist->checkOut = $request->checkOut ?? $empAttendExist->checkOut;
            $empAttendExist->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            $users = null;
        }

        return response()->json(
            [
                'success' => true,
                'message' => 'Employee attendance time updated successfully',
                'data' => null
            ],
            200
        );
    }

}

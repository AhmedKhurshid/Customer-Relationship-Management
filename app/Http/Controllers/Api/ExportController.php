<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Emp_attend;
use App\Models\Leave;
use App\Models\User;
use Carbon\Carbon;
use App\Exports\UserExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
// use App\Models\Patientlogs;
use Illuminate\Support\Facades\Validator;
use Response;



class ExportController extends Controller
{
     public function getCheckInCSV(Request $request, $data)
    {
        $current_dates = Carbon::now('Asia/Karachi');
        $temp = explode('-', $current_dates);
        $startDate = "$temp[0]-$temp[1]-01";

        $userId = User::where('id', $data)->first();

        $dateFrom = Carbon::parse($request->query('dateFrom'))->addDay();
        $dateTo = Carbon::parse($request->query('dateTo'))->addDay();

        $from = Carbon::parse($dateFrom);
        $to = Carbon::parse($dateTo);

        $dates = $from->diffInDays($to);

        $toDate = $dateTo->subDay()->toDateString();
        $current_date = Carbon::now('Asia/Karachi')->toDateString();

        $week_data = [];
        $totalHours = 0;
        $totalHoursPerWeek = '';

        for ($day = $dates; $day >= 0; $day--) {
            $startDates = date('Y-m-d', strtotime("-{$day} day", strtotime($dateTo)));

            $attendance = Emp_attend::where('userId', $data)
                ->whereDate('checkIn', $startDates)
                ->get();

            $checkIn = []; // Initialize checkIn array
            $checkOut = []; // Initialize checkOut array

            if (count($attendance) == 0) {
                $userLeaves = Leave::where('userId', $data)
                    ->whereDate('dateFrom', '<=', $startDates)
                    ->whereDate('dateTo', '>=', $startDates)
                    ->first();
                if ($userLeaves) {
                    $status = 'Leave';
                } else {
                    $status = 'Absent';
                }
            } else {
                $totall = 0;
                $checkInCheckOutDiff = 0;
                foreach ($attendance as $attend) {
                    $checkInTime = Carbon::parse($attend->checkIn);
                    $checkOutTime = Carbon::parse($attend->checkOut);

                    $checkIn[] = $checkInTime->toTimeString();
                    $checkOut[] = $checkOutTime->toTimeString();

                    $checkInCheckOutDiff += $checkInTime->diffInSeconds($checkOutTime);
                    $totall += $checkInTime->diffInSeconds($checkOutTime);

                    $todayhours = floor($checkInCheckOutDiff / 3600);
                    $todayremainingMinutes = floor(($checkInCheckOutDiff / 60) % 60);
                    $todayremainingSeconds = $checkInCheckOutDiff % 60;

                    $status = sprintf('%02d:%02d:%02d', $todayhours, $todayremainingMinutes, $todayremainingSeconds);
                }
                $totalHours += $totall; // Accumulate total hours

                $todayhour = floor($totalHours / 3600);
                $todayremainingMinute = floor(($totalHours / 60) % 60);
                $todayremainingSecond = $totalHours % 60;

                $totalHoursPerWeek = sprintf('%02d:%02d:%02d', $todayhour, $todayremainingMinute,  $todayremainingSecond);
            }

            $date = Carbon::parse($day);

            $week_data[] = [
                'name' => $day === $dates ? $userId->name : '',
                'date' => $startDates,
                'checkIn' => $checkIn,
                'checkOut' => $checkOut,
                'status' => $status,
                'totalTime' => $totalHoursPerWeek,
            ];
        }

       
        return Excel::download(new UserExport(['data' => $week_data]), $userId->employeeId . '_' . $userId->name . '_' . 'timeInOut.xlsx');


    }

}

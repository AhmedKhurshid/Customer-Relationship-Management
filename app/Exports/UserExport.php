<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Support\Facades\DB;
use App\Models\Emp_attend;
use App\Models\Leave;
use Maatwebsite\Excel\Facades\Excel;

use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;

class UserExport implements FromArray, WithCustomCsvSettings, WithHeadings, ShouldAutoSize, WithStyles
{

// class UserExport implements FromCollection, WithCustomCsvSettings, WithHeadings,ShouldAutoSize, WithStyles
// {
    /**
    * @return \Illuminate\Support\Collection
    */

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ','
        ];
    }

    public function headings(): array
    {
        return ["Name", "Date","Time In "," Time Out", "Day total hours ", "Total Hours"];
    }

    public function styles(Worksheet $sheet)
    {
        // Apply bold styling to the column headers
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);
    }

    public function columnFormats(): array
    {
        // Define the column sizes
        return [
            'A' => 40, // Column A width is set to 15
            'B' => 40, // Column B width is set to 30
            'C' => 40, // Column C width is set to 40
            'D' => 40, // Column A width is set to 15
            'E' => 40, // Column B width is set to 30
            'F' => 40, // Column B width is set to 30
            // Add more columns and their sizes as needed
        ];
    }
    public function __construct($data)
    {
        $this->data = $data;
    }
    public function array(): array
    {
        return $this->data; // Assuming $data is an array
    }


    public function collection()
{
    return collect($week_data);
}

    // public function collection()
    // {

    //     $current_dates = Carbon::now('Asia/Karachi');
    //     $temp = explode('-', $current_dates);
    //     $startDate = "$temp[0]-$temp[1]-01";

    //     $userId = User::where('id', $this->data)->first();


    //     $dateFrom = Carbon::parse('2023-12-23');
    //     $dateTo = Carbon::parse('2023-12-28');

    //     // $from = Carbon::parse($dateFrom);
    //     // $to = Carbon::parse($dateTo);
    //     $from = Carbon::parse('2023-12-23');
    //     $to = Carbon::parse('2023-12-28');

    //     $dates = $from->diffInDays($to);

    //     $toDate = $dateTo->subDay()->toDateString();
    //     $current_date = Carbon::now('Asia/Karachi')->toDateString();

    //     $week_data = [];

    //     for ($day = $dates; $day >= 0; $day--) {

    //         $startDates = date('Y-m-d', strtotime("-{$day} day", strtotime($dateTo)));

    //         $attendance = Emp_attend::where('userId', $this->data)
    //             ->whereDate('checkIn', $startDates)
    //             ->get();

    //         $checkInOut = Emp_attend::where('userId', $this->data)
    //             ->whereDate('checkIn', $startDates)
    //             ->get();

    //         if (count($attendance) == 0) {
    //             $userLeaves = Leave::where('userId', $this->data)->whereDate('dateFrom', '<=', $startDates)
    //                 ->whereDate('dateTo', '>=', $startDates)->first();
    //             if ($userLeaves) {
    //                 $status = 'Leave';
    //             } else {
    //                 $status = 'Absent';
    //             }
    //         } else {
    //             $total = [];

    //             for ($i = 0; $i < count($attendance); $i++) {

    //                 $checkInTime = Carbon::parse($attendance[$i]->checkIn);
    //                 $checkOutTime = Carbon::parse($attendance[$i]->checkOut);

    //                 $totalTime = $checkInTime->diffInSeconds($checkOutTime);
    //                 $total[$i] = $totalTime;
    //                 $sum = array_sum($total);


    //                 $todayhours = floor($sum / 3600);
    //                 $todayremainingMinutes = floor(($sum / 60) % 60);
    //                 $todayremainingSeconds = $sum % 60;

    //                 $status = sprintf('%02d:%02d:%02d', $todayhours, $todayremainingMinutes,  $todayremainingSeconds);
    //             }
    //         }
    //         $date = Carbon::parse($day);

    //         $week_data[] = [
    //             'name' => $userId->name,
    //             'date' => $startDates,
    //             'status' => $status,
    //             'timeInOut' => $checkInOut
    //         ];
    //         // $date->subDay();
    //         // $day++;
    //     }

    //     return $week_data;


    //     // return User::all();

    //     // return DB::table('patients')
    //     // ->join('users', 'patients.userId', '=', 'users.id')
    //     // ->where('patients.vendorId', '=', $this->data)
    //     // ->select('users.name',
    //     // 'users.email',
    //     // 'users.number',
    //     // 'users.address',
    //     // 'patients.patientStatus')
    //     // ->get();
    // }


}

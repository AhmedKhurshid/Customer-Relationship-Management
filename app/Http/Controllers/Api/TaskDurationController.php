<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Task_duration;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Mail;

class TaskDurationController extends Controller
{
    function taskDuration(Request $request, $id)
    {

        $userAut = auth()->user();
        $userId = Task::where('id', $id)->where('userId', $userAut->id)->first();
        $today = Carbon::now('Asia/Karachi');

        if ($userId) {

            $inProgress = Task::where('id', $userId->id)->where('status', 'TO DO')->first();

            if ($inProgress) {

                DB::beginTransaction();
                $data = [
                    'taskId' => $userId->id,
                    'startTime' => $today,

                ];
                try {
                    $users = Task_duration::create($data);
                    Task::where('id', $userId->id)->update(['status' => 'IN PROGRESS']);
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
                            'message' => 'Task IN PROGRESS',
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

            $notInQA = Task_duration::where('taskId', $userId->id)->whereNotNull('startTime')->first();
            if ($notInQA) {

                $alreadyInQA = Task::where('status', 'IN PROGRESS')->where('id', $userId->id)->first();
                if ($alreadyInQA) {

                    $taskEnd = Task_duration::where('taskId', $userId->id)->whereNotNull('startTime')->first();
                    $adminId = User::where('id', $alreadyInQA->adminId)->first();
                    if ($taskEnd && $adminId) {

                        Task::where('id', $userId->id)->update(['status' => 'QA']);
                        $completed = Task_duration::where('taskId', $userId->id)->whereNull('endTime')->update(['endTime' => $today]);

                        $otpcode =  $alreadyInQA->title . " " . 'Task completed successfully';
                        DB::beginTransaction();
                        // try {
                        $data['otpcode'] = $otpcode;
                        $data['email'] = $adminId->email;
                        $data['title'] = $alreadyInQA->title;
                        $data['body'] = $alreadyInQA->desc;
                        Mail::send('email', ['data' => $data], function ($message) use ($data) {
                            $message->from('codeaugment@gmail.com');
                            $message->to($data['email']);
                            $message->subject($data['title']);
                        });

                        $no_of_hours = DB::table('task_durations')
                            ->where('taskId', $userId->id)
                            ->selectRaw('TIME(SUM(TIMEDIFF(endTime, startTime))) as totalHours')
                            ->get();

                        return response()->json([
                            'success' => True,
                            'message' => 'Task in QA',
                            'data' => $no_of_hours
                        ], 200);
                    } else {
                        return response()->json(
                            [
                                'success' => false,
                                'message' => 'Data not found',
                                'data' => null,

                            ],
                            429
                        );
                    }
                } else {
                    return response()->json(
                        [
                            'success' => false,
                            'message' => 'This task already in QA ',
                            'data' => null,

                        ],
                        429
                    );
                }
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Task not found',
                'data' => null,
            ], 404);
        }
    }
}

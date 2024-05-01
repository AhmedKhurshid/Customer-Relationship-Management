<?php

namespace App\Http\Controllers\Api;

use App\Enums\TaskPriorityEnum;
use App\Enums\TaskStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Task_duration;
use App\Models\Task_notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;
use Mail;
use Carbon\Carbon;

class TaskController extends Controller
{
    function createTask(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make(
            $request->all(),
            [
                'title' => ['required', 'string', 'min:3', 'max:30'],
                'desc' => ['required', 'string', 'min:10'],
                'userId' => ['required', 'integer'],
                // 'priority' => ['required', 'string'],
                'priority' => [new Enum(TaskPriorityEnum::class)],
                'taskStartFrom' => ['required', 'date'],
                'taskEndTo' => ['required', 'date'],


            ]
        );
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages(),
                'data' => null
            ], 400);
        }
        $userExists = User::where('id', $request->userId)->first();

        if (!$userExists) {
            return response()->json(
                [
                    'message' => 'User not exist',
                    'success' => false,
                    'data' => null,

                ],
                500
            );
        }

        $yesterday = Carbon::yesterday('Asia/Karachi');
        if ($request->taskStartFrom <= ($yesterday)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid date input. It should be later than today.',
                'data' => null
            ], 400);
        }
        if ($request->taskStartFrom > $request->taskEndTo) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid date input. It should be later than start date.',
                'data' => null
            ], 400);
        }

        DB::beginTransaction();
        $data = [
            'adminId' => $user->id,
            'userId' => $request->userId,
            'title' => $request->title,
            'desc' => $request->desc,
            'priority' => $request->priority,
            'taskStartFrom' => $request->taskStartFrom,
            'taskEndTo' => $request->taskEndTo,
        ];
        $data1 = [
            'title' => $request->title,
            'desc' => $request->desc,
            'userId' => $request->userId,
        ];

        try {
            $taskNotification = Task_notification::create($data1);
            $users = Task::create($data);
            DB::commit();

            $userId = User::where('id', $users->taskId)->first();

            $otpcode =  $user->name . " " . 'created an issue.';
            DB::beginTransaction();
            // try {
            $data['otpcode'] = $otpcode;
            $data['email'] = $userExists->email;
            $data['title'] = $users->title;
            $data['body'] = $users->desc;
            Mail::send('email', ['data' => $data], function ($message) use ($data) {
                $message->from('rehaabkhan001@gmail.com');
                $message->to($data['email']);
                $message->subject($data['title']);
            });
        } catch (\Throwable $e) {
            DB::rollback();
            echo $e->getMessage();
            $users = null;
        }
        if ($users != null) {
            return response()->json(
                [
                    'success' => true,
                    'message' => 'Task assigned successfully',
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

    function singleUserTaskDuartion(Request $request, $id)
    {
        $perPage = $request->input('perPage', 10);
        $userId = User::where('id', $id)->first();

        if ($userId) {

            $tasksWithTotalHours = DB::table('tasks')
                ->select('tasks.id', 'tasks.title', 'tasks.desc', 'tasks.adminId', 'tasks.userId', 'tasks.status')
                ->selectRaw('TIME(SUM(TIMEDIFF(task_durations.endTime, task_durations.startTime))) as totalHours')
                ->leftJoin('task_durations', 'tasks.id', '=', 'task_durations.taskId')
                // ->where('tasks.status', 'QA')
                ->where('tasks.userId', $userId->id)
                ->groupBy('tasks.id', 'tasks.title', 'tasks.desc', 'tasks.adminId', 'tasks.userId', 'tasks.status')
                ->paginate($perPage);

            // $alltasks = DB::table('tasks')
            //     ->select('tasks.id', 'tasks.title', 'tasks.desc', 'tasks.adminId', 'tasks.userId', 'tasks.status')
            //     ->whereIn('tasks.status', ['IN PROGRESS', 'DONE', 'TO DO'])
            //     ->where('tasks.userId', $id)
            //     ->paginate($perPage);

            // if (!$alltasks || !$tasksWithTotalHours) {
            if (!$tasksWithTotalHours) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data not found',
                    'data' => null
                ], 404);
            }
            // $response =
            //     [
            //         'tasksWithTotalHours' => $tasksWithTotalHours,
            //         'alltasks' => $alltasks
            //     ];

            return response()->json([
                'success' => true,
                'message' => 'Data found',
                'data' =>  [
                    'tasksWithTotalHours' => $tasksWithTotalHours,
                    // 'alltasks' => $alltasks
                ]
            ], 200);
        }
        return response()->json([
            'success' => false,
            'message' => 'User not found',
            'data' => null
        ], 404);
    }

    function showTask(Request $request)
    {
        $userAut = auth()->user();
        $role = $userAut->role;
        $perPage = $request->input('perPage', 10);

        if ($userAut->role == 'User') {
            $userId = Task::where('userId', $userAut->id)->first();

            // $userFound = Task::where('userId', $userAut->id)->paginate($perPage);
            $userFound = Task::where('userId', $userAut->id)->paginate($perPage);

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => "Task doesn't exist",
                    'data' => null,
                ], 404);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'Task found',
                    'data' => $userFound,
                ], 200);
            }
        }

        $taskEmpty = task::first();
        if (!$taskEmpty) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found',
                'data' => null
            ], 404);
        }

        $search = $request->input('search');

        if ($search) {

            $users = Task::leftJoin('users', 'tasks.userId', '=', 'users.id')
                ->where(function ($query) use ($search) {
                    $query->where('tasks.status', 'LIKE', "%$search%")
                        ->orWhere('tasks.title', 'LIKE', "%$search%")
                        ->orWhere('tasks.desc', 'LIKE', "%$search%")
                        ->orWhere('tasks.priority', 'LIKE', "%$search%")
                        ->orWhere('tasks.taskStartFrom', 'LIKE', "%$search%")
                        ->orWhere('tasks.taskEndTo', 'LIKE', "%$search%")
                        ->orWhere('users.name', 'LIKE', "%$search%")
                        ->orWhere('users.email', 'LIKE', "%$search%");
                })
                ->select('tasks.*')
                ->paginate($perPage);
            return response()->json([
                'success' => true,
                'message' => 'Data found',
                'data' => $users
            ], 200);
        }

        if (!$request->status) {

            // $todoID = Task::paginate($perPage);
            // $todoID = DB::table('users')
            // ->join('tasks', 'users.id', '=', 'tasks.userId')
            // ->select('users.id', 'users.name', 'users.email', 'tasks.title', 'tasks.desc', 'tasks.priority', 'tasks.taskStartFrom','tasks.taskEndTo','tasks.adminId','tasks.userId','tasks.status','tasks.created_at')
            // ->paginate($perPage);
            
             $todoID = DB::table('users')
            ->join('tasks', 'users.id', '=', 'tasks.userId')
            ->select('tasks.id as taskId', 'users.name', 'users.email', 'tasks.title', 'tasks.desc', 'tasks.priority', 'tasks.taskStartFrom','tasks.taskEndTo','tasks.adminId','tasks.userId','tasks.status','tasks.created_at')
            ->paginate($perPage);


            return response()->json([
                'success' => true,
                'message' => "Task found",
                'data' => $todoID,
            ], 200);
        }

        $users = Task::where('status', $request->status)->get();
        $statusNotEmpty = $request->status;
        if ($statusNotEmpty) {

            if ($request->status == 'IN PROGRESS') {
                $progressID = Task::where('status', 'IN PROGRESS')->first();
                // $progress = Task::where('status', 'IN PROGRESS')->paginate($perPage);
                $progress = DB::table('tasks')
                ->where('tasks.status', 'IN PROGRESS')
                ->join('users', 'tasks.userId', '=', 'users.id')
                ->select('users.id', 'users.name', 'users.email', 'tasks.title', 'tasks.desc', 'tasks.priority', 'tasks.taskStartFrom','tasks.taskEndTo','tasks.adminId','tasks.userId','tasks.status','tasks.created_at')
                ->paginate($perPage);


                if (!$progressID) { {
                        return response()->json([
                            'success' => false,
                            'message' => "Task doesn't exist",
                            'data' => null,
                        ], 404);
                    }
                } else {
                    return response()->json([
                        'success' => true,
                        'message' => 'Task found',
                        'data' => $progress,
                    ], 200);
                }
            }

            if ($request->status == 'QA') {
                $qaID = Task::where('status', 'QA')->first();

                $tasksWithTotalHours = DB::table('tasks')
                ->join('users','tasks.userId', '=','users.id')
                    ->select('tasks.id', 'tasks.title', 'tasks.desc', 'tasks.adminId', 'tasks.userId', 'tasks.status','users.name','users.email')
                    ->selectRaw('TIME(SUM(TIMEDIFF(task_durations.endTime, task_durations.startTime))) as totalHours')
                    ->leftJoin('task_durations', 'tasks.id', '=', 'task_durations.taskId')
                    ->where('tasks.status', 'QA')
                    ->groupBy('tasks.id', 'tasks.title', 'tasks.desc', 'tasks.adminId', 'tasks.userId', 'tasks.status','users.name','users.email')
                    ->paginate($perPage);

                if (!$qaID) { {
                        return response()->json([
                            'success' => false,
                            'message' => "Task doesn't exist",
                            'data' => null,
                        ], 404);
                    }
                } else {
                    return response()->json([
                        'success' => true,
                        'message' => 'Task found',
                        'data' => $tasksWithTotalHours,
                    ], 200);
                }
            }

            if ($request->status == 'DONE') {
                $doneID = Task::where('status', 'DONE')->first();
                // $done = Task::where('status', 'DONE')->paginate($perPage);
                $done = DB::table('tasks')
                ->where('tasks.status', 'DONE')
                ->join('users', 'tasks.userId', '=', 'users.id')
                ->select('users.id', 'users.name', 'users.email', 'tasks.title', 'tasks.desc', 'tasks.priority', 'tasks.taskStartFrom','tasks.taskEndTo','tasks.adminId','tasks.userId','tasks.status','tasks.created_at')
                ->paginate($perPage);


                if (!$doneID) { {
                        return response()->json([
                            'success' => false,
                            'message' => "Task doesn't exist",
                            'data' => null,
                        ], 404);
                    }
                } else {
                    return response()->json([
                        'success' => true,
                        'message' => 'Task found',
                        'data' => $done,
                    ], 200);
                }
            }
            if ($request->status == 'TO DO') {


                $todosID = Task::where('status', 'TO DO')->first();
                // $todos = Task::where('status', 'TO DO')->paginate($perPage);
                $todos = DB::table('tasks')
            ->where('tasks.status', 'TO DO')
            ->join('users', 'tasks.userId', '=', 'users.id')
            ->select('users.id', 'users.name', 'users.email', 'tasks.title', 'tasks.desc', 'tasks.priority', 'tasks.taskStartFrom','tasks.taskEndTo','tasks.adminId','tasks.userId','tasks.status','tasks.created_at')
            ->paginate($perPage);


                if (!$todosID) { {
                        return response()->json([
                            'success' => false,
                            'message' => "Task doesn't exist",
                            'data' => null,
                        ], 404);
                    }
                } else {
                    return response()->json([
                        'success' => true,
                        'message' => 'Task found',
                        'data' => $todos,
                    ], 200);
                }
            }
        }
    }

    function showSingleTask($id)
    {
        $userAut = auth()->user();

        if ($userAut->role == 'User') {

            // $userFound = Task::where('userId', $userAut->id)->where('id', $id)->first();
            $userFound = DB::table('tasks')
            ->where('tasks.userId', $userAut->id)
            ->where('tasks.id', $id)
            ->join('users', 'tasks.userId', '=', 'users.id')
            ->select('users.id', 'users.name', 'users.email', 'tasks.title', 'tasks.desc', 'tasks.priority', 'tasks.taskStartFrom','tasks.taskEndTo','tasks.adminId','tasks.userId','tasks.status','tasks.created_at')
            ->first();

            if (!$userFound) {
                return response()->json([
                    'success' => false,
                    'message' => "Task doesn't exist",
                    'data' => null,
                ], 404);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'Task found',
                    'data' => $userFound,
                ], 200);
            }
        }

        // $taskEmpty = Task::where('id', $id)->first();
        $taskEmpty = DB::table('tasks')
            ->where('tasks.id', $id)
            ->join('users', 'tasks.userId', '=', 'users.id')
            ->select('tasks.id','users.id', 'users.name', 'users.email', 'tasks.title', 'tasks.desc', 'tasks.priority', 'tasks.taskStartFrom','tasks.taskEndTo','tasks.adminId','tasks.userId','tasks.status','tasks.created_at')
            ->first();
        if (!$taskEmpty) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found',
                'data' => null
            ], 404);
        }
        return response()->json([
            'success' => true,
            'message' => 'Data found',
            'data' => $taskEmpty
        ], 200);
    }

    public function updateStatus(Request $request, $id)
    {
        $userAut = auth()->user();
        $validator = Validator::make($request->all(), [
            // 'status' => ['required', 'string'],
            'status' => [new Enum(TaskStatusEnum::class)],
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
        $userId = Task::where('id', $id)->first();

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => "Task doesn't exist ",
                'data' => null
            ], 404,);
        }

        $userId->status = $request->status;
        $userId->save();
        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'data' => $userId,
        ], 200,);
    }

    public function updateTask(Request $request, $id)
    {
        $userAut = auth()->user();
        $validator = Validator::make($request->all(), [
            'title' => ['required_without_all:desc,userId,status,priority,taskStartFrom,taskEndTo', 'string'],
            'desc' =>  ['required_without_all:title,userId,status,priority,taskStartFrom,taskEndTo', 'string'],
            'userId' => ['required_without_all:desc,title,status,priority,taskStartFrom,taskEndTo', 'integer'],
            'priority' => [new Enum(TaskPriorityEnum::class)],
            'taskStartFrom' => ['required_without_all:desc,userId,status,priority,title,taskEndTo', 'date'],
            'taskEndTo' => ['required_without_all:desc,userId,status,priority,taskStartFrom,title', 'date'],
            // 'status' => ['required_without_all:title,desc,userId', 'string'],
            'status' => [new Enum(TaskStatusEnum::class)],
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

        $yesterday = Carbon::yesterday('Asia/Karachi');
        // if ($request->taskStartFrom <= ($yesterday)) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Invalid date input. It should be later than today.',
        //         'data' => null
        //     ], 400);
        // }
        if ($request->taskStartFrom > $request->taskEndTo) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid date input. It should be later than start date.',
                'data' => null
            ], 400);
        }


        // if ($request->taskStartFrom > $request->taskEndTo) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Invalid date input. It should be later than start date.',
        //         'data' => null
        //     ], 400);
        // }
        // $yesterday = Carbon::yesterday('Asia/Karachi');
        // if ($request->taskStartFrom <= ($yesterday)) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Invalid date input. It should be later than today.',
        //         'data' => null
        //     ], 400);
        // }

        $status = Task::find($id);
        if ($status == null) {
            return response()->json([
                'success' => false,
                'message' => "Task doesn't exist",
                'data' => null
            ], 200,);
        }

        $status->title = $request->title;
        $status->desc = $request->desc;
        $status->userId = $request->userId;
        $status->status = $request->status;
        $status->priority = $request->priority;
        $status->taskStartFrom = $request->taskStartFrom;
        $status->taskEndTo = $request->taskEndTo;

        $status->save();
        return response()->json([
            'success' => true,
            'message' => 'Data updated successfully',
            'data' => $status,
        ], 200,);
    }

    public function deleteTask(string $id)
    {
        $schedule = Task::find($id);
        if (is_null($schedule)) {
            return response()->json([
                'success' => false,
                'message' => "Task doesn't exist",
                'data' => null
            ], 200,);
        } else {

            $schedule->delete();
            return response()->json([
                'success' => true,
                'message' => 'Task deleted successfully',
                'data' => null,
            ], 200,);
        }
    }
}

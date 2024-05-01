<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task_notification;
use App\Models\User;
use Illuminate\Http\Request;

class TaskNotificationController extends Controller
{
    function showTaskNotification()
    {
        $auth = auth()->user();
        $user = User::where('id', $auth->id)->first();
        if ($user) {

            $notifications = Task_notification::where('userId', $user->id)->paginate(10);

            if ($notifications == null) {
                return response()->json([
                    'success' => false,
                    'message' => "Notification doesn't exist",
                    'data' => null,
                ], 404);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'Notifications found',
                    'data' => $notifications,
                ], 200);
            }
        }
        return response()->json([
            'success' => false,
            'message' => 'Data not found',
            'data' => null,
        ], 200);
    }
}

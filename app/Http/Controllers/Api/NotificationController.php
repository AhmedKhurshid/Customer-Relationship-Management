<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\PushNotificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    function notification(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make(
            $request->all(),
            [
                'title' => ['required', 'min:6',],
                'message' => ['required', 'min:10'],

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
        $dataCreate = [
            'title' => $request->title,
            'message' => $request->message,
            'adminId' => $user->id,
        ];
        $tokens = User::where('role', 'User')->pluck('deviceToken');

        // $tokens = $getToken->
        $data = [
            'title' => $request->title,
            'message' => $request->message,
        ];
        try {
            $controller = (new PushNotificationController)->sendFirebasePush($tokens, $data);

            // $controller = new PushNotificationController;
            // $method = $this->sendFirebasePush($tokens,$data);

            $users = Notification::create($dataCreate);
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
                    'message' => 'Notification created successfully',
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
    function showNotification(Request $request)
    {
        $notifications = Notification::paginate(10);

        if (is_null($notifications)) {
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
}

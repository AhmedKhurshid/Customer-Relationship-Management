<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ContactUsController extends Controller
{
    function createMessage(Request $request)
    {
        $auth = Auth::user();
        $validator = Validator::make(
            $request->all(),
            [
                'title' => ['required', 'string'],
                'message' => ['required', 'string'],
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
            'title' => $request->title,
            'message' => $request->message,
            'userId' => $auth->id,
        ];
        try {
            $contact = Contact::create($data);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            echo $e->getMessage();
            $contact = null;
        }

        if ($contact != null) {
            return response()->json([
                'success' => true,
                'message' => 'Form submitted successfully',
                'data' => $contact
            ]);
        }
    }

    function showMessageToUser()
    {
        $auth = Auth::user();
        $user = Contact::where('userId', $auth->id)->get();

        if ($user) {
            return response()->json([
                'success' => true,
                'message' => 'Message found',
                'data' => $user,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Message not found',
            'data' => null,
        ]);
    }

    function showMessageToAdmin()
    {
        $user = Contact::all();

        if ($user) {
            return response()->json([
                'success' => true,
                'message' => 'Message found',
                'data' => $user,
            ]);
        }
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Message not found',
                'data' => null,
            ]);
        }
    }

    // function updateMessage($id)
    // {
    //     $auth = Auth::user();
    //     $contact = Contact::where('userId', $id)->first();
    //     if ($contact) {

    //     }
    // }

}

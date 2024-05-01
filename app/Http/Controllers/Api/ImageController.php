<?php

namespace App\Http\Controllers\Api;

use App\Enums\ImageStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use File;
use App\Models\Emp_attend;
use Carbon\Carbon;

use Illuminate\Validation\Rules\Enum;

class ImageController extends Controller
{
    function changeImage(Request $request)
    {
        $auth = auth()->user();
        $validator = Validator::make($request->all(), [
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages(),
                'data' => null
            ], 400);
        }
        $user = User::where('id', $auth->id)->first();

        if ($user ==  null) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
                'data' => null,
            ], 404);
        }

        if ($request->hasFile('image')) {

            $filename = time() . $request->file('image')->getClientOriginalName();
            $imageURL = $request->file('image')->storeAs('image', $filename, 'public');
            $newImageUrl = ('storage/' . $imageURL);

            $imagePath = $user->image;

            if ($imagePath) {
                File::delete($imagePath);
            }

            $user->image = $newImageUrl;

            $user->imageStatus = 'Pending';
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Image request has been sent to Admin',
                'data' => null,
            ], 200);
        }
    }

    function imageRequests()
    {

        $getImageRequest = User::where('imageStatus', 'Pending')->get();

        if ($getImageRequest->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Image request not found',
                'data' => null,
            ], 404);
        }
        return response()->json([
            'success' => true,
            'message' => 'Image request found',
            'data' => $getImageRequest,
        ], 200);
    }

    function getSingleImageRequest($id)
    {
        $getImageRequest = User::where('imageStatus', 'Pending')->where('id', $id)->first();

        if ($getImageRequest == null) {
            return response()->json([
                'success' => false,
                'message' => 'Image request not found',
                'data' => null,
            ], 404);
        }
        return response()->json([
            'success' => true,
            'message' => 'Image request found',
            'data' => $getImageRequest,
        ], 200);
    }


    function imageApproveReject(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'imageStatus' => [new Enum(ImageStatusEnum::class)],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages(),
                'data' => null
            ], 400);
        }

        $user = User::where('id', $id)->whereNotNull('imageStatus')->first();

        if ($user ==  null) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
                'data' => null,
            ], 404);
        }
        if ($request->imageStatus == 'Reject') {

            $imagePath = $user->image;

            if ($imagePath) {
                File::delete($imagePath);
            }
            $user->imageStatus = 'Reject';
            $user->image = null;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Request rejected successfully',
                'data' => null,
            ], 200);
        }

        $user->imageStatus = 'Approve';
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Request approved successfully',
            'data' => null,
        ], 200);
    }


    
}

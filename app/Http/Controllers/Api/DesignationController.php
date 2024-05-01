<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Designation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DesignationController extends Controller
{
    function createDesignation(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'designation' => ['required', 'string'],

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
            'designation' => $request->designation,
        ];
        try {
            $users = Designation::create($data);
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
                    'message' => 'Designation created successfully',
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

    function showDesignation()
    {
        $designation = Designation::all();

        if ($designation == null) { {
                return response()->json([
                    'success' => false,
                    'message' => "Designation doesn't exist",
                    'data' => null,
                ], 404);
            }
        } else {
            return response()->json([
                'success' => true,
                'message' => 'Designation found',
                'data' => $designation,
            ], 200);
        }
    }
    
    function showSingleDesignation($id)
    {
        $designation = Designation::where('id',$id)->first();

        if ($designation == null) {
                return response()->json([
                    'success' => false,
                    'message' => "Designation doesn't exist",
                    'data' => null,
                ], 404);

        } else {
            return response()->json([
                'success' => true,
                'message' => 'Designation found',
                'data' => $designation,
            ], 200);
        }
    }

    public function updateDesignation(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'designation' => ['required'],
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
        $designation = Designation::find($id);

        if ($designation == null) {
            return response()->json([
                'success' => false,
                'message' => "Designation doesn't exist",
                'data' => null
            ], 404);
        }

        $designation->designation = $request->designation;
        $designation->save();
        return response()->json([
            'success' => true,
            'message' => 'Designation updated successfully',
            'data' => $designation,
        ], 200);
    }

    public function deleteDesignation($id)
    {
        $designation = Designation::find($id);
        if (is_null($designation)) {
            return response()->json([
                'success' => false,
                'message' => "Designation doesn't exist",
                'data' => null
            ], 404,);
        } else {

            $designation->delete();
            return response()->json([
                'success' => true,
                'message' => 'Designation deleted successfully',
                'data' => $designation,
            ], 200);
        }
    }
}

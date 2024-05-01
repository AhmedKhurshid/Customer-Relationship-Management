<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Leaves_type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use function PHPUnit\Framework\isNull;

class LeaveTypesController extends Controller
{
    function createLeaveType(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string'],
            'sick' => ['required', 'integer'],
            'annual' => ['required', 'integer'],
            'casual' => ['required', 'integer'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages(),
                'data' => null
            ], 400);
        }

        $data = [
            'name' => $request->name,
            'sick' => $request->sick,
            'annual' => $request->annual,
            'casual' => $request->casual,
        ];

        try {
            $leaveCreate = Leaves_type::create($data);
        } catch (\Throwable $e) {
            echo $e->getMessage();
        }
        if ($leaveCreate != null) {
            return response()->json(
                [
                    'success' => true,
                    'message' => 'Leaves type insert successfully',
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

    function showLeaveTypeToAdmin()
    {
        $leaves = Leaves_type::all();

        if (!$leaves) {
            return response()->json([
                'success' => false,
                'message' => 'Leaves types not found',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Leaves types found',
            'data' => $leaves,
        ], 200);
    }
    function showSingleLeaveTypeToAdmin($id)
    {
        $leaves = Leaves_type::where('id', $id)->first();

        if (!$leaves) {
            return response()->json([
                'success' => false,
                'message' => 'Leaves types not found',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Leaves types found',
            'data' => $leaves,
        ], 200);
    }

    function updateLeaveType(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required_without_all:annual,casual,sick', 'string'],
            'sick' => ['required_without_all:annual,casual,name', 'integer'],
            'annual' => ['required_without_all:casual,sick,name', 'integer'],
            'casual' => ['required_without_all:annual,sick,name', 'integer'],
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
        $findLeavesType = Leaves_type::find($id);

        if ($findLeavesType == null) {
            return response()->json([
                'success' => false,
                'message' => "Leaves Type doesn't exist",
                'data' => null
            ], 404);
        }

        $findLeavesType->name = $request->name;
        $findLeavesType->sick = $request->sick;
        $findLeavesType->annual = $request->annual;
        $findLeavesType->casual = $request->casual;
        $findLeavesType->save();
        return response()->json([
            'success' => true,
            'message' => 'Leaves Type updated successfully',
            'data' => $findLeavesType,
        ], 200);
    }

    function deleteLeaveType($id)
    {
        $findLeavesType = Leaves_type::find($id);
        if (is_null($findLeavesType)) {
            return response()->json([
                'success' => false,
                'message' => "Leaves Type doesn't exist",
                'data' => null
            ], 404,);
        } else {

            $findLeavesType->delete();
            return response()->json([
                'success' => true,
                'message' => 'Leaves Type deleted successfully',
                'data' => null,
            ], 200);
        }
    }
}

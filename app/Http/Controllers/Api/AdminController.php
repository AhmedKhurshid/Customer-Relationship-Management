<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Designation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use File;


class AdminController extends Controller
{
    function getAdmin(Request $request)
    {

        $usersAndOrders = DB::table('users')
            ->where('role', 'Admin')
            ->join('designations', 'users.designation', '=', 'designations.id')
            ->select('users.id', 'users.name', 'users.email', 'users.role', 'users.status', 'users.address', 'users.cnic', 'users.contactNo', 'users.image','users.isEmployee', 'designations.designation')
            ->get();


        if ($usersAndOrders == null) {
            return response([
                'success' => false,
                'message' => "User doesn't exist",
                'data' => null,
            ]);
        }

        $perPage = $request->input('perPage', 10);

        $search = $request->input('search');

        $query = User::query();


        if ($search) {
            $query->where('name', 'like', '%' . $search . '%')
                ->orWhere('email', 'like', '%' . $search . '%');
        }
        $users = $query->paginate($perPage);
        return response()->json([
            'success' => true,
            'message' => 'Data found',
            'data' => $usersAndOrders
        ]);
    }

    function getSingleAdmin($id)
    {
        $admin = User::where('id', $id)->where('role', 'Admin')->first();

        if ($admin == null) {
            return response()->json([
                'success' => false,
                'message' => 'Admin not found',
                'data' => null,
            ], 404);
        }
        return response()->json([
            'success' => false,
            'message' => 'Admin found',
            'data' => $admin,
        ], 200);
    }


    //  function updateAdmin(Request $request, $id)
    //  {

    // //     $validator = Validator::make($request->all(), [
    // //         'name' => ['required_without_all:email,password,address,contactNo,famContactNo,cnic,designation,image', 'string'],
    // //         'email' => ['required_without_all:name,password,address,contactNo,famContactNo,cnic,designation,image', 'email', 'unique:users,email'],
    // //         'password' => ['required_without_all:email,name,address,contactNo,famContactNo,cnic,designation,image', 'min:8'],
    // //         'address' => ['required_without_all:email,password,name,contactNo,famContactNo,cnic,designation,image', 'string'],
    // //         'contactNo' => ['required_without_all:email,password,address,name,famContactNo,cnic,designation,image', 'min:7', 'string'],
    // //         'famContactNo' => ['required_without_all:email,password,address,contactNo,name,cnic,designation,image', 'min:7'],
    // //         'cnic' => ['required_without_all:email,password,address,contactNo,famContactNo,name,designation,image', 'min:7', 'string'],
    // //         'designation' => ['required_without_all:email,password,address,contactNo,famContactNo,cnic,name,image', 'integer'],
    // //         'image' => ['required_without_all:email,password,address,contactNo,famContactNo,cnic,designation,name', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
    // //     ]);

    // //     if ($validator->fails()) {
    // //         return response()->json([
    // //             'success' => false,
    // //             'message' => $validator->messages(),
    // //             'data' => null,
    // //         ], 400);
    // //     }
    // //     $admin = User::where('id', $id)->where('role', 'Admin')->first();

    // //     if (!$admin) {
    // //         return response()->json([
    // //             'success' => false,
    // //             'message' => 'Admin not found',
    // //             'data' => null,
    // //         ], 404);
    // //     }

    // //     $designationExist = Designation::where('id', $request->designation)->first();

    // //     if ($designationExist == null) {
    // //         return response()->json([
    // //             'success' => false,
    // //             'message' => 'Designation does not exist',
    // //             'data' => null,

    // //         ], 404);
    // //     }

    // //     if ($request->hasFile('image')) {

    // //         $filename = time() . $request->file('image')->getClientOriginalName();
    // //         $imageURL = $request->file('image')->storeAs('image', $filename, 'public');
    // //         $newImageUrl = ('storage/' . $imageURL);

    // //         $imagePath = $admin->image;

    // //         if ($imagePath) {
    // //             File::delete($imagePath);
    // //         }
    // //     }

    // //     $admin->name = $request->name ?? $admin->name;
    // //     $admin->email = $request->email ?? $admin->email;
    // //     $admin->password = $request->password ?? $admin->password;
    // //     $admin->address = $request->address ?? $admin->address;
    // //     $admin->contactNo = $request->contactNo ?? $admin->contactNo;
    // //     $admin->famContactNo = $request->famContactNo ?? $admin->famContactNo;
    // //     $admin->cnic = $request->cnic ?? $admin->cnic;
    // //     $admin->designation = $request->designation ?? $admin->designation;
    // //     $admin->image = $newImageUrl ?? $admin->image;
    // //     $admin->save();

    // //     return response()->json([
    // //         'success' => true,
    // //         'message' => 'Admin updated successfully',
    // //         'data' => null,
    // //     ], 200);
    // }
    
    function updateAdmin(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'name' => ['required_without_all:email,password,address,contactNo,famContactNo,cnic,designation,image', 'string'],
            'email' => ['required_without_all:name,password,address,contactNo,famContactNo,cnic,designation,image', 'email', 'email'],
            'password' => ['required_without_all:email,name,address,contactNo,famContactNo,cnic,designation,image', 'min:8'],
            'address' => ['required_without_all:email,password,name,contactNo,famContactNo,cnic,designation,image', 'string'],
            'contactNo' => ['required_without_all:email,password,address,name,famContactNo,cnic,designation,image', 'min:7', 'string'],
            'famContactNo' => ['required_without_all:email,password,address,contactNo,name,cnic,designation,image', 'min:7'],
            'cnic' => ['required_without_all:email,password,address,contactNo,famContactNo,name,designation,image', 'min:7', 'string'],
            'designation' => ['required_without_all:email,password,address,contactNo,famContactNo,cnic,name,image', 'integer'],
            // 'image' => ['required_without_all:email,password,address,contactNo,famContactNo,cnic,designation,name', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages(),
                'data' => null,
            ], 400);
        }
        $admin = User::where('id', $id)->where('role', 'Admin')->first();

        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Admin not found',
                'data' => null,
            ], 404);
        }

        $designationExist = Designation::where('id', $request->designation)->first();

        if ($designationExist == null) {
            return response()->json([
                'success' => false,
                'message' => 'Designation does not exist',
                'data' => null,

            ], 404);
        }

        if ($request->hasFile('image')) {

            $filename = time() . $request->file('image')->getClientOriginalName();
            $imageURL = $request->file('image')->storeAs('image', $filename, 'public');
            $newImageUrl = ('storage/' . $imageURL);

            $imagePath = $admin->image;

            if ($imagePath) {
                File::delete($imagePath);
            }
        }

        $alluserEmail = User::where('email', $request->email)->where('id', '!=', $id)->first();


        if (!empty($request->email)) {

            $adminId = User::where('id', $id)->first();

            if ($request->email == $adminId->email || !$alluserEmail) {

                $admin->name = $request->name ?? $admin->name;
                $admin->email = $request->email;
                $admin->password = $request->password ?? $admin->password;
                $admin->address = $request->address ?? $admin->address;
                $admin->contactNo = $request->contactNo ?? $admin->contactNo;
                $admin->famContactNo = $request->famContactNo ?? $admin->famContactNo;
                $admin->cnic = $request->cnic ?? $admin->cnic;
                $admin->designation = $request->designation ?? $admin->designation;
                $admin->image = $newImageUrl ?? $admin->image;
                $admin->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Admin updated successfully',
                    'data' => null,
                ], 200);
            }
        }
        if ($alluserEmail) {
            return response()->json([
                'success' => false,
                'message' => 'Email already exist',
                'data' => null,
            ], 400);
        } else {

            $admin->name = $request->name ?? $admin->name;
            // $admin->email = $request->email ?? $admin->email;
            $admin->password = $request->password ?? $admin->password;
            $admin->address = $request->address ?? $admin->address;
            $admin->contactNo = $request->contactNo ?? $admin->contactNo;
            $admin->famContactNo = $request->famContactNo ?? $admin->famContactNo;
            $admin->cnic = $request->cnic ?? $admin->cnic;
            $admin->designation = $request->designation ?? $admin->designation;
            $admin->image = $newImageUrl ?? $admin->image;
            $admin->save();

            return response()->json([
                'success' => true,
                'message' => 'Admin updated successfully',
                'data' => null,
            ], 200);
        }
    }
}

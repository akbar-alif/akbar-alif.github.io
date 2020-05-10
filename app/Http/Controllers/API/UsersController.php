<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\User;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Facades\Image;

class UsersController extends Controller {
    /**
     * UsersController constructor.
     */
    public function __construct() {
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     * @throws AuthorizationException
     */
    public function index()
    {
        $this->authorize('isAdmin');
        $users = User::latest()->paginate(10);
        return Response(compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:191',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6'
        ]);

        $user = User::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'type' => $request['type'],
            'bio' => $request['bio'],
            'photo' => $request['photo'] ?? 'profile.png',
            'password' => Hash::make($request['password'])
        ]);

        return Response(compact('user'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        return Response(['message' => "User"]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return Response
     * @throws ValidationException
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|string|max:191',
            'email' => 'required|string|email|unique:users,email,' . $id,
            'password' => 'sometimes|string|min:6'
        ]);

        $user = User::findOrFail($id);
        $user->update($request->except(['id', 'password']));
        return Response(['message' => "User updated successfully"]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     * @throws Exception
     */
    public function destroy($id)
    {
        $this->authorize("isAdmin");
        User::findOrFail($id)->delete();
        return Response(['message' => "User deleted successfully"]);
    }

    /**
     * @return Authenticatable|null
     */
    public function profile(): ?Authenticatable {
        return auth('api')->user();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function updateProfile(Request $request) {
        $user = auth('api')->user();

        $this->validate($request, [
            'name' => 'required|string|max:191',
            'email' => 'required|string|email|unique:users,email,' . $user->id,
            'password' => 'sometimes|required|string|min:6'
        ]);

        $usersCurrentPhoto = $user->photo;
        $photo = $request->get("photo");
        if($photo != $usersCurrentPhoto) {
            $fileName = time() . "." . explode("/", substr($photo, 0, strpos($photo, ";")))[1];

            Image::make($photo)->save(public_path("img/profile/$fileName"));

            $request->merge(["photo" => $fileName]);

            $usersCurrentPhotoPath = public_path("img/profile/$usersCurrentPhoto");
            if(file_exists($usersCurrentPhotoPath))
                @unlink($usersCurrentPhotoPath);
        }

        if(!empty($request->get("password"))) // hash password
            $request->merge(["password" => Hash::make($request->get("password"))]);

        if(is_null($request["password"]))
            unset($request["password"]);

        $user->update($request->all());

        return response()->json(["message" => "Profile updated successfully."]);
    }

    public function search(Request $request) {
        $query = $request->get("q");

        $users = User::where("name", "like", "%$query%")
            ->orWhere("email", "like", "%$query%")
            ->orWhere("type", "like", "%$query%")
            ->paginate(10);

        return compact("users");
    }
}

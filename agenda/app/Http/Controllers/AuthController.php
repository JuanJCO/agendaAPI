<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Contact;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Password;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    
    public function authenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');
        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['Error' => 'Invalid Credentials'], 400);
            }
        } catch (JWTException $e) {
            return response()->json(['Error' => 'Could not create token'], 500);
        }  
        return response()->json(compact('token'));
    }


    public function getAuthenticatedUser()
    {
    try {
        if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['User Not Found'], 404);
        }
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
                return response()->json(['token_expired'], $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
                return response()->json(['token_invalid'], $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
                return response()->json(['token_absent'], $e->getStatusCode());
        }
        $user_send = compact('user');

        return response()->json($user_send);
    }


    public function register(Request $request)
    {
            $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed'
        ]);

        if($validator->fails()){
                return response()->json($validator->errors()->toJson(), 400);
        }

        $user = User::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password'))
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json(compact('token'),201);
    }


    public function logout(Request $request){
    	JWTAuth::invalidate($request->get('authorization'));

    	return response()->json([
    		'status'=> 'success',
    		'message'=>'logout'
    	], 200);
    }


    public function forgot(){

        $credentials = request()->validate(['email' => 'required|email']);

        Password::sendResetLink($credentials);

        return $this->respondWithMessage('Reset password link sent on your email id.');
    }


    public function update(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if ($user){
            if ($request->name){
                $user->name = $request->name;
            }

            if ($request->mail) {
                $user->email = $request->mail;
            }

            if ($request->password) {
                $user->password = Hash::make($request->password);
            }

            try{
                $user->save();

                return response()->json([
                    "Status" => "Update",
                    "Body" => $user
                ], 200);

            } catch (\Exception $e){
                return response()->json('No se ha realizado ningún cambio', 500);
            }
        }
        return response()->json('No se ha encontrado el usuario', 507);
    }


    public function reset() {
        $credentials = request()->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|confirmed'
        ]);

        $reset_password_status = Password::reset($credentials, function ($user, $password) {
            $user->password = Hash::make($password);
            $user->save();
        });

        if ($reset_password_status == Password::INVALID_TOKEN) {
            return response()->json(["Message" => "Invalid token provided"], 400);
        }
        return response()->json(["Message" => "Password has been successfully changed"]);
    }


    public function delete(Request $request)
    {

        $user = JWTAuth::parseToken()->authenticate();
        $id = $user->id;

        if($id) {

            $user = User::where('id', $id)->first();
            $contact = Contact::where('user_id', $id)->delete();

            $user->delete();

            return response()->json([
                'Status' => 'Remove',
                'Message' => 'User Deleted'
            ], 200);
        }

        return response()->json('No se ha encontrado el Id Usuario', 500);
    }

    public function search(Request $request){

        $data = $request->getContent();

        $user = JWTAuth::parseToken()->authenticate();
        $id = $user->id;
        
        $search = $request->search;
        $word = '%'.$search.'%';


        if($data){

            $contact = Contact::where('user_id', $id)
                                ->where('name', 'like', $word)
                                ->orWhere('mail', 'like', $word)
                                ->orWhere('phone', 'like', $word)->get();

            if ($contact->isEmpty()){
                return response()->json('No se ha encontrado ningún contacto', 404);
            }

            return response($contact);
        }
    }
}

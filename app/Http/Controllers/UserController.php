<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Repositories\PeopleRepository;
use App\Repositories\UserRepository;
use App\Models\People;
use App\Models\User;
use JWTAuth;
use Mail;
use Uuid;

class UserController extends Controller
{
    //
    protected $people_repository;
    protected $user_repository;

    public function __construct(PeopleRepository $people, UserRepository $user){
        $this->people_repository = $people;
        $this->user_repository = $user;
    }

    public function register(Request $request){
        $validator = Validator::make($request->all(),[
            'name' => 'required|string|max:35',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|max:16',
            'lastNameP' => 'required|string|max:30',
            'lastNameM' => 'required|string|max:30',
            'gender' => 'required|string|max:9',
            'birthDate' => 'required|date',
            'phone' => 'required|string|max:15',
            'curp' => 'required|string|max:18',
            'rfc' => 'required|string|max:13',
        ]);
        if($validator->fails()){
            Log::warning('UserController - register - Falta un campo por llenar');
            return response()->json($validator->errors()->toJson(), 400);
        }

        DB::beginTransaction();

        try{
            $person = $this->people_repository->create(
                Uuid::generate()->string,
                $request->get('name'),
                $request->get('lastNameP'),
                $request->get('lastNameM'),
                $request->get('gender'),
                $request->get('birthDate'),
                $request->get('phone'),
                $request->get('curp'),
                $request->get('curp'),

            );
            
            $user = $this->user_repository->create(
                Uuid::generate()->string,
                $request->get('name'),
                $request->get('lastNameP'),
                $request->get('lastNameM'),
                $request->get('email'),
                Hash::make($request->get('password')),
                $request->get('validation').substr($request->get('name'),0,3).substr($request->get('email'),0,3).'2022',
                $person->id,
                User::Empleado
            );

            $token = JWTAuth::fromUser($user);
            $this->sendEmail($user);

            DB::commit();
            
            Log::info('UserController - register - Se creo un nuevo Empleado');
            return response()->json(compact('user', 'token', 'person'),201);

        }catch(\Exception $ex){
            Log::emergency('UserController - register - Ocurrio un error');
            return response()->json(['error'=>$ex->getMessage()]);
            DB::rollback();
        }
    }

}

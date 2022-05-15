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

    public function authenticate(Request $request){
        $credentials = $request->only('email', 'password');
        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 400);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }
        $users = JWTAuth::user();

        if ($users->validation != '') {
            Log::warning('UserController - authenticate - el usuario no fue validado' . $users);
            return response()->json(['error' => 'User_not_validated'], 403);
        }

        if($users->rol_id == User::Empleado){
            $person = $users->people;
            $roles = $users->rol;
        }

        Log::info('UserController - authenticate - Inicio sesión el usuario' . $users);
        return response()->json(compact('token', 'users'));
    }

    public function getAuthenticatedUser(){
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                    return response()->json(['user_not_found'], 404);
            }
            } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
                    return response()->json(['token_expired'], $e->getStatusCode());
            } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
                    return response()->json(['token_invalid'], $e->getStatusCode());
            } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
                    return response()->json(['token_absent'], $e->getStatusCode());
            }
            $person = $user->people;
            $roles = $user->rol;
            return response()->json(compact('user'));
    }

    public function sendEmail($user){
        $datas['subject'] = 'Correo para Empleados';
        $datas['for'] = $user['email'];
        Mail::send('mail.mail', ['user'=>$user],function ($msj) use ($datas){
            $msj->from("20183l301014@utcv.edu.mx", "Practica");
            $msj->subject($datas['subject']);
            $msj->to($datas['for']);
        });
    }

    public function validation(Request $request){
        $user = User::Where([['email', '=', $request->get('email')],['validation', '=', $request->get('validar')]])->get();

        if(count($user) > 0){
            $user[0]->validation='';
            $user[0]->save();
        }
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
                $request->get('rfc'),
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

    public function update(Request $request, $uuid){
        $validator = Validator::make($request->all(),[
            'name' => 'required|string|max:35',
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
            Log::warning('UserController - update - Falta un campo por llenar');
            return response()->json($validator->errors()->toJson(), 400);
        }

        DB::beginTransaction();

        try{
            $global = People::Where('uuid', '=', $uuid)->first();

            $person = $this->people_repository->update(
                $global->uuid,
                $request->get('name'),
                $request->get('lastNameP'),
                $request->get('lastNameM'),
                $request->get('gender'),
                $request->get('birthDate'),
                $request->get('phone'),
                $request->get('curp'),
                $request->get('rfc'),
            );
            $user = $this->user_repository->update(
                $global->user->uuid,
                $request->get('name'),
                $request->get('lastNameP'),
                $request->get('lastNameM'),
                $request->get('password'),
            );

            DB::commit();

            Log::info('UserController - update - Se actualizó un Empleado');
            return response()->json(compact('user', 'person'),201);

        }catch(\Exception $ex){
            Log::emergency('UserController - update - Ocurrio un error');
            return response()->json(['error'=>$ex->getMessage()]);
            DB::rollback();
        }
    }

    public function list(){
        $person = User::Where('uuid', '!=', null)->get();
        $users = [];
        foreach($person as $key=> $value){
            $users[$key] = [
                'id'=> $value['id'],
                'uuid'=> $value['uuid'],
                'name'=> $value['name'],
                'email'=> $value['email'],
                'validation'=> $value['validation'],
                'people_id'=> $value['people_id'],
                'uuid_persona' => $value->people->uuid,
                'name_persona' => $value->people->name,
                'lastNameP' => $value->people->lastNameP,
                'lastNameM' => $value->people->lastNameM,
                'gender' => $value->people->gender,
                'birthDate' => $value->people->birthDate,
                'phone' => $value->people->phone,
                'curp' => $value->people->curp,
                'rfc' => $value->people->rfc,
            ];
        }
        return response()->json($users);
    }

    public function edit($uuid){
        $person = People::Where('uuid', '=', $uuid)->first();
        $user = User::Where('uuid', '=', $person->user->uuid)->first();

        $masvar = [
            'id' => $person['id'],
            'uuid' => $person['uuid'],
            'name' => $person['name'],
            'lastNameP' => $person['lastNameP'],
            'lastNameM' => $person['lastNameM'],
            'gender' => $person['gender'],
            'birthDate' => $person['birthDate'],
            'phone' => $person['phone'],
            'curp' => $person['curp'],
            'rfc' => $person['rfc'],
            'uuid_user' => $user['uuid'],
            'name_user' => $user['name'],
            'email' => $user['email'],
            'validation' => $user['validation'],
        ];
        return response()->json($masvar);
    }

    public function delete($uuid){
        try{
            $person = People::Where('uuid', '=', $uuid)->first();
            $person->user->delete();
            $person->delete();
            Log::info('UserController - delete - Eliminaste un Empleado');
            return response()->json('Datos eliminados');

        }catch(\Exception $ex){
            Log::emergency('UserController - delete - Ocurrio un error');
            return response()->json(['error'=>$ex->getMessage()]);
        }
    }

}

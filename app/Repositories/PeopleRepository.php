<?php
namespace App\Repositories;
use App\Models\People;

class PeopleRepository{

    public function create($uuid, $name, $lastNameP, $lastNameM, $gender, $birthDate, $phone, $curp, $rfc){
        $people['uuid'] = $uuid;
        $people['name'] = $name;
        $people['lastNameP'] = $lastNameP;
        $people['lastNameM'] = $lastNameM;
        $people['gender'] = $gender;
        $people['birthDate'] = $birthDate;
        $people['phone'] = $phone;
        $people['curp'] = $curp;
        $people['rfc'] = $rfc;
        return People::create($people);
    }

    public function update($uuid, $name, $lastNameP, $lastNameM, $gender, $birthDate, $phone, $curp, $rfc){
        $people = $this->find($uuid);
        $people->name = $name;
        $people->lastNameP = $lastNameP;
        $people->lastNameM = $lastNameM;
        $people->gender = $gender;
        $people->birthDate = $birthDate;
        $people->phone = $phone;
        $people->curp = $curp;
        $people->rfc = $rfc;
        $people->save();
        return $people;
    }

    public function find($uuid){
        return People::Where('uuid', '=', $uuid)->first();
    }

    public function delete($uuid){
        $people = $this->find($uuid);
        return $people->delete();
    }
}
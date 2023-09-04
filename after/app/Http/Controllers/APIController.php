<?php

namespace App\Http\Controllers;

use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Testing\Fakes\Fake;

class APIController extends Controller
{
    function createUser(Request $request)
    {
        $response = [
            "code" => 200,
            "status" => true,
        ];

        $rules = [
            "name" => ["required"],
            "no_ktp" => ["required", "numeric", "digits:16", "unique:users,no_ktp"],
            "email" => ["required", "unique:users,email"],
            "balance" => ["required", "numeric"],
            "password" => ["required", "min:8"],
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $response["code"] = 400;
            $response["status"] = \false;
            $response["message"] = "please check the parameters";
            $response["errors"] = $validator->errors();
        } else {
            $faker = Faker::create('id_ID');
            $norek = $faker->bothify("############");
            $newUser = new User();
            $newUser->name = $request->name;
            $newUser->email = $request->email;
            $newUser->norek = $norek;
            $newUser->password = \bcrypt($request->password);
            $newUser->balance = $request->balance;
            $newUser->no_ktp = $request->no_ktp;
            $newUser->save();

            $response["message"] = "success to create new user";
            $response["data"] = $newUser;
        }

        return \response()->json($response, $response["code"]);
    }

    function updateUser(Request $request)
    {
        $response = [
            "code" => 200,
            "status" => true,
        ];

        $rules = [
            "name" => ["required", "min:3"],
            "id" => ["required", "numeric"],
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $response["code"] = 400;
            $response["status"] = \false;
            $response["message"] = "please check the parameters";
            $response["errors"] = $validator->errors();
        } else {
            $findUser = User::find($request->id);
            if (!$findUser) {
                $response["code"] = 400;
                $response["status"] = \false;
                $response["message"] = "user not found";
            } else {
                $findUser->name = $request->name;
                $findUser->update();

                $response["message"] = "success to update user";
                $response["data"] = $findUser;
            }
        }

        return \response()->json($response, $response["code"]);
    }

    function getDataBank($norek)
    {
        $response = [
            "code" => 200,
            "status" => \true
        ];
        $findBank = User::where("norek", $norek)->first();
        if (!$findBank) {
            $response["code"] = 400;
            $response["status"] = \false;
            $response["message"] = "rekening number not found";
        } else {
            $response["message"] = "success get data bank account";
            $response["data"] = [
                "name" => $findBank->name,
                "norek" => $findBank->norek,
                "saldo" => $findBank->balance,
            ];
        }

        return \response()->json($response, $response["code"]);
    }

    function topUpSaldo(Request $request)
    {
        $response = [
            "code" => 200,
            "status" => true,
        ];

        $rules = [
            "norek" => ["required", "numeric"],
            "amount" => ["required", "numeric", "min:5000"],
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $response["code"] = 400;
            $response["status"] = \false;
            $response["message"] = "please check the parameters";
            $response["errors"] = $validator->errors();
        } else {
            $findBank = User::where("norek", $request->norek)->first();
            if (!$findBank) {
                $response["code"] = 400;
                $response["status"] = \false;
                $response["message"] = "rekening number not found";
            } else {
                $findBank->balance += $request->amount;
                $findBank->save();
                $response["message"] = "success get data bank account";
                $response["data"] = [
                    "name" => $findBank->name,
                    "norek" => $findBank->norek,
                    "saldo" => $findBank->balance,
                ];
            }
        }

        return \response()->json($response, $response["code"]);
    }

    function transferAmount(Request $request)
    {
        $response = [
            "code" => 200,
            "status" => true,
        ];

        $rules = [
            "sender_number" => ["required", "numeric"],
            "receiver_number" => ["required", "numeric"],
            "amount" => ["required", "numeric"],
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $response["code"] = 400;
            $response["status"] = \false;
            $response["message"] = "please check the parameters";
            $response["errors"] = $validator->errors();
        } else {
            if($request->sender_number == $request->receiver_number) {
                $response["code"] = 400;
                $response["status"] = \false;
                $response["message"] = "sender number cannot be the same on receiver number ";
            } else {
                $findSender = User::where("norek", $request->sender_number)->first();
                $findReceiver = User::where("norek", $request->receiver_number)->first();
                if($findSender->balance < $request->amount) {
                    $response["code"] = 400;
                    $response["status"] = \false;
                    $response["message"] = "sender's balance is not sufficient";
                } else {
                    if(!$findReceiver || !$findSender) {
                        $response["code"] = 400;
                        $response["status"] = \false;
                        if(!$findSender) {
                            $response["message"] = "bank account sender not found";
                        } else {
                            $response["message"] = "bank account receiver not found";
                        }
                    } else {
                        $findSender->createTransaction("OUT", $request->amount);
                        $findReceiver->createTransaction("IN", $request->amount);
        
                        $response["message"] = "success create transaction";
                    }
                }
            }
        }

        return \response()->json($response, $response["code"]);
    }

    function getMutation($norek) {
        $response = [
            "code" => 200,
            "status" => \true
        ];
        $findBank = User::with("mutations")->where("norek", $norek)->first();
        if (!$findBank) {
            $response["code"] = 400;
            $response["status"] = \false;
            $response["message"] = "rekening number not found";
        } else {
            $response["message"] = "success get data bank account";
            $response["data"] = [
                "name" => $findBank->name,
                "norek" => $findBank->norek,
                "saldo" => $findBank->balance,
                "mutations" => $findBank->mutations,
            ];
        }

        return \response()->json($response, $response["code"]);
    }
}

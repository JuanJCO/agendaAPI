<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\User;
use JWTAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = JWTAuth::parseToken()->authenticate();

        $id = $user->id;

        $contact = Contact::where('user_id', $id)->get()->toArray();

        return response()->json($contact, 200);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->getContent();

        //Valida los datos de la $request
        if ($data){
            $validator = Validator::make($request->all(),[
                'name' => 'required|string|max:20',
                'phone' => 'string|max:9',
                'mail' => 'string|max:255|email'
            ]);

        //Informa si la validación es errónea
            if ($validator->fails()) {
                return response()->json($validator->errors()->toJson(), 400);
            }

            $user = JWTAuth::parseToken()->authenticate();

            $contact = new Contact();

            $contact->user_id = $user->id;
            $contact->name = $request->name;
            
            if ($request->phone) {
                $contact->phone = $request->phone;
            }
            if ($request->mail) {
                $contact->mail = $request->mail;
            }

            $contact->save();

            return response()->json($contact->toJson(), 201);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Contact  $contact
     * @return \Illuminate\Http\Response
     */
    public function show(Contact $contact)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Contact  $contact
     * @return \Illuminate\Http\Response
     */
    public function edit(Contact $contact)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Contact  $contact
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $contact = Contact::find($id);

        if ($contact){

            if ($request->name){
                $contact->name = $request->name;
            }

            if ($request->phone) {
                $contact->phone = $request->phone;
            }

            if ($request->mail) {
                $contact->mail = $request->mail;
            }

            try{
                $contact->save();

                return response()->json([
                    "Status" => "Update",
                    "Body" => $contact
                ], 200);

            } catch (\Exception $e){
                return response()->json('No se ha realizado ningún cambio', 500);
            }
        }

        return response()->json('No se ha encontrado el contacto', 507);
    }


    public function delete(Request $request)
    {
        $data = $request->getContent();

        if ($data) {

            $contactId = $request->id;

            if ($contactId){

                $contact = Contact::where('id', $contactId)->first();

                $contact->delete();
                
                return response()->json([
                    'Status' => 'Remove',
                    'Message' => 'User deleted'
                ], 200);
            } 
        
        return response()->json('No se han encontrado ID', 500);

        }

    return response()->json('No se han encontrado datos', 501);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Contact  $contact
     * @return \Illuminate\Http\Response
     */
    public function destroy(Contact $contact)
    {
        
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Country;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\KeyInstructionRequest;
use App\Http\Traits\CountryTrait;
use App\KeyInstruction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KeyInstructionController extends Controller
{
    use CountryTrait;
    /**
     * Show the key instruction view page. Passing all active countries the key instruction view page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Countries trait to fetch the active countries.
        $countries = $this->country();

        return view('admin.keyinstruction', ['countries' => $countries]);
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
     * Store a newly key instruction into storage.
     *
     * @param  \App\Http\Requests\Admin\KeyInstructionRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(KeyInstructionRequest $request)
    {
        try {
            if (!empty($request->key_instruction_container_id) && !empty($request->key_instruction_language)) {

                // Fetching country code matching with key instruction lang.
                $countryCode         = Country::select('code')->find($request->key_instruction_language);

                // Path of directory.
                $path_of_directory   = 'key_instruction/' . (int) $request->key_instruction_container_id . '/' . $countryCode->code;

                // If directory doesn't exists create a new one.
                if (!Storage::exists($path_of_directory)) {
                    $createDirectory = Storage::makeDirectory($path_of_directory, 0775);
                }

                // Checking data already exist or not.
                $findKeyInstruction = KeyInstruction::where('key_container_id', $request->key_instruction_container_id)
                    ->where('country_id', $request->key_instruction_language)
                    ->first();

                if (!empty($findKeyInstruction) && Storage::exists($findKeyInstruction->instruction_url)) {

                    // Delete files from directory.
                    Storage::delete($findKeyInstruction->instruction_url); 

                    // Delete data from directory.
                    KeyInstruction::destroy($findKeyInstruction->id); 

                    // Storing new file in to folder.
                    $path_of_file = $request->file('key_instruction_file')->store($path_of_directory);

                    // Storing key instructions in to storage.
                    $keyInstruction                   = new KeyInstruction;
                    $keyInstruction->key_container_id = $request->key_instruction_container_id;
                    $keyInstruction->country_id       = $request->key_instruction_language;
                    $keyInstruction->instruction_url  = $path_of_file;
                    $keyInstruction->save();

                } 
                else {
                    // Storing new file in to folder.
                    $path_of_file = $request->file('key_instruction_file')->store($path_of_directory);

                    // Storing key instructions in to storage.
                    $keyInstruction                   = new KeyInstruction;
                    $keyInstruction->key_container_id = $request->key_instruction_container_id;
                    $keyInstruction->country_id       = $request->key_instruction_language;
                    $keyInstruction->instruction_url  = $path_of_file;
                    $keyInstruction->save();
                }

                return response()->json(['keyInstructionStatus' => 'success', 'message' => 'Well done! Key instruction created successfully'], 201);
            } 
            else {
                return response()->json(['keyInstructionStatus' => 'failure', 'message' => 'Whoops! Something went wrong'], 404);
            }
        } 
        catch (\Exception $e) {
            return response()->json(['keyInstructionStatus' => 'failure', 'message' => 'Whoops! Something went wrong'], 404);
        }
    }

    /**
     * Key insturctions download scripts.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function download($id)
    {
        try {
            // Find key instructions based on the key instruction id.
            $keyInstruction  = KeyInstruction::find($id);

            if (!empty($keyInstruction) && Storage::exists($keyInstruction->instruction_url)) {

                $explodeFilename = explode('/', $keyInstruction->instruction_url); //Exploding file name from file path
                $filename        = end($explodeFilename);
                $headers         = ['Content-Disposition: attachment; filename=' . $filename];

                return Storage::download($keyInstruction->instruction_url, $filename, $headers);
            } 
            else {
                abort(404);
            }
        } 
        catch (\Exception $e) {
            abort(404);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $keydeleteinstructionid
     * @return \Illuminate\Http\Response
     */
    public function destroy($keydeleteinstructionid)
    {
        try {
            // Find key instructions based on the key instruction id.
            $keyInstruction = KeyInstruction::find($keydeleteinstructionid);

            // Checking the key instruction data and files are already exists.
            if (!empty($keyInstruction) && Storage::exists($keyInstruction->instruction_url)) {
                // Delete files from storage.
                Storage::delete($keyInstruction->instruction_url); 

                // Delete data from database.
                KeyInstruction::destroy($keydeleteinstructionid); 
            }

            return response()->json(['keyInstructionDeleteStatus' => 'success', 'message' => 'Key instruction deleted successfully'], 201);
        } 
        catch (\Exception $e) {
            return response()->json(['keyInstructionDeleteStatus' => 'failure', 'message' => 'Whoops! Something went wrong'], 404);
        }
    }
}

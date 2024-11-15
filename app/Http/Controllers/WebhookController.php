<?php

namespace App\Http\Controllers;

use App\Models\Webhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WebhookController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $method  = Webhook::method();
        $module = Webhook::module();
        return view('webhook.create',compact('module','method'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        // $created_by = $user->get_created_by();
        $validator = \Validator::make(
            $request->all(),
            [
                'module' => 'required|string|max:50',
                'url' => 'required',
                'method' => 'required|string|max:50',
            ]);
        if ($validator->fails())
        {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $webhook             = new Webhook();
        $webhook->module     = $request->module;
        $webhook->url        = $request->url;
        $webhook->method     = $request->method;
        $webhook->created_by = $user->ownerId();
        $webhook->save();

        return redirect()->back()->with('success', __('Webhook successfully created.'));
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
        $user = Auth::user();
        // $created_by = $user->get_created_by();
        $method  = Webhook::method();
        $module  = Webhook::module();
        $webhook = Webhook::where('created_by', '=', $user->ownerId())->where('id',$id)->first();
        return view('webhook.edit',compact('module','method','webhook'));
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
        $validator = \Validator::make(
            $request->all(),
            [
                'module' => 'required|string|max:50',
                'url' => 'required',
                'method' => 'required|string|max:50',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }
        // $ip     = IpRestrict::find($id);
        $webhook             = Webhook::find($id);
        $webhook->module     = $request->module;
        $webhook->url        = $request->url;
        $webhook->method     = $request->method;
        $webhook->save();

        return redirect()->back()->with('success', __('Webhook successfully updated.'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $webhook = Webhook::find($id);
        $webhook->delete();

        return redirect()->back()->with('success', __('Webhook successfully deleted.'));
    }

    public function WebhookResponse(Request $request)
    {
        $user = User::where('email',$request['email'])->first();
        if(empty($user))
        {
            User::create([
                'name' => $request['name'],
                'email' => $request['email'],
                'password' => Hash::make($request['password']),
            ]);
        }
        \Log::debug('*******************************************************************************');
        \Log::debug($request->all());
        \Log::debug('*******************************************************************************');
    }
}

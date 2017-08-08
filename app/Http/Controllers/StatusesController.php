<?php

namespace App\Http\Controllers;

use App\Models\Status;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class StatusesController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth',[
            'only' => ['store','destroy']
        ]);
    }

    public function store(Request $request)
    {
        $this->validate($request,[
            'content' => 'required | max:140'
        ]);

        Auth::user()->statuses()->create([
            'content' => $request->content
        ]);

        return redirect()->back();
    }

    public function destroy($id)
    {
        $status = Status::findOrFail($id);
        $this->authorize('destroy',$status);
        $status->delete();
        session()->flash('success','微博已被删除');
        return redirect()->back();
    }
}

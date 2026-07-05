<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function index()
    {
        return view('toastr');
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function notification($type)
    {
        switch ($type) {
            case 'success':
                return back()->with("success", "User created successfully.");
                break;

            case 'info':
                return back()->with("info", "User updated successfully.");
                break;

            case 'warning':
                return back()->with("warning", "User can not access page.");
                break;

            case 'error':
                return back()->with("error", "This is testing error.");
                break;
            
            default:
                // code...
                break;
        }
    }
}
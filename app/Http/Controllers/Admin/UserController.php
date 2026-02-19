<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserController extends Controller
{
    public function index()
    {
        // returns a blade that will load the user list via javascript
        return view('admin.users.index');
    }

    /**
     * Return a paginated listing of all users (JSON).
     */
    public function list(Request $request)
    {
        // include roles so the front‑end can tell who is admin
        $users = User::with('roles')->paginate(20);
        return response()->json($users, Response::HTTP_OK);
    }

    /**
     * Toggle the `admin` role on the given user.  If the user already has the
     * role it is removed; otherwise it is granted.
     */
    public function toggleAdmin(Request $request, User $user)
    {
        // Prevent an admin from revoking their own admin role via this endpoint
        $current = $request->user();
        if ($user->id === $current->id && $user->hasRole('admin')) {
            return response()->json([
                'message' => 'You cannot revoke your own admin role.'
            ], Response::HTTP_FORBIDDEN);
        }

        if ($user->hasRole('admin')) {
            $user->removeRole('admin');
        } else {
            $user->assignRole('admin');
        }

        return response()->json([
            'user' => $user,
            'hasRole' => $user->hasRole('admin'),
        ], Response::HTTP_OK);
    }
}

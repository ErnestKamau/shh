<?php

namespace App\Http\Middleware;

use Closure;

class checkHasPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next,$permissions)
    {
        $parameter = $request->route('status');
        $user = auth()->user();
        $perm = explode('.',$permissions);
        if(sizeof($perm) == 2){
            if($user->check_permission($perm)){
                // return response()->json($user->check_permission($perm),200);
                return $next($request);
            }else{
                return redirect()->back()->with('error','You have no permission to perform the designated task!');
            }
        }
        if($perm[2] == 'empty'){
            $param = $request->route()->parameters();
            foreach($param as $key => $value){
                $perm[2] = $value;
                
                
            }
        }
        
		if($perm[2] == 'All Samples'){
			return $next($request);
		}
		if($user->check_permission($perm)){
			return $next($request);
		}else{
			return redirect()->back()->with('error','You have no permission to perform the designated task!');
		}

	}
}

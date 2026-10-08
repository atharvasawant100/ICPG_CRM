<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutController extends Controller
{
    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        // Audit the logout before the session is destroyed. Failure to audit
        // must not prevent the logout itself.
        \App\Rbac\AuditLogger::recordAuth('logout', Auth::id());

        // #region agent log
        $logPath = base_path('.cursor/debug.log');
        @file_put_contents($logPath, json_encode(['id'=>'log_'.time().'_entry','timestamp'=>time()*1000,'location'=>'LogoutController.php:14','message'=>'Logout method called','data'=>['method'=>$request->method(),'hasAuth'=>Auth::check(),'userId'=>Auth::check()?Auth::id():null],'sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'C'])."\n", FILE_APPEND | LOCK_EX);
        // #endregion
        
        try {
            // Use web guard for session-based logout (Sanctum web sessions use web guard)
            Auth::guard('web')->logout();
            // #region agent log
            @file_put_contents($logPath, json_encode(['id'=>'log_'.time().'_auth','timestamp'=>time()*1000,'location'=>'LogoutController.php:18','message'=>'Auth::guard(web)->logout() called','data'=>['authCheckAfter'=>Auth::check()],'sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'C'])."\n", FILE_APPEND | LOCK_EX);
            // #endregion
            
            $request->session()->invalidate();
            // #region agent log
            @file_put_contents($logPath, json_encode(['id'=>'log_'.time().'_session','timestamp'=>time()*1000,'location'=>'LogoutController.php:21','message'=>'Session invalidated','data'=>['sessionId'=>$request->session()->getId()],'sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'C'])."\n", FILE_APPEND | LOCK_EX);
            // #endregion
            
            $request->session()->regenerateToken();
            // #region agent log
            @file_put_contents($logPath, json_encode(['id'=>'log_'.time().'_token','timestamp'=>time()*1000,'location'=>'LogoutController.php:24','message'=>'Token regenerated','data'=>[],'sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'C'])."\n", FILE_APPEND | LOCK_EX);
            // #endregion
            
            $redirect = redirect()->route('login');
            // #region agent log
            @file_put_contents($logPath, json_encode(['id'=>'log_'.time().'_exit','timestamp'=>time()*1000,'location'=>'LogoutController.php:27','message'=>'Logout successful, redirecting','data'=>['redirectUrl'=>$redirect->getTargetUrl()],'sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'C,E'])."\n", FILE_APPEND | LOCK_EX);
            // #endregion
            return $redirect;
        } catch (\Exception $e) {
            // #region agent log
            @file_put_contents($logPath, json_encode(['id'=>'log_'.time().'_error','timestamp'=>time()*1000,'location'=>'LogoutController.php:30','message'=>'Logout exception','data'=>['error'=>$e->getMessage(),'trace'=>$e->getTraceAsString()],'sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'C'])."\n", FILE_APPEND | LOCK_EX);
            // #endregion
            throw $e;
        }
    }
}

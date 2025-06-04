<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\NotificationController;

class MobileAuthController 
{
    protected $notificationController;

    public function __construct(NotificationController $notificationController)
    {
        $this->notificationController = $notificationController;
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'fcm_token' => 'required|string'
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        $token = $user->createToken('mobile_token')->plainTextToken;

        $fcmSubscribed = false;
        
        // Handle notifikasi berdasarkan level user
        switch($user->level) {
            case 'penjual':
                $penitip = \App\Models\Penitip::where('user_id', $user->id)->first();
                if ($penitip) {
                    \Log::info('Subscribe to topic: ' . 'penitip_' . $penitip->id_penitip . ' with token: ' . $request->fcm_token);
                    $this->notificationController->subscribePenitipFromRequest($penitip->id_penitip, $request->fcm_token);
                    $fcmSubscribed = true;
                }
                break;
        }

        return response()->json([
            'token' => $token,
            'user' => $user,
            'fcm_subscribed' => $fcmSubscribed
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        
        // Jika user adalah penjual, unsubscribe dari notifikasi
        if ($user->level === 'penjual' && $request->fcm_token) {
            $penitip = \App\Models\Penitip::where('user_id', $user->id)->first();
            if ($penitip) {
                $this->notificationController->unsubscribeFromTopic([
                    'token' => $request->fcm_token,
                    'topic' => 'penitip_' . $penitip->id_penitip
                ]);
            }
        }

        // Hanya hapus token yang dibuat dari mobile
        $request->user()->tokens()->where('name', 'mobile_token')->delete();
        
        return response()->json([
            'message' => 'Logged out from mobile',
            'fcm_unsubscribed' => $user->level === 'penjual'
        ]);
    }

    public function updateFcmToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string'
        ]);

        $user = $request->user();
        
        // Re-subscribe dengan token baru jika user adalah penjual
        if ($user->level === 'penjual') {
            $penitip = \App\Models\Penitip::where('user_id', $user->id)->first();
            if ($penitip) {
                // Unsubscribe token lama jika ada
                if ($request->old_fcm_token) {
                    $this->notificationController->unsubscribeFromTopic([
                        'token' => $request->old_fcm_token,
                        'topic' => 'penitip_' . $penitip->id_penitip
                    ]);
                }
                
                // Subscribe token baru
                $this->notificationController->subscribePenitip($penitip->id_penitip, $request->fcm_token);
            }
        }

        return response()->json([
            'message' => 'FCM token updated successfully',
            'fcm_subscribed' => $user->level === 'penjual'
        ]);
    }
} 
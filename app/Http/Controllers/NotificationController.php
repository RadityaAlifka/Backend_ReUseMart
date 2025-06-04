<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class NotificationController
{
    protected $messaging;

    public function __construct()
    {
        $this->messaging = app('firebase.messaging');
    }

    /**
     * Mengirim notifikasi ke device tertentu
     */
    public function sendToDevice(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'title' => 'required|string',
            'body' => 'required|string',
            'data' => 'nullable|array'
        ]);

        try {
            $message = CloudMessage::withTarget('token', $request->token)
                ->withNotification(Notification::create($request->title, $request->body))
                ->withData($request->data ?? []);

            $this->messaging->send($message);

            return response()->json([
                'message' => 'Notification sent successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to send notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mengirim notifikasi ke topic
     */
    public function sendToTopic(Request $request)
    {
        $request->validate([
            'topic' => 'required|string',
            'title' => 'required|string',
            'body' => 'required|string',
            'data' => 'nullable|array'
        ]);

        try {
            $message = CloudMessage::withTarget('topic', $request->topic)
                ->withNotification(Notification::create($request->title, $request->body))
                ->withData($request->data ?? []);

            $this->messaging->send($message);

            return response()->json([
                'message' => 'Notification sent successfully to topic'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to send notification to topic',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Subscribe device token ke topic
     */
    public function subscribeToTopic(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'topic' => 'required|string'
        ]);

        try {
            $this->messaging->subscribeToTopic($request->topic, [$request->token]);

            return response()->json([
                'message' => 'Successfully subscribed to topic'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to subscribe to topic',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unsubscribe device token dari topic
     */
    public function unsubscribeFromTopic(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'topic' => 'required|string'
        ]);

        try {
            $this->messaging->unsubscribeFromTopic($request->topic, [$request->token]);

            return response()->json([
                'message' => 'Successfully unsubscribed from topic'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to unsubscribe from topic',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mengirim notifikasi H-3 sebelum masa titip berakhir
     */
    public function sendH3Notification()
    {
        try {
            $targetDate = now()->addDays(3)->toDateString();
            \Log::info('Checking for items expiring on: ' . $targetDate);

            // Ambil semua penitipan yang akan berakhir dalam 3 hari
            $penitipans = \App\Models\Penitipan::whereDate('batas_penitipan', '=', $targetDate)
                ->with(['penitip', 'barangs'])
                ->get();

            \Log::info('Found ' . $penitipans->count() . ' penitipan(s) that will expire');

            foreach ($penitipans as $penitipan) {
                // Hitung barang yang masih tersedia dengan case insensitive
                $barangCount = $penitipan->barangs->filter(function($barang) {
                    return in_array(strtolower($barang->status_barang), ['tersedia']);
                })->count();
                
                \Log::info('Penitipan ID: ' . $penitipan->id_penitipan . ' has ' . $barangCount . ' available items');
                
                // Hanya kirim notifikasi jika ada barang yang masih tersedia
                if ($barangCount > 0) {
                    // Kirim notifikasi ke topic berdasarkan id_penitip
                    $topic = 'penitip_' . $penitipan->id_penitip;
                    \Log::info('Sending H-3 notification to topic: ' . $topic);
                    
                    $message = CloudMessage::withTarget('topic', $topic)
                        ->withNotification(Notification::create(
                            'Pengingat Masa Titip',
                            "Anda memiliki {$barangCount} barang yang masa titipnya akan berakhir dalam 3 hari"
                        ))
                        ->withData([
                            'id_penitipan' => (string)$penitipan->id_penitipan,
                            'type' => 'h3_notification'
                        ]);

                    $this->messaging->send($message);
                    \Log::info('H-3 notification sent successfully to topic: ' . $topic);
                }
            }

            return response()->json([
                'message' => 'H-3 notifications sent successfully',
                'checked_date' => $targetDate,
                'penitipan_count' => $penitipans->count()
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send H-3 notifications: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to send H-3 notifications',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mengirim notifikasi pada hari H masa titip berakhir
     */
    public function sendHariHNotification()
    {
        try {
            // Ambil semua penitipan yang berakhir hari ini
            $penitipans = \App\Models\Penitipan::whereDate('batas_penitipan', '=', now()->toDateString())
                ->with(['penitip', 'barangs'])
                ->get();

            foreach ($penitipans as $penitipan) {
                // Kirim notifikasi ke topic berdasarkan id_penitip
                $topic = 'penitip_' . $penitipan->id_penitip;
                $barangCount = $penitipan->barangs->count();
                
                $message = CloudMessage::withTarget('topic', $topic)
                    ->withNotification(Notification::create(
                        'Masa Titip Berakhir',
                        "Anda memiliki {$barangCount} barang yang masa titipnya berakhir hari ini"
                    ))
                    ->withData([
                        'id_penitipan' => (string)$penitipan->id_penitipan,
                        'type' => 'hari_h_notification'
                    ]);

                $this->messaging->send($message);

                // Update status barang menjadi "Masa Titip Habis"
                foreach ($penitipan->barangs as $barang) {
                    if ($barang->status_barang === 'Tersedia') {
                        $barang->status_barang = 'Masa Titip Habis';
                        $barang->save();
                    }
                }
            }

            return response()->json([
                'message' => 'Hari H notifications sent successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to send Hari H notifications',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function sendDonasiNotification($id_penitip)
    {
        try {
            $topic = 'penitip_' . $id_penitip;
            \Log::info('Sending donation notification to topic: ' . $topic);
            
            $message = CloudMessage::withTarget('topic', $topic)
                ->withNotification(Notification::create(
                    'Donasi Baru',
                    "Barang anda berhasil di donasikan"
                ))
                ->withData([
                    'type' => 'donasi_notification'
                ]);

            $this->messaging->send($message);
            \Log::info('Donation notification sent successfully to topic: ' . $topic);

            return response()->json([
                'message' => 'Donation notification sent successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send donation notification: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to send donation notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Subscribe penitip ke topic notifikasi
     */
    public function subscribePenitip($id_penitip, $token)
    {
        try {
            $topic = 'penitip_' . $id_penitip;
            $this->messaging->subscribeToTopic($topic, [$token]);
            
            return response()->json([
                'message' => 'Successfully subscribed penitip to notifications'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to subscribe penitip',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Subscribe device ke topic penitip_{id_penitip} dari parameter
     */
    public function subscribePenitipFromRequest($id_penitip, $token)
    {
        $topic = 'penitip_' . $id_penitip;
        \Log::info('SubscribePenitipFromRequest: Subscribe to topic: ' . $topic . ' with token: ' . $token);
        try {
            $this->messaging->subscribeToTopic($topic, [$token]);
            return response()->json([
                'message' => 'Successfully subscribed to topic ' . $topic
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to subscribe to topic',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 
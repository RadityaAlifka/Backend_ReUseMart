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

    /**
     * Subscribe pembeli ke topic notifikasi
     */
    public function subscribePembeliFromRequest($id_pembeli, $token)
    {
        $topic = 'pembeli_' . $id_pembeli;
        \Log::info('SubscribePembeliFromRequest: Subscribe to topic: ' . $topic . ' with token: ' . $token);
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

    /**
     * Subscribe kurir ke topic notifikasi
     */
    public function subscribeKurirFromRequest($id_pegawai, $token)
    {
        $topic = 'kurir_' . $id_pegawai;
        \Log::info('Attempting to subscribe kurir to topic', [
            'topic' => $topic,
            'token' => $token,
            'id_pegawai' => $id_pegawai
        ]);

        try {
            $result = $this->messaging->subscribeToTopic($topic, [$token]);
            \Log::info('Successfully subscribed kurir to topic', [
                'topic' => $topic,
                'result' => $result
            ]);
            
            return response()->json([
                'message' => 'Successfully subscribed to topic ' . $topic,
                'topic' => $topic,
                'token' => $token
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to subscribe kurir to topic', [
                'topic' => $topic,
                'error' => $e->getMessage(),
                'id_pegawai' => $id_pegawai
            ]);
            
            return response()->json([
                'message' => 'Failed to subscribe to topic',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Kirim notifikasi jadwal pengiriman
     */
    public function sendDeliveryScheduleNotification($pengiriman)
    {
        try {
            // Dapatkan data yang diperlukan
            $transaksi = $pengiriman->transaksi;
            $pembeli = $transaksi->pembeli;
            $kurir = $pengiriman->pegawai;
            
            // Logging untuk debug
            \Log::info('Sending delivery schedule notifications', [
                'pengiriman_id' => $pengiriman->id_pengiriman,
                'kurir_id' => $kurir ? $kurir->id_pegawai : 'null',
                'pembeli_id' => $pembeli ? $pembeli->id_pembeli : 'null',
            ]);
            
            // Format tanggal pengiriman
            $tanggal = \Carbon\Carbon::parse($pengiriman->tanggal_pengiriman)->format('d M Y');
            
            // Notifikasi untuk pembeli
            if ($pembeli) {
                try {
                    $topic_pembeli = 'pembeli_' . $pembeli->id_pembeli;
                    \Log::info('Sending notification to pembeli', ['topic' => $topic_pembeli]);
                    
                    $message_pembeli = CloudMessage::withTarget('topic', $topic_pembeli)
                        ->withNotification(Notification::create(
                            'Update Jadwal Pengiriman',
                            "Pesanan Anda akan dikirim pada tanggal {$tanggal}"
                        ))
                        ->withData([
                            'type' => 'delivery_schedule',
                            'id_pengiriman' => (string)$pengiriman->id_pengiriman
                        ]);
                    $this->messaging->send($message_pembeli);
                    \Log::info('Successfully sent notification to pembeli');
                } catch (\Exception $e) {
                    \Log::error('Failed to send notification to pembeli: ' . $e->getMessage());
                }
            }
            
            // Notifikasi untuk kurir
            if ($kurir) {
                try {
                    $topic_kurir = 'kurir_' . $kurir->id_pegawai;
                    \Log::info('Sending notification to kurir', [
                        'topic' => $topic_kurir,
                        'kurir_id' => $kurir->id_pegawai,
                        'jabatan' => $kurir->jabatan ? $kurir->jabatan->nama_jabatan : 'unknown'
                    ]);
                    
                    $message_kurir = CloudMessage::withTarget('topic', $topic_kurir)
                        ->withNotification(Notification::create(
                            'Jadwal Pengiriman Baru',
                            "Anda memiliki jadwal pengiriman baru pada tanggal {$tanggal}"
                        ))
                        ->withData([
                            'type' => 'delivery_schedule',
                            'id_pengiriman' => (string)$pengiriman->id_pengiriman
                        ]);
                    $this->messaging->send($message_kurir);
                    \Log::info('Successfully sent notification to kurir');
                } catch (\Exception $e) {
                    \Log::error('Failed to send notification to kurir: ' . $e->getMessage(), [
                        'kurir_id' => $kurir->id_pegawai,
                        'error' => $e->getMessage()
                    ]);
                }
            } else {
                \Log::warning('No kurir found for pengiriman', ['pengiriman_id' => $pengiriman->id_pengiriman]);
            }
            
            // Notifikasi untuk penjual
            $detailTransaksi = $transaksi->detailtransaksi;
            if ($detailTransaksi) {
                $barang = $detailTransaksi->barang;
                if ($barang) {
                    $penitipan = $barang->penitipan;
                    if ($penitipan) {
                        $penitip = $penitipan->penitip;
                        if ($penitip) {
                            $topic_penjual = 'penitip_' . $penitip->id_penitip;
                            $message_penjual = CloudMessage::withTarget('topic', $topic_penjual)
                                ->withNotification(Notification::create(
                                    'Update Pengiriman Barang',
                                    "Barang Anda akan dikirim pada tanggal {$tanggal}"
                                ))
                                ->withData([
                                    'type' => 'delivery_schedule',
                                    'id_pengiriman' => (string)$pengiriman->id_pengiriman
                                ]);
                            $this->messaging->send($message_penjual);
                        }
                    }
                }
            }

            \Log::info('Delivery schedule notifications completed');
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to send delivery schedule notifications: ' . $e->getMessage());
            return false;
        }
    }

    public function NotifyPengambilanPenitip($id_penitip)
    {
        try {
            $topic = 'penitip_' . $id_penitip;
            \Log::info('Attempting to send pengambilan notification to penitip', [
                'topic' => $topic,
                'id_penitip' => $id_penitip
            ]);

            $message = CloudMessage::withTarget('topic', $topic)
                ->withNotification(Notification::create(
                    'Konfirmasi Pengambilan Barang',
                    'Barang Anda telah berhasil diambil'
                ))
                ->withData([
                    'type' => 'pengambilan_notification',
                    'id_penitip' => (string)$id_penitip
                ]);

            $result = $this->messaging->send($message);
            
            \Log::info('Pengambilan notification sent successfully', [
                'topic' => $topic,
                'result' => $result,
                'message' => $message
            ]);

            return response()->json([
                'message' => 'Notification sent successfully to penitip',
                'topic' => $topic
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send pengambilan notification to penitip', [
                'topic' => 'penitip_' . $id_penitip,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Failed to send notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function NotifyPengambilanPembeli($id_pembeli)
    {
        $topic = 'pembeli_' . $id_pembeli;
        $message = CloudMessage::withTarget('topic', $topic)
            ->withNotification(Notification::create(
                'Pengambilan Barang',
                "Barang anda telah diambil"
            ))
            ->withData([
                'type' => 'pengambilan_pembeli_notification'
            ]);
            if (!in_array(strtolower($barang->status_barang), ['masa titip habis', 'menunggu donasi'])) {
    return response()->json(['message' => 'Barang tidak dapat didonasikan'], 400);
}
    }

    // Tambahkan method untuk verifikasi subscription
    public function verifyTopicSubscription($topic)
    {
        try {
            \Log::info('Verifying topic subscription', ['topic' => $topic]);
            
            // Kirim test notification dengan flag khusus
            $message = CloudMessage::withTarget('topic', $topic)
                ->withNotification(Notification::create(
                    'Subscription Test',
                    'Testing topic subscription'
                ))
                ->withData([
                    'type' => 'subscription_test',
                    'timestamp' => now()->toIso8601String()
                ]);

            $result = $this->messaging->send($message);
            
            \Log::info('Topic subscription verification sent', [
                'topic' => $topic,
                'result' => $result
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to verify topic subscription', [
                'topic' => $topic,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    public function notifyBarangLaku($barang)
    {
        try {
            $topic = 'penitip_' . $barang->penitipan->id_penitip;
            \Log::info('Sending barang laku notification to topic: ' . $topic);
            
            $message = CloudMessage::withTarget('topic', $topic)
                ->withNotification(Notification::create(
                    'Barang Laku',
                    "Barang '{$barang->nama_barang}' telah terjual"
                ))
                ->withData([
                    'id_barang' => (string)$barang->id_barang,
                    'type' => 'barang_laku_notification'
                ]);

            $this->messaging->send($message);
            \Log::info('Barang laku notification sent successfully to topic: ' . $topic);

            return response()->json([
                'message' => 'Barang laku notification sent successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send barang laku notification: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to send barang laku notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 
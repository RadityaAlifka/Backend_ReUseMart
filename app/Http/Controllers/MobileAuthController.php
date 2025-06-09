<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\NotificationController;

// Import the specific models for each user type
use App\Models\Pembeli;
use App\Models\Penitip;
use App\Models\Pegawai;
use App\Models\Organisasi;
use App\Models\Barang; // Pastikan model Barang diimpor
use App\Models\Penitipan; // Pastikan model Penitipan diimpor
use App\Models\Pengiriman; // Import model Pengiriman
use App\Models\Jabatan; // Import model Jabatan jika digunakan


class MobileAuthController
{
    protected $notificationController;

    public function __construct(NotificationController $notificationController)
    {
        $this->notificationController = $notificationController;
    }

    public function login(Request $request)
    {
        \Log::info('Login request received', ['request' => $request->all()]);
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
            case 'pembeli':
                $pembeli = \App\Models\Pembeli::where('user_id', $user->id)->first();
                if ($pembeli) {
                    \Log::info('Subscribe to topic: ' . 'pembeli_' . $pembeli->id_pembeli . ' with token: ' . $request->fcm_token);
                    $this->notificationController->subscribePembeliFromRequest($pembeli->id_pembeli, $request->fcm_token);
                    $fcmSubscribed = true;
                }
                break;
            case 'pegawai':
                $pegawai = \App\Models\Pegawai::where('user_id', $user->id)->first();
                if ($pegawai) {
                    // Cek relasi jabatan, kemudian akses nama_jabatan
                    if ($pegawai->jabatan && $pegawai->jabatan->nama_jabatan === 'kurir') {
                        \Log::info('Subscribe to topic: ' . 'kurir_' . $pegawai->id_pegawai . ' with token: ' . $request->fcm_token);
                        $this->notificationController->subscribeKurirFromRequest($pegawai->id_pegawai, $request->fcm_token);
                        $fcmSubscribed = true;
                    }
                }
                break;
            case 'organisasi':
                $organisasi = \App\Models\Organisasi::where('user_id', $user->id)->first();
                if ($organisasi) {
                    \Log::info('Subscribe to topic: ' . 'organisasi_' . $organisasi->id_organisasi . ' with token: ' . $request->fcm_token);
                    $this->notificationController->subscribeOrganisasiFromRequest($organisasi->id_organisasi, $request->fcm_token);
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

        // Unsubscribe from notifications based on level
        if ($user) {
            $fcmUnsubscribed = false;
            switch ($user->level) {
                case 'penjual':
                    $penitip = Penitip::where('user_id', $user->id)->first();
                    if ($penitip && $request->fcm_token) {
                        $this->notificationController->unsubscribeFromTopic([
                            'token' => $request->fcm_token,
                            'topic' => 'penitip_' . $penitip->id_penitip
                        ]);
                        $fcmUnsubscribed = true;
                    }
                    break;
                case 'pembeli':
                    $pembeli = Pembeli::where('user_id', $user->id)->first();
                    if ($pembeli && $request->fcm_token) {
                        $this->notificationController->unsubscribeFromTopic([
                            'token' => $request->fcm_token,
                            'topic' => 'pembeli_' . $pembeli->id_pembeli
                        ]);
                        $fcmUnsubscribed = true;
                    }
                    break;
                case 'pegawai':
                    $pegawai = Pegawai::where('user_id', $user->id)->first();
                    // Cek relasi jabatan, kemudian akses nama_jabatan
                    if ($pegawai && $pegawai->jabatan && $pegawai->jabatan->nama_jabatan === 'kurir' && $request->fcm_token) {
                        $this->notificationController->unsubscribeFromTopic([
                            'token' => $request->fcm_token,
                            'topic' => 'kurir_' . $pegawai->id_pegawai
                        ]);
                        $fcmUnsubscribed = true;
                    }
                    break;
                case 'organisasi':
                    $organisasi = Organisasi::where('user_id', $user->id)->first();
                    if ($organisasi && $request->fcm_token) {
                        $this->notificationController->unsubscribeFromTopic([
                            'token' => $request->fcm_token,
                            'topic' => 'organisasi_' . $organisasi->id_organisasi
                        ]);
                        $fcmUnsubscribed = true;
                    }
                    break;
            }
            $request->user()->tokens()->where('name', 'mobile_token')->delete();

            return response()->json([
                'message' => 'Logged out from mobile',
                'fcm_unsubscribed' => $fcmUnsubscribed
            ]);
        }

        return response()->json(['message' => 'User not authenticated'], 401);
    }

    public function updateFcmToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string'
        ]);

        $user = $request->user();
        $fcmSubscribed = false;

        if ($user) {
            switch($user->level) {
                case 'penjual':
                    $penitip = Penitip::where('user_id', $user->id)->first();
                    if ($penitip) {
                        if ($request->old_fcm_token) {
                            $this->notificationController->unsubscribeFromTopic([
                                'token' => $request->old_fcm_token,
                                'topic' => 'penitip_' . $penitip->id_penitip
                            ]);
                        }
                        $this->notificationController->subscribePenitipFromRequest($penitip->id_penitip, $request->fcm_token);
                        $fcmSubscribed = true;
                    }
                    break;
                case 'pembeli':
                    $pembeli = Pembeli::where('user_id', $user->id)->first();
                    if ($pembeli) {
                        if ($request->old_fcm_token) {
                            $this->notificationController->unsubscribeFromTopic([
                                'token' => $request->old_fcm_token,
                                'topic' => 'pembeli_' . $pembeli->id_pembeli
                            ]);
                        }
                        $this->notificationController->subscribePembeliFromRequest($pembeli->id_pembeli, $request->fcm_token);
                        $fcmSubscribed = true;
                    }
                    break;
                case 'pegawai':
                    $pegawai = Pegawai::where('user_id', $user->id)->first();
                    // Cek relasi jabatan, kemudian akses nama_jabatan
                    if ($pegawai && $pegawai->jabatan && $pegawai->jabatan->nama_jabatan === 'kurir') {
                        if ($request->old_fcm_token) {
                            $this->notificationController->unsubscribeFromTopic([
                                'token' => $request->old_fcm_token,
                                'topic' => 'kurir_' . $pegawai->id_pegawai
                            ]);
                        }
                        $this->notificationController->subscribeKurirFromRequest($pegawai->id_pegawai, $request->fcm_token);
                        $fcmSubscribed = true;
                    }
                    break;
                case 'organisasi':
                    $organisasi = Organisasi::where('user_id', $user->id)->first();
                    if ($organisasi) {
                        if ($request->old_fcm_token) {
                            $this->notificationController->unsubscribeFromTopic([
                                'token' => $request->old_fcm_token,
                                'topic' => 'organisasi_' . $organisasi->id_organisasi
                            ]);
                        }
                        $this->notificationController->subscribeOrganisasiFromRequest($organisasi->id_organisasi, $request->fcm_token);
                        $fcmSubscribed = true;
                    }
                    break;
            }
        }

        return response()->json([
            'message' => 'FCM token updated successfully',
            'fcm_subscribed' => $fcmSubscribed
        ]);
    }

    /**
     * Get the authenticated user's profile based on their level/role.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProfile(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $profileData = [
            'id' => $user->id,
            'name' => $user->nama,
            'email' => $user->email,
            'level' => $user->level,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];

        switch ($user->level) {
            case 'pembeli':
                $pembeli = Pembeli::where('user_id', $user->id)->with('alamats')->first();
                if ($pembeli) {
                    $profileData['pembeli_id'] = $pembeli->id_pembeli;
                    $profileData['name'] = $pembeli->nama_pembeli;
                    $profileData['telepon'] = $pembeli->telepon_pembeli;
                    $profileData['tanggal_lahir'] = $pembeli->tanggal_lahir_pembeli;
                    $profileData['poin'] = $pembeli->poin;
                    $profileData['alamats'] = $pembeli->alamats ? $pembeli->alamats->toArray() : [];
                }
                break;
            case 'penjual':
                $penitip = Penitip::where('user_id', $user->id)
                            ->with(['penitipans' => function($query) {
                                $query->with('barangs');
                            }])
                            ->first();

                if ($penitip) {
                    $profileData['penitip_id'] = $penitip->id_penitip;
                    $profileData['name'] = $penitip->nama_penitip;
                    $profileData['telepon'] = $penitip->telepon_penitip;
                    $profileData['poin'] = $penitip->poin;
                    $profileData['saldo'] = $penitip->saldo;

                    $consignedItems = [];
                    foreach ($penitip->penitipans as $penitipan) {
                        foreach ($penitipan->barangs as $barang) {
                            $consignedItems[] = [
                                'id_barang' => $barang->id_barang,
                                'nama_barang' => $barang->nama_barang,
                                'deskripsi_barang' => $barang->deskripsi_barang,
                                'harga' => $barang->harga,
                                'status_barang' => $barang->status_barang,
                                'tanggal_penitipan' => $penitipan->tanggal_penitipan,
                                'batas_penitipan' => $penitipan->batas_penitipan,
                                'gambar1' => $barang->gambar1,
                                'gambar2' => $barang->gambar2,
                            ];
                        }
                    }
                    $profileData['consigned_items'] = $consignedItems;
                }
                break;
            case 'pegawai':
                // Eager load jabatan dan pengiriman yang terkait
                $pegawai = Pegawai::where('user_id', $user->id)->with('jabatan')->first();
                if ($pegawai) {
                    $profileData['pegawai_id'] = $pegawai->id_pegawai;
                    $profileData['name'] = $pegawai->nama_pegawai;
                    $profileData['jabatan'] = $pegawai->jabatan; // Objek jabatan
                    $profileData['telepon'] = $pegawai->no_telp; // Pastikan ini nama kolom yang benar untuk nomor telepon pegawai

                    // Jika pegawai adalah kurir, muat pengiriman yang ditugaskan
                    if ($pegawai->jabatan && $pegawai->jabatan->nama_jabatan === 'kurir') {
                        $deliveries = Pengiriman::where('id_pegawai', $pegawai->id_pegawai)
                                    ->with([
                                        'transaksi.pembeli.alamats', // Ambil data pembeli dan alamatnya
                                    ])
                                    ->orderBy('tanggal_pengiriman', 'desc')
                                    ->get();

                        $assignedDeliveries = [];
                        foreach ($deliveries as $delivery) {
                            $customerName = $delivery->transaksi?->pembeli?->nama_pembeli ?? 'Pelanggan Tidak Diketahui';
                            $customerAddress = 'Alamat Tidak Tersedia';

                            if ($delivery->transaksi?->pembeli?->alamats && $delivery->transaksi->pembeli->alamats->isNotEmpty()) {
                                $firstAddress = $delivery->transaksi->pembeli->alamats->first();
                                $customerAddress = ($firstAddress->detail_alamat ?? '') . ', ' .
                                                   ($firstAddress->kelurahan ?? '') . ', ' .
                                                   ($firstAddress->kecamatan ?? '') . ', ' .
                                                   ($firstAddress->kabupaten ?? '') . ' ' .
                                                   ($firstAddress->kode_pos ?? '');
                            }

                            $assignedDeliveries[] = [
                                'id_pengiriman' => $delivery->id_pengiriman,
                                'id_transaksi' => $delivery->id_transaksi,
                                'tanggal_pengiriman' => $delivery->tanggal_pengiriman,
                                'status_pengiriman' => $delivery->status_pengiriman,
                                'ongkir' => $delivery->ongkir,
                                'customer_name' => $customerName,
                                'customer_address' => $customerAddress,
                            ];
                        }
                        $profileData['assigned_deliveries'] = $assignedDeliveries;
                    }
                }
                break;
            case 'organisasi':
                $organisasi = Organisasi::where('user_id', $user->id)->first();
                if ($organisasi) {
                    $profileData['organisasi_id'] = $organisasi->id_organisasi;
                    $profileData['nama_organisasi'] = $organisasi->nama_organisasi;
                    $profileData['alamat_organisasi'] = $organisasi->alamat_organisasi;
                    $profileData['telepon_organisasi'] = $organisasi->telepon_organisasi;
                }
                break;
        }

        return response()->json($profileData, 200);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validatedUser = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
        ]);

        $user->update($validatedUser);

        switch ($user->level) {
            case 'pembeli':
                $pembeli = Pembeli::where('user_id', $user->id)->first();
                if ($pembeli) {
                    $validatedPembeli = $request->validate([
                        'telepon_pembeli' => 'sometimes|string|max:20',
                        'tanggal_lahir_pembeli' => 'sometimes|date',
                    ]);
                    $pembeli->update($validatedPembeli);
                }
                break;
            case 'penjual':
                $penitip = Penitip::where('user_id', $user->id)->first();
                if ($penitip) {
                    $validatedPenitip = $request->validate([
                        'telepon_penitip' => 'sometimes|string|max:20',
                        'alamat_penitip' => 'sometimes|string|max:255',
                    ]);
                    $penitip->update($validatedPenitip);
                }
                break;
            case 'pegawai':
                $pegawai = Pegawai::where('user_id', $user->id)->first();
                if ($pegawai) {
                    $validatedPegawai = $request->validate([
                        'telepon_pegawai' => 'sometimes|string|max:20',
                    ]);
                    $pegawai->update($validatedPegawai);
                }
                break;
            case 'organisasi':
                $organisasi = Organisasi::where('user_id', $user->id)->first();
                if ($organisasi) {
                    $validatedOrganisasi = $request->validate([
                        'nama_organisasi' => 'sometimes|string|max:255',
                        'alamat_organisasi' => 'sometimes|string|max:255',
                        'telepon_organisasi' => 'sometimes|string|max:20',
                    ]);
                    $organisasi->update($validatedOrganisasi);
                }
                break;
        }

        $user->fresh();

        $relatedProfile = null;
        switch ($user->level) {
            case 'pembeli':
                $relatedProfile = $user->pembeli;
                break;
            case 'penjual':
                $relatedProfile = $user->penitip;
                break;
            case 'pegawai':
                $relatedProfile = $user->pegawai;
                break;
            case 'organisasi':
                $relatedProfile = $user->organisasi;
                break;
        }

        return response()->json([
            'message' => 'Profile updated successfully!',
            'user' => $user,
            'profile_data' => $relatedProfile
        ], 200);
    }
}
<?php

namespace App\Http\Controllers\Penyewa;

use App\Http\Controllers\Controller;
use App\Models\BuktiBayar;
use App\Models\Kamar;
use App\Models\TipeKamar;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    /**
     * Store booking data and redirect to payment
     */
    public function store(Request $request, $id)
    {
        $user = auth()->user();
        
        // Check if tenant already has a room - can only extend, not book new
        $existingRoom = $user->activeRoom;
        if ($existingRoom) {
            return redirect()->route('tenant.extend-payment')
                ->with('error', 'Anda sudah memiliki kamar yang disewa. Silakan perpanjang durasi sewa.');
        }
        
        // Check if tenant has a rejected payment - must re-upload first
        $rejectedTransaction = Transaksi::where('penyewa_id', $user->id)
            ->whereIn('status', ['rejected_by_admin', 'rejected_by_owner'])
            ->first();
        if ($rejectedTransaction) {
            return redirect()->route('tenant.booking.retry', $rejectedTransaction->id)
                ->with('error', 'Anda memiliki pembayaran yang ditolak. Silakan upload ulang bukti pembayaran terlebih dahulu.');
        }
        
        $request->validate([
            'phone' => 'required|digits_between:8,15',
            'check_in_date' => 'required|date',
            'duration' => 'required|integer|in:1,3,6,12',
        ]);

        $profile = $user->tenantProfile ?? new \App\Models\Penyewa(['user_id' => $user->id]);
        $profile->phone = $request->phone;
        $profile->save();

        // Get room type for pricing
        $roomType = TipeKamar::findOrFail($id);

        // Get selected room if provided
        $selectedRoom = null;
        $roomNumber = null;
        if ($request->has('kamar_id')) {
            $selectedRoom = Kamar::where('id', $request->kamar_id)
                ->where('tipe_kamar_id', $id)
                ->hasAvailableSlot()
                ->first();
            $roomNumber = $selectedRoom?->room_number;
        }

        // Calculate total amount using rent_per_person (for Duo rooms, this is price/2)
        $pricePerMonth = $roomType->rent_per_person;
        $totalAmount = $pricePerMonth * $request->duration;

        // Generate unique invoice number
        $invoiceNumber = $this->generateInvoiceNumber();

        // Store booking data in session for payment page
        session([
            'booking' => [
                'tipe_kamar_id' => $id,
                'room_type_name' => $roomType->name,
                'kamar_id' => $selectedRoom?->id,
                'room_number' => $roomNumber,
                'capacity' => $roomType->capacity,
                'check_in_date' => $request->check_in_date,
                'duration' => $request->duration,
                'phone' => $request->phone,
                'price_per_month' => $pricePerMonth,
                'total_amount' => $totalAmount,
                'invoice_number' => $invoiceNumber,
            ]
        ]);

        return redirect()->route('tenant.booking.payment');
    }

    /**
     * Show payment page with booking data
     */
    public function showPayment()
    {
        $booking = session('booking');

        if (!$booking) {
            return redirect()->route('welcome')->with('error', 'Sesi booking tidak ditemukan. Silakan mulai dari awal.');
        }

        // Get owner's business settings for bank info (assuming first owner for now)
        $owner = User::where('role', 'owner')->first();
        $businessSettings = $owner ? $owner->businessSettings : null;

        return view('penyewa.pembayaran', [
            'booking' => $booking,
            'pemilik' => $owner,
            'businessSettings' => $businessSettings,
        ]);
    }

    /**
     * Process payment confirmation with proof upload
     */
    public function confirmPayment(Request $request)
    {
        $request->validate([
            'payment_proof' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'sender_bank' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z\s\/\.\-]+$/'],
            'sender_name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s\.\-]+$/'],
        ]);

        // Check if tenant has a rejected payment - must resolve first
        $user = Auth::user();
        $rejectedTransaction = Transaksi::where('penyewa_id', $user->id)
            ->whereIn('status', ['rejected_by_admin', 'rejected_by_owner'])
            ->first();
        if ($rejectedTransaction) {
            return response()->json([
                'success' => false,
                'message' => 'Anda memiliki pembayaran yang ditolak. Silakan selesaikan terlebih dahulu.'
            ], 400);
        }

        $booking = session('booking');

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi booking tidak ditemukan.'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $owner = User::where('role', 'owner')->first();

            // Use selected room from session, or find one if not specified
            $roomId = $booking['kamar_id'] ?? null;
            $room = null;
            if (!$roomId) {
                $roomType = TipeKamar::findOrFail($booking['tipe_kamar_id']);
                $room = Kamar::where('tipe_kamar_id', $roomType->id)
                    ->hasAvailableSlot()
                    ->first();
                $roomId = $room?->id;
            } else {
                $room = Kamar::find($roomId);
            }

            // Validate room has available slot
            if (!$room || !$room->hasAvailableSlot()) {
                throw new \Exception('Kamar tidak tersedia atau sudah penuh.');
            }

            // Calculate period dates
            $checkInDate = \Carbon\Carbon::parse($booking['check_in_date']);
            $periodEndDate = $checkInDate->copy()->addMonths((int)$booking['duration']);

            // Create transaction
            $transaction = Transaksi::create([
                'owner_id' => $owner->id,
                'penyewa_id' => $user->id,
                'kamar_id' => $roomId,
                'amount' => $booking['total_amount'],
                'duration_months' => $booking['duration'],
                'period_start_date' => $checkInDate,
                'period_end_date' => $periodEndDate,
                'reference_number' => $booking['invoice_number'],
                'invoice_number' => $booking['invoice_number'],
                'payment_date' => now(),
                'due_date' => now()->addDays(1), // 1 day for verification
                'status' => 'pending_verification',
                'payment_method' => 'bank_transfer',
                'sender_bank' => $request->sender_bank,
                'sender_name' => $request->sender_name,
            ]);

            // Upload payment proof
            $path = $request->file('payment_proof')->store('payment-proofs/' . $user->id, 'public');

            BuktiBayar::create([
                'transaksi_id' => $transaction->id,
                'file_path' => $path,
                'file_type' => $request->file('payment_proof')->getClientMimeType(),
                'uploaded_by' => $user->id,
                'uploaded_at' => now(),
                'verified_status' => 'pending',
            ]);

            // NOTE: Room is NOT updated here!
            // Room assignment (current_tenant_id, status, lease_dates) only happens 
            // when payment is verified by owner in TransactionVerificationController::verify()
            // This prevents room from becoming active if payment is rejected

            // Clear booking session
            session()->forget('booking');

            // Store transaction ID for success page
            session(['completed_transaction_id' => $transaction->id]);

            // Create notification for owner
            \App\Models\Notification::create([
                'user_id' => $owner->id,
                'type' => 'payment_received',
                'category' => 'finance',
                'title' => 'Pembayaran Baru',
                'message' => $user->name . ' (Kamar ' . ($room->room_number ?? 'Auto') . ') telah mengupload bukti transfer sebesar Rp ' . number_format($booking['total_amount'], 0, ',', '.'),
                'related_entity_type' => 'transaction',
                'related_entity_id' => $transaction->id,
                'priority' => 'high',
                'action_required' => true,
                'status' => 'unread',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil dikonfirmasi!',
                'redirect' => route('tenant.booking.success'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate unique invoice number
     * Format: INV-YYMM-XXXXX
     */
    private function generateInvoiceNumber(): string
    {
        $prefix = 'INV-' . date('ym') . '-';

        // Get the last invoice number for this month
        $lastTransaction = Transaksi::where('invoice_number', 'like', $prefix . '%')
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastTransaction) {
            // Extract the number and increment
            $lastNumber = (int) substr($lastTransaction->invoice_number, -5);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Show extend payment page for tenants who already have a room
     */
    public function showExtendPayment()
    {
        $user = Auth::user();
        
        // Check if tenant has a rejected payment - must resolve first
        $rejectedTransaction = Transaksi::where('penyewa_id', $user->id)
            ->whereIn('status', ['rejected_by_admin', 'rejected_by_owner'])
            ->first();
        if ($rejectedTransaction) {
            return redirect()->route('tenant.booking.retry', $rejectedTransaction->id)
                ->with('error', 'Anda memiliki pembayaran yang ditolak. Silakan selesaikan terlebih dahulu sebelum membuat transaksi baru.');
        }
        
        // Get tenant's current room
        $currentRoom = $user->activeRoom;
        if ($currentRoom) {
            $currentRoom->load('roomType');
        }
        
        if (!$currentRoom) {
            return redirect()->route('welcome')
                ->with('error', 'Anda belum memiliki kamar yang disewa.');
        }
        
        // Get owner's business settings for bank info
        $owner = User::where('role', 'owner')->first();
        $businessSettings = $owner ? $owner->businessSettings : null;
        
        // Get booking session data if exists
        $extendBooking = session('extend_booking');
        
        return view('penyewa.perpanjang-sewa', [
            'kamar' => $currentRoom,
            'pemilik' => $owner,
            'businessSettings' => $businessSettings,
            'extendBooking' => $extendBooking,
        ]);
    }

    /**
     * Store extend payment data
     */
    public function storeExtendPayment(Request $request)
    {
        $request->validate([
            'duration' => 'required|integer|min:1|max:24',
        ]);

        $user = Auth::user();
        
        // Check if tenant has a rejected payment - must resolve first
        $rejectedTransaction = Transaksi::where('penyewa_id', $user->id)
            ->whereIn('status', ['rejected_by_admin', 'rejected_by_owner'])
            ->first();
        if ($rejectedTransaction) {
            return redirect()->route('tenant.booking.retry', $rejectedTransaction->id)
                ->with('error', 'Anda memiliki pembayaran yang ditolak. Silakan selesaikan terlebih dahulu.');
        }
        
        // Get tenant's current room
        $currentRoom = $user->activeRoom;
        if ($currentRoom) {
            $currentRoom->load('roomType');
        }
        
        if (!$currentRoom) {
            return back()->with('error', 'Anda belum memiliki kamar yang disewa.');
        }
        
        // Calculate total amount
        $pricePerMonth = $currentRoom->roomType->rent_per_person;
        $totalAmount = $pricePerMonth * $request->duration;
        $invoiceNumber = $this->generateInvoiceNumber();
        
        // Store in session
        session([
            'extend_booking' => [
                'kamar_id' => $currentRoom->id,
                'room_number' => $currentRoom->room_number,
                'room_type_name' => $currentRoom->roomType->name,
                'duration' => $request->duration,
                'price_per_month' => $pricePerMonth,
                'total_amount' => $totalAmount,
                'invoice_number' => $invoiceNumber,
            ]
        ]);
        
        return redirect()->route('tenant.extend-payment');
    }

    /**
     * Confirm extend payment with proof upload
     */
    public function confirmExtendPayment(Request $request)
    {
        $request->validate([
            'payment_proof' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'sender_bank' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z\s\/\.\-]+$/'],
            'sender_name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s\.\-]+$/'],
        ]);

        // Check if tenant has a rejected payment - must resolve first
        $user = Auth::user();
        $rejectedTransaction = Transaksi::where('penyewa_id', $user->id)
            ->whereIn('status', ['rejected_by_admin', 'rejected_by_owner'])
            ->first();
        if ($rejectedTransaction) {
            return response()->json([
                'success' => false,
                'message' => 'Anda memiliki pembayaran yang ditolak. Silakan selesaikan terlebih dahulu.'
            ], 400);
        }

        $extendBooking = session('extend_booking');

        if (!$extendBooking) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi perpanjangan tidak ditemukan.'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $owner = User::where('role', 'owner')->first();

            // Get current room to calculate extension period
            $currentRoom = Kamar::find($extendBooking['kamar_id']);
            
            // Extension starts from current lease end date, or now if not set
            $periodStartDate = $currentRoom->lease_end_date 
                ? \Carbon\Carbon::parse($currentRoom->lease_end_date) 
                : now();
            $periodEndDate = $periodStartDate->copy()->addMonths((int)$extendBooking['duration']);

            // Create transaction for extension
            $transaction = Transaksi::create([
                'owner_id' => $owner->id,
                'penyewa_id' => $user->id,
                'kamar_id' => $extendBooking['kamar_id'],
                'amount' => $extendBooking['total_amount'],
                'duration_months' => $extendBooking['duration'],
                'period_start_date' => $periodStartDate,
                'period_end_date' => $periodEndDate,
                'reference_number' => $extendBooking['invoice_number'],
                'invoice_number' => $extendBooking['invoice_number'],
                'payment_date' => now(),
                'due_date' => now()->addDays(1),
                'status' => 'pending_verification',
                'payment_method' => 'bank_transfer',
                'sender_bank' => $request->sender_bank,
                'sender_name' => $request->sender_name,
            ]);

            // Upload payment proof
            $path = $request->file('payment_proof')->store('payment-proofs/' . $user->id, 'public');

            BuktiBayar::create([
                'transaksi_id' => $transaction->id,
                'file_path' => $path,
                'file_type' => $request->file('payment_proof')->getClientMimeType(),
                'uploaded_by' => $user->id,
                'uploaded_at' => now(),
                'verified_status' => 'pending',
            ]);

            // Clear session
            session()->forget('extend_booking');

            // Store transaction ID for success page
            session(['completed_transaction_id' => $transaction->id]);

            // Create notification for owner
            \App\Models\Notification::create([
                'user_id' => $owner->id,
                'type' => 'payment_received',
                'category' => 'finance',
                'title' => 'Perpanjangan Sewa',
                'message' => $user->name . ' (Kamar ' . ($currentRoom->room_number ?? '-') . ') telah mengupload bukti perpanjangan sewa sebesar Rp ' . number_format($extendBooking['total_amount'], 0, ',', '.'),
                'related_entity_type' => 'transaction',
                'related_entity_id' => $transaction->id,
                'priority' => 'high',
                'action_required' => true,
                'status' => 'unread',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran perpanjangan berhasil dikonfirmasi!',
                'redirect' => route('tenant.booking.success'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download PDF receipt for a transaction
     */
    public function downloadReceipt($id)
    {
        $user = Auth::user();
        $transaction = Transaksi::with(['room.roomType', 'tenant', 'paymentProofs'])
            ->where('id', $id)
            ->where('penyewa_id', $user->id)
            ->firstOrFail();

        // Get business settings for logo/company info
        $owner = User::where('role', 'owner')->first();
        $businessSettings = $owner ? $owner->businessSettings : null;

        $pdf = app('dompdf.wrapper')->loadView('pdf.receipt', [
            'transaksiItem' => $transaction,
            'businessSettings' => $businessSettings,
        ]);

        $filename = 'Receipt-' . $transaction->invoice_number . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Show retry payment page for rejected transactions
     */
    public function showRetryPayment($transactionId)
    {
        $user = Auth::user();
        
        // Get the rejected transaction
        $transaction = Transaksi::with(['room.roomType', 'paymentProofs'])
            ->where('id', $transactionId)
            ->where('penyewa_id', $user->id)
            ->whereIn('status', ['rejected_by_admin', 'rejected_by_owner'])
            ->firstOrFail();
        
        // Get owner's business settings for bank info
        $owner = User::where('role', 'owner')->first();
        $businessSettings = $owner ? $owner->businessSettings : null;
        
        // Calculate price per month from room type
        $pricePerMonth = $transaction->room?->roomType?->rent_per_person ?? ($transaction->amount / $transaction->duration_months);
        
        return view('penyewa.retry-payment', [
            'transaksiItem' => $transaction,
            'businessSettings' => $businessSettings,
            'pricePerMonth' => $pricePerMonth,
        ]);
    }

    /**
     * Process retry payment with new proof upload
     */
    public function confirmRetryPayment(Request $request, $transactionId)
    {
        $request->validate([
            'payment_proof' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'sender_bank' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z\s\/\.\-]+$/'],
            'sender_name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s\.\-]+$/'],
        ]);

        $user = Auth::user();
        
        // Get the rejected transaction
        $transaction = Transaksi::with(['paymentProofs', 'room.roomType'])
            ->where('id', $transactionId)
            ->where('penyewa_id', $user->id)
            ->whereIn('status', ['rejected_by_admin', 'rejected_by_owner'])
            ->firstOrFail();

        try {
            DB::beginTransaction();

            // Upload new payment proof
            $path = $request->file('payment_proof')->store('payment-proofs/' . $user->id, 'public');

            BuktiBayar::create([
                'transaksi_id' => $transaction->id,
                'file_path' => $path,
                'file_type' => $request->file('payment_proof')->getClientMimeType(),
                'uploaded_by' => $user->id,
                'uploaded_at' => now(),
                'verified_status' => 'pending',
            ]);

            // Update transaction status back to pending
            $transaction->update([
                'sender_bank' => $request->sender_bank,
                'sender_name' => $request->sender_name,
                'status' => 'pending_verification',
                'admin_verified_at' => null,
                'admin_verified_by' => null,
                'admin_notes' => null,
                'owner_verified_at' => null,
                'owner_verified_by' => null,
                'owner_notes' => null,
            ]);

            // Archive the rejection notification
            \App\Models\Notification::where('user_id', $user->id)
                ->where('type', 'payment_rejected')
                ->where('related_entity_id', $transaction->id)
                ->update(['status' => 'archived']);

            // Create notification for owner (Re-upload)
            $owner = User::where('role', 'owner')->first();
            if ($owner) {
                \App\Models\Notification::create([
                    'user_id' => $owner->id,
                    'type' => 'payment_received',
                    'category' => 'finance',
                    'title' => 'Revisi Bukti Bayar',
                    'message' => $user->name . ' telah mengupload ulang bukti pembayaran invoice ' . $transaction->invoice_number,
                    'related_entity_type' => 'transaction',
                    'related_entity_id' => $transaction->id,
                    'priority' => 'high',
                    'action_required' => true,
                    'status' => 'unread',
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bukti pembayaran baru berhasil diupload! Menunggu verifikasi.',
                'redirect' => route('tenant.dashboard'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel a rejected transaction
     */
    public function cancelTransaction($transactionId)
    {
        $user = Auth::user();
        
        // Get the rejected transaction
        $transaction = Transaksi::where('id', $transactionId)
            ->where('penyewa_id', $user->id)
            ->whereIn('status', ['rejected_by_admin', 'rejected_by_owner'])
            ->first();

        if (!$transaction) {
            return redirect()->route('tenant.dashboard')
                ->with('error', 'Transaksi tidak ditemukan atau tidak dapat dibatalkan.');
        }

        try {
            DB::beginTransaction();

            // Update transaction status to cancelled
            $transaction->update([
                'status' => 'cancelled_by_tenant',
            ]);

            // Archive related notifications
            \App\Models\Notification::where('user_id', $user->id)
                ->where('type', 'payment_rejected')
                ->where('related_entity_id', $transaction->id)
                ->update(['status' => 'archived']);

            DB::commit();

            return redirect()->route('tenant.dashboard')
                ->with('success', 'Transaksi berhasil dibatalkan. Anda sekarang dapat membuat transaksi baru.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('tenant.dashboard')
                ->with('error', 'Gagal membatalkan transaksi: ' . $e->getMessage());
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Setting;
use App\Models\VendorEarning;
use App\Models\VendorProfile;
use App\Models\VendorWithdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendorController extends Controller
{
    // ── Vendor List ───────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = VendorProfile::with('user')->latest();
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('q'))      $query->where('store_name', 'like', '%' . $request->q . '%');

        $vendors = $query->paginate(25)->withQueryString();

        $stats = [
            'total'     => VendorProfile::count(),
            'pending'   => VendorProfile::where('status', 'pending')->count(),
            'active'    => VendorProfile::where('status', 'active')->count(),
            'suspended' => VendorProfile::where('status', 'suspended')->count(),
        ];

        return view('admin.vendors.index', compact('vendors', 'stats'));
    }

    // ── Vendor Detail ─────────────────────────────────────────────────────

    public function show(VendorProfile $vendor)
    {
        $vendor->load('user', 'products', 'withdrawals', 'earnings.order');
        $pendingWithdrawals  = $vendor->withdrawals()->where('status', 'pending')->get();
        $approvedWithdrawals = $vendor->withdrawals()->whereIn('status', ['approved', 'paid'])->latest()->take(10)->get();
        $products            = $vendor->products()->with('category')->latest()->paginate(15);
        return view('admin.vendors.show', compact('vendor', 'pendingWithdrawals', 'approvedWithdrawals', 'products'));
    }

    // ── KYC management ────────────────────────────────────────────────────

    public function verifyKyc(VendorProfile $vendor)
    {
        $vendor->update(['kyc_status' => 'verified', 'kyc_verified_at' => now()]);

        // Also update the user-level KYC flag so dashboard shows correctly
        $vendor->user->update(['kyc_verified' => true]);

        $msg = 'KYC verified for ' . $vendor->store_name . '.';

        // If vendor is still pending, the KYC verification does NOT auto-approve —
        // the admin still needs to explicitly approve from the vendor page.
        // The page now shows a prominent "Approve Now" banner to guide this.
        if ($vendor->isPending()) {
            $msg .= ' Vendor is still pending — click Approve to activate their store.';
        }

        return back()->with('success', $msg);
    }

    public function rejectKyc(Request $request, VendorProfile $vendor)
    {
        $request->validate(['kyc_notes' => 'required|string|max:500']);
        $vendor->update(['kyc_status' => 'rejected', 'kyc_notes' => $request->kyc_notes]);
        app(\App\Services\NotificationService::class)->send(
            $vendor->user, 'order_update',
            'KYC Verification Update',
            "Your KYC documents for \"{$vendor->store_name}\" could not be verified. Reason: {$request->kyc_notes}. Please re-submit.",
            []
        );
        return back()->with('success', 'KYC rejected and vendor notified.');
    }

    public function viewKycDocument(VendorProfile $vendor)
    {
        abort_if(!$vendor->kyc_document_path, 404);
        $path = storage_path('app/' . $vendor->kyc_document_path);
        abort_if(!file_exists($path), 404);
        return response()->file($path);
    }

    // ── Approve / Reject / Suspend ────────────────────────────────────────

    public function approve(VendorProfile $vendor)
    {
        $vendor->update([
            'status'       => 'active',
            'activated_at' => now(),
            'rejection_reason' => null,
        ]);
        // is_vendor must also be true — isActiveVendor() requires BOTH flags
        $vendor->user->update(['vendor_status' => 'active', 'is_vendor' => true]);

        // Notify vendor
        app(\App\Services\NotificationService::class)->vendorApproved($vendor);

        return back()->with('success', "Vendor \"{$vendor->store_name}\" approved and activated.");
    }

    public function reject(Request $request, VendorProfile $vendor)
    {
        $request->validate(['reason' => 'required|string|max:500']);

        $vendor->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->reason,
        ]);
        $vendor->user->update(['vendor_status' => 'rejected']);

        app(\App\Services\NotificationService::class)->vendorRejected($vendor, $request->reason);

        return back()->with('success', 'Vendor application rejected.');
    }

    public function suspend(Request $request, VendorProfile $vendor)
    {
        $vendor->update([
            'status'       => 'suspended',
            'suspended_at' => now(),
            'admin_notes'  => $request->reason ?? $vendor->admin_notes,
        ]);
        $vendor->user->update(['vendor_status' => 'suspended']);
        // Deactivate all their products
        Product::where('vendor_id', $vendor->id)->update(['is_active' => false]);

        return back()->with('success', "Vendor \"{$vendor->store_name}\" suspended.");
    }

    public function reinstate(VendorProfile $vendor)
    {
        $vendor->update(['status' => 'active', 'suspended_at' => null]);
        $vendor->user->update(['vendor_status' => 'active', 'is_vendor' => true]);
        Product::where('vendor_id', $vendor->id)->update(['is_active' => true]);
        return back()->with('success', "Vendor \"{$vendor->store_name}\" reinstated.");
    }

    // ── Approve / Reject vendor products ─────────────────────────────────

    public function approveProduct(Product $product)
    {
        $product->update(['is_approved' => true, 'is_active' => true]);
        return back()->with('success', 'Product approved and live.');
    }

    public function rejectProduct(Request $request, Product $product)
    {
        $product->update(['is_approved' => false, 'is_active' => false, 'buyer_note' => $request->reason]);
        return back()->with('success', 'Product rejected.');
    }

    // ── Withdrawal management ─────────────────────────────────────────────

    public function withdrawalIndex()
    {
        $withdrawals = VendorWithdrawal::with(['vendor', 'user'])
            ->latest()->paginate(30);
        $pending = VendorWithdrawal::where('status', 'pending')->count();
        return view('admin.vendors.withdrawals', compact('withdrawals', 'pending'));
    }

    public function withdrawalApprove(VendorWithdrawal $withdrawal)
    {
        abort_if($withdrawal->status !== 'pending', 422);

        $withdrawal->update([
            'status'        => 'approved',
            'processed_by'  => auth()->id(),
            'processed_at'  => now(),
        ]);

        app(\App\Services\NotificationService::class)->send(
            $withdrawal->user, 'wallet_credit',
            'Withdrawal Approved ✅',
            "Your withdrawal of ₦" . number_format((float) $withdrawal->amount, 2) . " has been approved and will be transferred to your bank shortly.",
            [],
            route('vendor.dashboard')
        );

        return back()->with('success', 'Withdrawal approved — mark as paid once transferred.');
    }

    public function withdrawalMarkPaid(Request $request, VendorWithdrawal $withdrawal)
    {
        abort_if(!in_array($withdrawal->status, ['pending', 'approved']), 422);

        $request->validate(['payment_reference' => 'nullable|string|max:100']);

        DB::transaction(function () use ($withdrawal, $request) {
            $withdrawal->update([
                'status'             => 'paid',
                'payment_method'     => $request->input('payment_method', 'manual'),
                'payment_reference'  => $request->payment_reference,
                'admin_note'         => $request->admin_note,
                'processed_by'       => auth()->id(),
                'processed_at'       => now(),
            ]);

            // available_balance was already deducted when vendor submitted the request.
            // On paid: only increment total_withdrawn (balance is already correct).
            $withdrawal->vendor->increment('total_withdrawn', (float) $withdrawal->amount);

            // Mark earnings as withdrawn
            VendorEarning::where('vendor_id', $withdrawal->vendor_id)
                ->where('status', 'available')
                ->update(['status' => 'withdrawn', 'withdrawal_id' => $withdrawal->id]);
        });

        app(\App\Services\NotificationService::class)->vendorWithdrawalUpdated($withdrawal->vendor, $withdrawal);

        return back()->with('success', 'Withdrawal marked as paid and vendor notified.');
    }

    public function withdrawalReject(Request $request, VendorWithdrawal $withdrawal)
    {
        $request->validate(['reason' => 'required|string|max:300']);
        abort_if($withdrawal->status !== 'pending', 422);

        // Refund available_balance since it was deducted when vendor submitted the request
        $withdrawal->vendor->increment('available_balance', (float) $withdrawal->amount);

        $withdrawal->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->reason,
            'processed_by'     => auth()->id(),
            'processed_at'     => now(),
        ]);

        app(\App\Services\NotificationService::class)->send(
            $withdrawal->user, 'wallet_debit',
            'Withdrawal Rejected',
            "Your withdrawal request of ₦" . number_format((float) $withdrawal->amount, 2) . " was rejected. Reason: {$request->reason}",
            [],
            route('vendor.dashboard')
        );

        return back()->with('success', 'Withdrawal request rejected.');
    }

    // ── Release earnings from escrow ──────────────────────────────────────

    public function releaseEarnings(Request $request, VendorProfile $vendor)
    {
        $released = DB::transaction(function () use ($vendor) {
            $earnings = VendorEarning::where('vendor_id', $vendor->id)
                ->where('status', 'escrow')
                ->get();

            foreach ($earnings as $e) {
                $e->update(['status' => 'available', 'released_at' => now()]);
                $vendor->releaseToAvailable((float) $e->vendor_amount);
            }
            return $earnings->count();
        });

        return back()->with('success', "Released {$released} earning(s) to vendor's available balance.");
    }

    // ── Edit vendor profile (admin) ────────────────────────────────────────

    public function edit(VendorProfile $vendor)
    {
        return view('admin.vendors.edit', compact('vendor'));
    }

    public function update(Request $request, VendorProfile $vendor)
    {
        $request->validate([
            'store_name'         => 'required|string|max:100|unique:vendor_profiles,store_name,' . $vendor->id,
            'store_description'  => 'nullable|string|max:1000',
            'business_phone'     => 'nullable|string|max:20',
            'business_address'   => 'nullable|string|max:200',
            'business_state'     => 'nullable|string|max:80',
            'business_city'      => 'nullable|string|max:80',
            'bank_name'          => 'nullable|string|max:100',
            'bank_account_number'=> 'nullable|string|max:10',
            'bank_account_name'  => 'nullable|string|max:100',
            'commission_percent' => 'nullable|numeric|min:0|max:100',
            'admin_notes'        => 'nullable|string|max:1000',
        ]);

        $vendor->update($request->only([
            'store_name', 'store_description',
            'business_phone', 'business_address', 'business_state', 'business_city',
            'bank_name', 'bank_account_number', 'bank_account_name',
            'commission_percent', 'admin_notes',
        ]));

        // Update user name/email if provided
        if ($request->filled('user_name')) {
            $vendor->user->update(['name' => $request->user_name]);
        }

        return back()->with('success', "Vendor \"{$vendor->store_name}\" updated.");
    }

    // ── Force-activate vendor (skip fee/review) ────────────────────────────

    public function forceActivate(VendorProfile $vendor)
    {
        $vendor->update([
            'status'           => 'active',
            'activated_at'     => now(),
            'rejection_reason' => null,
        ]);
        $vendor->user->update(['vendor_status' => 'active', 'is_vendor' => true]);
        app(\App\Services\NotificationService::class)->vendorApproved($vendor);
        return back()->with('success', "Vendor force-activated and notified.");
    }

    // ── Block / unblock vendor user account ───────────────────────────────

    public function toggleBlock(VendorProfile $vendor)
    {
        $user    = $vendor->user;
        $blocked = !$user->is_blocked;
        $user->update(['is_blocked' => $blocked]);

        if ($blocked) {
            $vendor->update(['status' => 'suspended', 'suspended_at' => now()]);
            $vendor->user->update(['vendor_status' => 'suspended']);
            Product::where('vendor_id', $vendor->id)->update(['is_active' => false]);
        } else {
            $vendor->update(['status' => 'active', 'suspended_at' => null]);
            $vendor->user->update(['vendor_status' => 'active', 'is_vendor' => true]);
            Product::where('vendor_id', $vendor->id)->update(['is_active' => true]);
        }

        return back()->with('success', $blocked
            ? "Vendor account blocked and store suspended."
            : "Vendor account unblocked and store reinstated."
        );
    }

    // ── Manually adjust vendor balance ────────────────────────────────────

    public function adjustBalance(Request $request, VendorProfile $vendor)
    {
        $request->validate([
            'amount'    => 'required|numeric',
            'type'      => 'required|in:credit,debit',
            'note'      => 'required|string|max:200',
        ]);

        $amount = abs((float) $request->amount);

        if ($request->type === 'credit') {
            $vendor->increment('available_balance', $amount);
            $vendor->increment('total_revenue', $amount);
        } else {
            if ($vendor->available_balance < $amount) {
                return back()->withErrors(['amount' => 'Amount exceeds vendor available balance.']);
            }
            $vendor->decrement('available_balance', $amount);
        }

        $vendor->refresh();
        app(\App\Services\NotificationService::class)->send(
            $vendor->user, 'wallet_credit',
            'Admin Balance Adjustment',
            ($request->type === 'credit' ? '+' : '-') . '₦' . number_format($amount, 2) . ' — ' . $request->note,
            [],
            route('vendor.dashboard')
        );

        return back()->with('success', "Balance adjusted: {$request->type} ₦" . number_format($amount, 2));
    }

    // ── Save admin internal notes ─────────────────────────────────────────

    public function saveNotes(Request $request, VendorProfile $vendor)
    {
        $request->validate(['admin_notes' => 'nullable|string|max:2000']);
        $vendor->update(['admin_notes' => $request->admin_notes]);
        return back()->with('success', 'Notes saved.');
    }

    // ── Delete vendor account (hard delete) ───────────────────────────────

    public function destroy(VendorProfile $vendor)
    {
        $name = $vendor->store_name;
        // Deactivate all products
        Product::where('vendor_id', $vendor->id)->update(['is_active' => false, 'vendor_id' => null]);
        // Reset user vendor status
        $vendor->user->update(['is_vendor' => false, 'vendor_status' => 'none']);
        $vendor->delete();
        return redirect()->route('admin.vendors.index')->with('success', "Vendor \"{$name}\" deleted.");
    }
}

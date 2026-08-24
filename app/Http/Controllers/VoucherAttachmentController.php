<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Receipt;
use App\Models\VoucherAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VoucherAttachmentController extends Controller
{
    public function index()
    {
        $companyId = auth()->user()->company_id;
        return view('voucher_attachments.index', [
            'attachments' => VoucherAttachment::where('company_id', $companyId)->latest()->get(),
            'receipts' => Receipt::where('company_id', $companyId)->latest()->get(),
            'payments' => Payment::where('company_id', $companyId)->latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'voucher_type' => ['required', 'in:receipt,payment'],
            'voucher_id' => ['required', 'integer'],
            'attachment' => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx'],
        ]);
        $companyId = auth()->user()->company_id;
        $model = $data['voucher_type'] === 'receipt' ? Receipt::class : Payment::class;
        $model::whereKey($data['voucher_id'])->where('company_id', $companyId)->firstOrFail();
        $file = $request->file('attachment');
        $path = $file->store("voucher-attachments/{$companyId}", 'local');
        VoucherAttachment::create([
            'company_id' => $companyId, 'voucher_type' => $data['voucher_type'], 'voucher_id' => $data['voucher_id'],
            'original_name' => $file->getClientOriginalName(), 'path' => $path,
            'mime_type' => $file->getMimeType(), 'size' => $file->getSize(),
        ]);
        return back()->with('success', 'تم رفع المرفق وربطه بالسند');
    }

    public function download(VoucherAttachment $attachment)
    {
        $this->ensureOwned($attachment);
        abort_unless(Storage::disk('local')->exists($attachment->path), 404);
        return Storage::disk('local')->download($attachment->path, $attachment->original_name);
    }

    public function destroy(VoucherAttachment $attachment)
    {
        $this->ensureOwned($attachment);
        Storage::disk('local')->delete($attachment->path);
        $attachment->delete();
        return back()->with('success', 'تم حذف المرفق');
    }

    private function ensureOwned(VoucherAttachment $attachment): void
    {
        abort_unless($attachment->company_id === auth()->user()->company_id, 404);
    }
}

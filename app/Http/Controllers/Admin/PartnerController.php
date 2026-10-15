<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Services\WhatsAppService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PartnerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $partners = Partner::query()
            ->when(in_array($status, [Partner::STATUS_PENDING, Partner::STATUS_ACTIVE, Partner::STATUS_REJECTED], true), fn ($q) => $q->where('status', $status))
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->withCount('orders')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Partners/Index', [
            'partners' => $partners,
            'filters' => [
                'status' => in_array($status, [Partner::STATUS_PENDING, Partner::STATUS_ACTIVE, Partner::STATUS_REJECTED], true) ? $status : '',
                'search' => $search ?? '',
            ],
        ]);
    }

    /**
     * Approve a pending partner application.
     */
    public function approve(Partner $partner, WhatsAppService $wa): RedirectResponse
    {
        $partner->approve();

        $typeLabel = $partner->type_label;
        $message = "Halo {$partner->name},\n\n"
            ."Selamat! Aplikasi {$typeLabel} Anda di Siwride telah DISETUJUI.\n\n"
            ."Profil Anda sudah tampil di katalog publik:\n"
            .route('partners.show', $partner->slug)."\n\n"
            ."Silakan hubungi tim kami jika ada pertanyaan.\n"
            .'Terima kasih sudah bergabung!';

        $wa->sendPrivateMessage($partner->phone, $message);

        return redirect()->back()->with('success', "Partner {$partner->name} approved.");
    }

    /**
     * Reject a pending partner application.
     */
    public function reject(Partner $partner, WhatsAppService $wa): RedirectResponse
    {
        $partner->reject();

        $wa->sendPrivateMessage(
            $partner->phone,
            "Halo {$partner->name},\n\n"
            ."Terima kasih sudah melamar sebagai {$partner->type_label} di Siwride. "
            .'Untuk saat ini kami belum dapat melanjutkan aplikasi Anda. '
            ."Silakan kembali melamar di lain waktu.\n\n"
            .'Terima kasih.'
        );

        return redirect()->back()->with('success', "Partner {$partner->name} rejected.");
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Partner $partner): Response
    {
        return Inertia::render('Admin/Partners/Edit', [
            'partner' => $partner,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Partner $partner)
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:travel,guide'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(Partner::class)->ignore($partner->id)],
            'phone' => ['required', 'string', 'max:50', Rule::unique(Partner::class)->ignore($partner->id)],
            'description' => ['nullable', 'string', 'max:2000'],
            'nik' => ['nullable', 'string', 'max:20'],
            'nik_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'status' => ['required', 'string', 'in:pending,active,rejected'],
        ]);

        if ($request->hasFile('nik_image')) {
            if ($partner->nik_image) {
                Storage::disk('public')->delete($partner->nik_image);
            }
            $validated['nik_image'] = $request->file('nik_image')->store('partners/nik', 'public');
        } else {
            unset($validated['nik_image']);
        }

        if ($request->hasFile('image')) {
            if ($partner->image) {
                Storage::disk('public')->delete($partner->image);
            }
            $validated['image'] = $request->file('image')->store('partners/images', 'public');
        } else {
            unset($validated['image']);
        }

        $wasActive = $partner->isActive();
        $isActive = $validated['status'] === Partner::STATUS_ACTIVE;

        if ($isActive && ! $wasActive) {
            $validated['approved_at'] = $partner->approved_at ?? now();
        }

        $partner->update($validated);

        return redirect()->route('admin.partners.index')
            ->with('success', 'Partner updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Partner $partner)
    {
        if ($partner->nik_image) {
            Storage::disk('public')->delete($partner->nik_image);
        }

        if ($partner->image) {
            Storage::disk('public')->delete($partner->image);
        }

        $partner->delete();

        return redirect()->route('admin.partners.index')
            ->with('success', 'Partner deleted successfully.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadTindakLanjut;
use App\Models\User;
use App\Support\PencatatLogAktivitas;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LeadController extends Controller
{
    public function index(Request $request): View
    {
        $selectedStatus = $this->selectedStatus($request->query('status'));
        $selectedChannel = $this->selectedChannel($request->query('channel'));

        $baseQuery = $this->queryLeads($selectedStatus, $selectedChannel);

        $leads = (clone $baseQuery)
            ->with(['pic'])
            ->withCount('tindakLanjut')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $pengingat = LeadTindakLanjut::query()
            ->with('lead')
            ->where('status', 'direncanakan')
            ->whereNotNull('jadwal_tindak_lanjut')
            ->whereDate('jadwal_tindak_lanjut', '<=', now()->toDateString())
            ->when($selectedStatus || $selectedChannel, function (Builder $query) use ($selectedStatus, $selectedChannel) {
                $query->whereHas('lead', function (Builder $leadQuery) use ($selectedStatus, $selectedChannel) {
                    if ($selectedStatus) {
                        $leadQuery->where('status', $selectedStatus);
                    }

                    if ($selectedChannel) {
                        $leadQuery->where('channel', $selectedChannel);
                    }
                });
            })
            ->orderBy('jadwal_tindak_lanjut')
            ->limit(8)
            ->get();

        return view('leads.index', [
            'leads' => $leads,
            'funnel' => $this->buildFunnel($selectedChannel),
            'channels' => Lead::channelOptions(),
            'statuses' => Lead::statusOptions(),
            'statusTindakLanjut' => LeadTindakLanjut::statusOptions(),
            'selectedStatus' => $selectedStatus,
            'selectedChannel' => $selectedChannel,
            'daftarPic' => User::query()->orderBy('name')->get(['id', 'name']),
            'leadOptions' => Lead::query()->orderBy('nama_siswa')->get(['id', 'nama_siswa']),
            'pengingat' => $pengingat,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama_siswa' => ['required', 'string', 'max:150'],
            'asal_sekolah' => ['nullable', 'string', 'max:150'],
            'nomor_telepon' => ['nullable', 'string', 'max:32'],
            'channel' => ['nullable', Rule::in(array_keys(Lead::channelOptions()))],
            'sumber' => ['nullable', 'string', 'max:80'],
            'status' => ['required', Rule::in(array_keys(Lead::statusOptions()))],
            'jadwal_tindak_lanjut' => ['nullable', 'date'],
            'pic_id' => ['nullable', 'exists:users,id'],
            'catatan' => ['nullable', 'string'],
        ], [], [
            'nama_siswa' => 'nama siswa',
            'asal_sekolah' => 'asal sekolah',
            'nomor_telepon' => 'nomor telepon',
            'pic_id' => 'penanggung jawab',
            'jadwal_tindak_lanjut' => 'jadwal tindak lanjut',
        ]);

        $data['created_by'] = $request->user()->id;
        $data['kontak_terakhir'] = $data['status'] !== 'prospek' ? now() : null;

        $lead = Lead::create($data);
        PencatatLogAktivitas::catat(
            $request,
            'lead',
            'tambah',
            'Lead baru ditambahkan.',
            $lead,
            [
                'nama_siswa' => $lead->nama_siswa,
                'channel' => $lead->channel,
                'status' => $lead->status,
            ],
        );

        return back()->with('status', 'Prospek berhasil ditambahkan.');
    }

    public function updateStatus(Request $request, Lead $lead): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(Lead::statusOptions()))],
        ]);

        $statusSebelum = $lead->status;
        $lead->forceFill([
            'status' => $data['status'],
            'kontak_terakhir' => now(),
        ])->save();
        PencatatLogAktivitas::catat(
            $request,
            'lead',
            'ubah',
            'Status lead diperbarui.',
            $lead,
            [
                'status_sebelum' => $statusSebelum,
                'status_sesudah' => $lead->status,
            ],
        );

        return back()->with('status', 'Status prospek berhasil diperbarui.');
    }

    public function storeTindakLanjut(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'lead_id' => ['required', 'exists:leads,id'],
            'catatan' => ['nullable', 'string'],
            'jadwal_tindak_lanjut' => ['nullable', 'date'],
            'status' => ['required', Rule::in(array_keys(LeadTindakLanjut::statusOptions()))],
        ], [], [
            'lead_id' => 'prospek',
            'catatan' => 'catatan',
            'jadwal_tindak_lanjut' => 'jadwal tindak lanjut',
        ]);

        $lead = Lead::query()->findOrFail($data['lead_id']);

        $tindakLanjut = $lead->tindakLanjut()->create([
            'user_id' => $request->user()->id,
            'catatan' => $data['catatan'] ?? null,
            'jadwal_tindak_lanjut' => $data['jadwal_tindak_lanjut'] ?? null,
            'status' => $data['status'],
        ]);

        $lead->forceFill([
            'jadwal_tindak_lanjut' => $data['jadwal_tindak_lanjut'] ?? $lead->jadwal_tindak_lanjut,
            'kontak_terakhir' => now(),
            'status' => $lead->status === 'prospek' ? 'follow_up' : $lead->status,
        ])->save();
        PencatatLogAktivitas::catat(
            $request,
            'lead',
            'tindak_lanjut',
            'Tindak lanjut lead dicatat.',
            $lead,
            [
                'lead_tindak_lanjut_id' => $tindakLanjut->id,
                'status_tindak_lanjut' => $tindakLanjut->status,
                'status_lead_sesudah' => $lead->status,
            ],
        );

        return back()->with('status', 'Tindak lanjut prospek berhasil dicatat.');
    }

    private function queryLeads(?string $status, ?string $channel): Builder
    {
        return Lead::query()
            ->when($status, fn (Builder $query) => $query->where('status', $status))
            ->when($channel, fn (Builder $query) => $query->where('channel', $channel));
    }

    private function buildFunnel(?string $channel): array
    {
        $counts = Lead::query()
            ->when($channel, fn (Builder $query) => $query->where('channel', $channel))
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'prospek' => (int) ($counts['prospek'] ?? 0),
            'follow_up' => (int) ($counts['follow_up'] ?? 0),
            'closing' => (int) ($counts['closing'] ?? 0),
            'batal' => (int) ($counts['batal'] ?? 0),
        ];
    }

    private function selectedStatus(?string $value): ?string
    {
        return in_array($value, array_keys(Lead::statusOptions()), true) ? $value : null;
    }

    private function selectedChannel(?string $value): ?string
    {
        return in_array($value, array_keys(Lead::channelOptions()), true) ? $value : null;
    }
}

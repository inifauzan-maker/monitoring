@php
    $artikel = $artikel ?? null;
    $sedangUbah = $artikel !== null;
    $daftarKategori = $kategori ?? collect();
    $daftarRevisi = $artikel?->revisi ?? collect();

    $judul = old('judul', $artikel?->judul);
    $slug = old('slug', $artikel?->slug);
    $kataKunciUtama = old('kata_kunci_utama', $artikel?->kata_kunci_utama);
    $ringkasan = old('ringkasan', $artikel?->ringkasan);
    $konten = old('konten', $artikel?->konten);
    $kategoriArtikelId = old('kategori_artikel_id', $artikel?->kategori_artikel_id);
    $tingkatKeahlian = old('tingkat_keahlian', $artikel?->tingkat_keahlian ?? 'menengah');
    $bioPenulis = old('bio_penulis', $artikel?->bio_penulis);
    $judulSeo = old('judul_seo', $artikel?->judul_seo);
    $deskripsiSeo = old('deskripsi_seo', $artikel?->deskripsi_seo);
    $outlineSeo = old('outline_seo', $artikel?->outline_seo);
    $jadwalTerbit = old('diterbitkan_pada', $artikel?->diterbitkan_pada?->format('Y-m-d\TH:i'));
    $altGambarUnggulan = old('alt_gambar_unggulan', $artikel?->alt_gambar_unggulan);
    $hapusGambarUnggulan = (bool) old('hapus_gambar_unggulan', false);
    $jadwalTerkunci = $artikel?->sudahTerbitAktif() ?? false;
    $labelChecklistKesiapan = \App\Models\Artikel::labelChecklistKesiapan();
    $checklistKesiapan = \App\Models\Artikel::normalisasiChecklistKesiapan(old('checklist_kesiapan', $artikel?->checklist_kesiapan ?? []));

    $sumberReferensi = old('sumber_referensi', $artikel?->sumber_referensi ?? ['']);

    if (! is_array($sumberReferensi) || count(array_filter($sumberReferensi, fn ($item) => filled($item))) === 0) {
        $sumberReferensi = [''];
    }

    $slugOtomatis = \Illuminate\Support\Str::slug((string) $judul);
    $slugManual = filled($slug) && $slug !== $slugOtomatis;
    $evaluasiKesiapan = (new \App\Models\Artikel([
        'judul' => $judul,
        'slug' => $slug,
        'kata_kunci_utama' => $kataKunciUtama,
        'ringkasan' => $ringkasan,
        'konten' => $konten,
        'sumber_referensi' => $sumberReferensi,
        'judul_seo' => $judulSeo,
        'deskripsi_seo' => $deskripsiSeo,
        'outline_seo' => $outlineSeo,
        'checklist_kesiapan' => $checklistKesiapan,
    ]))->evaluasiKesiapan();
@endphp

<div data-editor-artikel @if ($sedangUbah) data-autosimpan-url="{{ route('tools.artikel.autosimpan', $artikel) }}" @endif>
    @if ($sedangUbah)
        <div class="alert alert-info mb-3">
            <div class="d-flex justify-content-between align-items-center gap-3">
                <div>
                    Autosimpan draft aktif. Perubahan teks dan metadata akan disimpan otomatis saat Anda berhenti mengetik.
                </div>
                <div class="small fw-semibold text-uppercase" data-status-autosimpan>Siap</div>
            </div>
        </div>
    @endif

    @if ($daftarKategori->isEmpty())
        <div class="alert alert-warning mb-3">
            Belum ada kategori artikel. Tambahkan dulu di
            <a href="{{ route('tools.artikel.kategori.index') }}" class="alert-link">halaman kategori</a>
            sebelum membuat artikel baru.
        </div>
    @endif

    <form method="POST" action="{{ $formAction }}" enctype="multipart/form-data" data-form-artikel>
        @csrf
        @if (strtoupper($formMethod ?? 'POST') !== 'POST')
            @method($formMethod)
        @endif

        <div class="row row-cards">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Konten Utama</h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label" for="judul">Judul</label>
                                <input type="text" class="form-control" id="judul" name="judul" value="{{ $judul }}" required data-judul>
                            </div>
                            <div class="col-md-7">
                                <label class="form-label" for="slug">Slug</label>
                                <input type="text" class="form-control" id="slug" name="slug" value="{{ $slug }}" required data-slug data-slug-manual="{{ $slugManual ? '1' : '0' }}">
                                <div class="form-hint">URL artikel: <span data-slug-preview>/tools/artikel/{{ $slug ?: 'judul-artikel' }}</span></div>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label" for="kata_kunci_utama">Kata Kunci Utama</label>
                                <input type="text" class="form-control" id="kata_kunci_utama" name="kata_kunci_utama" value="{{ $kataKunciUtama }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="ringkasan">Ringkasan</label>
                                <textarea class="form-control" id="ringkasan" name="ringkasan" rows="4" required>{{ $ringkasan }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="konten">Konten</label>
                                <textarea class="form-control font-monospace" id="konten" name="konten" rows="18" required>{{ $konten }}</textarea>
                                <div class="form-hint">Anda bisa menulis HTML sederhana atau struktur heading manual sesuai kebutuhan.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Referensi dan Penulis</h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Sumber Referensi</label>
                                <div class="d-grid gap-2" data-sumber-container>
                                    @foreach ($sumberReferensi as $nilaiSumber)
                                        <div class="d-flex gap-2" data-baris-sumber>
                                            <input type="url" name="sumber_referensi[]" value="{{ $nilaiSumber }}" class="form-control" placeholder="https://example.com/referensi">
                                            <button type="button" class="btn btn-outline-danger" data-hapus-sumber>Hapus</button>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="mt-2">
                                    <button type="button" class="btn btn-outline-primary btn-sm" data-tambah-sumber>Tambah Sumber</button>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="bio_penulis">Bio Penulis</label>
                                <textarea class="form-control" id="bio_penulis" name="bio_penulis" rows="4">{{ $bioPenulis }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Struktur SEO</h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label" for="outline_seo">Outline SEO</label>
                                <textarea class="form-control font-monospace" id="outline_seo" name="outline_seo" rows="10" data-outline-seo>{{ $outlineSeo }}</textarea>
                                <div class="form-hint">Gunakan struktur seperti `#`, `##`, atau daftar heading agar alur tulisan lebih rapi.</div>
                            </div>
                            <div class="col-12">
                                <div class="border rounded p-3 bg-body-tertiary">
                                    <div class="text-secondary text-uppercase fw-semibold small mb-2">Preview Pencarian</div>
                                    <div class="small text-success mb-1" data-snippet-url>
                                        {{ parse_url(config('app.url'), PHP_URL_HOST) ?: 'example.com' }}/tools/artikel/{{ $slug ?: 'judul-artikel' }}
                                    </div>
                                    <div class="fw-semibold text-primary mb-1" data-snippet-title>
                                        {{ $judulSeo ?: ($judul ?: 'Judul artikel Anda akan tampil di sini') }}
                                    </div>
                                    <div class="text-secondary small" data-snippet-description>
                                        {{ $deskripsiSeo ?: ($ringkasan ?: 'Deskripsi artikel akan tampil di sini.') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Kesiapan Publikasi</h3>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-end gap-3 mb-3">
                            <div>
                                <div class="text-secondary text-uppercase fw-semibold small">Skor</div>
                                <div class="display-6 mb-0" data-skor-kesiapan>{{ $evaluasiKesiapan['skor'] }}</div>
                            </div>
                            <span class="badge {{ $evaluasiKesiapan['skor'] >= 80 ? 'bg-green-lt text-green' : ($evaluasiKesiapan['skor'] >= 60 ? 'bg-yellow-lt text-yellow' : 'bg-red-lt text-red') }}" data-label-kesiapan>
                                {{ $evaluasiKesiapan['label'] }}
                            </span>
                        </div>

                        <div class="progress progress-sm mb-3">
                            <div class="progress-bar {{ $evaluasiKesiapan['skor'] >= 80 ? 'bg-green' : ($evaluasiKesiapan['skor'] >= 60 ? 'bg-yellow' : 'bg-red') }}"
                                style="width: {{ $evaluasiKesiapan['skor'] }}%" data-progress-kesiapan></div>
                        </div>

                        <div class="d-grid gap-2" data-daftar-kesiapan>
                            @foreach ($evaluasiKesiapan['checks'] as $check)
                                <div class="d-flex gap-2 align-items-start">
                                    <span class="badge {{ $check['ok'] ? 'bg-green-lt text-green' : 'bg-yellow-lt text-yellow' }}">{{ $check['ok'] ? 'OK' : 'Cek' }}</span>
                                    <div>
                                        <div class="fw-semibold small">{{ $check['judul'] }}</div>
                                        <div class="text-secondary small">{{ $check['pesan'] }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="card-footer text-secondary small">
                        Publish akan ditolak jika poin wajib seperti metadata SEO, outline, referensi, dan checklist belum lengkap.
                        @if ($artikel?->sedangTerjadwal())
                            Artikel ini sedang terjadwal terbit pada {{ $artikel->diterbitkan_pada?->format('d M Y H:i') }}.
                        @endif
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Pengaturan Artikel</h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label" for="kategori_artikel_id">Kategori</label>
                                <select class="form-select" id="kategori_artikel_id" name="kategori_artikel_id" required>
                                    <option value="">Pilih Kategori</option>
                                    @foreach ($daftarKategori as $itemKategori)
                                        <option value="{{ $itemKategori->id }}" @selected((string) $kategoriArtikelId === (string) $itemKategori->id)>{{ $itemKategori->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="tingkat_keahlian">Tingkat Keahlian</label>
                                <select class="form-select" id="tingkat_keahlian" name="tingkat_keahlian" required>
                                    @foreach (\App\Models\Artikel::TINGKAT_KEAHLIAN as $nilai => $label)
                                        <option value="{{ $nilai }}" @selected($tingkatKeahlian === $nilai)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="judul_seo">Judul SEO</label>
                                <input type="text" class="form-control" id="judul_seo" name="judul_seo" value="{{ $judulSeo }}" maxlength="60">
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="deskripsi_seo">Deskripsi SEO</label>
                                <textarea class="form-control" id="deskripsi_seo" name="deskripsi_seo" rows="4" maxlength="160">{{ $deskripsiSeo }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="diterbitkan_pada">Jadwal Terbit</label>
                                <input type="datetime-local" class="form-control" id="diterbitkan_pada" name="diterbitkan_pada"
                                    value="{{ $jadwalTerbit }}" @disabled($jadwalTerkunci)>
                                <div class="form-hint">
                                    @if ($jadwalTerkunci)
                                        Artikel sudah aktif diterbitkan. Batalkan terbit lebih dulu jika ingin mengubah jadwal.
                                    @else
                                        Kosongkan untuk terbit langsung saat tombol terbitkan dipakai. Isi tanggal masa depan jika ingin menjadwalkan terbit.
                                    @endif
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="gambar_unggulan">Gambar Unggulan</label>
                                <input type="file" class="form-control" id="gambar_unggulan" name="gambar_unggulan" accept="image/*">
                                @if ($artikel?->url_gambar_unggulan && ! $hapusGambarUnggulan)
                                    <div class="mt-3">
                                        <img src="{{ $artikel->url_gambar_unggulan }}" alt="{{ $artikel->alt_gambar_unggulan ?: $artikel->judul }}"
                                            class="img-fluid rounded border">
                                    </div>
                                @endif
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="alt_gambar_unggulan">Alt Gambar</label>
                                <input type="text" class="form-control" id="alt_gambar_unggulan" name="alt_gambar_unggulan" value="{{ $altGambarUnggulan }}">
                            </div>
                            @if ($artikel?->gambar_unggulan_path)
                                <div class="col-12">
                                    <label class="form-check">
                                        <input class="form-check-input" type="checkbox" name="hapus_gambar_unggulan" value="1" @checked($hapusGambarUnggulan)>
                                        <span class="form-check-label">Hapus gambar unggulan saat simpan</span>
                                    </label>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Checklist Editorial</h3>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-3">
                            @foreach ($labelChecklistKesiapan as $key => $label)
                                <label class="form-check">
                                    <input class="form-check-input" type="checkbox" name="checklist_kesiapan[{{ $key }}]" value="1" @checked($checklistKesiapan[$key] ?? false)>
                                    <span class="form-check-label">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                @if ($sedangUbah)
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Riwayat Revisi</h3>
                        </div>
                        <div class="list-group list-group-flush">
                            @forelse ($daftarRevisi as $itemRevisi)
                                @php
                                    $snapshot = $itemRevisi->snapshot ?? [];
                                    $jumlahKata = str_word_count(strip_tags((string) ($snapshot['konten'] ?? '')));
                                @endphp
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start gap-3">
                                        <div>
                                            <div class="fw-semibold">{{ $itemRevisi->labelPemicu() }}</div>
                                            <div class="text-secondary small">{{ $itemRevisi->created_at?->format('d M Y H:i') }}</div>
                                            <div class="text-secondary small">{{ number_format($jumlahKata, 0, ',', '.') }} kata</div>
                                        </div>
                                        <form method="POST" action="{{ route('tools.artikel.revisi.pulihkan', [$artikel, $itemRevisi]) }}" onsubmit="return confirm('Pulihkan revisi ini? Draft aktif akan diganti.')">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-primary btn-sm">Pulihkan</button>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <div class="list-group-item text-secondary">Belum ada revisi tersimpan.</div>
                            @endforelse
                        </div>
                        <div class="card-footer text-secondary small">
                            Catatan: restore tidak mengembalikan file gambar unggulan yang lama.
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-12">
                <div class="btn-list">
                    <button type="submit" class="btn btn-primary" @disabled($daftarKategori->isEmpty())>
                        {{ $sedangUbah ? 'Simpan Perubahan' : 'Simpan Draft' }}
                    </button>
                    <a href="{{ $sedangUbah ? route('tools.artikel.saya') : route('tools.artikel') }}" class="btn btn-outline-secondary">Batal</a>
                    @if ($sedangUbah)
                        <form method="POST" action="{{ route('tools.artikel.destroy', $artikel) }}" class="ms-auto" onsubmit="return confirm('Hapus artikel ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger">Hapus Artikel</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </form>
</div>

@push('skrip_halaman')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-editor-artikel]').forEach((editor) => {
                const form = editor.querySelector('[data-form-artikel]');
                const judul = editor.querySelector('[data-judul]');
                const slug = editor.querySelector('[data-slug]');
                const slugPreview = editor.querySelector('[data-slug-preview]');
                const kataKunciUtama = form.querySelector('#kata_kunci_utama');
                const ringkasan = form.querySelector('#ringkasan');
                const konten = form.querySelector('#konten');
                const judulSeo = form.querySelector('#judul_seo');
                const deskripsiSeo = form.querySelector('#deskripsi_seo');
                const outlineSeo = form.querySelector('[data-outline-seo]');
                const sumberContainer = editor.querySelector('[data-sumber-container]');
                const tombolTambahSumber = editor.querySelector('[data-tambah-sumber]');
                const statusAutosimpan = editor.querySelector('[data-status-autosimpan]');
                const autosimpanUrl = editor.dataset.autosimpanUrl || '';
                const skorKesiapan = editor.querySelector('[data-skor-kesiapan]');
                const labelKesiapan = editor.querySelector('[data-label-kesiapan]');
                const progressKesiapan = editor.querySelector('[data-progress-kesiapan]');
                const daftarKesiapan = editor.querySelector('[data-daftar-kesiapan]');
                const snippetUrl = editor.querySelector('[data-snippet-url]');
                const snippetTitle = editor.querySelector('[data-snippet-title]');
                const snippetDescription = editor.querySelector('[data-snippet-description]');

                let slugManual = slug?.dataset.slugManual === '1';
                let autosimpanTimer = null;
                let autosimpanSedangJalan = false;
                let autosimpanAntri = false;

                const slugify = (value) => value.toString().toLowerCase()
                    .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '')
                    .replace(/-{2,}/g, '-');

                const setStatusAutosimpan = (message, className = 'text-secondary') => {
                    if (!statusAutosimpan) {
                        return;
                    }

                    statusAutosimpan.className = `small fw-semibold text-uppercase ${className}`;
                    statusAutosimpan.textContent = message;
                };

                const perbaruiSlugPreview = () => {
                    if (!slugPreview || !slug) {
                        return;
                    }

                    slugPreview.textContent = `/tools/artikel/${slug.value.trim() || 'judul-artikel'}`;
                };

                const hitungKata = (value) => {
                    const text = value.replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim();
                    return text ? text.split(' ').length : 0;
                };

                const teksNormal = (value) => value.toString().toLowerCase()
                    .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                    .replace(/[^a-z0-9\s-]+/g, ' ')
                    .replace(/\s+/g, ' ')
                    .trim();

                const nilaiChecklist = () => [...form.querySelectorAll('input[name^="checklist_kesiapan["]')].every((input) => input.checked);

                const renderPreviewSnippet = () => {
                    const host = window.location.host || 'example.com';
                    const slugValue = slug?.value.trim() || 'judul-artikel';
                    const titleValue = judulSeo?.value.trim() || judul?.value.trim() || 'Judul artikel Anda akan tampil di sini';
                    const descriptionValue = deskripsiSeo?.value.trim() || ringkasan?.value.trim() || 'Deskripsi artikel akan tampil di sini.';

                    if (snippetUrl) {
                        snippetUrl.textContent = `${host}/tools/artikel/${slugValue}`;
                    }

                    if (snippetTitle) {
                        snippetTitle.textContent = titleValue;
                    }

                    if (snippetDescription) {
                        snippetDescription.textContent = descriptionValue;
                    }
                };

                const evaluasiKesiapan = () => {
                    const keyword = (kataKunciUtama?.value || '').trim();
                    const judulValue = (judul?.value || '').trim();
                    const slugValue = (slug?.value || '').trim();
                    const ringkasanPanjang = (ringkasan?.value || '').trim().length;
                    const judulSeoPanjang = (judulSeo?.value || '').trim().length;
                    const deskripsiSeoPanjang = (deskripsiSeo?.value || '').trim().length;
                    const jumlahSumber = [...form.querySelectorAll('input[name="sumber_referensi[]"]')]
                        .map((input) => input.value.trim())
                        .filter(Boolean)
                        .length;
                    const jumlahKataKonten = hitungKata(konten?.value || '');
                    const keywordNormal = teksNormal(keyword);
                    const judulNormal = teksNormal(judulValue);
                    const slugNormal = teksNormal(slugValue.replace(/-/g, ' '));

                    const checks = [
                        {
                            judul: 'Keyword utama tersedia',
                            ok: keyword !== '',
                            bobot: 10,
                            pesan: keyword !== '' ? 'Keyword utama sudah diisi.' : 'Isi kata kunci utama terlebih dahulu.',
                        },
                        {
                            judul: 'Keyword ada di judul',
                            ok: keyword !== '' && judulNormal.includes(keywordNormal),
                            bobot: 10,
                            pesan: keyword !== '' && judulNormal.includes(keywordNormal) ? 'Judul sudah memuat keyword utama.' : 'Masukkan keyword utama ke judul.',
                        },
                        {
                            judul: 'Slug relevan dengan keyword',
                            ok: keyword !== '' && slugNormal.includes(keywordNormal),
                            bobot: 10,
                            pesan: keyword !== '' && slugNormal.includes(keywordNormal) ? 'Slug sudah selaras dengan keyword.' : 'Selaraskan slug dengan keyword utama.',
                        },
                        {
                            judul: 'Ringkasan berada di rentang ideal',
                            ok: ringkasanPanjang >= 120 && ringkasanPanjang <= 200,
                            bobot: 10,
                            pesan: ringkasanPanjang >= 120 && ringkasanPanjang <= 200 ? 'Ringkasan sudah berada di rentang ideal.' : 'Buat ringkasan 120-200 karakter.',
                        },
                        {
                            judul: 'Metadata SEO lengkap',
                            ok: (judulSeo?.value || '').trim() !== '' && (deskripsiSeo?.value || '').trim() !== '',
                            bobot: 10,
                            pesan: (judulSeo?.value || '').trim() !== '' && (deskripsiSeo?.value || '').trim() !== '' ? 'Judul SEO dan deskripsi SEO sudah terisi.' : 'Lengkapi judul SEO dan deskripsi SEO.',
                        },
                        {
                            judul: 'Panjang metadata SEO sesuai',
                            ok: judulSeoPanjang >= 30 && judulSeoPanjang <= 60 && deskripsiSeoPanjang >= 120 && deskripsiSeoPanjang <= 160,
                            bobot: 10,
                            pesan: judulSeoPanjang >= 30 && judulSeoPanjang <= 60 && deskripsiSeoPanjang >= 120 && deskripsiSeoPanjang <= 160 ? 'Panjang metadata SEO sudah sesuai.' : 'Judul SEO ideal 30-60 karakter dan deskripsi SEO 120-160 karakter.',
                        },
                        {
                            judul: 'Outline SEO tersedia',
                            ok: (outlineSeo?.value || '').trim() !== '',
                            bobot: 10,
                            pesan: (outlineSeo?.value || '').trim() !== '' ? 'Outline SEO sudah tersedia.' : 'Isi outline SEO terlebih dahulu.',
                        },
                        {
                            judul: 'Referensi tersedia',
                            ok: jumlahSumber > 0,
                            bobot: 10,
                            pesan: jumlahSumber > 0 ? `${jumlahSumber} sumber referensi sudah ditambahkan.` : 'Tambahkan minimal satu sumber referensi.',
                        },
                        {
                            judul: 'Konten cukup panjang',
                            ok: jumlahKataKonten >= 300,
                            bobot: 10,
                            pesan: jumlahKataKonten >= 300 ? `Konten memiliki ${jumlahKataKonten} kata.` : `Konten masih ${jumlahKataKonten} kata. Target minimal 300 kata.`,
                        },
                        {
                            judul: 'Checklist editorial lengkap',
                            ok: nilaiChecklist(),
                            bobot: 10,
                            pesan: nilaiChecklist() ? 'Checklist editorial sudah lengkap.' : 'Lengkapi checklist kesiapan editorial.',
                        },
                    ];

                    const skor = checks.reduce((acc, item) => acc + (item.ok ? item.bobot : 0), 0);
                    const label = skor >= 80 ? 'Sangat Baik' : (skor >= 60 ? 'Cukup Baik' : 'Perlu Revisi');
                    const warna = skor >= 80 ? 'green' : (skor >= 60 ? 'yellow' : 'red');

                    if (skorKesiapan) {
                        skorKesiapan.textContent = skor;
                    }

                    if (labelKesiapan) {
                        labelKesiapan.textContent = label;
                        labelKesiapan.className = `badge bg-${warna}-lt text-${warna}`;
                    }

                    if (progressKesiapan) {
                        progressKesiapan.style.width = `${skor}%`;
                        progressKesiapan.className = `progress-bar bg-${warna}`;
                    }

                    if (daftarKesiapan) {
                        daftarKesiapan.innerHTML = checks.map((item) => `
                            <div class="d-flex gap-2 align-items-start">
                                <span class="badge ${item.ok ? 'bg-green-lt text-green' : 'bg-yellow-lt text-yellow'}">${item.ok ? 'OK' : 'Cek'}</span>
                                <div>
                                    <div class="fw-semibold small">${item.judul}</div>
                                    <div class="text-secondary small">${item.pesan}</div>
                                </div>
                            </div>
                        `).join('');
                    }
                };

                const bindBarisSumber = (baris) => {
                    const input = baris.querySelector('input[name="sumber_referensi[]"]');
                    const tombolHapus = baris.querySelector('[data-hapus-sumber]');

                    input?.addEventListener('input', () => {
                        evaluasiKesiapan();
                        renderPreviewSnippet();
                        jadwalkanAutosimpan();
                    });
                    tombolHapus?.addEventListener('click', () => {
                        baris.remove();

                        if (!sumberContainer.querySelector('[data-baris-sumber]')) {
                            tambahBarisSumber();
                        }

                        evaluasiKesiapan();
                        renderPreviewSnippet();
                        jadwalkanAutosimpan();
                    });
                };

                const tambahBarisSumber = (value = '') => {
                    const baris = document.createElement('div');
                    baris.className = 'd-flex gap-2';
                    baris.setAttribute('data-baris-sumber', '');
                    baris.innerHTML = `
                        <input type="url" name="sumber_referensi[]" value="${value}" class="form-control" placeholder="https://example.com/referensi">
                        <button type="button" class="btn btn-outline-danger" data-hapus-sumber>Hapus</button>
                    `;
                    sumberContainer.appendChild(baris);
                    bindBarisSumber(baris);
                };

                const payloadAutosimpan = () => {
                    const formData = new FormData(form);
                    formData.delete('_method');
                    formData.delete('gambar_unggulan');
                    formData.delete('hapus_gambar_unggulan');

                    return formData;
                };

                const jalankanAutosimpan = async () => {
                    if (!autosimpanUrl || autosimpanSedangJalan) {
                        autosimpanAntri = true;
                        return;
                    }

                    autosimpanSedangJalan = true;
                    autosimpanAntri = false;
                    setStatusAutosimpan('Menyimpan', 'text-primary');

                    try {
                        const response = await fetch(autosimpanUrl, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: payloadAutosimpan(),
                        });

                        const payload = await response.json().catch(() => ({}));

                        if (!response.ok) {
                            throw new Error(payload.message || 'Autosimpan gagal.');
                        }

                        setStatusAutosimpan(`Tersimpan ${payload.saved_at || ''}`.trim(), 'text-success');
                    } catch (error) {
                        setStatusAutosimpan(error.message || 'Autosimpan gagal', 'text-danger');
                    } finally {
                        autosimpanSedangJalan = false;

                        if (autosimpanAntri) {
                            window.clearTimeout(autosimpanTimer);
                            autosimpanTimer = window.setTimeout(jalankanAutosimpan, 1200);
                        }
                    }
                };

                function jadwalkanAutosimpan() {
                    if (!autosimpanUrl) {
                        return;
                    }

                    window.clearTimeout(autosimpanTimer);
                    setStatusAutosimpan('Perubahan terdeteksi', 'text-warning');
                    autosimpanTimer = window.setTimeout(jalankanAutosimpan, 1800);
                }

                editor.querySelectorAll('[data-baris-sumber]').forEach(bindBarisSumber);

                tombolTambahSumber?.addEventListener('click', () => {
                    tambahBarisSumber();
                    evaluasiKesiapan();
                    renderPreviewSnippet();
                    jadwalkanAutosimpan();
                });

                judul?.addEventListener('input', () => {
                    if (!slugManual && slug) {
                        slug.value = slugify(judul.value);
                    }

                    perbaruiSlugPreview();
                    evaluasiKesiapan();
                    renderPreviewSnippet();
                    jadwalkanAutosimpan();
                });

                slug?.addEventListener('input', () => {
                    slugManual = slug.value.trim() !== '' && slug.value.trim() !== slugify(judul?.value || '');
                    perbaruiSlugPreview();
                    evaluasiKesiapan();
                    renderPreviewSnippet();
                    jadwalkanAutosimpan();
                });

                slug?.addEventListener('blur', () => {
                    slug.value = slugify(slug.value || judul?.value || '');
                    slugManual = slug.value.trim() !== '' && slug.value.trim() !== slugify(judul?.value || '');
                    perbaruiSlugPreview();
                    evaluasiKesiapan();
                    renderPreviewSnippet();
                    jadwalkanAutosimpan();
                });

                form.querySelectorAll('input, textarea, select').forEach((input) => {
                    if (input.type === 'file' || input.name === 'hapus_gambar_unggulan') {
                        input.addEventListener('change', () => {
                            setStatusAutosimpan('Perubahan file perlu simpan manual', 'text-warning');
                        });

                        return;
                    }

                    const eventName = input.tagName === 'SELECT' || input.type === 'checkbox' ? 'change' : 'input';
                    input.addEventListener(eventName, () => {
                        evaluasiKesiapan();
                        renderPreviewSnippet();
                        jadwalkanAutosimpan();
                    });
                });

                perbaruiSlugPreview();
                evaluasiKesiapan();
                renderPreviewSnippet();

                if (autosimpanUrl) {
                    setStatusAutosimpan('Autosimpan aktif', 'text-secondary');
                }
            });
        });
    </script>
@endpush

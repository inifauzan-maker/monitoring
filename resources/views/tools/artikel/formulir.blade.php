@php
    $artikel = $artikel ?? null;
    $sedangUbah = $artikel !== null;
    $daftarKategori = $kategori ?? collect();
    $daftarRevisi = $artikel?->revisi ?? collect();

    $judul = old('judul', $artikel?->judul);
    $slug = old('slug', $artikel?->slug);
    $kataKunciUtama = old('kata_kunci_utama', $artikel?->kata_kunci_utama);
    $keywordTurunan = old('keyword_turunan', $artikel?->keyword_turunan);
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
    $alatEditorKonten = [
        ['aksi' => 'h1', 'label' => 'H1', 'tooltip' => 'Sisipkan heading utama untuk judul bagian besar artikel.'],
        ['aksi' => 'h2', 'label' => 'H2', 'tooltip' => 'Sisipkan subjudul utama untuk membagi topik besar.'],
        ['aksi' => 'h3', 'label' => 'H3', 'tooltip' => 'Sisipkan heading turunan untuk detail atau langkah.'],
        ['aksi' => 'p', 'label' => 'Paragraf', 'tooltip' => 'Bungkus teks aktif menjadi paragraf HTML.'],
        ['aksi' => 'ul', 'label' => 'Bullet', 'tooltip' => 'Tambahkan daftar bullet untuk poin-poin ringkas.'],
        ['aksi' => 'quote', 'label' => 'Quote', 'tooltip' => 'Tambahkan kutipan, insight, atau callout singkat.'],
        ['aksi' => 'link', 'label' => 'Link', 'tooltip' => 'Sisipkan tautan HTML dengan format siap edit.'],
    ];
    $templateEditorKonten = [
        [
            'aksi' => 'problem-solution-cta',
            'label' => 'Problem-Solution-CTA',
            'tooltip' => 'Struktur cepat untuk artikel yang membahas masalah, solusi, dan ajakan bertindak.',
        ],
        [
            'aksi' => 'how-to',
            'label' => 'How-to',
            'tooltip' => 'Template langkah demi langkah untuk panduan praktik atau tutorial.',
        ],
        [
            'aksi' => 'listicle',
            'label' => 'Listicle',
            'tooltip' => 'Template daftar poin untuk artikel dengan format daftar yang mudah dipindai.',
        ],
    ];
    $templateStrukturSeo = [
        [
            'aksi' => 'faq-seo',
            'label' => 'FAQ SEO',
            'tooltip' => 'Template FAQ cepat untuk outline artikel berbasis pertanyaan dan jawaban.',
        ],
    ];
    $evaluasiKesiapan = (new \App\Models\Artikel([
        'judul' => $judul,
        'slug' => $slug,
        'kata_kunci_utama' => $kataKunciUtama,
        'keyword_turunan' => $keywordTurunan,
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
                        <div>
                            <h3 class="card-title mb-1">Konten Utama</h3>
                            <div class="text-secondary small">Susun judul, slug, ringkasan, dan isi artikel utama. Gunakan struktur heading agar pembaca dan mesin pencari mudah mengikuti alurnya.</div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <label class="form-label mb-0" for="judul">Judul</label>
                                    <button type="button" class="ikon-bantuan-form" data-bs-toggle="tooltip" title="Buat judul spesifik, mengandung manfaat, dan bila memungkinkan memuat keyword utama.">i</button>
                                </div>
                                <input type="text" class="form-control" id="judul" name="judul" value="{{ $judul }}" required data-judul>
                                <div class="form-hint">Contoh: Strategi Follow Up WhatsApp untuk Meningkatkan Closing Leads Bimbel.</div>
                            </div>
                            <div class="col-md-7">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <label class="form-label mb-0" for="slug">Slug</label>
                                    <button type="button" class="ikon-bantuan-form" data-bs-toggle="tooltip" title="Slug dipakai di URL. Gunakan huruf kecil, kata penting saja, dan hindari kata sambung yang tidak perlu.">i</button>
                                </div>
                                <input type="text" class="form-control" id="slug" name="slug" value="{{ $slug }}" required data-slug data-slug-manual="{{ $slugManual ? '1' : '0' }}">
                                <div class="form-hint">URL artikel: <span data-slug-preview>/tools/artikel/{{ $slug ?: 'judul-artikel' }}</span></div>
                            </div>
                            <div class="col-md-5">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <label class="form-label mb-0" for="kata_kunci_utama">Kata Kunci Utama</label>
                                    <button type="button" class="ikon-bantuan-form" data-bs-toggle="tooltip" title="Isi satu keyword fokus yang ingin ditargetkan artikel ini. Keyword ini dipakai untuk evaluasi judul, slug, dan metadata SEO.">i</button>
                                </div>
                                <input type="text" class="form-control" id="kata_kunci_utama" name="kata_kunci_utama" value="{{ $kataKunciUtama }}">
                                <div class="form-hint">Contoh: follow up whatsapp leads bimbel</div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <label class="form-label mb-0" for="keyword_turunan">Keyword Turunan</label>
                                    <button type="button" class="ikon-bantuan-form" data-bs-toggle="tooltip" title="Isi keyword turunan per baris atau pisahkan dengan koma. Dipakai untuk generator FAQ, intent pencarian, dan variasi subtopik artikel.">i</button>
                                </div>
                                <textarea class="form-control" id="keyword_turunan" name="keyword_turunan" rows="3" data-keyword-turunan>{{ $keywordTurunan }}</textarea>
                                <div class="form-hint">Contoh: script follow up wa calon siswa, contoh pesan closing bimbel, jadwal follow up lead dingin.</div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <label class="form-label mb-0" for="ringkasan">Ringkasan</label>
                                    <button type="button" class="ikon-bantuan-form" data-bs-toggle="tooltip" title="Ringkasan ideal 120-200 karakter. Anggap ini sebagai hook singkat untuk pembaca dan kandidat deskripsi awal.">i</button>
                                </div>
                                <textarea class="form-control" id="ringkasan" name="ringkasan" rows="4" required>{{ $ringkasan }}</textarea>
                                <div class="form-hint">Contoh: Pelajari alur follow up WhatsApp yang singkat, rapi, dan lebih efektif untuk mendorong calon siswa segera mengambil keputusan.</div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <label class="form-label mb-0" for="konten">Konten</label>
                                        <button type="button" class="ikon-bantuan-form" data-bs-toggle="tooltip" title="Gunakan toolbar untuk menyisipkan heading, paragraf, list, quote, atau link HTML sederhana tanpa editor berat.">i</button>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2 toolbar-editor-artikel" data-toolbar-konten>
                                        @foreach ($alatEditorKonten as $alat)
                                            <button
                                                type="button"
                                                class="btn btn-outline-secondary btn-sm"
                                                data-editor-action="{{ $alat['aksi'] }}"
                                                data-bs-toggle="tooltip"
                                                title="{{ $alat['tooltip'] }}"
                                            >
                                                {{ $alat['label'] }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="contoh-card-editor mb-2">
                                    <div class="fw-semibold small text-uppercase mb-1">Contoh struktur cepat</div>
                                    <code>&lt;h2&gt;Masalah Umum Leads Dingin&lt;/h2&gt;</code><br>
                                    <code>&lt;p&gt;Jelaskan konteks dan dampak utamanya di sini...&lt;/p&gt;</code><br>
                                    <code>&lt;ul&gt;&lt;li&gt;Respon lambat&lt;/li&gt;&lt;li&gt;Follow up tidak konsisten&lt;/li&gt;&lt;/ul&gt;</code>
                                </div>
                                <div class="d-flex flex-wrap gap-2 mb-2 toolbar-template-artikel" data-template-konten>
                                    @foreach ($templateEditorKonten as $template)
                                        <button
                                            type="button"
                                            class="btn btn-outline-primary btn-sm"
                                            data-editor-template="{{ $template['aksi'] }}"
                                            data-bs-toggle="tooltip"
                                            title="{{ $template['tooltip'] }}"
                                        >
                                            {{ $template['label'] }}
                                        </button>
                                    @endforeach
                                </div>
                                <textarea class="form-control font-monospace" id="konten" name="konten" rows="18" required>{{ $konten }}</textarea>
                                <div class="form-hint">Anda bisa menulis HTML sederhana atau struktur heading manual sesuai kebutuhan. Toolbar di atas hanya menyisipkan template, jadi hasil akhir tetap mudah diedit.</div>
                                <div class="preview-konten-artikel mt-3">
                                    <div class="d-flex align-items-center justify-content-between gap-3 mb-2 flex-wrap">
                                        <div>
                                            <div class="fw-semibold">Preview Live Konten</div>
                                            <div class="text-secondary small">Panel ini membantu mengecek ritme heading, paragraf, list, dan CTA tanpa perlu pindah ke halaman preview.</div>
                                        </div>
                                        <span class="badge bg-blue-lt text-blue">Live</span>
                                    </div>
                                    <div class="preview-konten-artikel-body" data-preview-konten></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div>
                            <h3 class="card-title mb-1">Referensi dan Penulis</h3>
                            <div class="text-secondary small">Masukkan sumber rujukan dan konteks penulis supaya artikel lebih kredibel dan siap dipertanggungjawabkan.</div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <label class="form-label mb-0">Sumber Referensi</label>
                                    <button type="button" class="ikon-bantuan-form" data-bs-toggle="tooltip" title="Tambahkan URL artikel, riset, landing page, dokumen internal, atau sumber resmi lain yang benar-benar dipakai saat menulis.">i</button>
                                </div>
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
                                <div class="form-hint">Contoh: https://support.google.com, https://example.com/studi-kasus, atau dokumen SOP internal tim.</div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <label class="form-label mb-0" for="bio_penulis">Bio Penulis</label>
                                    <button type="button" class="ikon-bantuan-form" data-bs-toggle="tooltip" title="Bio penulis membantu pembaca memahami latar belakang dan kredibilitas penulis artikel ini.">i</button>
                                </div>
                                <textarea class="form-control" id="bio_penulis" name="bio_penulis" rows="4">{{ $bioPenulis }}</textarea>
                                <div class="form-hint">Contoh: Tim editorial Simarketing yang fokus pada strategi leads, konten edukasi, dan optimasi funnel pemasaran pendidikan.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div>
                            <h3 class="card-title mb-1">Struktur SEO</h3>
                            <div class="text-secondary small">Susun outline dan preview snippet agar struktur artikel dan metadata mesin pencari sudah terbayang sejak awal.</div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <label class="form-label mb-0" for="outline_seo">Outline SEO</label>
                                    <button type="button" class="ikon-bantuan-form" data-bs-toggle="tooltip" title="Gunakan heading bertingkat untuk memetakan alur artikel sebelum atau sambil menulis. Format sederhana seperti #, ##, dan ## sudah cukup.">i</button>
                                </div>
                                <div class="d-flex flex-wrap gap-2 mb-2 toolbar-template-artikel">
                                    @foreach ($templateStrukturSeo as $template)
                                        <button
                                            type="button"
                                            class="btn btn-outline-primary btn-sm"
                                            data-outline-template="{{ $template['aksi'] }}"
                                            data-bs-toggle="tooltip"
                                            title="{{ $template['tooltip'] }}"
                                        >
                                            {{ $template['label'] }}
                                        </button>
                                    @endforeach
                                    <button
                                        type="button"
                                        class="btn btn-outline-primary btn-sm"
                                        data-generate-outline
                                        data-bs-toggle="tooltip"
                                        title="Buat draft outline otomatis berdasarkan judul dan keyword utama yang sedang aktif."
                                    >
                                        Generate Outline
                                    </button>
                                    <button
                                        type="button"
                                        class="btn btn-outline-secondary btn-sm"
                                        data-salin-outline
                                        data-bs-toggle="tooltip"
                                        title="Salin isi outline SEO saat ini ke clipboard."
                                    >
                                        Salin Struktur
                                    </button>
                                    <button
                                        type="button"
                                        class="btn btn-outline-secondary btn-sm"
                                        data-generate-faq-keyword
                                        data-bs-toggle="tooltip"
                                        title="Buat blok FAQ otomatis dari keyword turunan yang Anda isi."
                                    >
                                        FAQ dari Keyword
                                    </button>
                                    <button
                                        type="button"
                                        class="btn btn-outline-secondary btn-sm"
                                        data-generate-paa
                                        data-bs-toggle="tooltip"
                                        title="Tambahkan blok People also ask dari keyword utama dan keyword turunan."
                                    >
                                        People also ask
                                    </button>
                                </div>
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-2 toolbar-template-artikel">
                                    <span class="small text-secondary me-1">Generator per tipe:</span>
                                    <button type="button" class="btn btn-outline-dark btn-sm" data-outline-generator="how-to">How-to SEO</button>
                                    <button type="button" class="btn btn-outline-dark btn-sm" data-outline-generator="listicle">Listicle SEO</button>
                                    <button type="button" class="btn btn-outline-dark btn-sm" data-outline-generator="faq">FAQ SEO</button>
                                    <button type="button" class="btn btn-outline-dark btn-sm" data-outline-generator="landing-seo">Landing SEO</button>
                                </div>
                                <textarea class="form-control font-monospace" id="outline_seo" name="outline_seo" rows="10" data-outline-seo>{{ $outlineSeo }}</textarea>
                                <div class="form-hint">Contoh: `# Strategi Follow Up` lalu `## Kesalahan Umum`, `## Template Pesan`, `## KPI yang Dipantau`.</div>
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
                            <div class="col-12">
                                <div class="intent-pencarian-panel">
                                    <div class="intent-pencarian-head">
                                        <div>
                                            <div class="fw-semibold">Indikator Intent Pencarian</div>
                                            <div class="text-secondary small">Dibaca dari keyword utama, keyword turunan, dan judul SEO untuk membantu menyesuaikan tipe konten.</div>
                                        </div>
                                        <span class="intent-pencarian-badge" data-intent-badge>Belum Terbaca</span>
                                    </div>
                                    <div class="intent-pencarian-detail" data-intent-detail>
                                        Isi keyword utama atau keyword turunan agar intent pencarian bisa dipetakan otomatis.
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="intent-pencarian-panel h-100">
                                    <div class="intent-pencarian-head">
                                        <div>
                                            <div class="fw-semibold">People also ask</div>
                                            <div class="text-secondary small">Pertanyaan turunan otomatis untuk memperkaya FAQ, section, atau blok pendukung artikel.</div>
                                        </div>
                                        <span class="badge bg-azure-lt text-azure">Generator</span>
                                    </div>
                                    <div class="intent-section-grid mt-3" data-preview-paa></div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="intent-pencarian-panel h-100">
                                    <div class="intent-pencarian-head">
                                        <div>
                                            <div class="fw-semibold">Intent per Section Outline</div>
                                            <div class="text-secondary small">Membaca setiap heading section untuk melihat apakah alurnya informasional, komersial, transaksional, atau masih terlalu umum.</div>
                                        </div>
                                        <span class="badge bg-dark-lt text-dark">Section</span>
                                    </div>
                                    <div class="intent-section-grid mt-3" data-intent-section-list></div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="preview-mobile-snippet">
                                    <div class="preview-mobile-snippet-head">
                                        <span class="badge bg-dark-lt text-dark">Mobile Preview</span>
                                        <span class="text-secondary small">Simulasi snippet di layar ponsel</span>
                                    </div>
                                    <div class="preview-mobile-snippet-body">
                                        <div class="preview-mobile-snippet-url" data-snippet-mobile-url>
                                            {{ parse_url(config('app.url'), PHP_URL_HOST) ?: 'example.com' }}/tools/artikel/{{ $slug ?: 'judul-artikel' }}
                                        </div>
                                        <div class="preview-mobile-snippet-title" data-snippet-mobile-title>
                                            {{ $judulSeo ?: ($judul ?: 'Judul artikel Anda akan tampil di sini') }}
                                        </div>
                                        <div class="preview-mobile-snippet-description" data-snippet-mobile-description>
                                            {{ $deskripsiSeo ?: ($ringkasan ?: 'Deskripsi artikel akan tampil di sini.') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="preview-jsonld-seo">
                                    <div class="preview-jsonld-seo-head">
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <span class="badge bg-azure-lt text-azure">Preview FAQ JSON-LD</span>
                                            <span class="text-secondary small">Terbentuk otomatis dari heading pertanyaan di outline SEO</span>
                                        </div>
                                        <button
                                            type="button"
                                            class="btn btn-outline-secondary btn-sm"
                                            data-salin-jsonld
                                            data-bs-toggle="tooltip"
                                            title="Salin schema FAQ JSON-LD yang sudah terbentuk."
                                        >
                                            Salin JSON-LD
                                        </button>
                                    </div>
                                    <pre class="preview-jsonld-seo-body" data-preview-faq-jsonld></pre>
                                    <div class="fw-semibold small text-uppercase mt-3 mb-2">Validator Struktur FAQ</div>
                                    <div class="validator-faq-grid mt-3" data-validator-faq></div>
                                    <div class="fw-semibold small text-uppercase mt-3 mb-2">Schema Readiness</div>
                                    <div class="schema-readiness-score mb-3" data-schema-score></div>
                                    <div class="validator-faq-grid" data-schema-readiness></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h3 class="card-title mb-1">Kesiapan Publikasi</h3>
                            <div class="text-secondary small">Panel ini membaca isi form Anda secara langsung untuk menunjukkan area yang sudah siap dan yang masih perlu diperbaiki.</div>
                        </div>
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
                        <div>
                            <h3 class="card-title mb-1">Pengaturan Artikel</h3>
                            <div class="text-secondary small">Atur kategori, level pembaca, metadata SEO, jadwal terbit, dan media pendukung artikel.</div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <label class="form-label mb-0" for="kategori_artikel_id">Kategori</label>
                                    <button type="button" class="ikon-bantuan-form" data-bs-toggle="tooltip" title="Kategori dipakai untuk pengelompokan daftar artikel, filtering editorial, dan konteks topik utama.">i</button>
                                </div>
                                <select class="form-select" id="kategori_artikel_id" name="kategori_artikel_id" required>
                                    <option value="">Pilih Kategori</option>
                                    @foreach ($daftarKategori as $itemKategori)
                                        <option value="{{ $itemKategori->id }}" @selected((string) $kategoriArtikelId === (string) $itemKategori->id)>{{ $itemKategori->nama }}</option>
                                    @endforeach
                                </select>
                                <div class="form-hint">Contoh: SEO, Leads, CRM, Kampanye, atau LMS.</div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <label class="form-label mb-0" for="tingkat_keahlian">Tingkat Keahlian</label>
                                    <button type="button" class="ikon-bantuan-form" data-bs-toggle="tooltip" title="Pilih tingkat keahlian pembaca sasaran agar bahasa dan kedalaman penjelasan artikel tetap konsisten.">i</button>
                                </div>
                                <select class="form-select" id="tingkat_keahlian" name="tingkat_keahlian" required>
                                    @foreach (\App\Models\Artikel::TINGKAT_KEAHLIAN as $nilai => $label)
                                        <option value="{{ $nilai }}" @selected($tingkatKeahlian === $nilai)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <div class="form-hint">Contoh: pilih `Pemula` untuk artikel edukasi dasar, `Menengah` untuk playbook tim, dan `Ahli` untuk bahasan teknis.</div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <label class="form-label mb-0" for="judul_seo">Judul SEO</label>
                                    <button type="button" class="ikon-bantuan-form" data-bs-toggle="tooltip" title="Judul SEO ideal 30-60 karakter dan tetap memuat keyword utama tanpa terasa dipaksakan.">i</button>
                                </div>
                                <input type="text" class="form-control" id="judul_seo" name="judul_seo" value="{{ $judulSeo }}" maxlength="60">
                                <div class="d-flex justify-content-between align-items-center gap-3 mt-1">
                                    <div class="form-hint mb-0">Contoh: Template Follow Up WhatsApp untuk Closing Leads Bimbel.</div>
                                    <div class="seo-counter" data-judul-seo-counter aria-live="polite"></div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <label class="form-label mb-0" for="deskripsi_seo">Deskripsi SEO</label>
                                    <button type="button" class="ikon-bantuan-form" data-bs-toggle="tooltip" title="Deskripsi SEO ideal 120-160 karakter. Fokus pada manfaat utama dan ajakan baca, bukan daftar keyword.">i</button>
                                </div>
                                <textarea class="form-control" id="deskripsi_seo" name="deskripsi_seo" rows="4" maxlength="160">{{ $deskripsiSeo }}</textarea>
                                <div class="d-flex justify-content-between align-items-center gap-3 mt-1">
                                    <div class="form-hint mb-0">Contoh: Pelajari cara follow up WhatsApp yang lebih rapi dan efektif agar calon siswa lebih cepat merespons.</div>
                                    <div class="seo-counter" data-deskripsi-seo-counter aria-live="polite"></div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <label class="form-label mb-0" for="diterbitkan_pada">Jadwal Terbit</label>
                                    <button type="button" class="ikon-bantuan-form" data-bs-toggle="tooltip" title="Kosongkan jika artikel akan diterbitkan langsung. Isi tanggal dan jam masa depan untuk penjadwalan terbit.">i</button>
                                </div>
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
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <label class="form-label mb-0" for="gambar_unggulan">Gambar Unggulan</label>
                                    <button type="button" class="ikon-bantuan-form" data-bs-toggle="tooltip" title="Gunakan gambar yang relevan dengan topik, cukup tajam, dan aman dipakai untuk publikasi.">i</button>
                                </div>
                                <input type="file" class="form-control" id="gambar_unggulan" name="gambar_unggulan" accept="image/*">
                                <div class="form-hint">Contoh: banner ilustrasi funnel, dashboard, chat WhatsApp, atau foto kelas yang relevan.</div>
                                @if ($artikel?->url_gambar_unggulan && ! $hapusGambarUnggulan)
                                    <div class="mt-3">
                                        <img src="{{ $artikel->url_gambar_unggulan }}" alt="{{ $artikel->alt_gambar_unggulan ?: $artikel->judul }}"
                                            class="img-fluid rounded border">
                                    </div>
                                @endif
                            </div>
                            <div class="col-12">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <label class="form-label mb-0" for="alt_gambar_unggulan">Alt Gambar</label>
                                    <button type="button" class="ikon-bantuan-form" data-bs-toggle="tooltip" title="Alt text menjelaskan isi gambar untuk aksesibilitas dan konteks mesin pencari. Jelaskan apa yang benar-benar terlihat.">i</button>
                                </div>
                                <input type="text" class="form-control" id="alt_gambar_unggulan" name="alt_gambar_unggulan" value="{{ $altGambarUnggulan }}">
                                <div class="form-hint">Contoh: Dashboard monitoring leads dengan grafik follow up dan status closing.</div>
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
                        <div>
                            <h3 class="card-title mb-1">Checklist Editorial</h3>
                            <div class="text-secondary small">Centang satu per satu saat proses review selesai supaya status siap terbit benar-benar mencerminkan kondisi artikel.</div>
                        </div>
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
                        <div class="form-hint mt-3">Contoh penggunaan: centang setelah keyword dicek, metadata final disetujui, dan referensi diverifikasi.</div>
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
                const keywordTurunan = form.querySelector('[data-keyword-turunan]');
                const ringkasan = form.querySelector('#ringkasan');
                const konten = form.querySelector('#konten');
                const bioPenulis = form.querySelector('#bio_penulis');
                const altGambarUnggulan = form.querySelector('#alt_gambar_unggulan');
                const judulSeo = form.querySelector('#judul_seo');
                const deskripsiSeo = form.querySelector('#deskripsi_seo');
                const outlineSeo = form.querySelector('[data-outline-seo]');
                const sumberContainer = editor.querySelector('[data-sumber-container]');
                const tombolTambahSumber = editor.querySelector('[data-tambah-sumber]');
                const tombolEditorKonten = editor.querySelectorAll('[data-editor-action]');
                const tombolTemplateKonten = editor.querySelectorAll('[data-editor-template]');
                const tombolTemplateOutline = editor.querySelectorAll('[data-outline-template]');
                const tombolGenerateOutline = editor.querySelector('[data-generate-outline]');
                const tombolGeneratorOutlineTipe = editor.querySelectorAll('[data-outline-generator]');
                const tombolSalinOutline = editor.querySelector('[data-salin-outline]');
                const tombolSalinJsonLd = editor.querySelector('[data-salin-jsonld]');
                const tombolGenerateFaqKeyword = editor.querySelector('[data-generate-faq-keyword]');
                const tombolGeneratePaa = editor.querySelector('[data-generate-paa]');
                const statusAutosimpan = editor.querySelector('[data-status-autosimpan]');
                const autosimpanUrl = editor.dataset.autosimpanUrl || '';
                const skorKesiapan = editor.querySelector('[data-skor-kesiapan]');
                const labelKesiapan = editor.querySelector('[data-label-kesiapan]');
                const progressKesiapan = editor.querySelector('[data-progress-kesiapan]');
                const daftarKesiapan = editor.querySelector('[data-daftar-kesiapan]');
                const snippetUrl = editor.querySelector('[data-snippet-url]');
                const snippetTitle = editor.querySelector('[data-snippet-title]');
                const snippetDescription = editor.querySelector('[data-snippet-description]');
                const snippetMobileUrl = editor.querySelector('[data-snippet-mobile-url]');
                const snippetMobileTitle = editor.querySelector('[data-snippet-mobile-title]');
                const snippetMobileDescription = editor.querySelector('[data-snippet-mobile-description]');
                const judulSeoCounter = editor.querySelector('[data-judul-seo-counter]');
                const deskripsiSeoCounter = editor.querySelector('[data-deskripsi-seo-counter]');
                const previewFaqJsonLd = editor.querySelector('[data-preview-faq-jsonld]');
                const validatorFaq = editor.querySelector('[data-validator-faq]');
                const schemaReadiness = editor.querySelector('[data-schema-readiness]');
                const schemaScore = editor.querySelector('[data-schema-score]');
                const intentBadge = editor.querySelector('[data-intent-badge]');
                const intentDetail = editor.querySelector('[data-intent-detail]');
                const previewPeopleAlsoAsk = editor.querySelector('[data-preview-paa]');
                const intentSectionList = editor.querySelector('[data-intent-section-list]');
                const previewKonten = editor.querySelector('[data-preview-konten]');

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

                const fokusTextarea = (textarea, start, end = start) => {
                    textarea.focus();
                    textarea.setSelectionRange(start, end);
                };

                const bungkusSeleksi = (textarea, pembuka, penutup, fallback = '') => {
                    const start = textarea.selectionStart ?? 0;
                    const end = textarea.selectionEnd ?? 0;
                    const teksTerpilih = textarea.value.slice(start, end) || fallback;
                    const hasil = `${pembuka}${teksTerpilih}${penutup}`;

                    textarea.setRangeText(hasil, start, end, 'end');
                    fokusTextarea(textarea, start + pembuka.length, start + pembuka.length + teksTerpilih.length);
                };

                const sisipkanTemplate = (textarea, template, posisiKursor = null) => {
                    const start = textarea.selectionStart ?? textarea.value.length;
                    const end = textarea.selectionEnd ?? start;

                    textarea.setRangeText(template, start, end, 'end');

                    const posisiAkhir = posisiKursor === null ? start + template.length : start + posisiKursor;
                    fokusTextarea(textarea, posisiAkhir);
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

                    if (snippetMobileUrl) {
                        snippetMobileUrl.textContent = `${host}/tools/artikel/${slugValue}`;
                    }

                    if (snippetTitle) {
                        snippetTitle.textContent = titleValue;
                    }

                    if (snippetMobileTitle) {
                        snippetMobileTitle.textContent = titleValue;
                    }

                    if (snippetDescription) {
                        snippetDescription.textContent = descriptionValue;
                    }

                    if (snippetMobileDescription) {
                        snippetMobileDescription.textContent = descriptionValue;
                    }
                };

                const renderCounterSeo = (target, panjang, minimumIdeal, maksimumIdeal) => {
                    if (!target) {
                        return;
                    }

                    const status = panjang === 0
                        ? 'kosong'
                        : (panjang >= minimumIdeal && panjang <= maksimumIdeal ? 'ideal' : 'perlu');

                    const kelas = status === 'ideal'
                        ? 'seo-counter-badge status-ideal'
                        : (status === 'kosong' ? 'seo-counter-badge status-kosong' : 'seo-counter-badge status-perlu');

                    const label = status === 'ideal'
                        ? 'Ideal'
                        : (status === 'kosong' ? 'Kosong' : 'Cek');

                    target.innerHTML = `
                        <span class="${kelas}">${label}</span>
                        <span class="seo-counter-meta">${panjang} karakter • target ${minimumIdeal}-${maksimumIdeal}</span>
                    `;
                };

                const renderCounterMetadataSeo = () => {
                    renderCounterSeo(judulSeoCounter, (judulSeo?.value || '').trim().length, 30, 60);
                    renderCounterSeo(deskripsiSeoCounter, (deskripsiSeo?.value || '').trim().length, 120, 160);
                };

                const daftarKeywordTurunan = () => (keywordTurunan?.value || '')
                    .split(/\r?\n|,/)
                    .map((item) => item.trim())
                    .filter(Boolean);

                const petaIntentPencarian = () => [
                    {
                        kode: 'transaksional',
                        label: 'Transaksional',
                        warna: 'status-transaksional',
                        regex: /\b(daftar|beli|booking|hubungi|konsultasi|jasa|layanan|paket|promo|diskon|harga|biaya)\b/,
                        alasan: 'Keyword mengarah ke aksi langsung, penawaran, atau intent closing.',
                    },
                    {
                        kode: 'komersial',
                        label: 'Komersial',
                        warna: 'status-komersial',
                        regex: /\b(terbaik|review|vs|perbandingan|rekomendasi|pilihan)\b/,
                        alasan: 'Keyword menunjukkan pembaca sedang membandingkan opsi sebelum memutuskan.',
                    },
                    {
                        kode: 'navigasional',
                        label: 'Navigasional',
                        warna: 'status-navigasional',
                        regex: /\b(login|masuk|kontak|alamat|website resmi|official)\b/,
                        alasan: 'Keyword terlihat seperti pencarian menuju brand, halaman, atau lokasi tertentu.',
                    },
                    {
                        kode: 'informasional',
                        label: 'Informasional',
                        warna: 'status-informasional',
                        regex: /\b(cara|apa itu|panduan|tutorial|contoh|tips|strategi|langkah)\b/,
                        alasan: 'Keyword menunjukkan intent belajar atau mencari penjelasan.',
                    },
                ];

                const bacaIntentDariTeks = (teks) => {
                    const gabungan = (teks || '').toLowerCase();

                    return petaIntentPencarian().find((item) => item.regex.test(gabungan)) || {
                        kode: 'belum-terbaca',
                        label: 'Belum Terbaca',
                        warna: 'status-belum',
                        alasan: 'Isi keyword utama atau keyword turunan lebih spesifik agar intent pencarian bisa dipetakan.',
                    };
                };

                const bacaIntentPencarian = () => {
                    const gabungan = [
                        kataKunciUtama?.value || '',
                        keywordTurunan?.value || '',
                        judulSeo?.value || '',
                        judul?.value || '',
                    ].join(' ');

                    return bacaIntentDariTeks(gabungan);
                };

                const bacaBagianOutline = () => {
                    const baris = (outlineSeo?.value || '').split(/\r?\n/);
                    const bagian = [];
                    let aktif = null;

                    const simpanAktif = () => {
                        if (!aktif || !aktif.judul) {
                            return;
                        }

                        aktif.isi = aktif.isi
                            .map((item) => item.trim())
                            .filter(Boolean);

                        bagian.push(aktif);
                        aktif = null;
                    };

                    baris.forEach((barisItem) => {
                        const nilai = barisItem.trim();

                        if (nilai === '') {
                            return;
                        }

                        const heading = nilai.match(/^(#{2,6})\s+(.+)$/);

                        if (heading) {
                            simpanAktif();
                            aktif = {
                                level: heading[1].length,
                                judul: heading[2].trim(),
                                isi: [],
                            };

                            return;
                        }

                        if (aktif) {
                            aktif.isi.push(nilai.replace(/^[-*]\s+/, '').trim());
                        }
                    });

                    simpanAktif();

                    return bagian;
                };

                const daftarPeopleAlsoAsk = () => {
                    const topikUtama = (kataKunciUtama?.value || judul?.value || '').trim();
                    const sumber = [topikUtama, ...daftarKeywordTurunan()]
                        .map((item) => item.replace(/\s+/g, ' ').trim())
                        .filter(Boolean);
                    const unik = [...new Set(sumber)];

                    if (unik.length === 0) {
                        return [];
                    }

                    const pola = [
                        {
                            pembuka: 'Apa yang perlu diketahui tentang',
                            detail: 'Cocok untuk definisi, konteks, dan pengantar topik.',
                        },
                        {
                            pembuka: 'Bagaimana cara memulai',
                            detail: 'Cocok untuk menjawab langkah awal yang paling sering dicari.',
                        },
                        {
                            pembuka: 'Kenapa',
                            penutup: 'penting untuk dipertimbangkan?',
                            detail: 'Cocok untuk menjelaskan urgensi dan manfaat topik.',
                        },
                        {
                            pembuka: 'Berapa biaya atau effort untuk',
                            detail: 'Cocok untuk keyword dengan intent transaksional atau keputusan.',
                        },
                    ];

                    return unik.slice(0, 5).flatMap((keyword, index) => pola.map((template, offset) => {
                        const kata = keyword.replace(/[?.!]+$/g, '').trim();
                        const pembuka = template.pembuka;
                        const penutup = template.penutup ?? `${kata}?`;

                        return {
                            pertanyaan: pembuka === 'Kenapa'
                                ? `${pembuka} ${kata} ${penutup}`
                                : `${pembuka} ${kata}?`,
                            detail: template.detail,
                            urutan: index * 10 + offset,
                        };
                    }))
                        .sort((a, b) => a.urutan - b.urutan)
                        .map(({ pertanyaan, detail }) => ({ pertanyaan, detail }))
                        .filter((item, index, array) => array.findIndex((pembanding) => pembanding.pertanyaan === item.pertanyaan) === index)
                        .slice(0, 6);
                };

                const renderIntentPencarian = () => {
                    if (!intentBadge || !intentDetail) {
                        return;
                    }

                    const intent = bacaIntentPencarian();
                    intentBadge.className = `intent-pencarian-badge ${intent.warna}`;
                    intentBadge.textContent = intent.label;
                    intentDetail.textContent = intent.alasan;
                };

                const renderPreviewPeopleAlsoAsk = () => {
                    if (!previewPeopleAlsoAsk) {
                        return;
                    }

                    const daftar = daftarPeopleAlsoAsk();

                    if (daftar.length === 0) {
                        previewPeopleAlsoAsk.innerHTML = `
                            <div class="intent-section-empty">
                                Isi keyword utama atau keyword turunan agar pertanyaan People also ask bisa digenerate.
                            </div>
                        `;
                        return;
                    }

                    previewPeopleAlsoAsk.innerHTML = daftar.map((item, index) => `
                        <div class="intent-section-item">
                            <div class="intent-section-head">
                                <span class="intent-section-order">PAA ${index + 1}</span>
                            </div>
                            <div class="intent-section-title">${item.pertanyaan}</div>
                            <div class="intent-section-detail">${item.detail}</div>
                        </div>
                    `).join('');
                };

                const renderIntentPerSection = () => {
                    if (!intentSectionList) {
                        return;
                    }

                    const bagian = bacaBagianOutline();

                    if (bagian.length === 0) {
                        intentSectionList.innerHTML = `
                            <div class="intent-section-empty">
                                Tambahkan heading seperti <strong>## Masalah Utama</strong> atau <strong>## Cara Memulai</strong> agar intent per section bisa dibaca.
                            </div>
                        `;
                        return;
                    }

                    intentSectionList.innerHTML = bagian.map((item, index) => {
                        const intent = bacaIntentDariTeks(`${item.judul} ${(item.isi || []).join(' ')}`);

                        return `
                            <div class="intent-section-item">
                                <div class="intent-section-head">
                                    <span class="intent-section-order">Bagian ${index + 1}</span>
                                    <span class="intent-pencarian-badge ${intent.warna}">${intent.label}</span>
                                </div>
                                <div class="intent-section-title">${item.judul}</div>
                                <div class="intent-section-detail">${intent.alasan}</div>
                            </div>
                        `;
                    }).join('');
                };

                const ekstrakFaqDariOutline = () => {
                    const baris = (outlineSeo?.value || '').split(/\r?\n/);
                    const faq = [];
                    let aktif = null;

                    const simpanAktif = () => {
                        if (!aktif) {
                            return;
                        }

                        const jawaban = aktif.jawaban
                            .map((item) => item.trim())
                            .filter(Boolean)
                            .join(' ');

                        if (aktif.pertanyaan && jawaban) {
                            faq.push({
                                pertanyaan: aktif.pertanyaan,
                                jawaban,
                            });
                        }

                        aktif = null;
                    };

                    baris.forEach((barisItem) => {
                        const nilai = barisItem.trim();

                        if (nilai === '') {
                            return;
                        }

                        if (/^#{2,6}\s+/.test(nilai)) {
                            simpanAktif();

                            const judulFaq = nilai.replace(/^#{2,6}\s+/, '').trim();

                            if (judulFaq.endsWith('?')) {
                                aktif = {
                                    pertanyaan: judulFaq,
                                    jawaban: [],
                                };
                            }

                            return;
                        }

                        if (aktif) {
                            aktif.jawaban.push(nilai.replace(/^[-*]\s+/, '').trim());
                        }
                    });

                    simpanAktif();

                    return faq;
                };

                const renderValidatorFaq = () => {
                    if (!validatorFaq) {
                        return;
                    }

                    const faq = ekstrakFaqDariOutline();
                    const jawabanInformatif = faq.filter((item) => item.jawaban.length >= 60).length;
                    const checks = [
                        {
                            label: 'Minimal 3 pertanyaan FAQ',
                            ok: faq.length >= 3,
                            detail: faq.length >= 3 ? `${faq.length} pertanyaan terdeteksi.` : `Baru ${faq.length} pertanyaan. Tambahkan lagi agar schema lebih kuat.`,
                        },
                        {
                            label: 'Setiap pertanyaan punya jawaban',
                            ok: faq.length > 0,
                            detail: faq.length > 0 ? 'Semua pertanyaan FAQ yang terbaca sudah punya jawaban.' : 'Belum ada pasangan pertanyaan-jawaban FAQ yang siap dirender.',
                        },
                        {
                            label: 'Jawaban cukup informatif',
                            ok: faq.length > 0 && jawabanInformatif === faq.length,
                            detail: faq.length > 0 && jawabanInformatif === faq.length ? 'Jawaban FAQ sudah cukup panjang untuk schema.' : 'Perpanjang jawaban FAQ agar tidak terlalu singkat.',
                        },
                        {
                            label: 'Schema siap disalin',
                            ok: faq.length >= 2,
                            detail: faq.length >= 2 ? 'Preview JSON-LD siap dipakai sebagai FAQ schema.' : 'Butuh minimal 2 FAQ siap agar schema layak dipasang.',
                        },
                    ];

                    validatorFaq.innerHTML = checks.map((item) => `
                        <div class="validator-faq-item ${item.ok ? 'is-ok' : 'is-check'}">
                            <div class="validator-faq-badge">${item.ok ? 'OK' : 'Cek'}</div>
                            <div>
                                <div class="validator-faq-title">${item.label}</div>
                                <div class="validator-faq-detail">${item.detail}</div>
                            </div>
                        </div>
                    `).join('');
                };

                const bacaSchemaReadiness = () => {
                    const faq = ekstrakFaqDariOutline();
                    const bagian = bacaBagianOutline();
                    const checks = [
                        {
                            label: 'Article metadata inti',
                            ok: (judulSeo?.value || '').trim() !== '' && (deskripsiSeo?.value || '').trim() !== '' && (ringkasan?.value || '').trim() !== '',
                            detail: 'Periksa judul SEO, deskripsi SEO, dan ringkasan sebagai fondasi Article schema.',
                        },
                        {
                            label: 'Author context tersedia',
                            ok: (bioPenulis?.value || '').trim() !== '',
                            detail: 'Bio penulis membantu memperkuat identitas author pada metadata artikel.',
                        },
                        {
                            label: 'FAQ schema siap',
                            ok: faq.length >= 2,
                            detail: 'Minimal 2 FAQ lengkap dibutuhkan agar schema FAQ layak dipasang.',
                        },
                        {
                            label: 'Media context tersedia',
                            ok: (altGambarUnggulan?.value || '').trim() !== '',
                            detail: 'Alt gambar membantu memberi konteks visual untuk metadata artikel.',
                        },
                        {
                            label: 'Struktur article body siap',
                            ok: bagian.length >= 3,
                            detail: 'Minimal 3 section outline membantu struktur Article schema lebih utuh.',
                        },
                    ];

                    const lolos = checks.filter((item) => item.ok).length;
                    const total = Math.max(checks.length, 1);
                    const persentase = Math.round((lolos / total) * 100);

                    return {
                        checks,
                        lolos,
                        total,
                        persentase,
                        cukupSiap: persentase >= 80,
                    };
                };

                const renderSchemaReadiness = () => {
                    if (!schemaReadiness) {
                        return;
                    }

                    const schema = bacaSchemaReadiness();

                    if (schemaScore) {
                        const warna = schema.persentase >= 80 ? 'green' : (schema.persentase >= 60 ? 'yellow' : 'red');

                        schemaScore.innerHTML = `
                            <div class="schema-readiness-head">
                                <span class="schema-readiness-value">${schema.persentase}%</span>
                                <span class="badge bg-${warna}-lt text-${warna}">${schema.lolos}/${schema.total} komponen siap</span>
                            </div>
                            <div class="schema-readiness-detail">Skor ini dirangkum dari Article metadata, author context, FAQ schema, media context, dan struktur section outline.</div>
                        `;
                    }

                    schemaReadiness.innerHTML = schema.checks.map((item) => `
                        <div class="validator-faq-item ${item.ok ? 'is-ok' : 'is-check'}">
                            <div class="validator-faq-badge">${item.ok ? 'OK' : 'Cek'}</div>
                            <div>
                                <div class="validator-faq-title">${item.label}</div>
                                <div class="validator-faq-detail">${item.detail}</div>
                            </div>
                        </div>
                    `).join('');
                };

                const renderPreviewFaqJsonLd = () => {
                    if (!previewFaqJsonLd) {
                        return;
                    }

                    const faq = ekstrakFaqDariOutline();

                    if (faq.length === 0) {
                        previewFaqJsonLd.textContent = [
                            '{',
                            '  "\u0040context": "https://schema.org",',
                            '  "\u0040type": "FAQPage",',
                            '  "mainEntity": []',
                            '}',
                            '',
                            '// Tambahkan heading pertanyaan seperti "## Apa itu ...?"',
                            '// lalu isi jawaban di baris bawahnya untuk membentuk FAQ schema.'
                        ].join('\n');

                        return;
                    }

                    const schema = {
                        '\u0040context': 'https://schema.org',
                        '\u0040type': 'FAQPage',
                        mainEntity: faq.map((item) => ({
                            '\u0040type': 'Question',
                            name: item.pertanyaan,
                            acceptedAnswer: {
                                '\u0040type': 'Answer',
                                text: item.jawaban,
                            },
                        })),
                    };

                    previewFaqJsonLd.textContent = JSON.stringify(schema, null, 2);
                };

                const renderPreviewKonten = () => {
                    if (!previewKonten) {
                        return;
                    }

                    const isiKonten = (konten?.value || '').trim();

                    if (isiKonten === '') {
                        previewKonten.innerHTML = `
                            <div class="preview-konten-kosong">
                                <p class="fw-semibold mb-2">Belum ada konten untuk dipreview.</p>
                                <p class="mb-0">Mulai menulis atau gunakan template cepat seperti <strong>How-to</strong>, <strong>Problem-Solution-CTA</strong>, atau <strong>Listicle</strong>.</p>
                            </div>
                        `;
                        return;
                    }

                    previewKonten.innerHTML = isiKonten;
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
                    const schema = bacaSchemaReadiness();
                    const intentPerSection = (() => {
                        const bagian = bacaBagianOutline();
                        const terbaca = bagian.filter((item) => bacaIntentDariTeks(`${item.judul} ${(item.isi || []).join(' ')}`).kode !== 'belum-terbaca').length;
                        const total = Math.max(bagian.length, 1);
                        const persentase = bagian.length === 0 ? 0 : Math.round((terbaca / total) * 100);

                        return {
                            total: bagian.length,
                            terbaca,
                            persentase,
                            cukupTerbaca: bagian.length >= 2 && persentase >= 60,
                        };
                    })();

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
                        {
                            judul: 'Schema readiness cukup kuat',
                            ok: schema.cukupSiap,
                            bobot: 10,
                            pesan: schema.cukupSiap
                                ? `Skor schema ${schema.persentase}% dengan ${schema.lolos} dari ${schema.total} komponen siap.`
                                : `Skor schema baru ${schema.persentase}%. Lengkapi metadata, FAQ, media, dan struktur section.`,
                        },
                        {
                            judul: 'Intent per section outline terbaca',
                            ok: intentPerSection.cukupTerbaca,
                            bobot: 10,
                            pesan: intentPerSection.cukupTerbaca
                                ? 'Intent terbaca pada mayoritas section outline.'
                                : `Baru ${intentPerSection.terbaca} dari ${intentPerSection.total} section yang intent-nya cukup jelas.`,
                        },
                    ];

                    const totalBobot = checks.reduce((acc, item) => acc + item.bobot, 0);
                    const nilaiMentah = checks.reduce((acc, item) => acc + (item.ok ? item.bobot : 0), 0);
                    const skor = Math.round((nilaiMentah / Math.max(totalBobot, 1)) * 100);
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

                const jalankanAksiEditorKonten = (aksi) => {
                    if (!konten) {
                        return;
                    }

                    const templatePerAksi = {
                        h1: () => bungkusSeleksi(konten, '<h1>', '</h1>', 'Judul utama bagian'),
                        h2: () => bungkusSeleksi(konten, '<h2>', '</h2>', 'Subjudul utama'),
                        h3: () => bungkusSeleksi(konten, '<h3>', '</h3>', 'Subjudul turunan'),
                        p: () => bungkusSeleksi(konten, '<p>', '</p>', 'Tulis paragraf Anda di sini.'),
                        ul: () => sisipkanTemplate(konten, "<ul>\n  <li>Poin pertama</li>\n  <li>Poin kedua</li>\n</ul>", 11),
                        quote: () => bungkusSeleksi(konten, '<blockquote>', '</blockquote>', 'Masukkan kutipan, insight, atau catatan penting.'),
                        link: () => bungkusSeleksi(konten, '<a href="https://example.com">', '</a>', 'Teks tautan'),
                    };

                    templatePerAksi[aksi]?.();
                    evaluasiKesiapan();
                    renderPreviewSnippet();
                    renderPreviewKonten();
                    jadwalkanAutosimpan();
                };

                const jalankanTemplateKonten = (aksi) => {
                    if (!konten) {
                        return;
                    }

                    const templatePerAksi = {
                        'problem-solution-cta': `<h2>Masalah Utama</h2>
<p>Jelaskan masalah yang sering terjadi, dampaknya, dan kenapa pembaca perlu peduli.</p>

<h2>Solusi yang Disarankan</h2>
<p>Terangkan pendekatan utama yang Anda sarankan.</p>
<ul>
  <li>Poin solusi pertama</li>
  <li>Poin solusi kedua</li>
  <li>Poin solusi ketiga</li>
</ul>

<h2>Langkah Implementasi</h2>
<p>Jabarkan tindakan praktis yang bisa dilakukan pembaca.</p>

<h2>Call to Action</h2>
<p>Arahkan pembaca ke langkah berikutnya, misalnya konsultasi, download, atau membuka halaman terkait.</p>`,
                        'how-to': `<h2>Tujuan</h2>
<p>Jelaskan hasil akhir yang akan didapat pembaca setelah mengikuti panduan ini.</p>

<h2>Persiapan</h2>
<ul>
  <li>Alat atau data yang dibutuhkan</li>
  <li>Akses yang perlu disiapkan</li>
</ul>

<h2>Langkah-langkah</h2>
<h3>Langkah 1</h3>
<p>Jelaskan tindakan pertama secara ringkas dan jelas.</p>
<h3>Langkah 2</h3>
<p>Jelaskan tindakan berikutnya.</p>
<h3>Langkah 3</h3>
<p>Tutup dengan verifikasi hasil atau checklist akhir.</p>`,
                        listicle: `<h2>Daftar Poin Utama</h2>
<p>Beri pengantar singkat tentang daftar yang akan dibahas.</p>

<h3>1. Poin Pertama</h3>
<p>Jelaskan manfaat atau konteks poin pertama.</p>

<h3>2. Poin Kedua</h3>
<p>Tambahkan insight, contoh, atau kesalahan umum.</p>

<h3>3. Poin Ketiga</h3>
<p>Tutup dengan kesimpulan atau langkah lanjut.</p>`,
                    };

                    const template = templatePerAksi[aksi];

                    if (!template) {
                        return;
                    }

                    if ((konten.value || '').trim() !== '' && ! window.confirm('Konten yang sudah ada akan ditambahkan template di posisi kursor. Lanjutkan?')) {
                        return;
                    }

                    sisipkanTemplate(konten, `${template}\n\n`);
                    evaluasiKesiapan();
                    renderPreviewSnippet();
                    renderPreviewKonten();
                    jadwalkanAutosimpan();
                };

                const jalankanTemplateOutline = (aksi) => {
                    if (! outlineSeo) {
                        return;
                    }

                    const templatePerAksi = {
                        'faq-seo': `# FAQ SEO
## Apa itu [topik utama]?
- Definisikan topik utama secara singkat dan jelas.

## Kenapa [topik utama] penting?
- Jelaskan manfaat, dampak, atau urgensinya.

## Bagaimana cara menerapkan [topik utama]?
- Ringkas langkah inti atau pendekatan yang direkomendasikan.

## Kesalahan umum saat menjalankan [topik utama]?
- Daftarkan 2-3 kesalahan yang paling sering terjadi.

## Kapan hasilnya mulai terlihat?
- Beri ekspektasi waktu, indikator, atau metrik evaluasi.`,
                    };

                    const template = templatePerAksi[aksi];

                    if (! template) {
                        return;
                    }

                    if ((outlineSeo.value || '').trim() !== '' && ! window.confirm('Outline SEO yang ada akan ditambahkan template baru di posisi kursor. Lanjutkan?')) {
                        return;
                    }

                    sisipkanTemplate(outlineSeo, `${template}\n\n`);
                    evaluasiKesiapan();
                    renderPreviewSnippet();
                    renderPreviewPeopleAlsoAsk();
                    renderIntentPerSection();
                    renderPreviewFaqJsonLd();
                    renderValidatorFaq();
                    renderSchemaReadiness();
                    renderIntentPencarian();
                    jadwalkanAutosimpan();
                };

                const jalankanGeneratorOutline = () => {
                    if (!outlineSeo) {
                        return;
                    }

                    const judulAktif = (judul?.value || '').trim();
                    const keywordAktif = (kataKunciUtama?.value || '').trim();
                    const topik = keywordAktif || judulAktif || '[topik utama]';
                    const judulStruktur = judulAktif || `Panduan ${topik}`;

                    const template = `# ${judulStruktur}
## Apa masalah utama terkait ${topik}?
- Jelaskan konteks masalah yang dialami audiens dan kenapa perlu segera dibenahi.

## Kenapa ${topik} penting untuk dibahas?
- Terangkan dampak, peluang, atau manfaat langsung bagi pembaca.

## Strategi utama untuk menjalankan ${topik}
- Ringkas 3-4 pendekatan inti yang akan dibahas di artikel.

## Langkah implementasi ${topik}
- Pecah ke langkah praktis yang bisa dilakukan pembaca.

## Kesalahan umum yang harus dihindari
- Tulis kesalahan paling sering yang membuat hasil tidak maksimal.

## FAQ tentang ${topik}?
- Siapkan 3-5 pertanyaan yang sering diajukan calon pembaca.`;

                    if ((outlineSeo.value || '').trim() !== '' && !window.confirm('Outline SEO yang ada akan diganti dengan struktur otomatis baru. Lanjutkan?')) {
                        return;
                    }

                    outlineSeo.value = template;
                    fokusTextarea(outlineSeo, 0, 0);
                    evaluasiKesiapan();
                    renderPreviewSnippet();
                    renderPreviewPeopleAlsoAsk();
                    renderIntentPerSection();
                    renderPreviewFaqJsonLd();
                    renderValidatorFaq();
                    renderSchemaReadiness();
                    renderIntentPencarian();
                    jadwalkanAutosimpan();
                };

                const jalankanGeneratorOutlineBerdasarkanTipe = (tipe) => {
                    if (!outlineSeo) {
                        return;
                    }

                    const judulAktif = (judul?.value || '').trim();
                    const keywordAktif = (kataKunciUtama?.value || '').trim();
                    const topik = keywordAktif || judulAktif || '[topik utama]';
                    const judulStruktur = judulAktif || `Panduan ${topik}`;

                    const templatePerTipe = {
                        'how-to': `# ${judulStruktur}
## Tujuan utama ${topik}
- Jelaskan hasil yang ingin dicapai pembaca.

## Persiapan sebelum menjalankan ${topik}
- Daftar alat, data, atau akses yang harus disiapkan.

## Langkah 1 menjalankan ${topik}
- Uraikan tindakan pertama dan indikator berhasilnya.

## Langkah 2 optimasi ${topik}
- Tambahkan detail praktik terbaik yang perlu diperhatikan.

## Langkah 3 evaluasi hasil ${topik}
- Jelaskan cara mengukur hasil dan kapan perlu penyesuaian.

## FAQ tentang ${topik}?
- Siapkan pertanyaan yang paling sering muncul dari pembaca.`,
                        listicle: `# ${judulStruktur}
## Kenapa ${topik} penting?
- Buka dengan konteks dan manfaat utama.

## 1. Poin utama pertama tentang ${topik}
- Jelaskan insight atau strategi paling penting.

## 2. Poin utama kedua tentang ${topik}
- Tambahkan contoh, data, atau best practice.

## 3. Poin utama ketiga tentang ${topik}
- Tunjukkan kesalahan umum atau quick win.

## 4. Poin utama keempat tentang ${topik}
- Tutup dengan poin yang paling actionable.

## FAQ tentang ${topik}?
- Tambahkan pertanyaan yang mendukung intent pencarian.`,
                        faq: `# ${judulStruktur}
## Apa itu ${topik}?
- Definisikan topik secara ringkas dan mudah dipahami.

## Kenapa ${topik} penting?
- Jelaskan urgensi, manfaat, atau dampaknya.

## Bagaimana cara mulai menjalankan ${topik}?
- Ringkas langkah awal yang paling aman dilakukan.

## Kesalahan umum saat menjalankan ${topik}?
- Sebutkan jebakan yang paling sering terjadi.

## Kapan hasil ${topik} mulai terlihat?
- Beri ekspektasi waktu atau indikator kemajuan.`,
                        'landing-seo': `# ${judulStruktur}
## Solusi utama untuk ${topik}
- Jelaskan value utama dan hasil yang ditawarkan halaman ini.

## Masalah yang dialami target audiens
- Tunjukkan pain point utama yang ingin diselesaikan.

## Kenapa memilih solusi ini?
- Sorot keunggulan, diferensiasi, atau jaminan hasil.

## Layanan atau manfaat utama
- Uraikan 3-5 manfaat yang langsung relevan.

## Bukti sosial dan hasil
- Siapkan tempat untuk testimoni, angka, atau studi kasus singkat.

## CTA utama untuk ${topik}
- Arahkan pembaca ke konsultasi, formulir, atau kontak WhatsApp.

## FAQ tentang ${topik}?
- Isi pertanyaan yang sering muncul sebelum closing.`,
                    };

                    const template = templatePerTipe[tipe];

                    if (!template) {
                        return;
                    }

                    if ((outlineSeo.value || '').trim() !== '' && !window.confirm('Outline SEO yang ada akan diganti dengan struktur tipe artikel baru. Lanjutkan?')) {
                        return;
                    }

                    outlineSeo.value = template;
                    fokusTextarea(outlineSeo, 0, 0);
                    evaluasiKesiapan();
                    renderPreviewSnippet();
                    renderPreviewPeopleAlsoAsk();
                    renderIntentPerSection();
                    renderPreviewFaqJsonLd();
                    renderValidatorFaq();
                    renderSchemaReadiness();
                    renderIntentPencarian();
                    jadwalkanAutosimpan();
                };

                const jalankanGeneratorFaqDariKeyword = () => {
                    if (!outlineSeo) {
                        return;
                    }

                    const keywords = daftarKeywordTurunan();

                    if (keywords.length === 0) {
                        window.alert('Isi keyword turunan dulu agar FAQ bisa digenerate.');
                        return;
                    }

                    const blokFaq = keywords.slice(0, 5).map((keyword) => {
                        const topik = keyword.replace(/\s+/g, ' ').trim();

                        return `## Apa yang perlu diketahui tentang ${topik}?\n- Jelaskan definisi, konteks, atau manfaat utama terkait ${topik}.\n\n## Bagaimana cara menerapkan ${topik}?\n- Beri jawaban praktis atau langkah yang bisa langsung dijalankan.`;
                    }).join('\n\n');

                    const template = `# FAQ dari Keyword Turunan\n${blokFaq}`;

                    if ((outlineSeo.value || '').trim() !== '' && !window.confirm('Outline SEO yang ada akan diganti dengan FAQ dari keyword turunan. Lanjutkan?')) {
                        return;
                    }

                    outlineSeo.value = template;
                    fokusTextarea(outlineSeo, 0, 0);
                    evaluasiKesiapan();
                    renderPreviewSnippet();
                    renderPreviewPeopleAlsoAsk();
                    renderIntentPerSection();
                    renderPreviewFaqJsonLd();
                    renderValidatorFaq();
                    renderSchemaReadiness();
                    renderIntentPencarian();
                    jadwalkanAutosimpan();
                };

                const jalankanGeneratorPeopleAlsoAsk = () => {
                    if (!outlineSeo) {
                        return;
                    }

                    const pertanyaan = daftarPeopleAlsoAsk();

                    if (pertanyaan.length === 0) {
                        window.alert('Isi keyword utama atau keyword turunan dulu agar People also ask bisa digenerate.');
                        return;
                    }

                    const blokPaa = `# People Also Ask\n${pertanyaan.map((item) => `## ${item.pertanyaan}\n- Jawab pertanyaan ini secara singkat, jelas, dan langsung menjawab intent pembaca.`).join('\n\n')}`;

                    if ((outlineSeo.value || '').trim() !== '' && !window.confirm('Blok People also ask akan ditambahkan di posisi kursor outline SEO. Lanjutkan?')) {
                        return;
                    }

                    sisipkanTemplate(outlineSeo, `${blokPaa}\n\n`);
                    evaluasiKesiapan();
                    renderPreviewSnippet();
                    renderPreviewPeopleAlsoAsk();
                    renderIntentPerSection();
                    renderPreviewFaqJsonLd();
                    renderValidatorFaq();
                    renderSchemaReadiness();
                    renderIntentPencarian();
                    jadwalkanAutosimpan();
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

                tombolEditorKonten.forEach((tombol) => {
                    tombol.addEventListener('click', () => {
                        jalankanAksiEditorKonten(tombol.dataset.editorAction);
                    });
                });

                tombolTemplateKonten.forEach((tombol) => {
                    tombol.addEventListener('click', () => {
                        jalankanTemplateKonten(tombol.dataset.editorTemplate);
                    });
                });

                tombolTemplateOutline.forEach((tombol) => {
                    tombol.addEventListener('click', () => {
                        jalankanTemplateOutline(tombol.dataset.outlineTemplate);
                    });
                });

                tombolGenerateOutline?.addEventListener('click', () => {
                    jalankanGeneratorOutline();
                });

                tombolGeneratorOutlineTipe.forEach((tombol) => {
                    tombol.addEventListener('click', () => {
                        jalankanGeneratorOutlineBerdasarkanTipe(tombol.dataset.outlineGenerator);
                    });
                });

                tombolGenerateFaqKeyword?.addEventListener('click', () => {
                    jalankanGeneratorFaqDariKeyword();
                });

                tombolGeneratePaa?.addEventListener('click', () => {
                    jalankanGeneratorPeopleAlsoAsk();
                });

                tombolSalinOutline?.addEventListener('click', async () => {
                    const teksAwal = tombolSalinOutline.textContent;
                    const nilai = outlineSeo?.value || '';

                    if (nilai.trim() === '') {
                        tombolSalinOutline.textContent = 'Outline Kosong';

                        window.setTimeout(() => {
                            tombolSalinOutline.textContent = teksAwal;
                        }, 1400);

                        return;
                    }

                    try {
                        await navigator.clipboard.writeText(nilai);
                        tombolSalinOutline.textContent = 'Struktur Tersalin';
                    } catch {
                        tombolSalinOutline.textContent = 'Gagal Menyalin';
                    }

                    window.setTimeout(() => {
                        tombolSalinOutline.textContent = teksAwal;
                    }, 1400);
                });

                tombolSalinJsonLd?.addEventListener('click', async () => {
                    const teksAwal = tombolSalinJsonLd.textContent;
                    const faq = ekstrakFaqDariOutline();

                    if (faq.length === 0) {
                        tombolSalinJsonLd.textContent = 'FAQ Belum Siap';

                        window.setTimeout(() => {
                            tombolSalinJsonLd.textContent = teksAwal;
                        }, 1400);

                        return;
                    }

                    try {
                        await navigator.clipboard.writeText(previewFaqJsonLd?.textContent || '');
                        tombolSalinJsonLd.textContent = 'JSON-LD Tersalin';
                    } catch {
                        tombolSalinJsonLd.textContent = 'Gagal Menyalin';
                    }

                    window.setTimeout(() => {
                        tombolSalinJsonLd.textContent = teksAwal;
                    }, 1400);
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
                        renderCounterMetadataSeo();
                        renderIntentPencarian();
                        renderPreviewPeopleAlsoAsk();
                        renderIntentPerSection();
                        renderPreviewFaqJsonLd();
                        renderValidatorFaq();
                        renderSchemaReadiness();
                        renderPreviewKonten();
                        jadwalkanAutosimpan();
                    });
                });

                perbaruiSlugPreview();
                evaluasiKesiapan();
                renderPreviewSnippet();
                renderCounterMetadataSeo();
                renderIntentPencarian();
                renderPreviewPeopleAlsoAsk();
                renderIntentPerSection();
                renderPreviewFaqJsonLd();
                renderValidatorFaq();
                renderSchemaReadiness();
                renderPreviewKonten();

                if (autosimpanUrl) {
                    setStatusAutosimpan('Autosimpan aktif', 'text-secondary');
                }
            });
        });
    </script>
@endpush

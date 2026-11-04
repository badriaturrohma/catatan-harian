# PRD — Sistem Monitoring Perjalanan Santri Mahasiswa

Versi 1.1 • 13 Oktober 2026 • Status: rancangan untuk implementasi

Sumber: Rincian_Proyek_Monitoring_Santri_Diperbarui.docx dan permintaan tambahan rekap harian/bulanan dengan akses riwayat. Dokumen ini merupakan spesifikasi; belum ada klaim hasil riset pengguna, aplikasi berjalan, atau benchmark.

## 1. Ringkasan

Website responsif Laravel untuk mencatat perjalanan santri dari pondok ke kampus dan kembali, menggunakan QR yang dipindai HP petugas. Sistem menyediakan monitoring pengurus, akses terbatas orang tua, input manual terkontrol, izin keluar, periode libur, peringatan bertingkat, dan laporan harian/bulanan yang tetap dapat ditelusuri pada periode sebelumnya.

Database mengikuti dokumen sumber: MySQL. PHP 8.3 merupakan target awal sumber, bukan keputusan kompatibilitas yang sudah diverifikasi. Versi Laravel, PHP, Composer dan pustaka ditentukan dari dokumentasi resmi saat implementasi. Frontend menggunakan Blade dan komponen responsif; JavaScript hanya untuk kamera, interaksi dan pembaruan dashboard.

Dinamis berarti pengurus dapat mengelola master data, penugasan, kalender libur, parameter peringatan, durasi izin, filter laporan dan cakupan monitoring dari UI. Empat jenis aktivitas inti tetap konsisten agar urutan dan perhitungan laporan tidak berubah secara sembarang.

## 2. Masalah dan tujuan

Pencatatan keberangkatan/kepulangan yang tidak terpusat menyulitkan pengurus mengetahui siapa yang belum tercatat tiba atau kembali. Kegagalan scan dapat menyerupai keterlambatan. Orang tua membutuhkan informasi anaknya tanpa akses ke santri lain. Rekap yang hanya menampilkan hari berjalan tidak cukup untuk menelusuri bulan sebelumnya.

Tujuan operasional: menyatukan pencatatan, mempercepat tindak lanjut, mempertahankan bukti histori, dan mengurangi pekerjaan rekap manual. Tujuan pengguna: scan sederhana, status jelas, koreksi terlacak, serta laporan periode lama yang mudah dicari dan diekspor.

Kesenjangan bukti: jumlah santri aktif, volume scan bersamaan, perangkat/koneksi pos, kebijakan retensi, penanggung jawab izin dan prosedur koreksi belum divalidasi. Angka kinerja di dokumen ini adalah target uji.

Tidak termasuk rilis pertama: GPS perjalanan langsung, pengenalan wajah, RFID, AI, aplikasi Android terpisah, scan offline/sinkronisasi, WhatsApp/SMS otomatis, pembayaran, dan akun santri untuk mengajukan izin sendiri. Status menunjukkan catatan sistem, bukan kepastian lokasi fisik santri.

## 3. Pengguna dan hak akses

| Peran | Hak utama | Batasan |
|---|---|---|
| Pengurus | Master data, akun, relasi wali, penugasan, aturan, izin, koreksi, seluruh monitoring dan laporan | Semua mutation penting diaudit |
| Petugas | Scan dan input manual pada lokasi penugasan aktif, melihat hasil dan catatan tugasnya, mengajukan koreksi | Tidak mengubah aturan, menyetujui izin atau membuka laporan seluruh pesantren |
| Orang tua/wali | Status, riwayat, izin dan rekap anak yang terhubung | Read-only; setiap akses objek dan ekspor dibatasi relasi wali |

Rancangan mendukung banyak wali per santri dan banyak santri per wali; pengurus memverifikasi relasi sebelum aktivasi. Santri belum menjadi akun pengguna pada rilis pertama. Akun dan master data dinonaktifkan, bukan dihapus jika sudah dipakai histori. Pembatasan berlaku pada server, termasuk endpoint dashboard, berkas unduhan dan pencarian.

## 4. Prioritas dan fitur

Must wajib rilis pertama; Should tahap berikutnya; Could opsional setelah validasi.

| ID | Fitur | Prioritas | Cakupan |
|---|---|---|---|
| F01 | Autentikasi dan otorisasi | Must | Login/logout, reset password melalui mekanisme yang disepakati, role dan pembatasan objek |
| F02 | Data santri | Must | NIUP/nomor internal unik, nama, foto identifikasi, kamar, daerah, wilayah, prodi, kampus, status |
| F03 | Master data dinamis | Must | CRUD kampus, prodi, wilayah, daerah, kamar, lokasi; nonaktif tanpa kehilangan histori |
| F04 | Akun dan relasi wali | Must | Pengurus/petugas/wali; verifikasi relasi anak-wali |
| F05 | Penugasan petugas | Must | Lokasi dan periode tugas; aktivitas yang diizinkan sesuai jenis lokasi |
| F06 | QR monitoring | Must | Token acak, cetak kartu, unduh QR, cabut/regenerasi token dengan audit |
| F07 | Scan empat tahap | Must | Identifikasi → cek identitas → konfirmasi aktivitas → simpan hasil |
| F08 | Validasi dan anti-duplikasi | Must | Urutan, waktu, izin, lokasi, santri aktif, idempotensi dan transaksi |
| F09 | Input manual dan koreksi | Must | Alasan wajib, waktu kejadian/input terpisah, persetujuan koreksi pengurus |
| F10 | Monitoring pengurus | Must | Status, aktivitas terbaru, perjalanan terbuka, peringatan, filter organisasi/kampus |
| F11 | Monitoring wali | Must | Status dan riwayat khusus anak; tampilkan waktu pembaruan |
| F12 | Kalender libur | Must | Periode per kampus, batas jam berangkat, preview aturan berlaku |
| F13 | Izin dan riwayat izin | Must | Draft, menunggu, disetujui/ditolak, dibatalkan, digunakan, selesai/kedaluwarsa |
| F14 | Peringatan bertingkat | Must | Belum tercatat tiba/kembali; kuning/merah; histori dan tindak lanjut |
| F15 | Pengaturan berversi | Must | Parameter tervalidasi, tanggal berlaku, nilai lama/baru, dampak perubahan |
| F16 | Rekap harian | Must | Tanggal bebas, ringkasan dan detail, drill-down, filter dan ekspor |
| F17 | Rekap bulanan | Must | Bulan/tahun bebas, agregat, rincian per santri/per hari dan ekspor |
| F18 | Histori dan audit | Must | Event dan perubahan append-only; revisi laporan dan bukti koreksi |
| F19 | Ekspor laporan | Must | PDF dan CSV sesuai filter serta hak akses |
| F20 | Backup/restore operasional | Must | Data, media dan uji pemulihan; pelaksanaan oleh administrator server |
| F21 | Impor santri | Should | Template CSV, preview, validasi dan laporan baris gagal |
| F22 | Tampilan/filter tersimpan | Should | Preset monitoring/laporan per pengguna dengan validasi cakupan |
| F23 | Dashboard modular | Should | Pilih dan urutkan widget dari katalog sistem |
| F24 | Aturan khusus kelompok | Should | Override per wilayah/kelompok dengan prioritas eksplisit |
| F25 | Notifikasi eksternal | Could | WhatsApp/email/SMS setelah persetujuan kanal, biaya dan privasi |
| F26 | Offline/PWA | Could | Rancangan terpisah untuk antrean, konflik dan keamanan perangkat |

## 5. Model perjalanan dan aturan bisnis

### Perjalanan reguler

Urutan wajib: Keluar Pondok → Tiba Kampus → Keluar Kampus → Kembali Pondok. Perjalanan memiliki ID sendiri, santri, tanggal operasional, jenis, status dan aktivitas terkait. Satu santri maksimal satu perjalanan terbuka; boleh mempunyai beberapa perjalanan selesai dalam hari yang sama. Status awal tanpa bukti tapping adalah “Belum Ada Catatan Hari Ini”, bukan “Di Pondok”.

Status perjalanan: menuju kampus, tercatat di kampus, menuju pondok, selesai, atau perlu rekonsiliasi. Keluar Kampus sebelum Tiba Kampus ditolak. Santri yang kembali tanpa tahap sebelumnya dapat dicatat melalui prosedur rekonsiliasi pengurus; tidak membuat tapping kampus fiktif. Perjalanan tersebut tetap ditandai tidak lengkap walaupun kepulangan tercatat.

Tanggal operasional ditetapkan dari waktu keluar pondok menurut Asia/Jakarta. Perjalanan melewati tengah malam tetap terkait tanggal berangkat, sementara laporan aktivitas mengikuti tanggal kejadian. Semua timestamp menyimpan zona waktu secara konsisten.

### Perjalanan berdasarkan izin

Izin memiliki tujuan: kampus atau non-kampus. Izin non-kampus menggunakan Keluar Pondok → Kembali Pondok; tidak menuntut tapping kampus. Izin kampus tetap mencatat empat tahap tetapi menonaktifkan peringatan menuju kampus sesuai sumber; batas kembali mengikuti izin. Izin harus disetujui sebelum digunakan; periode berlaku dan santri harus cocok. Tidak boleh ada izin disetujui yang bertumpang tindih untuk santri yang sama tanpa pembatalan/revisi terkontrol.

Waktu mulai izin ditetapkan saat persetujuan, batas kembali = waktu mulai + durasi. Scan lebih lambat tidak menggeser batas kembali. Durasi awal 3–5 jam; nilai dapat diatur dengan minimum >0 dan maksimum >= minimum. Izin tidak dipakai ditandai kedaluwarsa saat periode berakhir. Pembatalan izin yang sedang digunakan hanya oleh pengurus, dengan alasan dan keputusan batas kembali pengganti yang eksplisit.

### Libur kampus

Periode inklusif tanggal mulai/akhir, ditetapkan per kampus. Rancangan mengartikan batas jam sebagai jam terakhir boleh keluar pondok menuju kampus, sesuai penjelasan sumber; batas durasi di kampus bukan maknanya. Setelah batas, keberangkatan ditolak kecuali izin berlaku. Batas tepat diperbolehkan; setelahnya ditolak. Periode tumpang tindih pada kampus yang sama ditolak; penonaktifan tetap menyimpan histori. Di luar periode berlaku aturan reguler. Batas jam libur wajib diisi sebelum periode diaktifkan.

### Peringatan

| Kondisi | Aturan awal | Tampilan |
|---|---|---|
| Reguler belum tiba kampus | >=15 menit sejak keluar: kuning; >=30: merah | Belum Tercatat Tiba di Kampus |
| Belum kembali reguler | Batas kembali 16.00; segera lewat batas status terlambat administratif, +15 kuning, +30 merah | Belum Tercatat Kembali ke Pondok |
| Belum kembali izin | Batas sesuai izin; +15 kuning, +30 merah | Belum Tercatat Kembali Sesuai Izin |
| Tiba/kembali akhirnya tercatat | Tutup peringatan aktif | Histori peringatan tetap tersedia |

Ambang merah harus lebih besar daripada kuning, keduanya >0. Peringatan kembali hanya untuk perjalanan yang sudah keluar dan belum kembali; santri tanpa keberangkatan tidak dianggap terlambat kembali. Peringatan kuning/merah bukan bukti pelanggaran atau alasan hukuman otomatis.

Simpan waktu seharusnya ambang tercapai dan waktu sistem mendeteksi secara terpisah. Scheduler menghitung peringatan walaupun dashboard tidak dibuka; dashboard menghitung ulang tampilan untuk menghindari status basi. Pencatatan manual dengan waktu kejadian sebelum ambang dapat menyelesaikan peringatan sebagai keterlambatan pencatatan. Jika waktu kejadian sesudah ambang, keterlambatan kejadian tetap tercatat. Histori membedakan dua hal tersebut.

### Perubahan aturan dinamis

Aturan memiliki versi, scope, effective_at dan pengubah. Nilai baru berlaku pada evaluasi berikutnya untuk perjalanan terbuka, mengikuti sumber. Sistem menampilkan dampak sebelum menyimpan. Perjalanan selesai mempertahankan evaluasi historis; perubahan aturan saja tidak menghitung ulang laporan perjalanan selesai. Reevaluasi perjalanan aktif menambah catatan perubahan evaluasi, tidak menghapus peringatan lama. Urutan awal: aturan kampus mengatasi aturan global; F24 menambah scope kelompok dengan prioritas tersendiri yang harus diuji.

### Koreksi

Petugas mengajukan koreksi; pengurus menyetujui dengan alasan. Event lama tidak diedit/dihapus: buat event pengganti atau pembatal dengan referensi event terdahulu. Validasi ulang seluruh urutan perjalanan, waktu dan laporan terdampak. Perubahan mundur yang mengubah laporan historis menghasilkan revisi baru; versi sebelumnya tetap dapat dilihat.

## 6. Laporan harian dan bulanan

### Prinsip histori

Tidak ada reset data pada pergantian hari atau bulan. Pengguna memilih tanggal atau bulan/tahun sebelumnya. Laporan terkini dibentuk dari event efektif, izin dan evaluasi. Sistem menyimpan snapshot rekap final dengan nomor revisi, generated_at, rentang waktu, filter/cakupan, versi aturan dan alasan revisi. Snapshot tidak menggantikan event sumber.

Tanggal sebelumnya ditutup otomatis setelah hari operasional selesai dan job penutupan berjalan; bulan ditutup setelah seluruh tanggal selesai diproses. Rekap periode berjalan bertanda “Sementara”. Perjalanan yang belum selesai saat penutupan tetap tercatat terbuka pada snapshot. Kedatangan atau koreksi belakangan menghasilkan revisi laporan hari/bulan terdampak. Tampilan default adalah revisi terbaru; pengguna berwenang dapat membuka versi sebelumnya. Snapshot kanonis dibuat global/per kampus, bukan untuk setiap kombinasi filter; ekspor berfilter menyimpan metadata dan nomor revisi sumber.

### Definisi hitungan

- Jumlah perjalanan: jumlah ID perjalanan, bukan jumlah santri.
- Santri bepergian: jumlah santri unik dengan keberangkatan efektif dalam periode.
- Perjalanan lengkap reguler: memiliki empat tahap valid; izin non-kampus lengkap jika dua tahap wajib ada.
- Perjalanan tidak lengkap: kepulangan ada tetapi tahap wajib kurang; dibedakan dari perjalanan terbuka.
- Keterlambatan kejadian: waktu tiba atau kembali melewati ambang/batas evaluasi yang berlaku; keterlambatan pencatatan dilaporkan terpisah.
- Izin disetujui: jumlah izin menurut waktu persetujuan; izin digunakan menurut keberangkatan terkait. Kedua angka tidak dicampur.
- Peringatan: jumlah episode peringatan, bukan jumlah polling atau perubahan warna. Kuning menjadi merah tetap satu episode dengan tingkat tertinggi merah.
- Tidak ada perjalanan: ketiadaan keberangkatan; bukan bukti bolos. Persentase kehadiran/ketidakberangkatan tidak dibuat tanpa daftar santri yang memang dijadwalkan berangkat.

### Rekap harian

Filter tanggal, kampus, prodi, wilayah, daerah, kamar, santri, jenis perjalanan, status, tingkat peringatan dan metode pencatatan. Ringkasan: perjalanan reguler/izin, santri unik, lengkap, tidak lengkap, terbuka, terlambat tiba/kembali, input manual dan peringatan.

Detail per perjalanan: tanggal, NIUP, nama, identitas organisasi historis, ID perjalanan, empat waktu tapping (atau dua tahap izin), batas waktu, durasi, status, izin terkait, metode/petugas tiap tahap, peringatan dan catatan koreksi. Waktu kosong ditulis “Belum tercatat”, tidak diisi jam 00.00. Klik baris membuka timeline.

### Rekap bulanan

Filter bulan dan tahun serta filter organisasi yang sama. Menampilkan total, santri unik bulanan, jumlah per hari, ringkasan per santri dan rincian perjalanan. Santri unik bulanan dihitung ulang, bukan menjumlahkan santri unik harian. Satu santri dengan dua perjalanan sehari dihitung dua perjalanan dan satu santri unik.

Perjalanan masuk agregat menurut tanggal operasional, aktivitas menurut tanggal kejadian. Catatan kembali bulan berikutnya merevisi perjalanan bulan sebelumnya tanpa menghilangkan event pada laporan aktivitas bulan berikutnya. Tampilan/ekspor menjelaskan basis tanggalnya.

### Ekspor dan akses

PDF untuk ringkasan dan tabel terbaca, CSV untuk data detail. Ekspor mempertahankan filter, timezone, basis tanggal, generated_at dan revisi sumber. Kolom petugas internal dan audit rinci tidak dimasukkan ke ekspor wali. Wali hanya dapat mengekspor anaknya. Ekspor besar berjalan melalui antrean; berkas unduhan membutuhkan otorisasi dan kedaluwarsa setelah 24 jam secara default. CSV menetralkan formula spreadsheet dari input pengguna. Penghapusan berkas ekspor tidak menghapus laporan atau data sumber.

## 7. User stories dan acceptance criteria

| ID | Cerita | Kriteria penerimaan |
|---|---|---|
| AC01 | Petugas mencatat perjalanan normal | Empat tahap berurutan tersimpan dengan waktu, lokasi, petugas dan metode; perjalanan selesai |
| AC02 | Petugas melakukan retry | request_id sama memberikan hasil yang sama tanpa event kedua; dua request bersamaan tidak menggandakan tahap |
| AC03 | Petugas scan pada pos salah | Server menolak walaupun ID lokasi pada request dimanipulasi |
| AC04 | Pengurus menangani belum tiba | Tepat ambang 15/30 menit warna sesuai; tanpa dashboard terbuka scheduler tetap menyimpan episode |
| AC05 | Petugas mencatat belakangan | Waktu kejadian dan input berbeda; alert selesai dengan klasifikasi keterlambatan yang benar |
| AC06 | Pengurus mengatur libur | Setelah batas berangkat ditolak; izin berlaku memperbolehkan; luar periode aturan normal |
| AC07 | Pengurus menyetujui izin | Durasi di luar rentang ditolak; izin non-kampus tidak memunculkan peringatan tiba kampus |
| AC08 | Wali memantau anak | URL, pencarian, endpoint dan ekspor santri lain ditolak tanpa membocorkan identitas |
| AC09 | Pengurus mengubah aturan | Nilai tervalidasi dan audit tersimpan; perjalanan aktif reevaluasi, histori selesai tidak berubah karena aturan baru saja |
| AC10 | Pengurus melihat rekap hari lama | Pilih tanggal bulan sebelumnya; data tersedia, subtotal dan detail cocok |
| AC11 | Pengurus melihat rekap bulan lama | Pilih bulan/tahun; agregat konsisten dengan perjalanan sumber dan santri unik dihitung benar |
| AC12 | Koreksi memengaruhi laporan | Revisi baru tercipta, versi lama tetap tersedia dan alasan/perubahan dapat ditelusuri |
| AC13 | Rekap melintasi tengah malam | Keberangkatan dan kembali berbeda hari/bulan tidak menghasilkan perjalanan ganda |
| AC14 | QR diganti | Token lama ditolak; token baru berlaku; histori sebelum rotasi tetap terbaca |
| AC15 | Ekspor dan restore | PDF/CSV cocok dengan filter; backup dipulihkan di lingkungan uji dengan relasi dan media utuh |
| AC16 | Kamera/jaringan gagal | Pesan jelas, tidak menampilkan sukses sebelum commit; input manual tersedia sesuai izin akses |

## 8. Alur UX dan kondisi antarmuka

Petugas login → pilih lokasi aktif yang ditugaskan → izinkan kamera → scan → kartu identitas santri → aktivitas yang valid → konfirmasi → hasil tersimpan beserta jam server. Scanner dihentikan sementara saat konfirmasi untuk menghindari scan berulang. Setelah sukses petugas dapat mulai scan berikutnya.

Kamera ditolak: tampilkan instruksi izin browser dan pencarian santri/input manual. Jaringan gagal: tampilkan “Belum tersimpan”, pertahankan request_id untuk retry; jangan mengklaim scan berhasil. QR tidak valid: pesan umum tanpa identitas. Urutan salah: tampilkan tahap yang diperlukan dan opsi pengajuan rekonsiliasi, bukan melewati validasi.

Pengurus membuka dashboard → filter → lihat peringatan → timeline → tindak lanjut/rekonsiliasi. Mencatat tindak lanjut tidak berarti santri sudah tiba. Alert dapat diakui dengan catatan tetapi baru terselesaikan oleh event sah, koreksi atau keputusan pengurus yang diaudit.

Wali login → pilih anak terhubung → status dengan waktu update → timeline/izin → pilih periode laporan. Akun tanpa relasi menampilkan pesan untuk menghubungi pengurus, bukan data semua santri.

Laporan → pilih harian/bulanan → periode/filter → ringkasan → detail → versi laporan → ekspor. Empty state membedakan belum ada aktivitas, hasil filter kosong, dan periode belum diproses. Loading tidak menampilkan angka nol sebagai hasil akhir. Kesalahan menyediakan retry; semua konfirmasi koreksi menampilkan dampaknya.

## 9. Persyaratan konten, aksesibilitas dan pengukuran

Bahasa Indonesia, timezone default Asia/Jakarta, format tanggal/jam jelas. Gunakan “tercatat” pada status; dashboard menunjukkan waktu terakhir refresh. Warna kuning/merah disertai teks dan ikon. Target aksesibilitas WCAG 2.2 AA: keyboard, fokus terlihat, label, kontras dan dialog terkelola; sasaran uji, bukan klaim sertifikasi. Uji UI pada lebar 360, 768 dan 1280 px; tabel lebar memakai scroll internal dan kolom penting tetap terlihat.

Analitik operasional mencakup scan sukses/ditolak, durasi, kegagalan job, ekspor dan koreksi. Hindari isi alasan izin, token QR, password dan data pribadi di log teknis. Audit domain menyimpan aktor dan perubahan yang memang diperlukan, dengan akses pengurus.

## 10. Rancangan data

| Entitas | Field/relasi utama |
|---|---|
| users | role, identitas login, password hash, active; tidak menyimpan password pada profil |
| santri | ID, nomor unik, nama, foto, master organisasi, active |
| guardian_santri | user wali, santri, jenis relasi, verified_by, verified_at; pasangan unik |
| campuses/programs/regions/areas/rooms | master aktif dan histori perubahan |
| locations | kampus/pondok, jenis pos, nama, active |
| officer_assignments | user petugas, location_id, valid_from/until, active |
| qr_credentials | santri, token_hash, issued_at, revoked_at; token plaintext tidak di-log |
| journeys | santri, type, operational_date, status, permit_id, started_at, completed_at, organization_snapshot |
| journey_events | journey, activity, occurred_at, recorded_at, actor, location, method, request_id, correction_reference |
| correction_requests | target event, proposed_data, reason, status, reviewer dan waktu |
| permits | santri, tujuan, mulai, durasi, deadline, alasan, status, approver, revision |
| holiday_periods | campus, tanggal mulai/akhir, cutoff, active, rule_version |
| rule_versions | scope, parameter terstruktur, effective_at, actor, previous_version |
| alert_episodes | journey, type, highest_level, threshold_at, detected_at, resolved_at, resolution_kind |
| alert_evaluations | episode, rule_version, evaluated_at, state, alasan perubahan |
| followups | episode, actor, catatan, created_at |
| report_snapshots | period_type, period_key, scope, revision, source_watermark, rule_refs, totals, generated_at, supersedes |
| report_rows | snapshot, journey_reference, historical_summary |
| export_jobs | requester, filter, scope, snapshot_revision, status, private_path, expiry |
| audit_logs | actor, action, object, old/new terbatas, reason, timestamp |

Indeks direncanakan pada santri+operational_date, journey+activity, occurred_at, status, permit deadline, alert status, dan period+scope+revision. request_id unik per aktor/action; satu event efektif per tahap/perjalanan ditegakkan lewat transaksi dan struktur projection yang memiliki unique constraint. Event audit append-only terpisah dari projection efektif. Relasi yang pernah dipakai histori tidak cascade-delete secara sembarang.

Snapshot organisasi pada perjalanan mencegah perpindahan kamar/prodi mengubah laporan lama. Orang tua mengikuti relasi akses aktif saat membaca, bukan snapshot wali lama. Kebijakan pencabutan relasi berlaku segera termasuk unduhan.

## 11. Arsitektur, route dan kontrak

Modul rencana: Auth/Policies, Santri/MasterData, Assignments, QrCredentials, Journeys/Events, Permits, Holidays/Rules, Alerts, Reports/Exports, Corrections/Audit. Implementasi menggunakan controller tipis, validation request, service domain dan policy; frontend Blade. Pilihan pustaka QR, scanner dan PDF baru dikunci setelah verifikasi dependency.

| Halaman | Route rencana |
|---|---|
| Login | /login |
| Dashboard pengurus | /admin/dashboard |
| Master dan penugasan | /admin/santri, /admin/master-data, /admin/assignments |
| Scan petugas | /officer/scan |
| Izin, libur, aturan | /admin/permits, /admin/holidays, /admin/rules |
| Koreksi dan audit | /admin/corrections, /admin/audit |
| Rekap harian/bulanan | /admin/reports/daily, /admin/reports/monthly |
| Wali | /parent/dashboard, /parent/children/{id}/history, /parent/reports |

GET hanya membaca. Mutation memakai POST/PATCH dengan session auth, CSRF dan policy. Contoh kontrak:

- POST /officer/qr/resolve: token dan assignment_id → kartu identitas minimal serta aktivitas valid, tanpa menulis event.
- POST /officer/events: request_id, credential_reference, assignment_id, activity, journey_id opsional → event/journey/status/waktu tercatat; waktu scan berasal dari server. Reference hasil resolve berumur singkat dan terikat petugas.
- POST /officer/manual-events: santri_id, activity, occurred_at, assignment_id, reason, request_id → event manual. Waktu masa depan ditolak; backdate >24 jam masuk permohonan koreksi pengurus.
- POST /admin/corrections/{id}/approve: revision, reason → projection diperbarui dan job revisi laporan.
- POST /admin/permits/{id}/approve: revision → izin disetujui dengan snapshot parameter.
- PATCH /admin/rules/{id}: revision, parameters, effective_at → versi baru dengan audit.
- GET /admin/reports/daily?date=YYYY-MM-DD&campus_id=...; GET /admin/reports/monthly?month=YYYY-MM&... → ringkasan/detail/revisi.
- POST /reports/exports: format, period, filters, revision → job_id; GET /reports/exports/{id}/download memeriksa akses kembali.

Kesalahan JSON berbentuk {error:{code,message,fields}}; 422 validasi, 403 akses, 404 objek tidak tersedia, 409 konflik urutan/revisi, 429 batas request, 503 layanan sementara gagal. Respons tidak membocorkan token atau data santri di luar akses.

Kunci row santri/perjalanan dalam transaksi saat pencatatan, cek ulang izin dan urutan, lalu commit event dan projection. Dua HP yang scan bersamaan harus menghasilkan satu tahap efektif. Mutation izin/koreksi memakai optimistic revision. Scheduler dan job laporan idempotent, memiliki lock dan retry; revisi snapshot memakai source watermark sehingga hasil tidak mencampur sumber dari waktu berbeda.

## 12. Operasional, keamanan dan performa

HTTPS diperlukan pada deployment kamera browser yang dihosting; izin kamera dan kompatibilitas perangkat diuji. Lokasi penugasan adalah kewenangan administratif, bukan bukti GPS petugas berada di pos. QR dapat disalin, sehingga petugas mengecek nama/foto sebelum konfirmasi. QR dicabut saat santri nonaktif sesuai prosedur.

Hash password menggunakan mekanisme Laravel; rate limit login/resolve, session expiry, CSRF, escape output, validasi upload foto dan file private. Validasi role dan relasi pada setiap request. Audit tidak dapat diedit pengguna. Backup terenkripsi dengan akses terbatas; jadwal harian dan uji restore bulanan sebagai usulan operasional.

Scheduler evaluasi peringatan setiap menit; dashboard refresh setiap 30 detik dan menunjukkan waktu data. Rekap harian/bulanan serta ekspor besar melalui queue. Kegagalan scheduler/queue terlihat pada health monitoring pengurus/administrator. Target alert terdeteksi <=2 menit setelah ambang saat server sehat.

Fixture performa awal: 1.000 santri aktif, 100.000 event historis, 10 petugas melakukan request bersamaan, server referensi 2 vCPU/4 GB. Target p95 commit scan <=2 detik, dashboard <=3 detik, laporan awal berpaginasi <=5 detik; benchmark nyata wajib sebelum menetapkan kapasitas. Laporan panjang tidak dirender seluruh baris dalam satu request.

Konfigurasi environment: APP_KEY/APP_URL, DB_*, SESSION_*, QUEUE_CONNECTION, timezone, disk private, batas upload dan export expiry. Server harus mempunyai scheduler, queue worker, MySQL persisten, backup dan TLS. Tidak memasukkan database, password atau foto santri ke repository.

Histori tidak dihapus otomatis pada rilis pertama. Kebijakan retensi dan penghapusan data pribadi ditetapkan pihak pesantren sebelum produksi; aplikasi menyediakan audit/arsip dan proses penghapusan terotorisasi setelah kebijakan disepakati. Backup operasional berbeda dari histori laporan: keduanya diperlukan.

## 13. Metrik keberhasilan

| Jenis | Target awal |
|---|---|
| Leading | >=95% petugas menyelesaikan scan dan konfirmasi <=10 detik pada uji tugas, di luar waktu antrean |
| Outcome | Rekap hari/bulan sebelumnya dapat ditemukan <=30 detik dalam uji pengurus |
| Outcome | Semua perjalanan sampel dapat ditelusuri sampai event, petugas dan versi aturan |
| Guardrail | 0 akses silang wali pada seluruh uji otorisasi |
| Guardrail | 0 event ganda efektif pada uji retry dan concurrency |
| Guardrail | 100% rekap sampel cocok dengan perhitungan independen |
| Guardrail | Alert <=2 menit dari ambang dalam uji scheduler sehat |

Target merupakan kriteria evaluasi, bukan hasil yang sudah dicapai. Pemantauan tidak memakai jumlah peringatan sebagai ukuran pelanggaran santri.

## 14. Tahapan dan dependensi

Estimasi relatif untuk satu pengembang berpengalaman, perlu diperbarui setelah validasi cakupan:

1. Fondasi dan master — 1–2 minggu: model, autentikasi, policy, master, wali dan assignment.
2. QR dan perjalanan — 1–2 minggu: scan, urutan, transaksi, input manual dan koreksi.
3. Izin/libur/aturan/alert — 1–2 minggu: versioning, scheduler, dashboard pengurus/wali.
4. Rekap dan ekspor — 1–2 minggu: harian/bulanan, snapshot, revisi dan PDF/CSV.
5. Uji/pilot/operasional — 1–2 minggu: perangkat nyata, otorisasi, concurrency, rekonsiliasi dan restore.
6. Fitur Should/Could — setelah umpan balik pilot; estimasi tersendiri.

Laporan dan alert bergantung pada event/lifecycle yang stabil. Rekap histori dan audit wajib rilis pertama, tidak ditunda sebagai fitur opsional.

## 15. Risiko dan keputusan terbuka

| Hal | Keputusan rancangan/tindak lanjut |
|---|---|
| Identitas santri | Nomor internal unik; tentukan apakah NIUP wajib sebelum migrasi produksi |
| Wali dan approver izin | Banyak wali didukung; pengurus menyetujui izin pada rilis awal |
| Makna batas libur | Jam terakhir berangkat, bukan durasi; konfirmasi operasional sebelum pilot |
| Batas 16.00 bagi keberangkatan sore | Tanpa izin, keluar reguler setelah batas kembali ditolak; izin menjadi jalur pengecualian |
| Jadwal kuliah berbeda | F24 untuk override kelompok; sementara aturan per kampus |
| Scan terlewat | Rekonsiliasi tanpa event fiktif; laporan membedakan incomplete |
| Perubahan aturan vs histori | Reevaluasi aktif saja; koreksi event merevisi laporan lama dan mempertahankan versi |
| Kehilangan koneksi | Tidak mengklaim berhasil; retry idempotent/manual; offline bukan rilis awal |
| QR disalin | Identifikasi visual petugas; QR bukan autentikasi keberadaan fisik |
| Retensi dan akses laporan | Tentukan kebijakan lembaga sebelum produksi |
| Snapshot bertambah | Monitor ukuran, indeks dan penyimpanan; jangan menghapus revisi tanpa kebijakan |

## 16. Handoff coding agent dan Definition of Done

Belum ada repository yang diperiksa. Saat implementasi diminta, periksa AGENTS.md, composer.json, versi PHP, konfigurasi auth, migration, model, job dan test yang ada. Jika proyek baru, bentuk modul pada bagian 11. Jangan mengubah framework/database yang sudah ditetapkan. Verifikasi versi dan kompatibilitas dari dokumentasi resmi, lalu kunci dependency.

Implementasikan F01–F20; F21–F26 memerlukan tahap tambahan. Penuhi AC01–AC16, seed skenario dan dokumentasi deployment. Tidak menambahkan akun santri, GPS atau WhatsApp sebagai asumsi.

| Area uji | Skenario wajib |
|---|---|
| Hak akses | Role, lokasi, relasi wali, URL langsung, lookup dan file ekspor |
| Scan | QR invalid/revoked, santri nonaktif, urutan salah, retry, dua petugas bersamaan |
| Waktu | Ambang tepat 15/30 menit, 16.00, tengah malam, pergantian bulan dan tahun |
| Izin/libur | Rentang inklusif, cutoff, izin expired/overlap/non-kampus, pembatalan saat perjalanan |
| Koreksi | Manual terlambat, urutan kurang, approval, pembatalan event, projection dan revisi |
| Laporan | Dua perjalanan satu santri, unik bulanan, periode kosong, lintas bulan, revisi lama, perubahan kamar |
| Operasional | Scheduler terhenti/retry, queue gagal, backup/restore dan request saat job penutupan |
| UI/ekspor | HP kamera/izin ditolak, jaringan gagal, keyboard, PDF panjang, CSV formula injection |

Definition of Done: seluruh Must dan acceptance criteria lolos dengan bukti; laporan dihitung terhadap fixture independen; histori periode lama dan versi koreksi tersedia; akses wali tidak bocor; QR retry tidak menggandakan; backup berhasil dipulihkan; scheduler/queue terdokumentasi dan dipantau; UI/PDF diperiksa; keputusan retensi, identitas, scope libur dan aturan pengecualian disepakati sebelum produksi.

Prompt implementasi: “Bangun Sistem Monitoring Perjalanan Santri Mahasiswa sesuai PRD ini menggunakan Laravel, MySQL dan Blade. Implementasikan F01–F20 secara bertahap, termasuk empat tapping QR, policy tiap peran, izin/libur, aturan berversi, audit, dan rekap harian/bulanan dengan histori serta revisi. Tunjukkan bukti AC01–AC16 dan dokumentasikan deployment, scheduler, queue, backup dan restore.”


## 17. Tampilan modern, clean dan responsif — wajib rilis pertama

Spesifikasi ini melengkapi permintaan terbaru. Seluruh fitur monitoring, izin, audit serta rekap harian/bulanan dan riwayat tetap berlaku. Modern berarti hierarki informasi jelas dan interaksi cepat; clean berarti ruang antar elemen cukup dan dekorasi terbatas. Responsif dibuktikan pada perangkat sasaran, bukan klaim kompatibel dengan setiap perangkat tanpa uji.

### Sistem visual

| Elemen | Spesifikasi |
|---|---|
| Warna utama | Teal #0F766E; latar #F8FAFC; kartu putih |
| Teks | Utama #0F172A, sekunder #475569; system sans-serif |
| Ukuran | Isi 16 px, metadata minimal 14 px; heading bertingkat |
| Status | Normal hijau/teal, perhatian amber, kritis merah; label dan ikon selalu tersedia |
| Komponen | Radius kartu 12 px, border lembut, bayangan ringan |
| Spasi | Skala konsisten 4/8/12/16/24/32 px |
| Interaksi | Fokus terlihat, animasi singkat; reduced motion didukung |

Kontras semua kombinasi diuji. Logo/nama lembaga dapat dikelola pengurus, warna merek dipilih dari preset tervalidasi. Warna dan makna peringatan tetap konsisten. Tidak menerima HTML/JavaScript bebas pada konfigurasi branding.

### Layout adaptif

| Perangkat/viewport | Tata letak |
|---|---|
| HP 320–767 px | Satu kolom, navigasi drawer, filter lipat, timeline vertikal, kartu detail |
| Tablet 768–1023 px | Sidebar ringkas/drawer, kartu dua kolom; form dua kolom jika label tetap jelas |
| Desktop >=1024 px | Sidebar sekitar 240 px, topbar, kartu 3–4 kolom sesuai ruang |
| Layar besar | Konten maksimal sekitar 1600 px; teks tidak melebar tanpa batas |

Mobile-first, ukuran fleksibel, portrait/landscape dan zoom 200%. Tidak ada scroll horizontal seluruh halaman. Tabel panjang memakai kontainer scroll berlabel dan alternatif kartu ringkasan mobile; font tidak diperkecil untuk memaksakan tabel desktop di HP. Target sentuh utama minimal 44×44 px. Aksi form tetap dapat dijangkau ketika keyboard HP terbuka.

### Rancangan per halaman

- Login: identitas lembaga, form sederhana dengan label, tombol tampilkan password, pesan kesalahan jelas.
- Dashboard pengurus: waktu refresh, filter kampus/periode, ringkasan, peringatan prioritas, aktivitas terbaru dan pintasan laporan. Klik kartu menerapkan filter. Grafik tren dilengkapi angka/tabel yang dapat dibaca.
- Scanner petugas: pos/akun terlihat, kamera dominan, mulai/hentikan, pilihan kamera bila tersedia, input manual dan hasil terakhir. Konfirmasi menampilkan nama/foto/NIUP dan aktivitas sebelum simpan. Suara/getaran opsional; teks hasil selalu tersedia.
- Dashboard wali: pemilih anak, status terakhir beserta timestamp, timeline, izin dan rekap; navigasi khusus wali.
- Data santri: pencarian/filter/pagination; tabel desktop dan kartu nama/NIUP/status pada HP.
- Izin: status approval, waktu mulai/deadline dan preview durasi; persetujuan berbeda dari edit draft.
- Pengaturan: kelompok Monitoring, Libur, Izin, Akun dan Identitas; helper text, satuan, preview dampak dan konfirmasi. Angka dimuat dari server, bukan hardcoded di UI.
- Laporan: tab Harian/Bulanan, pilihan periode lama, filter aktif berupa chip, reset, ringkasan, detail, ekspor serta label Sementara/Final/Revisi. Basis tanggal terlihat.
- Timeline: tahap, jam kejadian, jam input jika berbeda, label manual/koreksi. Tidak menyerupai pelacakan GPS langsung.

### Komponen dan state

Komponen Blade reusable: AppShell, RoleNavigation, PageHeader, StatCard, StatusBadge, FilterPanel, ResponsiveTable, MobileRecordCard, JourneyTimeline, ScanResult, FormField, ConfirmDialog, Toast, EmptyState dan Pagination.

Dukung loading, empty, success, error dan akses ditolak. Skeleton tidak mengisi angka nol seolah hasil final. Auto-refresh mempertahankan fokus, filter dan posisi baca. Tombol memakai kata kerja spesifik seperti Konfirmasi Tapping, Setujui Izin dan Unduh Laporan. Form mempunyai label, error per field dan input yang dipertahankan saat gagal. Dialog mengelola dan mengembalikan fokus; tidak menutupi aksi di layar kecil.

Preferensi per akun: kepadatan nyaman/ringkas, halaman awal sesuai role, jumlah baris 10/25/50; disimpan pada user_preferences. Preferensi tidak mengubah akses. F22 filter tersimpan/F23 widget modular tetap tahap lanjutan. Tema gelap opsional setelah semua status diuji terpisah. Layout responsif dan kualitas tampilan wajib sejak rilis pertama.

### Kriteria penerimaan UI

| ID | Uji | Hasil wajib |
|---|---|---|
| AC17 | 320, 360, 390, 768, 1024, 1366, 1920 px | Alur inti selesai tanpa overlap, tombol terpotong atau scroll horizontal global |
| AC18 | HP/tablet portrait dan landscape | Form, drawer, scanner, filter dan timeline adaptif tanpa mode desktop |
| AC19 | Zoom 200%, keyboard, pembaca layar | Fokus/label/status terbaca, navigasi logis dan dialog mengembalikan fokus |
| AC20 | Chrome Android, Safari iOS, Chrome/Edge Windows, Safari macOS pada versi pilot | Login/monitoring/laporan berfungsi; scan diuji di HP nyata dan manual tersedia bila kamera gagal |
| AC21 | Branding/preferensi diubah | Tampilan konsisten setelah reload; histori dan hak akses tidak berubah |
| AC22 | Auto-refresh/jaringan gagal | Fokus/filter tetap, timestamp terlihat dan gagal tidak ditampilkan sebagai sukses |

Catat versi browser/perangkat pada hasil QA. Perangkat tanpa kamera tetap dapat memakai data/laporan; scan memerlukan kamera yang dapat diakses browser. Uji viewport virtual tidak menggantikan uji kamera nyata.

### Handoff dan Definition of Done tambahan

Rilis pertama memenuhi AC17–AC22 selain AC01–AC16. Siapkan desain komponen dan halaman login, dashboard tiga peran, scanner, izin, pengaturan serta laporan sebelum pengembangan UI penuh. Tambahkan migration branding tervalidasi dan user_preferences. Gunakan aset ringan, foto optimal dan komponen konsisten; hindari video latar/dekorasi berat.

Verifikasi visual pada breakpoint sasaran, termasuk loading/empty/error, keyboard HP, fokus dan tabel panjang. Dokumentasikan browser yang diuji serta keterbatasan nyata. Screenshot internal untuk QA bukan deliverable wajib.

Prompt implementasi terbaru: “Implementasikan PRD versi 1.1 menggunakan Laravel, MySQL dan Blade. Kerjakan F01–F20 dan UI modern, clean, mobile-first sesuai bagian 17. Penuhi AC01–AC22, pertahankan rekap harian/bulanan dengan histori dan revisi, uji HP/tablet/desktop serta kamera nyata, lalu dokumentasikan hasil, deployment dan keterbatasan.”

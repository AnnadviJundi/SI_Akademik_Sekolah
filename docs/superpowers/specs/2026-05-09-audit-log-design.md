# Audit Log Design

## Tujuan

Menjadikan `audit_logs` sebagai pusat pencatatan aktivitas penting user agar admin dapat:

- Mengetahui siapa yang melakukan aksi penting.
- Mengetahui kapan aksi dilakukan.
- Mengetahui data apa yang terdampak.
- Mengunduh laporan audit log dalam bentuk PDF berdasarkan periode tertentu.

Fitur ini ditujukan untuk pengawasan administratif, jejak perubahan data, dan pelaporan operasional sekolah.

## Ruang Lingkup

Audit log hanya mencatat aksi penting, bukan seluruh interaksi user. Daftar aksi awal yang wajib dicatat:

- `login`
- `logout`
- `create`
- `update`
- `delete`
- `verifikasi pembayaran`
- `input nilai`
- `update nilai`

Aktivitas seperti membuka halaman, pencarian tabel, pagination, dan interaksi UI biasa tidak dicatat.

## Kondisi Saat Ini

- Model `AuditLog` sudah tersedia.
- Tabel `audit_logs` sudah tersedia.
- Resource admin untuk melihat data audit log sudah tersedia.
- Audit log belum dipakai secara konsisten untuk merekam aksi penting.
- Belum ada filter periode yang sesuai kebutuhan bisnis.
- Belum ada ekspor PDF.

## Pendekatan yang Dipilih

Menggunakan service audit log terpusat.

Alasan pemilihan:

- Format log menjadi konsisten di seluruh modul.
- Lebih mudah menambah pencatatan di titik-titik aksi penting.
- Lebih mudah dipakai ulang untuk halaman admin dan ekspor PDF.
- Kompleksitas lebih rendah dibanding pendekatan event/listener penuh.

## Desain Arsitektur

### Service Audit Logger

Buat service terpusat, misalnya `App\Services\AuditLogger`, yang bertanggung jawab untuk membuat entri audit log.

Tanggung jawab utama:

- Menentukan user aktif pencatat log.
- Menyimpan nama aksi dalam format yang konsisten.
- Menyimpan referensi objek yang terdampak.
- Menyimpan metadata tambahan pada `properties`.
- Menyimpan IP address request bila tersedia.

Antarmuka minimal service:

- `log(string $action, ?Model $subject = null, array $properties = [])`

Format data yang disimpan:

- `user_id`: user yang melakukan aksi
- `action`: nama aksi penting
- `subject_type`: class model atau label domain
- `subject_id`: id data yang terdampak bila ada
- `properties`: metadata tambahan
- `ip_address`: IP request aktif

### Penamaan Aksi

Nama aksi harus manusiawi dan stabil agar mudah dibaca pada tabel maupun PDF.

Daftar aksi awal:

- `login`
- `logout`
- `create`
- `update`
- `delete`
- `verifikasi pembayaran`
- `input nilai`
- `update nilai`

Untuk aksi `create`, `update`, dan `delete`, konteks objek dijelaskan lewat `subject_type`, `subject_id`, dan `properties`.

### Titik Integrasi

#### Login

Log dibuat segera setelah autentikasi berhasil.

Metadata:

- `username`
- `role`

#### Logout

Log dibuat saat user melakukan logout.

Metadata:

- `username`
- `role`

#### CRUD Data Penting

CRUD dicatat pada resource atau page Filament yang memang mengelola data penting sekolah.

Target awal:

- master user
- siswa
- guru
- kelas
- mata pelajaran
- semester
- nilai
- pembayaran

Metadata minimal untuk CRUD:

- `label`: nama entitas
- `identifier`: kode unik atau nama data bila ada
- `changes`: ringkasan field yang berubah untuk aksi update

#### Verifikasi Pembayaran

Saat status pembayaran diverifikasi, audit log harus memakai aksi domain khusus `verifikasi pembayaran`, bukan `update`.

Metadata minimal:

- `pembayaran_id`
- `siswa`
- `jenis_pembayaran`
- `status_lama`
- `status_baru`

#### Input dan Update Nilai

Saat nilai pertama kali dibuat, gunakan `input nilai`.
Saat nilai yang sudah ada diubah, gunakan `update nilai`.

Metadata minimal:

- `nilai_id`
- `siswa`
- `mata_pelajaran`
- `semester`
- `jenis_nilai`
- `nilai_lama` untuk update
- `nilai_baru`

## Halaman Audit Log Admin

Resource audit log tetap menjadi pusat tampilan histori aktivitas.

Peningkatan yang diperlukan:

- Tambah filter berdasarkan user.
- Tambah filter berdasarkan aksi.
- Tambah filter berdasarkan periode laporan.
- Tampilkan kolom yang lebih mudah dibaca.
- Sediakan tombol ekspor PDF berdasarkan filter aktif.

Kolom utama yang direkomendasikan:

- user
- aksi
- entitas/subject
- ringkasan aktivitas
- IP address
- waktu

## Filter Periode

Filter periode yang wajib tersedia:

- per hari
- per minggu
- per tahun
- per semester

Perilaku filter:

- `per hari`: berdasarkan satu tanggal
- `per minggu`: berdasarkan rentang minggu kalender
- `per tahun`: berdasarkan satu tahun
- `per semester`: berdasarkan data `semester` aktif atau semester yang dipilih user

Rekomendasi implementasi:

- Simpan seluruh filter dalam state tabel/resource agar konsisten dipakai baik untuk tampilan maupun ekspor.
- Untuk `per semester`, gunakan relasi atau kecocokan tanggal dengan periode semester yang dipilih. Jika audit log tidak punya `semester_id`, maka filter semester bekerja lewat rentang tanggal semester.

## Ekspor PDF

Admin dapat mengunduh PDF audit log berdasarkan filter aktif saat ini.

Isi PDF minimal:

- judul laporan
- periode laporan
- waktu cetak
- nama admin pencetak
- tabel ringkasan log

Kolom PDF minimal:

- No
- Waktu
- User
- Aksi
- Entitas
- Keterangan
- IP Address

Ketentuan:

- PDF hanya mengekspor data sesuai filter yang aktif.
- Jika tidak ada data pada periode terpilih, tampilkan pesan yang jelas dan jangan menghasilkan PDF kosong tanpa informasi.

## Struktur Data Properties

Kolom `properties` dipakai untuk metadata tambahan agar isi log tetap fleksibel.

Struktur yang direkomendasikan:

```php
[
    'label' => 'Pembayaran',
    'identifier' => 'SPP - Andi Saputra',
    'summary' => 'Status pembayaran diubah menjadi Lunas',
    'changes' => [
        'status' => [
            'old' => 'Menunggu Verifikasi',
            'new' => 'Lunas',
        ],
    ],
]
```

Tujuan struktur ini:

- Mudah ditampilkan pada tabel.
- Mudah diringkas pada PDF.
- Tetap fleksibel untuk banyak jenis entitas.

## Error Handling

- Kegagalan menyimpan audit log tidak boleh menggagalkan aksi utama yang sedang dilakukan user.
- Kegagalan pencatatan audit perlu dicatat ke log aplikasi agar tetap bisa ditelusuri.
- Ekspor PDF harus menampilkan pesan valid jika filter tidak lengkap atau data kosong.

## Keamanan dan Akses

- Hanya admin yang dapat melihat halaman audit log.
- Hanya admin yang dapat mengekspor laporan PDF audit log.
- User biasa tidak boleh melihat histori audit log user lain.

## Pengujian

Pengujian minimal yang perlu dibuat nanti:

- login menghasilkan audit log
- logout menghasilkan audit log
- create/update/delete menghasilkan audit log
- verifikasi pembayaran menghasilkan aksi domain yang benar
- input/update nilai menghasilkan aksi domain yang benar
- filter periode mengembalikan data yang sesuai
- ekspor PDF menggunakan hasil filter aktif

## Tahapan Implementasi

1. Buat service `AuditLogger`.
2. Integrasikan log `login` dan `logout`.
3. Integrasikan log CRUD pada resource penting.
4. Integrasikan log domain khusus untuk pembayaran dan nilai.
5. Tingkatkan tampilan tabel audit log dan filter periodenya.
6. Tambahkan ekspor PDF dari filter aktif.
7. Tambahkan test fitur untuk alur audit log dan laporan.

## Batasan Awal

- Audit log belum mencatat aksi lihat halaman.
- Audit log belum mencatat unduhan selain laporan audit log.
- Audit log tahap awal fokus pada admin panel Filament.

## Hasil yang Diharapkan

Setelah fitur selesai:

- Admin dapat memantau aksi penting user dengan jelas.
- Aktivitas penting dapat difilter berdasarkan periode operasional sekolah.
- Riwayat aktivitas dapat diunduh sebagai PDF untuk kebutuhan arsip dan evaluasi.

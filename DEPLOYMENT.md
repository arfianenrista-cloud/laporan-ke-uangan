# Panduan Deploy KeuanganKu ke Cloud (Railway / Render)

Kode proyek KeuanganKu sudah teroptimasi dengan **Dockerfile** produksi dan konfigurasi **Render & Railway**, serta sudah di-push ke repositori GitHub:
👉 **[https://github.com/arfianenrista-cloud/laporan-ke-uangan](https://github.com/arfianenrista-cloud/laporan-ke-uangan)**

---

## Opsi 1: Deploy ke Railway (Sangat Cepat & Direkomendasikan)

1. Buka situs **[Railway.com](https://railway.com/)** dan pilih **Login with GitHub**.
2. Klik tombol **New Project** di dashboard.
3. Pilih **Deploy from GitHub repo**.
4. Cari dan pilih repositori: `arfianenrista-cloud/laporan-ke-uangan`.
5. Railway akan otomatis mendeteksi `Dockerfile` dan langsung memulai proses build & deployment.
6. **Mendapatkan Link Publik**:
   - Klik kartu layanan proyek Anda.
   - Buka tab **Settings** $\rightarrow$ cari bagian **Networking** $\rightarrow$ klik **Generate Domain**.
   - Anda akan mendapatkan URL HTTPS gratis (contoh: `https://laporan-ke-uangan-production.up.railway.app`).
7. **Environment Variables (Opsional)**:
   - Buka tab **Variables** $\rightarrow$ **New Variable**:
     - `APP_ENV`: `production`
     - `APP_DEBUG`: `false`
     - `APP_URL`: *isi dengan domain yang digenerate di atas*

---

## Opsi 2: Deploy ke Render.com

1. Buka situs **[Render.com](https://render.com/)** dan pilih **Sign in with GitHub**.
2. Klik tombol **New +** di pojok kanan atas $\rightarrow$ pilih **Web Service**.
3. Hubungkan repositori GitHub: `arfianenrista-cloud/laporan-ke-uangan`.
4. Render otomatis mendeteksi file `render.yaml` dan `Dockerfile`:
   - **Runtime**: Docker
   - **Instance Type**: Free
   - **Region**: Singapore (paling cepat untuk Indonesia)
5. Klik **Deploy Web Service**.
6. Tunggu sekitar 2-3 menit hingga status menjadi **Live**. Aplikasi Anda siap diakses melalui link `https://keuanganku-xxxx.onrender.com`.

---

## Akun Demo Default
- **Email**: `demo@keuangan.id`
- **Password**: `password123`


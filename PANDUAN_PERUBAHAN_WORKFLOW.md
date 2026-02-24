# Panduan Pemisahan Alur Persetujuan Aplikasi dan Pengadaan

## 📋 Ringkasan Perubahan

Sistem telah diperbarui untuk memisahkan alur persetujuan antara **Request Aplikasi** dan **Pengadaan**. Ini memungkinkan management untuk menyetujui aplikasi terlebih dahulu sambil menunggu proses pengadaan yang biasanya memerlukan waktu lebih lama.

---

## 🔄 Alur Baru

### Status di Level Database
Ditambahkan 2 kolom baru di tabel `app_requests`:
- **`procurement_approval_status`**: Melacak status persetujuan pengadaan secara terpisah (default: `pending`)
- **`catatan_management_procurement`**: Untuk catatan khusus management tentang pengadaan

### Workflow Aplikasi (Tidak Ada Pengadaan)

```
kepala_ruang membuat request
         ↓
   submitted_to_admin
         ↓
    Admin IT review
         ↓
   submitted_to_management
         ↓
   Management ACC aplikasi
         ↓
   submitted_to_director
         ↓
   Director ACC aplikasi
         ↓
    approved
         ↓
   Admin mulai pekerjaan
         ↓
    in_progress → completed
```

### Workflow dengan Pengadaan (BARU)

Sekarang ada **2 approval stream terpisah**:

#### Stream 1: Persetujuan Aplikasi
```
kepala_ruang membuat request + mark needs_procurement
         ↓
   submitted_to_admin
         ↓
    Admin IT isi detail pengadaan
         ↓
   submitted_to_management
         ↓
   Management menyetujui APLIKASI
         ↓
   submitted_to_director
         ↓
   Director ACC APLIKASI
         ↓
    approved ← APLIKASI SUDAH BISA DIMULAI
         ↓
   Admin mulai pekerjaan
         ↓
    in_progress
```

#### Stream 2: Persetujuan Pengadaan (TERPISAH)
```
Management menyetujui PENGADAAN (bisa bersamaan dengan aplikasi)
         ↓
   procurement_approval_status = "submitted_to_management"
         ↓
   Bendahara review pengadaan
         ↓
   Bendahara approve/reject pengadaan
         ↓
   Direktur final approval pengadaan
```

### Keuntungan Desain Baru

✅ **Aplikasi bisa langsung berjalan** tanpa menunggu pengadaan selesai
✅ **Management bisa oversee 2 alur** secara independen
✅ **Fleksibilitas lebih** dalam timing setiap tahap
✅ **Reduce blocking** pada tahap development aplikasi
✅ **Parallel processing** aplikasi dan pengadaan

---

## 👤 Alur untuk Setiap Role

### Kepala Ruang
- Membuat request aplikasi dengan/tanpa flag "needs_procurement"
- Jika ada pengadaan, isi detail barang yang dibutuhkan
- Lihat status baik aplikasi maupun pengadaan di dashboard

### Admin IT
- Review request aplikasi
- Jika aplikasi needs_procurement: isi estimasi harga & detail barang
- Forward ke Management untuk persetujuan

### Management
- **Lihat 2 panel terpisah** untuk Aplikasi dan Pengadaan
- **Menyetujui/Tolak APLIKASI**:
  - Klik "Setujui Aplikasi" → aplikasi lanjut ke Direktur
  - Atau klik "Tolak Aplikasi" → langsung ditolak
- **Menyetujui/Tolak PENGADAAN** (jika ada):
  - Bisa dilakukan bersamaan atau terpisah dari aplikasi
  - Klik "Lanjutkan Pengadaan" → pengadaan masuk ke proses Bendahara/Direktur
  - Atau klik "Tolak Pengadaan" → hanya pengadaan yang ditolak, aplikasi tetap bisa lanjut

### Direktur
- Persetujuan akhir untuk APLIKASI
- Persetujuan akhir untuk PENGADAAN (jika sudah dari Bendahara)

### Bendahara
- Validasi detail pengadaan (jika aplikasi punya `needs_procurement`)
- Approve atau reject pengadaan

---

## 🔧 Detail Teknis

### Perubahan Database
File migrasi: `database/migrations/2026_02_24_000000_separate_app_and_procurement_approval.php`

Kolom baru di `app_requests`:
```
- procurement_approval_status varchar(255) default 'pending'
- catatan_management_procurement text nullable
```

Status yang mungkin untuk `procurement_approval_status`:
- `pending` - Belum ada action
- `submitted_to_management` - Menunggu Management
- `submitted_to_bendahara` - Diteruskan ke Bendahara  
- `submitted_to_director` - Diteruskan ke Direktur
- `approved` - Sudah disetujui
- `rejected` - Ditolak

### Perubahan Controller
File: `app/Http/Controllers/AppRequestController.php`

**Method Baru:**
- `managementApproveProcurementForApp()` - Approve pengadaan di level AppRequest
- `managementRejectProcurementForApp()` - Reject pengadaan di level AppRequest

**Method Diperbarui:**
- `managementApprove()` - Sekarang hanya approve aplikasi, pengadaan terpisah
- `managementReject()` - Reject aplikasi saja

### Perubahan Routes
File: `routes/web.php`

Routes baru:
```php
Route::patch('/management/app/{id}/procurement/approve', ...)
    ->name('management.app.procurement.approve');
    
Route::patch('/management/app/{id}/procurement/reject', ...)
    ->name('management.app.procurement.reject');
```

### Perubahan View
File: `resources/views/apps/show.blade.php`

Sebelumnya: 1 panel untuk approval aplikasi
Sekarang: **2 panel terpisah**
- Panel kiri: Persetujuan Aplikasi
- Panel kanan: Persetujuan Pengadaan (hanya muncul jika `needs_procurement == true`)

---

## 📊 Contoh Skenario

### Skenario 1: Aplikasi Cepat, Pengadaan Lambat
```
Week 1:
- Kepala Ruang: Submit request aplikasi + pengadaan
- Admin IT: Review & forward ke Management
- Management: 
  ✓ Acc APLIKASI (ke Director)
  ✓ Lanjutkan PENGADAAN (ke Bendahara)
  
Week 2:
- Director: Acc APLIKASI
- Admin: Mulai development APP

Week 3:
- Bendahara: Acc pengadaan
- Direktur: Acc pengadaan (final)
- Procurement: Lanjut proses pembelian

Week 4-6:
- Admin: Continue development sambil menunggu hardware
- Aplikasi mostly done, tinggal testing

Result: Aplikasi jadi lebih cepat, tidak terblokir pengadaan
```

### Skenario 2: Reject Pengadaan Saja
```
Management review:
- Aplikasi: OK → Acc untuk development
- Pengadaan: Ada typo/harga aneh → Reject

Result:
- Aplikasi tetap berjalan di development
- Pengadaan diedit ulang oleh Admin IT
- Tidak perlu approval aplikasi lagi
```

---

## ✅ Checklist & Testing

Untuk memverifikasi perubahan bekerja dengan baik:

- [ ] Database migration berhasil dijalankan
- [ ] Kolom baru ada di tabel `app_requests`
- [ ] Kepala Ruang bisa buat request dengan/tanpa flag pengadaan
- [ ] Admin IT bisa process & forward ke Management
- [ ] Management melihat 2 panel terpisah untuk App & Procurement
- [ ] Management bisa ACC aplikasi tanpa ACC pengadaan
- [ ] Management bisa ACC pengadaan tanpa ACC aplikasi
- [ ] Director melihat app untuk final approval
- [ ] Bendahara melihat pengadaan untuk approval
- [ ] Status `procurement_approval_status` terupdate dengan benar
- [ ] Aplikasi bisa go to `in_progress` meski pengadaan masih pending

---

## 🆘 Troubleshooting

### Q: Aplikasi tidak muncul di step Management?
**A:** Pastikan statusnya `submitted_to_management` di database. Cek di Admin dashboard.

### Q: Panel pengadaan tidak muncul?
**A:** Pastikan `needs_procurement = true` untuk aplikasi tersebut. Jika tidak, edit via Admin atau buat request baru.

### Q: Bagaimana jika ingin batalkan pengadaan tapi aplikasi tetap lanjut?
**A:** Management bisa "Tolak Pengadaan" - aplikasi akan tetap di alurnya, hanya pengadaan yang di-reset.

### Q: Status procurement_approval tidak update?
**A:** Check file routes/web.php apakah route untuk `management.app.procurement.approve` sudah ada dan benar.

---

## 📧 Updated Relations

**AppRequest Model** sekarang memiliki:
- Status label untuk app: `getStatusLabelAttribute()`
- Status label untuk procurement: `getProcurementApprovalStatusLabelAttribute()`

---

## 🎯 Next Steps (Optional Improvements)

Jika ingin lebih advanced:
1. **Dashboard Management**: Tampilkan summary aplikasi vs pengadaan yang pending
2. **Notification**: Alert jika aplikasi di-acc tapi pengadaan masih lama
3. **Bulk Actions**: Approve multiple procurements sekaligus
4. **Report**: Analytics berapa lama typical approval time untuk app vs procurement
5. **Integration**: Link dengan system PO (Purchasing Order) untuk tracking lebih detail

---

Generated: 24 Feb 2026

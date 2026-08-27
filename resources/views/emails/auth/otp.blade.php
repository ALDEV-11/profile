<x-mail::message>
# 🔐 Verifikasi Login Admin

Halo,

Seseorang (mudah-mudahan Anda) sedang mencoba login ke **dashboard admin**. Gunakan kode OTP berikut untuk menyelesaikan proses verifikasi:

<x-mail::panel>
<div style="text-align: center; font-size: 32px; font-weight: 700; letter-spacing: 8px; color: #696cff; padding: 12px 0;">
{{ $otp }}
</div>
</x-mail::panel>

**Informasi Penting:**
- ⏱️ Kode ini berlaku selama **5 menit**
- 🔒 Maksimum **3 kali percobaan**
- 🚫 Setelah 3 kali gagal, akun akan diblokir selama **15 menit**

> Jika Anda **tidak melakukan login**, abaikan email ini dan pastikan keamanan akun Anda.

Salam,<br>
Admin <x-mail::subcopy>
    
Email ini dikirim secara otomatis oleh sistem keamanan {{ config('app.name') }}. Jangan bagikan kode OTP ini kepada siapapun.
</x-mail::subcopy>
</x-mail::message>

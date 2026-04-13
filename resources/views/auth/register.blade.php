@extends('layouts.app')

@section('content')
<div class="glass-card">
    <h1>Manual Registration Test</h1>
    <p class="subtitle">Modul 1: Identity & Verification - Registration Flow</p>

    @if(session('success'))
        <div class="success-box">
            {{ session('success') }} 
            <br>
            <strong>ID User:</strong> {{ session('user_id') }}
        </div>
    @endif

    <form action="{{ route('register.post') }}" method="POST">
        @csrf
        
        <div class="grid">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email" placeholder="name@example.com" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" placeholder="••••••••" required>
            </div>
        </div>

        <div class="form-group">
            <label for="full_name">Full Name (Sesuai Sertifikat)</label>
            <input type="text" name="full_name" id="full_name" placeholder="Masukkan nama lengkap" required>
        </div>

        <div class="grid">
            <div class="form-group">
                <label for="rank">Rank / Jabatan</label>
                <select name="rank" id="rank" required>
                    <option value="" disabled selected>Pilih Rank</option>
                    <option value="Captain">Captain / Master</option>
                    <option value="Chief Officer">Chief Officer</option>
                    <option value="Second Officer">Second Officer</option>
                    <option value="Chief Engineer">Chief Engineer</option>
                    <option value="Rating">Rating / AB</option>
                </select>
            </div>
            <div class="form-group">
                <label for="phone">Nomor WhatsApp</label>
                <input type="text" name="phone" id="phone" placeholder="0812..." required>
            </div>
        </div>

        <div class="grid">
            <div class="form-group">
                <label for="sea_time_total">Sea Time Total (Bulan)</label>
                <input type="number" name="sea_time_total" id="sea_time_total" placeholder="Contoh: 24" required>
            </div>
            <div class="form-group">
                <label for="region">Provinsi / Domisili</label>
                <input type="text" name="region" id="region" placeholder="Contoh: Jawa Barat" required>
            </div>
        </div>

        <div class="grid">
            <div class="form-group">
                <label for="preferred_vessel">Tipe Kapal Favorit</label>
                <select name="preferred_vessel" id="preferred_vessel" required>
                    <option value="Container">Container</option>
                    <option value="Bulk Carrier">Bulk Carrier</option>
                    <option value="Tanker">Tanker</option>
                    <option value="Cruise">Cruise</option>
                </select>
            </div>
            <div class="form-group">
                <label for="preferred_route">Rute Favorit</label>
                <select name="preferred_route" id="preferred_route" required>
                    <option value="Domestic">Domestic (Indonesia)</option>
                    <option value="ASEAN">Regional ASEAN</option>
                    <option value="Oceanic">Oceanic / International</option>
                </select>
            </div>
        </div>

        <button type="submit">Daftar Kandidat (Simpan ke DB)</button>
    </form>
</div>
@endsection

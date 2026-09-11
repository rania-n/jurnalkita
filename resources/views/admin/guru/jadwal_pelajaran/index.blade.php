@extends('layouts.app')

@section('content')
<div class="container my-4">

    <!-- BAGIAN 1: Status / Kartu Piket Hari Ini -->
    <div class="alert alert-warning shadow-sm mb-4 border-left-warning">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="alert-heading font-weight-bold mb-1">📌 Jadwal Piket Hari Ini</h5>
                <p class="mb-0 text-muted">Anda terdaftar sebagai petugas piket hari ini. Jika berhalangan, silakan ajukan dispensasi.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="/guru/piket" class="btn btn-primary btn-sm px-3">Piket Hari Ini</a>
                <a href="/guru/dispensasi" class="btn btn-outline-danger btn-sm px-3">Ajukan Dispensasi</a>
            </div>
        </div>
    </div>

    <!-- BAGIAN 2: Jadwal Pelajaran Seminggu -->
    <h4 class="font-weight-bold mb-3 text-dark">📅 Jadwal Mengajar Minggu Ini</h4>
    
    <div class="row">
        <!-- Senin -->
        <div class="col-md-6 mb-3">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white font-weight-bold">
                    Senin
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Pemrograman Web</strong><br>
                                <small class="text-muted">07:00 - 09:30</small>
                            </div>
                            <span class="badge bg-primary text-white">XII RPL 1</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Basis Data</strong><br>
                                <small class="text-muted">10:00 - 12:00</small>
                            </div>
                            <span class="badge bg-info text-white">XI RPL 2</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Selasa -->
        <div class="col-md-6 mb-3">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white font-weight-bold">
                    Selasa
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Informatika</strong><br>
                                <small class="text-muted">08:00 - 10:30</small>
                            </div>
                            <span class="badge bg-success text-white">X PPLG 1</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Rabu -->
        <div class="col-md-6 mb-3">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white font-weight-bold">
                    Rabu
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Pemrograman Web</strong><br>
                                <small class="text-muted">07:00 - 11:00</small>
                            </div>
                            <span class="badge bg-primary text-white">XII RPL 2</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Kamis -->
        <div class="col-md-6 mb-3">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white font-weight-bold">
                    Kamis
                </div>
                <div class="card-body p-3 text-center text-muted">
                    <small>Tidak ada jadwal mengajar hari ini.</small>
                </div>
            </div>
        </div>

        <!-- Jumat -->
        <div class="col-md-12 mb-3">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white font-weight-bold">
                    Jumat
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Basis Data</strong><br>
                                <small class="text-muted">07:30 - 09:30</small>
                            </div>
                            <span class="badge bg-info text-white">XI RPL 1</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
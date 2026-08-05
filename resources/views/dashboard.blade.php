<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | SAKERA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <style>
        :root { --primary: #435ebe; --bg-body: #f2f7ff; --sidebar-width: 280px; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-body); overflow-x: hidden; }
        #sidebar { width: var(--sidebar-width); height: 100vh; position: fixed; background: white; border-right: 1px solid #e9ecef; z-index: 1050; transition: all 0.3s ease; }
        .sidebar-header { padding: 2rem; text-align: center; }
        .nav-link { padding: 0.8rem 2rem; color: #25396f; font-weight: 600; display: flex; align-items: center; gap: 12px; transition: 0.2s; }
        .nav-link:hover { background: #f8f9fa; color: var(--primary); }
        .nav-link.active { background-color: #ebf3ff; color: var(--primary); border-right: 4px solid var(--primary); }
        #main-content { margin-left: var(--sidebar-width); padding: 2rem; transition: all 0.3s ease; }
        .card-stats { border: none; border-radius: 1.2rem; box-shadow: 0 10px 20px rgba(0,0,0,0.03); }
        .table-container { background: white; padding: 1.5rem; border-radius: 1.2rem; box-shadow: 0 8px 24px rgba(0,0,0,0.05); }
        .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; }
        .select2-container--bootstrap-5 .select2-selection { border: 1px solid #dee2e6 !important; border-radius: 0.75rem !important; min-height: 50px !important; background-color: #f8f9fa !important; display: flex; align-items: center; }
        @media (max-width: 992px) { #sidebar { margin-left: calc(-1 * var(--sidebar-width)); } #sidebar.active { margin-left: 0; } #main-content { margin-left: 0; padding: 1rem; } .sidebar-overlay { display: none; position: fixed; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 1040; top: 0; left: 0; } .sidebar-overlay.active { display: block; } }
        .burger-btn { display: none; cursor: pointer; padding: 10px; background: white; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        @media (max-width: 992px) { .burger-btn { display: inline-block; } }

        .swal2-container {
            z-index: 100000 !important; /* Pastikan di atas modal manapun */
        }

        .modal-open .swal2-container {
            pointer-events: all;
        }

        .modal-header.bg-primary {
            background: linear-gradient(45deg, #435ebe, #5a73d8) !important;
        }
        .form-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #4b4b4b;
        }
        .bg-light {
            background-color: #f8f9fa !important;
        }

        .tb-kgbkp th {
            vertical-align: middle!important; 
            text-align: center!important;    
        }
    </style>
</head>
<body>

    <div class="sidebar-overlay" id="overlay" onclick="toggleMenu()"></div>

    <nav id="sidebar">
        <div class="sidebar-header">
            <h2 class="fw-bold text-primary mb-0">SAKERA</h2>
            <p class="text-muted small text-uppercase" style="letter-spacing: 1px;">KPU Kabupaten Pasuruan</p>
        </div>
        <div class="nav flex-column mt-2">
            <a href="{{ route('dashboard') }}" class="nav-link {{ !request('category') && !Route::is('users.*') ? 'active' : '' }}">
                <i class="bi bi-grid-fill"></i> <span>Dashboard</span>
            </a>

            @if(Auth::user()->role == 'admin')
            <hr class="mx-4 my-2">
            <p class="mx-4 mb-2 text-muted small fw-bold">ADMINISTRATOR</p>
            <a href="{{ route('users.index') }}" class="nav-link {{ Route::is('users.*') ? 'active' : '' }}">
                <i class="bi bi-people-fill"></i> <span>Manajemen User</span>
            </a>
            @endif

            <hr class="mx-4 my-2">
            <p class="mx-4 mb-2 text-muted small fw-bold">MENU ARSIP</p>
            @php
                $menu = [
                    'Dokumen Pribadi dan Keluarga' => 'people-fill',
                    'Dokumen Sertifikat' => 'award-fill',
                    'Dokumen Pendidikan' => 'mortarboard-fill',
                    'Dokumen Surat Keputusan (SK)' => 'file-earmark-text-fill',
                    'Dokumen Izin / Pemberhentian' => 'file-earmark-x-fill',
                    'Dokumen Evaluasi Kinerja' => 'bar-chart-line-fill',
                    'Laporan Kinerja' => 'file-earmark-ruled-fill',
                    'Dokumen SPT Tahunan' => 'calculator-fill'
                ];
            @endphp
            @foreach($menu as $cat => $icon)
                <a href="{{ route('dashboard', ['category' => $cat, 'user_id' => request('user_id')]) }}" 
                   class="nav-link {{ request('category') == $cat ? 'active' : '' }}">
                    <i class="bi bi-{{ $icon }}"></i> <span>{{ str_replace('Dokumen ', '', $cat) }}</span>
                </a>
            @endforeach
            
            @if(Auth::user()->role == 'pegawai')
            <hr class="mx-4 my-2">
            <a href="#" class="nav-link text-dark" data-bs-toggle="modal" data-bs-target="#modalPassword">
                <i class="bi bi-shield-lock-fill"></i> <span>Ganti Password</span>
            </a>
            @endif
        </div>
    </nav>

    <main id="main-content">
        <header class="d-flex justify-content-between align-items-center mb-4 gap-2">
            <div class="d-flex align-items-center gap-3">
                <div class="burger-btn" onclick="toggleMenu()">
                    <i class="bi bi-list fs-3 text-primary"></i>
                </div>
                <h4 class="fw-bold mb-0 d-none d-sm-block">Halo, {{ Auth::user()->name }} 
                    <span class="badge bg-light text-primary small fw-normal ms-2">{{ strtoupper(Auth::user()->role) }}</span>
                </h4>
                <h6 class="fw-bold mb-0 d-block d-sm-none">SAKERA</h6>
            </div>
            
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-white shadow-sm rounded-pill px-4 fw-bold text-danger border-0">
                    <i class="bi bi-box-arrow-right me-2"></i> <span class="d-none d-md-inline">Keluar</span>
                </button>
            </form>
        </header>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        
        @if($errors->any())
            <div class="alert alert-danger rounded-4 border-0 shadow-sm mb-4">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row g-3 mb-4">
            <div class="col-12 col-md-4">
                <div class="card card-stats p-4 bg-primary text-white border-0 shadow-sm">
                    <small class="opacity-75">Total Berkas</small>
                    <h2 class="mb-0 fw-bold">{{ $stats['total'] }}</h2>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card card-stats p-4 bg-success text-white border-0 shadow-sm">
                    <small class="opacity-75">Berkas Valid</small>
                    <h2 class="mb-0 fw-bold">{{ $stats['valid'] }}</h2>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card card-stats p-4 bg-warning text-dark border-0 shadow-sm">
                    <small class="opacity-75">Perlu Validasi</small>
                    <h2 class="mb-0 fw-bold">{{ $stats['pending'] }}</h2>
                </div>
            </div>
        </div>

        <!-- export sertifikat -->
         @if(Auth::user()->role == 'admin')
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0 text-dark">Data Sertifikat</h5>
            <button type="button" class="btn btn-success btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#modalExportCertificates">
                <i class="bi bi-file-earmark-excel me-1"></i> Export Excel Sertifikat
            </button>
        </div>
        @endif

        {{-- Filter hanya muncul jika sedang membuka kategori (bukan dashboard utama) --}}
        @if(Auth::user()->role == 'admin' && request()->has('category'))
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-filter-square me-2"></i> Filter Data Pegawai</h6>
                <form action="{{ route('dashboard') }}" method="GET" class="row g-3" id="formFilterPegawai">
                    <input type="hidden" name="category" value="{{ request('category') }}">
                    <div class="col-md-8">
                        <select name="user_id" id="selectPegawai" class="form-select" required>
                            <option value="">-- Ketik Nama atau NIP Pegawai --</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                                    {{ $u->name }} ({{ $u->nip ?? 'NIP Tidak Ada' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('dashboard', ['category' => request('category')]) }}" class="btn btn-outline-secondary btn-lg w-100 rounded-3 text-uppercase fw-bold" style="font-size: 14px; height: 50px; display: flex; align-items: center; justify-content: center;">Reset Filter</a>
                    </div>
                </form>
            </div>
        </div>
        @endif

        <!-- KGB KP -->
        @if(!request()->has('category'))
        <div class="card border-0 shadow-sm rounded-4 mt-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom-0">
                <h6 class="m-0 fw-bold text-primary">Daftar Tabel Pengajuan Kenaikan Gaji Berkala dan Kenaikan Pangkat</h6>
                @if(Auth::user()->role == 'admin')
                <button class="btn btn-primary btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#modalTambahKgb">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Pengajuan
                </button>
                @endif
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle tb-kgbkp" style="min-width: 1800px; font-size: 13px;">
                        <thead class="text-center">
                            <tr>
                                <th rowspan="2">No</th>
                                <th rowspan="2">Nama</th>
                                <th rowspan="2">NIP</th>
                                <th rowspan="2">Pangkat/Gol</th>
                                <th rowspan="2">TMT CPNS</th>
                                <th colspan="4" class="bg-primary text-white">Kenaikan Gaji Berkala (KGB)</th>
                                <th colspan="4" class="bg-success text-white">Kenaikan Pangkat (KP)</th>
                                @if(Auth::user()->role == 'admin')
                                    <th rowspan="2">Aksi</th>
                                @endif
                            </tr>
                            <tr>
                                <th>Terakhir</th>
                                <th>Selanjutnya</th>
                                <th>Deadline</th>
                                <th>Status</th>
                                <th>Terakhir</th>
                                <th>Selanjutnya</th>
                                <th>Deadline</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kgbKpData as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="fw-bold">{{ $item->nama }}</td>
                                <td>{{ $item->nip }}</td>
                                <td>{{ $item->pangkat }} ({{ $item->golongan }})</td>
                                <td>{{ $item->tmt_cpns ? \Carbon\Carbon::parse($item->tmt_cpns)->format('d/m/Y') : '-' }}</td>
                                
                                {{-- Kolom KGB --}}
                                <td>{{ $item->tmt_kgb_terakhir }}</td>
                                <td>{{ $item->tmt_kgb_selanjutnya }}</td>
                                <td class="text-danger fw-bold">{{ $item->deadline_kgb }}</td>
                                <td><span class="badge bg-warning text-dark">{{ $item->status_kgb }}</span></td>

                                {{-- Kolom KP --}}
                                <td>{{ $item->tmt_kp_terakhir }}</td>
                                <td>{{ $item->tmt_kp_selanjutnya }}</td>
                                <td class="text-danger fw-bold">{{ $item->deadline_kp }}</td>
                                <td><span class="badge bg-warning text-dark">{{ $item->status_kp }}</span></td>

                                @if(Auth::user()->role == 'admin')
                                <td class="text-center">
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-outline-warning" onclick='editKgb(@json($item))'>
                                            <i class="bi bi-pencil"></i>
                                        </button>

                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteKgb({{ $item->id }})">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalTambahKgb" tabindex="-1">
            <div class="modal-dialog modal-lg"> <div class="modal-content border-0 shadow">
                    <form action="{{ route('kgb-kp.store') }}" method="POST">
                        @csrf
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title fw-bold"><i class="bi bi-person-plus me-2"></i>Tambah Data Pengajuan KGB & KP</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="fw-bold text-secondary border-bottom pb-2">DATA PEGAWAI</h6>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Pilih Pegawai</label>
                                    <select name="user_id" id="user_id_add" class="form-select select2-modal" required>
                                        <option value="">-- Pilih Pegawai --</option>
                                        @foreach($users as $u)
                                            <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->nip ?? 'NIP Kosong' }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Pangkat</label>
                                    <input type="text" name="pangkat" class="form-control" placeholder="Pembina">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Golongan</label>
                                    <input type="text" name="golongan" class="form-control" placeholder="IV/a">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">TMT CPNS</label>
                                    <input type="date" name="tmt_cpns" class="form-control">
                                </div>
                            </div>

                            <div class="row mb-4 bg-light p-3 rounded-3">
                                <div class="col-12">
                                    <h6 class="fw-bold text-primary border-bottom border-primary pb-2">KENAIKAN GAJI BERKALA (KGB)</h6>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">TMT KGB Terakhir</label>
                                    <input type="date" name="tmt_kgb_terakhir" class="form-control">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">TMT KGB Selanjutnya</label>
                                    <input type="date" name="tmt_kgb_selanjutnya" class="form-control">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Deadline KGB</label>
                                    <input type="date" name="deadline_kgb" class="form-control">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Status KGB</label>
                                    <input type="text" name="status_kgb" class="form-control" placeholder="Aktif / Selesai">
                                </div>
                            </div>

                            <div class="row bg-light p-3 rounded-3">
                                <div class="col-12">
                                    <h6 class="fw-bold text-success border-bottom border-success pb-2">KENAIKAN PANGKAT (KP)</h6>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">TMT KP Terakhir</label>
                                    <input type="date" name="tmt_kp_terakhir" class="form-control">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">TMT KP Selanjutnya</label>
                                    <input type="date" name="tmt_kp_selanjutnya" class="form-control">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Deadline KP</label>
                                    <input type="date" name="deadline_kp" class="form-control">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Status KP</label>
                                    <input type="text" name="status_kp" class="form-control" placeholder="Proses / Selesai">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary px-4 fw-bold">Simpan Data Pengajuan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalEditKgb" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content border-0 shadow">
                    <form id="formEditKgb" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-header bg-warning text-dark">
                            <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Data Pengajuan</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="row mb-4">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Pangkat</label>
                                    <input type="text" name="pangkat" id="edit_pangkat" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Golongan</label>
                                    <input type="text" name="golongan" id="edit_golongan" class="form-control">
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">TMT CPNS</label>
                                    <input type="date" name="tmt_cpns" id="edit_tmt_cpns" class="form-control">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Status KGB</label>
                                    <input type="text" name="status_kgb" id="edit_status_kgb" class="form-control" placeholder="Aktif">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Status KP</label>
                                    <input type="text" name="status_kp" id="edit_status_kp" class="form-control" placeholder="Proses">
                                </div>
                            </div>
                            
                            <div class="row mb-4 bg-light p-3 rounded-3">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">TMT KGB Terakhir</label>
                                    <input type="date" name="tmt_kgb_terakhir" id="edit_tmt_kgb_terakhir" class="form-control">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">TMT KGB Selanjutnya</label>
                                    <input type="date" name="tmt_kgb_selanjutnya" id="edit_tmt_kgb_selanjutnya" class="form-control">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Deadline KGB</label>
                                    <input type="date" name="deadline_kgb" id="edit_deadline_kgb" class="form-control">
                                </div>
                            </div>

                            <div class="row bg-light p-3 rounded-3">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">TMT KP Terakhir</label>
                                    <input type="date" name="tmt_kp_terakhir" id="edit_tmt_kp_terakhir" class="form-control">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">TMT KP Selanjutnya</label>
                                    <input type="date" name="tmt_kp_selanjutnya" id="edit_tmt_kp_selanjutnya" class="form-control">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Deadline KP</label>
                                    <input type="date" name="deadline_kp" id="edit_deadline_kp" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-warning fw-bold">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
        <!-- END KGB KP -->

        @if($isCategory)
            @if(Auth::user()->role == 'admin' && !request('user_id'))
                <div class="text-center py-5 bg-white rounded-4 shadow-sm">
                    <i class="bi bi-person-check display-1 text-muted opacity-25"></i>
                    <h5 class="text-muted mt-3">Pilih nama pegawai terlebih dahulu.</h5>
                </div>
            @else
                <div class="table-container">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
                        <div>
                            <h5 class="fw-bold mb-1 text-primary">{{ request('category') }}</h5>
                            <p class="text-muted small mb-0">Dokumen: <strong>{{ $selected_user_name ?? Auth::user()->name }}</strong></p>
                        </div>
                        <a href="{{ route('download.batch', ['user_id' => request('user_id')]) }}" class="btn btn-dark btn-sm rounded-pill px-4 mt-2 mt-md-0"><i class="bi bi-download me-2"></i> Download ZIP</a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th width="50" class="ps-3 border-0">No</th>
                                    <th class="border-0">Jenis Dokumen</th>
                                    <th class="border-0">Status</th>
                                    <th class="text-center border-0">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $documentMap = [
                                        'Dokumen Pribadi dan Keluarga' => ['Kartu Pegawai', 'Kartu istri / Kartu Suami', 'NPWP', 'Taspen (PNS)', 'Surat Nikah', 'Surat Cerai / Kematian', 'E-KTP Pegawai', 'E-KTP Suami / Istri', 'Kartu Keluarga', 'BPJS Pegawai', 'BPJS Suami / Istri', 'BPJS Anak', 'BPJS Anak Ke-2', 'BPJS Anak Ke-3', 'Akta Kelahiran Pegawai', 'Akta Kelahiran Suami / Istri', 'Akta Kelahiran Anak', 'Akta Kelahiran Anak Ke-2', 'Akta Kelahiran Anak Ke-3'],
                                        'Dokumen Sertifikat' => ['Prajabatan', 'STT Ujian Kenaikan Tingkat', 'SK Penyesuaian Ijazah (BKN)', 'Sertifikat Diklat Struktural', 'Sertifikat Diklat Fungsional', 'Sertifikat Pelatihan / Seminar / Sosialisasi', 'Sertifikat Piagam Penghargaan'],
                                        'Dokumen Pendidikan' => ['Ijazah SD', 'Ijazah SMP', 'Ijazah SMA', 'Ijazah Diploma', 'Ijazah S1', 'Ijazah S2', 'Ijazah S3'],
                                        'Dokumen Surat Keputusan (SK)' => ['Kenaikan Pangkat', 'Kenaikan Gaji Berkala', 'Mutasi', 'CPNS', 'PNS', 'Penyesuaian Jabatan Fungsional', 'Penyesuaian Ijazah (KPU RI)', 'Pengangkatan Jabatan', 'Penugasan Jabatan'],
                                        'Dokumen Izin / Pemberhentian' => ['Surat izin belajar', 'Keputusan sanksi hukuman disiplin'],
                                        'Dokumen Evaluasi Kinerja' => ['SKP Triwulan', 'SKP Tahunan'],
                                        'Laporan Kinerja' => ['Laporan Kinerja'],
                                        'Dokumen SPT Tahunan' => ['Laporan SPT Tahunan']
                                    ];
                                    $items = $documentMap[request('category')] ?? [];
                                @endphp

                                @foreach($items as $index => $label)
                                @php $doc = $documents->where('title', $label)->first(); @endphp
                                <tr>
                                    <td class="ps-3">{{ $index + 1 }}</td>
                                    <td><div class="fw-bold">{{ $label }}</div></td>
                                    <td>
                                        @if($doc)
                                            <span class="status-badge border border-{{ $doc->status == 'valid' ? 'success' : ($doc->status == 'invalid' ? 'danger' : 'warning') }} text-{{ $doc->status == 'valid' ? 'success' : ($doc->status == 'invalid' ? 'danger' : 'warning') }} bg-{{ $doc->status == 'valid' ? 'success' : ($doc->status == 'invalid' ? 'danger' : 'warning') }} bg-opacity-10">
                                                {{ strtoupper($doc->status == 'invalid' ? 'DITOLAK' : $doc->status) }}
                                            </span>
                                            @if($doc->status == 'invalid' && $doc->admin_note)
                                                <div class="text-danger mt-1" style="font-size: 10px; max-width: 200px; line-height: 1.2;">
                                                    <i class="bi bi-info-circle"></i> {{ $doc->admin_note }}
                                                </div>
                                            @endif
                                        @else
                                            <span class="text-muted small">Belum diunggah</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex gap-1 justify-content-center">
                                            <button class="btn btn-sm btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalUpload{{$index}}"><i class="bi bi-cloud-arrow-up"></i></button>
                                            
                                            @if($doc)
                                                @php 
                                                    $multipleTitles = [
                                                        'Sertifikat Pelatihan / Seminar / Sosialisasi', 
                                                        'Sertifikat Piagam Penghargaan',
                                                        'SKP Triwulan', 
                                                        'SKP Tahunan', 
                                                        'Laporan SPT Tahunan', 
                                                        'Laporan Kinerja'
                                                    ];
                                                    $isMultiple = in_array($label, $multipleTitles); 
                                                @endphp
                                                @if($isMultiple)
                                                    <button class="btn btn-sm btn-info text-white shadow-sm" onclick="openModalList('{{ request('category') }}', '{{ $label }}', '{{ request('user_id') }}')">
                                                        <i class="bi bi-stack"></i>
                                                    </button>
                                                @else
                                                    <button class="btn btn-sm btn-info text-white shadow-sm" onclick="previewPdf('{{ asset('storage/'.$doc->file_path) }}')"><i class="bi bi-eye"></i></button>
                                                @endif

                                                <a href="{{ route('download', $doc->id) }}" class="btn btn-sm btn-success shadow-sm"><i class="bi bi-download"></i></a>
                                                <form action="{{ route('document.destroy', $doc->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus dokumen?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger shadow-sm"><i class="bi bi-trash"></i></button>
                                                </form>
                                                <!-- @if(Auth::user()->role == 'admin')
                                                    <button class="btn btn-sm btn-dark shadow-sm" data-bs-toggle="modal" data-bs-target="#valModal{{$doc->id}}"><i class="bi bi-check-circle"></i></button>
                                                @endif -->

                                                @if(Auth::user()->role == 'admin' && $doc)
                                                    <button class="btn btn-sm btn-dark shadow-sm" data-bs-toggle="modal" data-bs-target="#valModal{{$doc->id}}">
                                                        <i class="bi bi-check-circle"></i>
                                                    </button>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @else
            <!-- <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
                <i class="bi bi-folder2-open display-1 text-primary opacity-25"></i>
                <h4 class="fw-bold mt-4">Pusat Arsip Digital SAKERA</h4>
                <p class="text-muted">Gunakan menu di samping untuk mulai mengelola dokumen kepegawaian Anda.</p>
            </div> -->
        @endif
    </main>

    {{-- MODAL EXPORT EXCEL SERTIFIKAT --}}
    <div class="modal fade" id="modalExportCertificates" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Export Rekap Sertifikat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('documents.export-certificates') }}" method="GET">
                    <div class="modal-body p-4">
                        <p class="text-muted small mb-3">Pilih rentang tahun perolehan sertifikat pelatihan/seminar/sosialisasi yang ingin di-download:</p>
                        
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small fw-bold">Tahun Awal</label>
                                <input type="number" name="start_year" class="form-control" placeholder="Contoh: 2020" min="2000" max="{{ date('Y') }}" value="{{ date('Y') - 5 }}" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">Tahun Akhir</label>
                                <input type="number" name="end_year" class="form-control" placeholder="Contoh: {{ date('Y') }}" min="2000" max="{{ date('Y') }}" value="{{ date('Y') }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success rounded-pill px-4">
                            <i class="bi bi-download me-1"></i> Download Excel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL VALIDASI UNTUK ADMIN --}}
    @if(Auth::user()->role == 'admin')
        @foreach($documents as $doc) {{-- Pastikan menggunakan loop yang sama dengan data tabel --}}
        <div class="modal fade" id="valModal{{$doc->id}}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form action="{{ route('document.validate', $doc->id) }}" method="POST" class="modal-content border-0 shadow-lg">
                    @csrf
                    <div class="modal-header border-0 pb-0">
                        <h5 class="fw-bold text-dark">Validasi Berkas</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="p-3 bg-light rounded-3 mb-3">
                            <p class="small text-muted mb-1">Nama Dokumen:</p>
                            <p class="fw-bold mb-0 text-primary">{{ $doc->title }}</p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="small fw-bold mb-2">Status Verifikasi</label>
                            <select name="status" class="form-select border-2" required>
                                <option value="valid" {{ $doc->status == 'valid' ? 'selected' : '' }}>Setujui</option>
                                <option value="invalid" {{ $doc->status == 'invalid' ? 'selected' : '' }}>Ditolak</option>
                            </select>
                        </div>
                        
                        <div class="mb-0">
                            <label class="small fw-bold mb-2">Catatan Admin (Opsional)</label>
                            <textarea name="admin_note" class="form-control" rows="3" placeholder="Contoh: Dokumen kurang jelas atau salah upload...">{{ $doc->admin_note }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow">SIMPAN PERUBAHAN</button>
                    </div>
                </form>
            </div>
        </div>
        @endforeach
    @endif

    {{-- MODAL UPLOAD --}}
    @if($isCategory && isset($items))
        @foreach($items as $index => $label)
            <div class="modal fade" id="modalUpload{{$index}}" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <form action="{{ route('upload') }}" method="POST" enctype="multipart/form-data" class="modal-content border-0 shadow">
                        @csrf
                        <div class="modal-header border-0 bg-light">
                            <h6 class="fw-bold">Upload: {{ $label }}</h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4 text-start">
                            <input type="hidden" name="title" value="{{ $label }}">
                            <input type="hidden" name="category" value="{{ request('category') }}">
                            @if(Auth::user()->role == 'admin') <input type="hidden" name="forced_user_id" value="{{ request('user_id') }}"> @endif

                            <div class="mb-3 text-center"><i class="bi bi-file-earmark-pdf text-danger display-3"></i></div>

                            @if(request('category') == 'Laporan Kinerja')
                            <div class="mb-3">
                                <label class="small fw-bold mb-2">Pilih Bulan</label>
                                <select name="quarter" class="form-select" required>
                                    <option value="1">Januari</option>
                                    <option value="2">Februari</option>
                                    <option value="3">Maret</option>
                                    <option value="4">April</option>
                                    <option value="5">Mei</option>
                                    <option value="6">Juni</option>
                                    <option value="7">Juli</option>
                                    <option value="8">Agustus</option>
                                    <option value="9">September</option>
                                    <option value="10">Oktober</option>
                                    <option value="11">November</option>
                                    <option value="12">Desember</option>
                                </select>
                            </div>
                            @endif

                            @if($label == 'SKP Triwulan')
                            <div class="mb-3">
                                <label class="small fw-bold mb-2">Pilih Triwulan</label>
                                <select name="quarter" class="form-select" required>
                                    <option value="1">Triwulan 1</option><option value="2">Triwulan 2</option><option value="3">Triwulan 3</option><option value="4">Triwulan 4</option>
                                </select>
                            </div>
                            @endif

                            @if(request('category') == 'Dokumen SPT Tahunan' || request('category') == 'Dokumen Evaluasi Kinerja' || request('category') == 'Laporan Kinerja')
                            <div class="mb-3">
                                <label class="small fw-bold mb-2">Tahun Periode</label>
                                <input type="number" name="year" class="form-control" required value="{{ date('Y') }}">
                            </div>
                            @endif

                            @if(in_array($label, ['Sertifikat Piagam Penghargaan']))
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Judul Sertifikat</label>
                                    <input type="text" name="doc_title" class="form-control" placeholder="Contoh: Piagam Penghargaan Satker Terbaik" required>
                                </div>

                                <div class="mb-3">                                    
                                    <label class="form-label small fw-bold">Tahun</label>
                                    <input type="number" name="year" class="form-control" placeholder="Contoh: 2026" min="1900" max="{{ date('Y') }}" required>
                                </div>
                            @endif

                            @if(in_array($label, ['Sertifikat Pelatihan / Seminar / Sosialisasi']))
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Judul Sertifikat</label>
                                    <input type="text" name="doc_title" class="form-control" placeholder="Contoh: Pelatihan Tata Naskah Dinas" required>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Tahun</label>
                                        <input type="number" name="year" class="form-control" placeholder="Contoh: 2026" min="1900" max="{{ date('Y') }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">JP / Jam Pelatihan</label>
                                        <input type="number" name="training_hours" class="form-control" placeholder="Masukkan angka saja" min="1" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                    </div>
                                </div>
                            @endif

                            <div class="mb-0">
                                <label class="small fw-bold mb-2">Pilih File PDF (Maks 2MB)</label>
                                <input type="file" name="file" class="form-control" accept=".pdf" required>
                            </div>
                        </div>
                        <div class="modal-footer border-0"><button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow">SIMPAN BERKAS</button></div>
                    </form>
                </div>
            </div>
        @endforeach
    @endif

    {{-- MODAL DAFTAR BERKAS (STACK) --}}
    <div class="modal fade" id="modalListBerkas">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 bg-light">
                    <h6 class="fw-bold mb-0" id="modalListTitle">Daftar Arsip</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3 py-3 small">Periode / Judul</th>
                                    <th class="text-center py-3 small">Status</th>
                                    <th class="text-center py-3 small">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="listBerkasContainer"></tbody>
                        </table>
                    </div>
                </div>
                {{-- Tambahan Footer untuk Info & Tombol Pagination --}}
                <div class="modal-footer d-flex justify-content-between align-items-center bg-light border-0 py-2 px-3">
                    <small class="text-muted" id="paginationInfo">Menampilkan 0 dari 0 data</small>
                    <nav>
                        <ul class="pagination pagination-sm mb-0" id="paginationContainer"></ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL PREVIEW PDF --}}
    <div class="modal fade" id="previewModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header bg-dark text-white border-0"><h6 class="fw-bold mb-0">Preview Dokumen</h6><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                <div class="modal-body p-0 bg-secondary text-center">
                    <iframe id="pdfFrame" src="" width="100%" height="750px" class="border-0"></iframe>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL GANTI PASSWORD --}}
    <div class="modal fade" id="modalPassword" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('password.update') }}" method="POST" class="modal-content border-0 shadow">
                @csrf 
                @method('PUT')
                <div class="modal-header border-0">
                    <h6 class="fw-bold"><i class="bi bi-shield-lock me-2"></i>Ganti Password</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    
                    @if(Auth::user()->role === 'admin')
                        <div class="mb-3">
                            <label class="small fw-bold mb-2">Pilih Pegawai / User</label>
                            <select name="user_id" class="form-select border-primary">
                                <option value="{{ Auth::id() }}">-- Diri Sendiri (Admin) --</option>
                                @foreach(\App\Models\User::orderBy('name', 'asc')->get() as $u)
                                    @if($u->id !== Auth::id())
                                        <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->nip }})</option>
                                    @endif
                                @endforeach
                            </select>
                            <div class="form-text text-primary small mt-2">
                                <i class="bi bi-info-circle"></i> Admin tidak memerlukan password lama untuk meriset.
                            </div>
                        </div>
                    @else
                        <div class="mb-3">
                            <label class="small fw-bold mb-2">Password Lama</label>
                            <input type="password" name="current_password" class="form-control" required placeholder="Masukkan password saat ini">
                        </div>
                    @endif

                    <hr class="my-3 opacity-50">

                    <div class="mb-3">
                        <label class="small fw-bold mb-2">Password Baru</label>
                        <input type="password" name="password" class="form-control" required placeholder="Minimal 6 karakter">
                    </div>

                    <div class="mb-0">
                        <label class="small fw-bold mb-2">Konfirmasi Password Baru</label>
                        <input type="password" name="password_confirmation" class="form-control" required placeholder="Ulangi password baru">
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">SIMPAN PERUBAHAN</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL EDIT SERTIFIKAT --}}
    <div class="modal fade" id="modalEditDoc" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form id="formEditDoc" onsubmit="submitEditDoc(event)" enctype="multipart/form-data" class="modal-content border-0 shadow">
                @csrf
                <input type="hidden" name="_method" value="PUT">
                <input type="hidden" id="edit_doc_id">

                <div class="modal-header border-0 bg-light">
                    <h6 class="fw-bold mb-0"><i class="bi bi-pencil-square me-2"></i>Edit Sertifikat</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 text-start">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Judul Sertifikat</label>
                        <input type="text" name="doc_title" id="edit_doc_title" class="form-control" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Tahun</label>
                            <input type="number" name="year" id="edit_year" class="form-control" min="1900" max="{{ date('Y') }}" required>
                        </div>
                        <div class="col-md-6" id="container_edit_jp">
                            <label class="form-label small fw-bold">JP / Jam Pelatihan</label>
                            <input type="number" name="training_hours" id="edit_training_hours" class="form-control" placeholder="Masukkan angka">
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="small fw-bold mb-2">Ganti File PDF (Opsional)</label>
                        <input type="file" name="file" class="form-control" accept=".pdf">
                        <div class="form-text text-muted small">Biarkan kosong jika tidak ingin mengganti file PDF.</div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold">Simpan & Reset Validasi</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // SOLUSI UTAMA: Mematikan 'focus trap' Bootstrap agar SweetAlert bisa menerima input
        $(document).on('focusin', function(e) {
            if ($(e.target).closest(".swal2-container").length) {
                e.stopImmediatePropagation();
            }
        });
    </script>
    
    <script>
        $(document).ready(function() {
            $('#selectPegawai').select2({ theme: 'bootstrap-5', width: '100%', placeholder: "-- Ketik Nama atau NIP Pegawai --", allowClear: true });
            $('#selectPegawai').on('change', function() { if($(this).val() != "") { $('#formFilterPegawai').submit(); } });
        });

        function toggleMenu() { document.getElementById('sidebar').classList.toggle('active'); document.getElementById('overlay').classList.toggle('active'); }
        function previewPdf(url) { document.getElementById('pdfFrame').src = url; new bootstrap.Modal(document.getElementById('previewModal')).show(); }

        // OPEN MODAL LIST BERKAS PAGINATION
        let currentModalDocs = [];
        let currentPage = 1;
        const itemsPerPage = 10; // Default 10 file per halaman
        let currentIsAdmin = false;

        function openModalList(category, title, userId) {
            const container = document.getElementById('listBerkasContainer');
            document.getElementById('modalListTitle').innerText = 'Daftar ' + title;
            container.innerHTML = '<tr><td colspan="3" class="text-center py-4 text-muted">Memuat data...</td></tr>';
            document.getElementById('paginationInfo').innerText = 'Memuat...';
            document.getElementById('paginationContainer').innerHTML = '';
            
            new bootstrap.Modal(document.getElementById('modalListBerkas')).show();

            fetch(`{{ route('documents.by-category') }}?category=${encodeURIComponent(category)}&title=${encodeURIComponent(title)}&user_id=${userId}`)
                .then(r => r.json()).then(res => {
                    currentModalDocs = res.data || [];
                    currentIsAdmin = res.is_admin || false;
                    currentPage = 1; // Reset ke halaman 1
                    
                    renderModalTable();
                });
        }

        // FUNGSI RENDER TABEL BERDASARKAN HALAMAN AKTIF
        function renderModalTable() {
            const container = document.getElementById('listBerkasContainer');
            container.innerHTML = '';

            if (currentModalDocs.length === 0) {
                container.innerHTML = '<tr><td colspan="3" class="text-center py-4 text-muted">Tidak ada arsip.</td></tr>';
                document.getElementById('paginationInfo').innerText = 'Menampilkan 0 dari 0 data';
                document.getElementById('paginationContainer').innerHTML = '';
                return;
            }

            // Hitung Slice Data untuk Halaman Aktif
            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;
            const paginatedItems = currentModalDocs.slice(startIndex, endIndex);

            paginatedItems.forEach(doc => {
                let labelTampilan = doc.period ? doc.period : '-';
                if (doc.doc_title) {
                    let jpInfo = doc.training_hours ? ` (${doc.training_hours} JP)` : '';
                    let yearInfo = doc.period ? ` - Tahun ${doc.period}` : '';
                    labelTampilan = `<strong>${doc.doc_title}</strong><br><small class="text-muted">${yearInfo}${jpInfo}</small>`;
                }

                const badgeColor = doc.status === 'valid' ? 'success' : (doc.status === 'invalid' ? 'danger' : 'warning');
                const badgeText = doc.status === 'invalid' ? 'DITOLAK' : doc.status.toUpperCase();
                
                let actions = `<div class="d-flex gap-1 justify-content-center">
                    <button class="btn btn-sm btn-primary" onclick="previewPdf('/storage/${doc.file_path}')" title="Lihat"><i class="bi bi-eye"></i></button>
                    <a href="/document/download/${doc.id}" class="btn btn-sm btn-success" title="Download"><i class="bi bi-download"></i></a>
                    <button class="btn btn-sm btn-warning text-white" onclick='openModalEditDoc(${JSON.stringify(doc)})' title="Edit Sertifikat"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm btn-danger" onclick="deleteDoc(${doc.id})" title="Hapus"><i class="bi bi-trash"></i></button>`;

                if(currentIsAdmin) {
                    actions += `
                        <button type="button" class="btn btn-sm btn-success rounded-pill px-2" onclick="quickValidate(${doc.id}, 'valid')" title="Validasi"><i class="bi bi-check-lg"></i></button>
                        <button type="button" class="btn btn-sm btn-dark rounded-pill px-2" onclick="quickValidate(${doc.id}, 'invalid')" title="Tolak"><i class="bi bi-x-lg"></i></button>`;
                }
                actions += `</div>`;

                container.innerHTML += `
                    <tr>
                        <td class="ps-3 py-3">${labelTampilan}</td>
                        <td class="text-center py-3">
                            <span class="status-badge border border-${badgeColor} text-${badgeColor} bg-${badgeColor} bg-opacity-10">
                                ${badgeText}
                            </span>
                            ${doc.status === 'invalid' && doc.admin_note ? `<br><small class="text-danger" style="font-size:10px;">${doc.admin_note}</small>` : ''}
                        </td>
                        <td class="text-center py-3">${actions}</td>
                    </tr>`;
            });

            // Update Info Teks
            const showingStart = startIndex + 1;
            const showingEnd = Math.min(endIndex, currentModalDocs.length);
            document.getElementById('paginationInfo').innerText = `Menampilkan ${showingStart}-${showingEnd} dari ${currentModalDocs.length} data`;

            // Render Tombol Halaman
            renderPaginationControls();
        }

        // FUNGSI RENDER TOMBOL PAGINATION
        function renderPaginationControls() {
            const totalPages = Math.ceil(currentModalDocs.length / itemsPerPage);
            const paginationContainer = document.getElementById('paginationContainer');
            paginationContainer.innerHTML = '';

            if (totalPages <= 1) return; // Jika data <= 10, tidak perlu tampilkan tombol pagination

            // Tombol Prev
            const prevDisabled = currentPage === 1 ? 'disabled' : '';
            paginationContainer.innerHTML += `
                <li class="page-item ${prevDisabled}">
                    <a class="page-link" href="#" onclick="changeModalPage(${currentPage - 1}); return false;">&laquo;</a>
                </li>`;

            // Angka Halaman
            for (let i = 1; i <= totalPages; i++) {
                const activeClass = i === currentPage ? 'active' : '';
                paginationContainer.innerHTML += `
                    <li class="page-item ${activeClass}">
                        <a class="page-link" href="#" onclick="changeModalPage(${i}); return false;">${i}</a>
                    </li>`;
            }

            // Tombol Next
            const nextDisabled = currentPage === totalPages ? 'disabled' : '';
            paginationContainer.innerHTML += `
                <li class="page-item ${nextDisabled}">
                    <a class="page-link" href="#" onclick="changeModalPage(${currentPage + 1}); return false;">&raquo;</a>
                </li>`;
        }

        // FUNGSI PINDAH HALAMAN
        function changeModalPage(newPage) {
            const totalPages = Math.ceil(currentModalDocs.length / itemsPerPage);
            if (newPage < 1 || newPage > totalPages) return;
            
            currentPage = newPage;
            renderModalTable();
        }
        // END OPEN MODAL LIST BERKAS PAGINATION

        function openModalEditDoc(doc) {
            document.getElementById('edit_doc_id').value = doc.id;
            document.getElementById('edit_doc_title').value = doc.doc_title || '';
            document.getElementById('edit_year').value = doc.period || '';
            
            const thInput = document.getElementById('edit_training_hours');
            if(thInput) {
                thInput.value = doc.training_hours || '';
                // Sembunyikan input JP jika bukan sertifikat pelatihan
                if(doc.title === 'Sertifikat Piagam Penghargaan') {
                    document.getElementById('container_edit_jp').style.display = 'none';
                } else {
                    document.getElementById('container_edit_jp').style.display = 'block';
                }
            }

            new bootstrap.Modal(document.getElementById('modalEditDoc')).show();
        }

        // SUBMIT EDIT SERTIFIKAT VIA AJAX
        function submitEditDoc(event) {
            event.preventDefault();
            const id = document.getElementById('edit_doc_id').value;
            const formData = new FormData(document.getElementById('formEditDoc'));

            fetch(`/document/${id}`, {
                method: 'POST', // Laravel SPOOFING Method PUT via _method
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(r => r.json())
            .then(res => {
                if(res.success) {
                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message, timer: 1500, showConfirmButton: false })
                    .then(() => location.reload());
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                }
            })
            .catch(err => Swal.fire({ icon: 'error', title: 'Error', text: err.message }));
        }

        // PERBAIKAN UTAMA DI FUNGSI quickValidate
        function quickValidate(id, status) {
            const isValid = (status === 'valid');
            
            Swal.fire({
                title: isValid ? 'Validasi Berkas?' : 'Tolak Berkas?',
                text: isValid ? 'Berkas akan ditandai sebagai Valid.' : 'Berikan alasan penolakan berkas ini:',
                icon: isValid ? 'success' : 'warning',
                input: isValid ? null : 'textarea',
                inputPlaceholder: 'Tulis alasan di sini...',
                showCancelButton: true,
                confirmButtonColor: isValid ? '#198754' : '#212529',
                confirmButtonText: isValid ? 'Ya, Valid!' : 'Ya, Tolak!',
                cancelButtonText: 'Batal',
                showLoaderOnConfirm: true,
                
                // --- DUA KUNCI SOLUSI AGAR KURSOR BISA DIKLIK DI MODAL BOOTSTRAP ---
                target: document.getElementById('modalListBerkas'), // Menjadikan SweetAlert2 di dalam konteks Modal Bootstrap
                heightAuto: false,
                // -------------------------------------------------------------------

                didOpen: () => {
                    if (!isValid) {
                        const input = Swal.getInput();
                        if (input) {
                            setTimeout(() => { input.focus(); }, 100);
                        }
                    }
                },

                preConfirm: (note) => {
                    if (!isValid && !note) {
                        Swal.showValidationMessage('Alasan penolakan wajib diisi!');
                        return false;
                    }
                    
                    return fetch(`/document/validate/${id}`, {
                        method: 'POST',
                        headers: { 
                            'X-CSRF-TOKEN': '{{ csrf_token() }}', 
                            'Content-Type': 'application/json', 
                            'Accept': 'application/json' 
                        },
                        body: JSON.stringify({ 
                            status: status, 
                            admin_note: note || "" 
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(err => { 
                                throw new Error(err.message || 'Gagal memproses validasi'); 
                            });
                        }
                        return response.json();
                    })
                    .catch(error => {
                        Swal.showValidationMessage(`Gagal: ${error.message}`);
                    });
                }
            }).then((result) => {
                if (result.isConfirmed && result.value && result.value.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Status diperbarui.',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        location.reload(); 
                    });
                }
            });
        }

        function deleteDoc(id) {
            Swal.fire({
                title: 'Hapus Berkas?',
                text: "Data yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    const f = document.createElement('form');
                    f.method = 'POST'; f.action = `/document/${id}`;
                    f.innerHTML = `@csrf @method('DELETE')`;
                    document.body.appendChild(f); 
                    f.submit();
                }
            });
        }

        function editKgb(data) {
            document.getElementById('formEditKgb').action = `/kgb-kp/${data.id}`;
            
            if(document.getElementById('edit_pangkat')) document.getElementById('edit_pangkat').value = data.pangkat;
            if(document.getElementById('edit_golongan')) document.getElementById('edit_golongan').value = data.golongan;
            
            if(document.getElementById('edit_tmt_cpns')) document.getElementById('edit_tmt_cpns').value = data.tmt_cpns;
            if(document.getElementById('edit_status_kgb')) document.getElementById('edit_status_kgb').value = data.status_kgb;
            if(document.getElementById('edit_status_kp')) document.getElementById('edit_status_kp').value = data.status_kp;

            if(document.getElementById('edit_tmt_kgb_terakhir')) document.getElementById('edit_tmt_kgb_terakhir').value = data.tmt_kgb_terakhir;
            if(document.getElementById('edit_tmt_kgb_selanjutnya')) document.getElementById('edit_tmt_kgb_selanjutnya').value = data.tmt_kgb_selanjutnya;
            if(document.getElementById('edit_deadline_kgb')) document.getElementById('edit_deadline_kgb').value = data.deadline_kgb;

            if(document.getElementById('edit_tmt_kp_terakhir')) document.getElementById('edit_tmt_kp_terakhir').value = data.tmt_kp_terakhir;
            if(document.getElementById('edit_tmt_kp_selanjutnya')) document.getElementById('edit_tmt_kp_selanjutnya').value = data.tmt_kp_selanjutnya;
            if(document.getElementById('edit_deadline_kp')) document.getElementById('edit_deadline_kp').value = data.deadline_kp;

            new bootstrap.Modal(document.getElementById('modalEditKgb')).show();
        }

        function deleteKgb(id) {
            Swal.fire({
                title: 'Hapus Data?',
                text: "Data pengajuan ini akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/kgb-kp/${id}`;
                    form.innerHTML = `
                        @csrf
                        @method('DELETE')
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
    </script>
</body>
</html>
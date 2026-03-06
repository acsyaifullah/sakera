<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen User | SAKERA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root { --primary: #435ebe; --bg-body: #f2f7ff; --sidebar-width: 280px; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-body); overflow-x: hidden; }
        
        #sidebar { 
            width: var(--sidebar-width); 
            height: 100vh; 
            position: fixed; 
            background: white; 
            border-right: 1px solid #e9ecef; 
            z-index: 1050; 
            transition: all 0.3s ease; 
        }
        
        .sidebar-header { padding: 2rem; text-align: center; }
        .nav-link { 
            padding: 0.8rem 2rem; 
            color: #25396f; 
            font-weight: 600; 
            display: flex; 
            align-items: center; 
            gap: 12px; 
            transition: 0.2s; 
            text-decoration: none;
        }
        .nav-link:hover { background: #f8f9fa; color: var(--primary); }
        .nav-link.active { background-color: #ebf3ff; color: var(--primary); border-right: 4px solid var(--primary); }
        
        #main-content { 
            margin-left: var(--sidebar-width); 
            padding: 2rem; 
            transition: all 0.3s ease; 
        }
        
        .table-container { background: white; padding: 1.5rem; border-radius: 1.2rem; box-shadow: 0 8px 24px rgba(0,0,0,0.05); }
        .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; }

        @media (max-width: 992px) {
            #sidebar { margin-left: calc(-1 * var(--sidebar-width)); }
            #sidebar.active { margin-left: 0; }
            #main-content { margin-left: 0; padding: 1rem; }
            .sidebar-overlay {
                display: none; position: fixed; width: 100%; height: 100%;
                background: rgba(0, 0, 0, 0.5); z-index: 1040; top: 0; left: 0;
            }
            .sidebar-overlay.active { display: block; }
        }

        .burger-btn {
            display: none; cursor: pointer; padding: 10px; background: white;
            border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        @media (max-width: 992px) { .burger-btn { display: inline-block; } }
    </style>
</head>
<body>

    <div class="sidebar-overlay" id="overlay" onclick="toggleMenu()"></div>

    <nav id="sidebar">
        <div class="sidebar-header">
            <h2 class="fw-bold text-primary mb-0 text-uppercase">Sakera</h2>
            <p class="text-muted small text-uppercase" style="letter-spacing: 1px;">KPU Kab. Pasuruan</p>
        </div>
        <div class="nav flex-column mt-2">
            <a href="{{ route('dashboard') }}" class="nav-link">
                <i class="bi bi-grid-fill"></i> <span>Dashboard</span>
            </a>

            @if(Auth::user()->role == 'admin')
            <hr class="mx-4 my-2 opacity-25">
            <p class="mx-4 mb-2 text-muted small fw-bold">PENGATURAN</p>
            <a href="{{ route('users.index') }}" class="nav-link active">
                <i class="bi bi-people-fill"></i> <span>Manajemen User</span>
            </a>
            @endif

            <hr class="mx-4 my-2 opacity-25">
            <p class="mx-4 mb-2 text-muted small fw-bold">MENU ARSIP</p>
            @php
                $menu = [
                    'Dokumen Pribadi dan Keluarga' => 'people-fill',
                    'Dokumen Sertifikat' => 'award-fill',
                    'Dokumen Pendidikan' => 'mortarboard-fill',
                    'Dokumen Surat Keputusan (SK)' => 'file-earmark-text-fill',
                    'Dokumen Izin / Pemberhentian' => 'file-earmark-x-fill',
                    'Dokumen Evaluasi Kinerja' => 'bar-chart-line-fill',
                    'Dokumen SPT Tahunan' => 'calculator-fill'
                ];
            @endphp
            @foreach($menu as $cat => $icon)
                <a href="{{ route('dashboard', ['category' => $cat]) }}" class="nav-link">
                    <i class="bi bi-{{ $icon }}"></i> <span>{{ str_replace('Dokumen ', '', $cat) }}</span>
                </a>
            @endforeach
        </div>
    </nav>

    <main id="main-content">
        <header class="d-flex justify-content-between align-items-center mb-4 gap-2">
            <div class="d-flex align-items-center gap-3">
                <div class="burger-btn" onclick="toggleMenu()">
                    <i class="bi bi-list fs-3 text-primary"></i>
                </div>
                <div>
                    <h4 class="fw-bold mb-0 text-dark">Manajemen User</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 small text-muted">
                            <li class="breadcrumb-item">Admin</li>
                            <li class="breadcrumb-item active" aria-current="page">User List</li>
                        </ol>
                    </nav>
                </div>
            </div>
            
            <a href="{{ route('dashboard') }}" class="btn btn-white shadow-sm rounded-pill px-4 fw-bold border-0 text-dark">
                <i class="bi bi-arrow-left me-2 text-primary"></i> Dashboard
            </a>
        </header>

        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm rounded-4 alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="table-container border-0">
            <div class="row align-items-center mb-4">
                <div class="col-md-6">
                    <h5 class="fw-bold mb-0 text-primary">Database Pegawai</h5>
                    <p class="text-muted small mb-0">Total: {{ count($users) }} Pengguna terdaftar.</p>
                </div>
                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                    <button class="btn btn-primary rounded-pill px-4 shadow" data-bs-toggle="modal" data-bs-target="#modalTambahUser">
                        <i class="bi bi-person-plus-fill me-2"></i> Tambah User Baru
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr class="text-secondary small text-uppercase">
                            <th class="ps-3 border-0">NIP</th>
                            <th class="border-0">Nama Pegawai</th>
                            <th class="border-0">Hak Akses</th>
                            <th class="text-center border-0">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td class="ps-3 fw-bold text-dark">{{ $user->nip }}</td>
                            <td class="fw-semibold">{{ $user->name }}</td>
                            <td>
                                @if($user->role == 'admin')
                                    <span class="status-badge bg-danger text-white">ADMIN</span>
                                @else
                                    <span class="status-badge bg-primary text-white">PEGAWAI</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-2 justify-content-center">
                                    <button class="btn btn-sm btn-light border rounded-pill px-3" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalEditUser{{ $user->id }}">
                                        <i class="bi bi-pencil-square me-1"></i> Edit
                                    </button>

                                    @if($user->id !== Auth::id())
                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Hapus user ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2 border-0"><i class="bi bi-trash"></i></button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div class="modal fade" id="modalTambahUser" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('users.store') }}" method="POST" class="modal-content border-0 shadow-lg">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold text-primary">Tambah Akun Pegawai</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="small fw-bold mb-2">NIP</label>
                        <input type="text" name="nip" class="form-control" placeholder="Contoh: 1990..." required>
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold mb-2">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control" placeholder="Nama Lengkap & Gelar" required>
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold mb-2">Role</label>
                        <select name="role" class="form-select">
                            <option value="pegawai">Pegawai</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="small fw-bold mb-2">Password Awal</label>
                        <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold rounded-3 shadow">BUAT AKUN</button>
                </div>
            </form>
        </div>
    </div>

    @foreach($users as $user)
    <div class="modal fade" id="modalEditUser{{ $user->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('users.update', $user->id) }}" method="POST" class="modal-content border-0 shadow-lg">
                @csrf @method('PUT')
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold">Update Informasi User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="small fw-bold mb-2">NIP</label>
                        <input type="text" name="nip" class="form-control" value="{{ $user->nip }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold mb-2">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold mb-2">Role</label>
                        <select name="role" class="form-select">
                            <option value="pegawai" {{ $user->role == 'pegawai' ? 'selected' : '' }}>Pegawai</option>
                            <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                    </div>
                    <div class="p-3 bg-light rounded-3">
                        <label class="small fw-bold mb-2 text-primary">Reset Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Isi hanya jika ingin ganti password">
                        <small class="text-muted" style="font-size: 11px;">Minimal 6 karakter.</small>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold rounded-3">SIMPAN PERUBAHAN</button>
                </div>
            </form>
        </div>
    </div>
    @endforeach

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleMenu() {
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('overlay').classList.toggle('active');
        }
    </script>
</body>
</html>
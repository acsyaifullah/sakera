<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;
use App\Exports\CertificateExport;
use Maatwebsite\Excel\Facades\Excel;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $selected_user_id = $request->get('user_id');
        $category = $request->get('category');
        $isCategory = $request->has('category');

        // --- START: LOGIKA TABEL KGB & KP ---
        $kgbKpData = [];
        if (!$isCategory) {
            // Jika Admin
            if ($user->role == 'admin') {
                $kgbKpQuery = \App\Models\InfoKgbKp::query();
                // Jika admin memilih user tertentu di filter, saring tabelnya
                if ($selected_user_id) {
                    $kgbKpQuery->where('user_id', $selected_user_id);
                }
                $kgbKpData = $kgbKpQuery->orderBy('tmt_cpns', 'desc')->get();
            } 
            // Jika Pegawai, HANYA ambil data miliknya sendiri
            else {
                $kgbKpData = \App\Models\InfoKgbKp::where('user_id', $user->id)
                    ->orderBy('tmt_cpns', 'desc')
                    ->get();
            }
        }
        // --- END: LOGIKA TABEL KGB & KP ---

        // Logika Stats Dokumen (Source Code Asli)
        $statsQuery = ($user->role == 'admin' && $selected_user_id) 
                    ? Document::where('user_id', $selected_user_id) 
                    : (($user->role == 'admin') ? Document::query() : Document::where('user_id', $user->id));

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'valid' => (clone $statsQuery)->where('status', 'valid')->count(),
            'pending' => (clone $statsQuery)->where('status', 'pending')->count(),
        ];

        $users = [];
        $selected_user_name = null;

        if ($user->role == 'admin') {
            $users = User::where('role', 'pegawai')->orderBy('name', 'asc')->get();
            $query = Document::query();
            
            if ($selected_user_id) {
                $query->where('user_id', $selected_user_id);
                // $selected_user_name = User::find($selected_user_id)?->name;
                $userFound = User::find($selected_user_id);
                $selected_user_name = $userFound ? $userFound->name : null;
            } else {
                if(!$isCategory) {
                    $query = Document::query();
                } else {
                    $query->where('id', 0); 
                }
            }
        } else {
            $query = Document::where('user_id', $user->id);
        }

        if ($category) {
            $query->where('category', $category);
        }

        $documents = $query->orderBy('period', 'desc')->get();

        return view('dashboard', compact('documents', 'users', 'stats', 'isCategory', 'selected_user_name', 'kgbKpData'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:pdf|max:2048',
            'title' => 'required',
            'category' => 'required',
            'doc_title'      => 'nullable|string|max:255',
            'training_hours' => 'nullable|numeric',
            'year' => 'nullable|numeric|digits:4',
            'quarter' => 'nullable'
        ]);

        $user = Auth::user();
        
        if ($user->role == 'admin' && $request->has('forced_user_id')) {
            $targetUser = User::find($request->forced_user_id);
            $userId = $targetUser->id;
            $userName = $targetUser->name;
        } else {
            $userId = $user->id;
            $userName = $user->name;
        }

        // LOGIKA LABEL PERIODE (UNTUK TAMPILAN)
        $inputYear = $request->year;
        $inputQuarter = $request->quarter;
        $periodLabel = null;
    
        if ($inputYear) {
            // Jika kategori adalah Laporan Kinerja, cukup simpan tahun saja di kolom period
            if ($request->category == 'Laporan Kinerja') {
                $periodLabel = $inputYear . '-' . $inputQuarter;
            } else {
                // Untuk kategori lain (seperti SKP), tetap gunakan format (T1) jika ada quarter
                $periodLabel = $inputQuarter ? $inputYear . ' (T' . $inputQuarter . ')' : $inputYear;
            }
        }

        // TIMPA DOKUMEN LAMA
        $multipleTitles = [
            'Sertifikat Pelatihan / Seminar / Sosialisasi', 
            'Sertifikat Piagam Penghargaan',
            'SKP Triwulan', 
            'SKP Tahunan', 
            'Laporan SPT Tahunan', 
            'Laporan Kinerja'
        ];

        $isMultiple = in_array($request->title, $multipleTitles);

        // Jika BUKAN multiple, lakukan timpa (overwrite) dokumen lama
        if (!$isMultiple) {
            $existingDoc = Document::where('user_id', $userId)
                ->where('category', $request->category)
                ->where('title', $request->title)
                ->first();

            if ($existingDoc) {
                if (Storage::disk('public')->exists($existingDoc->file_path)) {
                    Storage::disk('public')->delete($existingDoc->file_path);
                }
                $existingDoc->delete();
            }
        }
        // END TIMPA DOKUMEN LAMA

        // LOGIKA PENAMAAN FILE (PERBAIKAN BUG PENUMPUKAN SERTIFIKAT)
        $file = $request->file('file');
        $cleanUserName = Str::slug($userName, '_');
        $cleanTitle = Str::slug($request->title, '_');
        $qSuffix = $inputQuarter ? ($request->category == 'Laporan Kinerja' ? "_" . $inputQuarter : "_t" . $inputQuarter) : "";
        $ySuffix = $inputYear ? "_" . $inputYear : "";

        // Tambahkan Slug Judul Sertifikat dan JP jika ada
        $docTitleSuffix = $request->doc_title ? "_" . Str::slug($request->doc_title, '_') : "";
        $jpSuffix = $request->training_hours ? "_" . $request->training_hours . "jp" : "";
        
        // Gabungkan penamaan unik
        $fileName = "{$cleanUserName}_{$cleanTitle}{$ySuffix}{$docTitleSuffix}{$jpSuffix}{$qSuffix}.pdf";
        
        $folderCategory = Str::slug($request->category, '_'); 
        $destinationPath = "archives/{$userId}/{$folderCategory}";

        $filePath = $file->storeAs($destinationPath, $fileName, 'public');

        // SIMPAN KE DATABASE
        Document::create([
            'user_id'    => $userId,
            'category'   => $request->category,
            'title'      => $request->title,
            'doc_title'      => $request->doc_title,
            'training_hours' => $request->training_hours,
            'file_path'  => $filePath,
            'status'     => 'pending',
            'period'     => $periodLabel,
            'quarter'    => $inputQuarter // Pastikan kolom ini ada di database
        ]);

        return back()->with('success', 'Berhasil mengarsipkan ' . $request->title . ($periodLabel ? " Periode $periodLabel" : ""));
    }

    // FITUR EDIT / UPDATE SERTIFIKAT & BERKAS MULTIPLE
    public function update(Request $request, $id)
    {
        $request->validate([
            'file'           => 'nullable|mimes:pdf|max:2048',
            'doc_title'      => 'nullable|string|max:255',
            'training_hours' => 'nullable|numeric',
            'year'           => 'nullable|numeric|digits:4',
        ]);

        $doc = Document::findOrFail($id);
        $user = Auth::user();

        if ($user->role != 'admin' && $doc->user_id != $user->id) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $targetUser = User::find($doc->user_id);
        $userName = $targetUser ? $targetUser->name : $user->name;

        // Ambil data baru atau gunakan yang lama
        $inputYear = $request->filled('year') ? $request->year : $doc->period;
        $docTitle  = $request->has('doc_title') ? $request->doc_title : $doc->doc_title;
        $tHours    = $request->has('training_hours') ? $request->training_hours : $doc->training_hours;

        $doc->doc_title      = $docTitle;
        $doc->training_hours = $tHours;
        $doc->period         = $inputYear;

        // Jika user mengunggah PDF baru
        if ($request->hasFile('file')) {
            // Hapus file lama
            if (Storage::disk('public')->exists($doc->file_path)) {
                Storage::disk('public')->delete($doc->file_path);
            }

            $file = $request->file('file');
            $cleanUserName  = Str::slug($userName, '_');
            $cleanTitle     = Str::slug($doc->title, '_');
            $ySuffix        = $inputYear ? "_" . $inputYear : "";
            $docTitleSuffix = $docTitle ? "_" . Str::slug($docTitle, '_') : "";
            $jpSuffix       = $tHours ? "_" . $tHours . "jp" : "";

            $fileName = "{$cleanUserName}_{$cleanTitle}{$ySuffix}{$docTitleSuffix}{$jpSuffix}.pdf";
            $folderCategory = Str::slug($doc->category, '_'); 
            $destinationPath = "archives/{$doc->user_id}/{$folderCategory}";

            $doc->file_path = $file->storeAs($destinationPath, $fileName, 'public');
        }

        // RESET STATUS KE PENDING (Agar direview ulang admin)
        $doc->status     = 'pending';
        $doc->admin_note = null;
        $doc->save();

        return response()->json([
            'success' => true, 
            'message' => 'Sertifikat berhasil diperbarui dan status dikembalikan ke Pending.'
        ]);
    }

    public function getDocumentsByCategory(Request $request)
    {
        $user = Auth::user();
        $targetUserId = ($user->role == 'admin' && $request->filled('user_id')) ? $request->user_id : $user->id;
        
        $documents = Document::where('user_id', $targetUserId)
            ->where('category', $request->category)
            ->where('title', $request->title)
            ->orderByRaw("LEFT(period, 4) DESC")
            ->orderByRaw("CAST(quarter AS UNSIGNED) DESC")
            ->get();

        return response()->json([
            'success' => true,
            'data' => $documents,
            'is_admin' => ($user->role == 'admin')
        ]);
    }

    public function validateDocument(Request $request, $id)
    {
        // Gunakan validator manual agar kita bisa menangkap error dengan pasti
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'status' => 'required|in:valid,invalid',
            'admin_note' => 'nullable' // Hilangkan 'string' untuk menghindari konflik tipe data
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . implode(', ', $validator->errors()->all())
            ], 422);
        }

        try {
            $doc = Document::findOrFail($id);
            $doc->update([
                'status' => $request->status,
                // Pastikan admin_note diubah ke string kosong jika null agar tidak error
                'admin_note' => $request->admin_note ?? '' 
            ]);

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Status diperbarui']);
            }

            return back()->with('success', 'Status dokumen berhasil diperbarui.');
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function download($id)
    {
        $doc = Document::findOrFail($id);
        return Storage::disk('public')->download($doc->file_path);
    }

    public function downloadBatch(Request $request)
    {
        $user = Auth::user();
        $userId = ($user->role == 'admin' && $request->has('user_id')) ? $request->user_id : $user->id;
        $targetUser = User::find($userId);

        if (!$targetUser) return back()->with('error', 'User tidak ditemukan.');

        $zipFileName = 'Arsip_' . Str::slug($targetUser->name, '_') . '_' . time() . '.zip';
        $zip = new ZipArchive;
        $zipPath = storage_path('app/public/' . $zipFileName);

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            $documents = Document::where('user_id', $userId)->get();

            foreach ($documents as $doc) {
                $fullPath = storage_path('app/public/' . $doc->file_path);
                if (file_exists($fullPath)) {
                    $folderInsideZip = Str::slug($doc->category, '_');
                    $zip->addFile($fullPath, $folderInsideZip . '/' . basename($fullPath));
                }
            }
            $zip->close();
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    public function destroy($id)
    {
        try {
            $document = Document::findOrFail($id);
            $user = Auth::user();
    
            if ($user->role != 'admin' && $document->user_id != $user->id) {
                return back()->with('error', 'Anda tidak memiliki akses.');
            }
    
            if (Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }
    
            $document->delete();
            return back()->with('success', 'Dokumen berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }

    public function exportCertificates(Request $request)
    {
        $request->validate([
            'start_year' => 'required|numeric',
            'end_year'   => 'required|numeric|gte:start_year',
        ]);

        $startYear = $request->start_year;
        $endYear   = $request->end_year;

        $fileName = "Rekap_Sertifikat_Pelatihan_{$startYear}_sd_{$endYear}.xlsx";

        return Excel::download(new CertificateExport($startYear, $endYear), $fileName);
    }
}
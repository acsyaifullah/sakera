<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

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
        $existingDoc = Document::where('user_id', $userId)
            ->where('category', $request->category)
            ->where('title', $request->title)
            ->where('period', $periodLabel)
            ->first();

        if ($existingDoc) {
            // Hapus file fisik dari storage
            if (Storage::disk('public')->exists($existingDoc->file_path)) {
                Storage::disk('public')->delete($existingDoc->file_path);
            }
            // Hapus record lama di database
            $existingDoc->delete();
        }
        // END TIMPA DOKUMEN LAMA

        // LOGIKA PENAMAAN FILE
        $file = $request->file('file');
        $cleanUserName = Str::slug($userName, '_');
        $cleanTitle = Str::slug($request->title, '_');
        // $qSuffix = $inputQuarter ? ($request->category == 'Laporan Kinerja' ? "_" . Str::slug($inputQuarter, '_') : "_t" . $inputQuarter) : "";
        $qSuffix = $inputQuarter ? ($request->category == 'Laporan Kinerja' ? "_" . $inputQuarter : "_t" . $inputQuarter) : "";
        $ySuffix = $inputYear ? "_" . $inputYear : "";
        
        $fileName = "{$cleanUserName}_{$cleanTitle}{$ySuffix}{$qSuffix}.pdf";
        
        $folderCategory = Str::slug($request->category, '_'); 
        $destinationPath = "archives/{$userId}/{$folderCategory}";

        $filePath = $file->storeAs($destinationPath, $fileName, 'public');

        // SIMPAN KE DATABASE
        Document::create([
            'user_id'    => $userId,
            'category'   => $request->category,
            'title'      => $request->title,
            'file_path'  => $filePath,
            'status'     => 'pending',
            'period'     => $periodLabel,
            'quarter'    => $inputQuarter // Pastikan kolom ini ada di database
        ]);

        return back()->with('success', 'Berhasil mengarsipkan ' . $request->title . ($periodLabel ? " Periode $periodLabel" : ""));
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
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InfoKgbKp;
use App\Models\User;

class KgbKpController extends Controller
{
    
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'pangkat' => 'nullable|string',
            'golongan' => 'nullable|string',
            'tmt_cpns' => 'nullable|date',
            'tmt_kgb_terakhir' => 'nullable|date',
            'tmt_kgb_selanjutnya' => 'nullable|date',
            'deadline_kgb' => 'nullable|date',
            'status_kgb' => 'nullable|string',
            'tmt_kp_terakhir' => 'nullable|date',
            'tmt_kp_selanjutnya' => 'nullable|date',
            'deadline_kp' => 'nullable|date',
            'status_kp' => 'nullable|string',
        ]);

        $user = \App\Models\User::find($request->user_id);
        
        // Tambahkan data identitas dari tabel user
        $data['nama'] = $user->name;
        $data['nip'] = $user->nip ?? '-';

        \App\Models\InfoKgbKp::create($data);

        return back()->with('success', 'Data pengajuan berhasil disimpan.');
    }

    // Update Data
    public function update(Request $request, $id)
    {
        $item = InfoKgbKp::findOrFail($id);
        $item->update($request->all());
        return back()->with('success', 'Data berhasil diperbarui');
    }

    public function destroy($id)
    {
        $item = InfoKgbKp::findOrFail($id);
        $item->delete();
        return back()->with('success', 'Data berhasil dihapus');
    }
}
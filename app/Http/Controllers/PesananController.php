<?php

namespace App\Http\Controllers;

use App\Models\ItemKeranjang;
use App\Models\ItemPesanan;
use App\Models\Keranjang;
use App\Models\Pesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PesananController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $keranjang = Keranjang::where('user_id', $user->id)->first();

        $item_keranjang = ItemKeranjang::where('keranjang_id', $keranjang->id)->get();
        $total_harga = $item_keranjang->sum('total');
        return view('home.checkout', compact('item_keranjang', 'total_harga'));
    }

    public function makeOrder(Request $request)
    {
        $user = Auth::user();
        $keranjang = Keranjang::where('user_id', $user->id)->first();

        $items = ItemKeranjang::where('keranjang_id', $keranjang->id)->get();

        // Gabungkan alamat lengkap
        $alamatLengkap = $request->alamat . ', ' . $request->kode_pos . ', ' . $request->kota . ', ' . $request->provinsi . ', ' . $request->negara;

        // Simpan data order ke database
        $pesanan = Pesanan::create([
            'user_id' => $user->id,
            'nama_lengkap' => $request->nama_lengkap,
            'alamat' => $alamatLengkap,
            'nomor_hp' => $request->nomor_hp,
            'catatan_pesanan' => $request->catatan_pesanan,
            'total' => $items->sum('total'),
            'status' => 'pending',
        ]);

        // Simpan detail item pesanan
        foreach ($items as $item) {
            $harga = $this->convertCurrency($item->produk->harga);

            ItemPesanan::create([
                'pesanan_id' => $pesanan->id,
                'produk_id' => $item->produk_id,
                'quantity' => $item->quantity,
                'harga' => $harga,
                'total' => $item->total,
            ]);
        }

        // Hapus item dari keranjang setelah checkout
        ItemKeranjang::where('keranjang_id', $keranjang->id)->delete();

        return redirect()->route('cart')->with('success', 'Your order has been placed successfully.');
    }

    private function convertCurrency($value)
    {
        // Menghapus prefix 'Rp ' dan tanda titik
        $value = str_replace(['Rp ', '.'], '', $value);

        // Mengonversi string ke integer
        return intval($value);
    }
}

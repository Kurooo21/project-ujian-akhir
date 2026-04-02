<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Banner;
use Illuminate\Support\Facades\File;

class BannerController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Banner::all()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120', // Maks 5MB
            'description' => 'nullable|string|max:255'
        ]);

        $banner = new Banner();
        
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            // Simpan gambar dengan nama unik di folder public/asset/banners
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('asset/banners');
            
            // Buat folder jika belum ada (opsional jika sudah ada)
            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true);
            }
            
            $image->move($destinationPath, $imageName);
            $banner->image_path = 'asset/banners/' . $imageName;
        }

        $banner->description = $request->description;
        $banner->save();

        return response()->json([
            'success' => true,
            'message' => 'Banner berhasil ditambahkan!',
            'data' => $banner
        ]);
    }

    public function destroy($id)
    {
        $banner = Banner::findOrFail($id);

        // Hapus file gambar dari server
        $imagePath = public_path($banner->image_path);
        if (File::exists($imagePath)) {
            File::delete($imagePath);
        }

        $banner->delete();

        return response()->json([
            'success' => true,
            'message' => 'Banner berhasil dihapus!'
        ]);
    }
}

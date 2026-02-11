include 'koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $nama_pelanggan = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $no_hp = mysqli_real_escape_string($koneksi, $_POST['no_hp']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $pesanan = mysqli_real_escape_string($koneksi, $_POST['pesanan_item']);
    $jumlah = (int) $_POST['jumlah'];
    
    $jenis_belanja = mysqli_real_escape_string($koneksi, $_POST['jenis_belanja']);
    
    // Harga satuan (bisa diambil dari database produk jika ada, tapi untuk sederhana kita ambil dari form atau set manual di sini)
    // Di sini kita asumsikan harga dikirim dari form (hidden input) atau ditentukan berdasarkan nama pesanan
    $harga_satuan = (float) $_POST['harga_satuan'];
    
    $total_harga = $jumlah * $harga_satuan;

    // Query insert data
    $query = "INSERT INTO pesanan (nama_pelanggan, no_hp, alamat, pesanan, jumlah, harga_satuan, total_harga, jenis_belanja) 
              VALUES ('$nama_pelanggan', '$no_hp', '$alamat', '$pesanan', '$jumlah', '$harga_satuan', '$total_harga', '$jenis_belanja')";

    if (mysqli_query($koneksi, $query)) {
        // Redirect kembali ke index.html dengan pesan sukses (bisa via alert JS)
        echo "<script>
                alert('Pesanan berhasil dibuat! Terima kasih.');
                window.location.href = 'index.html';
              </script>";
    } else {
        echo "Error: " . $query . "<br>" . mysqli_error($koneksi);
    }
} else {
    // Jika diakses langsung tanpa submit form
    header("Location: index.html");
    exit();
}
?>

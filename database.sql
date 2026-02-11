-- Membuat Database
CREATE DATABASE IF NOT EXISTS chipok_db;
USE chipok_db;

-- Membuat Tabel Pesanan
CREATE TABLE IF NOT EXISTS pesanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_pelanggan VARCHAR(255) NOT NULL,
    no_hp VARCHAR(20) NOT NULL,
    alamat TEXT NOT NULL,
    pesanan VARCHAR(255) NOT NULL,
    jumlah INT NOT NULL,
    harga_satuan DECIMAL(10, 2) NOT NULL,
    total_harga DECIMAL(10, 2) NOT NULL,
    tanggal_pesan TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

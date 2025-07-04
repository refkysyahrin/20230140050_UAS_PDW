-- Tabel untuk menyimpan data pengguna (Mahasiswa & Asisten)
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('mahasiswa','asisten') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel untuk menyimpan data mata praktikum
CREATE TABLE `mata_praktikum` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_praktikum` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `id_asisten` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_asisten` (`id_asisten`),
  CONSTRAINT `mata_praktikum_ibfk_1` FOREIGN KEY (`id_asisten`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel untuk menyimpan modul per mata praktikum
CREATE TABLE `modul` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_praktikum` int(11) NOT NULL,
  `nama_modul` varchar(255) NOT NULL,
  `deskripsi_modul` text DEFAULT NULL,
  `file_materi` varchar(255) DEFAULT NULL,
  `batas_waktu` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_praktikum` (`id_praktikum`),
  CONSTRAINT `modul_ibfk_1` FOREIGN KEY (`id_praktikum`) REFERENCES `mata_praktikum` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel untuk pendaftaran mahasiswa ke mata praktikum
CREATE TABLE `pendaftaran_praktikum` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_mahasiswa` int(11) NOT NULL,
  `id_praktikum` int(11) NOT NULL,
  `tanggal_daftar` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `mahasiswa_praktikum` (`id_mahasiswa`,`id_praktikum`),
  KEY `id_praktikum` (`id_praktikum`),
  CONSTRAINT `pendaftaran_praktikum_ibfk_1` FOREIGN KEY (`id_mahasiswa`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pendaftaran_praktikum_ibfk_2` FOREIGN KEY (`id_praktikum`) REFERENCES `mata_praktikum` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel untuk menyimpan laporan/tugas yang dikumpulkan mahasiswa
CREATE TABLE `laporan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_modul` int(11) NOT NULL,
  `id_mahasiswa` int(11) NOT NULL,
  `file_laporan` varchar(255) NOT NULL,
  `tanggal_kumpul` timestamp NOT NULL DEFAULT current_timestamp(),
  `nilai` int(3) DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_modul` (`id_modul`),
  KEY `id_mahasiswa` (`id_mahasiswa`),
  CONSTRAINT `laporan_ibfk_1` FOREIGN KEY (`id_modul`) REFERENCES `modul` (`id`) ON DELETE CASCADE,
  CONSTRAINT `laporan_ibfk_2` FOREIGN KEY (`id_mahasiswa`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
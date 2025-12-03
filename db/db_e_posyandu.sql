-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 03, 2025 at 04:11 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_e_posyandu`
--

-- --------------------------------------------------------

--
-- Table structure for table `balita`
--

CREATE TABLE `balita` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `nama_balita` varchar(100) NOT NULL,
  `nik` varchar(16) DEFAULT NULL,
  `tgl_lahir` date DEFAULT NULL,
  `jenis_kelamin` enum('L','P') DEFAULT NULL,
  `nama_ayah` varchar(100) DEFAULT NULL,
  `nama_ibu` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `balita`
--

INSERT INTO `balita` (`id`, `user_id`, `nama_balita`, `nik`, `tgl_lahir`, `jenis_kelamin`, `nama_ayah`, `nama_ibu`) VALUES
(1, 1, 'riko simanjuntak', '45272923237232', '2025-01-13', 'L', NULL, NULL),
(2, 1, 'riki', '23725327523823', '2025-12-02', 'P', NULL, NULL),
(3, 4, 'si imut', '', '2025-12-03', 'L', NULL, NULL),
(4, 1, 'fahrudin', '', '2025-11-30', 'L', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `jadwal`
--

CREATE TABLE `jadwal` (
  `id` int(11) NOT NULL,
  `kegiatan` varchar(100) DEFAULT NULL,
  `lokasi` varchar(100) DEFAULT NULL,
  `waktu_mulai` datetime DEFAULT NULL,
  `waktu_selesai` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `janji_imunisasi`
--

CREATE TABLE `janji_imunisasi` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `balita_id` int(11) NOT NULL,
  `imunisasi_id` int(11) NOT NULL,
  `tanggal_rencana` date NOT NULL,
  `catatan` text DEFAULT NULL,
  `status` enum('Menunggu','Disetujui','Selesai','Batal') DEFAULT 'Menunggu',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `janji_imunisasi`
--

INSERT INTO `janji_imunisasi` (`id`, `user_id`, `balita_id`, `imunisasi_id`, `tanggal_rencana`, `catatan`, `status`, `created_at`) VALUES
(1, 1, 1, 1, '2025-12-05', 'pagi jam 9', 'Selesai', '2025-12-03 11:47:25'),
(2, 1, 1, 3, '2025-12-04', 'jam 9 pagi ya buuu\r\n', 'Selesai', '2025-12-03 12:15:58'),
(3, 4, 3, 1, '2025-12-04', '', 'Selesai', '2025-12-03 12:56:09'),
(4, 1, 4, 1, '2025-12-03', '', 'Selesai', '2025-12-03 13:06:40'),
(5, 1, 4, 1, '2025-12-03', '', 'Batal', '2025-12-03 13:07:29');

-- --------------------------------------------------------

--
-- Table structure for table `master_imunisasi`
--

CREATE TABLE `master_imunisasi` (
  `id` int(11) NOT NULL,
  `nama_imunisasi` varchar(50) DEFAULT NULL,
  `usia_wajib_bulan` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `master_imunisasi`
--

INSERT INTO `master_imunisasi` (`id`, `nama_imunisasi`, `usia_wajib_bulan`) VALUES
(1, 'Hepatitis B0', 0),
(2, 'BCG', 1),
(3, 'Polio 1', 1),
(4, 'DPT-HB-Hib 1', 2),
(5, 'Polio 2', 2),
(6, 'DPT-HB-Hib 2', 3),
(7, 'Polio 3', 3),
(8, 'DPT-HB-Hib 3', 4),
(9, 'Polio 4', 4),
(10, 'Campak', 9);

-- --------------------------------------------------------

--
-- Table structure for table `pengukuran`
--

CREATE TABLE `pengukuran` (
  `id` int(11) NOT NULL,
  `balita_id` int(11) DEFAULT NULL,
  `tgl_ukur` date DEFAULT NULL,
  `berat_badan` decimal(5,2) DEFAULT NULL,
  `tinggi_badan` decimal(5,2) DEFAULT NULL,
  `lingkar_kepala` decimal(5,2) DEFAULT NULL,
  `keterangan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengukuran`
--

INSERT INTO `pengukuran` (`id`, `balita_id`, `tgl_ukur`, `berat_badan`, `tinggi_badan`, `lingkar_kepala`, `keterangan`) VALUES
(1, 2, '2025-12-03', 4.00, 50.00, 6.00, 'sehat tapi kurus'),
(2, 4, '2025-12-03', 3.00, 60.00, 5.00, ''),
(3, 3, '2025-12-03', 7.00, 100.00, 10.00, ''),
(4, 3, '2025-12-04', 9.00, 120.00, 11.00, '');

-- --------------------------------------------------------

--
-- Table structure for table `riwayat_imunisasi`
--

CREATE TABLE `riwayat_imunisasi` (
  `id` int(11) NOT NULL,
  `balita_id` int(11) DEFAULT NULL,
  `imunisasi_id` int(11) DEFAULT NULL,
  `tgl_suntik` date DEFAULT NULL,
  `bidan_penyuntik` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `riwayat_imunisasi`
--

INSERT INTO `riwayat_imunisasi` (`id`, `balita_id`, `imunisasi_id`, `tgl_suntik`, `bidan_penyuntik`) VALUES
(1, 1, 3, '2025-12-03', 'bidan rusdi'),
(2, 3, 1, '2025-12-03', 'Bidan Rusdii'),
(3, 4, 1, '2025-12-03', 'bidan anii');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `avatar` varchar(255) DEFAULT 'default.png',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama_lengkap`, `email`, `password`, `google_id`, `role`, `avatar`, `created_at`) VALUES
(1, 'srimulyano', 'test@test.com', '$2y$10$qIWywSuayETMoe774s.4D..bEbN3HdRXVjnccgB4iRYWboPCPmIwS', NULL, 'user', 'default.png', '2025-12-03 07:42:05'),
(3, 'admin', 'admin123@gmail.com', '$2y$10$AjhWSdOOBXDXMwUicVf.fuymnYfL1egO1PlO6Yb3Wf7f.uFQ903OS', NULL, 'admin', 'default.png', '2025-12-03 07:47:10'),
(4, 'sri astuti', 'test2@test.com', '$2y$10$qwuXqkC6X54xj3pr/Cka6uouqNidPHw9Nudm8roDXOwZuFF8hC2ja', NULL, 'user', 'default.png', '2025-12-03 12:55:11');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `balita`
--
ALTER TABLE `balita`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `jadwal`
--
ALTER TABLE `jadwal`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `janji_imunisasi`
--
ALTER TABLE `janji_imunisasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `balita_id` (`balita_id`),
  ADD KEY `imunisasi_id` (`imunisasi_id`);

--
-- Indexes for table `master_imunisasi`
--
ALTER TABLE `master_imunisasi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pengukuran`
--
ALTER TABLE `pengukuran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `balita_id` (`balita_id`);

--
-- Indexes for table `riwayat_imunisasi`
--
ALTER TABLE `riwayat_imunisasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `balita_id` (`balita_id`),
  ADD KEY `imunisasi_id` (`imunisasi_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `balita`
--
ALTER TABLE `balita`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `jadwal`
--
ALTER TABLE `jadwal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `janji_imunisasi`
--
ALTER TABLE `janji_imunisasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `master_imunisasi`
--
ALTER TABLE `master_imunisasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `pengukuran`
--
ALTER TABLE `pengukuran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `riwayat_imunisasi`
--
ALTER TABLE `riwayat_imunisasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `balita`
--
ALTER TABLE `balita`
  ADD CONSTRAINT `balita_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `janji_imunisasi`
--
ALTER TABLE `janji_imunisasi`
  ADD CONSTRAINT `janji_imunisasi_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `janji_imunisasi_ibfk_2` FOREIGN KEY (`balita_id`) REFERENCES `balita` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `janji_imunisasi_ibfk_3` FOREIGN KEY (`imunisasi_id`) REFERENCES `master_imunisasi` (`id`);

--
-- Constraints for table `pengukuran`
--
ALTER TABLE `pengukuran`
  ADD CONSTRAINT `pengukuran_ibfk_1` FOREIGN KEY (`balita_id`) REFERENCES `balita` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `riwayat_imunisasi`
--
ALTER TABLE `riwayat_imunisasi`
  ADD CONSTRAINT `riwayat_imunisasi_ibfk_1` FOREIGN KEY (`balita_id`) REFERENCES `balita` (`id`),
  ADD CONSTRAINT `riwayat_imunisasi_ibfk_2` FOREIGN KEY (`imunisasi_id`) REFERENCES `master_imunisasi` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

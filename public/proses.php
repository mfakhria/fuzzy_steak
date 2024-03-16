<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Result</title>
    <link rel="stylesheet" href="css/output.css" />
</head>
<body class="bg-orange-700">
    <div class="max-w-6xl mx-auto justify-center text-center mt-5">
        <h1 class="text-4xl font-bold text-white p-4">Result</h1>
        <div class="text-3xl text-white font-semibold">
            <?php
            // Include file koneksi.php
            include 'koneksi.php';

            // Ambil data dari formulir
            $suhu = $_POST['suhu'];
            $waktu = $_POST['waktu'];

            // Fungsi keanggotaan untuk suhu
            function fuzzySuhu($value) {
                $rendah = max(0, min(1, (250 - $value) / (250 - 200)));
                $sedang = max(0, min(1, (min($value, 300) - 240) / (300 - 240)));
                $tinggi = max(0, min(1, (min($value, 350) - 290) / (350 - 290)));

                return array('rendah' => $rendah, 'sedang' => $sedang, 'tinggi' => $tinggi);
            }

            // Fungsi keanggotaan untuk waktu
            function fuzzyWaktu($value) {
                $cepat = max(0, min(1, (7 - $value) / (7 - 3)));
                $sedang = max(0, min(1, (min($value, 12) - 6) / (12 - 6)));
                $lama = max(0, min(1, (min($value, 15) - 11) / (15 - 11)));

                return array('cepat' => $cepat, 'sedang' => $sedang, 'lama' => $lama);
                }

            // Fungsi keanggotaan untuk tingkat kematangan
            function fuzzyKematangan($value) {
                $rare = max(0, min(1, ($value - 50) / (60 - 50)));
                $medium_rare = max(0, min(1, ($value - 55) / (65 - 55)));
                $medium = max(0, min(1, ($value - 60) / (70 - 60)));
                $medium_well = max(0, min(1, ($value - 65) / (75 - 65)));
                $welldone = max(0, min(1, ($value - 70) / (80 - 70)));

                return array('rare' => $rare, 'medium rare' => $medium_rare, 'medium' => $medium, 'medium well' => $medium_well, 'welldone' => $welldone);
                }

            // Fungsi untuk mencari nilai maksimum
            function maxNilai($array) {
                $max = -INF;
                foreach ($array as $value) {
                    if ($value > $max) {
                        $max = $value;
                    }
                }
                return $max;
            }

            // Fungsi untuk menghitung nilai kematangan steak menggunakan metode Mamdani
            function fuzzyMamdani($suhu, $waktu) {
                $kematangan = array();
                foreach ($suhu as $key_suhu => $value_suhu) {
                    foreach ($waktu as $key_waktu => $value_waktu) {
                        $minKematangan = min($value_suhu, $value_waktu);
                        switch ($key_suhu) {
                            case 'rendah':
                                switch ($key_waktu) {
                                    case 'cepat':
                                        $kematangan['rare'][] = $minKematangan;
                                        break;
                                    case 'sedang':
                                        $kematangan['medium rare'][] = $minKematangan;
                                        break;
                                    case 'lama':
                                        $kematangan['medium rare'][] = $minKematangan;
                                        break;
                                }
                                break;
                            case 'sedang':
                                switch ($key_waktu) {
                                    case 'cepat':
                                        $kematangan['medium rare'][] = $minKematangan;
                                        break;
                                    case 'sedang':
                                        $kematangan['medium'][] = $minKematangan;
                                        break;
                                    case 'lama':
                                        $kematangan['medium well'][] = $minKematangan;
                                        break;
                                }
                                break;
                            case 'tinggi':
                                switch ($key_waktu) {
                                    case 'cepat':
                                        $kematangan['medium rare'][] = $minKematangan;
                                        break;
                                    case 'sedang':
                                        $kematangan['medium well'][] = $minKematangan;
                                        break;
                                    case 'lama':
                                        $kematangan['welldone'][] = $minKematangan;
                                        break;
                                }
                                break;
                        }
                    }
                }
            
                $output = array();
                foreach ($kematangan as $key => $value) {
                    $output[$key] = maxNilai($value);
                }
            
                return $output;
            }

            // Penggunaan fungsi-fungsi
            $suhu_input = $suhu;
            $waktu_input = $waktu;

            // Menghitung nilai keanggotaan
            $suhu = fuzzySuhu($suhu_input);
            $waktu = fuzzyWaktu($waktu_input);

            // Menghitung kematangan menggunakan metode Mamdani
            $kematangan = fuzzyMamdani($suhu, $waktu);

            // Menentukan tingkat kematangan dengan tingkat keanggotaan tertinggi
            $max_membership = max($kematangan);
            $tingkat_kematangan = array_search($max_membership, $kematangan);

            // Menampilkan hasil
            echo "Steak Doneness Level: " . ucfirst($tingkat_kematangan) . "<br>";

            // Simpan hasil ke database
            $sql = "INSERT INTO steak_kematangan (suhu, waktu, kematangan) VALUES ('$suhu_input', '$waktu_input', '$tingkat_kematangan')";

            if ($conn->query($sql) === TRUE) {
                echo "Data has been Successfully Saved to Database";
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }

            // Tutup koneksi database
            $conn->close();
            ?>
        </div>
        <div class="back-button" style="text-align: center; margin-top: 20px;">
        <button onclick="window.location.href = 'hitung.html';" class="bg-yellow-500 w-fit mx-auto p-3 rounded-xl text-xl text-white font-semibold hover:bg-yellow-600 transition-all">Count Back
</button>
        </div>

    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Ujian - {{ $riwayat->nim }}</title>
    <style>
        @page {
            size: A5 landscape;
            margin: 10mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.3;
            background: #fff;
        }

        .card {
            width: 100%;
            max-width: 210mm;
            margin: 0 auto;
            padding: 8mm;
            border: 2px solid #000;
        }

        .header {
            display: flex;
            align-items: flex-start;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }

        .header-logo {
            width: 60px;
            height: 60px;
            margin-right: 15px;
        }

        .header-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .header-text {
            flex: 1;
            text-align: center;
        }

        .header-text h1 {
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .header-text h2 {
            font-size: 13pt;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .header-text p {
            font-size: 10pt;
        }

        .student-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .student-info-left {
            flex: 1;
        }

        .student-info-left table {
            border-collapse: collapse;
        }

        .student-info-left td {
            padding: 2px 5px 2px 0;
            vertical-align: top;
        }

        .student-info-left td:first-child {
            width: 100px;
        }

        .nim-badge {
            border: 3px solid #000;
            padding: 10px 15px;
            text-align: center;
            font-size: 18pt;
            font-weight: bold;
            min-width: 120px;
        }

        .exam-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .exam-table th,
        .exam-table td {
            border: 1px solid #000;
            padding: 4px 6px;
            text-align: left;
            font-size: 10pt;
        }

        .exam-table th {
            background: #f0f0f0;
            text-align: center;
            font-weight: bold;
        }

        .exam-table .no {
            width: 30px;
            text-align: center;
        }

        .exam-table .kode {
            width: 70px;
        }

        .exam-table .matkul {
            width: auto;
        }

        .exam-table .paraf {
            width: 50px;
            text-align: center;
        }

        .footer {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
        }

        .footer-note {
            font-size: 9pt;
            font-style: italic;
            max-width: 60%;
        }

        .footer-signature {
            text-align: center;
            min-width: 150px;
        }

        .footer-signature .date {
            font-size: 10pt;
            margin-bottom: 50px;
        }

        .footer-signature .name {
            font-size: 10pt;
            font-weight: bold;
            border-top: 1px solid #000;
            padding-top: 3px;
        }

        .footer-signature .title {
            font-size: 9pt;
        }

        @media print {
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .no-print {
                display: none !important;
            }
        }

        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .print-button:hover {
            background: #2563eb;
        }
    </style>
</head>

<body>
    <button class="print-button no-print" onclick="window.print()">🖨️ Cetak Kartu</button>

    <div class="card">
        <div class="header">
            <div class="header-logo">
                {{-- Logo placeholder - ganti dengan logo institusi --}}
                <img src="{{ asset('images/logo.png') }}" alt="Logo" onerror="this.style.display='none'">
            </div>
            <div class="header-text">
                <h1>POLITEKNIK SAWUNGGALIH AJI</h1>
                <h2>KARTU UJIAN {{ $jenisUjian === 'UTS' ? 'TENGAH' : 'AKHIR' }} SEMESTER
                    {{ $activeSemester?->semester == 1 ? 'GANJIL' : 'GENAP' }}</h2>
                <p>TAHUN AKADEMIK {{ $activeSemester?->nama_semester ?? '-' }}</p>
            </div>
        </div>

        <div class="student-info">
            <div class="student-info-left">
                <table>
                    <tr>
                        <td>Nama</td>
                        <td>:</td>
                        <td><strong>{{ $mahasiswa->nama_mahasiswa }}</strong></td>
                    </tr>
                    <tr>
                        <td>NIM</td>
                        <td>:</td>
                        <td><strong>{{ $riwayat->nim }}</strong></td>
                    </tr>
                    <tr>
                        <td>Prodi</td>
                        <td>:</td>
                        <td>{{ $riwayat->prodi?->nama_program_studi ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Semester/Kelas</td>
                        <td>:</td>
                        <td>{{ $riwayat->latestAktivitasKuliah?->smt_mhs ?? '-' }} /
                            {{ strtoupper(substr($riwayat->nim, 0, 4)) }}-{{ substr($riwayat->nim, -3) }}</td>
                    </tr>
                </table>
            </div>
            <div class="nim-badge">
                {{ strtoupper(substr($riwayat->nim, 0, 4)) }}-{{ substr($riwayat->nim, -3) }}
            </div>
        </div>

        <table class="exam-table">
            <thead>
                <tr>
                    <th class="no">NO</th>
                    <th class="kode">KODE MK</th>
                    <th class="matkul">MATA KULIAH</th>
                    <th class="paraf" colspan="2">PARAF PENGAWAS</th>
                </tr>
                <tr>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th class="paraf">Tgl:</th>
                    <th class="paraf">Tgl:</th>
                </tr>
            </thead>
            <tbody>
                @forelse($jadwalUjians as $index => $jadwal)
                    <tr>
                        <td class="no">{{ $index + 1 }}</td>
                        <td class="kode">{{ $jadwal->kelasKuliah?->matkul?->kode_mata_kuliah ?? '-' }}</td>
                        <td class="matkul">{{ $jadwal->kelasKuliah?->matkul?->nama_mata_kuliah ?? '-' }}</td>
                        <td class="paraf"></td>
                        <td class="paraf"></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 20px;">
                            Belum ada jadwal ujian {{ $jenisUjian }}
                        </td>
                    </tr>
                @endforelse
                {{-- Add empty rows to fill minimum 7 rows --}}
                @for ($i = $jadwalUjians->count(); $i < 7; $i++)
                    <tr>
                        <td class="no">{{ $i + 1 }}</td>
                        <td class="kode"></td>
                        <td class="matkul"></td>
                        <td class="paraf"></td>
                        <td class="paraf"></td>
                    </tr>
                @endfor
            </tbody>
        </table>

        <div class="footer">
            <div class="footer-note">
                <p>* Kartu ini harus selalu dibawa saat ujian</p>
                <p>* Dispensasi:</p>
            </div>
            <div class="footer-signature">
                <p class="date">Purworejo, {{ now()->translatedFormat('d F Y') }}</p>
                <p class="name">{{ $riwayat->prodi?->kaprodi?->nama ?? '___________________' }}</p>
                <p class="title">Ketua Program Studi</p>
            </div>
        </div>
    </div>
</body>

</html>

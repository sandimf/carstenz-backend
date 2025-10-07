

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Surat Pemeriksaan Kesehatan Pendakian</title>

    <style>
        @page {
            margin: 0;
            size: A5 portrait;
        }

        * {
            -webkit-print-color-adjust: exact !important;
            color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        @font-face {
            font-family: 'Dosis';
            font-weight: 300;
            src: url("{{ public_path('fonts/Dosis-Light.ttf') }}") format('truetype');
        }

        @font-face {
            font-family: 'Dosis';
            font-weight: 400;
            src: url("{{ public_path('fonts/Dosis-Book.ttf') }}") format('truetype');
        }

        @font-face {
            font-family: 'Dosis';
            font-weight: 500;
            src: url("{{ public_path('fonts/Dosis-Medium.ttf') }}") format('truetype');
        }

        @font-face {
            font-family: 'Dosis';
            font-weight: 600;
            src: url("{{ public_path('fonts/Dosis-SemiBold.ttf') }}") format('truetype');
        }

        @font-face {
            font-family: 'Dosis';
            font-weight: 700;
            src: url("{{ public_path('fonts/Dosis-Bold.ttf') }}") format('truetype');
        }

        @font-face {
            font-family: 'Dosis';
            font-weight: 800;
            src: url("{{ public_path('fonts/Dosis-ExtraBold.ttf') }}") format('truetype');
        }

        body {
            margin: 0;
            padding: 0;
            width: 148mm;
            height: 210mm;
            position: relative;
            @if($background_path)
            background: url("{{ $background_path }}") no-repeat;
            background-size: 148mm 210mm;
            background-position: top left;
            background-repeat: no-repeat;
            @endif
            font-family: 'Dosis', Arial, sans-serif;
            font-weight: 400;
         }
        .field {
            position: absolute;
            color: #003366;
            font-size: 9pt;
            line-height: 1;
            white-space: nowrap;
            font-family: 'Dosis', Arial, sans-serif;
            /* font-weight: 400; */
            /* DEBUG: outline boxes while aligning */
            /* outline:1px dashed red; */
        }

        /* --- re-calibrated positions --- */
        .no {
            top: 20.1mm;
            left: 10.5mm;
            width: 80mm;
        }

        /* No. */
        .name {
            top: 52.3mm;
            left: 30mm;
            width: 120mm;
        }

        /* Nama */
        .age {
            top: 52.3mm;
            left: 110mm;
            width: 20mm;
        }

        /* Umur */
        .id_no {
            top: 65.3mm;
            left: 30mm;
            width: 120mm;
        }

        /* No. Identitas */
        .gender {
            top: 65.3mm;
            left: 110mm;
            width: 30mm;
        }

        /* Jenis Kelamin */
        .address {
            top: 77mm;
            left: 30mm;
            width: 160mm;
        }
        .tinggi_badan {
            top: 87mm;
            left: 40mm;
            width: 160mm;
        }
        .berat_badan {
            top: 94.6mm;
            left: 40mm;
            width: 160mm;
        }
        .tekanan_darah {
            top: 87mm;
            left: 123mm;
            width: 160mm;
        }
        .saturasi_oksigen {
            top: 94.6mm;
            left: 123mm;
            width: 160mm;
        }

        .result_1 {
            top: 105.5mm;
            left: 10mm;
            font-size: 9.3pt;
        }

        /* ✓ Medically Fit */
        .result_2 {
            top: 109.3mm;
            left: 11mm;
            font-size: 9.3pt;
        }

        /* ✓ Fit with supervision */
        .result_3 {
            top: 113.5mm;
            left: 11mm;
            font-size: 9.3pt;
        }

        /* ✓ Not medically fit */
        .location {
            top: 218mm;
            left: 130mm;
            width: 60mm;
        }

        /* Ranupani */
        .year {
            top: 218mm;
            left: 185mm;
            width: 20mm;
        }

        /* 2025 */
        .sign_line {
            top: 226mm;
            left: 120mm;
            width: 70mm;
            border-top: 1px solid #003366;
        }
        .signature {
            position: absolute;
            top: 135mm;
            left: 73mm;
        }

        .pemeriksa {
            top: 164mm;
            left: 94mm;
            width: 30mm;
        }
        .qrcode {
            top: 144.5mm;
            left: 8.9mm;
            /* width: 40mm;
            height: 40mm; */
            text-align: center;
        }


        .qrcode img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>

<body>
    <div class="field no">
        {{ $medical_record_number ?? '-' }}/SKP/KGS/KUN/{{ $bulan_romawi ?? '-' }}/2025
    </div>
    <div class="field name">
        {{ ucwords(strtolower($screening->name ?? '-')) }}
    </div>
    <div class="field age">
        {{ $screening->age ?? '-' }}
    </div>
    <div class="field id_no">
        {{ $screening->nik ?? '-' }}
    </div>
    <div class="field gender">
        {{ ucwords(strtolower($screening->gender ?? '-')) }}
    </div>
    <div class="field address">
        {{ ucwords(strtolower($screening->address ?? '-')) }}
    </div>
    <div class="field tinggi_badan">
        {{ $screening->tinggi_badan?? '-' }} cm
    </div>
    <div class="field berat_badan">
       {{ $screening->berat_badan ?? '-' }} kg
    </div>
    <div class="field tekanan_darah">
        {{ $latestExamination->blood_pressure ?? '-' }} mmhg
    </div>
    <div class="field saturasi_oksigen">
        {{ $latestExamination->oxygen_saturation ?? '-' }} %
    </div>
    {{-- layak untuk mendaki / medically fit --}}
    <!-- <div class="field result_1">
        @if($screening->health_status === 'sehat')
        <img src="{{ public_path('images/pdf/check-mark.png') }}" alt="✓" style="width: 14px; height: 14px;">
        @endif
    </div> -->
    {{-- Layak dengan pendampingan medis / Fit with medical supervision --}}
    <!-- <div class="field result_2">
        @if(
        $screening->health_status === 'tidak_sehat' &&
        in_array(
            $screening->pendampingan ?? '',
            ['pendampingan_perawat', 'pendampingan_paramedis', 'pendampingan_dokter']
        )
    )
    <img src="{{ public_path('images/pdf/check-mark.png') }}" alt="✓" style="width: 14px; height: 14px;">
    @endif
    </div> -->
    <!-- <div class="field result_3">
        @if($screening->health_status === 'tidak_sehat' && empty($screening->pendampingan))
        <img src="{{ public_path('images/pdf/check-mark.png') }}" alt="✓" style="width: 14px; height: 14px;">
        @endif
    </div> -->

    <!-- QR Code Section -->
    <div class="field qrcode">
     @if(!empty($qr_code_base64))
        <img src="{{ $qr_code_base64 }}" style="width: 21.5mm; height: 21.5mm;" alt="QR Code" />
    @endif
    </div>


    <!-- <div class="field pemeriksa">
    {{-- @if(isset($examiner_name))
        <span>{{ $examiner_name }}</span>
    @endif --}}
    </div> -->


    {{-- <!-- @if(isset($examiner_signature_path) && $examiner_signature_path && file_exists($examiner_signature_path))
        <div class="field signature">
            <img src="{{ $examiner_signature_path }}" style="width: 90mm; height: auto;">
        </div>
    @endif --> --}}
</body>

</html>

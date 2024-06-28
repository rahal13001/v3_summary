<html>
<head>
  <link rel="shortcut icon" href="{{ asset('img/logoweb.png') }}">
  <title>Ekspor PDF</title>
  
  <style>
    @page {
      size: A4;
      margin: 2cm 2cm 2cm 2cm;
    }
    body {
        font-family: Arial;
      }
      .tabledata {
        border-collapse: collapse;
        border: none;
        width: 100%;
        padding: 5px;
      }
      td, th {
        padding: 8px;
        /* width: 50%; */
        vertical-align: top;
        text-align: justify;
      }
      .keterangan{
        width: 25%;
      }
      .titik{
        width: 5%;
      }
      .isi{
        width: 70%;
      }
      .header {
        text-align: center;
        font-weight: bold;
        network error
        font-size: 20px;
        }
        .header-img {
        width: 110%;
        height: auto;
        margin-top: -1.5cm;
        display: block;
        }
      
      .report-content {
          padding: 20px 0;
          padding-left: 10px;
          text-align: justify;
          text-justify: inter-word;
          
          
      }
      .report-content .isi {
        text-indent: 4em;
      }


    img {
        width: 100%; /* This will make the image take the full width of the .qr-code container */
        height: auto; /* This will maintain the aspect ratio of the image */
    }

    .img-content {
        width: auto;
        height: auto;
        display: inline-block;
        margin: 0 10px;
        clear: both; /* This will prevent the images from floating up next to the floated QR codes */
    }

    .qr-code {
      display: inline-block;
      width: 100px; /* Adjust as needed */
      height: 100px; /* Adjust as needed */
      text-align: center;
      align-content: center;
      margin-bottom: 20px;
      margin-top: 50px;
      page-break-inside: avoid;
      object-fit: cover;
      }

    .qr-code-container {
        margin-top: 30px;
        clear: both; /* This will prevent the QR code container from floating up next to the floated images */
    }
  </style>

  </head>
  <body>
    <!-- KOP Surat -->
    <div class="header">
      Laporan 5W1H <br>{{$report->what}}
    </div>


    <br>
    <!-- Tabel Informasi -->
    <table class="tabledata">
      <!-- Nomor Surat Tugas -->
      <tr>
        <td class="keterangan">Nomor ST</td>
        <td class="titik">:</td>
        <td class="isi">{{ $report->no_st }}</td>
      </tr>

      <!-- Judul Kegiatan -->
      {{-- <tr>
        <td>Judul Kegiatan</td>
        <td>:</td>
        <td>{{ $report->what }}</td>
      </tr> --}}
      <!-- Nama Pembuat Laporan -->
      <tr>
        <td>Penyusun</td>
        <td>:</td>
        <td>{{ $report->user->name }}
          @foreach ($report->followers as $item)
           @if ($loop->last)
              dan
           @endif
           @if(!$loop->last)
              ,
            @endif
          {{ $item->name }}
              
          @endforeach
        </td>
      </tr>
      <!-- Tanggal Mulai -->
      <tr>
        <td>Tanggal Mulai</td>
        <td>:</td>
        <td>{{ date('d-m-Y', strtotime($report->when ))}}</td>
      </tr>
      <!-- Tanggal Selesai -->
      <tr>
        <td>Tanggal Selesai</td>
        <td>:</td>
        <td>{{ date('d-m-Y', strtotime($report->tanggal_selesai ))}}</td>
      </tr>
      <!-- Tempat -->
      <tr>
        <td>Tempat</td>
        <td>:</td>
        <td>{{ $report->where }}</td>
      </tr>
      <!-- IKU -->
      <tr>
        <td>IKU</td>
        <td>:</td>
        <td>@foreach ($report->indicators as $iku)
            @if (!$loop->first && $loop->last)
              &
            @endif
            @if(!$loop->last)
                ,
              @endif
            {{ $iku->nama_iku }} (IKU : {{ $iku->nomor_iku }})
        @endforeach</td>
      </tr>
      <!-- Pihak Yang Terlibat -->
      <tr>
        <td>Pihak Yang Terlibat</td>
        <td>:</td>
        <td>{{ $report->who }}</td>
      </tr>

      {{-- Total Peserta --}}
      <tr>
        <td>Total Peserta</td>
        <td>:</td>
        <td>{{ $report->total_peserta }} orang, persentase perempuan yang hadir {{ $report->total_wanita}} %</td>
      </tr>
    </table>
    <!-- Deskripsi Kegiatan -->
    <div class="report-content">
      <p>Deskripsi Kegiatan</p>
     
        {!! $report->how !!}

        <div style="margin-top: 30px; margin-left: 55%;text-align:center;">
          <span style="margin-left: 30%">Penyusun</span>
            @if ($report->kode != null)
              <br>
              <br>
              <div>
                <img src="{!!$q_ttd!!}" alt="" srcset="" style="width: 40%">
              </div>
              <br>
            @else
            <br>
            <br>
            <br>
            <br>
            <br>
            @endif
          <span style="margin-left: 30%">{{$report->user->name}}</span>
      </div>

    </div>
<!-- Dokumentasi Gambar 1 -->
      <div style="margin: 30px 0; text-align: center;">
        <div style="margin-top: 30px; margin-bottom:30px;"><h4>Dokumentasi</h4></div>
       
       <img src="{{ asset('storage/'.$report->documentation->dokumentasi1) }}" style="width: 50%; height: auto; border: 1px solid #ccc;">
        {{-- <p style="margin-top: 10px;">Gambar 1: Judul Gambar 1</p> --}}
      </div>

      <!-- Dokumentasi Gambar 2 -->
      @if ($report->documentation->dokumentasi2 != null)
        
      <div style="margin: 30px 0; text-align: center;">
        <img src="{{ asset('storage/'.$report->documentation->dokumentasi2) }}" style="width: 50%; height: auto; border: 1px solid #ccc;">
        {{-- <p style="margin-top: 10px;">Gambar 2: Judul Gambar 2</p> --}}
      </div>
      @endif

      <!-- Dokumentasi Gambar 3 -->
      @if ($report->documentation->dokumentasi3 != null)
      <div style="margin: 30px 0; text-align: center;">
        <img src="{{ asset('storage/'.$report->documentation->dokumentasi3) }}" style="width: 50%; height: auto; border: 1px solid #ccc;">
        {{-- <p style="margin-top: 10px;">Gambar 3: Judul Gambar 3</p> --}}
      </div>
      @endif

   
    <!-- QR Code -->
    <div class="qr-code-container">
      {{-- <div style="margin-top: 30px;"><h4>QR Code</h4></div> --}}
      <table style="width: 100%; margin: 0 auto; margin-top:30px">
        <tr>
          @if ($report->documentation->st != null)
            <td style="text-align:center">ST <br><img style="width: 120px" src="{!! $q_st !!} "></td>
          @endif

          @if ($report->documentation->lainnya != null)
            <td style="text-align:center">Lainnya <br> <img style="width: 120px" src="{!! $q_lainnya !!} "></td>
          @endif

          <td style="text-align:center">5W1H <br> <img style="width: 120px" src="{!! $q_report !!} "></td>
        </tr>
      </table>
      
    </div>
  </body>
</html>
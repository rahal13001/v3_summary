<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <title>Verifikasi tanda tangan</title>
</head>
<body>


    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                Verifikasi Tanda Tangan
            </div>
            <div class="card-body">
                <div class="container">
                    <div class="row">
                        Laporan Ditanda Tangani Oleh : <strong>{{$report->user->name}}</strong>
                        Judul Laporan : <strong>{{$report->what}}</strong>
                    </div>
                    <div class="row mt-3">
                        <a role="button" class="btn btn-success" target="_blank" href="{{route('pdf', $report->slug)}}">Lihat Dokumen</a>
                    </div>
                </div>
            </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
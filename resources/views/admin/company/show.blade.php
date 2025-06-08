<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Information</title>
    <style>
        body { font-family: sans-serif; line-height: 1.6; padding: 20px; }
        .container { max-width: 700px; margin: auto; background: #f4f4f4; padding: 20px; border-radius: 5px; }
        h1 { text-align: center; }
        .alert { padding: 15px; margin-bottom: 20px; border: 1px solid transparent; border-radius: 4px; }
        .alert-success { color: #3c763d; background-color: #dff0d8; border-color: #d6e9c6; }
        .alert-info { color: #31708f; background-color: #d9edf7; border-color: #bce8f1; }
        .company-details p { margin-bottom: 10px; }
        .company-details strong { display: inline-block; width: 100px; }
        .action-btn { display: inline-block; padding: 10px 15px; background: #337ab7; color: #fff; text-decoration: none; border-radius: 3px; margin-top: 20px;}
    </style>
</head>
<body>
    <div class="container">
        <h1>Company Information</h1>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if($company)
            <div class="company-details">
                <p><strong>Name:</strong> {{ $company->name }}</p>
                <p><strong>Address:</strong> {{ nl2br(e($company->address)) }}</p>
                <p><strong>Phone:</strong> {{ $company->phone ?? 'N/A' }}</p>
                <p><strong>Email:</strong> {{ $company->email ?? 'N/A' }}</p>
                <p><strong>Website:</strong> <a href="{{ $company->website }}" target="_blank">{{ $company->website ?? 'N/A' }}</a></p>
                <p><strong>Logo Path:</strong> {{ $company->logo_path ?? 'N/A' }}</p>
                {{-- In a real app, display the logo image if path exists --}}
            </div>
            <a href="{{ route('admin.company.edit') }}" class="action-btn">Edit Company Information</a>
        @else
            <div class="alert alert-info">
                Company information has not been set up yet.
            </div>
            <a href="{{ route('admin.company.edit') }}" class="action-btn">Setup Company Information</a>
        @endif
    </div>
</body>
</html>

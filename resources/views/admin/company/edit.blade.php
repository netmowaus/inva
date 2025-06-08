<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $company ? 'Edit' : 'Setup' }} Company Information</title>
    <style>
        body { font-family: sans-serif; line-height: 1.6; padding: 20px; }
        .container { max-width: 700px; margin: auto; background: #f4f4f4; padding: 20px; border-radius: 5px; }
        h1 { text-align: center; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; }
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="url"],
        .form-group textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 3px; box-sizing: border-box; }
        .form-group textarea { min-height: 100px; }
        .btn-submit { display: block; width: 100%; padding: 10px 15px; background: #5cb85c; color: #fff; text-decoration: none; border-radius: 3px; border: none; cursor: pointer; font-size: 16px; }
        .btn-cancel { display: inline-block; margin-top:10px; color: #333; }
        .alert-danger ul { list-style-type: none; padding-left: 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>{{ $company ? 'Edit' : 'Setup' }} Company Information</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Whoops! Something went wrong.</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.company.update') }}" method="POST">
            @csrf
            @method('PUT') {{-- Or POST if you prefer a single endpoint for create/update --}}

            <div class="form-group">
                <label for="name">Company Name:</label>
                <input type="text" id="name" name="name" value="{{ old('name', $company->name ?? '') }}" required>
            </div>

            <div class="form-group">
                <label for="address">Address:</label>
                <textarea id="address" name="address" required>{{ old('address', $company->address ?? '') }}</textarea>
            </div>

            <div class="form-group">
                <label for="phone">Phone:</label>
                <input type="text" id="phone" name="phone" value="{{ old('phone', $company->phone ?? '') }}">
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="{{ old('email', $company->email ?? '') }}">
            </div>

            <div class="form-group">
                <label for="website">Website:</label>
                <input type="url" id="website" name="website" value="{{ old('website', $company->website ?? '') }}">
            </div>

            <div class="form-group">
                <label for="logo_path">Logo Path (URL or placeholder):</label>
                <input type="text" id="logo_path" name="logo_path" value="{{ old('logo_path', $company->logo_path ?? '') }}">
                <small>For this example, enter a URL or path. File upload would be implemented in a full app.</small>
            </div>

            <button type="submit" class="btn-submit">{{ $company ? 'Update' : 'Save' }} Information</button>
            <a href="{{ route('admin.company.show') }}" class="btn-cancel">Cancel</a>
        </form>
    </div>
</body>
</html>

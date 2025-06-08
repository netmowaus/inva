<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Client</title>
    <style>
        body { font-family: sans-serif; line-height: 1.6; padding: 20px; background-color: #f9f9f9; }
        .container { max-width: 700px; margin: auto; background: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { text-align: center; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 3px; box-sizing: border-box; }
        .form-group textarea { min-height: 100px; }
        .btn-submit { display: inline-block; padding: 10px 20px; background: #5cb85c; color: #fff; text-decoration: none; border-radius: 3px; border: none; cursor: pointer; font-size: 16px; }
        .btn-cancel { display: inline-block; margin-left: 10px; color: #333; text-decoration: none; }
        .alert-danger { background-color: #f2dede; border-color: #ebccd1; color: #a94442; padding: 15px; margin-bottom: 20px; border: 1px solid transparent; border-radius: 4px;}
        .alert-danger ul { list-style-type: none; padding-left: 0; margin-bottom:0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Add New Client</h1>

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

        <form action="{{ route('admin.clients.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="company_name">Company Name:</label>
                <input type="text" id="company_name" name="company_name" value="{{ old('company_name') }}" required>
            </div>

            <div class="form-group">
                <label for="contact_name">Contact Person Name:</label>
                <input type="text" id="contact_name" name="contact_name" value="{{ old('contact_name') }}" required>
            </div>

            <div class="form-group">
                <label for="contact_email">Contact Email:</label>
                <input type="email" id="contact_email" name="contact_email" value="{{ old('contact_email') }}" required>
            </div>

            <div class="form-group">
                <label for="address">Address:</label>
                <textarea id="address" name="address" required>{{ old('address') }}</textarea>
            </div>

            <div class="form-group">
                <label for="phone">Phone (Optional):</label>
                <input type="text" id="phone" name="phone" value="{{ old('phone') }}">
            </div>

            <button type="submit" class="btn-submit">Save Client</button>
            <a href="{{ route('admin.clients.index') }}" class="btn-cancel">Cancel</a>
        </form>
    </div>
</body>
</html>

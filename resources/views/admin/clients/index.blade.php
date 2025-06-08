<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Clients</title>
    <style>
        body { font-family: sans-serif; line-height: 1.6; padding: 20px; }
        .container { max-width: 900px; margin: auto; background: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { text-align: center; margin-bottom: 20px; }
        .alert { padding: 15px; margin-bottom: 20px; border: 1px solid transparent; border-radius: 4px; }
        .alert-success { color: #3c763d; background-color: #dff0d8; border-color: #d6e9c6; }
        .action-btn { display: inline-block; padding: 8px 12px; background: #337ab7; color: #fff; text-decoration: none; border-radius: 3px; margin-bottom: 20px; }
        .btn-edit { background: #f0ad4e; }
        .btn-delete { background: #d9534f; border:none; cursor:pointer; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .no-clients { text-align: center; color: #777; }
        .pagination { margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Manage Clients</h1>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <a href="{{ route('admin.clients.create') }}" class="action-btn">Add New Client</a>

        @if($clients->isEmpty())
            <p class="no-clients">No clients found.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Company Name</th>
                        <th>Contact Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($clients as $client)
                        <tr>
                            <td>{{ $client->company_name }}</td>
                            <td>{{ $client->contact_name }}</td>
                            <td>{{ $client->contact_email }}</td>
                            <td>{{ $client->phone ?? 'N/A' }}</td>
                            <td>
                                <a href="{{ route('admin.clients.edit', $client) }}" class="action-btn btn-edit">Edit</a>
                                <form action="{{ route('admin.clients.destroy', $client) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this client?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-btn btn-delete">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="pagination">
                {{ $clients->links() }}
            </div>
        @endif
    </div>
</body>
</html>

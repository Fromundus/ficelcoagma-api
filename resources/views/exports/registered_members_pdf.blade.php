<h2 style="font-family: sans-serif">Registered Members</h2>
<table width="100%" border="1" cellspacing="0" cellpadding="5" style="font-size: 10px; font-family: sans-serif;">
    <thead>
        <tr>
            <th>Account Number</th>
            <th>Book</th>
            <th>Name</th>
            <th>Address</th>
            <th>Occupant</th>
            <th>Phone Number</th>
            <th>Email</th>
            <th>Reference Number</th>
        </tr>
    </thead>
    <tbody>
        @foreach($members as $member)
        <tr>
            <td>{{ $member->account_number }}</td>
            <td>{{ $member->book }}</td>
            <td>{{ $member->name }}</td>
            <td>{{ $member->address }}</td>
            <td>{{ $member->occupant }}</td>
            <td>{{ $member->phone_number }}</td>
            <td>{{ $member->email }}</td>
            <td>{{ $member->reference_number }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

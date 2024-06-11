@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Loan Details</h1>
    @if (session('status'))
        <p>{{ session('status') }}</p>
    @endif
   
        <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Client ID</th>
                <th>Number of Payments</th>
                <th>First Payment Date</th>
                <th>Last Payment Date</th>
                <th>Loan Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($loanDetails as $loan)
                <tr>
                    <td>{{ $loan->id }}</td>
                    <td>{{ $loan->clientid }}</td>
                    <td>{{ $loan->num_of_payment }}</td>
                    <td>{{ $loan->first_payment_date }}</td>
                    <td>{{ $loan->last_payment_date }}</td>
                    <td>{{ $loan->loan_amount }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <form action="{{ route('loan_details.process') }}" method="POST">
        @csrf
        <button type="submit">Process Data</button>
    </form>

    @php
        use Illuminate\Support\Facades\Schema;
    @endphp

    @if (Schema::hasTable('emi_details'))
        <h2>EMI Details</h2>
        @php
            $emiColumns = Schema::getColumnListing('emi_details');
            unset($emiColumns[0]); // Remove 'id' column
        @endphp
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Client ID</th>
                    @foreach ($emiColumns as $column)
                        <th>{{ $column }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @php
                    $emiDetails = \DB::table('emi_details')->get();
                @endphp
                @foreach ($emiDetails as $emi)
                    <tr>
                        <td>{{ $emi->clientid }}</td>
                        @foreach ($emiColumns as $column)
                            <td>{{ $emi->$column }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    
    @else
        <p>No EMI details available. Please click "Process Data" to generate EMI details.</p>
    @endif
</div>
@endsection

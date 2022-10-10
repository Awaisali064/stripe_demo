@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Your Subscreption Details') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <table style="width:100%">
                        <tr>
                            <th>Type:</th>
                            <td>{{ ucfirst(Auth::user()->stripe_subscreption_type) }}</td>
                        </tr>
                        <tr>
                            <th>Auto Renew:</th>
                            @if(Auth::user()->auto_renew == 1)
                            <td>Yes</td>
                            @elseif(Auth::user()->auto_renew == 0)
                            <td>No</td>
                            @endif
                        </tr>
                        <!-- <tr>
                            <th>Telephone:</th>
                            <td>555 77 855</td>
                        </tr> -->
                        </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

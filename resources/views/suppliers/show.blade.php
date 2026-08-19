@extends('layouts.app')

@section('content')
@php
    $party = $supplier;
    $statementTitle = __('messages.supplier_statement');
    $printUrl = url('/suppliers/'.$supplier->id.'/print');
    $resetUrl = url('/suppliers/'.$supplier->id);
    $receiptUrl = url('/receipts?party_type=supplier&party_id='.$supplier->id);
    $paymentUrl = url('/payments?party_type=supplier&party_id='.$supplier->id);
@endphp
@include('statements.show')
@endsection

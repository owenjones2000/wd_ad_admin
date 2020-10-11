<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice</title>
    <style>
        .invoice{
            font-family: 'NSTRTE+Roboto-Regular';
        }
        .invoice table th{
            text-align: left;
        }
        .invoice h1, h3, hr{
            color: #666666;
        }
        .resume td{
        }
        .detail {
            line-height: 2em;
            border-collapse: collapse;
        }
        .detail .odd-row{
            background: whitesmoke;
        }
        .bank_info {
            font-size: 12px;
            line-height: 1.5em;
            border-collapse: collapse;
        }
        .bank_info .odd-row{
            /*background: whitesmoke;*/
        }
    </style>
</head>
<body>
<div class="invoice">
    <div style="height: 10px; background: #f6993f;" ></div>
    <div>
        <h3 style="font-size: 1.38125em; color: #666666;">
            GOOD LUCK STUDIO LIMITED
        </h3>
        <p style=" font-size: 0.8125em; color: #666666;">
            Mandar House, 3rd Floor P.O.Box 2196,Johnson’s Ghut Tortola,British Virgin Islands
        </p>
        @if($billAdr)
        <h3 style="font-size: 1.38125em; color: #666666;">
            Bill To:
        </h3>
        <p style=" font-size: 0.8125em; color: #666666;">
            {{$billAdr['company']}}
        </p>
        <p style=" font-size: 0.8125em; color: #666666;">
            {{$billAdr['address']}}
        </p>
        @endif
    </div>
    <h1>Invoice</h1>
    <hr />
    <table class="resume">
        <tr>
            <th style="width: 150px">
                Invoice for
            </th>
            <th style="width: 300px">
                Payable to
            </th>
            <th style="width: 100px;">
                Invoice #
            </th>
        </tr>
        <tr>
            <td rowspan="3">
                {{ $bill['account']['realname'] }}
            </td>
            <td>
                <p>GOOD LUCK STUDIO LIMITED</p>
            </td>
            <td>
                <p>{{ $bill['invoice_id'] }}</p>
            </td>
        </tr>
        <tr>
            <th>
                Project
            </th>
            <th>
                Due date
            </th>
        </tr>
        <tr>
            <td>
                <p>Service from {{ $bill['start_date'] }} to<br/> {{ $bill['end_date'] }}</p>
            </td>
            <td>
                <p>{{ $bill['due_date'] }}</p>
            </td>
        </tr>
    </table>
    <hr />
    <table class="detail" style="width: 100%">
        <tr>
            <th >Campaign</th>
            <th >Install</th>
            <th >Spend</th>
        </tr>
        @foreach ($billInfo as $item)
        <tr class="odd-row">
        <td>{{$item['campagin_name']}}</td>
        <td>{{$item['installations']}}</td>
        <td>${{$item['spend']}}</td>
        </tr>
        @endforeach
        {{-- <tr class="odd-row">
            <td>Service Fee</td>
            <td style="text-align: right;">${{ number_format($bill['fee_amount'], 2) }}</td>
        </tr> --}}
        {{-- <tr>
            <td>(See attached for invoice details.)</td><td></td>
        </tr> --}}
    </table>
    <hr />
    @if(isset($prePay) && !$prePay->isEmpty())
    <table class="detail" style="width: 100%">
        <tr>
            <th >Date</th>
            <th >Paid</th>
        </tr>
        @foreach ($prePay as $item)
        <tr class="odd-row">
        <td>{{$item['date']}}</td>
        <td>${{$item['amount']}}</td>
        </tr>
        @endforeach
    </table>
    <hr />
    
    <p style="color: #E01B84;text-align: right"><strong>Paid subtotal: ${{ number_format($prePay->sum('amount'), 2) }}</strong></p>
    <hr>
    @endif
    
    {{-- <p style="color: #E01B84;text-align: right"><strong>Dues Subtotal: ${{ !$prePay->isEmpty()?number_format($bill['fee_amount'] - $prePay->sum('amount'), 2):number_format($bill['fee_amount'], 2) }}</strong></p> --}}
    <p style="color: #E01B84;text-align: right"><strong>Dues Subtotal: ${{ number_format($bill['fee_amount']-$prePay->sum('amount'), 2) }}</strong></p>
    <hr />                             











 
    <table class="bank_info" style="width: 100%">
        <tr>
            <th style="">Payment Info:</th>
        </tr>
        <tr class="odd-row">
            <td>ACCOUNT NAME: GOOD LUCK STUDIO LIMITED </td>
        </tr>
        <tr class="odd-row">
            <td>ACCOUNT NUMBER :716-113721-8 (USD)</td>
        </tr>
        <tr class="">
            <td>BANK NAME:   OCBC BANK (M) BERHAD</td>
        </tr>
        <tr class="odd-row">
            <td>OCBC BANK SWIFT CODE: OCBCMYKL</td>
        </tr>
        <tr class="">
            <td>OCBC BRANCH CODE: First 3 digits of your account number</td>
        </tr>
        <tr class="odd-row">
            <td>Bank address:MENARA OCBC FLOOR 11 18 JALAN TUN PERAK KUALA LUMPUR 50500</td>
        </tr>
        <tr class="">
            <td>Recipient's address:Mandar House, 3rd Floor P.O.Box 2196,Johnson’s Ghut Tortola,British Virgin Islands</td>
        </tr>
    </table>
    <p>

    </p>
</div>
</body>
</html>
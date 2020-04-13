<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice</title>
    <style>
        body{
            font-family: 'NSTRTE+Roboto-Regular';
        }
        table th{
            text-align: left;
        }
        h1, h3, hr{
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
<div>
    <div style="height: 10px; background: #f6993f;" ></div>
    <div>
        <h3 style="font-size: 1.38125em; color: #666666;">
            Essentials Tech Limited
        </h3>
        <p style="width:150px; font-size: 0.8125em; color: #666666;">
            FLAT/RM A5, 9/F SILVERCORP INT*L TOWER 707-713 NATHAN RD, MONGKOK KLN, HONG KONG
        </p>
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
                <p>Essentials Tech Limited</p>
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
            <th style="width: 80%;">Description</th>
            <th style="text-align: right;">Fee Amount</th>
        </tr>
        <tr class="odd-row">
            <td>Service Fee</td>
            <td style="text-align: right;">${{ number_format($bill['fee_amount'], 2) }}</td>
        </tr>
        <tr>
            <td>(See attached for invoice details.)</td><td></td>
        </tr>
    </table>
    <hr />
    <p style="color: #E01B84;text-align: right"><strong>Subtotal: ${{ number_format($bill['fee_amount'], 2) }}</strong></p>
    <hr />
    <table class="bank_info" style="width: 50%">
        <tr>
            <th style="">Payment Info:</th>
        </tr>
        <tr class="odd-row">
            <td>012 BANK OF CHINA (HONG KONG) LIMITED</td>
        </tr>
        <tr class="odd-row">
            <td>Name: ESSENTIALS TECH LIMITED</td>
        </tr>
        <tr class="">
            <td>Account (HKD): 80510752405 (HKD)</td>
        </tr>
        <tr class="odd-row">
            <td>Account (USD): 80592389638 (USD)</td>
        </tr>
        <tr class="">
            <td>swift code: BKCHHKHH</td>
        </tr>
        <tr class="odd-row">
            <td>Address: Shop 2103, Level 2, Ma On Shan Plaza Sai Sha Road, Ma On Shan, New Territories, Hong Kong</td>
        </tr>
        <tr class="">
            <td>Bank Name: Bank of China (Hong Kong) Ltd</td>
        </tr>
        <tr class="odd-row">
            <td>Bank Country: Hong Kong</td>
        </tr>
        <tr class="">
            <td>Clearing Code: 012 / 805</td>
        </tr>
    </table>
    <p>

    </p>
</div>
</body>
</html>
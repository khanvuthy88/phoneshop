@extends('layouts/contract-invoice')
@section('title', trans('app.invoice'))
@section('content')
    <div class="row">
        <div class="col-md-12">
            <table>
                <tr>
                    <td>លេខគណនី (LID)</td>
                    <td>:</td>
                    <td>{{ $loan->account_number . (isset($loan->account_number_append) ? '-' . $loan->account_number_append : '') }}</td>
                    <th rowspan="5" style="padding: 0px 20px 0px 50px;">
                        <span style="font-size:16px; font-family: 'Moul', cursive; font-weight:normal;">វិក័យប័ត្រ - ទទួលទំនិញ</span><br/>
                        ថ្ងៃទី: {{ khmerDate($loan->updated_at) }}<br>
                        លេខៈ {{ substr($loan->account_number, 4) }}
                    </th>
                </tr>
                <tr>
                    <td>ឈ្មោះ:</td>
                    <td>:</td>
                    <td>{{ $loan->client->name }}</td>
                    <td></td>
                </tr>
                <tr>
                    <td>លេខទូរស័ព្ទ</td>
                    <td>:</td>
                    <td>{{ $loan->client->first_phone }}
                        @isset ($loan->client->second_phone)) / {{ $loan->client->second_phone }} @endisset
                    </td>
                </tr>
                <tr>
                    <td>លេខអត្តសញ្ញាប័ណ្ណ</td>
                    <td>:</td>
{{--                    <td>{{ $loan->client->id_card_number ?? trans('app.none') }}</td>--}}
                </tr>
                <tr>
                    <td>ភ្នាក់ងារ</td>
                    <td>:</td>
                    <td>{{ $loan->staff->name }}</td>
                </tr>
                <tr>
                    <td>អាស័យដ្ឋាន</td>
                    <td>:</td>
                    <td>{{ $loan->client->address }}</td>
                </tr>
            </table>
        </div>
    </div>
    <div class="row render" >
        <div class="col-md-12">
            <table>
                <tr style="background: #EEE;">
                    <th style="width:65%;">ពណ៌នាអំពីទំនិញ</th>
                    <th style="width:10%;">ចំនួន</th>
                    <th style="width:10%;">តម្លៃរាយ</th>
                    <th style="width:15%;">តម្លែសរុប (ដុល្លា)</th>
                </tr>
                <tr>
                    <td style="padding: 10px 10px;">
                        {{ $loan->product->name }}, លេខកូដ (IME) {{ $loan->product_ime }}
                    </td>
                    <td>1</td>
                    <td>{{ decimalNumber($loan->product->price) }}</td>
                    <td>{{ decimalNumber($loan->product->price) }}</td>
                </tr>
                <tr>
                    <td rowspan="3" class="border-0">
                        <b>ចំណាំ៖</b> - សូមពិនិត្យទំនិញមុនចាកចេញ<br>
                        <span style="padding-left:35px"> - មិនទទួលខុសត្រូវចំពោះទូរស័ព្ទដែលដាក់កន្លែងមានសីតុណ្ហភាពខ្ពស់ ផ្ទុះសេ ចូលទឹក </span>
                        <br>
                        <span style="padding-left:35px">និងធ្លាក់បាក់បែក (ទិញរួចមិនអាចប្តូរយកលុយវិញបានទេ)</span>
                        <br>
                        <span style="padding-left:35px"> - តម្លៃ​សេវា​បន្ថែម : <b> {{ isset($loan->extra_fee) ? '$' . decimalNumber($loan->extra_fee) : trans('app.none') }}</b></span>
                    </td>
                    <td colspan="2" class="text-right">ទឹកប្រាក់សរុប</td>
                    <td style="background: #EEE;" >{{ decimalNumber($loan->product->price) }}</td>
                </tr>
                <tr>
                    <td colspan="2" class="text-right">បានបង់ប្រាក់មុន</td>
                    <td style="background: #EEE;">{{ decimalNumber($loan->depreciation_amount) }}</td>
                </tr>
                <tr>
                    <td colspan="2" class="text-right">នៅសល់</td>
                    <td style="background: #EEE;">{{ decimalNumber($loan->loan_amount) }}</td>
                </tr>
            </table>
            <p class="text-right" style="font-size: 12px;">(1 ដុល្លា = 4100 រៀល)</p>
        </div>
        <div class="col-md-12">
            <p class="text-center" style="margin: 20px 0 80px 0">
                <span style="padding:20px; padding-right:300px;"> អ្នកលក់ </span>អ្នកទិញ
            </p>
        </div>
    </div>
@endsection

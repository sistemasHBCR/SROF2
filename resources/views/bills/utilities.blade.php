<!DOCTYPE html>
<html class="no-js" lang="en">

<head>
    <!-- Meta Tags -->
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="Laralink">
    <!-- Site Title -->
    <title>Utility Bill</title>
    <link rel="stylesheet" href="{{ asset('assets/css/bills/style.css') }}" />
</head>

<body>
    @foreach ($data as $index => $row)
        <div class="tm_container" id="tm_container_{{ $index }}">
            <div class="tm_invoice_wrap">
                <div class="tm_invoice tm_style1" id="tm_download_section">
                    <div class="tm_coffee_shop_img">
                        <img src="{{ asset('') }}" alt>
                    </div>
                    <div class="tm_invoice_in">
                        <div class="tm_invoice_head tm_align_center">
                            <div class="tm_invoice_left">
                                <div class="tm_logo"><img style="max-height:100px"
                                        src="../../logo.png" alt="Logo"></div>
                            </div>
                            <div class="tm_invoice_right tm_text_right">
                                <div class="tm_primary_color tm_f50 tm_text_uppercase">utilities
                                    bill</div>
                            </div>
                        </div>
                        <div class="tm_invoice_info tm_mb20">
                            <div class="tm_invoice_seperator"></div>
                            <div class="tm_invoice_info_list">
                                <p class="tm_invoice_number tm_m0">No: <b class="tm_primary_color">#{{$row->id}}</b></p>
                                <p class="tm_invoice_date tm_m0">Period: <b
                                        class="tm_primary_color">{{ \Carbon\Carbon::parse($start_date)->format('d.m.Y') }}
                                        - {{ \Carbon\Carbon::parse($end_date)->format('d.m.Y') }}
                                    </b></p>
                            </div>
                        </div>
                        <div class="tm_invoice_head tm_mb10">
                            <div class="tm_invoice_left">
                                <p class="tm_mb2"><b class="tm_primary_color">Residence
                                        {{ $row->residencia }}</b></p>
                                <p> {{ $row->owner }}<br>
                                </p>
                            </div>
                            <div class="tm_invoice_right tm_text_right">

                            </div>
                        </div>
                        <div class="tm_table tm_style1 tm_mb30 tm_m0_md">
                            <div class="tm_radius_0">
                                <div class="tm_table_responsive">
                                    <table class="tm_border_bottom tm_border_top">
                                        <thead>
                                            <tr>
                                                <th class="tm_width_3 tm_semi_bold tm_primary_color tm_gray_bg">Concept
                                                </th>
                                                <th class="tm_width_2 tm_semi_bold tm_primary_color tm_gray_bg">Quantity
                                                </th>
                                                <th
                                                    class="tm_width_2 tm_semi_bold tm_primary_color tm_gray_bg tm_text_right">
                                                    Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="tm_table_baseline">
                                                <td class="tm_width_7 tm_primary_color">Electricity</td>
                                                <td class="tm_width_2">{{ number_format($row->kw, 2, '.', ',') }} Kw/h
                                                </td>
                                                <td class="tm_width_2 tm_text_right">
                                                    ${{ number_format($row->total_kwfee, 2, '.', ',') }}</td>
                                            </tr>
                                            <tr class="tm_table_baseline">
                                                <td class="tm_width_7 tm_primary_color">Water</td>
                                                <td class="tm_width_2">{{ number_format($row->agua, 2, '.', ',') }} M3
                                                </td>
                                                <td class="tm_width_2 tm_text_right">
                                                    ${{ number_format($row->total_agua, 2, '.', ',') }}</td>
                                            </tr>
                                            <tr class="tm_table_baseline">
                                                <td class="tm_width_7 tm_primary_color">Sewer</td>
                                                <td class="tm_width_2">{{ number_format($row->agua, 2, '.', ',') }} M3
                                                </td>
                                                <td class="tm_width_2 tm_text_right">
                                                    ${{ number_format($row->total_sewer, 2, '.', ',') }}</td>
                                            </tr>
                                            <tr class="tm_table_baseline">
                                                <td class="tm_width_7 tm_primary_color">Propane</td>
                                                <td class="tm_width_2">{{ number_format($row->gas, 2, '.', ',') }} M3
                                                </td>
                                                <td class="tm_width_2 tm_text_right">
                                                    {{ number_format($row->total_gasfee, 2, '.', ',') }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tm_invoice_footer">
                                <div class="tm_left_footer tm_padd_left_15_md">
                                    <!--
                <p class="tm_mb2"><b class="tm_primary_color">Payment info:</b></p>
                <p class="tm_m0">Credit Card - 236***********928 <br>Amount: $272</p>
                   -->
                                </div>
                                <div class="tm_right_footer">
                                    <table>
                                        <tbody>
                                            <tr class="tm_gray_bg tm_border_top">
                                                <td class="tm_width_3 tm_primary_color tm_border_none tm_bold">Subtotal
                                                </td>
                                                <td
                                                    class="tm_width_3 tm_primary_color tm_text_right tm_border_none tm_bold">
                                                    ${{ number_format($row->subtotal, 2, '.', ',') }}</td>
                                            </tr>
                                            <!--
                    <tr class="tm_gray_bg">
                      <td class="tm_width_3 tm_primary_color tm_border_none tm_pt0">Discount <span class="tm_ternary_color">(0%)</span></td>
                      <td class="tm_width_3 tm_text_right tm_border_none tm_pt0 tm_danger_color">-$0</td>
                    </tr>
                    --->
                                            <tr class="tm_gray_bg">
                                                <td class="tm_width_3 tm_primary_color tm_border_none tm_pt0">Tax
                                                    <span class="tm_ternary_color">(16%)</span>
                                                </td>
                                                <td
                                                    class="tm_width_3 tm_primary_color tm_text_right tm_border_none tm_pt0">
                                                    +${{ number_format($row->tax, 2, '.', ',') }}</td>
                                            </tr>
                                            <tr class="tm_border_top tm_border_bottom tm_gray_bg">
                                                <td class="tm_width_3 tm_border_top_0 tm_bold tm_f16 tm_primary_color">
                                                    Total </td>
                                                <td
                                                    class="tm_width_3 tm_border_top_0 tm_bold tm_f16 tm_primary_color tm_text_right">
                                                    ${{ number_format($row->total, 2, '.', ',') }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <hr class="tm_mb20">
                        <div class="tm_text_center">
                            <p class="tm_mb5"><b class="tm_primary_color">For your information:</b></p>
                            <p class="tm_m0">All prices shown on this receipt are in United States dollars (USD).</p>
                        </div><!-- .tm_note -->
                    </div>
                </div>
                <div class="tm_invoice_btns tm_hide_print">
                    <a onclick="printDiv('tm_container_{{ $index }}')" class="tm_invoice_btn tm_color1">
                        <span class="tm_btn_icon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="ionicon" viewBox="0 0 512 512">
                                <path
                                    d="M384 368h24a40.12 40.12 0 0040-40V168a40.12 40.12 0 00-40-40H104a40.12 40.12 0 00-40 40v160a40.12 40.12 0 0040 40h24"
                                    fill="none" stroke="currentColor" stroke-linejoin="round" stroke-width="32" />
                                <rect x="128" y="240" width="256" height="208" rx="24.32" ry="24.32"
                                    fill="none" stroke="currentColor" stroke-linejoin="round"
                                    stroke-width="32" />
                                <path d="M384 128v-24a40.12 40.12 0 00-40-40H168a40.12 40.12 0 00-40 40v24"
                                    fill="none" stroke="currentColor" stroke-linejoin="round"
                                    stroke-width="32" />
                                <circle cx="392" cy="184" r="24" fill='currentColor' />
                            </svg>
                        </span>
                        <span class="tm_btn_text">Print</span>
                    </a>
                    <!--
                    <button id="tm_download_btn" class="tm_invoice_btn tm_color2">
                        <span class="tm_btn_icon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="ionicon" viewBox="0 0 512 512">
                                <path
                                    d="M320 336h76c55 0 100-21.21 100-75.6s-53-73.47-96-75.6C391.11 99.74 329 48 256 48c-69 0-113.44 45.79-128 91.2-60 5.7-112 35.88-112 98.4S70 336 136 336h56M192 400.1l64 63.9 64-63.9M256 224v224.03"
                                    fill="none" stroke="currentColor" stroke-linecap="round"
                                    stroke-linejoin="round" stroke-width="32" />
                            </svg>
                        </span>
                        <span class="tm_btn_text">Download</span>
                    </button>-->
                </div>
            </div>
        </div>
    @endforeach
    <script src="{{ asset('assets/jsv3/bills/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/jsv3/bills/jspdf.min.js') }}"></script>
    <script src="{{ asset('assets/jsv3/bills/html2canvas.min.js') }}"></script>
    <script src="{{ asset('assets/jsv3/bills/main.js') }}"></script>
    <script src="{{ asset('assets/jsv3/bills/print.js') }}"></script>
</body>

</html>
